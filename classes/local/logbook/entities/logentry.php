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
 * Class representing a log entry.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logentry implements logentry_interface {

    /**
     * @var logbook $parent The logbook containing this entry.
     */
    protected $parent;

    /**
     * @var int $id The id of this log entry.
     */
    protected $id;

    /**
     * @var int $exercise The course exercise id of this log entry.
     */
    protected $exerciseid;

    /**
     * @var int $flighttimemins The flight duration minutes of this log entry.
     */
    protected $flighttimemins;

    /**
     * @var int $soloflighttimemins The solo flight duration minutes of this log entry.
     */
    protected $soloflighttimemins;

    /**
     * @var int $sessiontimemins The session duration minutes of this log entry.
     */
    protected $sessiontimemins;

    /**
     * @var int $picid The user id of the primary in command of this log entry.
     */
    protected $picid;

    /**
     * @var int $sicid The user id of the secondary in command of this log entry.
     */
    protected $sicid;

    /**
     * @var int $aricraft The flight aircraft typeof this log entry.
     */
    protected $aircraft;

    /**
     * @var string $pirep The PIREP of this log entry.
     */
    protected $pirep;

    /**
     * @var string $callsign The flight callsign of this log entry.
     */
    protected $callsign;

    /**
     * @var string $fromicao The flight departure airport ICAO of this log entry.
     */
    protected $fromicao;

    /**
     * @var string $toicao The flight arrival airport ICAO of this log entry.
     */
    protected $toicao;

    /**
     * @var int $sessiondate The date timestamp of this log entry.
     */
    protected $sessiondate;

    /**
     * Converts the object to array through casting.
     *
     * @param bool  $formattostring: whether to return some values in string format
     * @return array
     */
    public function __toArray(bool $formattostring = false) {
        return [
            'id' => $this->id,
            'exerciseid' => $this->exerciseid,
            'flighttimemins' => $formattostring ? $this->get_flighttimemins(false) : $this->flighttimemins,
            'sessiontimemins' => $formattostring ? $this->get_sessiontimemins(false) : $this->sessiontimemins,
            'soloflighttimemins' => $formattostring ? $this->get_soloflighttimemins(false) : $this->soloflighttimemins,
            'picid' => $this->picid,
            'sicid' => $this->sicid,
            'aircraft' => $this->aircraft,
            'pirep' => $this->pirep,
            'callsign' => $this->callsign,
            'fromicao' => $this->fromicao,
            'toicao' => $this->toicao,
            'sessiondate' => $formattostring ? $this->get_sessiondate(true) : $this->sessiondate,
        ];
    }

    /**
     * Saves a logbook entry (create or update).
     *
     * @return bool
     */
    public function save() {
        $result = false;

        if ($this->id == 0) {
            $this->id = logbook_vault::insert_logentry($this->parent->get_courseid(), $this->parent->get_userid(), $this);
            $result = true;
        } else {
            $result = logbook_vault::update_logentry($this->parent->get_courseid(), $this->parent->get_userid(), $this);
        }

        return $result;
    }

    /**
     * Get the parent logbook.
     *
     * @return logbook
     */
    public function get_parent() {
        return $this->parent;
    }

    /**
     * Get the id for the logbook entry.
     *
     * @param int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get the course exercise id for the log entry.
     *
     * @return int
     */
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    /**
     * Get the aircraft typeof the log entry.
     *
     * @return int
     */
    public function get_aircraft() {
        return $this->aircraft;
    }

    /**
     * Get the flight duration minutes of the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_flighttimemins(bool $numeric = true) {
        return $numeric ? $this->flighttimemins : logbook::convert_duration($this->flighttimemins, 'text');
    }

    /**
     * Get the session duration minutes of the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_sessiontimemins(bool $numeric = true) {
        return $numeric ? $this->sessiontimemins : logbook::convert_duration($this->sessiontimemins, 'text');
    }

    /**
     * Get the solo flight duration minutes of the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_soloflighttimemins(bool $numeric = true) {
        return $numeric ? $this->soloflighttimemins : logbook::convert_duration($this->soloflighttimemins, 'text');
    }

    /**
     * Get the pilot in command user id of the log entry.
     *
     * @return int
     */
    public function get_picid() {
        return $this->picid;
    }

    /**
     * Get the pilot in command name of the log entry.
     *
     * @return string
     */
    public function get_picname() {
        return participant::get_fullname($this->picid);
    }

    /**
     * Get the secondary in command user id of the log entry.
     *
     * @return int
     */
    public function get_sicid() {
        return $this->sicid;
    }

    /**
     * Set the secondary in command name of the log entry.
     *
     * @return string
     */
    public function get_sicname() {
        return participant::get_fullname($this->sicid);
    }

    /**
     * Get the PIREP string of log entry.
     *
     * @return string $pirep
     */
    public function get_pirep() {
        return $this->pirep;
    }

    /**
     * Get the flight callsign of the log entry.
     *
     * @return string
     */
    public function get_callsign() {
        return $this->callsign;
    }

    /**
     * Get the flight departure airport ICAO of the log entry.
     *
     * @return string
     */
    public function get_fromicao() {
        return $this->fromicao;
    }

    /**
     * Get the flight arrival airport ICAO of the log entry.
     *
     * @return string
     */
    public function get_toicao() {
        return $this->toicao;
    }

    /**
     * Get the date timestamp of the log entry.
     *
     * @param bool $formatted string formatting of the date
     * @return mixed
     */
    public function get_sessiondate(bool $formatted = false) {
        $sessiondate = $formatted ? (new DateTime('@'.$this->sessiondate))->format('l M d, Y') : $this->sessiondate;

        return $sessiondate;
    }

    /**
     * Set the parent logbook.
     *
     * @param logbook
     */
    public function set_parent(logbook $logbook) {
        $this->parent = $logbook;
    }

    /**
     * Set the id for the logbook entry.
     *
     * @param int
     */
    public function set_id(int $id) {
        $this->id = $id;
    }

    /**
     * Set the course exercise id for the log entry.
     *
     * @param int $exerciseid
     */
    public function set_exerciseid(int $exerciseid) {
        $this->exerciseid = $exerciseid;
    }

    /**
     * Set the flight duration minutes of the log entry.
     *
     * @param mixed $flighttimemins The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_flighttimemins($flighttimemins, bool $isnumeric = true) {
        $this->flighttimemins = $isnumeric ? $flighttimemins : logbook::convert_duration($flighttimemins, 'number');
    }

    /**
     * Set the session duration minutes of the log entry.
     *
     * @param mixed $sessiontimemins The session time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_sessiontimemins($sessiontimemins, bool $isnumeric = true) {
        $this->sessiontimemins = $isnumeric ? $sessiontimemins : logbook::convert_duration($sessiontimemins, 'number');
    }

    /**
     * Set the solo flight time duration minutes of the log entry.
     *
     * @param mixed $soloflighttimemins The solo flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_soloflighttimemins($soloflighttimemins, bool $isnumeric = true) {
        $this->soloflighttimemins = $isnumeric ? $soloflighttimemins : logbook::convert_duration($soloflighttimemins, 'number');
    }

    /**
     * Set the pilot in command user id of the log entry.
     *
     * @param int $picid
     */
    public function set_picid(int $picid) {
        $this->picid = $picid;
    }

    /**
     * Set the secondary in command user id of the log entry.
     *
     * @param int $sicid
     */
    public function set_sicid(int $sicid) {
        $this->sicid = $sicid;
    }

    /**
     * Set the aircraft typeof the log entry.
     *
     * @return string
     */
    public function set_aircraft(string $aircraft) {
        $this->aircraft = $aircraft;
    }

    /**
     * Set the PIREP string of log entry.
     *
     * @param string $pirep
     */
    public function set_pirep(string $pirep) {
        $this->pirep = $pirep;
    }

    /**
     * Set the flight callsign of the log entry.
     *
     * @param string $callsign
     */
    public function set_callsign(string $callsign) {
        $this->callsign = $callsign;
    }

    /**
     * Set the flight departure airport ICAO of the log entry.
     *
     * @param string $fromicao
     */
    public function set_fromicao(string $fromicao) {
        $this->fromicao = $fromicao;
    }

    /**
     * Set the flight arrival airport ICAO of the log entry.
     *
     * @return string
     */
    public function set_toicao(string $toicao) {
        $this->toicao = $toicao;
    }

    /**
     * Set the date timestamp of the log entry.
     *
     * @param int $sessiondate
     */
    public function set_sessiondate(int $sessiondate) {
        $this->sessiondate = $sessiondate;
    }

    /**
     * Populates a log book entry with a modal form data.
     *
     * @param object $formdata
     */
    public function populate(object $formdata) {
        if (!empty($formdata->logentryid)) {
            $this->id = $formdata->logentryid;
        }

        $this->exerciseid = $formdata->exerciseid;
        $this->soloflighttimemins = logbook::convert_duration($formdata->soloflighttimemins, 'number');
        $this->flighttimemins = logbook::convert_duration($formdata->flighttimemins, 'number');
        $this->sessiontimemins = !empty($formdata->sessiontimemins) ? logbook::convert_duration($formdata->sessiontimemins, 'number') : 0;
        $this->picid = $formdata->picid;
        $this->sicid = $formdata->sicid;
        $this->pirep = $formdata->pirep;
        $this->aircraft = $formdata->aircraft;
        $this->callsign = strtoupper($formdata->callsign);
        $this->fromicao = strtoupper($formdata->fromicao);
        $this->toicao = strtoupper($formdata->toicao);
        $this->sessiondate = $formdata->sessiondate;
    }
}
