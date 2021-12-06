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
     * @param logbook
     */
    public function get_parent();

    /**
     * Get the id for the logbook entry.
     *
     * @return int
     */
    public function get_id();

    /**
     * Get the course exercise id for the log entry.
     *
     * @return int
     */
    public function get_exerciseid();

    /**
     * Get the aircraft type of the log entry.
     *
     * @return string
     */
    public function get_aircraft();

    /**
     * Get the flight duration minutes of the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_flighttimemins(bool $numeric = true);

    /**
     * Get the session duration minutes of the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_sessiontimemins(bool $numeric = true);

    /**
     * Get the solo flight duration minutes of the log entry.
     *
     * @param bool $numeric whether the request value in number or text format
     * @return mixed
     */
    public function get_soloflighttimemins(bool $numeric = true);

    /**
     * Get the pilot in command user id of the log entry.
     *
     * @return int
     */
    public function get_picid();

    /**
     * Get the pilot in command name of the log entry.
     *
     * @return string
     */
    public function get_picname();

    /**
     * Get the secondary in command user id of the log entry.
     *
     * @return int
     */
    public function get_sicid();

    /**
     * Get the secondary in command name of the log entry.
     *
     * @return string
     */
    public function get_sicname();

    /**
     * Get the PIREP string of log entry.
     *
     * @return string $pirep
     */
    public function get_pirep();

    /**
     * Get the flight callsign of the log entry.
     *
     * @return string
     */
    public function get_callsign();

    /**
     * Get the flight departure airport ICAO of the log entry.
     *
     * @return string
     */
    public function get_fromicao();

    /**
     * Get the flight arrival airport ICAO of the log entry.
     *
     * @return string
     */
    public function get_toicao();

    /**
     * Get the date timestamp of the log entry.
     *
     * @param bool $formatted string formatting of the date
     * @return mixed
     */
    public function get_sessiondate(bool $formatted = false);

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
     * Set the aircraft type of the log entry.
     *
     * @param string $aircraft
     */
    public function set_aircraft(string $aircraft);

    /**
     * Set the flight duration minutes of the log entry.
     *
     * @param mixed $flighttimemins The flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_flighttimemins($flighttimemins, bool $isnumeric = true);

    /**
     * Set the session duration minutes of the log entry.
     *
     * @param mixed $sessiontimemins The session time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_sessiontimemins($sessiontimemins, bool $isnumeric = true);

    /**
     * Set the solo flight time duration minutes of the log entry.
     *
     * @param mixed $soloflighttimemins The solo flight time total minutes duration
     * @param bool $isnumeric whether the passed duration is numberic or string format
     */
    public function set_soloflighttimemins($soloflighttimemins, bool $isnumeric = true);

    /**
     * Set the pilot in command user id of the log entry.
     *
     * @param int $picid
     */
    public function set_picid(int $picid);

    /**
     * Set the secondary in command user id of the log entry.
     *
     * @param int $sicid
     */
    public function set_sicid(int $sicid);

    /**
     * Set the PIREP string of log entry.
     *
     * @param string $pirep
     */
    public function set_pirep(string $pirep);

    /**
     * Set the flight callsign of the log entry.
     *
     * @param string $callsign
     */
    public function set_callsign(string $callsign);

    /**
     * Set the flight departure airport ICAO of the log entry.
     *
     * @param string $fromicao
     */
    public function set_fromicao(string $fromicao);

    /**
     * Set the flight arrival airport ICAO of the log entry.
     *
     * @param string
     */
    public function set_toicao(string $toicao);

    /**
     * Set the date timestamp of the log entry.
     *
     * @param int $sessiondate
     */
    public function set_sessiondate(int $sessiondate);

    /**
     * Populates a log book entry with a modal form data.
     *
     * @param object $formdata
     */
    public function populate(object $formdata);
}
