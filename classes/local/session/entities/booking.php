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
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\slot\data_access\slot_vault;
use \local_booking\local\slot\entities\slot;
use \local_booking\local\calendar\moodle_calendar;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a course exercise booking.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking implements booking_interface {

    /**
     * @var int $id The booking id.
     */
    protected $id;

    /**
     * @var int $courseid The course id of this bookng.
     */
    protected $courseid;

    /**
     * @var int $exerciseid The course exercise id of this bookng.
     */
    protected $exerciseid;

    /**
     * @var int $instructorid The instructor user id of this booking.
     */
    protected $instructorid;

    /**
     * @var string $instructorname The instructor name of this booking.
     */
    protected $instructorname;

    /**
     * @var int $studentid The user id of the student of this booking.
     */
    protected $studentid;

    /**
     * @var string $studentname The student name of this booking.
     */
    protected $studentname;

    /**
     * @var slot $slot The booked slot.
     */
    protected $slot;

    /**
     * @var bool $confirmed The booking is confirmed.
     */
    protected $confirmed;

    /**
     * @var bool $noshow The student didn't show to the booked session.
     */
    protected $noshow = false;

    /**
     * @var bool $active The booking is active.
     */
    protected $active;

    /**
     * @var int $bookingdate The date timestamp of this booking.
     */
    protected $bookingdate;

    /**
     * Constructor.
     *
     * @param int       $id             The booking id.
     * @param int       $courseid       The course id associated with the booking.
     * @param int       $studentid      The student id associated with this booking..
     * @param int       $exerciseid     The exercise id associated with the booking.
     */
    public function __construct(
            int $id = 0,
            $courseid = 0,
            $studentid = 0,
            $exerciseid = 0,
            $slot = null,
            $studentname = '',
            $instructorid = 0,
            $instructorname = '',
            $confirmed = false,
            $active = true,
            $bookingdate = 0
        ) {
        $this->id = $id;
        $this->courseid = $courseid;
        $this->studentid = $studentid;
        $this->exerciseid = $exerciseid;
        $this->slot = $slot;
        $this->bookingdate = $bookingdate;
        $this->confirmed = $confirmed;
        $this->active = $active;
        $this->studentname = $studentname;
        $this->instructorid = $instructorid;
        $this->instructorname = $instructorname;
    }

    /**
     * Loads this booking from the database.
     *
     * @param object $bookingrec the database record.
     * @return bool
     */
    public function load(object $bookingrec = null) {
        if (empty($bookingrec)) {
            $bookingrec = booking_vault::get_booking($this);
        }

        if (!empty($bookingrec)) {
            $this->id = $bookingrec->id;
            $this->courseid = $bookingrec->courseid;
            $this->exerciseid = $bookingrec->exerciseid;
            $this->instructorid = $bookingrec->userid;
            $this->studentid = $bookingrec->studentid;
            $this->slot = new slot($bookingrec->slotid);
            $this->slot->load();
            $this->confirmed = $bookingrec->confirmed;
            $this->active = $bookingrec->active;
            $this->noshow = $bookingrec->noshow;
            $this->bookingdate = $bookingrec->timemodified;
        }

        return !empty($bookingrec);
    }

    /**
     * Saves this booking to database.
     *
     * @return bool
     */
    public function save() {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        // save the booking, its associate slot with update
        // booking info, and purge student availability posts
        $result = false;
        if ($this->slot->save()) {
            // save new booking
            if ($this->id = booking_vault::save_booking($this)) {
                // delete other posted slots
                if (slot_vault::delete_slots($this->courseid, $this->studentid, 0, 0, false)) {
                    // add instructor and student Moodle calendar events
                    $moodlecalendar =  new moodle_calendar($this);
                    $result = $moodlecalendar->add_events();
                }
            }
        }


        // purge all slots not associated with bookings once the booking is saved
        if ($result) {
            $transaction->allow_commit();
        } else {
            $transaction->rollback(new moodle_exception(get_string('bookingsaveunable', 'local_booking')));
        }

        return $this->id != 0;
    }

    /**
     * Deletes this booking from database along with
     * assocaited instructor and student Moodle calendar events
     *
     * @return bool
     */
    public function delete() {
        // delete associated Moodle calendar events
        $moodlecalendar =  new moodle_calendar($this);
        $moodlecalendar->delete_events();
        return booking_vault::delete_booking($this);
    }

    /**
     * Persists booking confirmation to the database.
     *
     * @param string    Confirmation message
     * @return bool
     */
    public function confirm(string $confirmationmsg) {
        return booking_vault::confirm_booking($this->courseid, $this->studentid, $this->exerciseid, $this->slot, $confirmationmsg);
    }

    /**
     * Get the booking confirmation.
     *
     * @return bool
     */
    public function confirmed() {
        return $this->confirmed;
    }

    /**
     * Returns whether the booking conflicts with another
     * for the same instructor.
     *
     * @param int   $instructorid The instructor id making the booking.
     * @param int   $studentid    The student id the booking is for.
     * @param array $slottobook   The booking start & end timestamps.
     * @return {object?}
     */
    public static function conflicts(int $instructorid, int $studentid, array $slottobook) {
        // check if there is a conflict
        return booking_vault::get_booking_conflict($instructorid, $studentid, $slottobook['starttime'], $slottobook['endtime']);
    }

    /**
     * Get the booking active status.
     *
     * @return bool
     */
    public function active() {
        return $this->active;
    }

    /**
     * Get whether the student didn't show for the booked session.
     *
     * @return bool
     */
    public function noshow() {
        return $this->noshow;
    }

    /**
     * Deactivates a booking after the session
     * has been conducted.
     *
     * @return bool
     */
    public function deactivate() {
        if (booking_vault::set_booking_inactive($this->id, $this->noshow)) {
            return slot_vault::delete_slots($this->courseid, 0, 0, $this->studentid, false);
        }
    }

    /**
     * Process booking cancellation and no-shows
     *
     * @param bool $noshow Whether the student didn't show without prior notice
     * @return bool
     */
    public function cancel(bool $noshow = false) {
        if ($noshow) {
            $this->noshow = $noshow;
            $result = $this->deactivate();
        } else {
            $result = $this->delete();
        }

        return $result;
    }

    /**
     * Get the booking id for the booking.
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    // Getter functions
    /**
     * Get the course id for the booking.
     *
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Get the exercis id for the booking.
     *
     * @return int
     */
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    /**
     * Get the slot id for the booking.
     *
     * @return slot
     */
    public function get_slot() {
        return $this->slot;
    }

    /**
     * Get the instructor id for the booking.
     *
     * @return int
     */
    public function get_instructorid() {
        return $this->instructorid;
    }

    /**
     * Get the instructor name for the booking.
     *
     * @return string
     */
    public function get_instructorname() {
        return instructor::get_fullname($this->instructorid);
    }

    /**
     * Get the student id for the booking.
     *
     * @return int
     */
    public function get_studentid() {
        return $this->studentid;
    }

    /**
     * Get the student name for the booking.
     *
     * @return string
     */
    public function get_studentname() {
        return student::get_fullname($this->studentid);
    }

    /**
     * Get the booking date timestamp for the booking.
     *
     * @return int
     */
    public function get_bookingdate() {
        return $this->bookingdate;
    }

    /**
     * Get the last booking date associated
     * with the course and exercise id for the student.
     *
     * @param int $courseid      The associated course id
     * @param int $studentid     The student id conducted the session
     * @param int $exerciseid    The exercise id for the session
     * @return ?          The date of last session for that exercise
     */
    public static function get_exercise_date(int $courseid, int $studentid, int $exerciseid) {
        $exercisedatets = booking_vault::get_booked_exercise_date($courseid, $studentid, $exerciseid);
        return $exercisedatets ? new \DateTime('@' . $exercisedatets) : null;
    }

    /**
     * Get the date of the last booked session.
     *
     * @param int $courseid     The associated course id
     * @param int $userid       The user id for the booked session
     * @param int $isinstructor Whether the user is an instructor
     * @return DateTime         The date of last session for that exercise
    */
    public static function get_last_session_date(int $courseid, int $userid, bool $isinstructor = false) {
        $lastbookedsession = booking_vault::get_last_booked_session($courseid, $userid, $isinstructor);
        $lastbookedsessionts = $lastbookedsession->lastbookedsession;
        return $lastbookedsessionts ? new \DateTime('@' . $lastbookedsessionts) : null;
    }

    /**
     * Get the total sessions for a user.
     *
     * @param int $courseid     The associated course id
     * @param int $userid       The user id
     * @param int $isinstructor Whether the user is an instructor
     * @return int              The total amount of sessions conducted for the student
     */
    public static function get_total_sessions(int $courseid, int $userid, bool $isinstructor = false) {
        return booking_vault::get_user_total_sessions($courseid, $userid, $isinstructor);
    }

    // Setter functions
    /**
     * Set the course exercise id for the booking.
     *
     * @return int
     */
    public function set_courseid(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Set the course exercise id for the booking.
     *
     * @return int
     */
    public function set_exerciseid(int $exerciseid) {
        $this->exerciseid = $exerciseid;
    }

    /**
     * Set the id of booked slot.
     *
     * @return slot
     */
    public function set_slot(slot $slot = null) {
        $this->slot = $slot;
    }

    /**
     * Set the instructor user id of the booking.
     *
     * @return int
     */
    public function set_instructorid(int $instructorid) {
        $this->instructorid = $instructorid;
    }

    /**
     * Set the instructor name of the booking.
     *
     * @return string
     */
    public function set_instructorname(string $instructorname) {
        $this->instructorname = $instructorname;
    }

    /**
     * Set the studnet user id of the booking.
     *
     * @return int
     */
    public function set_studentid(int $studentid) {
        $this->studentid = $studentid;
    }

    /**
     * Set the studnet name of the booking.
     *
     * @return string
     */
    public function set_studentname(string $studentname) {
        $this->studentname = $studentname;
    }

    /**
     * Set the date array of the booking.
     *
     * @return array
     */
    public function set_bookingdate(int $bookingdate) {
        $this->bookingdate = $bookingdate;
    }
}
