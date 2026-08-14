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

namespace local_booking;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

/**
 * Helpers to restore Mac-dev course copies into PHPUnit.
 *
 * @package    local_booking
 */
trait copy_course_helper {

    /**
     * Source course ids copied from the development site.
     */
    public const COPIED_COURSE_IDS = [10, 33, 52];

    /**
     * Path to a course fixture backup.
     *
     * @param int $sourcecourseid
     * @return string
     */
    protected function copied_course_fixture_path(int $sourcecourseid): string {
        $files = glob(__DIR__ . '/fixtures/courses/course' . $sourcecourseid . '_*.mbz');
        return $files[0] ?? (__DIR__ . '/fixtures/courses/course' . $sourcecourseid . '.mbz');
    }

    /**
     * Whether a course fixture is available on this machine.
     *
     * @param int $sourcecourseid
     * @return bool
     */
    protected function copied_course_fixture_available(int $sourcecourseid): bool {
        $path = $this->copied_course_fixture_path($sourcecourseid);
        return is_readable($path) && filesize($path) > 0;
    }

    /**
     * Restore a copied course and enrol an instructor and a student.
     *
     * @param int $sourcecourseid
     * @return \stdClass
     */
    protected function restore_copied_course(int $sourcecourseid): \stdClass {
        global $DB, $USER;

        if (!$this->copied_course_fixture_available($sourcecourseid)) {
            $this->markTestSkipped(
                'Course ' . $sourcecourseid . ' fixture is missing. Export it on this Mac with tests/fixtures/courses/export.php'
            );
        }

        $this->setAdminUser();

        $backupid = 'copy' . $sourcecourseid . '_' . uniqid();
        $backuppath = make_backup_temp_directory($backupid);
        get_file_packer('application/vnd.moodle.backup')->extract_to_pathname(
            $this->copied_course_fixture_path($sourcecourseid),
            $backuppath
        );

        $categoryid = $DB->get_field_sql('SELECT MIN(id) FROM {course_categories}');
        $newcourseid = \restore_dbops::create_new_course(
            'PHPUnit copy ' . $sourcecourseid,
            'phpucopy' . $sourcecourseid . '_' . substr($backupid, -6),
            $categoryid
        );

        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        $course = get_course($newcourseid);
        $this->ensure_course_subscribed($course->id);
        $this->ensure_booking_groups($course->id);
        $users = $this->enrol_instructor_and_student($course);

        $exercise = $this->first_exercise_cm($course->id);

        return (object) [
            'course' => $course,
            'teacher' => $users['teacher'],
            'student' => $users['student'],
            'exerciseid' => $exercise ? (int) $exercise->id : 0,
            'sourcecourseid' => $sourcecourseid,
        ];
    }

    /**
     * Create or update a course custom field value.
     *
     * @param int $courseid
     * @param string $shortname
     * @param string $value
     * @param int|null $intvalue
     */
    protected function ensure_course_customfield(int $courseid, string $shortname, string $value, ?int $intvalue = null): void {
        global $DB;

        $fieldid = $DB->get_field('customfield_field', 'id', ['shortname' => $shortname], MUST_EXIST);
        $record = $DB->get_record('customfield_data', ['fieldid' => $fieldid, 'instanceid' => $courseid]);
        $now = time();
        if ($record) {
            $record->value = $value;
            if ($intvalue !== null) {
                $record->intvalue = $intvalue;
            }
            $record->timemodified = $now;
            $DB->update_record('customfield_data', $record);
            return;
        }

        $DB->insert_record('customfield_data', (object) [
            'fieldid' => $fieldid,
            'instanceid' => $courseid,
            'intvalue' => $intvalue,
            'value' => $value,
            'valueformat' => 1,
            'valuetrust' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
            'contextid' => \context_course::instance($courseid)->id,
        ]);
    }

    /**
     * Mark the restored course as a Session Booking subscriber.
     *
     * @param int $courseid
     */
    protected function ensure_course_subscribed(int $courseid): void {
        $this->ensure_course_customfield($courseid, 'subscribed', '1', 1);
        \cache::make('local_booking', 'subscribed')->delete($courseid);
    }

    /**
     * Enrol an editing teacher and a student on the course.
     *
     * @param \stdClass $course
     * @return array
     */
    protected function enrol_instructor_and_student(\stdClass $course): array {
        global $DB;

        $generator = $this->getDataGenerator();
        if (!$DB->record_exists('role', ['shortname' => LOCAL_BOOKING_INSTRUCTORROLE])) {
            $generator->create_role([
                'shortname' => LOCAL_BOOKING_INSTRUCTORROLE,
                'name' => 'Instructor',
                'archetype' => 'editingteacher',
            ]);
        }

        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        role_assign(
            $DB->get_field('role', 'id', ['shortname' => LOCAL_BOOKING_INSTRUCTORROLE], MUST_EXIST),
            $teacher->id,
            \context_course::instance($course->id)->id
        );

        return [
            'teacher' => $teacher,
            'student' => $generator->create_and_enrol($course, 'student'),
        ];
    }

    /**
     * Ensure Session Booking groups exist on the course.
     *
     * @param int $courseid
     */
    protected function ensure_booking_groups(int $courseid): void {
        global $DB;

        $generator = $this->getDataGenerator();
        foreach ([
            LOCAL_BOOKING_ONHOLDGROUP,
            LOCAL_BOOKING_KEEPACTIVEGROUP,
            LOCAL_BOOKING_GRADUATESGROUP,
            LOCAL_BOOKING_INACTIVEGROUP,
        ] as $groupname) {
            if (!$DB->record_exists('groups', ['courseid' => $courseid, 'name' => $groupname])) {
                $generator->create_group([
                    'courseid' => $courseid,
                    'name' => $groupname,
                ]);
            }
        }
    }

    /**
     * First visible assignment or quiz course module.
     *
     * @param int $courseid
     * @return \cm_info|null
     */
    protected function first_exercise_cm(int $courseid): ?\cm_info {
        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->visible && ($cm->modname === 'assign' || $cm->modname === 'quiz')) {
                return $cm;
            }
        }
        return null;
    }
}
