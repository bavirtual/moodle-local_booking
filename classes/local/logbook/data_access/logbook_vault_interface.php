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
 * Logbook data access
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\data_access;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\logbook\entities\logentry;

interface logbook_vault_interface {

    /**
     * Get a user's logbook.
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param logbook   $logbook    The logbook_interface of for all entries.
     * @param bool      $allentries Whether to get entries for all courses
     * @return logentries[]     Array of logentry_interfaces.
     */
    public static function get_logbook(int $courseid, int $userid, $logbook, $allentries);

    /**
     * Get a specific logbook entry.
     *
     * @param int $userid       The logentry user id.
     * @param int $courseid     The id of the course in context.
     * @param int $logentryid   The logentry id.
     * @param int $exerciseid   The logentry with exericse id.
     * @param logbook $logbook  The logbook_interface of for all entries.
     * @return logentry         A logentry_insterface.
     */
    public static function get_logentry(int $userid, int $courseid, int $logentryid = 0, int $exerciseid = 0, $logbook);

    /**
     * Update a user's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @return array    $totaldualtime, $totalgroundtime, $totalpictime
     */
    public static function get_logbook_summary(int $courseid, int $userid);

    /**
     * Create a user's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param logentry  $logentry   A logbook entry of the user.
     * @return bool     $result of the database add operation.
     */
    public static function insert_logentry(int $courseid, int $userid, logentry $logentry);

    /**
     * Update a user's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param logentry  $logentry   A logbook entry of the user.
     * @return bool     $result of the database update operation.
     */
    public static function update_logentry(int $courseid, int $userid, logentry $logentry);

    /**
     * Insert/Update then link two logentries.
     *
     * @param int $courseid
     * @param logentry $logentry1
     * @param logentry $logentry2
     * @return bool
     */
    public static function save_linked_logentries(int $courseid, logentry $logentry1, logentry $logentry2);

    /**
     * Delete a logbook entry by id with its associated entries.
     *
     * @param int   $logentryid         The logbook entry id to be deleted.
     * @param int   $linkedlogentryid   The associated logbook entry id to be deleted.
     * @return bool result of the database update operation.
     */
    public static function delete_logentry(int $logentryid, int $linkedlogentryid = 0);
}
