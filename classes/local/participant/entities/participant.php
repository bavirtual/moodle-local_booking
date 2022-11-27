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
 * Class representing all student and instructor course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\entities;

use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\entities\booking;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\subscriber\entities\subscriber;

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . "/lib/enrollib.php");

class participant implements participant_interface {

    /**
     * @var participant_vault $vault The vault access to the database.
     */
    protected $vault;

    /**
     * @var subscriber $course The participant enrolment course id.
     */
    protected $course;

    /**
     * @var int $userid The participant user id.
     */
    protected $userid;

    /**
     * @var string $fullname The participant user fullname (first last alternate).
     */
    protected $fullname;

    /**
     * @var string $name The participant user first and last name.
     */
    protected $name;

    /**
     * @var int $enroldate The participant enrolment date timestamp.
     */
    protected $enroldate;

    /**
     * @var int $lastlogin The participant last login date timestamp.
     */
    protected $lastlogin;

    /**
     * @var string $callsign The participant callsign.
     */
    protected $callsign;

    /**
     * @var string $simulator The participant simulator.
     */
    protected $simulator;

    /**
     * @var bool $is_student The participant is a student.
     */
    protected $is_student;

    /**
     * @var bool $is_active The participant is active.
     */
    protected $is_active = false;

    /**
     * @var bool $status The participant's enrolment status.
     */
    protected $status;

    /**
     * @var booking[] $bookings The student array of bookings.
     */
    protected $bookings;

    /**
     * @var logbook $logbook The student logbook.
     */
    protected $logbook;

    /**
     * Constructor.
     *
     * @param subscriber $course The subscribing course the participant is enrolled in.
     * @param int $userid The user id.
     */
    public function __construct(subscriber $course, int $userid) {

        $this->vault = new participant_vault();
        $this->course = $course;
        $this->userid = $userid;

        // lookup user type and active status
        if ($userid != 0) {
            $this->is_student = count(get_user_roles($course->get_context(), $userid)) > 0 &&
                current(get_user_roles($course->get_context(), $userid))->shortname == 'student' ? true : false;

            // get active participant courses
            $enroledcourses = enrol_get_users_courses($userid, true);
            foreach ($enroledcourses as $ue) {
                // Default status field label and value.
                if ($ue->id == $course->get_id()) {
                    $this->is_active = !$this->is_student ? !self::is_member_of($course->get_id(), $userid, LOCAL_BOOKING_INACTIVEGROUP) : true;
                    break;
                }
            }
        }
    }

    /**
     * Get user id.
     *
     * @return int $userid
     */
    public function get_id() {
        return $this->userid;
    }

    /**
     * Get participant's subscribed course.
     *
     * @return subscriber $course
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Get course id.
     *
     * @return int $course->id
     */
    public function get_courseid() {
        return $this->course->get_id();
    }

    /**
     * Returns full username
     *
     * @param int       $participantid The user id.
     * @param bool      $includealternate Whether to include the user's alternate name.
     * @return string   $fullusername The full participant username
     */
    public static function get_fullname(int $participantid, bool $alternate = true) {
        return participant_vault::get_participant_name($participantid, $alternate);
    }

    /**
     * Get fullname.
     *
     * @param bool $alternate returns either the fullname w/ alternate or just first/last name
     * @return string $fullname/$name;
     */
    public function get_name(bool $alternate = true) {
        // get profile user name information
        if (empty($this->name)) {
            $u = \core_user::get_user($this->userid);
            $this->name = $u->firstname . ' ' . $u->lastname;
            $this->fullname = $this->name . ' ' . $u->alternatename;
        }
        return $alternate ? $this->fullname : $this->name;
    }

    /**
     * Get an participant's bookings
     *
     * @param bool $isstudent   Whether to get student bookings
     * @param bool $activeonly  Whether to get active bookings only
     * @param bool $oldestfirst Whether to sort results by oldest
     * @return booking[] An array of bookings.
     */
    public function get_bookings(bool $isstudent = true, bool $activeonly = false, bool $oldestfirst = false) {

        if (empty($this->bookings)) {
            $bookings = [];
            $bookingobjs = booking_vault::get_bookings($this->course->get_id(), $this->userid, $isstudent, $oldestfirst, $activeonly);
            foreach ($bookingobjs as $bookingobj) {
                $booking = new booking();
                $booking->load($bookingobj);
                $bookings[] = $booking;
            }
            $this->bookings = $bookings;
        }

        return $this->bookings;
    }

