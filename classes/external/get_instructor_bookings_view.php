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
use core_external\external_function_parameters;
use core_external\external_single_structure;
use local_booking\exporters\dashboard_mybookings_exporter;
use local_booking\output\views\booking_view;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_instructor_bookings_view extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve instructor's My bookings view.
     *
     * @param int $courseid  The course id for context.
     * @return \stdClass|null Instructor bookings object array.
     */
    public static function execute(int $courseid) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'courseid' => $courseid,
                )
            );

        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

        $bookingview = new booking_view(
            ['instructor' => $subscriber->get_instructor($USER->id)],
            ['subscriber'=>$subscriber, 'context'=>$subscriber->get_context()]
        );
        $instuctorbookings = $bookingview->get_instructor_bookings(false);

        return $instuctorbookings;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function execute_returns() {
        return dashboard_mybookings_exporter::get_read_structure();
    }
}
