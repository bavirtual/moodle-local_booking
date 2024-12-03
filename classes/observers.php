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
 * Grading observers.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @category   event
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');

/**
 * Group observers class to listen to graded assignments
 * for clearing previously posted student availability,
 * and user enrolments to add the user stats for
 * subscribed courses.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @category   event handler
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;
use core\event\course_module_completion_updated;
use core\event\course_updated;
use core\session\exception;
use mod_assign\event\submission_graded;
use mod_lesson\event\lesson_ended;
use grade_grade;

class observers {

    /**
     * A course settings update.
     *
     * @param \core\event\course_updated $event The event.
     * @return void
     */
    public static function course_updated(course_updated $event) {

        // check if the user is enroled to a subscribing course
        if (subscriber::is_subscribed($event->courseid) && !subscriber::stats_exist($event->courseid)) {
            // check if the course update
            subscriber::add_new_enrolments($event->courseid);
        }
    }

    /**
     * A completion of a lesson.
     *
     * @param \mod\lesson\lesson_ended $event The event.
     * @return void
     */
    public static function lesson_ended(lesson_ended $event) {

        // check if the user is enroled to a subscribing course
        if (subscriber::is_subscribed($event->courseid)) {

            // update stats with the student's last lesson completion
            $data = $event->get_data();

            // check if the user being enrolled is a student and update statistics if so
            $participant = new participant($event->courseid, $data['userid']);

            if ($participant->is_student()) {
                // add stats record for the newly graded student
                $student = new student($event->courseid, $data['userid']);
                $student->update_lessonscomplete_stat();
            }
        }
    }

    /**
     * A completion of a lesson.
     *
     * @param \core\event\course_module_completion_updated $event The event.
     * @return void
     */
    public static function course_module_completion_updated(course_module_completion_updated $event) {
        // TODO: update stats with subscribed course module completion
    }

    /**
     * A submission has been graded.
     *
     * @param \mod\assign\submission_graded $event The event.
     * @return void
     */
    public static function submission_graded(submission_graded $event) {

        // check if the user is enroled to a subscribing course
        if (subscriber::is_subscribed($event->courseid)) {

            $courseid = $event->courseid;
            $studentid = $event->relateduserid;
            $exerciseid = $event->contextinstanceid;

            // Respond to submission graded events by deactivating the active booking.
            $booking = new booking(0, $courseid, $studentid, $exerciseid);
            $booking->load();

            // update the booking status from active to inactive
            if ($booking->active())
                $booking->deactivate();

            // revoke 'Keep Active' status
            $groupid = groups_get_group_by_name($courseid, LOCAL_BOOKING_KEEPACTIVEGROUP);
            if (groups_is_member($groupid, $studentid)) {
                groups_remove_member($groupid, $studentid);
            }

            // update stats based on passing
            $assign = $event->get_assign();
            $gradeitem = $assign->get_grade_item();
            if ($gradeitem) {
                $gradegrade = grade_grade::fetch(array('userid' => $studentid, 'itemid' => $gradeitem->id));

                // update current & next exercise stats,
                // update last completed session date (confirming the session had took place)
                // reset required lesson completed if the grade is passed
                if (!empty($gradegrade)) {
                    $student = new student($event->courseid, $studentid);
                    if ($gradegrade->is_passed()) {

                        // get last session
                        $nextexerciseid = $student->get_next_exercise()->id;
                        $lastsessiondate = $booking->get_last_session_date($courseid, $studentid);
                        $lastsessiondatets = !empty($lastsessiondate) ? $lastsessiondate->getTimestamp() : 0;

                        // add stats record for the newly graded student
                        $student->update_statistic('currentexerciseid', $exerciseid);
                        $student->update_statistic('nextexerciseid', $nextexerciseid);
                        $student->update_statistic('lastsessiondate', $lastsessiondatets);
                        $student->update_lessonscomplete_stat($courseid, $studentid);

                    } else {
                        $student->update_statistic('currentexerciseid', $exerciseid);
                    }
                } else {
                    throw new exception(get_string('errorgradenotfound', 'local_booking'));
                }
            } else {
                throw new exception(get_string('errorgradeitemnotfound', 'local_booking'));
            }
        }
    }
}
