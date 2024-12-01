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
 * Class interface for data access of course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\data_access;

defined('MOODLE_INTERNAL') || die();

interface participant_vault_interface {

    /**
     * Get a participant from the database.
     *
     * @param int  $courseid The course id.
     * @param int  $userid   A specific user.
     * @param bool $active   Whether the user is actively enrolled.
     * @param bool $student  Whether the user is a student or not
     * @return {Object}      Array of database records.
     */
    public static function get_participant(int $courseid, int $userid = 0, bool $active = true, bool $student = true);

    /**
     * Get all active students from the database.
     *
     * @param int $courseid     The course id.
     * @param bool $studentid   A specific student for booking confirmation
     * @return {Object}         Array of database records.
     */
    public static function get_student(int $courseid, int $studentid = 0);

    /**
     * Get all active students from the database.
     *
     * @param int $courseid         The course id.
     * @param string $filter        The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold   Whether to include on-hold students as well
     * @param int $offset           The offset record for pagination
     * @param bool &$count          Reference to the count of students count before pagination
     * @param bool $requirescompletion Whether the course has lesson completion restriction
     * @return {Object}[]           Array of database records.
     */
    public static function get_students(
        int $courseid,
        string $filter = 'active',
        bool $includeonhold = false,
        int $offset = 0,
        int &$count = 0,
        bool $requirescompletion = true);

    /**
     * Get all active participants for a course for UI select controls (ids & fullname)
     *
     * @param int $courseid         The course id.
     * @param string $filter        The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold   Whether to include on-hold students as well
     * @param string $roles         The roles of the participants
     * @return {Object}[]           Array of database records.
     */
    public static function get_participants_simple(int $courseid, string $filter = 'active', bool $includeonhold = false, string $roles = null);

    /**
     * Get all active instructors for the course from the database.
     *
     * @param int $courseid      The course id.
     * @param bool $courseadmins Indicates whether the instructor is an admin or not.
     * @return {Object}[]        Array of database records.
     */
    public static function get_instructors(int $courseid, bool $courseadmins = false);

    /**
     * Returns full username
     *
     * @param int $userid            The user id.
     * @param bool $includealternate Whether to include the user's alternate name.
     * @return string $fullusername  The full participant username
     */
    public static function get_participant_name(int $userid, bool $includealternate = true);

    /**
     * Get student's enrolment date.
     *
     * @param int $studentid        The student id in reference
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date(int $courseid, int $studentid);

    /**
     * Returns the timestamp of the last
     * graded session.
     *
     * @param int $userid       The user id
     * @param int $courseid     The course id
     * @param bool $is_student  The participant is a student?
     * @return  stdClass The record containing timestamp of the last grading
     */
    public function get_last_graded_date(int $userid, int $courseid, bool $is_student);

    /**
     * Returns the list of completed lesson ids
     * for a student in a course.
     *
     * @param   int     The student user id
     * @param   int     The course id
     * @return  array   List of completed lesson ids
     */
    public function get_student_completed_lesson_ids(int $userid, int $courseid);

    /**
     * Returns the list of incomplete lessons for a student
     * prior to the upcoming next exercise.
     *
     * @param   int     The student user id
     * @param   int     The course id
     * @param   int     The next exercise id
     * @return  array   List of incomplete lesson mod ids
     */
    public function get_student_incomplete_lesson_ids(int $userid, int $courseid, int $nextexercise);

    /**
     * Updates a user's profile field with a value
     *
     * @param   int    $userid  The student user id
     * @param   string $field   The field to be updated
     * @param   mixed  $value   The value to update to
     * @return  bool            Whether the comment was updated or not.
     */
    public function update_participant_field(int $userid, string $field, $value);

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param int   $courseid   The course the student is being unenrolled from.
     * @param int   $studentid  The student id in reference
     * @param int   $status     The status of the enrolment suspended = 1
     * @return bool             The result of the suspension action.
     */
    public function suspend(int $courseid, int $studentid, int $status);
}
