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
 * Calendar slot class.
 *
 * @package    core_calendar
 * @copyright  2017 Cameron Ball <cameron@cameron1729.xyz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\slot\entities;

use local_booking\local\slot\data_access\slot_vault;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a calendar slot.
 *
 * @copyright 2017 Cameron Ball <cameron@cameron1729.xyz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slot implements slot_interface {
    /**
     * @var int $id The slot's id in the database.
     */
    protected $id;

    /**
     * @var int $userid The slot's userid in the database.
     */
    protected $userid;

    /**
     * @var int $courseid The slot's courseid in the database.
     */
    protected $courseid;

    /**
     * @var int $starttime The slot's start time in the database.
     */
    protected $starttime;

    /**
     * @var int $endtime The slot's end time in the database.
     */
    protected $endtime;

    /**
     * @var int $year The slot's year in the database.
     */
    protected $year;

    /**
     * @var int $week The slot's week in the database.
     */
    protected $week;

    /**
     * @var string $slotstatus The slot's booking stauts in the database.
     */
    protected $slotstatus;

    /**
     * @var string $bookinginfo The slot's booking info in the database.
     */
    protected $bookinginfo;

    /**
     * Constructor.
     *
     * @param int                        $id            The slot's ID in the database.
     * @param int                        $userid        The slot's user id in the database.
     * @param int                        $courseid      The slot's course id in the database.
     * @param int                        $starttime     The slot's start time in the database.
     * @param int                        $endtime       The slot's end time in the database.
     * @param int                        $year          The slot's year in the database.
     * @param int                        $week          The slot's week in the database.
     * @param string                     $slotstatus    The slot's booking status in the database.
     * @param string                     $bookinginfo   The slot's booking info in the database.
     */
    public function __construct(
        $id = 0,
        $userid = 0,
        $courseid = 0,
        $starttime = 0,
        $endtime = 0,
        $year = 0,
        $week = 0,
        $slotstatus = '',
        $bookinginfo = ''
    ) {
        $this->id = $id;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->starttime = $starttime;
        $this->endtime = $endtime;
        $this->year = $year;
        $this->week = $week;
        $this->slotstatus = $slotstatus;
        $this->bookinginfo = $bookinginfo;
    }

    /**
     * Loads the slot from the database.
     *
     */
    public function load() {
        $slotrec = slot_vault::get_slot($this->id);
        if (!empty($slotrec)) {
            $this->id = $slotrec->id;
            $this->userid = $slotrec->userid;
            $this->courseid = $slotrec->courseid;
            $this->starttime = $slotrec->starttime;
            $this->endtime = $slotrec->endtime;
            $this->year = $slotrec->year;
            $this->week = $slotrec->week;
            $this->slotstatus = $slotrec->slotstatus;
            $this->bookinginfo = $slotrec->bookinginfo;
        }
    }

    /**
     * Saves this slot to the database.
     *
     */
    public function save() {
        $vault = new slot_vault();
        $this->id = $vault->save_slot($this);
        return $this->id != 0;
    }

    /**
     * Deletes this slot from the database.
     *
     */
    public function delete() {
        $vault = new slot_vault();

        return $vault->delete_slot($this->id);
    }

    /**
     * Confirm this slot.
     *
     * @return bool
     */
    public function confirm(string $bookinginfo) {
        return slot_vault::confirm_slot($this, $bookinginfo);
    }

    /**
     * Get the slot id.
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get the slot user id.
     *
     * @return int
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * Get the course id of this slot.
     *
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Get the start timestamp of this slot.
     *
     * @return int
     */
    public function get_starttime() {
        return $this->starttime;
    }

    /**
     * Get the end timestamp of this slot.
     *
     * @return int
     */
    public function get_endtime() {
        return $this->endtime;
    }

    /**
     * Get the year of this slot.
     *
     * @return int
     */
    public function get_year() {
        return $this->year;
    }

    /**
     * Get the week of year for this slot.
     *
     * @return int
     */
    public function get_week() {
        return $this->week;
    }

    /**
     * Get the status of this slot.
     *
     * @return int
     */
    public function get_slotstatus() {
        return $this->slotstatus;
    }

    /**
     * Get the associated booking info for this slot.
     *
     * @return int
     */
    public function get_bookinginfo() {
        return $this->bookinginfo;
    }

    /**
     * Get the date of the last booked availability slot
     *
     * @param int $courseid
     * @param int $studentid
     */
    public static function get_last_booking(int $courseid, int $studentid) {
        list($lastsession, $beforelastsession) = slot_vault::get_last_booked_slot($courseid, $studentid);
        return $lastsession;
    }
}
