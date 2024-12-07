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

use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_booking\local\participant\entities\participant;
use local_booking\local\session\entities\booking;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class has_conflicting_booking extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'studentid'  => new external_value(PARAM_INT, 'The student id the booking is for', VALUE_DEFAULT),
                'bookedslot'  => new external_single_structure(
                        array(
                            'starttime' => new external_value(PARAM_INT, 'booked slot start time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'endtime' => new external_value(PARAM_INT, 'booked slot end time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'year' => new external_value(PARAM_INT, 'booked slot year', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'week' => new external_value(PARAM_INT, 'booked slot week', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                        ), 'booking'),
            )
        );
    }

    /**
     * Checks if the booking conflicts with another booking.
     *
     * @param {object} $bookedslot array containing booked slots.
     * @return array array of slots created.
     */
    public static function execute($studentid, $slottobook) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'studentid' => $studentid,
                'bookedslot' => $slottobook,
                )
            );

        $result = false;
        $warnings = array();
        $instructorid = $USER->id;

        $conflictingbooking = booking::conflicts($instructorid, $params['studentid'], $params['bookedslot']);

        if (!empty($conflictingbooking)) {

            // set the subscriber object
            $subscriber = get_course_subscriber_context('/local/booking/', $conflictingbooking->courseid);
            require_login($conflictingbooking->courseid, false);

            $result = true;
            $warninginfo = [
                'studentname'   => participant::get_fullname($conflictingbooking->studentid),
                'coursename'    => $subscriber->get_shortname(),
                'exercisename'  => $subscriber->get_exercise($conflictingbooking->exerciseid)->name,
                'date'          => (new \DateTime('@' . $conflictingbooking->starttime))->format('l M j \a\t H:i \z\u\l\u'),
            ];
            $warnings[] = [
                'warningcode' => 'errorconflictingbooking',
                'message' => get_string('errorconflictingbooking', 'local_booking', $warninginfo)
            ];
        }

        return array(
            'result' => $result,
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
