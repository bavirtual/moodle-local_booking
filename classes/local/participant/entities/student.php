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
 * Class representing all student course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\entities;

use local_booking\local\session\entities\priority;
use local_booking\local\session\entities\booking;
use local_booking\local\session\entities\grade;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\slot\entities\slot;
use local_booking\local\subscriber\entities\subscriber;

class student extends participant {

    /**
     * Process user enrollments table name.
     */
    const SLOT_COLOR = '#00e676';

    /**
     * @var array $exercises The student exercise grades.
     */
    protected $exercises = [];

    /**
     * @var array $quizes The student quize grades.
     */
    protected $quizes = [];

    /**
     * @var array $grades The student exercise/quize grades.
     */
    protected $grades = [];

    /**
     * @var array $slots The student posted timeslots.
     */
    protected $slots;

    /**
     * @var string $slotcolor The slot color for the student slots.
     */
    protected $slotcolor;

    /**
     * @var int $total_posts The student's total number of availability posted.
     */
    protected $total_posts;

    /**
     * @var int $nextlesson The student's next upcoming lesson.
     */
    protected $nextlesson;

    /**
     * @var array $nextexercise The student's next exercise and section.
     */
    protected $nextexercise;

    /**
     * @var array $currentexercise The student's current exercise and section.
     */
    protected $currentexercise;

    /**
     * @var booking $activebooking The currently active booking for the student.
     */
    protected $activebooking;

    /**
     * @var DateTime $restrictiondate The student's end of restriction period date.
     */
    protected $restrictiondate;

    /**
     * @var priority $priority The student's priority object.
     */
    protected $priority;

    /**
     * @var boolean $lessonsecomplete Whether the student completed all pending lessons.
     */
    protected $lessonsecomplete;

    /**
     * @var bool $qualified Whether the student has been passed the Qualifying Cross-country or other qualifying exercise.
     */
    protected $qualified;

    /**
     * @var bool $tested Whether the student passed skills test exam.
     */
    protected $tested;

    /**
     * @var bool $evaluated Whether the student was evaluated for the final skill test examination for the course.
     */
    protected $evaluated;

    /**
     * Constructor.
     *
     * @param subscriber $course The subscribing course the student is enrolled in.
     * @param int $studentid     The student id.
     */
    public function __construct(subscriber $course, int $studentid, string $studentname = '', int $enroldate = 0) {
        parent::__construct($course, $studentid);
        $this->username = $studentname;
        $this->enroldate = $enroldate;
        $this->slotcolor = self::SLOT_COLOR;
        $this->is_student = true;
    }

    /**
     * Save a student list of slots
     *
     * @param array $params The year, and week.
     * @return bool $result The result of the save transaction.
     */
    public function save_slots(array $params) {
        global $DB;

        $slotid = 0;
        $slotids = '';
        $slots = $params['slots'];
        $year = $params['year'];
        $week = $params['week'];

        // start transaction
        $transaction = $DB->start_delegated_transaction();

        // remove all week/year slots for the user to avoid updates
        $result = slot_vault::delete_slots($this->course->get_id(), $year, $week, $this->userid);

        if ($result) {
            foreach ($slots as $slot) {
                $newslot = new slot(0,
                    $this->userid,
                    $this->course->get_id(),
                    $slot['starttime'],
                    $slot['endtime'],
                    $year,
                    $week,
                );

                // add each slot and record the slot id
                $slotid = slot_vault::save_slot($newslot);
                $slotids .= (!empty($slotids) ? ',' : '') . $slotid;
                $result = $result && $slotid != 0;
            }
        }

        if ($result) {
            $transaction->allow_commit();
        } else {
            $transaction->rollback(new \moodle_exception(get_string('slotssaveunable', 'local_booking')));
        }

        return $slotids;
    }

    /**
     * Delete student slots
     *
     * @param array $params The year, and week.
     * @return bool $result The result of the save transaction.
     */
    public function delete_slots(array $params) {
        global $DB;

        $year = $params['year'];
        $week = $params['week'];

        // start transaction
        $transaction = $DB->start_delegated_transaction();

        // remove all week/year slots for the user to avoid updates
        $result = slot_vault::delete_slots($this->course->get_id(), $this->userid, $year, $week);
        if ($result) {
            $transaction->allow_commit();
        } else {
            $transaction->rollback(new \moodle_exception(get_string('slotsdeleteunable', 'local_booking')));
        }

        return $result;
    }

