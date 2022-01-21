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

use DateTime;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\entities\booking;
use local_booking\local\logbook\entities\logbook;

require_once($CFG->dirroot . "/lib/completionlib.php");

class participant implements participant_interface {

    /**
     * @var participant_vault $vault The vault access to the database.
     */
    protected $vault;

    /**
     * @var int $courseid The participant enrolment course id.
     */
    protected $courseid;

    /**
     * @var int $userid The participant user id.
     */
    protected $userid;

    /**
     * @var string $fullname The participant user fullname.
     */
    protected $fullname;

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
     * @var bool $isstudent The participant is a student.
     */
    protected $is_student;

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
     * @param int $courseid The course id.
     * @param int $userid The user id.
     */
    public function __construct(int $courseid, int $userid) {
        $this->vault = new participant_vault();
        $this->courseid = $courseid;
        $this->userid = $userid;
        $context = \context_course::instance($courseid);
        $this->is_student = count(get_user_roles($context, $userid)) > 0 && current(get_user_roles($context, $userid))->shortname == 'student' ? true : false;
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
     * Get course id.
     *
     * @return int $courseid
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Get fullname.
     *
     * @return string $fullname;
     */
    public function get_name() {
        return $this->fullname;
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
            $bookingobjs = booking_vault::get_bookings($this->courseid, $this->userid, $isstudent, $oldestfirst, $activeonly);
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
     * @return logentry[]   An array of bookings.
     */
    public function get_logbook(bool $loadentries = false) {
        if (empty($this->logbook)) {
            $logbook = new logbook($this->courseid, $this->userid);
            if ($loadentries)
                $logbook->load();
            $this->logbook = $logbook;
        }
        return $this->logbook;
    }

    /**
     * Get participant's enrolment date.
     *
     * @return DateTime $enroldate  The enrolment date of the participant.
     */
    public function get_enrol_date() {
        $enrol = $this->enroldate ?: ($this->vault->get_enrol_date($this->courseid, $this->userid))->timecreated;
        $enrolmentdate = new DateTime('@' . $enrol);
        return $enrolmentdate;
    }

    /**
     * Get student's last login date.
     *
     * @return DateTime $lastlogindate  The participant's last login date.
     */
    public function get_last_login_date() {
        $lastlogindate = !empty($this->lastlogin) ? new DateTime('@' . $this->lastlogin) : null;
        return $lastlogindate;
    }

    /**
     * Returns the date of the last
     * graded session.
     *
     * @return  DateTime    The timestamp of the last grading
     */
    public function get_last_graded_date() {
        $lastgraded = $this->vault->get_last_graded_date($this->userid, $this->courseid, $this->is_student);

        $lastgradeddate = !empty($lastgraded) ? new DateTime('@' . $lastgraded->timemodified) : null;

        return $lastgradeddate;
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
     * Returns participant's simulator user field
     *
     * @return string   The participant callsign
     */
    public function get_simulator() {
        $this->simulator = $this->simulator ?: participant_vault::get_customfield_data($this->courseid, $this->userid, 'simulator');
        return $this->simulator;
    }

    /**
     * Returns participant's callsign user field
     *
     * @return string   The participant callsign
     */
    public function get_callsign() {
        $this->callsign = empty($this->callsign) ? participant_vault::get_customfield_data($this->courseid, $this->userid, 'callsign') : $this->callsign;
        return $this->callsign;
    }

    /**
     * Set user name.
     *
     * @param string $fullname;
     */
    public function set_name(string $fullname) {
        $this->fullname = $fullname;
    }

    /**
     * Suspends the student's enrolment to a course.
     *
     * @return bool The result of the suspension action.
     */
    public function suspend() {
        return $this->vault->suspend($this->courseid, $this->userid);
    }

    /**
     * Loads participant's date from a table record
     *
     * @param string   The participant callsign
     */
    public function populate($record) {
        $this->courseid = $record->courseid;
        $this->userid = $record->userid;
        $this->fullname = $record->fullname;
        $this->enroldate = $record->enroldate;
        $this->lastlogin = $record->lastlogin;
        $this->simulator = $this->get_simulator();
    }

    /**
     * verifies whether the participant is part of a course group
     *
     * @param string $groupname The group name to verify membership.
     * @return bool             The result of the being a member of the passed group.
     */
    public function is_member_of(string $groupname) {
        $groupid = groups_get_group_by_name($this->courseid, $groupname);
        return groups_is_member($groupid, $this->userid);
    }

    /**
     * check if the participant is a student
     *
     * @return bool $is_student.
     */
    public function is_student() {
        return $this->is_student;
    }
}