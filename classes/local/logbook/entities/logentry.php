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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\entities;

use local_booking\local\participant\entities\participant;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a logbook entry.
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
    protected $id = 0;

    /**
     * @var int $courseid The course id.
     */
    protected $courseid = 0;

    /**
     * @var int $userid The user id.
     */
    protected $userid = 0;

    /**
     * @var int $exerciseid The course exercise id.
     */
    protected $exerciseid = 0;

    /**
     * @var int $sessionid The session id.
     */
    protected $sessionid = 0;

    /**
     * @var int $flightdate The date the flight took place.
     */
    protected $flightdate = 0;

    /**
     * @var int $p1id The user id of the instructor.
     */
    protected $p1id = 0;

    /**
     * @var int $p2id The user id of the student.
     */
    protected $p2id = 0;

    /**
     * @var int $pilot_id The ATO pilot id for the student.
     */
    protected $pilot_id = 0;

    /**
     * @var int $groundtime The training session time in minutes.
     */
    protected $groundtime = 0;

    /**
     * @var int $flighttime The flight duration in minutes.
     */
    protected $flighttime = 0;

    /**
     * @var int $pictime The flying as PIC time in minutes.
     */
    protected $pictime = 0;

    /**
     * @var int $dualtime The flight instructor/student time in minutes.
     */
    protected $dualtime = 0;

    /**
     * @var int $instructortime The flight instructor time in minutes.
     */
    protected $instructortime = 0;

    /**
     * @var int $picustime The flight instructor time in minutes.
     */
    protected $picustime = 0;

    /**
     * @var int $multipilottime The flight multipilot time in minutes.
     */
    protected $multipilottime = 0;

    /**
     * @var int $copilottime The flying as copilot time in minutes.
     */
    protected $copilottime = 0;

    /**
     * @var int $checkpilottime The checkride examiner time in minutes.
     */
    protected $checkpilottime = 0;

    /**
     * @var string $pirep The PIREP.
     */
    protected $pirep = '';

    /**
     * @var string $callsign The flight callsign.
     */
    protected $callsign = '';

    /**
     * @var string $depicao The flight departure airport ICAO.
     */
    protected $depicao = '';

    /**
     * @var int $deptime The departure time.
     */
    protected $deptime = 0;

    /**
     * @var string $arricao The flight arrival airport ICAO.
     */
    protected $arricao = '';

    /**
     * @var int $arrtime The arrival time.
     */
    protected $arrtime = 0;

    /**
     * @var string $aricraft The flight aircraft type.
     */
    protected $aircraft = '';

    /**
     * @var string $aricraftreg The flight aircraft registration.
     */
    protected $aircraftreg = '';

    /**
     * @var string $enginetype The flight aircraft engine type.
     */
    protected $enginetype = '';

    /**
     * @var string $route The flight route.
     */
    protected $route = '';

    /**
     * @var int $landingsday The number of day landings for the flight.
     */
    protected $landingsday = 0;

    /**
     * @var int $landingsnight The number of night landings for the flight.
     */
    protected $landingsnight = 0;

    /**
     * @var int $nighttime The flying at night time in minutes.
     */
    protected $nighttime = 0;

    /**
     * @var int $ifrtime The IFR flying time in minutes.
     */
    protected $ifrtime = 0;

    /**
     * @var string $remarks The instructor remarks.
     */
    protected $remarks = '';

    /**
     * @var string $fstd Flight Simulation Training Device qualification.
     */
    protected $fstd = '';

    /**
     * @var bool $flighttype the type of flight: training, solo, or check (line check / check ride).
     */
    protected $flighttype = 'training';

    /**
     * @var int $linkedlogentryid Other logentries associated with the flight.
     */
    protected $linkedlogentryid = 0;

    /**
     * @var int $linkedpirep Other linked pireps associated with the flight.
     */
    protected $linkedpirep = '';

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
     * Get the course id loaded in the logbook entry.
     *
     * @param int
     */
    public function get_courseid() {
        return $this->courseid;
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
     * Get the course exercise id for the logbook entry.
     *
     * @return int
     */
    public function get_exercise_id() {
        return $this->exerciseid;
    }

    /**
     * Get the session id for the log entry.
     *
     * @return int
     */
    public function get_sessionid() {
        return $this->sessionid;
    }

    /**
     * Get the PIREP string of logbook entry.
     *
     * @return string $pirep
     */
    public function get_pirep() {
        return $this->pirep;
    }

    /**
     * Get the linked PIREP string of logbook entry.
     *
     * @return string $linkedpirep
     */
    public function get_linkedpirep() {
        return $this->linkedpirep;
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
    public function get_flightdate(bool $tostring = false, bool $shortdate = false) {
        $date = $tostring ? (new \DateTime('@'.$this->flightdate))->format(($shortdate?'Y\/m\/d':'l M d\, Y \- H:i\z')) : $this->flightdate;
        return $date;
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
     * Get the flight route.
     *
     * @return string
     */
    public function get_route() {
        return $this->route;
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
     * Get the ATO pilot id of the student.
     *
     * @return int
     */
    public function get_pilotid() {
        return $this->pilot_id;
    }

    /**
     * Get the pilot in command name.
     *
     * @return string
     */
    public function get_p1name() {
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
     * Get the training session time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_groundtime(bool $numeric = true) {
        return $numeric ? $this->groundtime : logbook::convert_time($this->groundtime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flight time/duration in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_flighttime(bool $numeric = true) {
        return $numeric ? $this->flighttime : logbook::convert_time($this->flighttime, 'MINS_TO_TEXT');
    }

    /**
     * Get the multipilot time/duration in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_multipilottime(bool $numeric = true) {
        return $numeric ? $this->multipilottime : logbook::convert_time($this->multipilottime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying at night time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_nighttime(bool $numeric = true) {
        return $numeric ? $this->nighttime : logbook::convert_time($this->nighttime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying IFR time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_ifrtime(bool $numeric = true) {
        return $numeric ? $this->ifrtime : logbook::convert_time($this->ifrtime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying as PIC time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_pictime(bool $numeric = true) {
        return $numeric ? $this->pictime : logbook::convert_time($this->pictime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flying as copilot time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_copilottime(bool $numeric = true) {
        return $numeric ? $this->copilottime : logbook::convert_time($this->copilottime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flight instructor/student time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_dualtime(bool $numeric = true) {
        return $numeric ? $this->dualtime : logbook::convert_time($this->dualtime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flight instructor time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_instructortime(bool $numeric = true) {
        return $numeric ? $this->instructortime : logbook::convert_time($this->instructortime, 'MINS_TO_TEXT');
    }

    /**
     * Get the flight PIC under supervision time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_picustime(bool $numeric = true) {
        return $numeric ? $this->picustime : logbook::convert_time($this->picustime, 'MINS_TO_TEXT');
    }

    /**
     * Get the total time for the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_totalsessiontime(bool $numeric = true) {
        // get the log entry total time depending on the pilot's function
        $totalsessiontime = $this->flighttime + $this->groundtime;
        return $numeric ? $totalsessiontime : logbook::convert_time($totalsessiontime, 'MINS_TO_TEXT');
    }

    /**
     * Get the examiner checkride time in minutes.
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
     * Get the flight type.
     *
     */
    public function get_flighttype() {
        return $this->flighttype;
    }

    /**
     * Set the parent logbook.
     *
     * @param logbook
     */
    public function set_parent(logbook $logbook) {
        $this->courseid = $logbook->get_courseid();
        $this->userid = $logbook->get_userid();
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
     * Set the course id for the logbook entry.
     *
     * @param int
     */
    public function set_courseid(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Set the course exercise id for the logbook entry.
     *
     * @param int $exerciseid
     */
    public function set_exerciseid(int $exerciseid) {
        $this->exerciseid = $exerciseid;
    }

    /**
     * Set the session id for the logbook entry.
     *
     * @param int $sessionid
     */
    public function set_sessionid(int $sessionid) {
        $this->sessionid = $sessionid;
    }

    /**
     * Set the PIREP string of logbook entry.
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
     * Set the flight type.
     *
     * @param string $flighttype
     */
    public function set_flighttype(string $flighttype) {
        $this->flighttype = $flighttype;
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
     * @param int $deptime departure timestamp
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
     * @param int $arrtime arrival timestamp
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
     * Set the flight route.
     *
     * @return string
     */
    public function set_route(string $route) {
        $this->route = $route;
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
     * Set the student's ATO pilot id'.
     *
     * @param int $pilot_id
     */
    public function set_pilotid(int $pilot_id) {
        $this->pilot_id = $pilot_id;
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
     * Set the training session time in minutes.
     *
     * @param mixed $groundtime The session time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_groundtime($groundtime, bool $isnumeric = true) {
        $this->groundtime = $isnumeric ? $groundtime : logbook::convert_time($groundtime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight time/duration in minutes.
     *
     * @param mixed $flighttime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_flighttime($flighttime, bool $isnumeric = true) {
        $this->flighttime = $isnumeric ? $flighttime : logbook::convert_time($flighttime, 'MINS_TO_NUM');
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
     * Set the flying at night time in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_nighttime($nighttime, bool $isnumeric = true) {
        $this->nighttime = $isnumeric ? $nighttime : logbook::convert_time($nighttime, 'MINS_TO_NUM');
    }

    /**
     * Set the flying IFR time in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_ifrtime($ifrtime, bool $isnumeric = true) {
        $this->ifrtime = $isnumeric ? $ifrtime : logbook::convert_time($ifrtime, 'MINS_TO_NUM');
    }

    /**
     * Set the flying as PIC time in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_pictime($pictime, bool $isnumeric = true) {
        $this->pictime = $isnumeric ? $pictime : logbook::convert_time($pictime, 'MINS_TO_NUM');
    }

    /**
     * Set the flying as copilot time in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_copilottime($copilottime, bool $isnumeric = true) {
        $this->copilottime = $isnumeric ? $copilottime : logbook::convert_time($copilottime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight instructor/student time in minutes.
     *
     * @param mixed $dualtime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_dualtime($dualtime, bool $isnumeric = true) {
        $this->dualtime = $isnumeric ? $dualtime : logbook::convert_time($dualtime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight instructor time in minutes.
     *
     * @param mixed $instructortime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_instructortime($instructortime, bool $isnumeric = true) {
        $this->instructortime = $isnumeric ? $instructortime : logbook::convert_time($instructortime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight PIC under supervision time in minutes.
     *
     * @param mixed $instructortime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_picustime($picustime, bool $isnumeric = true) {
        $this->picustime = $isnumeric ? $picustime : logbook::convert_time($picustime, 'MINS_TO_NUM');
    }

    /**
     * Set the flight instructor time in minutes.
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
     * Set the associated linked pirep.
     *
     * @param int $linkedpirep
     */
    public function set_linkedpirep(int $linkedpirep) {
        $this->linkedpirep = $linkedpirep;
    }

    /**
     * Populates a log book entry with a modal form data.
     *
     * ***************************************************   FLIGHT RULES   ****************************************************
     *
     *  P1 (Left-hand seat  LHS) : Instructor or student in Multicrew ops and always the instructor in Dual ops.
     *  P2 (Right-hand seat RHS) : Instructor or student in Multicrew ops. Not applicable in Dual ops.
     *
     *  ------------------------------------------------------------------------------------------------------------------------
     *   Dual operations
     *  ------------------------------------------------------------------------------------------------------------------------
     *     Dual time      : booked for the student only.
     *     PIC time       : booked for the instructor, examiner, and the student in solo flights.
     *     PICUS time     : booked for the student in skill tests only, given the student passes, otherwise Dual time.
     *     Instructor time: booked for the instructor in training flights.
     *     Examiner time  : booked for the examiner in skill test flights regardless pass/fail.
     *     Ground time    : booked for the student and instructor.
     *
     *  ------------------------------------------------------------------------------------------------------------------------
     *   Multicrew operations
     *  ------------------------------------------------------------------------------------------------------------------------
     *     Multipilot time: booked for the student and instructor.
     *     PIC time       : booked for the instructor only.
     *     PICUS time     : booked for the student in a command check only, given the student passes, otherwise Multipilot time.
     *     Copilot time   : booked for the student as P2.
     *     Instructor time: booked for the instructor in training flights.
     *     Examiner time  : booked the examiner in skill test flights regardless pass/fail.
     *     Ground time    : booked for the student and instructor.
     *
     * *************************************************************************************************************************
     *
     * @param object $formdata
     * @param bool $isinstructor
     * @param bool $edit
     */
    public function populate(object $formdata, bool $isinstructor = false, bool $edit = false) {
        $this->id = $formdata->id ?: null;
        $this->exerciseid = $formdata->exerciseid;
        $this->sessionid  = $formdata->sessionid;
        $this->flightdate = $formdata->flightdate;
        $pirep = (property_exists('formdata', 'p2pirep') || $edit || $formdata->flighttypehidden == 'solo') ? $formdata->p1pirep : '';
        $this->pirep = $isinstructor || $edit || $formdata->flighttypehidden == 'solo' ? ($pirep ?: 0) : ($pirep ?: $formdata->linkedpirep);
        $this->p1id = $formdata->flighttypehidden == 'solo' ? $this->parent->get_userid() : $formdata->p1id;
        $this->p2id = $formdata->flighttypehidden == 'solo' ? 0 : $formdata->p2id;

        // flight rules
        $pictimerule        = $isinstructor || $formdata->flighttypehidden == 'solo';
        $dualtimerule       = !$isinstructor && $formdata->trainingtype == 'Dual' && $formdata->flighttypehidden == 'training';
        $multipilottimerule = $formdata->trainingtype == 'Multicrew';
        $copilottimerule    = !$isinstructor && $formdata->trainingtype == 'Multicrew' &&
                                ($formdata->flighttypehidden == 'training' || $formdata->flighttypehidden == 'check' && $formdata->passfail == 'fail');
        $ifrtimerule        = $formdata->flightrule == 'ifr';
        $instructortimerule = $isinstructor && $formdata->flighttypehidden == 'training';
        $picustimerule      = !$isinstructor && $formdata->flighttypehidden == 'check' && $formdata->passfail == 'pass';
        $checkpilottimerule = $isinstructor && $formdata->flighttypehidden == 'check' && $formdata->checkpilottime != 0;
        $landingsdayrule    = $isinstructor || $formdata->flighttypehidden == 'solo' || $edit;
        $landingsnightrule  = $isinstructor || $formdata->flighttypehidden == 'solo' || $edit;

        // process flight times first for the different flight types (Training, Solo, and Check flights pass/fail)
        // handle missing ground time in solo and check flights
        $this->groundtime   = !empty($formdata->groundtime) ? logbook::convert_time($formdata->groundtime, 'MINS_TO_NUM') : 0;
        $this->flighttime   = logbook::convert_time($formdata->flighttime, 'MINS_TO_NUM');
        $this->pictime      = $pictimerule ? logbook::convert_time($formdata->flighttime, 'MINS_TO_NUM') : 0;
        $this->dualtime     = $dualtimerule ? logbook::convert_time($formdata->flighttime, 'MINS_TO_NUM') : 0;
        $this->multipilottime = $multipilottimerule  ? logbook::convert_time($formdata->flighttime, 'MINS_TO_NUM') : 0;
        $this->copilottime  = $copilottimerule ? logbook::convert_time($formdata->flighttime, 'MINS_TO_NUM') : 0;
        $this->ifrtime      = $ifrtimerule ? logbook::convert_time($formdata->ifrtime, 'MINS_TO_NUM') : 0;
        $this->instructortime = $instructortimerule ? logbook::convert_time($formdata->flighttime, 'MINS_TO_NUM') : 0;
        $this->picustime    = $picustimerule ? logbook::convert_time($formdata->flighttime, 'MINS_TO_NUM') : 0;
        $this->checkpilottime = $checkpilottimerule ? logbook::convert_time($formdata->checkpilottime, 'MINS_TO_NUM') : 0;

        // additional logentry data
        $this->callsign     = strtoupper($formdata->callsign);
        $this->depicao      = strtoupper($formdata->depicao);
        $this->arricao      = strtoupper($formdata->arricao);
        // convert from 24hr time format to a timestamp given the flightdate date start
        $this->deptime      = logbook::convert_time($formdata->deptime, 'TIME_TO_TS', strtotime("today", $formdata->flightdate));
        $this->arrtime      = logbook::convert_time($formdata->arrtime, 'TIME_TO_TS', strtotime("today", $formdata->flightdate));
        $this->aircraft     = $formdata->aircraft;
        $this->aircraftreg  = $formdata->aircraftreg;
        $this->enginetype   = $formdata->enginetype;
        $this->route        = $formdata->route;

        // assign landings day/night of p1 to instructor, solo, or editing mode case otherwise use p2 for the student
        $this->landingsday  = $landingsdayrule ? $formdata->landingsp1day : $formdata->landingsp2day;
        $this->landingsnight = $landingsnightrule ? $formdata->landingsp1night : $formdata->landingsp2night;

        $this->nighttime    = $formdata->nighttime ? logbook::convert_time($formdata->nighttime, 'MINS_TO_NUM') : 0;
        $this->remarks      = $formdata->remarks;
        $this->fstd         = $formdata->fstd;
        $this->flighttype   = $formdata->flighttypehidden;
        $this->linkedlogentryid = $formdata->linkedlogentryid ?: 0;
        $this->linkedpirep  = $isinstructor || $edit || $formdata->flighttypehidden == 'solo' ? ($pirep ?: $formdata->linkedpirep) : $formdata->p1pirep;
    }

    /**
     * Converts the object to array.
     *
     * @param bool  $formattostring: whether to return some values in string format
     * @param bool  $nullable:       whether to return null values or not
     * @param bool  $shortdate:      whether to show short vs long date formats
     * @return array
     */
    public function __toArray(bool $formattostring = false, bool $nullable = true, bool $shortdate = false) {
        $logentryarray = [];
        $logentryarray['id'] = $this->id;
        $logentryarray['exerciseid'] = $this->exerciseid;
        $logentryarray['sessionid']  = $this->sessionid;
        $logentryarray['flightdate'] = $this->get_flightdate($formattostring && $nullable, $shortdate);
        $logentryarray['p1id'] = $this->p1id;
        $logentryarray['p2id'] = $this->p2id;
        $logentryarray['groundtime'] = $this->get_groundtime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['flighttime'] = $this->get_flighttime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['pictime'] = $this->get_pictime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['dualtime'] = $this->get_dualtime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['instructortime'] = $this->get_instructortime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['picustime'] = $this->get_picustime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['multipilottime'] = $this->get_multipilottime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['copilottime'] = $this->get_copilottime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['checkpilottime'] = $this->get_checkpilottime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['totalsessiontime'] = $this->get_totalsessiontime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['pirep'] = $this->pirep ?: '';
        $logentryarray['linkedpirep'] = $this->linkedpirep ?: '';
        $logentryarray['callsign'] = $this->callsign;
        $logentryarray['depicao'] = $this->depicao;
        $logentryarray['deptime'] = $formattostring ? logbook::convert_time($this->deptime, 'TS_TO_TIME') : $this->deptime;
        $logentryarray['arricao'] = $this->arricao;
        $logentryarray['arrtime'] = $formattostring ? logbook::convert_time($this->arrtime, 'TS_TO_TIME') : $this->arrtime;
        $logentryarray['aircraft'] = $this->aircraft;
        $logentryarray['aircraftreg'] = $this->aircraftreg;
        $logentryarray['enginetype'] = $this->enginetype;
        $logentryarray['route'] = $this->route;
        $logentryarray['landingsday'] = $this->landingsday;
        $logentryarray['landingsnight'] = $this->landingsnight;
        $logentryarray['nighttime'] = $this->get_nighttime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['ifrtime'] = $this->get_ifrtime(!$formattostring) ?: ($nullable ? null : 0);
        $logentryarray['fstd'] = $this->fstd;
        $logentryarray['remarks'] = $this->remarks;
        $logentryarray['linkedlogentryid'] = $this->linkedlogentryid;
        $logentryarray['se'] = $this->enginetype == 'SE' ? 'X' : ($nullable ? null : '');
        $logentryarray['me'] = $this->enginetype == 'ME' ? 'X' : ($nullable ? null : '');
        $logentryarray['flighttype'] = $this->flighttype;

        return $logentryarray;
    }

    /**
     * Reads a record array and populates the logentery
     * with array's key=>value pairs.
     *
     * @param array $record
     * @return logentry
     */
    public function read(array $record) {
        // assign record's values according to keys' requivalent logentry property
        // evaluate results for different types of properties.
        foreach ($record as $key => $value) {
            // TODO: PHP9 deprecates dynamic properties
            switch ($key) {
                case 'linkedpirep':
                    $this->$key = $value ?: '';
                    break;
                case 'flightdate':
                    $this->$key = strtotime($value);
                    break;
                case 'deptime':
                case 'arrtime':
                    $value = substr_replace($value, ':', 2, 0);
                    $this->$key = logbook::convert_time($value, 'TIME_TO_TS', $this->flightdate);
                    break;
                default:
                    if (!is_null($value))
                        $this->$key = $value;
                    break;
                }
        }
    }
}
