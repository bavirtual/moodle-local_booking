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
use local_booking\local\session\entities\action;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_mybookings_exporter extends exporter {

    // data payload
    protected $data;

    // related objects
    protected $related;

    // instructor bookings
    protected $mybookings;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        global $USER;

        $subscriber = $related['subscriber'];
        $data['contextid'] = $subscriber->get_context()->id;
        $data['courseid'] = $subscriber->get_id();

        // get intructor bookings
        $instructor = $subscriber->get_instructor($USER->id);
        $this->mybookings = $instructor->get_bookings(false, true, true);

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'contextid' => [
                'type' => PARAM_INT
            ],
            'courseid' => [
                'type' => PARAM_INT,
            ],
        ];
     }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'activebookings' => [
                'type' => dashboard_booking_exporter::read_properties_definition(),
                'multiple' => true,
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(\renderer_base $output) {

        $course = $this->related['subscriber'];
        $instructorbookings = [];

        foreach ($this->mybookings as $booking) {
            $student = $course->get_student($booking->get_studentid());
            $action = new action($course, $student, 'cancel', $booking->get_exerciseid());
            $slot = $booking->get_slot();
            $starttime = new DateTime('@' . $slot->get_starttime());
            // TODO: end time should include the last hour
            $endtime = new DateTime(('@' . ($slot->get_endtime()) + (60 * 60)));

            $data = [
            'bookingid'     => $booking->get_id(),
            'studentid'     => $booking->get_studentid(),
            'studentname'   => $student->get_name(),
            'exerciseid'    => $booking->get_exerciseid(),
            'noshows'       => count($student->get_noshow_bookings()),
            'exercise'      => $course->get_exercise_name($booking->get_exerciseid(), $booking->get_courseid()),
            'sessiondate'   => $starttime->format('D M j'),
            'starttime'     => $starttime->format('H:i \z\u\l\u'),
            'endtime'       => $endtime->format('H:i \z\u\l\u'),
            'actionname'    => $action->get_name(),
            'actionurl'     => $action->get_url()->out(false),
            'coursename'    => $course->get_course($booking->get_courseid())->shortname,
            ];

            $instructorbookingexporter = new dashboard_booking_exporter($data);
            $instructorbookings[] = $instructorbookingexporter->export($output);
        }

        return ['activebookings' => $instructorbookings];
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
            'subscriber' => 'local_booking\local\subscriber\entities\subscriber',
        );
    }
}
