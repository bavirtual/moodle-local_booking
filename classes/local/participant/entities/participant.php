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
use local_booking\local\session\data_access\grading_vault;
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
     * @var int $courseid The participant user id.
     */
    protected $courseid;

    /**
     * @var \stdClass $user The participant Moodle user object.
     */
    protected $user;

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
     * @var array $roles The participant assigned roles.
     */
    protected $roles;

    /**
     * @var array $rolenames The participant assigned roles shortname.
     */
    protected $rolenames;

    /**
     * @var int $enroldate The participant enrolment date timestamp.
     */
    protected $enroldate = 0;

    /**
     * @var int $suspenddate The participant enrolment suspension date timestamp.
     */
    protected $suspenddate = 0;

    /**
     * @var int $lastlogin The participant last login date timestamp.
     */
    protected $lastlogin;

    /**
     * @var \stdClass $lastgraded The participant last graded information.
     */
    protected $lastgraded;

    /**
     * @var int $lastbookeddatets The participant last booked date timestamp.
     */
    protected $lastbookeddatets;

    /**
     * @var int $lastsessiondatets The participant last session date timestamp.
     */
    protected $lastsessiondatets;

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
    protected $is_student = true;

    /**
     * @var bool $is_instructor The participant is an instructor.
     */
    protected $is_instructor = false;

    /**
     * @var bool $is_examiner The participant is an examiner.
     */
    protected $is_examiner = false;

    /**
     * @var bool $is_active The participant is active.
     */
    protected $is_active = false;

    /**
     * @var bool $status The participant's enrolment status.
     */
    protected $status;

    /**
     * @var array $groups The student array of member of groups.
     */
    protected $groups = [];

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
     * @param $course     The subscribing course object or id the participant is enrolled in.
     * @param int $userid The user id.
     */
    public function __construct($course, int $userid) {

        $this->vault = new participant_vault();
        $this->course = is_numeric($course) ? (new subscriber(intval($course))) : $course;
        $this->courseid = $this->course->get_id();
        $this->userid = $userid;
        $this->user = \core_user::get_user($this->userid);

        // lookup user type and active status
        if ($userid != 0) {

            // enrolment type
            $this->is_student = $this->has_role('student');
            $this->is_instructor = $this->has_role(LOCAL_BOOKING_INSTRUCTORROLE) || $this->has_role(LOCAL_BOOKING_SENIORINSTRUCTORROLE) || $this->has_role(LOCAL_BOOKING_FLIGHTTRAININGMANAGERROLE);
            $this->is_examiner = $this->has_role(LOCAL_BOOKING_EXAMINERROLE);

            // get active participant courses
            $this->is_active = !$this->is_student ? !$this->is_member_of(LOCAL_BOOKING_INACTIVEGROUP) : true;
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
     * Get participant's roles in the course.
     *
     * @param  bool  $roleattribute The role object attributes to return
     * @return array $roles
     */
    public function get_roles(string $roleattribute = null) {

        $roles = [];

        // check if shortnames is requested
        if ($roleattribute == 'shortname' && isset($this->rolenames)) {
            $roles = $this->rolenames;
        } else {
            if (!isset($this->roles)) {
                // assign roles if not already available
                $this->roles = get_user_roles($this->course->get_context(), $this->userid);
                $this->rolenames = array_column($this->roles, 'shortname');
            }

            // get course named roles if requested
            if ($roleattribute == 'course') {
                $roles = explode(', ', strip_tags(get_user_roles_in_course($this->userid, $this->course->get_id())));
            } else {
                $roles = !empty($roleattribute) ? array_column($this->roles, $roleattribute) : $this->roles;
            }
        }

        return $roles;
    }

    /**
     * Get course id.
     *
     * @return int $course->id
     */
    public function get_courseid() {
        return $this->courseid;
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
     * Get participant name.
     *
     * @param bool   $alternate returns either the fullname w/ alternate or just first/last name
     * @param string $namepart  returns name part (either first or last), $alternate must be false
     * @return string $fullname/$name;
     */
    public function get_name(bool $alternate = true, $namepart = '') {
        // get profile user name information
        if (empty($this->name)) {
            $this->name = $this->user->firstname . ' ' . $this->user->lastname;
            $this->fullname = $this->name . ' ' . $this->user->alternatename;
        }

        // check for name part
        switch ($namepart) {
            case 'first':
                $name = $this->user->firstname;
                break;
            case 'last':
                $name = $this->user->lastname;
                break;
            default:
                $name = $this->name;
        }

        return $alternate ? $this->fullname : $name;
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

        if (!isset($this->bookings)) {
            $bookings = [];
            $allcourses = \get_user_preferences('local_booking_1_xcoursebookings', false, $this->userid);
            $bookingobjs = booking_vault::get_bookings($this->course->get_id(), $this->userid, $isstudent, $oldestfirst, $activeonly, $allcourses);
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
        if (!isset($this->logbook) || $loadentries) {
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
        if (!isset($this->enroldate)) {
            // TODO: PHP9 deprecates dynamic properties
            $enroldate = 'enroldate';
            $this->enroldate = ($this->vault->get_enrol_date($this->course->get_id(), $this->userid))->$enroldate;
        }
        return new \DateTime('@' . $this->enroldate);
    }

    /**
     * Get participant's enrolment suspension date.
     *
     * @return \DateTime $enroldate  The enrolment suspension date of the participant.
     */
    public function get_suspension_date() {
        if (!isset($this->suspenddate)) {
            // TODO: PHP9 deprecates dynamic properties
            $suspenddate = 'suspenddate';
            $this->suspenddate = ($this->vault->get_enrol_date($this->course->get_id(), $this->userid))->$suspenddate;
        }
        return new \DateTime('@' . $this->suspenddate);
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
     * @return  \DateTime    The date of the last grading
     */
    public function get_last_graded_date() {
        if (!isset($this->lastgraded))
            $this->lastgraded = grading_vault::get_last_graded_date($this->userid, $this->course->get_id(), $this->is_student);

        $lastgradeddate = !empty($this->lastgraded) ? new \DateTime('@' . $this->lastgraded->timemodified) : null;

        return $lastgradeddate;
    }

    /**
     * Returns the date of the last booked session.
     *
     * @return  \DateTime   The date of the last booked session
     */
    public function get_last_booked_date() {

        $lastbookeddate = null;
        if (!isset($this->lastbookeddatets) || empty($this->lastbookeddatets)) {

            $lastbookeddate = booking::get_last_booked_date($this->course->get_id(), $this->userid, !$this->is_student);

            if (!empty($lastbookeddate)) {
                $this->lastbookeddatets = $lastbookeddate->getTimestamp();
            }
        } else {
            $lastbookeddate = new \DateTime('@' . $this->lastbookeddatets);
        }

        return $lastbookeddate;
    }

    /**
     * Returns the date of the last session
     *
     * @return  \DateTime   The date of the last booked session
     */
    public function get_last_session_date() {

        $lastsessiondate = null;
        if (!isset($this->lastsessiondatets) || empty($this->lastsessiondatets)) {
            $this->lastsessiondatets = booking::get_last_session_date($this->course->get_id(), $this->userid, !$this->is_student);
        }

        if (!empty($this->lastsessiondatets)) {
            $lastsessiondate  = new \DateTime('@' . $this->lastsessiondatets);
        }

        return $lastsessiondate;
    }

    /**
     * Returns participant's simulator user field
     *
     * @param  bool $primary Whether requesting the primary|secondary simulator
     * @return string   The participant callsign
     */
    public function get_simulator(bool $primary = true) {
        return $this->get_profile_field('simulator' . ($primary ? '' : '2'));
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
     * Returns participant's ATO recorded hours
     * using integration db.
     *
     * @return string   The participant's total ATO hours
     */
    public function get_ato_hours() {
        $totalatohours = 'N/A';
        // get pilot stats integrated info
        if (subscriber::has_integration('external_data', 'pilotstats')) {
            // TODO: PHP9 deprecates dynamic properties
            // get pilot id from integration
            $pilotrec = $this->course->get_external_data('pilotid', 'pilotinfo', $this->user->alternatename);
            $pilotid = $pilotrec['pilotid'];
            $pilotstatsrec = $this->course->get_external_data('pilotstats', 'stats', $pilotid);
            $totalatohours = $pilotstatsrec['totalatohours'];
        }
        return $totalatohours;
    }

    /**
     * Returns pilot's fleet association
     *
     * @return string   The participant's fleet
     */
    public function get_fleet() {
        return $this->get_profile_field('fleet');
    }

    /**
     * Returns a participant's user profile field
     *
     * @param string $field     The name of the field
     * @param bool   $corefield Whether the field is a core Moodle field
     * @return string           The participant custom field
     */
    public function get_profile_field(string $field, bool $corefield = false) {
        $value = '';

        if (!$corefield) {
            profile_load_data($this->user);
            $fld = 'profile_field_' . $field;
            if (property_exists($this->user, $fld)) {
                $value = $this->user->$fld ?: '';
            }
        } else {
            $value = $this->user->$field;
        }
        // TODO: PHP9 deprecates dynamic properties
        return $value;
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
     * Suspends the student's enrolment to a course.
     *
     * @param bool $status  The status of the enrolment suspended = true
     * @return bool         The result of the suspension action.
     */
    public function suspend(bool $status = true) {
        return $this->vault->suspend($this->course->get_id(), $this->userid, (int)$status);
    }

    /**
     * Loads participant's data from a table record
     *
     * @param \stdClass The table record
     */
    public function populate($record) {
        if (!empty($record)) {
            $this->fullname    = $record->fullname;
            $this->enroldate   = $record->enroldate;
            $this->suspenddate = $record->suspenddate;
            $this->lastlogin   = $record->lastlogin;
            $this->simulator   = $this->get_simulator();
            if (!empty($record->roles))
                $this->rolenames   = explode(',', $record->roles);
            if (!empty($record->lastsessiondate))
                $this->lastsessiondatets = $record->lastsessiondate;
        }
    }

    /**
     * checkes whether the participant has a particular role.
     *
     * @param string $role The role to check.
     * @return bool        Whether the participant has the role.
     */
    public function has_role(string $role) {
        return in_array($role, $this->get_roles('shortname'));
    }

    /**
     * Returns the date from which the participant had
     * the passed role otherwise returns a null.
     *
     * @param string $role      The role to check.
     * @param bool   $tostring  Whether to return a string or the date object.
     * @return DateTime|string  The date the participant had the role (null if not found).
     */
    public function has_role_since(string $role, bool $tostring = true) {
        $returnval = null;
        $roles = array_values($this->get_roles());
        $idx = array_search($role, array_column($roles, 'shortname'));

        // determine return value format
        if ($idx !== false) {
            $sincedate = new DateTime ('@'.$roles[$idx]->timemodified);
            $returnval = $tostring ? $sincedate->format('M j\, Y') : $sincedate;
        }

        return $returnval;
    }

    /**
     * verifies whether the participant is part of a course group
     *
     * @param int    $studentid The associated user id.
     * @param string $groupname The group name to verify membership.
     * @return bool             The result of the being a member of the passed group.
     */
    public function is_member_of(string $groupname) {
        $ismember = false;
        if (!$ismember = in_array($groupname, $this->groups)) {
            $groupid = groups_get_group_by_name($this->course->get_id(), $groupname);
            if ($ismember = groups_is_member($groupid, $this->userid)) {
                array_push($this->groups, $groupname);
            }
        }
        return $ismember;
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
     * check if the participant is an instructor
     *
     * @return bool $is_instructor.
     */
    public function is_instructor() {
        return $this->is_instructor;
    }

    /**
     * check if the participant is an examiner
     *
     * @return bool $is_examiner.
     */
    public function is_examiner() {
        return $this->is_examiner;
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