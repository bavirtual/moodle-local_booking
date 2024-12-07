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

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_function_parameters;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_student_preferences extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(array(
                'preference' => new external_value(PARAM_RAW, 'The preference key', VALUE_DEFAULT),
                'value' => new external_value(PARAM_RAW, 'The value of the preference', VALUE_DEFAULT),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid' => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Set the user preferences
     *
     * @param string $preference The preference key of to be set.
     * @param string $value      The value of the preference to be set.
     * @param int $courseid      The course id.
     * @param int $userid        The user id.
     * @return array  The result of the availability override operation.
     */
    public static function execute(string $preference, string $value, int $courseid, int $userid) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
            'preference' => $preference,
            'value'      => $value,
            'courseid' => $courseid,
            'userid'     => $userid,
            )
        );

        $msgdata = ['preference' => $params['preference'], 'value' => $params['value']];
        $warnings = array();
        $result = set_user_preference("local_booking_$courseid" . "_$preference", $value, $params['userid']);

        if (!$result) {
            \core\notification::WARNING(get_string('bookingsetpreferencesunable', 'local_booking', $msgdata));
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
