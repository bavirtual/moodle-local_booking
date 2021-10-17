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

class analytics_vault implements analytics_vault_interface {

    // Bookings table name for querying
    const DB_BOOKINGS = 'local_booking_sessions';

    // Availability Slots table name for querynig
    const DB_SLOTS = 'local_booking_slots';

    // Moodle logstore log table name for querying
    const DB_LOGSTORE = 'logstore_standard_log';

    // lesson_timer table name for querying
    const DB_LESSONCOMPLETION = 'lesson_timer';

    /**
     * Get Session Recency in days for a particular student
     *
     * @param int   $courseid   The course id in reference
     * @param int   $studentid  The student id in reference
     * @return int  $days       The number of days since last session
     */
    public function get_session_recency(int $courseid, int $studentid) {
        global $DB;

        $sql = 'SELECT endtime AS lastsessiondate
                FROM {' . self::DB_SLOTS . '}
                WHERE userid = :studentid
                ORDER BY endtime desc LIMIT 1';

        $rs = $DB->get_record_sql($sql, ['studentid'=>$studentid]);

        if (!empty($rs)) {
            $lastsessiondate = new DateTime('@' . $rs->lastsessiondate);
        } else {
            $student = new student($courseid, $studentid);
            $lastsessiondate = $student->get_enrol_date($studentid);
        }

        $today = new DateTime('@' . time());
        $interval = date_diff($lastsessiondate, $today);
        $days = $interval->days;

        return $days;
    }

    /**
     * Get the number of Availability slots marked by the student.
     *
     * @param int   $studentid  The student id in reference
     * @return int  $slotcount  The number of availability slots marked by the student.
     */
    public function get_slot_count(int $studentid) {
        global $DB;

        $slotcount = $DB->count_records(self::DB_SLOTS, ['userid' => $studentid]);

        return $slotcount;
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @param int   $studentid      The student id in reference
     * @return int  $activitycount  The number of activity events in the log.
     */
    public function get_activity_count(int $studentid) {
        global $DB, $COURSE;

        $activitycount = $DB->count_records(self::DB_LOGSTORE, ['userid' => $studentid, 'courseid' => $COURSE->id]);

        return $activitycount;
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @param int   $studentid      The student id in reference
     * @return int  $completions    The number of lesson completions.
     */
    public function get_lesson_completions(int $studentid) {
        global $DB;

        $completions = $DB->count_records(self::DB_LESSONCOMPLETION, ['userid' => $studentid, 'completed' => '1']);

        return $completions;
    }
}