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
 * Contains event class for displaying the week view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\slot\data_access;

use local_booking\local\slot\entities\slot;

class slot_vault implements slot_vault_interface {

    /** Table name for the persistent. */
    const DB_SLOTS = 'local_booking_slots';

    /**
     * Get a based on its id
     *
     * @param int       $slot The id of the slot
     * @return slot     The slot object from the id
     */
    public static function get_slot(int $slotid) {
        global $DB;

        return $DB->get_record(static::DB_SLOTS, ['id' => $slotid]);
    }

    /**
     * Get a list of slots for the user
     *
     * @param int $studentid The student id
     * @param int $year The year of the slots
     * @param int $week The week of the slots
     * @return array
     */
    public static function get_slots($studentid, $week = 0, $year = 0) {
        global $DB;


        $condition = [
            'userid' => $studentid,
            'week'   => $week,
            'year'   => $year
        ];

        return $DB->get_records(static::DB_SLOTS, $condition, 'slotstatus');
    }

    /**
     * save a slot
     *
     * @param string $slot
     * @return bool
     */
    public static function save_slot(slot $slot) {
        global $DB, $USER;

        $slotrecord = new \stdClass();
        $slotrecord->userid = $slot->get_userid() == 0 ? $USER->id : $slot->get_userid();
        $slotrecord->courseid = $slot->get_courseid();
        $slotrecord->starttime = $slot->get_starttime();
        $slotrecord->endtime = $slot->get_endtime();
        $slotrecord->year = $slot->get_year();
        $slotrecord->week = $slot->get_week();
        $slotrecord->slotstatus = $slot->get_slotstatus();
        $slotrecord->bookinginfo = $slot->get_bookinginfo();

        return $DB->insert_record(static::DB_SLOTS, $slotrecord);
    }

    /**
     * delete specific slot
     *
     * @param int $slotid The slot id.
     * @return bool
     */
    public static function delete_slot($slotid) {
        global $DB;

        return $DB->delete_records(self::DB_SLOTS, ['id' => $slotid]);
    }

    /**
     * remove all records for a user for a
     * specific year and week
     *
     * @param int $course       The associated course.
     * @param int $year         The associated course.
     * @param int $week         The associated course.
     * @param int $userid       The associated course.
     * @param int $useredits    The associated course.
     * @return bool
     */
    public static function delete_slots($course = 0, $userid = 0, $year = 0, $week = 0, $useredits = true) {
        global $DB;

        $condition = [
            'slotstatus'    => '',
            'courseid'      => $course,
            'userid'        => $userid,
        ];
        // don't delete slots with status tentative/booked
        if ($useredits) {
            $condition += [
                'year'          => $year,
                'week'          => $week,
            ];
        }

        return $DB->delete_records(self::DB_SLOTS, $condition);
    }

    /**
     * Update the specified slot status and bookinginfo
     *
     * @param slot $slot The slot to be confirmed
     * @return bool the result of the update
     */
    public static function confirm_slot(slot $slot, string $bookinginfo) {
        global $DB;

        $slotrecord = new \stdClass();
        $slotrecord->id = $slot->get_id();
        $slotrecord->slotstatus = 'booked';
        $slotrecord->bookinginfo = $bookinginfo;

        return $DB->update_record(static::DB_SLOTS, $slotrecord);
    }

    /**
     * Get the date of the last posted availability slot
     *
     * @param int $studentid
     */
    public static function get_first_posted_slot(int $studentid) {
        global $DB;

        $sql = 'SELECT starttime
                FROM {' . static::DB_SLOTS. '}
                WHERE userid = :studentid
                AND slotstatus = ""
                ORDER BY starttime
                LIMIT 1';

        return $DB->get_record_sql($sql, ['studentid'=>$studentid]);
    }

    /**
     * Get the date of the last booked availability slot
     *
     * @param int $courseid
     * @param int $studentid
     */
    public static function get_last_booked_slot(int $courseid, int $studentid) {
        global $DB;

        $sql = 'SELECT starttime
                FROM {' . static::DB_SLOTS. '}
                WHERE courseid = :courseid
                AND userid = :studentid
                AND slotstatus != ""
                ORDER BY starttime DESC
                LIMIT 1';

        $params = [
            'courseid' => $courseid,
            'studentid'  => $studentid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Returns the total number of active posts.
     *
     * @param   int     The course id
     * @param   int     The student id
     * @return  int     The number of active posts
     */
    public static function get_slot_count(int $courseid, int $studentid) {
        global $DB;

        $condition = [
            'courseid'  => $courseid,
            'userid'    => $studentid,
            'slotstatus'=> ''
        ];

        return $DB->count_records(self::DB_SLOTS, $condition);
    }
}