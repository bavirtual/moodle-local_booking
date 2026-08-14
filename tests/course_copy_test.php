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
require_once($CFG->dirroot . '/local/booking/lib.php');
require_once(__DIR__ . '/copy_course_helper.php');

/**
 * Restore development courses 10, 33, and 52 and enrol test users.
 *
 * @package    local_booking
 * @covers     \local_booking\copy_course_helper
 */
final class course_copy_test extends \advanced_testcase {
    use copy_course_helper;

    /**
     * Reset the database after each test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Copied course ids and the activity types each is expected to contain.
     *
     * @return array
     */
    public static function copied_course_provider(): array {
        return [
            'PPL' => [10, [
                'assign' => 1,
                'lesson' => 1,
                'quiz' => 1,
            ]],
            'A320 Line Training' => [33, [
                'assign' => 1,
                'checklist' => 1,
            ]],
            'SEIR' => [52, [
                'assign' => 1,
                'lesson' => 1,
                'quiz' => 1,
            ]],
        ];
    }

    /**
     * Each copied course keeps its sections and key activities, with an instructor and student enrolled.
     *
     * @dataProvider copied_course_provider
     * @param int $sourcecourseid
     * @param array $expectedmods
     */
    public function test_copied_course_content_and_enrolments(int $sourcecourseid, array $expectedmods): void {
        global $DB;

        $setup = $this->restore_copied_course($sourcecourseid);
        $courseid = $setup->course->id;
        $context = \context_course::instance($courseid);

        $this->assertGreaterThan(1, $DB->count_records('course_sections', ['course' => $courseid]));
        $this->assertTrue(is_enrolled($context, $setup->teacher, '', true));
        $this->assertTrue(is_enrolled($context, $setup->student, '', true));
        $this->assertGreaterThan(0, $setup->exerciseid);

        $modinfo = get_fast_modinfo($courseid);
        foreach ($expectedmods as $modname => $minimum) {
            $this->assertGreaterThanOrEqual(
                $minimum,
                count($modinfo->get_instances_of($modname)),
                "Course {$sourcecourseid} should contain {$modname} activities"
            );
        }

        $this->assertTrue($DB->record_exists('groups', [
            'courseid' => $courseid,
            'name' => LOCAL_BOOKING_ONHOLDGROUP,
        ]));
        $this->assertTrue($DB->record_exists('local_booking_progress', [
            'courseid' => $courseid,
            'userid' => $setup->student->id,
        ]));
    }
}
