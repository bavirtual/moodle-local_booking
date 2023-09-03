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
 * Class for displaying student time slots for the week calendar view.
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
use DateTime;
use local_booking\local\session\entities\action;
use local_booking\local\participant\entities\student;

/**
 * Class for displaying each student row in progression view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_student_exporter extends exporter {

    /**
     * @var student $student The student.
     */
    protected $student;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        global $CFG;

        $this->student = $data['student'];
        $data['studentid'] = $this->student->get_id();
        $data['studentname'] = $this->student->get_name();
        $data['fleet'] = $this->student->get_fleet();
        $data['simulator'] = $this->student->get_simulator();
        $data['profileurl'] = $CFG->wwwroot . '/local/booking/profile.php?courseid=' . $related['course']->get_id() . '&userid=' . $this->student->get_id();

        // get recency or relavent information dates depending on the filter view
        switch ($related['filter']) {
            case 'active':
            case 'onhold':
                $data['dayssincelast'] = $this->student->get_priority()->get_recency_days();
                $data['recencytooltip'] = $this->student->get_priority()->get_recency_info();
                break;
            case 'graduates':
                $data['dateinfo'] = (new \DateTime('@'.$this->student->timeadded))->format('M d, y');
                break;
            case 'suspended':
                $data['dateinfo'] = $this->student->get_suspension_date()->format('M d, y');
                break;
            }

        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'tag' => [
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
            ],
            'sequence' => [
                'type' => PARAM_INT,
            ],
            'sequencetooltip' => [
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
            ],
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'studentname' => [
                'type' => PARAM_RAW,
            ],
            'dayssincelast' => [
                'type' => PARAM_INT,
                'optional' => true,
                'default' => 0,
            ],
            'dateinfo' => [
                'type' => \PARAM_RAW,
                'optional' => true,
                'default' => '',
            ],
            'recencytooltip' => [
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
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
            'fleet' => [
                'type' => PARAM_RAW,
            ],
            'profileurl' => [
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
                'type' => \PARAM_RAW,
                'optional' => true,
                'default' => '',
            ],
            'actiontype' => [
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
            ],
            'actionname' => [
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
            ],
            'actionbook' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => false,
            ],
            'actionenabled' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => false,
            ],
            'actiontooltip' => [
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
            ],
            'sessionoptions' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'multiple' => true,
            ],
            'posts' => [
                'type' => PARAM_INT,
                'optional' => true,
                'default' => 0,
            ],
            'week' => [
                'type' => PARAM_INT,
                'optional' => true,
                'default' => 0,
            ],
            'formaction' => [
                'type' => PARAM_RAW,
                'optional' => true,
                'default' => '',
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
        global $CFG;

        $sessions = $this->get_sessions($output, $this->student, $this->related);
        $return = [
            'sessions'       => $sessions,
        ];

        if ($this->related['filter'] == 'active') {

        // action is grading if the student has any active booking, completed coursework
        // awaiting certification, or graduated already; otherwise it is a booking action
        if (!empty($this->student->get_active_booking()))
            $actiontype = 'grade';
        else if ($this->student->has_completed_coursework() && !$this->student->graduated())
            $actiontype = 'graduate';
        else
            $actiontype = 'book';

            $action = new action($this->related['course'], $this->student, $actiontype);
            $posts = $this->data['view'] == 'confirm' ? $this->student->get_total_posts() : 0;

            $return = array_merge(array(
                'actionurl'         => $action->get_url()->out(false),
                'actiontype'        => $action->get_type(),
                'actionname'        => $action->get_name(),
                'actionbook'        => $action->get_name() == 'Book',
                'actionenabled'     => $action->is_enabled(),
                'actiontooltip'     => $action->get_tooltip(),
                'sessionoptions'    => $this->get_session_options($action),
                'posts'             => $posts,
                'week'              => $this->get_booking_week(),
                'formaction'        => $CFG->httpswwwroot . '/local/booking/availability.php',
            ), $return);
        }

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
            'coursemodules'=>'cm_info[]?',
            'course'=>'local_booking\local\subscriber\entities\subscriber',
            'filter'=>'string',
        );
    }

    /**
     * Get the list of conducted sessions for the student.
     *
     * @param   $output  The output to be rendered
     * @return  $sessions[]
     */
    public static function get_sessions($output, student $student, $related) {
        $sessions = [];

        // get details for active and on-hold students only
        $grades = $student->get_grades();
        $bookings = $student->get_bookings();
        $logbook = $student->get_logbook();

        $studentname = $student->get_name();
        $gradexercise = $related['course']->get_graduation_exercise();

        // export all exercise sessions, quizes, and exams
        $coursemods = $related['coursemodules'];
        foreach ($coursemods as $coursemod) {
            $studentinfo = [];
            $studentinfo = [
                'student'     => $student,
                'studentname' => $studentname,
                'exerciseid'  => $coursemod->id,
                'flighttype'  => $gradexercise != $coursemod->id ? 'training' : 'check',
                'grades'      => $grades,
                'bookings'    => $bookings,
                'logbook'     => $logbook,
            ];
            $coursemodsession = new booking_session_exporter($studentinfo, $related);
            $sessions[] = $coursemodsession->export($output);
        }

        return $sessions;
    }

    /**
     * Returns an array of option default selected values
     * for session confirmation view.
     *
     * @param {object} $action  The next action for the student
     * @return {object} $sessionoptions
     */
    protected function get_session_options($action) {
        global $COURSE;

        $sessionoptions = [];
        $grades = $this->student->get_grades();

        if ($this->data['view'] == 'confirm') {

            $coursemods = $this->related['coursemodules'];
            foreach ($coursemods as $coursemod) {

                // check for assignment exercises
                if ($coursemod->modname == 'assign') {

                    // show the graduation exercise booking option for examiners only
                    $gradexercise = $COURSE->subscriber->get_graduation_exercise();
                    $examiner = false;
                    if (!empty($this->data['instructor']))
                        $examiner = $this->data['instructor']->is_examiner();

                    if (($coursemod->id ==  $gradexercise && $examiner) || $coursemod->id != $gradexercise) {
                        $sessionoptions[] = [
                            'nextsession' => ($action->get_exerciseid() == $coursemod->id ? "checked" : ""),
                            'bordered' => $action->get_exerciseid() == $coursemod->id,
                            'graded'  => isset($grades[$coursemod->id]),
                            'exerciseid'  => $coursemod->id
                        ];
                    }
                }
            }
        }

        return $sessionoptions;
    }

    /**
     * Returns a timestamp of the first day of the week
     * to show the student's availability view during
     * booking confirmation process.
     *
     * @return int $week
     */
    protected function get_booking_week() {
        $week = 0;

        if ($this->data['view'] == 'confirm') {
            $nextslotdate = ($this->student->get_first_slot_date())->getTimestamp();
            $waitenddate = ($this->student->get_next_allowed_session_date())->getTimestamp();
            $week = $nextslotdate > time() ? $nextslotdate : $waitenddate;
        }

        return $week;
    }
}