    /**
     * Get a list of the student exercise grades objects.
     *
     * @return {object}[]   An array of the student exercise grade objects.
     */
    public function get_exercise_grades() {

        if (empty($this->exercises)) {
            $this->exercises = $this->get_mod_grades('assign');
        }

        return $this->exercises;
    }

    /**
     * Get records of a specific quiz for a student.
     *
     * @return {object}[]   The exam grade objects.
     */
    public function get_quize_grades() {

        if (empty($this->quizes)) {
            $this->quizes = $this->get_mod_grades('quiz');
        }

        return $this->quizes;
    }

    /**
     * Get grades for a specific student from
     * assignments and quizes.
     *
     * @return {object}[] The student grades.
     */
    public function get_grades() {

        if (empty($this->grades)) {
            // join both graded assignments and attempted quizes into one grades array
            $this->grades = $this->get_exercise_grades() + $this->get_quize_grades();
        }

        return $this->grades;
    }

    /**
     * Get student grade for a specific exercise.
     *
     * @param int  $coursemodid  The exercise id associated with the grade
     * @return grade The student exercise grade.
     */
    public function get_grade(int $coursemodid) {

        // get the grade if already exists otherwise create a new one making sure it's not empty
        if (array_key_exists($coursemodid, $this->grades)) {

            $grade = $this->grades[$coursemodid];

        } else {

            // fetch grade_grade then ensure it is graded!
            $grade = new grade($this->course->get_grading_items()[$coursemodid]->id, $this->userid, $coursemodid);

            if (empty($grade->finalgrade)) {
                $grade = null;
            }
        }

        return $grade;
    }

    /**
     * Get a list of the module grades.
     *
     * @param string $modetype The module type for the course modules.
     * @return array           An array of the student exercise grade objects.
     */
    protected function get_mod_grades(string $modtype) {

        $grades = [];
        // get all exercise grades (assignments)
        $coursemods = $this->course->get_modules();
        foreach ($coursemods as $coursemod) {
            if ($coursemod->modname == $modtype) {
                // add scored grade (has finalgrade)
                if ($grade = $this->get_grade($coursemod->id))
                    $grades[$coursemod->id] = $grade;
            }
        }

        return $grades;
    }

    /**
     * Get student grade for the current exercise.
     *
     * @return grade The student exercise grade.
     */
    public function get_current_grade() {
        return $this->get_grade($this->get_current_exercise());;
    }

    /**
     * Return student slots for a particular week/year.
     *
     * @return object slot
     */
    public function get_slot(int $slotid) {

        if (empty($this->slots[$slotid]))
            $this->slots[$slotid] = slot_vault::get_slot($slotid);

        return $this->slots[$slotid];
    }

    /**
     * Return student slots for a particular week/year.
     *
     * @return array array of days
     */
    public function get_slots($weekno, $year) {

        if (empty($this->slots)) {
            $this->slots = slot_vault::get_slots($this->userid, $weekno, $year);

            // add student's slot color to each slot
            foreach ($this->slots as $slot) {
                $slot->slotcolor = $this->slotcolor;
            }
        }

        return $this->slots;
    }

    /**
     * Get student slots color.
     *
     * @return string $slotcolor;
     */
    public function get_slotcolor() {
        return $this->slotcolor;
    }

    /**
     * Return student's next lesson.
     *
     * @return string $nextlesson
     */
    public function get_next_lesson() {
        return $this->nextlesson;
    }

    /**
     * Returns the timestamp of the first
     * nonbooked availability slot for
     * the student.
     *
     * @return  DateTime
     */
    public function get_first_slot_date() {
        $firstsession = slot_vault::get_first_posted_slot($this->userid);
        $sessiondatets = !empty($firstsession) ? $firstsession->starttime : time();
        $sessiondate = new \DateTime('@' . $sessiondatets);

        return $sessiondate;
    }

