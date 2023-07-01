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
     * get booked sessions for a user
     *
     * @param bool   $courseid    The course id.
     * @param int    $userid      The student user id in the booking.
     * @param bool   $isstudent   Whether to get student bookings
     * @param bool   $oldestfirst Sort order of the returned records.
     * @param bool   $activeonly  Retrieve active bookings only.
     * @param bool   $allcourses  Retrieve bookings for all courses.
     * @return array {Object}
     */
    public static function get_bookings(int $courseid, int $userid, bool $isstudent, bool $oldestfirst = false, bool $activeonly = true, bool $allcourses = false);

    /**
     * Get booking based on passed object.
     *
     * @param booking $booking
     * @return Object
     */
    public static function get_booking(booking $booking);

    /**
     * Get the date of the booked exercise
     *
     * @param int $courseid      The associated course
     * @param int $studentid     The student id conducted the session
     * @param int $exerciseid    The exercise id for the session
     * @return DateTime $exercisedate The date of last session for that exercise
     */
    public static function get_booked_exercise_date(int $courseid, int $studentid, int $exerciseid);

    /**
     * Get the date of the last booked session
     *
     * @param int $instructorid
     */
    public static function get_last_booked_session(int $courseid, int $userid, bool $isinstructor = false);

    /**
     * Get an array of booked session count for each exercise for the user.
     *
     * @param int $courseid The associated course
     * @param int $userid   The user id conducting the session
     * @return array
     */
    public static function get_user_total_booked_sessions(int $courseid, int $userid);

    /**
     * Set active flag to false to deactive the booking
     * and set no-show status of the booking accordingly
     *
     * @param int  $bookingid The booking id in reference.
     * @param bool $noshow    Whether the student didn't show for the booked session
     * @return bool
     */
    public static function set_booking_inactive(int $bookingid, bool $noshow = false);

    /**
     * Get an array of no-show bookings for a student in a course.
     *
     * @param int $courseid  The associated course
     * @param int $studentid The student id conducted the session
     * @return array
     */
    public static function get_noshow_bookings(int $courseid, int $studentid);

    /**
     * Retreives the conflicting booking if exists.
     *
     * @param int $instructorid The instructor id making a booking
     * @param int $studnetid    The student id the booking is for
     * @param int $start        The start & end dates
     * @param itn $end          The start & end dates
     * @return {object?}
     */
    public static function get_booking_conflict(int $instructorid, int $studentid, int $start, int $end);
}
