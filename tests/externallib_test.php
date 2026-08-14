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

namespace local_booking;

use core_external\external_api;
use local_booking\external\cancel_booking;
use local_booking\external\save_booking;
use local_booking\external\save_logentry;
use local_booking\external\save_slots;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/booking/lib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once(__DIR__ . '/copy_course_helper.php');

/**
 * External API tests for slot posting, booking, logentries, and checklist updates.
 *
 * @package    local_booking
 * @covers     \local_booking\external\save_slots
 * @covers     \local_booking\external\save_booking
 * @covers     \local_booking\external\cancel_booking
 * @covers     \local_booking\external\save_logentry
 * @covers     \local_booking\external\update_checklist_item
 */
final class externallib_test extends \core_external\tests\externallib_testcase {
    use copy_course_helper;

    /**
     * Reset the database after each test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * A student can save weekly availability slots.
     */
    public function test_save_slots_persists_availability(): void {
        global $DB, $COURSE;

        $setup = $this->create_subscribed_course();
        unset($COURSE->subscriber);

        $slot = $this->future_slot();
        $this->setUser($setup->student);

        $result = save_slots::execute(
            [['starttime' => $slot['starttime'], 'endtime' => $slot['endtime']]],
            $setup->course->id,
            $slot['year'],
            $slot['week']
        );
        $result = external_api::clean_returnvalue(save_slots::execute_returns(), $result);

        $this->assertTrue($result['result']);
        $this->assertEmpty($result['warnings']);
        $this->assertEquals(1, $DB->count_records('local_booking_slots', [
            'courseid' => $setup->course->id,
            'userid' => $setup->student->id,
        ]));
        $record = $DB->get_record('local_booking_slots', [
            'courseid' => $setup->course->id,
            'userid' => $setup->student->id,
        ], '*', MUST_EXIST);
        $this->assertEquals($slot['starttime'], $record->starttime);
        $this->assertEquals($slot['endtime'], $record->endtime);
        $this->assertEquals($slot['year'], $record->year);
        $this->assertEquals($slot['week'], $record->week);
    }

    /**
     * Saving slots for a week replaces the previous unmarked posts.
     */
    public function test_save_slots_replaces_week_slots(): void {
        global $DB, $COURSE;

        $setup = $this->create_subscribed_course();
        unset($COURSE->subscriber);

        $first = $this->future_slot();
        $this->setUser($setup->student);
        save_slots::execute(
            [['starttime' => $first['starttime'], 'endtime' => $first['endtime']]],
            $setup->course->id,
            $first['year'],
            $first['week']
        );

        $secondstart = $first['starttime'] + HOURSECS;
        $secondend = $secondstart + (2 * HOURSECS);
        $result = save_slots::execute(
            [['starttime' => $secondstart, 'endtime' => $secondend]],
            $setup->course->id,
            $first['year'],
            $first['week']
        );
        $result = external_api::clean_returnvalue(save_slots::execute_returns(), $result);

        $this->assertTrue($result['result']);
        $slots = $DB->get_records('local_booking_slots', [
            'courseid' => $setup->course->id,
            'userid' => $setup->student->id,
        ]);
        $this->assertCount(1, $slots);
        $record = reset($slots);
        $this->assertEquals($secondstart, $record->starttime);
        $this->assertEquals($secondend, $record->endtime);
    }

    /**
     * An instructor cannot post availability as a student.
     */
    public function test_save_slots_denied_for_instructor(): void {
        global $COURSE;

        $setup = $this->create_subscribed_course();
        unset($COURSE->subscriber);

        $slot = $this->future_slot();
        $this->setUser($setup->teacher);
        $this->expectException(\moodle_exception::class);
        save_slots::execute(
            [['starttime' => $slot['starttime'], 'endtime' => $slot['endtime']]],
            $setup->course->id,
            $slot['year'],
            $slot['week']
        );
    }

