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
     * @var int $id The id.
     */
    protected $id;

    /**
     * @var int $exercise The course exercise id.
     */
    protected $exerciseid;

    /**
     * @var int $flightdate The date the flight took place.
     */
    protected $flightdate;

    /**
     * @var int $p1id The user id of the instructor.
     */
    protected int $p1id;

    /**
     * @var int $p2id The user id of the student.
     */
    protected int $p2id;

    /**
     * @var int $sessiontime The training session duration in minutes.
     */
    protected $sessiontime;

    /**
     * @var int $pictime The flying as PIC duration in minutes.
     */
    protected $pictime;

    /**
     * @var int $dualtime The flight instructor/student duration minutes.
     */
    protected $dualtime;

    /**
     * @var int $instructortime The flight instructor duration minutes.
     */
    protected $instructortime;

    /**
     * @var int $multipilottime The flight multipilot time in minutes.
     */
    protected $multipilottime;

    /**
     * @var int $copilottime The flying as copilot duration in minutes.
     */
    protected $copilottime;

    /**
     * @var int $checkpilottime The checkride examiner duration minutes.
     */
    protected $checkpilottime;

    /**
     * @var string $pirep The PIREP.
     */
    protected $pirep;

    /**
     * @var string $callsign The flight callsign.
     */
    protected $callsign;

    /**
     * @var string $depicao The flight departure airport ICAO.
     */
    protected $depicao;

    /**
     * @var int $deptime The departure time.
     */
    protected $deptime;

    /**
     * @var string $arricao The flight arrival airport ICAO.
     */
    protected $arricao;

    /**
     * @var int $arrtime The arrival time.
     */
    protected $arrtime;

    /**
     * @var string $aricraft The flight aircraft type.
     */
    protected $aircraft;

    /**
     * @var string $aricraftreg The flight aircraft registration.
     */
    protected $aircraftreg;

    /**
     * @var string $enginetype The flight aircraft engine type.
     */
    protected $enginetype;

    /**
     * @var int $landingsday The number of day landings for the flight.
     */
    protected $landingsday;

    /**
     * @var int $landingsnight The number of night landings for the flight.
     */
    protected $landingsnight;

    /**
     * @var int $nighttime The flying at night duration in minutes.
     */
    protected $nighttime;

    /**
     * @var int $ifrtime The IFR flying duration in minutes.
     */
    protected $ifrtime;

    /**
     * @var string $remarks The instructor remarks.
     */
    protected $remarks;

    /**
     * @var string $fstd Flight Simulation Training Device qualification.
     */
    protected $fstd;

    /**
     * @var bool $soloflight Whether the flight is a solo flight or not.
     */
    protected $soloflight = false;

    /**
     * @var int $linkedlogentryid Other logentries associated with the flight.
     */
    protected $linkedlogentryid = 0;

    /**
     * Saves a logbook entry (create or update).
     *
     * @return bool
     */
    public function save() {
        $result = false;

        if ($this->id == 0) {
            $this->id = $this->parent->insert($this);
            $result = true;
        } else {
            $result = $this->parent->update($this);
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
     * Get the user id for the logbook entry (student).
     *
     * @param int
     */
    public function get_userid() {
        return $this->parent->get_userid();
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
     * Get the PIREP string of log entry.
     *
     * @return string $pirep
     */
    public function get_pirep() {
        return $this->pirep;
    }

    /**
     * Get the flight callsign.
     *
     * @return string
     */
    public function get_callsign() {
        return $this->callsign;
    }

    /**
     * Get the flight date timestamp.
     *
     * @return mixed
     */
    public function get_flightdate(bool $tostring = false) {
        return ($tostring ? (new \DateTime('@'.$this->flightdate))->format('l M j \- H:i \z\u\l\u') : $this->flightdate);
    }

    /**
     * Get the flight departure airport ICAO.
     *
     * @return string
     */
    public function get_depicao() {
        return $this->depicao;
    }

    /**
     * Get the flight departure time.
     *
     * @return int
     */
    public function get_deptime() {
        return $this->deptime;
    }

    /**
     * Get the flight arrival airport ICAO.
     *
     * @return string
     */
    public function get_arricao() {
        return $this->arricao;
    }

    /**
     * Get the flight arrival time.
     *
     * @return int
     */
    public function get_arrtime() {
        return $this->arrtime;
    }

    /**
     * Get the aircraft type.
     *
     * @return string
     */
    public function get_aircraft() {
        return $this->aircraft;
    }

    /**
     * Get the aircraft registration.
     *
     * @return string
     */
    public function get_aircraftreg() {
        return $this->aircraftreg;
    }

    /**
     * Get the aircraft engine type.
     *
     * @return string
     */
    public function get_enginetype() {
        return $this->enginetype;
    }

    /**
     * Get the P1 instructor user id.
     *
     * @return int
     */
    public function get_p1id() {
        return $this->p1id;
    }

    /**
     * Get the P2 student user id.
     *
     * @return int
     */
    public function get_p2id() {
        return $this->p2id;
    }

    /**
     * Get the pilot in command name.
     *
     * @return string
     */
    public function get_picname() {
        return participant::get_fullname($this->p1id);
    }

    /**
     * Get the number of day landings for the flight..
     *
     * @return int
     */
    public function get_landingsday() {
        return $this->landingsday;
    }

    /**
     * Get the number of night landings for the flight..
     *
     * @return int
     */
    public function get_landingsnight() {
        return $this->landingsnight;
    }

    /**
     * Get the training session duration in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_sessiontime(bool $numeric = true) {
        return $numeric ? $this->sessiontime : logbook::convert_time($this->sessiontime, 'MINS_TO_TEXT');
    }

    /**
     * Get the multipilot time duration minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_multipilottime(bool $numeric = true) {
        return $numeric ? $this->multipilottime : logbook::convert_time($this->multipilottime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying at night duration in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_nighttime(bool $numeric = true) {
        return $numeric ? $this->nighttime : logbook::convert_time($this->nighttime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying IFR duration in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_ifrtime(bool $numeric = true) {
        return $numeric ? $this->ifrtime : logbook::convert_time($this->ifrtime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying as PIC duration in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_pictime(bool $numeric = true) {
        return $numeric ? $this->pictime : logbook::convert_time($this->pictime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying as copilot duration in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_copilottime(bool $numeric = true) {
        return $numeric ? $this->copilottime : logbook::convert_time($this->copilottime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flight instructor/student duration minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_dualtime(bool $numeric = true) {
        return $numeric ? $this->dualtime : logbook::convert_time($this->dualtime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flight instructor duration minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_instructortime(bool $numeric = true) {
        return $numeric ? $this->instructortime : logbook::convert_time($this->instructortime, 'MINS_TO_TEXT');
    }

    /**
     * Get the examiner checkride duration minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_checkpilottime(bool $numeric = true) {
        return $numeric ? $this->checkpilottime : logbook::convert_time($this->checkpilottime, 'MINS_TO_TEXT');
    }

    /**
     * Get the Flight Simulation Training Device qualification.
     *
     * @return string
     */
    public function get_fstd() {
        return $this->fstd;
    }

    /**
     * Get the instructor remarks.
     *
     * @return string
     */
    public function get_remarks() {
        return $this->remarks;
    }

    /**
     * Get the associated logentry.
     *
     * @return int
     */
    public function get_linkedlogentryid() {
        return $this->linkedlogentryid;
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
     * Set the PIREP string of log entry.
     *
     * @param string $pirep
     */
    public function set_pirep(string $pirep) {
        $this->pirep = $pirep;
    }

    /**
     * Set the flight callsign.
     *
     * @param string $callsign
     */
    public function set_callsign(string $callsign) {
        $this->callsign = $callsign;
    }

    /**
     * Set the flight date timestamp.
     *
     * @param int $flightdate
     */
    public function set_flightdate(int $flightdate) {
        $this->flightdate = $flightdate;
    }

    /**
     * Set the flight departure airport ICAO.
     *
     * @param string $depicao
     */
    public function set_depicao(string $depicao) {
        $this->depicao = $depicao;
    }

    /**
     * Set the flight departure time.
     *
     * @param int $deptime departure time timestamp
     */
    public function set_deptime($deptime) {
        $this->deptime = $deptime;
    }

    /**
     * Set the flight arrival airport ICAO.
     *
     * @return string
     */
    public function set_arricao(string $arricao) {
        $this->arricao = $arricao;
    }

    /**
     * Set the flight arrival time.
     *
     * @param int $arrtime arrival time timestamp
     */
    public function set_arrtime($arrtime) {
        $this->arrtime = $arrtime;
    }

    /**
     * Set the aircraft type.
     *
     * @return string
     */
    public function set_aircraft(string $aircraft) {
        $this->aircraft = $aircraft;
    }

    /**
     * Set the aircraft registration.
     *
     * @return string
     */
    public function set_aircraftreg(string $aircraftreg) {
        $this->aircraftreg = $aircraftreg;
    }

    /**
     * Set the aircraft engine type.
     *
     * @return string
     */
    public function set_enginetype(string $enginetype) {
        $this->enginetype = $enginetype;
    }

    /**
     * Set the P1 (instructor) user id.
     *
     * @param int $p1id
     */
    public function set_p1id(int $p1id) {
        $this->p1id = $p1id;
    }

    /**
     * Set the P2 (student) user id.
     *
     * @param int $p2id
     */
    public function set_p2id(int $p2id) {
        $this->p2id = $p2id;
    }

    /**
     * Set the number of day landings for the flight.
     *
     * @param int $landingsday
     */
    public function set_landingsday(int $landingsday) {
        $this->landingsday = $landingsday;
    }

    /**
     * Set the number of night landings for the flight.
     *
     * @param int $landingsnight
     */
    public function set_landingsnight(int $landingsnight) {
        $this->landingsnight = $landingsnight;
    }

    /**
     * Set the training session duration in minutes.
     *
     * @param mixed $sessiontime The session time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_sessiontime($sessiontime, bool $isnumeric = true) {
        $this->sessiontime = $isnumeric ? $sessiontime : logbook::convert_time($sessiontime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight multipilot time in minutes.
     *
     * @param mixed $multipilottime The flight multipilot time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_multipilottime($multipilottime, bool $isnumeric = true) {
        $this->multipilottime = $isnumeric ? $multipilottime : logbook::convert_time($multipilottime, 'MINS_TO_NUM');
    }

    /**
     * Set the flying at night duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_nighttime($nighttime, bool $isnumeric = true) {
        $this->nighttime = $isnumeric ? $nighttime : logbook::convert_time($nighttime, 'MINS_TO_NUM');
    }

    /**
     * Set the flying IFR duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_ifrtime($ifrtime, bool $isnumeric = true) {
        $this->ifrtime = $isnumeric ? $ifrtime : logbook::convert_time($ifrtime, 'MINS_TO_NUM');
    }

    /**
     * Set the flying as PIC duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_pictime($pictime, bool $isnumeric = true) {
        $this->pictime = $isnumeric ? $pictime : logbook::convert_time($pictime, 'MINS_TO_NUM');
    }

    /**
     * Set the flying as copilot duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_copilottime($copilottime, bool $isnumeric = true) {
        $this->copilottime = $isnumeric ? $copilottime : logbook::convert_time($copilottime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight instructor/student duration minutes.
     *
     * @param mixed $dualtime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_dualtime($dualtime, bool $isnumeric = true) {
        $this->dualtime = $isnumeric ? $dualtime : logbook::convert_time($dualtime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight instructor duration minutes.
     *
     * @param mixed $instructortime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_instructortime($instructortime, bool $isnumeric = true) {
        $this->instructortime = $isnumeric ? $instructortime : logbook::convert_time($instructortime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight instructor duration minutes.
     *
     * @param mixed $checkpilottime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_checkpilottime($checkpilottime, bool $isnumeric = true) {
        $this->checkpilottime = $isnumeric ? $checkpilottime : logbook::convert_time($checkpilottime, 'MINS_TO_NUM');
    }

    /**
     * Set the secondary in command user id.
     *
     * @param string $fstd
     */
    public function set_fstd(string $fstd) {
        $this->fstd = $fstd;
    }

    /**
     * Set the instructor's remarks.
     *
     * @param string $remarks
     */
    public function set_remarks(string $remarks) {
        $this->remarks = $remarks;
    }

    /**
     * Set the associated logentry.
     *
     * @param int $linkedlogentryid
     */
    public function set_linkedlogentryid(int $linkedlogentryid) {
        $this->linkedlogentryid = $linkedlogentryid;
    }

    /**
     * Whether the flight is a solo flight or not.
     *
     * @param bool $soloflight
     */
    public function is_solo() {
        return $this->soloflight;
    }

    /**
     * Populates a log book entry with a modal form data.
     *
     * @param object $formdata
     * @param bool $isinstructor
     */
    public function populate(object $formdata, bool $isinstructor = false) {
        $this->id = $formdata->id ?: null;
        $this->exerciseid = $formdata->exerciseid;
        $this->sessiontime = !empty($formdata->sessiontime) ? logbook::convert_time($formdata->sessiontime, 'MINS_TO_NUM') : 0;
        $this->flightdate = $formdata->flightdate;
        $this->p1id = $formdata->soloflight ? $this->parent->get_userid() : $formdata->p1id;
        $this->p2id = $formdata->soloflight ? 0 : $formdata->p2id;
        $this->pictime = !empty($formdata->pictime) ? logbook::convert_time($formdata->pictime, 'MINS_TO_NUM') : 0;
        $this->dualtime = !empty($formdata->pictime) ? logbook::convert_time($formdata->dualtime, 'MINS_TO_NUM') : 0;
        $this->instructortime = $isinstructor ? logbook::convert_time($formdata->instructortime, 'MINS_TO_NUM') : 0;
        $this->pirep = $isinstructor ? $formdata->p1pirep : $formdata->p2pirep;
        $this->callsign = strtoupper($formdata->callsign);
        $this->fstd = $formdata->fstd;
        $this->multipilottime = !empty($formdata->multipilottime) ? logbook::convert_time($formdata->multipilottime, 'MINS_TO_NUM') : 0;
        $this->copilottime = !empty($formdata->copilottime) ? logbook::convert_time($formdata->copilottime, 'MINS_TO_NUM') : 0;
        $this->depicao = strtoupper($formdata->depicao);
        $this->arricao = strtoupper($formdata->arricao);
        // convert from 24hr time format to a timestamp given the flightdate date start
        $this->deptime = !empty($formdata->deptime) ? logbook::convert_time($formdata->deptime, 'TIME_TO_TS', strtotime("today", $formdata->flightdate)) : 0;
        $this->arrtime = !empty($formdata->arrtime) ? logbook::convert_time($formdata->arrtime, 'TIME_TO_TS', strtotime("today", $formdata->flightdate)) : 0;
        $this->aircraft = $formdata->aircraft;
        $this->aircraftreg = $formdata->aircraftreg;
        $this->enginetype = $formdata->enginetype;
        $this->landingsday = $formdata->landingsday;
        $this->landingsnight = $formdata->landingsnight;
        $this->nighttime = !empty($formdata->nighttime) ? logbook::convert_time($formdata->nighttime, 'MINS_TO_NUM') : 0;
        $this->ifrtime = !empty($formdata->ifrtime) ? logbook::convert_time($formdata->ifrtime, 'MINS_TO_NUM') : 0;
        $this->checkpilottime = !empty($formdata->checkpilottime) && $isinstructor ? logbook::convert_time($formdata->checkpilottime, 'MINS_TO_NUM') : 0;
        $this->remarks = $formdata->remarks;
        $this->linkedlogentryid = $formdata->linkedlogentryid ?: 0;
        $this->soloflight = $formdata->soloflight;
    }

    /**
     * Converts the object to array.
     *
     * @param bool  $formattostring: whether to return some values in string format
     * @return array
     */
    public function __toArray(bool $formattostring = false) {
        return [
            'id' => $this->id,
            'exerciseid' => $this->exerciseid,
            'flightdate' => $this->get_flightdate($formattostring),
            'p1id' => $this->p1id,
            'p2id' => $this->p2id,
            'sessiontime' => $this->get_sessiontime(!$formattostring),
            'pictime' => $this->get_pictime(!$formattostring) ?: null,
            'dualtime' => $this->get_dualtime(!$formattostring) ?: null,
            'instructortime' => $this->get_instructortime(!$formattostring) ?: null,
            'multipilottime' => $this->get_multipilottime(!$formattostring) ?: null,
            'copilottime' => $this->get_copilottime(!$formattostring) ?: null,
            'checkpilottime' => $this->get_checkpilottime(!$formattostring) ?: null,
            'pirep' => $this->pirep,
            'callsign' => $this->callsign,
            'depicao' => $this->depicao,
            'deptime' => ($formattostring ? logbook::convert_time($this->deptime, 'TS_TO_TIME') : $this->deptime),
            'arricao' => $this->arricao,
            'arrtime' => ($formattostring ? logbook::convert_time($this->arrtime, 'TS_TO_TIME') : $this->arrtime),
            'aircraft' => $this->aircraft,
            'aircraftreg' => $this->aircraftreg,
            'enginetype' => $this->enginetype,
            'landingsday' => $this->landingsday,
            'landingsnight' => $this->landingsnight,
            'nighttime' => $this->get_nighttime(!$formattostring) ?: null,
            'ifrtime' => $this->get_ifrtime(!$formattostring) ?: null,
            'fstd' => $this->fstd,
            'remarks' => $this->remarks,
            'linkedlogentryid' => $this->linkedlogentryid,
            'soloflight' => $this->soloflight,
        ];
    }
}
