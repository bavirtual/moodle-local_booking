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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\slot\data_access;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\slot\entities\slot;

/**
 * Interface for an slot vault class
 *
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface slot_vault_interface {

    /**
     * Get a based on its id
     *
     * @param int       $slot The id of the slot
     * @return slot     The slot object from the id
     */
    public static function get_slot(int $slotid);

    /**
     * Get a list of slots for the user
     *
     * @param int  $courseid  The course id
     * @param int  $studentid The student id
     * @param int  $week      The week of the slots
     * @param int  $year      The year of the slots
     * @param bool $notified  Whether slot notification was sent
     * @return array
     */
    public static function get_slots(int $courseid, int $studentid, $week = 0, $year = 0, $notified = false);

    /**
     * Saves the passed slot
     *
     * @param slot_interface $slot
     */
    public static function save_slot(slot $slot);

    /**
     * Delete all slots for a user that fall on a specific year and week.
     *
     * @param int|null              $userid     slots for this user
     * @param int|null              $year       slots that fall in this year
     * @param int|null              $week       slots that fall in this week
     *
     * @return result               result
     */
    public static function delete_slots(
        $course = 0,
        $year = 0,
        $week = 0,
        $userid = 0,
        $useredits = true
    );

    /**
     * Update the specified slot status and bookinginfo
     *
     * @param slot $slot The slot to be confirmed
     */
    public static function confirm_slot(slot $slot, string $bookinginfo);

    /**
     * Get the date of the last posted availability slot
     *
     * @param int $studentid
     */
    public static function get_first_posted_slot(int $studentid);

    /**
     * Get the date of the last booked availability slot
     *
     * @param int $courseid
     * @param int $studentid
     * @return array $lastslotdate, $beforelastslotdate
     */
    public static function get_last_booked_slot(int $courseid, int $studentid);

    /**
     * Returns the total number of active posts.
     *
     * @param   int     The course id
     * @param   int     The student id
     * @return  int     The number of active posts
     */
    public static function get_slot_count(int $courseid, int $studentid);
}