    /**
     * Get an a's active bookings
     *
     * @param  $loadentries Whether to load all enteries or not
     * @param  bool $allentries Whether to get entries for all courses
     * @return logbook   An array of bookings.
     */
    public function get_logbook(bool $loadentries = false, bool $allentries = false) {
        if (empty($this->logbook)) {
            $logbook = new logbook($this->course->get_id(), $this->userid);
            if ($loadentries)
                $logbook->load($allentries);
            $this->logbook = $logbook;
        }
        return $this->logbook;
    }

    /**
     * Get participant's enrolment date.
     *
     * @return \DateTime $enroldate  The enrolment date of the participant.
     */
    public function get_enrol_date() {
        $enrol = $this->enroldate ?: ($this->vault->get_enrol_date($this->course->get_id(), $this->userid))->timecreated;
        $enrolmentdate = new \DateTime('@' . $enrol);
        return $enrolmentdate;
    }

    /**
     * Get student's last login date.
     *
     * @return DateTime $lastlogindate  The participant's last login date.
     */
    public function get_last_login_date() {
        $lastlogindate = !empty($this->lastlogin) ? new \DateTime('@' . $this->lastlogin) : null;
        return $lastlogindate;
    }

    /**
     * Returns the date of the last graded session.
     *
     * @return  \DateTime    The timestamp of the last grading
     */
    public function get_last_graded_date() {
        $lastgraded = $this->vault->get_last_graded_date($this->userid, $this->course->get_id(), $this->is_student);

        $lastgradeddate = !empty($lastgraded) ? new \DateTime('@' . $lastgraded->timemodified) : null;

        return $lastgradeddate;
    }

    /**
     * Returns the date of the last booked session.
     *
     * @return  \DateTime    The timestamp of the last booked session
     */
    public function get_last_booked_date() {
        $sessiondate = booking::get_last_session($this->course->get_id(), $this->userid, !$this->is_student);

        $lastsessiondate = !empty($sessiondate) ? new \DateTime('@' . $sessiondate->lastbookedsession) : null;

        return $lastsessiondate;
    }

    /**
     * Returns participant's simulator user field
     *
     * @return string   The participant callsign
     */
    public function get_simulator() {
        return $this->get_profile_field('simulator');
    }

    /**
     * Returns participant's callsign user field
     *
     * @return string   The participant callsign
     */
    public function get_callsign() {
        return $this->get_profile_field('callsign');
    }

    /**
     * Returns participant's profile comment, user description field
     *
     * @return string   The participant comment
     */
    public function get_comment() {
        return $this->get_profile_field('description', true);
    }

    /**
     * Updates a participant's profile comment, user description field
     *
     * @param string $comment   The participant comment
     * @return bool
     */
    public function update_comment(string $comment) {
        return $this->vault->update_participant_field($this->userid, 'description', $comment);
    }

    /**
     * Returns a participant's user profile field
     *
     * @param string $field     The name of the field
     * @param bool   $corefield Whether the field is a core Moodle field
     * @return string           The participant custom field
     */
    public function get_profile_field(string $field, bool $corefield = false) {
        $u = \core_user::get_user($this->userid);

        if (!$corefield) {
            profile_load_data($u);
            $fld = 'profile_field_' . $field;
        }
        return $corefield ? $u->$field : $u->$fld;
    }

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param bool $status  The status of the enrolment suspended = true
     * @return bool         The result of the suspension action.
     */
    public function suspend(bool $status = true) {
        return $this->vault->suspend($this->course->get_id(), $this->userid, (int)$status);
    }

    /**
     * Loads participant's date from a table record
     *
     * @param string   The participant callsign
     */
    public function populate($record) {
        $this->fullname = $record->fullname;
        $this->enroldate = $record->enroldate;
        $this->lastlogin = $record->lastlogin;
        $this->simulator = $this->get_simulator();
    }

    /**
     * verifies whether the participant is part of a course group
     *
     * @param int    $courseid  The associated course id.
     * @param int    $studentid The associated user id.
     * @param string $groupname The group name to verify membership.
     * @return bool             The result of the being a member of the passed group.
     */
    public static function is_member_of(int $courseid, int $userid, string $groupname) {
        $groupid = groups_get_group_by_name($courseid, $groupname);
        return groups_is_member($groupid, $userid);
    }

    /**
     * check if the participant is a student
     *
     * @return bool $is_student.
     */
    public function is_student() {
        return $this->is_student;
    }

    /**
     * check if the participant is active
     *
     * @return bool $is_active.
     */
    public function is_active() {
        return $this->is_active;
    }
}