    /**
     * An instructor can book a posted student slot.
     */
    public function test_save_booking_creates_session(): void {
        global $DB, $COURSE;

        $setup = $this->create_subscribed_course();
        $slot = $this->future_slot();
        $this->post_slots($setup, $slot);

        unset($COURSE->subscriber);
        $this->setUser($setup->teacher);
        $this->redirectMessages();
        $GLOBALS['USER']->ignoresesskey = true;

        $result = save_booking::execute(
            [
                'starttime' => $slot['starttime'],
                'endtime' => $slot['endtime'],
                'year' => $slot['year'],
                'week' => $slot['week'],
            ],
            $setup->course->id,
            $setup->exerciseid,
            $setup->student->id
        );
        $result = external_api::clean_returnvalue(save_booking::execute_returns(), $result);

        $this->assertTrue($result['result']);
        $booking = $DB->get_record('local_booking_sessions', [
            'courseid' => $setup->course->id,
            'studentid' => $setup->student->id,
            'exerciseid' => $setup->exerciseid,
        ], '*', MUST_EXIST);
        $this->assertEquals($setup->teacher->id, $booking->userid);
        $this->assertEquals(1, $booking->active);
        $this->assertEquals(0, $booking->noshow);
        $this->assertNotEquals(0, $booking->slotid);
    }

    /**
     * Booking APIs work on a copied Line Training course from this development site.
     */
    public function test_save_booking_on_copied_line_training_course(): void {
        global $DB, $COURSE;

        $setup = $this->restore_copied_course(33);
        $slot = $this->future_slot();
        $this->post_slots($setup, $slot);

        unset($COURSE->subscriber);
        $this->setUser($setup->teacher);
        $this->redirectMessages();
        $GLOBALS['USER']->ignoresesskey = true;

        $result = save_booking::execute(
            [
                'starttime' => $slot['starttime'],
                'endtime' => $slot['endtime'],
                'year' => $slot['year'],
                'week' => $slot['week'],
            ],
            $setup->course->id,
            $setup->exerciseid,
            $setup->student->id
        );
        $result = external_api::clean_returnvalue(save_booking::execute_returns(), $result);

        $this->assertTrue($result['result']);
        $this->assertTrue($DB->record_exists('local_booking_sessions', [
            'courseid' => $setup->course->id,
            'studentid' => $setup->student->id,
            'exerciseid' => $setup->exerciseid,
            'userid' => $setup->teacher->id,
            'active' => 1,
        ]));
    }

    /**
     * A student cannot create a booking.
     */
    public function test_save_booking_denied_for_student(): void {
        global $COURSE;

        $setup = $this->create_subscribed_course();
        $slot = $this->future_slot();
        unset($COURSE->subscriber);

        $this->setUser($setup->student);
        $this->expectException(\required_capability_exception::class);
        save_booking::execute(
            [
                'starttime' => $slot['starttime'],
                'endtime' => $slot['endtime'],
                'year' => $slot['year'],
                'week' => $slot['week'],
            ],
            $setup->course->id,
            $setup->exerciseid,
            $setup->student->id
        );
    }

    /**
     * Cancelling a booking removes the session.
     */
    public function test_cancel_booking_deletes_session(): void {
        global $DB, $COURSE;

        $setup = $this->create_subscribed_course();
        $bookingid = $this->create_booking($setup);
        unset($COURSE->subscriber);

        $this->setUser($setup->teacher);
        $this->redirectMessages();
        $GLOBALS['USER']->ignoresesskey = true;

        $result = cancel_booking::execute($bookingid, 'Weather', false);
        $this->resetDebugging();
        $result = external_api::clean_returnvalue(cancel_booking::execute_returns(), $result);

        $this->assertTrue($result['result']);
        $this->assertFalse($DB->record_exists('local_booking_sessions', ['id' => $bookingid]));
    }

    /**
     * Recording a no-show keeps the booking inactive and flagged.
     */
    public function test_cancel_booking_noshow_marks_inactive(): void {
        global $DB, $COURSE;

        $setup = $this->create_subscribed_course();
        $bookingid = $this->create_booking($setup);
        unset($COURSE->subscriber);

        $this->setUser($setup->teacher);
        $this->redirectMessages();
        $GLOBALS['USER']->ignoresesskey = true;

        $result = cancel_booking::execute($bookingid, '', true);
        $this->resetDebugging();
        $result = external_api::clean_returnvalue(cancel_booking::execute_returns(), $result);

        $this->assertTrue($result['result']);
        $booking = $DB->get_record('local_booking_sessions', ['id' => $bookingid], '*', MUST_EXIST);
        $this->assertEquals(0, $booking->active);
        $this->assertEquals(1, $booking->noshow);
    }

