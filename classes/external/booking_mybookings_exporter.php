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
 * Class for displaying instructor's active bookings in the 'My bookings' view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use DateTime;
use core\external\exporter;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\action;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_mybookings_exporter extends exporter {

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        global $COURSE;

        $booking = $data['booking'];
        $student = new student($COURSE->subscriber, $booking->get_studentid());
        $action = new action($COURSE->subscriber, $student, 'cancel', $booking->get_exerciseid());
        $slot = $booking->get_slot();
        $starttime = new DateTime('@' . $slot->get_starttime());
        // TODO: end time should include the last hour
        $endtime = new DateTime(('@' . ($slot->get_endtime()) + (60 * 60)));

        $data = [
        'bookingid'     => $booking->get_id(),
        'studentid'     => $booking->get_studentid(),
        'studentname'   => student::get_fullname($booking->get_studentid()),
        'exerciseid'    => $booking->get_exerciseid(),
        'noshows'       => count($student->get_noshow_bookings()),
        'exercise'      => $COURSE->subscriber->get_exercise_name($booking->get_exerciseid(), $booking->get_courseid()),
        'sessiondate'   => $starttime->format('D M j'),
        'starttime'     => $starttime->format('H:i \z\u\l\u'),
        'endtime'       => $endtime->format('H:i \z\u\l\u'),
        'actionname'    => $action->get_name(),
        'actionurl'     => $action->get_url()->out(false),
        'coursename'    => $COURSE->subscriber->get_course($booking->get_courseid())->shortname,
        ];

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'bookingid' => [
                'type' => PARAM_INT,
            ],
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'coursename' => [
                'type' => PARAM_RAW,
            ],
            'studentname' => [
                'type' => PARAM_RAW,
            ],
            'exerciseid' => [
                'type' => PARAM_INT,
            ],
            'noshows' => [
                'type' => PARAM_INT,
            ],
            'exercise' => [
                'type' => PARAM_RAW,
            ],
            'sessiondate' => [
                'type' => PARAM_RAW,
            ],
            'starttime' => [
                'type' => PARAM_RAW,
            ],
            'endtime' => [
                'type' => PARAM_RAW,
            ],
            'actionname' => [
                'type' => PARAM_RAW,
            ],
            'actionurl' => [
                'type' => PARAM_RAW,
            ],
        ];
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
        );
    }
}
