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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
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
     * @return string
     */
    public function get_feedback_file(string $component, string $filearea);

    /**
     * Get the student's rubric grade info.
     *
     * @return string[]
     */
    public function get_graderubric();

    /**
     * Wether the grade has rubric grading.
     *
     * @return bool
     */
    public function has_rubric();
}
