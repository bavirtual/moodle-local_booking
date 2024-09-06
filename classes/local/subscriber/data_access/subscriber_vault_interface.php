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
 * slot vault interface
 *
 * @package    local_booking
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace local_booking\local\subscriber\data_access;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for the subscriber_vault class
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface subscriber_vault_interface {

    /**
     * Retreive a data point from the stats table
     *
     * @param int    $courseid  The course id
     * @param int    $userid    The user id
     * @param string $stat      The stat field being update
     * @return bool             The result
     */
    public static function get_subscriber_stat(int $courseid, int $userid, string $stat);

    /**
     * Updates the stats table with a specific value
     *
     * @param int    $courseid  The course id
     * @param int    $userid    The user id
     * @param string $stat      The stat field being update
     * @param string $value     The field value being update
     * @return bool             The result
     */
    public static function update_subscriber_stat(int $courseid, int $userid, string $stat, $value);

    /**
     * Updates the stats table with a lastest lesson completed
     *
     * @param int    $courseid  The course id
     * @param int    $userid    The user id
     * @return bool             The result
     */
    public static function update_subscriber_lessonscomplete_stat(int $courseid, int $userid);

    /**
     * Get a based on its id
     *
     * @param int   $courseid The course id
     * @return bool           Whether the course is subscribed or not
     */
    public static function is_course_enabled(int $courseid);

    /**
     * Get a based on its id
     *
     * @param int   $courseid The course id
     * @return bool           Whether the course is subscribed or not
     */
    public static function add_new_subscriber_enrolments(int $courseid);
}
