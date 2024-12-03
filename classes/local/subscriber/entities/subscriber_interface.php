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
     * Retrieves a Moodle course based on the courseid.
     *
     * @param int  $courseid  The courseid id.
     * @return string
     */
    public function get_course(int $courseid = 0);

    /**
     * Get the subscriber's course context.
     *
     * @return \context_course $context
     */
    public function get_context();

    /**
     * Get the subscriber's course fullname.
     *
     * @return string $fullname
     */
    public function get_fullname();

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
     * Get all senior instructors for the course.
     *
     * @param bool $rawdata Whether to return participant raw data
     * @return {Object}[]   Array of course's senior instructors.
     */
    public function get_participants(bool $rawdata = false);

    /**
     * Get all active participant names for UI from the database.
     *
     * @param string $filter        The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold   Whether to include on-hold students as well
     * @param string $roles         The roles of the participants
     * @return array                Array of student ids & names
     */
    public function get_participant_names(string $filter = 'active', bool $includeonhold = false, string $roles = null);

    /**
     * Get a student.
     *
     * @param int  $studentid   A participant user id.
     * @param bool $courseid    Course id for student from different course required for instructor's mybookings w/ muultiple courses.
     * @return student          The student object
     */
    public function get_student(int $studentid, int $courseid = 0);

    /**
     * Get students based on filter.
     *
     * @param string $filter       The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold  Whether to include on-hold students as well
     * @param bool $rawdata        Whether to return students raw data
     * @return array $activestudents Array of active students.
     */
    public function get_students(string $filter = 'active', bool $includeonhold = false, bool $rawdata = false);

    /**
     * Get an active instructor.
     *
     * @param int $instructorid An instructor user id.
     * @return instructor       The instructor object
     */
    public function get_instructor(int $instructorid);

    /**
     * Get all active instructors for the course.
     *
     * @param bool $courseadmins Whether the instructors returned are part of course admins
     * @param bool $rawdata      Whether to return instructors raw data
     * @return {Object}[]   Array of active instructors.
     */
    public function get_instructors(bool $courseadmins = false, bool $rawdata = false);

    /**
     * Get subscribing course senior instructors list.
     *
     * @return {Object}[]   Array of course's senior instructors.
     */
    public function get_senior_instructors();

    /**
     * Get subscribing course Flight Training Managers.
     *
     * @return array The Flight Training Manager users.
     */
    public function get_flight_training_managers();

    /**
     * Retrieves subscribing course roles
     *
     * @return array
     */
    public function get_roles();

    /**
     * Retrieves subscribing course modules (exercises & quizes)
     *
     * @param  bool $visible    Whether the modules to return are visible
     * @return array
     */
    public function get_modules(bool $visibleonly = false);

    /**
     * Retrieves subscribing course lessons
     *
     * @return array
     */
    public function get_lessons();

    /**
     * Returns the subscribed course lesson by the lesson module id
     *
     * @param int $lessonid The lesson id
     * @return stdClass  The lesson module
     */
    public function get_lesson_module(int $lessonid);

    /**
     * Returns the subscribed course section id and lesson name that contains the exercise
     *
     * @param int $exerciseid The exercise id in the course inside the section
     * @return array  The section name of a course associated with the exercise
     */
    public function get_lesson_by_exercise_id(int $exerciseid);

    /**
     * Retrieves subscribing course modules (exercises & quizes)
     *
     * @return array
     */
    public function get_exercises();

    /**
     * Retrieves a specific exercise object
     * based on its id, and optionally course.
     *
     * @param int  $exerciseid The exercise id.
     * @param int  $courseid   The course id the exercise belongs to.
     * @return object
     */
    public function get_exercise(int $exerciseid);

    /**
     * Returns the course graduation exercise the last exercise
     * the student takes before graduating the course
     *
     * @return int The last exericse id
     */
    public function get_graduation_exercise_id();

    /**
     * Get subscribing course grading item for a module
     *
     * @param int  $modid The exercise id requiring the grade item
     * @return array
     */
    public function get_grading_item(int $modid);

    /**
     * Retrieves an array with the moodle file path and file name of a course file resource.
     *
     * @param  string The resource module name
     * @return array
     */
    public function get_moodlefile(string $resourcename);

    /**
     * Returns the settings from config.xml
     *
     * @param  string $key      The key to look up the value
     * @return mixed  $config   The requested setting value.
     */
    public static function get_booking_config(string $key);

    /**
     * Returns an array of records from integrated external database
     * that matches the passed criteria.
     *
     * @param string $key    The key associated with the integration.
     * @param string $target The target data structure of the integration.
     * @param string $value  The data selection criteria
     * @return array
     */
    public function get_external_data($key, $data, $value);

    /**
     * Checks if the subscribing course require
     * skills evaluation.
     *
     * @return bool
     */
    public function requires_skills_evaluation();

    /**
     * Checks if the passed course is a subscriber 'enabled'
     *
     * @param int $courseid
     * @return bool
     */
    public static function is_subscribed(int $courseid);

    /**
     * Checks if the subscribed course has any student status or not.
     * If not then the course is new subscriber.
     *
     * @param int $courseid
     * @return bool
     */
    public static function stats_exist(int $courseid);

    /**
     * Adds students stats for a newly enabled course subscriber
     *
     * @param int $courseid
     * @return bool
     */
    public static function add_new_enrolments(int $courseid);

    /**
     * Removes user stats data once student is unenroled from the course
     *
     * @param int $courseid The subscribing course
     * @param int $userid   The assign module id
     * @return bool
     */
    public static function delete_enrolment_stats(int $courseid, int $userid);

    /**
     * Whether the course requires students to complete lessons
     * prior to an air exercise
     *
     * @return bool
     */
    public function requires_lesson_completion();

    /**
     * Checks if there is a database integration
     * for the specified passed key.
     *
     * @param string $root The root node in the integration json.
     * @param string $key  The key associated with the integration.
     * @return bool
     */
    public static function has_integration($root, $key);

    /**
     * Forces completion of the subscribed course for a specific student.
     * This function is to fix eliminate legacy enrolments
     *
     * @param int $studentid    The user id for the student to force course completion for
     */
    public function force_student_course_completion(int $studentid);
}
