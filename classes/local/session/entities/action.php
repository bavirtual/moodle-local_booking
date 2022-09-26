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

namespace local_booking\local\session\entities;

use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a course exercise session action.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action implements action_interface {

    /**
     * @var string $type The type of this action.
     */
    protected $type;

    /**
     * @var boolean $enabled The status of this action.
     */
    protected $enabled = true;

    /**
     * @var url $url The name of this action.
     */
    protected $url;

    /**
     * @var string $type The name of this action.
     */
    protected $name;

    /**
     * @var int $exerciseid The exerciseid id associated with the action.
     */
    protected $exerciseid;

    /**
     * @var string $tooltip The action's tooltip explaining its status.
     */
    protected $tooltip;

    /**
     * Constructor.
     *
     * @param subscriber $course     The subscribing course.
     * @param student    $student    The student behind the action.
     * @param string     $actiontype The type of action requested.
     * @param int        $refid      The reference exercise id if available.
     */
    public function __construct(subscriber $course, student $student, string $actiontype, int $refid = 0) {

        $enabled =  $student->is_active();
        $tooltip = '';
        $params = [];

        // get the next student action if this is not a cancel booking action
        switch ($actiontype) {

            // book action
            case 'book':

                // check if the session to book is the next exercise after passing the current session or the same
                $lastgrade = $student->get_last_grade();
                $getnextexercise = (!empty($lastgrade) ? $lastgrade->is_passinggrade() : true);
                $exerciseid = $getnextexercise ? $student->get_next_exercise() : $student->get_current_exercise();
                $tooltip = get_string('actionbooksession', 'local_booking');

                // get action enabled status by checking if there are more exercises to book and if instructor
                // is an examiner in the case of graduation skill tests

                // get action enabled status and tooltip
                if (!$student->has_completed_lessons()) {

                    $enabled = false;
                    $tooltip = get_string('actiondisabledincompletelessonstooltip', 'local_booking');

                // check if student completed all lessons (graduated)
                } else if ($student->graduated()) {

                    $enabled = false;
                    $tooltip = get_string('actiondisabledexercisescompletedtooltip', 'local_booking');

                // check if the user is an examiner when the student's next exercise is a final exam
                } else if ($student->get_next_exercise() == $course->get_graduation_exercise()) {
                    global $USER;

                    $instructor = new instructor($course, $USER->id);
                    $enabled = $instructor->is_examiner() && $student->is_active();
                    $tooltip = !$this->enabled ? get_string('actiondisabledexaminersonlytooltip', 'local_booking') : '';

                }

                // Book action takes the instructor to the week of the firs slot or after waiting period
                $actionurl = '/local/booking/view.php';
                $params = [
                    'exid'   => $exerciseid,
                    'action' => 'confirm',
                    'view'   => 'user',
                ];
                $name = get_string('book', 'local_booking');
                break;

            case 'grade':

                // get exercise to be graded
                $grade = $student->get_current_grade();
                if (!empty($grade)) {
                    $exerciseid = $grade->get_finalgrade() > 1 ? $student->get_next_exercise() : $student->get_current_exercise();
                } else {
                    $exerciseid = $student->get_current_exercise();
                }

                // set grading url
                $actionurl = '/local/booking/assign.php';
                $params = ['exeid' => $exerciseid];
                $name = get_string('grade', 'grades');
                $tooltip = get_string('actiongradesession', 'local_booking');

                // check if the exercise to be graded is the final skill test or assessments
                if ($student->has_completed_coursework() || $student->get_next_exercise() == $course->get_graduation_exercise()) {
                    global $USER;
                    $instructor = new instructor($course, $USER->id);
                    $enabled =  $instructor->is_examiner();
                    $tooltip = !$enabled ? get_string('actiondisabledexaminersonlytooltip', 'local_booking') : $tooltip;
                }
                break;

            case 'evaluate':
            case 'graduate':

                // evaluate or graduate the student's next grading action, which will be 'grade' for exercises
                // set url to the student's skill test form
                $exerciseid = 0;
                $actionurl = '/local/booking/certify.php';
                $name = get_string($actiontype, 'local_booking');
                $tooltip = get_string('action' . $actiontype . 'tooltip', 'local_booking', ['studentname'=>$student->get_name(false)]);

                // check if the certifer is the examiner
                if ($student->has_completed_coursework() || $student->get_current_exercise() == $course->get_graduation_exercise()) {
                    global $USER;
                    $examinerid = $student->get_grade($course->get_graduation_exercise())->get_graderid();
                    $enabled =  $examinerid == $USER->id;
                    $tooltip = !$enabled ? get_string('actiondisabledexaminersonlytooltip', 'local_booking') : $tooltip;
                }
                break;

            case 'cancel':

                // cancel action
                $exerciseid = $refid;
                $actiontype = 'cancel';
                $actionurl = '/local/booking/view.php';
                $name = get_string('bookingcancel', 'local_booking');
                $tooltip = get_string('actioncancelsession', 'local_booking');
                break;

        }

        $params += ['courseid' => $course->get_id(), 'userid' => $student->get_id()];
        $this->url = new moodle_url($actionurl, $params);
        $this->type = $actiontype;
        $this->name = $name;
        $this->exerciseid = $exerciseid;
        $this->enabled = $enabled;
        $this->tooltip = $tooltip;

    }

    /**
     * Get the type of the action.
     *
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get the URL of the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * Get the name of the action.
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get the exercise id of the action.
     *
     * @return int
     */
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    /**
     * Get the action's tooltip explaining its status.
     *
     * @return string
     */
    public function get_tooltip() {
        return $this->tooltip;
    }

    /**
     * Get the action's status.
     *
     * @return boolean the action's status
     */
    public function is_enabled() {
        return $this->enabled;
    }
}
