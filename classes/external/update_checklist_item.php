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
 * Checklist external API class
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');
require_once($CFG->dirroot . '/mod/checklist/lib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use local_booking\local\checklist\entities\checklist;

/**
 * External function to update a checklist item sign-off or teacher comment.
 *
 * @package    local_booking
 */
class update_checklist_item extends external_api {
    use helper;

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'studentid' => new external_value(PARAM_INT, 'Student user ID'),
            'bookingid' => new external_value(PARAM_INT, 'Booking ID'),
            'itemid' => new external_value(PARAM_INT, 'Checklist item ID'),
            'state' => new external_value(PARAM_INT, 'Check state (0=undecided, 1=no, 2=yes)', VALUE_DEFAULT, 0),
            'comment' => new external_value(PARAM_TEXT, 'Teacher comment for the checklist item', VALUE_DEFAULT, ''),
            'savecomment' => new external_value(PARAM_BOOL, 'Whether to save the teacher comment instead of the sign-off', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Update a checklist item sign-off or teacher comment for a student.
     *
     * @param int $courseid
     * @param int $studentid
     * @param int $bookingid
     * @param int $itemid
     * @param int $state
     * @param string $comment
     * @param bool $savecomment
     * @return array
     */
    public static function execute($courseid, $studentid, $bookingid, $itemid, $state = 0, $comment = '', $savecomment = false) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'studentid' => $studentid,
            'bookingid' => $bookingid,
            'itemid' => $itemid,
            'state' => $state,
            'comment' => $comment,
            'savecomment' => $savecomment,
        ]);

        self::validate_course_access($params['courseid'], 'mod/checklist:updateother');

        $checklist = new checklist($params['courseid'], $params['studentid']);
        $success = true;

        // Save teacher comment without changing the sign-off when only the comment is submitted.
        if ($params['savecomment']) {
            $success = $checklist->update_checklist_comment(
                $params['itemid'],
                $params['studentid'],
                $USER->id,
                $params['comment']
            );
        } else {
            $success = $checklist->update_checklist_item(
                $params['itemid'],
                $params['studentid'],
                $USER->id,
                $params['state'],
                $params['bookingid']
            );
        }

        $items = $checklist->get_items($params['studentid']);
        $checkedcount = 0;
        $itemcount = 0;
        foreach ($items as $item) {
            if ($item->hidden) {
                continue;
            }

            $isheading = ($item->itemoptional == CHECKLIST_OPTIONAL_HEADING);

            if (!$isheading) {
                $itemcount++;
                if ($item->teachercheck == CHECKLIST_TEACHERMARK_YES) {
                    $checkedcount++;
                }
            }
        }

        $progress = $itemcount > 0 ? round(($checkedcount / $itemcount) * 100) : 0;

        return [
            'success' => $success,
            'timestamp' => userdate(time(), get_string('strftimedatetimeshort', 'langconfig')),
            'checklistid' => $checklist->id,
            'count' => "$checkedcount / $itemcount",
            'progress' => "$progress%",
            'message' => $success ? get_string('checklistitemupdated', 'local_booking') : get_string('checklistitemupdatefailed', 'local_booking'),
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'timestamp' => new external_value(PARAM_TEXT, 'Timestamp of the update'),
            'checklistid' => new external_value(PARAM_INT, 'Checklist ID'),
            'count' => new external_value(PARAM_TEXT, 'Checked count out of total items'),
            'progress' => new external_value(PARAM_TEXT, 'Progress of items check'),
            'message' => new external_value(PARAM_TEXT, 'Response message'),
        ]);
    }
}
