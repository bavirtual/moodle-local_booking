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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Export development courses 10, 33, and 52 as PHPUnit fixtures (no users/files).
 *
 * Run from the Moodle root against the development database:
 *   php local/booking/tests/fixtures/courses/export.php
 *
 * @package    local_booking
 */

define('CLI_SCRIPT', true);

require dirname(__DIR__, 5) . '/config.php';
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

if (defined('PHPUNIT_TEST') && PHPUNIT_TEST) {
    fwrite(STDERR, "Run this script against the development site, not PHPUnit.\n");
    exit(1);
}

$courseids = [10, 33, 52];
$destdir = __DIR__;
$admin = get_admin();
$include = ['activities' => true, 'customfields' => true];
$exclude = [
    'users', 'anonymize', 'role_assignments', 'blocks', 'files', 'filters', 'comments',
    'badges', 'calendarevents', 'userscompletion', 'logs', 'grade_histories', 'groups',
    'competencies', 'contentbankcontent', 'legacyfiles',
];

foreach ($courseids as $courseid) {
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    mtrace("Backing up {$course->id} {$course->shortname}...");

    $bc = new backup_controller(
        backup::TYPE_1COURSE,
        $course->id,
        backup::FORMAT_MOODLE,
        backup::INTERACTIVE_NO,
        backup::MODE_GENERAL,
        $admin->id
    );
    $plan = $bc->get_plan();
    foreach ($include as $name => $value) {
        if ($plan->setting_exists($name)) {
            $setting = $plan->get_setting($name);
            $setting->set_status(base_setting::NOT_LOCKED);
            $setting->set_value($value);
        }
    }
    foreach ($exclude as $name) {
        if ($plan->setting_exists($name)) {
            $setting = $plan->get_setting($name);
            $setting->set_status(base_setting::NOT_LOCKED);
            $setting->set_value(false);
        }
    }

    $filename = 'course' . $course->id . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $course->shortname) . '.mbz';
    $plan->get_setting('filename')->set_value($filename);
    $bc->execute_plan();
    $results = $bc->get_results();
    $file = $results['backup_destination'];
    $target = $destdir . '/' . $filename;
    if (!$file || !$file->copy_content_to($target)) {
        fwrite(STDERR, "Failed to write $target\n");
        $bc->destroy();
        exit(1);
    }
    $file->delete();
    $bc->destroy();
    mtrace("Wrote $target (" . round(filesize($target) / 1048576, 2) . " MB)");
}

mtrace('Done.');
