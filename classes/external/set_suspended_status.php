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
use local_booking\local\participant\entities\student;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_suspended_status extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(array(
                'status' => new external_value(PARAM_BOOL, 'The suspension status', VALUE_DEFAULT),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid' => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Suspend or unsuspend a user course enrolment.
     *
     * @param bool $status       The suspend status true/false.
     * @param int $courseid      The course id.
     * @param int $studentid     The student id.
     * @return array $result      The result of the availability override operation.
     */
    public static function execute($status, $courseid, $studentid) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
            'status' => $status,
            'courseid' => $courseid,
            'userid'  => $studentid,
            )
        );

        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $courseid);

        // suspend a student
        $student = new student($subscriber, $params['userid']);
        $result = $student->suspend($params['status']);

        return array(
            'result' => $result,
            'warnings' => array()
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
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