    /**
     * Creating a training logentry persists student and instructor entries.
     */
    public function test_save_logentry_creates_linked_entries(): void {
        global $DB, $COURSE;

        $setup = $this->create_subscribed_course();
        unset($COURSE->subscriber);

        $this->setUser($setup->teacher);
        $this->redirectMessages();
        $GLOBALS['USER']->ignoresesskey = true;

        $result = save_logentry::execute(
            $this->logentry_formargs($setup),
            $this->logentry_formdata($setup, ['remarks' => 'First dual session'])
        );
        $result = external_api::clean_returnvalue(save_logentry::execute_returns(), $result);

        $this->assertFalse($result['validationerror']);
        $this->assertNotEmpty($result['logentry']['id']);
        $this->assertEquals(2, $DB->count_records('local_booking_logbooks', ['courseid' => $setup->course->id]));
        $this->assertTrue($DB->record_exists('local_booking_logbooks', [
            'courseid' => $setup->course->id,
            'userid' => $setup->student->id,
        ]));
        $this->assertTrue($DB->record_exists('local_booking_logbooks', [
            'courseid' => $setup->course->id,
            'userid' => $setup->teacher->id,
        ]));
        $studententry = $DB->get_record('local_booking_logbooks', [
            'courseid' => $setup->course->id,
            'userid' => $setup->student->id,
        ], '*', MUST_EXIST);
        $this->assertStringContainsString('dual session', strtolower($studententry->remarks));
        $this->assertEquals('C172', $studententry->aircraft);
        $this->assertEquals('training', $studententry->flighttype);
    }

    /**
     * Editing a logentry updates the stored remarks.
     */
    public function test_save_logentry_updates_remarks(): void {
        global $DB, $COURSE;

        $setup = $this->create_subscribed_course();
        unset($COURSE->subscriber);

        $this->setUser($setup->teacher);
        $this->redirectMessages();
        $GLOBALS['USER']->ignoresesskey = true;

        $created = save_logentry::execute(
            $this->logentry_formargs($setup),
            $this->logentry_formdata($setup, ['remarks' => 'Original remarks'])
        );
        $created = external_api::clean_returnvalue(save_logentry::execute_returns(), $created);
        $logentryid = $created['logentry']['id'];

        unset($COURSE->subscriber);
        $updated = save_logentry::execute(
            $this->logentry_formargs($setup),
            $this->logentry_formdata($setup, [
                'id' => $logentryid,
                'remarks' => 'Corrected remarks',
            ])
        );
        $updated = external_api::clean_returnvalue(save_logentry::execute_returns(), $updated);

        $this->assertFalse($updated['validationerror']);
        $record = $DB->get_record('local_booking_logbooks', ['id' => $logentryid], '*', MUST_EXIST);
        $this->assertStringContainsString('remarks', strtolower($record->remarks));
    }

    /**
     * Invalid logentry form data is rejected.
     */
    public function test_save_logentry_validation_error(): void {
        global $COURSE;

        $setup = $this->create_subscribed_course();
        unset($COURSE->subscriber);

        $this->setUser($setup->teacher);
        $GLOBALS['USER']->ignoresesskey = true;
        $result = save_logentry::execute(
            $this->logentry_formargs($setup),
            $this->logentry_formdata($setup, ['aircraft' => ''])
        );
        $result = external_api::clean_returnvalue(save_logentry::execute_returns(), $result);

        $this->assertTrue($result['validationerror']);
    }

