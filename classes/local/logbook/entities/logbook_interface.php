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
 * Logbook interface
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\entities;


defined('MOODLE_INTERNAL') || die();

/**
 * Interface for a log entry class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface logbook_interface {

    /**
     * Loads the logbook of entries for a user.
     *
     * @return bool true if the Logbook has entries
     */
    public function load();

    /**
     * Whether the logbook as entries or not.
     *
     * @return bool true if the Logbook has entries
     */
    public function has_entries();

    /**
     * Creates a logbook entry.
     *
     * @return logentry
     */
    public function create_logentry();

    /**
     * Save a logbook entry.
     *
     * @return bool
     */
    public function add(logentry $logentry);

    /**
     * Update a logbook entry.
     *
     * @return bool
     */
    public function update(logentry $logentry);

    /**
     * Deletes a logbook entry.
     *
     * @return bool
     */
    public function delete(int $logentryid);

    /**
     * Load a logbook entry.
     *
     * @return logentry
     */
    public function get_logentry(int $logentryid);

    /**
     * Get the course id for the log entry.
     *
     * @return int
     */
    public function get_courseid();

    /**
     * Get the user user id of the log entry.
     *
     * @return int
     */
    public function get_userid();

    /**
     * Get the user name of the log entry.
     *
     * @return string
     */
    public function get_username();

    /**
     * Set the course  id for the log entry.
     *
     * @param int $courseid
     */
    public function set_courseid(int $courseid);

    /**
     * Set the studnet user id of the log entry.
     *
     * @param int $userid
     */
    public function set_userid(int $userid);
}