    /**
     * Returns the timestamp of the next
     * allowed session date for the student.
     *
     * @return  DateTime
     */
    public function get_next_allowed_session_date() {

        if (empty($this->restrictiondate)) {
            // get wait time restriction waiver if exists
            $hasrestrictionwaiver = (bool) get_user_preferences('local_booking_' . $this->course->get_id() . '_availabilityoverride', false, $this->userid);
            $nextsessiondate = new \DateTime('@' . time());

            // process restriction if posting wait restriction is enabled or if the student doesn't have a waiver
            if ($this->course->postingwait > 0 && !$hasrestrictionwaiver) {

                $lastsession = $this->get_last_booking();

                // fallback to last graded then enrollment date
                if (!empty($lastsession)) {
                    $nextsessiondate = new \DateTime('@' . $lastsession);
                } else {
                    $lastgraded = $this->get_last_graded_date();
                    if (!empty($lastgraded))
                        $nextsessiondate = $lastgraded;
                    else
                        $nextsessiondate = $this->get_enrol_date();
                }

                // add posting wait period to last session
                date_add($nextsessiondate, date_interval_create_from_date_string($this->course->postingwait . ' days'));

                // return today's date if the posting wait restriction date had passed
                if ($nextsessiondate->getTimestamp() < time())
                    $nextsessiondate = new \DateTime('@' . time());
            }

            // rest the hours to start of the day
            $nextsessiondate->settime(0,0);
            $this->restrictiondate = $nextsessiondate;
        }

        return $this->restrictiondate;
    }

    /**
     * Returns the student's currently active booking.
     *
     * @return booking
     */
    public function get_active_booking() {

        if (empty($this->activebooking)) {
            $booking = new booking(0, $this->course->get_id(), $this->userid);
            if ($booking->load())
                $this->activebooking = $booking;
        }

        return $this->activebooking;
    }

    /**
     * Returns the current exercise id for the student.
     *
     * @param bool $next  Whether to get the next exercise or current, default is next exercise
     * @return int The current exercise id
     */
    public function get_current_exercise() {

        if (empty($this->currentexercise)) {

            // get last graded exercise
            $this->currentexercise = array_key_last($this->get_exercise_grades());

            // check for newly enrolled student (boundry condition)
            if (empty($this->currentexercise) )
                $this->currentexercise = array_values($this->course->get_modules())[0]->id;
        }

        return $this->currentexercise;
    }

    /**
     * Returns the next exercise id for the student.
     *
     * @return int The next exercise id
     */
    public function get_next_exercise() {

        if (empty($this->nextexercise)) {

            // get booking if exists otherwise pick the next exercise
            if ($booking = $this->get_active_booking()) {

                $this->nextexercise = $booking->get_exerciseid();

            } else {

                // check for newly enrolled students (boundry condition)
                if (empty($this->get_exercise_grades())) {
                    return $this->get_current_exercise();
                }

                // get the current student's course modules then move to the next exercise
                // filter out none 'assign' modules
                $coursemodules = array_filter($this->course->get_modules(), function($mod) {
                    return $mod->modname == 'assign';
                });

                $modids = array_keys($coursemodules);
                $nextid = array_search($this->get_current_exercise(), $modids) + 1;

                // check for graduated student (boundry condition)
                if (array_key_exists($nextid, $modids)) {
                    $this->nextexercise = ($nextmod = $coursemodules[$modids[$nextid]]) ? $nextmod->id : 0;
                } else {
                    $this->nextexercise = 0;
                }
            }
        }

        return $this->nextexercise;
    }

    /**
     * Returns the student's priority object.
     *
     * @return priority The student's priority object
     */
    public function get_priority() {

        if (empty($this->priority)) {
            $this->priority = new priority($this->course->get_id(), $this->userid);
        }

        return $this->priority;
    }

    /**
     * Returns the total number of active posts.
     *
     * @return int The number of active posts
     */
    public function get_total_posts() {

        if (empty($this->total_posts))
            $this->total_posts = slot_vault::get_slot_count($this->course->get_id(), $this->userid);

        return $this->total_posts;
    }

    /**
     * Get the last grade the student received
     * assignments and quizes.
     *
     * @return grade The student last grade.
     */
    public function get_last_grade() {
        $grade = end($this->exercises);
        return $grade;
    }

    /**
     * Get the date timestamp of the last booked slot
     *
     */
    public function get_last_booking() {
        return slot::get_last_booking($this->course->get_id(), $this->userid);
    }

    /**
     * Set the student's slot color.
     *
     * @param string $slotcolor
     */
    public function set_slot_color(string $slotcolor) {
        $this->slotcolor = $slotcolor;
    }

