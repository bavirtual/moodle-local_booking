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

namespace local_booking\local\logbook\entities;

use local_booking\local\logbook\data_access\logbook_vault;
use local_booking\local\participant\entities\participant;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a logbook.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logbook implements logbook_interface {

    /**
     * @var int $courseid The course id of this logbook.
     */
    protected $courseid;

    /**
     * @var int $userid The user user id of this logbook.
     */
    protected $userid;

    /**
     * @var logentry[] $entries The list of logbook entries.
     */
    protected $entries;

    /**
     * Constructor.
     *
     * @param int   $courseid   The course id associated with this logbook.
     * @param int   $userid  The user id associated with this logbook.
     */
    public function __construct($courseid, $userid = 0) {
        $this->courseid = $courseid;
        $this->userid = $userid;
    }

    /**
     * Load and retrieve the logbook of a user.
     *
     * @param  bool $allentries whether to get entries for all courses
     * @return bool true if the Logbook has entries
     */
    public function load(bool $allentries = false) {
        $this->entries = logbook_vault::get_logbook($this->courseid, $this->userid, $this, $allentries);
        return count($this->entries) > 0;
    }

    /**
     * Creates a logbook entry.
     *
     * @return logentry
     */
    public function create_logentry() {
        $logentry = new logentry();
        $logentry->set_parent($this);
        return $logentry;
    }

    /**
     * Save a logbook entry.
     *
     * @param logentry $logentry
     * @return int The id of the logbook entery inserted
     */
    public function insert(logentry $logentry) {
        return logbook_vault::insert_logentry($this->courseid, $this->userid, $logentry);
    }

    /**
     * Update a logbook entry.
     *
     * @param logentry $logentry
     * @return bool
     */
    public function update(logentry $logentry){
        return logbook_vault::update_logentry($this->courseid, $this->userid, $logentry);
    }

    /**
     * Deletes a logbook entry and its associated logentires.
     *
     * @param int $logentryid
     * @return bool
     */
    public function delete(int $logentryid) {
        $logentry = $this->get_logentry($logentryid);
        return logbook_vault::delete_logentry($logentryid, $logentry->get_linkedlogentryid());
    }

    /**
     * Insert/Update then link the instructor
     * and student logbook entries.
     *
     * @param int $courseid
     * @param logentry $instructorlogentry
     * @param logentry $studentlogentry
     * @return bool
     */
    public static function save_linked_logentries(int $courseid, logentry $instructorlogentry, logentry $studentlogentry) {
        return logbook_vault::save_linked_logentries($courseid, $instructorlogentry, $studentlogentry);
    }

    /**
     * Get the logbook of a user.
     *
     * @return logentry[]
     */
    public function get_logentries() {
        return $this->entries;
    }

    /**
     * Load a logbook entry by entry id or exercise id.
     *
     * @param int $logentryid   The logentry with matching id to be retrieved from the logbook.
     * @param int $exerciseid   The logentry with matching exercise id to be retrieved from the logbook.
     * @param int $sessionid   The logentry with matching session id to be retrieved from the logbook.
     * @return logentry
     */
    public function get_logentry(int $logentryid = 0, int $exerciseid = 0, int $sessionid = 0, bool $reload = true) {
        $logentry = null;

        if ($logentryid != 0) {
            $logentry = $reload ?
                logbook_vault::get_logentry($this->userid, $this->courseid, $this, $logentryid) :
                $this->entries[$logentryid];
        } else if ($exerciseid != 0) {
            $logentry = $reload ?
                logbook_vault::get_logentry($this->userid, $this->courseid, $this, 0, $exerciseid) :
                $this->get_logentry_by_exericseid($exerciseid);
        } else if ($sessionid != 0) {
            $logentry = $reload ?
                logbook_vault::get_logentry($this->userid, $this->courseid, $this, 0, 0, $sessionid) :
                $this->get_logentry_by_sessionid($sessionid);
        }
        if (!empty($logentry))
            $logentry->set_parent($this);

        return $logentry;
    }

    /**
     * get an entry from the logbook entris by
     * exercise id.
     *
     * @param int $exerciseid: The entry associated exercise id
     * @return logentry $logentry The logbook entry db record
     */
    public function get_logentry_by_exericseid(int $exerciseid) {
        $logentry = null;
        foreach ($this->entries as $entry) {
            if ($entry->get_exerciseid() == $exerciseid) {
                $logentry = $entry;
                break;
            }
        }
        return $logentry;
    }

    /**
     * get an entry from the logbook entris by
     * session id.
     *
     * @param int $sessionid: The entry associated session id
     * @return logentry $logentry The logbook entry db record
     */
    public function get_logentry_by_sessionid(int $sessionid) {
        $logentry = null;
        foreach ($this->entries as $entry) {
            if ($entry->get_sessionid() == $sessionid) {
                $logentry = $entry;
                break;
            }
        }
        return $logentry;
    }

    /**
     * Get the logbook entries time totals
     *
     * @param  bool $tostring   The totals in string time format
     * @param  bool $allcourses The totals of all courses
     * @param  int  $examid     The graduation exam exericse id
     * @return object           The logbook time table totals
     */
    public function get_summary(bool $tostring = false, bool $allcourses = false, int $examid = 0) {
        $totals = logbook_vault::get_logbook_summary($this->courseid, $this->userid, $examid, $allcourses);
        if ($tostring) {
            foreach ($totals as $key => $total) {
                if ($key != 'totallandingsday' && $key != 'totallandingsnight') {
                    // TODO: PHP9 deprecates dynamic properties
                    $totals->$key = self::convert_time($total, 'MINS_TO_TEXT') ?: '';
                }
            }
        }
        return $totals;
    }

    /**
     * Get the logbook entries time totals until a specific exercise
     *
     * @param int $exerciseid  The exercise id to sum up to.
     * @param  bool $tostring  The totals in string time format
     * @return array           The logbook time table totals
     */
    public function get_summary_upto_exercise(int $exerciseid, bool $tostring = false) {
        $totals = logbook_vault::get_logbook_summary_to_exercise($this->courseid, $this->userid, $exerciseid);
        if ($tostring) {
            foreach ($totals as $key => $total) {
                if ($key != 'totallandingsday' && $key != 'totallandingsnight') {
                    // TODO: PHP9 deprecates dynamic properties
                    $totals->$key = self::convert_time($total, 'MINS_TO_TEXT') ?: '';
                }
            }
        }
        return $totals;
    }

    /**
     * Get the course id for the log entry.
     *
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Get the user user id of the log entry.
     *
     * @return int
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * Get the user name of the log entry.
     *
     * @return string
     */
    public function get_username() {
        return participant::get_fullname($this->userid);
    }

    /**
     * Set the course  id for the log entry.
     *
     * @param int $courseid
     */
    public function set_courseid(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Set the studnet user id of the log entry.
     *
     * @param int $userid
     */
    public function set_userid(int $userid) {
        $this->userid = $userid;
    }

    /**
     * Whether the logbook as entries or not.
     *
     * @return bool true if the Logbook has entries
     */
    public function has_entries() {
        return count($this->entries) > 0;
    }

    /**
     * Converts the total number of minutes from
     * a text to number or timestamp duration and back.
     *
     * @param  mixed  $value     The value to be converted
     * @param  string $toformat  The conversion tyep
     * @param  int    $dayts     Additional information for the day timestamp
     * @return mixed  $converted The converted value
     */
    public static function convert_time($value = null, string $toformat = 'MINS_TO_TEXT', int $dayts = 0) {

        $result = 0;
        if (!empty($value)) {
            switch ($toformat) {
                case 'MINS_TO_TEXT':
                    if ($value > 0 && is_numeric($value)) {
                        $hrs = floor($value / 60);
                        $mins = $value % 60;
                        $result = ($hrs < 10 ? substr('00' . $hrs, -2) : $hrs) . ':' . substr('00' . $mins, -2);
                    }
                    break;
                case 'MINS_TO_NUM':
                    $hrs = substr($value, 0, strpos($value, ':'));
                    $mins = substr($value, strpos($value, ':') - strlen($value) + 1);
                    $result = ($hrs * 60) + $mins;
                    break;
                case 'TS_TO_TIME':
                    if ($value > 0 && is_numeric($value)) {
                        $daymins = ($value - strtotime("today", $value))  / 60;
                        $hrs = floor($daymins / 60);
                        $mins = (int) $daymins % 60;
                        $result = ($hrs < 10 ? substr('00' . $hrs, -2) : $hrs) . ':' . substr('00' . $mins, -2);
                    }
                    break;
                case 'TIME_TO_TS':
                    $hrs = substr($value, 0, strpos($value, ':'));
                    $mins = substr($value, strpos($value, ':') - strlen($value) + 1);
                    $result = $dayts + ((($hrs * 60) + $mins) * 60);
                    break;
            }
        }
        return $result;
    }
}
