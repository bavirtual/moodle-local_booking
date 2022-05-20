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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\subscriber\entities;

defined('MOODLE_INTERNAL') || die();

interface subscriber_interface {

    /**
     * Get the subscriber's course id.
     *
     * @return int $courseid
     */
    public function get_id();

    /**
     * Get the subscriber's course context.
     *
     * @return \context_course $context
     */
    public function get_context();

    /**
     * Get the subscriber's course shortname.
     *
     * @return string $shortname
     */
    public function get_shortname();

    /**
     * Set the subscriber's course shortname.
     *
     * @param string $shortname
     */
    public function set_shortname(string $shortname);

    /**
     * Get an active participant.
     *
     * @param int $participantid A participant user id.
     * @param bool $populate     Whether to get the participant data.
     * @param bool $active       Whether the participant is active.
     * @return participant       The participant object
     */
    public function get_participant(int $participantid, bool $populate = false, bool $active = true);

    /**
     * Get all active instructors for the course.
     *
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_participants();

    /**
     * Get a student.
     *
     * @param int  $studentid   A participant user id.
     * @param bool $populate    Whether to get the student data.
     * @param bool $active      Whether the student has an active enrolment.
     * @return student          The student object
     */
    public function get_student(int $studentid, bool $populate = false, bool $active = true);

    /**
     * Get an active student.
     *
     * @param int $studentid    A specific student for booking confirmation
     * @return student $student The active student object.
     */
    public function get_active_student(int $studentid);

    /**
     * Get all active students.
     *
     * @param bool $includeonhold    Whether to include on-hold students as well
     * @return array $activestudents Array of active students.
     */
    public function get_active_students(bool $includeonhold = false);

    /**
     * Get an active instructor.
     *
     * @param int $instructorid An instructor user id.
     * @return instructor       The instructor object
     */
    public function get_active_instructor(int $instructorid);

    /**
     * Get all active instructors for the course.
     *
     * @param bool $courseadmins Indicates whether the instructors returned are part of course admins
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_instructors(bool $courseadmins = false);

    /**
     * Get subscribing course senior instructors list.
     *
     * @return {Object}[]   Array of course's senior instructors.
     */
    public function get_senior_instructors();

    /**
     * Returns the course section name containing the exercise
     *
     * @param int $courseid The course id of the section
     * @param int $exerciseid The exercise id in the course inside the section
     * @return string  The section name of a course associated with the exercise
     */
    public static function get_section_name(int $courseid, int $exerciseid);

    /**
     * Returns the course last exercise
     *
     * @param int $courseid The course id of the section
     * @return string  The last exericse id
     */
    public function get_last_exercise();

    /**
     * Retrieves exercises for the course
     *
     * @return array
     */
    public function get_exercises();

    /**
     * Retrieves the total number of modules in a course.
     *
     * @return int
     */
    public function get_modules_count();

    /**
     * Retrieves the exercise name of a specific exercise
     * based on its id statically.
     *
     * @param int $exerciseid The exercise id.
     * @return string
     */
    public static function get_exercise_name($exerciseid);
}
