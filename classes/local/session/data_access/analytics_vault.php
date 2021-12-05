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

use DateTime;
use local_booking\local\participant\entities\student;
use local_booking\local\slot\data_access\slot_vault;

class analytics_vault implements analytics_vault_interface {

    // Bookings table name for querying
    const DB_BOOKINGS = 'local_booking_sessions';

    // Availability Slots table name for querynig
    const DB_SLOTS = 'local_booking_slots';

    // Moodle logstore log table name for querying
    const DB_LOGSTORE = 'logstore_standard_log';

    // lesson_timer table name for querying
    const DB_LESSON = 'lesson';

    // lesson_timer table name for querying
    const DB_LESSONCOMPLETION = 'lesson_timer';

    /**
     * Returns the session recency in days for a particular student.
     * If there is no record of a booking, the grade is returned, and
     * in the case of new students, the enrolment date is returned.
     *
     * @param int   $courseid   The course id in reference
     * @param int   $studentid  The student id in reference
     * @return array            The number of days since last session and recency source information
     */
    public static function get_session_recency(int $courseid, int $studentid) {
        list($lastsession, $beforelastsession) = slot_vault::get_last_booked_slot($courseid, $studentid);
        $today = new DateTime('@' . time());
        $info = [];

        // check for sessions booked but not conducted yet, and if so revert to previous session, otherwise
        // use the last session booked date. If neither are available revert to last graded, then enrol date
        // for the newly enrolled.
        if (!empty($lastsession) && !empty($beforelastsession)) {
            $lastsessiondate = new DateTime('@' . ($lastsession < time() ? $lastsession : $beforelastsession));
            $info['source'] = 'booking';
        } elseif (!empty($lastsession) && $lastsession < time()) {
            $lastsessiondate = new DateTime('@' . $lastsession);
            $info['source'] = 'booking';
        } else {
            $student = new student($courseid, $studentid);
            $lastgraded = $student->get_last_graded_date();
            $lastsessiondate = empty($lastgraded) ? $student->get_enrol_date($studentid) : $lastgraded;
            $info['source'] = empty($lastgraded) ? 'enrol' : 'grade';
        }
        $info['date'] = $lastsessiondate;

        // get the difference between today and the date of the last booked or graded slot
        $days =  (date_diff($lastsessiondate, $today))->days;

        return [$days, $info];
    }

    /**
     * Get the number of Availability slots marked by the student.
     *
     * @param int   $courseid   The course id in reference
     * @param int   $studentid  The student id in reference
     * @return int  $slotcount  The number of availability slots marked by the student.
     */
    public static function get_slot_count(int $courseid, int $studentid) {
        return slot_vault::get_slot_count($courseid, $studentid);
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @param int   $courseid   The course id in reference
     * @param int   $studentid      The student id in reference
     * @return int  $activitycount  The number of activity events in the log.
     */
    public static function get_activity_count(int $courseid, int $studentid) {
        global $DB, $COURSE;

        $activitycount = $DB->count_records(self::DB_LOGSTORE, ['userid' => $studentid, 'courseid' => $COURSE->id]);

        return $activitycount;
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @param int   $courseid   The course id in reference
     * @param int   $studentid      The student id in reference
     * @return int  $completions    The number of lesson completions.
     */
    public static function get_lesson_completions(int $courseid, int $studentid) {
        global $DB;

        $sql = 'SELECT COUNT(DISTINCT l.id) AS lessons
                FROM {' . self::DB_LESSONCOMPLETION . '} lt
        INNER JOIN {' . self::DB_LESSON . '} l ON l.id = lt.lessonid
        WHERE l.course= :courseid
            AND lt.userid= :studentid
            AND lt.completed= :completed';

        $completions = $DB->get_record_sql($sql, ['courseid' => $courseid, 'studentid'=>$studentid, 'completed' => '1']);

        return $completions->lessons;
    }
}