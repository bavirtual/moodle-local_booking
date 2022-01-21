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
 * Logbook entry interface
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\entities;

use DateTime;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for a log entry class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface logentry_interface {

    /**
     * Saves a logbook entry (create or update).
     *
     * @return bool
     */
    public function save();

    /**
     * Get the parent logbook.
     *
     * @return logbook
     */
    public function get_parent();

    /**
     * Get the id for the logbook entry.
     *
     * @param int
     */
    public function get_id();

    /**
     * Get the user id for the logbook entry (student).
     *
     * @param int
     */
    public function get_userid();

    /**
     * Get the course exercise id for the log entry.
     *
     * @return int
     */
    public function get_exerciseid();

    /**
     * Get the PIREP string of log entry.
     *
     * @return string $pirep
     */
    public function get_pirep();

    /**
     * Get the linked PIREP string of logbook entry.
     *
     * @return string $linkedpirep
     */
    public function get_linkedpirep();

    /**
     * Get the flight callsign.
     *
     * @return string
     */
    public function get_callsign();

    /**
     * Get the flight date timestamp.
     *
     * @return mixed
     */
    public function get_flightdate(bool $tostring = false, bool $shortdate = false);

    /**
     * Get the flight departure airport ICAO.
     *
     * @return string
     */
    public function get_depicao();

    /**
     * Get the flight departure time.
     *
     * @return int
     */
    public function get_deptime();

    /**
     * Get the flight arrival airport ICAO.
     *
     * @return string
     */
    public function get_arricao();

    /**
     * Get the flight arrival time.
     *
     * @return int
     */
    public function get_arrtime();

    /**
     * Get the aircraft type.
     *
     * @return string
     */
    public function get_aircraft();

    /**
     * Get the aircraft registration.
     *
     * @return string
     */
    public function get_aircraftreg();

    /**
     * Get the aircraft engine type.
     *
     * @return string
     */
    public function get_enginetype();

    /**
     * Get the multipilot time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_multipilottime(bool $numeric = true);

    /**
     * Get the P1 (instructor) id.
     *
     * @return int
     */
    public function get_p1id();

    /**
     * Get the P2 (student) id.
     *
     * @return int
     */
    public function get_p2id();

    /**
     * Get the pilot 1 name.
     *
     * @return string
     */
    public function get_p1name();

    /**
     * Get the number of day landings for the flight..
     *
     * @return int
     */
    public function get_landingsday();

    /**
     * Get the number of night landings for the flight..
     *
     * @return int
     */
    public function get_landingsnight();

    /**
     * Get the training session time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_groundtime(bool $numeric = true);

    /**
     * Get the flying at night time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_nighttime(bool $numeric = true);

    /**
     * Get the flying IFR time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_ifrtime(bool $numeric = true);

    /**
     * Get the flying as PIC time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_pictime(bool $numeric = true);

    /**
     * Get the flying as copilot time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_copilottime(bool $numeric = true);

    /**
     * Get the flight instructor/student time minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_dualtime(bool $numeric = true);

    /**
     * Get the flight instructor time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_instructortime(bool $numeric = true);

    /**
     * Get the flight PIC under supervision time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_picustime(bool $numeric = true);

    /**
     * Get the examiner checkride time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_checkpilottime(bool $numeric = true);

    /**
     * Get the total time for the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_totaltime(bool $numeric = true);

    /**
     * Get the Flight Simulation Training Device qualification.
     *
     * @return string
     */
    public function get_fstd();

    /**
     * Get the instructor remarks.
     *
     * @return string
     */
    public function get_remarks();

    /**
     * Get the associated logentry.
     *
     * @return int
     */
    public function get_linkedlogentryid();

    /**
     * Get the flight type.
     *
     */
    public function get_flighttype();

    /**
     * Set the parent logbook.
     *
     * @param logbook
     */
    public function set_parent(logbook $logbook);

    /**
     * Set the id for the logbook entry.
     *
     * @param int
     */
    public function set_id(int $id);

    /**
     * Set the course exercise id for the log entry.
     *
     * @param int $exerciseid
     */
    public function set_exerciseid(int $exerciseid);

    /**
     * Set the PIREP string of log entry.
     *
     * @param string $pirep
     */
    public function set_pirep(string $pirep);

    /**
     * Set the associated linked pirep.
     *
     * @param int $linkedpirep
     */
    public function set_linkedpirep(int $linkedpirep);

    /**
     * Set the flight callsign.
     *
     * @param string $callsign
     */
    public function set_callsign(string $callsign);

    /**
     * Set the flight type.
     *
     * @param string $flighttype
     */
    public function set_flighttype(string $flighttype);

    /**
     * Set the flight date timestamp.
     *
     * @param int $flightdate
     */
    public function set_flightdate(int $flightdate);

    /**
     * Set the flight departure airport ICAO.
     *
     * @param string $depicao
     */
    public function set_depicao(string $depicao);

    /**
     * Set the flight departure time.
     *
     * @param int $deptime departure time timestamp
     */
    public function set_deptime($deptime);

    /**
     * Set the flight arrival airport ICAO.
     *
     * @return string
     */
    public function set_arricao(string $arricao);

    /**
     * Set the flight arrival time.
     *
     * @param int $arrtime arrival time timestamp
     */
    public function set_arrtime($arrtime);

    /**
     * Set the aircraft type.
     *
     * @return string
     */
    public function set_aircraft(string $aircraft);

    /**
     * Set the aircraft registration.
     *
     * @return string
     */
    public function set_aircraftreg(string $aircraftreg);
    /**
     * Set the aircraft engine type.
     *
     * @return string
     */
    public function set_enginetype(string $enginetype) ;

    /**
     * Set the flight multipilot time in minutes.
     *
     * @param mixed $multipilottime The flight multipilot time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_multipilottime($multipilottime, bool $isnumeric = true);

    /**
     * Set the p1 (instructor) id.
     *
     * @param int $p1id
     */
    public function set_p1id(int $p1id);

    /**
     * Set the p2 (student) id.
     *
     * @param int $p2id
     */
    public function set_p2id(int $p2id);

    /**
     * Set the number of day landings for the flight.
     *
     * @param int $landingsday
     */
    public function set_landingsday(int $landingsday);

    /**
     * Set the number of night landings for the flight.
     *
     * @param int $landingsnight
     */
    public function set_landingsnight(int $landingsnight);

    /**
     * Set the training session duration in minutes.
     *
     * @param mixed $groundtime The session time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_groundtime($groundtime, bool $isnumeric = true);

    /**
     * Set the flying at night duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_nighttime($nighttime, bool $isnumeric = true);

    /**
     * Set the flying IFR duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_ifrtime($ifrtime, bool $isnumeric = true);

    /**
     * Set the flying as PIC duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_pictime($pictime, bool $isnumeric = true);

    /**
     * Set the flying as copilot duration in minutes.
     *
     * @param mixed $nighttime The night time minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_copilottime($copilottime, bool $isnumeric = true);

    /**
     * Set the flight instructor/student duration minutes.
     *
     * @param mixed $dualtime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_dualtime($dualtime, bool $isnumeric = true);

    /**
     * Set the flight instructor duration minutes.
     *
     * @param mixed $instructortime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_instructortime($instructortime, bool $isnumeric = true);

    /**
     * Set the flight PIC under supervision time in minutes.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function set_picustime($instructortime, bool $isnumeric = true);

    /**
     * Set the flight instructor duration minutes.
     *
     * @param mixed $checkpilottime The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_checkpilottime($checkpilottime, bool $isnumeric = true);

    /**
     * Set the secondary in command user id.
     *
     * @param string $fstd
     */
    public function set_fstd(string $fstd);

    /**
     * Set the instructor's remarks.
     *
     * @param string $remarks
     */
    public function set_remarks(string $remarks);

    /**
     * Populates a log book entry with a modal form data.
     *
     * @param object $formdata
     * @param bool $isinstructor
     * @param bool $edit
     */
    public function populate(object $formdata, bool $isinstructor = false, bool $edit = false);

    /**
     * Converts the object to array.
     *
     * @param bool  $formattostring: whether to return some values in string format
     * @param bool  $nullable:       whether to return null values or not
     * @param bool  $shortdate:      whether to show short vs long date formats
     * @return array
     */
    public function __toArray(bool $formattostring = false, bool $nullable = true, bool $shortdate = false);

    /**
     * Reads a record array and populates the logentery
     * with array's key=>value pairs.
     *
     * @param array $record
     * @return logentry
     */
    public function read(array $record);
}
