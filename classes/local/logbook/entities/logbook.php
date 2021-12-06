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

use DateTime;
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
     * @var array $entries The list of logbook entries.
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
     * @return bool true if the Logbook has entries
     */
    public function load() {
        $this->entries = logbook_vault::get_logbook($this->courseid, $this->userid, $this);
        return count($this->entries) > 0;
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
     * @return int The id of the logbook entery inserted
     */
    public function add(logentry $logentry) {
        $logentry->set_parent($this);
        return logbook_vault::insert_logentry($this->courseid, $this->userid, $logentry);
    }

    /**
     * Update a logbook entry.
     *
     * @return bool
     */
    public function update(logentry $logentry){
        $logentry->set_parent($this);
        return logbook_vault::update_logentry($this->courseid, $this->userid, $logentry);
    }

    /**
     * Deletes a logbook entry.
     *
     * @return bool
     */
    public function delete(int $logentryid) {
        return logbook_vault::delete_logentry($logentryid);
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
     * @return logentry
     */
    public function get_logentry(int $logentryid = 0, int $exerciseid = 0, bool $reload = true) {
        $logentry = null;

        if ($logentryid != 0) {
            $logentry = $reload ?
                logbook_vault::get_logentry($this->userid, $this->courseid, $logentryid, 0, $this) :
                $logentry = $this->entries[$logentryid];
        } else if ($exerciseid != 0) {
            $logentry = $reload ?
                logbook_vault::get_logentry($this->userid, $this->courseid, 0, $exerciseid, $this) :
                $logentry = $this->get_logentry_by_exericseid($exerciseid);
        }
        if (!empty($logentry))
            $logentry->set_parent($this);

        return $logentry;
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
     * exercise id.
     *
     * @param int $exerciseid: The entry associated exercise id
     * @return logentry $logentry The logbook entry db record
     */
    public function get_summary() {
        list($totalflighttime, $totalsessiontime, $totalsolotime) = logbook_vault::get_logbook_summary($this->courseid, $this->userid);

        return [
            self::convert_duration($totalflighttime, 'text'),
            self::convert_duration($totalsessiontime, 'text'),
            self::convert_duration($totalsolotime, 'text')
        ];
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
     * Converts the total number of minutes from
     * a text to integer duration and back.
     *
     * @param mixed $value
     * @param string $toformat
     * @return mixed $converted
     */
    public static function convert_duration($value, string $toformat) {

        $result = 0;
        if ($toformat == 'text') {
            $result = '00:00';
            if ($value > 0 && is_numeric($value)) {
                $hrs = floor($value / 60);
                $mins = $value % 60;
                $result = substr('00' . $hrs, -2) . ':' . substr('00' . $mins, -2);
            }
        } else if ($toformat == 'number') {
            if (!empty($value)) {
                $hrs = substr($value, 0, strpos($value, ':'));
                $mins = substr($value, strpos($value, ':') - strlen($value) + 1);
                $result = ($hrs * 60) + $mins;
            }
        }

        return $result;
    }
}