    /**
     * An instructor can toggle a checklist item and save a teacher comment.
     */
    public function test_update_checklist_item_toggles_and_comment(): void {
        global $CFG, $DB, $COURSE;

        if (!file_exists($CFG->dirroot . '/mod/checklist/lib.php')) {
            $this->markTestSkipped('mod_checklist is not installed.');
        }

        require_once($CFG->dirroot . '/mod/checklist/lib.php');

        $setup = $this->create_subscribed_course();
        $bookingid = $this->create_booking($setup);
        $itemid = $this->create_checklist_item($setup);

        unset($COURSE->subscriber);
        $this->setUser($setup->teacher);

        $result = \local_booking\external\update_checklist_item::execute(
            $setup->course->id,
            $setup->student->id,
            $bookingid,
            $itemid,
            CHECKLIST_TEACHERMARK_YES
        );
        $result = external_api::clean_returnvalue(
            \local_booking\external\update_checklist_item::execute_returns(),
            $result
        );

        $this->assertTrue($result['success']);
        $check = $DB->get_record('checklist_check', [
            'item' => $itemid,
            'userid' => $setup->student->id,
        ], '*', MUST_EXIST);
        $this->assertEquals(CHECKLIST_TEACHERMARK_YES, $check->teachermark);

        $commentresult = \local_booking\external\update_checklist_item::execute(
            $setup->course->id,
            $setup->student->id,
            $bookingid,
            $itemid,
            CHECKLIST_TEACHERMARK_YES,
            'Good brief',
            true
        );
        $commentresult = external_api::clean_returnvalue(
            \local_booking\external\update_checklist_item::execute_returns(),
            $commentresult
        );

        $this->assertTrue($commentresult['success']);
        $this->assertTrue($DB->record_exists('checklist_comment', [
            'itemid' => $itemid,
            'userid' => $setup->student->id,
        ]));
    }

    /**
     * Create a subscribed course with an instructor, student, and assignment.
     *
     * @return \stdClass
     */
    protected function create_subscribed_course(): \stdClass {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course([
            'fullname' => 'PPL Dual Training',
            'customfield_subscribed' => 1,
            'customfield_postingwait' => 0,
            'customfield_minslotperiod' => 2,
            'customfield_homeicao' => 'EGLL',
            'customfield_aircrafticao' => 'C172',
        ]);

        $this->ensure_course_customfield($course->id, 'subscribed', '1', 1);
        $this->ensure_course_customfield($course->id, 'aircrafticao', 'C172');
        $this->ensure_course_customfield($course->id, 'homeicao', 'EGLL');
        $this->ensure_course_customfield($course->id, 'postingwait', '0');
        $this->ensure_course_customfield($course->id, 'minslotperiod', '2');
        \cache::make('local_booking', 'subscribed')->delete($course->id);

        $this->ensure_booking_groups($course->id);

        // Modules must exist before student enrolment so next-exercise progress can be stored.
        $assign = $generator->create_module('assign', [
            'course' => $course->id,
            'name' => 'Exercise 1',
        ]);

        $users = $this->enrol_instructor_and_student($course);

        return (object) [
            'course' => $course,
            'teacher' => $users['teacher'],
            'student' => $users['student'],
            'assign' => $assign,
            'exerciseid' => $assign->cmid,
        ];
    }

    /**
     * Build a future two-hour slot in the current timezone.
     *
     * @return array
     */
    protected function future_slot(): array {
        $start = (new \DateTimeImmutable('next monday 08:00:00'))->getTimestamp();
        $end = $start + (2 * HOURSECS);

        return [
            'starttime' => $start,
            'endtime' => $end,
            'year' => (int) date('o', $start),
            'week' => (int) date('W', $start),
        ];
    }

    /**
     * Post availability slots as the student.
     *
     * @param \stdClass $setup
     * @param array $slot
     */
    protected function post_slots(\stdClass $setup, array $slot): void {
        global $COURSE;

        unset($COURSE->subscriber);
        $this->setUser($setup->student);
        save_slots::execute(
            [['starttime' => $slot['starttime'], 'endtime' => $slot['endtime']]],
            $setup->course->id,
            $slot['year'],
            $slot['week']
        );
    }

    /**
     * Create an active booking via the save_booking API.
     *
     * @param \stdClass $setup
     * @return int
     */
    protected function create_booking(\stdClass $setup): int {
        global $DB, $COURSE;

        $slot = $this->future_slot();
        $this->post_slots($setup, $slot);

        unset($COURSE->subscriber);
        $this->setUser($setup->teacher);
        $this->redirectMessages();
        $GLOBALS['USER']->ignoresesskey = true;

        $result = save_booking::execute(
            [
                'starttime' => $slot['starttime'],
                'endtime' => $slot['endtime'],
                'year' => $slot['year'],
                'week' => $slot['week'],
            ],
            $setup->course->id,
            $setup->exerciseid,
            $setup->student->id
        );
        $result = external_api::clean_returnvalue(save_booking::execute_returns(), $result);
        $this->assertTrue($result['result']);

        return (int) $DB->get_field('local_booking_sessions', 'id', [
            'courseid' => $setup->course->id,
            'studentid' => $setup->student->id,
            'exerciseid' => $setup->exerciseid,
            'active' => 1,
        ], MUST_EXIST);
    }

