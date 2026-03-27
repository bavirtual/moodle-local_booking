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

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_booking\local\checklist\entities\checklist;

class update_checklist_item extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'studentid' => new external_value(PARAM_INT, 'Student user ID'),
            'bookingid' => new external_value(PARAM_INT, 'Booking ID'),
            'itemid' => new external_value(PARAM_INT, 'Checklist item ID'),
            'state' => new external_value(PARAM_INT, 'Check state (0=undecided, 1=no, 2=yes)'),
        ]);
    }

    public static function execute($courseid, $studentid, $bookingid, $itemid, $state) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'studentid' => $studentid,
            'bookingid' => $bookingid,
            'itemid' => $itemid,
            'state' => $state,
        ]);

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('mod/checklist:updateother', $context);

        require_once(__DIR__ . '/../../lib.php');

        $checklist = new checklist($courseid, $studentid);
        $success = $checklist->update_checklist_item(
            $params['itemid'],
            $params['studentid'],
            $USER->id,
            $params['state'],
            $params['bookingid']
        );

        // Get checked items count out of the total and progress percentage
        $items = $checklist->get_items($studentid);
        $checkedcount = 0;
        $itemcount = 0;
        foreach ($items as $item) {
            // Skip hidden items for students
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
            'timestamp' => date('d M Y, H:i'),
            'checklistid' => $checklist->id,
            'count' => "$checkedcount / $itemcount",
            'progress' => "$progress%",
            'message' => $success ? 'Updated successfully' : 'Update failed',
        ];
    }

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
