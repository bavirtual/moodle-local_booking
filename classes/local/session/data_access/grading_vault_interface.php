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
 * Class interface for data access of student grading
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\data_access;

defined('MOODLE_INTERNAL') || die();

interface grading_vault_interface {

    /**
     * Returns the timestamp of the last
     * graded session.
     *
     * @param int $userid       The user id
     * @param int $courseid     The course id
     * @param bool $is_student  The participant is a student?
     * @return  stdClass The record containing timestamp of the last grading
     */
    public static function get_last_graded_date(int $userid, int $courseid, bool $is_student);

    /**
     * Get an array of graded session count for each exercise for the user.
     *
     * @param int $isinstructor
     * @param int $userid
     * @return int
     */
    public static function get_user_total_graded_sessions(int $courseid, int $userid);

    /**
     * Get list of student grades for an exercise attempts.
     *
     * @param  int $courseid   The course id
     * @param  int $userid     The user id
     * @param  int $exerciseid The exercise id that was attempted
     * @return array
     */
    public static function get_student_exercise_attempts(int $courseid, int $userid, int $exerciseid);

    /**
     * Get an array of a grade item scale grades
     *
     * @param  int $scaleid    The scale id for the exercise
     * @return array
     */
    public static function get_exercise_gradeitem_scale(int $scaleid);
}
