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
     * @param bool   $courseid    The course id.
     * @param int    $userid      The student user id in the booking.
     * @param bool   $isstudent   Whether to get student bookings
     * @param bool   $oldestfirst Sort order of the returned records.
     * @param bool   $activeonly  Retrieve active bookings only.
     * @return array {Object}
     */
    public static function get_bookings(int $courseid, int $userid, bool $isstudent, bool $oldestfirst = false, bool $activeonly = true) {
        global $DB;

        $sql = 'SELECT b.id, b.userid, b.courseid, b.studentid, b.exerciseid,
                       b.slotid, b.confirmed, b.noshow, b.active, b.timemodified
                FROM {' . static::DB_BOOKINGS. '} b
                INNER JOIN {' . static::DB_SLOTS . '} s on s.id = b.slotid
                WHERE b.courseid = :courseid
                    AND ' . ($isstudent ? 'b.studentid' : 'b.userid') . ' = :userid' .
                    ($activeonly ? ' AND b.active = 1' : '') .
                    ($oldestfirst ? ' ORDER BY s.starttime' : '');

        return $DB->get_records_sql($sql, ['courseid'=>$courseid, 'userid'=>$userid]);
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
     * @param int $courseid      The associated course
     * @param int $studentid     The student id conducted the session
     * @param int $exerciseid    The exercise id for the session
     * @return DateTime $exercisedate The date of last session for that exercise
     */
    public static function get_booked_exercise_date(int $courseid, int $studentid, int $exerciseid) {
        global $DB;

        $sql = 'SELECT s.starttime as exercisedate
                FROM {' . static::DB_BOOKINGS. '} b
                INNER JOIN {' . static::DB_SLOTS. '} s ON s.id = b.slotid
                WHERE b.courseid = :courseid
                AND b.studentid = :studentid
                AND b.exerciseid = :exerciseid
                ORDER BY b.timemodified DESC LIMIT 1';

        $params = [
            'courseid'   => $courseid,
            'studentid'  => $studentid,
            'exerciseid' => $exerciseid
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
    public static function get_last_booked_session(int $courseid, int $userid, bool $isinstructor = false) {
        global $DB;

        $sql = 'SELECT s.starttime as lastbookedsession
                FROM {' . static::DB_BOOKINGS. '} b
                INNER JOIN {' . static::DB_SLOTS. '} s ON s.id = b.slotid
                WHERE b.courseid = :courseid AND ' . ($isinstructor ? 'b.userid = :userid' : 'b.studentid = :userid') . '
                ORDER BY b.timemodified DESC
                LIMIT 1';

        return $DB->get_record_sql($sql, ['courseid'=>$courseid, 'userid'=>$userid]);
    }

    /**
     * Get the date of the last booked session
     *
     * @param int $isinstructor
     * @param int $userid
     * @return int totalsessions
     */
    public static function get_user_total_sessions(int $courseid, int $userid, bool $isinstructor = false) {
        global $DB;

        $sql = 'SELECT COUNT(id) as totalsessions
                FROM {' . static::DB_BOOKINGS. '}
                WHERE courseid = :courseid AND ' . ($isinstructor ? 'userid = :userid' : 'studentid = :userid');

        return $DB->get_record_sql($sql, ['courseid'=>$courseid, 'userid'=>$userid])->totalsessions;
    }

    /**
     * Set active flag to false to deactive the booking
     * and set no-show status of the booking accordingly
     *
     * @param int  $bookingid The booking id in reference.
     * @param bool $noshow    Whether the student didn't show for the booked session
     * @return bool
     */
    public static function set_booking_inactive(int $bookingid, bool $noshow = false) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_BOOKINGS . '}
                SET active = 0 ' . ($noshow ? ', noshow = 1' : '') . '
                WHERE id = :bookingid';

        return $DB->execute($sql, ['bookingid'=>$bookingid]);
    }

    /**
     * Get an array of no-show bookings for a student in a course.
     *
     * @param int $courseid  The associated course
     * @param int $studentid The student id conducted the session
     * @return array
     */
    public static function get_noshow_bookings(int $courseid, int $studentid) {
        global $DB;

        // get the latest no-show date where all no-shows between this date and evaluation period are included (since date)
        $sincedate = 0;
        $sql = 'SELECT s.starttime AS sincedate
            FROM {' . static::DB_BOOKINGS . '} b
            INNER JOIN {' . static::DB_SLOTS. '} s ON s.id = b.slotid
            WHERE b.courseid = :courseid
                AND b.studentid = :studentid
                AND b.noshow = 1
            ORDER BY s.starttime DESC';
        $params = [
            'courseid'   => $courseid,
            'studentid'  => $studentid,
            'noshow' => 1
        ];
        // get the last no-show date to determine period
        $noshowrecs = $DB->get_records_sql($sql, $params);
        if (count($noshowrecs)) {
            $sincedate = strtotime('-' . LOCAL_BOOKING_NOSHOWPERIOD . ' day', array_values($noshowrecs)[0]->sincedate);
        }

        // get no-show records since a specific date
        if ($sincedate) {
            $sql = 'SELECT b.timemodified, s.starttime, b.exerciseid
                    FROM {' . static::DB_BOOKINGS . '} b
                    INNER JOIN {' . static::DB_SLOTS. '} s ON s.id = b.slotid
                    WHERE b.courseid = :courseid
                        AND b.studentid = :studentid
                        AND b.noshow = 1
                        AND b.timemodified >= :sincedate
                    ORDER BY b.timemodified DESC';

            // add since date to the array
            $params['sincedate'] = $sincedate;

            $noshowrecs = $DB->get_records_sql($sql, $params);
        }

        return $noshowrecs;
    }
}