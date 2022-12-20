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
 * Upgrade code for install
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @category   Uninstall
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \core_customfield\api;

/**
 * upgrade the logentry - this function could be skipped but it will be needed later
 * @param int $oldversion The old version of session booking
 * @return bool
 */
function xmldb_local_booking_upgrade($oldversion) {
    global $DB;

    $dbmanager = $DB->get_manager();

    // Automatically generated Moodle v3.11.0 release upgrade line.
    // Put any upgrade step following this.

    // change local_booking_sessions table to include a noshow field to track student no-show occurrences
    if ($oldversion < 2022122001) {
        // Define field hidegrader to be added to local_booking_sessions.
        $table = new xmldb_table('local_booking_sessions');
        $field = new xmldb_field('noshow', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0', 'confirmed');

        if (!$dbmanager->field_exists($table, $field)) {
            $dbmanager->add_field($table, $field);
        }

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2022122001, 'local', 'booking');
    }

    // change Session booking settings in course settings category from the ATO name to Session booking
    if ($oldversion < 2022102600) {
        if ($atoname = get_config('local_booking', 'atoname')) {

            // get all categories for the site
            $categories = api::get_categories_with_fields('core_course', 'course', 0);

            foreach ($categories as $coursecategory) {

                $categoryname = $coursecategory->get('name');

                // update subscribing course custom field category label w/ the plugin name 'Session booking'
                if ($categoryname == $atoname) {
                    $coursecategory->set('name', ucfirst(get_string('pluginname', 'local_booking')));
                    api::save_category($coursecategory);
                }
            }
        }

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2022102600, 'local', 'booking');
    }

    // change the PIREP field from the old char(50) to int(10)
    if ($oldversion < 2022100900) {
        // Changing type of field attachment on table block_quickmail_log to text.
        $table = new xmldb_table('local_booking_logbooks');
        $field = new xmldb_field('pirep', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'p2id');

        // Launch change of type for field attachment.
        $dbmanager->change_field_type($table, $field);

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2022100900, 'local', 'booking');
    }

    // add the flight time field
    if ($oldversion < 2022100700) {
        // Define field hidegrader to be added to logbooks.
        $table = new xmldb_table('local_booking_logbooks');
        $field = new xmldb_field('flighttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'groundtime');

        if (!$dbmanager->field_exists($table, $field)) {
            $dbmanager->add_field($table, $field);
        }

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2022100700, 'local', 'booking');
    }

    return true;
}
