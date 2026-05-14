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

    /**
     * Get all checklists in a course, optionally including their items and student progress
     *
     * @param int $courseid
     * @param bool $getitems Whether to get the checklist items as well (takes longer)
     * @param int $studentid The student ID for whom to retrieve checked items
     * @return array of checklist records
     */
    public static function get_course_checklists($courseid, $getitems = false, $studentid = 0)
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
    public static function get_checklist_items($checklistid, $studentid = 0)
    {
        global $DB;

        // Get all items for this checklist
        $items = $DB->get_records(
            self::DB_CHECKLIST_ITEMS,
            ['checklist' => $checklistid],
            'position ASC'
        );

        if (!$studentid) {
            return $items;
        }

        // Get student's check status for each item
        foreach ($items as $item) {
            $check = $DB->get_record(self::DB_CHECKLIST_CHECK, [
                'item' => $item->id,
                'userid' => $studentid
            ]);

            $item->studentcheck = $check ? $check->usertimestamp : 0;
            $item->teachercheck = $check ? $check->teachermark : CHECKLIST_TEACHERMARK_UNDECIDED;
            $item->teachertimestamp = $check ? $check->teachertimestamp : 0;
            $item->checkid = $check ? $check->id : 0;
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
     * @return object checklist record
     */
    public static function update_course_checklist_item($itemid, $studentid, $teacherid, $state)
    {
        global $DB;

        $item = $DB->get_record(self::DB_CHECKLIST_ITEMS, ['id' => $itemid], '*', MUST_EXIST);
        $checklist = $DB->get_record(self::DB_CHECKLIST, ['id' => $item->checklist], '*', MUST_EXIST);

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
}