    /**
     * URI-encoded logentry form arguments.
     *
     * @param \stdClass $setup
     * @return string
     */
    protected function logentry_formargs(\stdClass $setup): string {
        return http_build_query([
            'courseid' => $setup->course->id,
            'exerciseid' => $setup->exerciseid,
            'userid' => $setup->student->id,
        ], '', '&');
    }

    /**
     * URI-encoded logentry form data for Dual training (no PIREP lookup).
     *
     * @param \stdClass $setup
     * @param array $overrides
     * @return string
     */
    protected function logentry_formdata(\stdClass $setup, array $overrides = []): string {
        $flightdate = time();
        $dt = (new \DateTimeImmutable('@' . $flightdate))->setTimezone(new \DateTimeZone('UTC'));

        $data = [
            'id' => 0,
            'linkedlogentryid' => 0,
            'linkedpirep' => 0,
            'userid' => $setup->student->id,
            'exerciseid' => $setup->exerciseid,
            'sessionid' => 0,
            'courseid' => $setup->course->id,
            'contextid' => \context_course::instance($setup->course->id)->id,
            'flighttypehidden' => 'training',
            'trainingtype' => 'Dual',
            'visible' => 1,
            'flighttype' => 'training',
            'p1id' => $setup->teacher->id,
            'p2id' => $setup->student->id,
            'groundtime' => '00:30',
            'flighttime' => '01:00',
            'flightrule' => 'vfr',
            'dualtime' => '01:00',
            'p1pirep' => 0,
            'p2pirep' => 0,
            'flightdate' => [
                'day' => (int) $dt->format('j'),
                'month' => (int) $dt->format('n'),
                'year' => (int) $dt->format('Y'),
                'hour' => (int) $dt->format('G'),
                'minute' => (int) $dt->format('i'),
            ],
            'depicao' => 'EGLL',
            'deptime' => '08:00',
            'arricao' => 'EGKK',
            'arrtime' => '09:00',
            'aircraft' => 'C172',
            'aircraftreg' => 'G-TEST',
            'enginetype' => 'SE',
            'route' => 'EGLL DCT EGKK',
            'landingsp1day' => 1,
            'landingsp1night' => 0,
            'landingsp2day' => 1,
            'landingsp2night' => 0,
            'callsign' => 'BAW123',
            'remarks' => 'Dual training session',
            'fstd' => 'MSFS',
        ];

        $data = array_merge($data, $overrides);
        unset($data['sesskey']);
        $data = \local_booking\local\logbook\form\create::mock_generate_submit_keys($data);

        return http_build_query($data, '', '&');
    }

    /**
     * Create a checklist activity with one item for the student.
     *
     * @param \stdClass $setup
     * @return int
     */
    protected function create_checklist_item(\stdClass $setup): int {
        global $DB;

        $existing = $DB->get_field_sql(
            "SELECT ci.id
               FROM {checklist_item} ci
               JOIN {checklist} c ON c.id = ci.checklist
               JOIN {course_modules} cm ON cm.instance = c.id
               JOIN {modules} m ON m.id = cm.module AND m.name = 'checklist'
              WHERE cm.course = ?
                AND ci.userid = 0
           ORDER BY ci.id ASC",
            [$setup->course->id]
        );
        if ($existing) {
            return (int) $existing;
        }

        $checklist = $this->getDataGenerator()->create_module('checklist', [
            'course' => $setup->course->id,
            'name' => 'Session checklist',
        ]);

        $item = [
            'checklist' => $checklist->id,
            'userid' => 0,
            'displaytext' => 'Preflight briefing',
            'position' => 1,
            'indent' => 0,
            'itemoptional' => 0,
            'duetime' => 0,
            'colour' => 'black',
            'moduleid' => 0,
            'hidden' => 0,
            'groupingid' => 0,
            'linkcourseid' => 0,
            'linkurl' => '',
            'openlinkinnewwindow' => 0,
        ];
        $columns = $DB->get_columns('checklist_item');
        $item = array_intersect_key($item, $columns);

        return (int) $DB->insert_record('checklist_item', (object) $item);
    }
}