    /**
     * Set the student's next lesson.
     *
     * @param string $nextlesson
     */
    public function set_next_lesson(string $nextlesson) {
        $this->nextlesson = $nextlesson;
    }

    /**
     * Returns whether the student completed
     * all lessons prior to the upcoming next
     * exercise.
     *
     * @return  bool    Whether the lessones were completed or not.
     */
    public function has_completed_lessons() {

        if (!isset($this->lessonsecomplete)) {

            // check if the student is not graduating
            if (!$this->has_completed_coursework()) {

                // exercise associated with completed lessons depends on whether the student passed the current exercise
                $grade = $this->get_grade($this->get_current_exercise());
                if (!empty($grade))
                    // check if passed exercise or received a progressing or objective not met grade
                    $exerciseid = $grade->is_passed() ? $this->get_next_exercise() : $this->get_current_exercise();
                else
                    $exerciseid = $this->get_current_exercise();

                // get lessons complete
                $this->lessonsecomplete = $this->vault->is_student_lessons_complete($this->userid, $this->course->get_id(), $exerciseid);

            } else {
                $this->lessonsecomplete = true;
            }
        }

        return $this->lessonsecomplete;
    }

    /**
     * Returns whether the student completed
     * all course work including skill test.
     *
     * @return  bool    Whether the course work has been completed.
     */
    public function has_completed_coursework() {
        return $this->tested();
    }

    /**
     * Returns whether the student is on hold or not.
     *
     * @return  bool    Whether the student is on hold.
     */
    public function is_onhold() {
        return self::is_member_of($this->course->get_id(), $this->userid, LOCAL_BOOKING_ONHOLDGROUP);
    }

    /**
     * Returns whether the student is in 'Keep Active' status.
     *
     * @return  bool    Whether the student is in 'Keep Active' status.
     */
    public function is_kept_active() {
        return self::is_member_of($this->course->get_id(), $this->userid, LOCAL_BOOKING_KEEPACTIVEGROUP);
    }

    /**
     * Returns whether the student has been passed the Qualifying
     * Cross-country or other qualifying exercise.  Checking assumes
     * the qualifying exercise is the one prior to the skill test exercise.
     *
     * @return  bool    Whether the student is qualified.
     */
    public function qualified() {

        if (!isset($this->qualified)) {

            // set qualification negative
            $this->qualified = false;

            // get qualification exercise id
            $modkeys = array_keys($this->course->get_modules());
            $qualifyexerciseidx = array_search($this->course->get_graduation_exercise(), $modkeys);
            $qualifyexerciseid = $modkeys[$qualifyexerciseidx - 1];

            // check grade for the qualifying exercise
            $grade = $this->get_grade($qualifyexerciseid);
            if (!empty($grade)) {
                $this->qualified = $grade->is_passed();
            }
        }

        return $this->qualified;

    }

    /**
     * Returns whether the student has been passed
     * skills test or final exam.
     *
     * @return  bool    Whether the student has been evaluated.
     */
    public function tested() {

        if (!isset($this->tested)) {

            // set tested negative
            $this->tested = false;

            // check grade for the qualifying exercise
            $grade = $this->get_grade($this->course->get_graduation_exercise());
            if (!empty($grade)) {
                $this->tested = $grade->is_passed();
            }
        }

        return $this->tested;
    }

    /**
     * Returns whether the student has been evaluated
     * where by the evaluation form is uploaded to
     * the feedback file submission of the skill test exercise.
     *
     * @return  bool    Whether the student has been evaluated.
     */
    public function evaluated() {

        // check if the course requires evaluation first
        if (!$this->course->has_skills_evaluation()) {
            return false;
        }

        // the student is considered evaluated if the student has a skill test exam feedback evaluation file
        if (!isset($this->evaluated)) {

            $finalgrade = $this->get_grade($this->course->get_graduation_exercise());
            if (!empty($finalgrade)) {
                $this->evaluated = !empty($finalgrade->get_feedback_file('assignfeedback_file', 'feedback_files'));
            } else {
                $this->evaluated = false;
            }
        }

        return $this->evaluated;
    }

    /**
     * Returns whether the student has graduated
     * and in the graduates group.
     *
     * @return  bool    Whether the student had graduated.
     */
    public function graduated() {
        return self::is_member_of($this->course->get_id(), $this->userid, LOCAL_BOOKING_GRADUATESGROUP);
    }
}