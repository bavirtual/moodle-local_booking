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
use local_booking\exporters\availability_week_exporter;
use local_booking\output\views\calendar_view;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_weekly_view extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'year' => new external_value(PARAM_INT, 'Year to be viewed', VALUE_REQUIRED),
                'week' => new external_value(PARAM_INT, 'Week to be viewed', VALUE_REQUIRED),
                'time' => new external_value(PARAM_INT, 'Timestamp of the first day of the week to be viewed', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course being viewed', VALUE_DEFAULT, SITEID, NULL_ALLOWED),
                'categoryid' => new external_value(PARAM_INT, 'Category being viewed', VALUE_DEFAULT, null, NULL_ALLOWED),
                'action' => new external_value(PARAM_RAW, 'The action being performed view or book', VALUE_DEFAULT, 'view', NULL_ALLOWED),
                'view' => new external_value(PARAM_RAW, 'The action being performed view or book', VALUE_DEFAULT, 'view', NULL_ALLOWED),
                'studentid' => new external_value(PARAM_INT, 'The user id the slots belongs to', VALUE_DEFAULT, 0, NULL_ALLOWED),
                'exerciseid' => new external_value(PARAM_INT, 'The exercise id the slots belongs to', VALUE_DEFAULT, 0, NULL_ALLOWED),
            ]
        );
    }

    /**
     * Get data for the weekly calendar view.
     *
     * @param   int     $year       The year to be shown
     * @param   int     $week       The week to be shown
     * @param   int     $time       The timestamp of the first day in the week to be shown
     * @param   int     $courseid   The course to be included
     * @param   int     $categoryid The category to be included
     * @param   string  $action     The action to be performed if in booking view
     * @param   string  $view       The view to be displayed if user or all
     * @param   int     $studentid  The student id the action is performed on
     * @param   int     $exercise   The exercise id the action is associated with
     * @return  \stdClass
     */
    public static function execute($year, $week, $time, $courseid, $categoryid, $action, $view, $userid, $exerciseid) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'year'      => $year,
            'week'      => $week,
            'time'      => $time,
            'courseid'  => $courseid,
            'categoryid'=> $categoryid,
            'action'    => $action,
            'view'      => $view,
            'studentid' => $userid,
            'exerciseid'=> $exerciseid,
        ]);

        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);
        require_login($params['courseid'], false);

        $calendar = \calendar_information::create($time, $params['courseid'], $params['categoryid']);

        $data = [
            'calendar'  => $calendar,
            'view'      => $view,
            'action'    => $action,
            'student'   => $subscriber->get_participant($userid),
            'exerciseid'=> $exerciseid == null ? 0 : $exerciseid,
        ];

        $calendarview = new calendar_view($data, ['subscriber'=>$subscriber, 'context'=>$subscriber->get_context()]);

        return $calendarview->get_exported_data();
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return availability_week_exporter::get_read_structure();
    }
}
