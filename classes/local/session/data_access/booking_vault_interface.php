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

use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;

interface booking_vault_interface {

    /**
     * get booked sessions for a user
     *
     * @param int    $userid of the student in the booking.
     * @param bool   $oldestfirst sort order of the returned records.
     * @return array {Object}
     */
    public static function get_bookings(int $userid, bool $oldestfirst = false);

    /**
     * Get booking based on passed object.
     *
     * @param booking $booking
     * @return Object
     */
    public static function get_booking(booking $booking);

    /**
     * Delete a specific booking.
     *
     * @param   booking $booking  The bookingid id to be deleted.
     * @return  bool    $result
     */
    public static function delete_booking(booking $booking);

    /**
     * Saves the passed booked session
     *
     * @param   booking_interface   $session
     * @return  bool                $result
     */
    public static function save_booking(booking $booking);

    /**
     * Confirm the passed book
     *
     * @param   int                 $courseid
     * @param   int                 $studentid
     * @param   int                 $exerciseid
     * @param   slot                $slot
     * @param   string              $confirmationmsg
     * @return  bool                $result
     */
    public static function confirm_booking(int $courseid, int $studentid, int $exerciseid, slot $slot, string $confirmationmsg);

    /**
     * Get the date of the booked exercise
     *
     * @param int $studentid
     * @param int $exerciseid
     */
    public static function get_booked_exercise_date(int $studentid, int $exerciseid);

    /**
     * Get the date of the last booked session
     *
     * @param int $instructorid
     */
    public static function get_last_booked_session(int $userid, bool $isinstructor = false);

    /**
     * set active flag to false to deactive the booking.
     *
     * @param booking $booking The booking in reference.
     * @return bool
     */
    public static function set_booking_inactive($booking);
}
