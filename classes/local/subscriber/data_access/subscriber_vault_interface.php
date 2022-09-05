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
 * Class interface for data access of subscribing course and module data
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\subscriber\data_access;

defined('MOODLE_INTERNAL') || die();

interface subscriber_vault_interface {

    /**
     * Returns the course section name containing the exercise
     *
     * @param int $courseid The course id of the section
     * @param int $exerciseid The exercise id in the course inside the section
     * @return array  The section name of a course associated with the exercise
     */
    public static function get_subscriber_section(int $courseid, int $exerciseid);

    /**
     * Retrieves exercises for the course
     *
     * @param int $courseid                  The course id of the section
     * @return array
     */
    public static function get_subscriber_exercises(int $courseid);

    /**
     * Returns the subscribed course exercise by name
     *
     * @param int $courseid The course id of the section
     * @param string $exercisename The graduation exercise name
     * @return int  The exercise id of the graduation last exercise
     */
    public static function get_subscriber_exercise_by_name(int $courseid, string $exercisename);

    /**
     * Returns the subscribed course last exercise
     *
     * @param int $courseid The course id of the section
     * @return int  The exercise id of the graduation last exercise
     */
    public static function get_subscriber_last_exercise(int $courseid);

    /**
     * Retrieves the exercise name of a specific exercise
     * based on its id statically.
     *
     * @param int $exerciseid The exercise id.
     * @return string
     */
    public static function get_subscriber_exercise_name(int $exerciseid);

    /**
     * Retrieves the number of modules for a specific exercise course.
     *
     * @param int $courseid The course id
     * @return int
     */
    public static function get_subscriber_modules_count(int $courseid);

    /**
     * Returns the first file stored for the context id and grade id (itemid)
     *
     * @param int $contextid The context id for the exercise (assignment)
     * @param int $itemid    The context id for the exercise (assignment)
     * @return object  The file record
     */
    public static function get_file_info(int $contextid, int $itemid);
}
