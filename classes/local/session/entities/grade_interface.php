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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\entities;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for a grade class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface grade_interface {

    /**
     * Get the exercise assignment.
     *
     * @return \assign
     */
    public function get_assignment();

    /**
     * Get the studnet grade exercise id of the grade.
     *
     * @return int
     */
    public function get_exercise_id();

    /**
     * Get the studnet user id of the grade.
     *
     * @return int
     */
    public function get_userid();

    /**
     * Get the user grade item for the grade.
     *
     * @param int $attempt The assignment grade attempt to be evaluate.
     * @return stdClass
     */
    public function get_user_grade_attempt(int $attempt = 0);

    /**
     * Get subscribing course grading item for a module
     *
     * @param int      $courseid The subscribing course id
     * @param \cm_info $mod      The exercise module requiring the grade item
     * @return array
     */
    public static function get_grading_item(int $courseid, \cm_info $mod);

    /**
     * Get grade name
     *
     * @param int $finalgrade The final grade
     * @return array
     */
    public function get_grade_name(int $finalgrade);

    /**
     * Get the student's grade feedback comments.
     *
     * @return string
     */
    public function get_feedback_comments();

    /**
     * Get the grade feedback file.
     *
     * @param string $component The assignment component
     * @param string $filearea  The assignment file area
     * @param string $itemid    The assignment grade item
     * @param bool   $path      Whether to return the path or the Stored_file
     * @param int    $attempt The assignment grade attempt to be evaluate.
     * @return string
     */
    public function get_feedback_file(string $component, string $filearea, string $itemid = '', $path = true, int $attempt = 0);

    /**
     * Get the grade feedback file.
     *
     * @param string $feedbackfile The feedback file path & name to be uploaded
     * @param bool   $path         Whether to return the path or the Stored_file
     * @param int    $attempt The assignment grade attempt to be evaluate.
     * @return string|\stored_file
     */
    public function save_feedback_file(string $feedbackfile, $path = true, int $attempt = 0);

    /**
     * Get the student's rubric grade info.
     *
     * @param int    $attempt The assignment grade attempt to be evaluate.
     * @return string[]
     */
    public function get_graderubric(int $attempt = 0);

    /**
     * Whether the grade has rubric grading.
     *
     * @return bool
     */
    public function has_rubric();
}
