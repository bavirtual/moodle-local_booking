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
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_booking\exporters\dashboard_bookings_exporter;
use local_booking\output\views\booking_view;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_bookings_view extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
                'filter'  => new external_value(PARAM_RAW, 'The results filter', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve students booking progression view.
     *
     * @param int $courseid  The course id for context.
     * @param int $userid    The user id for single user selection.
     * @param string $filter The filter to show students, inactive (including graduates), suspended, and default to active.
     * @return \stdClass|null Student bookings object array.
     */
    public static function execute(int $courseid, int $userid, string $filter) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'courseid' => $courseid,
                'userid' => $userid,
                'filter' => $filter,
                )
            );

        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

        // data required get data
        $data = [
            'instructor' => $subscriber->get_instructor($USER->id),
            'studentid'  => $params['userid'],
            'action'     => 'book',
            'view'       => 'sessions',
            'sorttype'   => '',
            'filter'     => $filter,
            'page'       => 0,
        ];

        $bookingview = new booking_view($data, ['subscriber'=>$subscriber, 'context'=>$subscriber->get_context()]);
        $bookings = $bookingview->get_student_progression(false);

        return $bookings;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function execute_returns() {
        return dashboard_bookings_exporter::get_read_structure();
    }
}
