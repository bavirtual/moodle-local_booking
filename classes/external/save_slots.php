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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/booking/lib.php');

use DateTime;
use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_slots extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        // Userid is always current user, so no need to get it from client.
        return new external_function_parameters(
            array('slots' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'starttime' => new external_value(PARAM_INT, 'slot start time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                        'endtime' => new external_value(PARAM_INT, 'slot end time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                    ), 'slot')
                ),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'year' => new external_value(PARAM_INT, 'The slot year', VALUE_DEFAULT),
                'week' => new external_value(PARAM_INT, 'The slot week', VALUE_DEFAULT)
            )
        );
    }

    /**
     * Save availability slot.
     *
     * @param array $slots A list of slots to create.
     * @param int $courseid the course id associated with the slot.
     * @param int $year the year in which the slot occur.
     * @param int $week the week in which the slot occur.
     * @return array operation result.
     */
    public static function execute($slots, $courseid, $year, $week) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'slots'     => $slots,
                'courseid'  => $courseid,
                'year'      => $year,
                'week'      => $week)
            );

        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

        $warnings = [];
        $student = $subscriber->get_student($USER->id);

        // add new slots after removing previous ones for the week
        $slots = [];
        $currentweek = (new DateTime())->format("W");
        if ($week >= $currentweek) {
            $slots = $student->save_slots($params);
        }

        // activate posting notification
        $existingslots = get_user_preferences('local_booking_' . $courseid . '_postingnotify', '', $student->get_id());
        $slotstonotify = $existingslots . (empty($existingslots) ? '' : ',') . $slots;
        set_user_preference('local_booking_' . $courseid . '_postingnotify', $slotstonotify, $student->get_id());

        if (!empty($slots)) {
            \core\notification::SUCCESS(get_string('slotssavesuccess', 'local_booking'));
        } else {
            $warnings[] = [
                'warningcode' => 'warnsomeslotsnotsaved',
                'message' => get_string('warnsomeslotsnotsaved', 'local_booking')
            ];
            \core\notification::ERROR(get_string('slotssaveunable', 'local_booking'));
        }

        return array(
            'result' => !empty($slots),
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }
}
