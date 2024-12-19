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

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_exercise_name extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id in context', VALUE_DEFAULT),
                'exerciseid'  => new external_value(PARAM_INT, 'The exercise id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve the name of a course exercise.
     *
     * @param int $courseid   The course id.
     * @param int $exerciseid The exerciser id.
     * @return array exercise name.
     */
    public static function execute($courseid, $exerciseid) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'courseid' => $courseid,
                'exerciseid' => $exerciseid,
                )
            );

        // set the subscriber object
        if ($exerciseid) {
            $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);
            $exercisename = $subscriber->get_exercise($params['exerciseid'], $params['courseid'])->name;
        } else {
            $exercisename = get_string('titlenewlogentry', 'local_booking');
        }


        return ['exercisename' => $exercisename, 'warnings' => array()];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function execute_returns() {
        return new external_single_structure(array(
            'exercisename' => new external_value(PARAM_RAW, 'The exercise name', VALUE_DEFAULT),
            'warnings' => new external_warnings()
            )
        );
    }
}
