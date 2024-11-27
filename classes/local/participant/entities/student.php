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
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\entities;

require_once($CFG->dirroot . '/mod/assign/externallib.php');

use ArrayObject;
use DateTime;
use Exception;
use local_booking\local\subscriber\data_access\subscriber_vault;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\session\entities\priority;
use local_booking\local\session\entities\booking;
use local_booking\local\session\entities\grade;
use local_booking\local\slot\entities\slot;
use local_booking\local\subscriber\entities\subscriber;

class student extends participant {

    /**
     * Process user enrollments table name.
     */
    const SLOT_COLOR = '#00e676';

    /**
     * @var array $exercises The student graded exercise.
     */
    protected $exercises = [];

    /**
     * @var array $quizes The student graded quizes.
     */
    protected $quizes = [];

    /**
     * @var array $grades The student exercise/quiz grades.
     */
    protected $grades = [];

    /**
     * @var bool $gradesloaded Whether the grades were loaded or not.
     */
    protected $gradesloaded = false;

    /**
     * @var array $slots The student posted timeslots.
     */
    protected $slots;

    /**
     * @var string $slotcolor The slot color for the student slots.
     */
    protected $slotcolor;

    /**
     * @var string $progressionstatus The progression status
     */
    protected $progressionstatus = '';

    /**
     * @var int $total_posts The student's total number of availability posted.
     */
    protected $total_posts;

    /**
     * @var bool $lessonscomplete List of student's completed lessons.
     */
    protected $lessonscomplete;

    /**
     * @var int $nextlesson The student's next upcoming lesson id.
     */
    protected $nextlesson;

    /**
     * @var array $nextexerciseid The student's next exercise id.
     */
    protected $nextexerciseid;

    /**
     * @var int $currentexerciseid The student's current exercise id.
     */
    protected $currentexerciseid;

    /**
     * @var DateTime $restrictiondate The student's end of restriction period date.
     */
    protected $restrictiondate;

    /**
     * @var int $graduateddate The student's graduated date timestamp.
     */
    protected $graduateddate = 0;

    /**
     * @var priority $priority The student's priority object.
     */
    protected $priority;

    /**
     * @var int $waitdate The date from which the student was waiting since (enroled or last booked).
     */
    protected $waitdate;

    /**
     * @var int $recencydays The amount of days since the waiting date.
     */
    protected $recencydays;

    /**
     * @var array $incompletelessons the list of pending lessons.
     * @var array $incompletelessons the list of pending lessons.
     */
    protected $incompletelessons;
    protected $incompletelessons;

    /**
     * @var bool $qualified Whether the student has been passed the Qualifying Cross-country or other qualifying exercise.
     */
    protected $qualified;

    /**
     * @var bool $tested Whether the student was tested for the skills test exam.
     */
    protected $tested;

    /**
     * @var bool $passed Whether the student passed skills test exam.
     */
    protected $passed;

    /**
     * @var string $finalgrade The skill test / check ride test final grade.
     */
    protected $finalgrade;

    /**
     * @var array $noshowbookings A array of no-show bookings
     */
    protected $noshowbookings;

