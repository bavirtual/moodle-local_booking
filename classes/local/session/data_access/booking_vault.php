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

use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;

class booking_vault implements booking_vault_interface {

    /** Bookings table name for the persistent. */
    const DB_BOOKINGS = 'local_booking_sessions';

    /** Availability Slots table name for the persistent. */
    const DB_SLOTS = 'local_booking_slots';

    /**
     * get booked sessions for a user
     *
     * @param int    $userid of the student in the booking.
     * @param bool   $oldestfirst sort order of the returned records.
     * @return array {Object}
     */
    public static function get_bookings(int $userid, bool $oldestfirst = false) {
        global $DB;

        $sql = 'SELECT b.id, b.userid, b.courseid, b.studentid, b.exerciseid,
                       b.slotid, b.confirmed, b.active, b.timemodified
                FROM {' . static::DB_BOOKINGS. '} b
                INNER JOIN {' . static::DB_SLOTS . '} s on s.id = b.slotid
                WHERE b.userid = :userid
                AND b.active = 1' .
                ($oldestfirst ? ' ORDER BY s.starttime' : '');

        return $DB->get_records_sql($sql, ['userid'=>$userid]);
    }

    /**
     * Get booking based on passed object.
     *
     * @param booking $booking
     * @return Object
     */
    public static function get_booking($booking) {
        global $DB;

        $conditions = [];
        if (!empty($booking->get_id())) {
            $conditions['id'] = $booking->get_id();
        }
        if (!empty($booking->get_courseid())) {
            $conditions['courseid'] = $booking->get_courseid();
        }
        if (!empty($booking->get_studentid())) {
            $conditions['studentid'] = $booking->get_studentid();
        }
        if (!empty($booking->get_exerciseid())) {
            $conditions['exerciseid'] = $booking->get_exerciseid();
        }
        if (!empty($booking->active())) {
            $conditions['active'] = '1';
        }

        return $DB->get_record(static::DB_BOOKINGS, $conditions);
    }

    /**
     * remove all bookings for a user for a
     *
     * @param string $username The username.
     * @return bool
     */
    public static function delete_booking(booking $booking) {
        global $DB;

        $conditions = !empty($booking->get_id()) ? [
            'id' => $booking->get_id()
            ] : [
            'courseid'  => $booking->get_courseid(),
            'studentid' => $booking->get_studentid(),
            'exerciseid'=> $booking->get_exerciseid()
        ];

        // start a transaction
        $transaction = $DB->start_delegated_transaction();

        if ($result = $DB->delete_records(static::DB_BOOKINGS, $conditions)) {
            if ($result = ($booking->get_slot())->delete()) {
                $transaction->allow_commit();
            }
        }

        if (!$result) {
            $transaction->rollback(new \moodle_exception(get_string('bookingsaveunable', 'local_booking')));
        }

        return $result;
    }

    /**
     * save a booking
     *
     * @param {booking} $booking
     * @return bool
     */
    public static function save_booking(booking $booking) {
        global $DB;

        $sessionrecord = new \stdClass();
        $sessionrecord->userid       = $booking->get_instructorid();
        $sessionrecord->courseid     = $booking->get_courseid();
        $sessionrecord->studentid    = $booking->get_studentid();
        $sessionrecord->exerciseid   = $booking->get_exerciseid();
        $sessionrecord->slotid       = ($booking->get_slot())->get_id();
        $sessionrecord->timemodified = time();

        return $DB->insert_record(static::DB_BOOKINGS, $sessionrecord);
    }

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
    public static function confirm_booking(int $courseid, int $studentid, int $exerciseid, slot $slot, string $confirmationmsg) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_BOOKINGS . '}
                SET confirmed = 1
                WHERE courseid = :courseid
                AND studentid = :studentid
                AND exerciseid = :exerciseid';

        $params = [
            'courseid' => $courseid,
            'studentid'  => $studentid,
            'exerciseid'  => $exerciseid
        ];

        // confirm the booking and slot as well
        $transaction = $DB->start_delegated_transaction();

        if ($result = $DB->execute($sql, $params)) {
            if ($result = $slot->confirm($confirmationmsg)) {
                $transaction->allow_commit();
            }
        }

        if (!$result) {
            $transaction->rollback(new \moodle_exception(get_string('bookingconfirmunable', 'local_booking')));
        }

        return $result;
    }

    /**
     * Get the date of the booked exercise
     *
     * @param int $studentid
     * @param int $exerciseid
     * @return int $exercisedate
     */
    public static function get_booked_exercise_date(int $studentid, int $exerciseid) {
        global $DB;

        $sql = 'SELECT timemodified as exercisedate
                FROM {' . static::DB_BOOKINGS. '}
                WHERE studentid = :studentid
                AND exerciseid = :exerciseid';

        $params = [
            'studentid'  => $studentid,
            'exerciseid'  => $exerciseid
        ];

        $booking = $DB->get_record_sql($sql, $params);
        return $booking ? $booking->exercisedate : 0;
    }

    /**
     * Get the date of the last booked session
     *
     * @param int $isinstructor
     * @param int $userid
     */
    public static function get_last_booked_session(int $userid, bool $isinstructor = false) {
        global $DB;

        $sql = 'SELECT timemodified as lastbookedsession
                FROM {' . static::DB_BOOKINGS. '}
                WHERE ' . ($isinstructor ? 'userid = :userid' : 'studentid = :userid') . '
                ORDER BY timemodified DESC
                LIMIT 1';

        return $DB->get_record_sql($sql, ['userid'=>$userid]);
    }

    /**
     * set active flag to false to deactive the booking.
     *
     * @param booking $booking The booking in reference.
     * @return bool
     */
    public static function set_booking_inactive($booking) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_BOOKINGS . '}
                SET active = 0
                WHERE courseid = :courseid
                AND studentid = :studentid
                AND exerciseid = :exerciseid';

        $params = [
            'courseid' => $booking->get_courseid(),
            'studentid'  => $booking->get_studentid(),
            'exerciseid'  => $booking->get_exerciseid()
        ];

        return $DB->execute($sql, $params);
    }
}