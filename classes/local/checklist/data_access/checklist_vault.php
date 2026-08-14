<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Checklist plugin data access functions
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\checklist\data_access;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class checklist_vault implements checklist_vault_interface
{

    /** Course modules for graded sessions */
    const DB_COURSE_MODS = 'course_modules';

    // course module tables
    const DB_MODULES = 'modules';

    /** Checklist table */
    const DB_CHECKLIST = 'checklist';

    /** Checklist items table */
    const DB_CHECKLIST_ITEMS = 'checklist_item';

    /** Checklist check table */
    const DB_CHECKLIST_CHECK = 'checklist_check';

    /** Teacher comments on a student's checklist item */
    const DB_CHECKLIST_COMMENT = 'checklist_comment';

    /**
     * Get all checklists in a course, optionally including their items and student progress
     *
     * @param int $courseid
     * @param bool $getitems Whether to get the checklist items as well (takes longer)
     * @param int $studentid The student ID for whom to retrieve checked items
     * @return array of checklist records
     */
    public static function get_course_checklists(int $courseid, bool $getitems = false, int $studentid = 0)
    {
        global $DB;

        $sql = "SELECT c.*, cm.id as cmid
                FROM {" . self::DB_CHECKLIST . "} c
                JOIN {" . self::DB_COURSE_MODS . "} cm ON cm.instance = c.id
                JOIN {" . self::DB_MODULES . "} m ON m.id = cm.module
                WHERE c.course = :courseid
                AND m.name = 'checklist'
                AND cm.deletioninprogress = 0
                ORDER BY cm.section, cm.id";

        $checklists = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if ($getitems) {
            // Get items for each checklist
            foreach ($checklists as $checklist) {
                $checklist->items = self::get_checklist_items($checklist->id, $studentid);
            }
        }
        return $checklists;
    }

    /**
     * Get checklist items with student progress
     *
     * @param int $checklistid
     * @param int $studentid (optional)
     * @return array of items with progress
     */
    public static function get_checklist_items($checklistid, $studentid = 0) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/checklist/lib.php');

        // Get all items for this checklist
        $items = $DB->get_records(
            self::DB_CHECKLIST_ITEMS,
            ['checklist' => $checklistid],
            'position ASC'
        );

        if (!$studentid) {
            return $items;
        }

        $comments = [];
        if (!empty($items)) {
            list($insql, $inparams) = $DB->get_in_or_equal(array_keys($items), SQL_PARAMS_NAMED);
            $inparams['userid'] = $studentid;
            $comments = $DB->get_records_select(
                self::DB_CHECKLIST_COMMENT,
                "userid = :userid AND itemid $insql",
                $inparams,
                '',
                'itemid, text, commentby'
            );
        }

        // Get student's check status and teacher comment for each item.
        $checks = [];
        if (!empty($items)) {
            list($checksql, $checkparams) = $DB->get_in_or_equal(array_keys($items), SQL_PARAMS_NAMED, 'ch');
            $checkparams['userid'] = $studentid;
            $checks = $DB->get_records_select(
                self::DB_CHECKLIST_CHECK,
                "userid = :userid AND item $checksql",
                $checkparams,
                '',
                'item, usertimestamp, teachermark, teachertimestamp, id'
            );
        }

        foreach ($items as $item) {
            $check = $checks[$item->id] ?? null;

            $item->studentcheck = $check ? $check->usertimestamp : 0;
            $item->teachercheck = $check ? $check->teachermark : CHECKLIST_TEACHERMARK_UNDECIDED;
            $item->teachertimestamp = $check ? $check->teachertimestamp : 0;
            $item->checkid = $check ? $check->id : 0;
            $item->teachercomment = isset($comments[$item->id]) ? $comments[$item->id]->text : '';
            $item->teachermark_comment = $item->teachercomment;
        }

        return $items;
    }

    /**
     * Update checklist item for student
     *
     * @param int $checklistid
     * @param int $studentid
     * @return array of checked items
     */
    public static function get_checked_items($checklistid, $studentid)
    {
        global $DB;

        $sql = "SELECT *
                FROM {" . self::DB_CHECKLIST_CHECK . "}
                WHERE userid = :userid
                AND item IN (SELECT id FROM {" . self::DB_CHECKLIST_ITEMS . "} WHERE checklist = :checklistid)";

        $records = $DB->get_records_sql($sql, [
            'userid' => $studentid,
            'checklistid' => $checklistid
        ]);

        return array_column($records, 'item');
    }

    /**
     * Update checklist item for student
     *
     * @param int $itemid
     * @param int $studentid
     * @param int $teacherid
     * @param int $state (CHECKLIST_TEACHERMARK_YES or CHECKLIST_TEACHERMARK_NO)
     * @param int $courseid Optional course id to verify the item belongs to this course
     * @return object checklist record
     */
    public static function update_course_checklist_item($itemid, $studentid, $teacherid, $state, int $courseid = 0)
    {
        global $DB;

        $item = $DB->get_record(self::DB_CHECKLIST_ITEMS, ['id' => $itemid], '*', MUST_EXIST);
        $checklist = $DB->get_record(self::DB_CHECKLIST, ['id' => $item->checklist], '*', MUST_EXIST);
        if ($courseid && (int) $checklist->course !== $courseid) {
            throw new \moodle_exception('nopermissions', 'error');
        }

        // Check if record exists
        $check = $DB->get_record(self::DB_CHECKLIST_CHECK, [
            'item' => $itemid,
            'userid' => $studentid
        ]);

        if ($check) {
            // Update existing
            $check->teachermark = $state;
            $check->teachertimestamp = time();
            $check->teacherid = $teacherid;
            $DB->update_record(self::DB_CHECKLIST_CHECK, $check);
        }

        if (!$check) {
            // Create new
            $check = new stdClass();
            $check->item = $itemid;
            $check->userid = $studentid;
            $check->teachermark = $state;
            $check->teachertimestamp = time();
            $check->teacherid = $teacherid;
            $check->usertimestamp = 0;
            $DB->insert_record(self::DB_CHECKLIST_CHECK, $check);
        }

        return $checklist;
    }

    /**
     * Create, update, or delete a teacher comment on a student's checklist item.
     * Empty comment text removes the existing comment, matching the Checklist plugin.
     *
     * @param int $itemid The checklist item id
     * @param int $studentid The student the comment is for
     * @param int $teacherid The teacher writing the comment
     * @param string $comment The comment text
     * @param int $courseid Optional course id to verify the item belongs to this course
     * @return object The parent checklist record
     */
    public static function update_checklist_comment(int $itemid, int $studentid, int $teacherid, string $comment, int $courseid = 0)
    {
        global $DB;

        $item = $DB->get_record(self::DB_CHECKLIST_ITEMS, ['id' => $itemid], '*', MUST_EXIST);
        $checklist = $DB->get_record(self::DB_CHECKLIST, ['id' => $item->checklist], '*', MUST_EXIST);
        if ($courseid && (int) $checklist->course !== $courseid) {
            throw new \moodle_exception('nopermissions', 'error');
        }

        $comment = trim($comment);
        $existing = $DB->get_record(self::DB_CHECKLIST_COMMENT, [
            'itemid' => $itemid,
            'userid' => $studentid,
        ]);

        if ($comment === '') {
            if ($existing) {
                $DB->delete_records(self::DB_CHECKLIST_COMMENT, ['id' => $existing->id]);
            }
        } else if ($existing) {
            if ($existing->text != $comment) {
                $existing->text = $comment;
                $existing->commentby = $teacherid;
                $DB->update_record(self::DB_CHECKLIST_COMMENT, $existing);
            }
        } else {
            $addcomment = new stdClass();
            $addcomment->itemid = $itemid;
            $addcomment->userid = $studentid;
            $addcomment->commentby = $teacherid;
            $addcomment->text = $comment;
            $DB->insert_record(self::DB_CHECKLIST_COMMENT, $addcomment);
        }

        return $checklist;
    }

    /**
     * Get pending checklists for a student in a course
     *
     * @param int $courseid
     * @param int $studentid
     * @return array Array of pending checklist names or empty array if none pending
     */
    public static function get_pending_checklists_for_student(int $courseid, int $studentid)
    {
        global $DB;
        $checklistnames = [];

        // Query to get checklists with pending (unchecked) items for this student
        $sql = 'SELECT DISTINCT c.id, c.name
                  FROM {' . self::DB_CHECKLIST . '} c
            INNER JOIN {' . self::DB_CHECKLIST_ITEMS . '} ci ON ci.checklist = c.id
             LEFT JOIN {' . self::DB_CHECKLIST_CHECK . '} sc ON sc.item = ci.id
                   AND sc.userid = :studentid
                 WHERE c.course = :courseid
                   AND (sc.id IS NULL OR sc.teachermark = 0)
              ORDER BY c.name';

        $params = [
            'studentid' => $studentid,
            'courseid' => $courseid,
        ];

        $pendingchecklists = $DB->get_records_sql($sql, $params);

        if (!empty($pendingchecklists)) {
            // Build array of checklist names
            $checklistnames = array_map(function ($checklist) {
                return $checklist->name;
            }, $pendingchecklists);
        }

        return $checklistnames;
    }
}