    /**
     * Constructor.
     *
     * @param $course The subscribing course the student is enrolled in.
     * @param int $studentid     The student id.
     */
    public function __construct($course, int $studentid, string $studentname = '', int $enroldate = 0) {
        parent::__construct($course, $studentid);
        $this->fullname = $studentname;
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

        try {
            // remove all week/year slots for the user to avoid updates
            $result = slot_vault::delete_slots($this->courseid, $year, $week, $this->userid);

            if ($result) {
                foreach ($slots as $slot) {
                    $newslot = new slot(0,
                        $this->userid,
                        $this->courseid,
                        $slot['starttime'],
                        $slot['endtime'],
                        $year,
                        $week,
                    );

                    // add each slot and record the slot id
                    $slotid = slot_vault::save_slot($newslot);
                    $slotids .= (!empty($slotids) ? ',' : '') . $slotid;
                    $result &= $slotid != 0;
                }
            }

            if ($result) {
                // commit transaction
                $transaction->allow_commit();
            } else {
                $transaction->rollback(new \moodle_exception(get_string('slotssaveunable', 'local_booking')));
            }
        } catch (Exception $e) {
            $transaction->rollback($e);
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
        $result = slot_vault::delete_slots($this->courseid, $this->userid, $year, $week);

        if (empty($this->lastsessiondatets)) {
            $this->get_last_session_date();
        }

        $this->update_statistic('lastsessiondate', $this->lastsessiondatets);

        if ($result) {
            $transaction->allow_commit();
        } else {
            $transaction->rollback(new \moodle_exception(get_string('slotsdeleteunable', 'local_booking')));
        }

        return $result;
    }

    /**
     * Updates the student statistics with a specific value
     *
     * @param string $stat      The stat field being update
     * @param string $value     The field value being update
     * @return bool             The result
     */
    public function update_statistic(string $stat = null, $value = null) {
        return subscriber_vault::update_subscriber_stat($this->courseid, $this->userid, $stat, $value);
    }

    /**
     * Updates the stats table with a lastest lesson completed
     *
     * @return bool             The result
     */
    public function update_lessonscomplete_stat() {
        return subscriber_vault::update_subscriber_lessonscomplete_stat($this->courseid, $this->userid);
    }

    /**
     * Loads the student's grades
     *
     */
    public function load_grades() {
        $this->grades = $this->get_exercise_grades() + $this->get_quize_grades();
        $this->gradesloaded = true;
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

        if (!$this->gradesloaded) {
            // join both graded assignments and attempted quizes into one grades array
            $this->grades = $this->get_exercise_grades() + $this->get_quize_grades();
        }

        return $this->grades;
    }

    /**
     * Get student grade for a specific exercise.
     *
     * @param int  $coursemodid  The quiz/exercise id associated with the grade
     * @param bool $getattempts  Wether to retrieve all attempts or not.
     * @return grade The student exercise grade.
     */
    public function get_grade(int $coursemodid, bool $getattempts = false) {

        // get the grade if already exists otherwise create a new one making sure it's not empty
        if ($this->gradesloaded) {

            $grade = array_key_exists($coursemodid, $this->grades) ? $this->grades[$coursemodid] : null;

        } else {

            // fetch grade_grade then ensure it is graded!
            $gradeitem = $this->course->get_grading_item($coursemodid);
            $grade = new grade($gradeitem, $this->userid, $coursemodid, false, $getattempts);

            // discard the grade if the final grade is missing or it's over before cutoff period for processing past data
            if (empty($grade->finalgrade) || (strtotime(LOCAL_BOOKING_PASTDATACUTOFF . ' day', $grade->timemodified) < time())) {
                $grade = null;
            }

            // add to grades object
            if (!array_key_exists($coursemodid, $this->grades)) {
                $this->grades[$coursemodid] = $grade;
            }
        }

        return $grade;
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
     * Get a list of the module grades.
     *
     * @param string $modetype The module type for the course modules.
     * @return array An array of the student exercise grade objects.
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
    public function get_slots($weekno, $year, bool $notified = false) {

        if (empty($this->slots)) {
            $this->slots = slot_vault::get_slots($this->course->get_id(), $this->userid, $weekno, $year, $notified);

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
     * Get student progression status.
     *
     * @return string $progressionstatus;
     */
    public function get_status() {
        return $this->progressionstatus;
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
     * Returns the date time of the student
     * graduated date.
     *
     * @param bool $timestamp   whether to return the timestamp or datetime object
     * @return  DateTime|int
     */
    public function get_graduated_date(bool $timestamp = false) {
        $graduatedate = null;
        if (!empty($this->graduateddate)) {
            $graduatedate = new \DateTime('@' . $this->graduateddate);
        }
        return $timestamp ? $this->graduateddate : $graduatedate;
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
            $today = new \DateTime('@' . time());
            $nextsessiondate = $today;

            // process restriction if posting wait restriction is enabled or if the student doesn't have a waiver
            if ($this->course->postingwait > 0 && !$hasrestrictionwaiver) {

                $lastsession = $this->get_last_booking_date();

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
                if ($nextsessiondate->getTimestamp() < time()) {
                    $nextsessiondate = new \DateTime('@' . time());
                }
            }

            // If at end of the week, the next session date would be the first day of the following week.
            if ($nextsessiondate == $today && date('N') == 7) {
                $nextsessiondate->modify('+1 day');
            }

            // rest the hours to start of the day
            $nextsessiondate->settime(0,0);
            $this->restrictiondate = $nextsessiondate;
        }

        return $this->restrictiondate;
    }

    /**
     * Returns the current exercise id for the student.
     *
     * @param bool $next  Whether to get the next exercise or current, default is next exercise
     * @return int The current exercise id
     */
    public function get_current_exercise() {

        if (empty($this->currentexerciseid)) {

            // get last booked exercise
            $booking = $this->get_last_booking();
            $this->currentexerciseid = !empty($booking) ? $booking->get_exerciseid() : 0;

        }

        return $this->currentexerciseid;
    }

    /**
     * Returns the next exercise id for the student.
     *
     * @return int The next exercise id
     */
    public function get_next_exercise(int $afterexercise = 0) {

        if (empty($this->nextexerciseid)) {

            // get booking if exists otherwise pick the next exercise
            $booking = $this->get_active_booking();
            if (!empty($booking)) {

                $this->nextexerciseid = $booking->get_exerciseid();

            } else {

                // check for newly enrolled students (boundry condition)
                if (empty($this->get_exercise_grades())) {
                    $this->nextexerciseid = array_values($this->course->get_modules())[0]->id;
                } else {
                    // since there are no active sessions, the next exercise is the next course module after current exercise
                    // filter out none 'assign' modules
                    $coursemodules = $this->course->get_exercises();
                    $modids = array_keys($coursemodules);
                    $nextid = array_search(($afterexercise ?: $this->get_current_exercise()), $modids) + 1;

                    // check for graduated student (boundry condition)
                    if (isset($modids[$nextid])) {
                        $this->nextexerciseid = $modids[$nextid];
                    } else {
                        $this->nextexerciseid = 0;
                    }
                }
            }
        }

        return $this->nextexerciseid;
    }

    /**
     * Returns the student's priority object.
     *
     * @return priority The student's priority object
     */
    public function get_priority() {

        if (empty($this->priority)) {
            $this->priority = new priority($this);
        }

        return $this->priority;
    }

    /**
     * Returns the student's days waiting
     *
     * @return int
     */
    public function get_recency_days() {

        if (!isset($this->recencydays)) {
            $today = new DateTime('@' . time());
            $this->recencydays =  (date_diff($this->get_wait_date(), $today))->days;
        }

        return $this->recencydays;
    }

    /**
     * Returns the date the student was waiting since
     *
     * @return DateTime
     */
    public function get_wait_date() {
        return new DateTime('@' . $this->waitdate);
    }

    /**
     * Returns the total number of active posts.
     *
     * @return int The number of active posts
     */
    public function get_total_posts() {

        if (!isset($this->total_posts))
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
        $grade = null;
        $exercises = $this->get_exercise_grades();
        if (count($exercises) > 0) {
            $exercisesIterator = (new ArrayObject($exercises))->getIterator();
            $exercisesIterator->seek(count($this->get_exercise_grades())-1);
            $grade = $exercisesIterator->current();
        }
        return $grade;
    }

    /**
     * Get the date timestamp of the last booked slot
     *
     * @return int The last booked session datetime
     */
    public function get_last_booking_date() {
        return slot::get_last_booked_slot_date($this->course->get_id(), $this->userid);
    }

    /**
     * Get the student no-show bookings.
     *
     * @return array no-show bookings
     */
    public function get_noshow_bookings() {

        if (!isset($this->noshowbookings)) {
            $this->noshowbookings = booking_vault::get_noshow_bookings($this->course->get_id(), $this->userid);
        }

        return $this->noshowbookings;
    }

    /**
     * Get the list of pending lessons
     *
     * @param  bool  $byname Whether to return the name or ids of pending lessons
     * @return array list of pending lessons
     */
    public function get_completed_lessons(bool $byname = false) {
    }

    /**
     * Get the list of pending lessons
     *
     * @param  bool  $byname Whether to return the name or ids of pending lessons
     * @return array list of pending lessons
     */
    public function get_pending_lessons(bool $byname = false) {

        $pendinglessons = [];
        if (!isset($this->incompletelessons)) {

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
                $this->incompletelessons = $this->vault->get_student_incomplete_lesson_ids($this->userid, $this->course->get_id(), $exerciseid);

            } else {
                $this->incompletelessons = [];
            }
        }

        // check whether to return ids or string
        if ($byname) {
            foreach ($this->incompletelessons as $lessonid) {
                $pendinglessons[] = $this->course->get_lesson_module($lessonid)->name;
            }
        } else  {
            $pendinglessons = $this->incompletelessons;
        }

        return $pendinglessons;
    }

    /**
     * Get the student's skill test / check ride test final grade.
     *
     * @return string
     */
    public function get_finalgrade() {
        return $this->finalgrade;
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
     * Set the student's progression status.
     *
     * @param string $slotcolor
     */
    public function set_status(string $progressionstatus) {
        $this->progressionstatus = $progressionstatus;
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
     * Get data from student statistics
     *
     * @param string $stat
     * @param $value
     */
    public function get_statistic(string $stat) {
        return subscriber_vault::get_subscriber_stat($this->course->get_id(), $this->userid, $stat);
    }

    /**
     * Get a specific notification scheduled to be sent
     *
     * @param string $notification
     * @return bool  $enable
     */
    public function get_notify(string $notification) {
        $enabled = false;
        $notifyjson = json_decode($this->get_statistic("notifyflags"), true);

        // verify the JSON string
        if (!empty($notifyjson)) {
            $enabled = array_key_exists($notification, $notifyjson) ? $notifyjson[$notification] : false;
        }

        // encode notifications
        return $enabled;
    }

    /**
     * Set student notification to schedule a notification for
     * the participant depending on the type of notification.
     *
     * @param string $notification
     * @param bool   $enable
     */
    public function set_notify(string $notification, bool $enabled = true) {
        $notifications = array();
        $notifyjson = json_decode($this->get_statistic("notifyflags"), true);

        // verify the JSON string
        if (!empty($notifyjson)) {
            $notifications = $notifyjson;
        }
        $notifications[$notification] = $enabled;

        // encode notifications
        $this->update_statistic("notifyflags", json_encode($notifications));
    }

    /**
     * Returns whether the student completed
     * all lessons prior to the upcoming next
     * exercise.
     *
     * @return  bool    Whether the lessones were completed or not.
     */
    public function has_completed_lessons() {
        return isset($this->lessonscomplete) ? $this->lessonscomplete : false;
    }

    /**
     * Returns whether the student completed
     * all course work including skill test.
     *
     * @return  bool    Whether the course work has been completed.
     */
    public function has_completed_coursework() {
        return $this->tested() && $this->passed;
    }

    /**
     * Returns whether the student submitted assignment for
     * the passed exercise.
     *
     * @return  bool    Whether the course work has been completed.
     */
    public function has_submitted_assignment(int $exerciseid) {

        if ($hassubmission = !empty($exerciseid)) {
            // get the assignment associated with the exercise comments
            $gradeitem = $this->course->get_grading_item($exerciseid);

            if (!empty($gradeitem)) {
                $grade = new grade($gradeitem, $this->userid, $exerciseid, true);
                $assign = $grade->get_assignment();

                // check if a file submission is required for this exercise
                if ($assign->is_any_submission_plugin_enabled()) {

                    // get all submissions
                    $submissions = $assign->get_user_submission($this->userid, 0);

                    // get the file storage object and verify that an assignment file has been submitted
                    if ($submissions) {
                        $fs = get_file_storage();
                        $hassubmission = !$fs->is_area_empty($gradeitem->get_context()->id, 'assignsubmission_file', 'submission_files', $submissions->id);
                    } else {
                        $hassubmission = false;
                    }
                }
            }
        }

        return $hassubmission;
    }

    /**
     * Returns whether the student is on hold or not.
     *
     * @return  bool    Whether the student is on hold.
     */
    public function is_onhold() {
        return $this->is_member_of(LOCAL_BOOKING_ONHOLDGROUP);
    }

    /**
     * Returns whether the student is in 'Keep Active' status.
     *
     * @return  bool    Whether the student is in 'Keep Active' status.
     */
    public function is_kept_active() {
        return $this->is_member_of(LOCAL_BOOKING_KEEPACTIVEGROUP);
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
     * Returns whether the student has been tested
     * skills test or final exam.
     *
     * @return bool
     */
    public function tested() {

        if (!isset($this->tested)) {

            // set tested negative
            $this->tested = false;

            // check grade for the qualifying exercise
            $grade = $this->get_grade($this->course->get_graduation_exercise());
            if ($this->tested = !empty($grade)) {
                $this->finalgrade = $grade->gradeinfo->grades[$this->userid]->str_grade;
                $this->passed = $grade->is_passed();
            }
        }

        return $this->tested;
    }

    /**
     * Returns whether the student has been passed
     * skills test or final exam.
     *
     * @return bool
     */
    public function passed() {
        return $this->passed;
    }

    /**
     * Returns whether the student has completed the course.
     *
     * @param  bool? $getcompletion forces lookup of course completion
     * @return bool  Whether the student had graduated.
     */
    public function graduated(bool $getcompletion = false) {
        return $getcompletion ? (new \completion_info($this->course->get_course()))->is_course_complete($this->userid) : !empty($this->graduateddate);
    }

    /**
     * Loads student's data from a table record
     *
     * @param \stdClass The table record
     */
    public function populate($record) {
        // call extended method first
        parent::populate($record);
        if (!empty($record)) {
            if (!empty($record->currentexerciseid))
                $this->currentexerciseid = $record->currentexerciseid;
            if (!empty($record->nextexerciseid))
                $this->nextexerciseid = $record->nextexerciseid;
            if (!empty($record->lessonscomplete))
                $this->lessonscomplete = $record->lessonscomplete;
            if (!empty($record->graduateddate))
                $this->graduateddate = $record->graduateddate;
            if (!empty($record->waitdate))
                $this->waitdate = $record->waitdate;
        }
        // set status
        if ($this->has_completed_coursework()) {
            $this->progressionstatus = 'graduate';
        } elseif ($this->lessonscomplete || !$this->course->requires_lesson_completion()) {
            if ($record->booked) {
                $this->progressionstatus = 'posts_grade';
            } else {
                $this->progressionstatus = $record->hasactiveposts ? 'posts_completed' : 'noposts_completed';
            }
        } else {
            $this->progressionstatus = 'not_completed';
        }
    }
}