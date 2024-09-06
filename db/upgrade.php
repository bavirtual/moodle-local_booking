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
use local_booking\local\subscriber\entities\subscriber;

/**
 * upgrade the logentry - this function could be skipped but it will be needed later
 * @param int $oldversion The old version of session booking
 * @return bool
 */
function xmldb_local_booking_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.11.0 release upgrade line.
    // Put any upgrade step following this.

    // add sessionid column in logbooks to link log entries to sessions
    if ($oldversion < 2024090600) {

        // Define table local_booking_stats to be created.
        $table = new xmldb_table('local_booking_stats');

        // Adding fields to table local_booking_stats.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('activeposts', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('lessonscomplete', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('lastsessiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('currentexerciseid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('nextexerciseid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        // Adding keys to table local_booking_stats.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('user_course', XMLDB_KEY_UNIQUE, ['userid', 'courseid']);

        // Conditionally launch create table for local_booking_stats.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // add data to the stats table for each course
        $courses = $DB->get_records_sql("SELECT instanceid AS id FROM mdl_customfield_data cd INNER JOIN mdl_customfield_field cf ON cf.id = cd.fieldid WHERE cf.shortname = 'subscribed' AND cd.value = 1");
        foreach ($courses as $course) {
            subscriber::add_new_enrolments($course->id);
        }

        // Add duplicate slots to add unique key for a slot with specific start end for a user in a course
        // Add duplicate slots to a temporary table then delete all records in the slots table
        // Define table local_booking_slots to be created.
        $table = new xmldb_table('local_booking_slots_tmp');

        // Adding fields to table local_booking_slots.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('week', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('year', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('endtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('slotstatus', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('notified', XMLDB_TYPE_INTEGER, '3', null, null, null, '0');
        $table->add_field('bookinginfo', XMLDB_TYPE_CHAR, '500', null, null, null, null);

        // Conditionally launch create table for local_booking_slots.
        if (!$dbman->table_exists($table)) {
            $dbman->create_temp_table($table);
        }

        // Copy slot records to the temporary table, then delete all records from the slots table
        $DB->execute("INSERT INTO mdl_local_booking_slots_tmp (id, userid, courseid, week, `year`, starttime, endtime, slotstatus, notified, bookinginfo)
                      SELECT id, userid, courseid, week, `year`, starttime, endtime, slotstatus, notified, bookinginfo
                      FROM mdl_local_booking_slots
                      WHERE userid != ''");
        $DB->execute("DELETE FROM mdl_local_booking_slots WHERE id != ''");

        // Define key slotunique (unique) to be added to local_booking_slots.
        $table = new xmldb_table('local_booking_slots');
        $key = new xmldb_key('slotunique', XMLDB_KEY_UNIQUE, ['courseid', 'userid', 'starttime', 'endtime']);

        // Launch add key slotunique.
        $dbman->add_key($table, $key);

        // Add records back from tmp table that are mapped to past sessions, then add the rest and ignore duplicates
        $DB->execute("INSERT IGNORE INTO mdl_local_booking_slots (id, userid, courseid, week, `year`, starttime, endtime, slotstatus, notified, bookinginfo)
                        SELECT s.id, s.userid, s.courseid, s.week, s.`year`, s.starttime, s.endtime, s.slotstatus, s.notified, s.bookinginfo
                        FROM mdl_local_booking_slots_tmp s
                        INNER JOIN mdl_local_booking_sessions bs
                        WHERE bs.slotid != ''");
        $DB->execute("INSERT IGNORE INTO mdl_local_booking_slots (userid, courseid, week, `year`, starttime, endtime, slotstatus, notified, bookinginfo)
                        SELECT userid, courseid, week, `year`, starttime, endtime, slotstatus, notified, bookinginfo
                        FROM mdl_local_booking_slots_tmp
                        WHERE userid != ''");

        // Booking savepoint reached.
        upgrade_plugin_savepoint(true, 2024090600, 'local', 'booking');
    }

    // add sessionid column in logbooks to link log entries to sessions
    if ($oldversion < 2023121900) {
        // get table and field info.
        $table = new xmldb_table('local_booking_logbooks');
        $field = new xmldb_field('sessionid', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0', 'userid');

        // Launch addition of the session id field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // update session id to match past logentries for both instructors and students
        $DB->execute('UPDATE mdl_local_booking_logbooks l INNER JOIN mdl_local_booking_sessions s ON (l.courseid = s.courseid AND l.p1id = s.userid AND l.exerciseid = s.exerciseid) SET l.sessionid = s.id');
        $DB->execute('UPDATE mdl_local_booking_logbooks l INNER JOIN mdl_local_booking_sessions s ON (l.courseid = s.courseid AND l.p2id = s.studentid AND l.exerciseid = s.exerciseid) SET l.sessionid = s.id');
        $DB->execute('UPDATE mdl_local_booking_logbooks l INNER JOIN mdl_local_booking_sessions s ON (l.courseid = s.courseid AND l.p1id = s.userid AND l.p2id = s.studentid AND l.exerciseid = s.exerciseid AND s.timemodified IN (SELECT MAX(s2.timemodified) FROM mdl_local_booking_sessions s2 WHERE l.courseid = s2.courseid AND l.p1id = s2.userid AND l.p2id = s2.studentid AND l.exerciseid = s2.exerciseid)) SET l.sessionid = s.id');

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2023121900, 'local', 'booking');
    }

    // change local_booking_sessions table to include a notified field to track student posting notifications
    if ($oldversion < 2023020600) {
        // Define field hidegrader to be added to local_booking_sessions.
        $table = new xmldb_table('local_booking_slots');
        $field = new xmldb_field('notified', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0', 'slotstatus');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // update to notified as they should
        $DB->execute('UPDATE mdl_local_booking_slots SET notified = 1');

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2023020600, 'local', 'booking');
    }

    // change local_booking_sessions table to include a noshow field to track student no-show occurrences
    if ($oldversion < 2022122001) {
        // Define field hidegrader to be added to local_booking_sessions.
        $table = new xmldb_table('local_booking_sessions');
        $field = new xmldb_field('noshow', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0', 'confirmed');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
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
        // get table and field info.
        $table = new xmldb_table('local_booking_logbooks');
        $field = new xmldb_field('pirep', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'p2id');

        // Launch change of type for field pirep.
        $dbman->change_field_type($table, $field);

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2022100900, 'local', 'booking');
    }

    // add the flight time field
    if ($oldversion < 2022100700) {
        // Define field hidegrader to be added to logbooks.
        $table = new xmldb_table('local_booking_logbooks');
        $field = new xmldb_field('flighttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'groundtime');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2022100700, 'local', 'booking');
    }

    return true;
}
