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

use DateTime;
use moodle_exception;
use local_booking\local\session\entities\priority;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\slot\entities\slot;
use local_booking\local\subscriber\entities\subscriber;

class student extends participant {

    /**
     * Process user enrollments table name.
     */
    const SLOT_COLOR = '#00e676';

    /**
     * @var array $grades The student exercise/quize grades.
     */
    protected $grades;

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

                // add each slot.
                $result = $result && slot_vault::save_slot($newslot);
            }
        }

        if ($result) {
            $transaction->allow_commit();
        } else {
            $transaction->rollback(new moodle_exception(get_string('slotssaveunable', 'local_booking')));
        }

        return $result;
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
            $transaction->rollback(new moodle_exception(get_string('slotsdeleteunable', 'local_booking')));
        }

        return $result;
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
            $assignments = $this->vault->get_student_assignment_grades($this->course->get_id(), $this->userid);
            $quizes = $this->vault->get_student_quizes_grades($this->course->get_id(), $this->userid);
            $this->grades = $assignments + $quizes;
        }

        return $this->grades;
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
        $sessiondate = new DateTime('@' . $sessiondatets);

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
            $hasrestrictionwaiver = (bool) get_user_preferences('local_booking_availabilityoverride', false, $this->userid);
            $sessiondate = new DateTime('@' . time());

            // process restriction if posting wait restriction is enabled for this course or the student doesn't have a waiver
            if ($this->course->postingwait > 0 || !$hasrestrictionwaiver) {

                $lastsession = slot::get_last_booking($this->course->get_id(), $this->userid);
                $sessiondatets = $lastsession ?: time();
                $sessiondate = new DateTime('@' . $sessiondatets);
                date_add($sessiondate, date_interval_create_from_date_string($this->course->postingwait . ' days'));

                // advance by one day if it is Sunday to avoid showing a restricted week
                if ((getdate($sessiondate->getTimestamp()))['wday'] == 0) {
                    date_add($sessiondate, date_interval_create_from_date_string('1 days'));
                }
            }

            // return today's date if the last next allowed session is in the past
            if ($sessiondate->getTimestamp() < time()) {
                $sessiondate = new DateTime('@' . time());
            }
            $sessiondate->settime(0,0);
            $this->restrictiondate = $sessiondate;
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
     * Returns the next upcoming exercise id and section
     * for the student and its associated course section.
     *
     * @return int The next exercise id and associated course section
     */
    public function get_next_exercise() {
        if (empty($this->next_exercise)) {
            $this->next_exercise = $this->vault->get_next_student_exercise($this->course->get_id(), $this->userid);
        }
        return $this->next_exercise;
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
     * Returns whether the student complete
     * all sessons prior to the upcoming next
     * exercise.
     *
     * @param   int     The upcoming next exercise id
     * @return  bool    Whether the lessones were completed or not.
     */
    public function has_completed_lessons() {
        if (empty($this->nextexercise))
            $this->nextexercise = $this->get_next_exercise();
        list($exerciseid, $section) = $this->nextexercise;
        return !empty($this->nextexercise) ? $this->vault->get_student_lessons_complete($this->userid, $this->course->get_id(), $section) : false;
    }
}