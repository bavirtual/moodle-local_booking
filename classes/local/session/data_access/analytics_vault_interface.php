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

namespace local_booking\local\session\data_access;

defined('MOODLE_INTERNAL') || die();

interface analytics_vault_interface {

    /**
     * Get Session Recency in days for a particular student
     *
     * @param int   $courseid   The course id in reference
     * @param int   $studentid  The student id in reference
     * @return int  $days       The number of days since last session
     */
    public static function get_session_recency(int $courseid, int $studentid);

    /**
     * Get the number of Availability slots marked by the student.
     *
     * @param int   $studentid  The student id in reference
     * @return int  $slotcount  The number of availability slots marked by the student.
     */
    public static function get_slot_count(int $studentid);

    /**
     * Get course activity for a student from the logs.
     *
     * @param int   $studentid      The student id in reference
     * @return int  $activitycount  The number of activity events in the log.
     */
    public static function get_activity_count(int $studentid);

    /**
     * Get course activity for a student from the logs.
     *
     * @param int   $studentid      The student id in reference
     * @return int  $completions    The number of lesson completions.
     */
    public static function get_lesson_completions(int $studentid);
}
