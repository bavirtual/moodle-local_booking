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
 * Calendar slot interface.
 *
 * @package    local_booking
 * @copyright  2017 Cameron Ball <cameron@cameron1729.xyz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\slot\entities;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for an slot class.
 *
 * @copyright  2017 Cameron Ball <cameron@cameron1729.xyz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface slot_interface {

    /**
     * Loads the slot from the database.
     *
     */
    public function load();

    /**
     * Saves this slot to the database.
     *
     */
    public function save();

    /**
     * Deletes this slot from the database.
     *
     */
    public function delete();

    /**
     * Confirm this slot.
     *
     * @return bool
     */
    public function confirm(string $bookinginfo);

    /**
     * Get the slot's ID.
     *
     * @return integer
     */
    public function get_id();

    /**
     * Get the slot course id.
     *
     * @return integer
     */
    public function get_courseid();

    /**
     * Get the slot's start timestamp.
     *
     * @return integer
     */
    public function get_starttime();

    /**
     * Get the slot's end timestamp.
     *
     * @return integer
     */
    public function get_endtime();

    /**
     * Get the slot's year.
     *
     * @return integer
     */
    public function get_year();

    /**
     * Get the slot's week.
     *
     * @return integer
     */
    public function get_week();

    /**
     * Get the slot's booking status.
     *
     * @return string
     */
    public function get_slotstatus();

    /**
     * Get the slot's booking information.
     *
     * @return string
     */
    public function get_bookinginfo();

    /**
     * Get the date of the last booked availability slot
     *
     * @param int $courseid
     * @param int $studentid
     * @return int
     */
    public static function get_last_booked_slot_date(int $courseid, int $studentid);
}
