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

namespace local_booking\local\subscriber;

defined('MOODLE_INTERNAL') || die();

interface subscriber_interface {

    /**
     * Get all active students.
     *
     * @return {Object}[]   Array of active students.
     */
    public function get_active_students();

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
     * Get all active instructors for the course.
     *
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_participants();

    /**
     * Retrieves exercises for the course
     *
     * @return array
     */
    public function get_exercises();

    /**
     * Retrieves the exercise name of a specific exercise
     * based on its id statically.
     *
     * @param int $exerciseid The exercise id.
     * @return string
     */
    public static function get_exercise_name($exerciseid);
}