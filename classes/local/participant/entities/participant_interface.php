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

defined('MOODLE_INTERNAL') || die();

interface participant_interface {

    /**
     * Get user id.
     *
     * @return int $userid
     */
    public function get_id();

    /**
     * Get participant's subscribed course.
     *
     * @return subscriber $course
     */
    public function get_course();

    /**
     * Get participant's roles in the course.
     *
     * @return array $roles
     */
    public function get_roles();

    /**
     * Get participant name.
     *
     * @param bool   $alternate returns either the fullname w/ alternate or just first/last name
     * @param string $namepart  returns name part (either first or last)
     * @return string $fullname/$name;
     */
    public function get_name(bool $alternate = true, $namepart = '');

    /**
     * Returns full username
     *
     * @param int       $participantid The user id.
     * @param bool      $includealternate Whether to include the user's alternate name.
     * @return string   $fullusername The full participant username
     */
    public static function get_fullname(int $participantid, bool $alternate = true);

    /**
     * Get an participant's bookings
     *
     * @param bool $isstudent   Whether to get student bookings
     * @param bool $activeonly  Whether to get active bookings only
     * @param bool $oldestfirst Whether to sort results by oldest
     * @return booking[] An array of bookings.
     */
    public function get_bookings(bool $isstudent = true, bool $activeonly = false, bool $oldestfirst = false);

    /**
     * Get an a's active bookings
     *
     * @param  $loadentries Whether to load all enteries or not
     * @param  bool $allentries whether to get entries for all courses
     * @return logbook   An array of bookings.
     */
    public function get_logbook(bool $loadentries = false, bool $allentries = false);

    /**
     * Get student's enrolment date.
     *
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date();

    /**
     * Get participant's enrolment suspension date.
     *
     * @return \DateTime $enroldate  The enrolment suspension date of the participant.
     */
    public function get_suspension_date();

    /**
     * Get student's last login date.
     *
     * @return \DateTime $lastlogindate  The participant's last login date.
     */
    public function get_last_login_date();

    /**
     * Returns the date of the last
     * graded session.
     *
     * @return  \DateTime    The date of the last grading
     */
    public function get_last_graded_date();

    /**
     * Returns the date of the last booked session.
     *
     * @return  \DateTime    The date of the last booked session
     */
    public function get_last_booked_date();

    /**
     * Returns the date of the last session
     *
     * @return  \DateTime   The date of the last booked session
     */
    public function get_last_session_date();

    /**
     * Returns participant's simulator user field
     *
     * @param  bool $primary Whether requesting the primary|secondary simulator
     * @return string   The participant callsign
     */
    public function get_simulator(bool $primary = true);

    /**
     * Returns pilot's callsign user field
     *
     * @return string   The participant callsign
     */
    public function get_callsign();

    /**
     * Returns participant's ATO recorded hours
     * using integration db.
     *
     * @return string   The participant callsign
     */
    public function get_ato_hours();

    /**
     * Returns pilot's fleet association
     *
     * @return string   The participant's fleet
     */
    public function get_fleet();

    /**
     * Returns a participant's user profile field
     *
     * @param string $field     The name of the field
     * @param bool   $corefield Whether the field is a core Moodle field
     * @return string           The participant custom field
     */
    public function get_profile_field(string $field, bool $corefield = false);

    /**
     * Returns participant's profile comment, user description field
     *
     * @return string   The participant comment
     */
    public function get_comment();

    /**
     * Updates a participant's profile comment, user description field
     *
     * @param string $comment   The participant comment
     * @return bool
     */
    public function update_comment(string $comment);

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param bool $status  The status of the enrolment suspended = true
     * @return bool         The result of the suspension action.
     */
    public function suspend(bool $status = true);

    /**
     * Loads participant's date from a table record
     *
     * @param string   The participant callsign
     */
    public function populate($record);

    /**
     * checkes whether the participant has a particular role.
     *
     * @param string $role The role to check.
     * @return bool        Whether the participant has the role.
     */
    public function has_role(string $role);

    /**
     * Returns the date from which the participant had
     * the passed role otherwise returns a null.
     *
     * @param string $role      The role to check.
     * @param bool   $tostring  Whether to return a string or the date object.
     * @return DateTime|string  The date the participant had the role.
     */
    public function has_role_since(string $role, bool $tostring = true);

    /**
     * verifies whether the participant is part of a course group
     *
     * @param string $groupname The group name to verify membership.
     * @return bool             The result of the being a member of the passed group.
     */
    public function is_member_of(string $groupname);

    /**
     * check if the participant is a student
     *
     * @return bool $is_student.
     */
    public function is_student();

    /**
     * check if the participant is an instructor
     *
     * @return bool $is_instructor.
     */
    public function is_instructor();

    /**
     * check if the participant is an examiner
     *
     * @return bool $is_examiner.
     */
    public function is_examiner();

    /**
     * check if the participant is active
     *
     * @return bool $is_active.
     */
    public function is_active();
}
