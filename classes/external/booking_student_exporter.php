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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

require_once($CFG->dirroot . '/lib/completionlib.php');

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use core\external\exporter;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\session\entities\action;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;

/**
 * Class for displaying each student row in progression view.
 *
 * @package   local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_student_exporter extends exporter {

    /**
     * @var int $course An id of the current course.
     */
    protected $courseid;

    /**
     * @var student $student The student.
     */
    protected $student;

    /**
     * @var array $courseexercises An array of the course exercises.
     */
    protected $courseexercises;

    /**
     * @var array $studentgrades An array of the student's exercise grades.
     */
    protected $studentgrades;

    /**
     * @var booking $booking A vault to access booking data.
     */
    protected $booking;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student data.
     * @param array $related Related objects.
     */
    public function __construct($data, $courseid, $related) {
        $this->courseid = $courseid;
        $this->student = new student($courseid, $data['studentid']);
        $this->courseexercises = $related['courseexercises'];
        $this->booking = new booking(0, $courseid, $data['studentid']);
        $this->booking->load();

        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'sequence' => [
                'type' => PARAM_INT,
            ],
            'sequencetooltip' => [
                'type' => PARAM_RAW,
            ],
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'studentname' => [
                'type' => PARAM_RAW,
            ],
            'dayssincelast' => [
                'type' => PARAM_INT,
            ],
            'overduewarning' => [
                'type' => PARAM_BOOL,
            ],
            'latewarning' => [
                'type' => PARAM_BOOL,
            ],
            'simulator' => [
                'type' => PARAM_RAW,
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
            'sessions' => [
                'type' => booking_session_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'actionurl' => [
                'type' => PARAM_URL,
            ],
            'actiontype' => [
                'type' => PARAM_RAW,
            ],
            'actionname' => [
                'type' => PARAM_RAW,
            ],
            'actionbook' => [
                'type' => PARAM_BOOL,
            ],
            'lessonincomplete' => [
                'type' => PARAM_BOOL,
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        $hasincompletelessons = false;
        $sessions = $this->get_sessions($output);
        $action = $this->get_next_action();

        // check if the student to be book has incomplete lessons
        if ($action->get_type() == 'book') {
            $hasincompletelessons = !$this->student->completed_lessons();
            if ($hasincompletelessons) { $action->set_type('disabled'); }
        }

        $return = [
            'sessions'         => $sessions,
            'actionurl'        => $action->get_url()->out(false),
            'actiontype'       => $action->get_type(),
            'actionname'       => $action->get_name(),
            'actionbook'       => $action->get_name() == 'Book',
            'lessonincomplete' => $hasincompletelessons,
        ];

        return $return;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context'=>'context',
            'courseexercises'=>'stdClass[]?',
        );
    }

    /**
     * Get the list of sessions for the course.
     *
     * @return  $sessions[]
     */
    protected function get_sessions($output) {
        // get student grades
        $this->studentgrades = $this->student->get_grades();

        // get student log book
        $logbook = new logbook($this->courseid, $this->student->get_id());
        $logbook->load();

        // export all exercise sessions, quizes, and exams
        foreach ($this->courseexercises as $exercise) {
            $studentinfo = [];
            $studentinfo = [
                'studentid'   => $this->student->get_id(),
                'studentname' => $this->data['studentname'],
                'courseid'    => $this->courseid,
                'exerciseid'  => $exercise->exerciseid,
                'grades'      => $this->studentgrades,
                'logentry'    => $logbook->get_logentry(0, $exercise->exerciseid, false),
                'booking'     => $this->booking,
            ];
            $exercisesession = new booking_session_exporter($studentinfo, $this->related);
            $sessions[] = $exercisesession->export($output);
        }

        return $sessions;
    }

    /**
     * Retrieves the action object containing
     * action name and url. Next action for
     * students with booked session is a grading
     * action, otherwise it is a booking action
     *
     * @param  bool  $disable create a disabled action
     * @return {Object}
     */
    protected function get_next_action() {
        // next action depends if the student has any booking
        $actiontype = !empty($this->booking->get_id()) ? 'grade' : 'book';
        if ($actiontype == 'book') {
            list($nextexerciseid, $section) = $this->student->get_next_exercise();
        } else {
            $nextexerciseid = $this->booking->get_exerciseid();
        }
        $action = new action($actiontype, $this->courseid, $this->student->get_id(), $nextexerciseid);

        return $action;
    }
}
