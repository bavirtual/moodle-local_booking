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

    // Add minium slot period course setting
    if ($oldversion < 2024092000) {

        // Get Session booking category
        $categories = \core_customfield\api::get_categories_with_fields('core_course', 'course', 0);
        $category = array_values(array_filter(array_map(function($category) {
                if ($category->get('name') == get_string('pluginname', 'local_booking')) return $category;
            }, $categories)))[0];

        // get the insert before field 'homeicao'
        $fields = $category->get_fields();
        $beforeid = array_key_first(array_filter(array_map(function($field) {
                if ($field->get('shortname') == 'homeicao') return $field;
            }, $fields)));

        // create the minimum slot time period field (minslotperiod)
        $fieldrec = new \stdClass();
        $fieldrec->type = 'text';
        $field = \core_customfield\field_controller::create(0, $fieldrec, $category);
        $field->set('shortname', 'minslotperiod');
        $field->set('name', get_string('minslotperiod', 'local_booking'));
        $field->set('description', '<p dir="ltr" style="text-align:left;">' . get_string('minslotperioddesc', 'local_booking') . '</p>');
        $field->set('descriptionformat', 1);
        $field->set('configdata', '{"required":"0","uniquevalues":"0","locked":"0","visibility":"0","defaultvalue":"2","displaysize":5,"maxlength":2,"ispassword":"0","link":""}');
        $field->save();
        \core_customfield\api::move_field($field, $category->get('id'), $beforeid);
        \core_customfield\event\field_created::create_from_object($field)->trigger();

        // create the lesson completion required field (requirelessoncompletion)
        $fieldrec = new \stdClass();
        $fieldrec->type = 'checkbox';
        $field = \core_customfield\field_controller::create(0, $fieldrec, $category);
        $field->set('shortname', 'requirelessoncompletion');
        $field->set('name', get_string('requirelessoncompletion', 'local_booking'));
        $field->set('description', '<p dir="ltr" style="text-align:left;">' . get_string('requirelessoncompletiondesc', 'local_booking') . '</p>');
        $field->set('descriptionformat', 1);
        $field->set('configdata', '{"required":"0","uniquevalues":"0","locked":"0","visibility":"0","checkbydefault":"0"}');
        $field->save();
        \core_customfield\api::move_field($field, $category->get('id'), $beforeid);
        \core_customfield\event\field_created::create_from_object($field)->trigger();

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2024092000, 'local', 'booking');
    }

    // add minium slot period course setting
    if ($oldversion < 2024101900) {

        // Define table local_booking_stats to be created.
        $table = new xmldb_table('local_booking_stats');

        // Conditionally add field notifyflags.
        $field = new xmldb_field('notifyflags', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'nextexerciseid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Conditionally drop field activeposts.
        $field = new xmldb_field('activeposts');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Update inacurate student statistic records
        $DB->execute("UPDATE mdl_local_booking_stats SET nextexerciseid = 0, currentexerciseid = 0 WHERE currentexerciseid = nextexerciseid");
        $DB->execute("UPDATE mdl_local_booking_stats s SET s.lastsessiondate = (SELECT MAX(b.timemodified) FROM mdl_local_booking_sessions b WHERE b.courseid = s.courseid AND b.studentid = s.userid) WHERE s.lastsessiondate IS NULL");

        // Extend the size of the remarks field in logbooks
        $DB->execute("ALTER TABLE mdl_local_booking_logbooks MODIFY COLUMN remarks VARCHAR(1000)");

        // Assignment savepoint reached.
        upgrade_plugin_savepoint(true, 2024101900, 'local', 'booking');
    }

    return true;
}
