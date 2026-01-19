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
 * Session Booking Plugin cron task
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use DateTime;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\instructor;
use local_booking\local\message\notification;
use local_booking\local\subscriber\entities\subscriber;

/**
 * A schedule task for student and instructor status cron job.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {

    protected bool $debugmode;

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskcron', 'local_booking');
    }

    /**
     * Run session booking cron.
     */
    public function execute() {
        global $CFG;

        // set debug mode
        $this->debugmode = $CFG->debug == 32767;

        // get course list
        $sitecourses = \get_courses();

        foreach ($sitecourses as $sitecourse) {
            if ($sitecourse->id != SITEID) {
                // check if the course is using Session Booking
                $course = new subscriber($sitecourse->id);

                if (!empty($course->subscribed)) {

                    // get on-hold, suspension, and instructor overdue restrictions
                    $onholddays = $course->get_on_hold_days_restriction();
                    $suspensiondays = $course->get_suspend_days_restriction();
                    $overdueperiod = $course->get_instructor_commitment_days();

                    $restrictionsenabled = $onholddays > 0 || $suspensiondays > 0 || $overdueperiod > 0;
                    mtrace("    Course: $sitecourse->shortname (id=$sitecourse->id) -- RESTRICTIONS " . ($restrictionsenabled ? 'ENABLED' : 'DISABLED') . ".");

                    // get list of senior instructors for communication
                    $seniorinstructors = $course->get_senior_instructors();

                    // check for student's session overdue restriction if enabled
                    if ($onholddays > 0 || $suspensiondays > 0) {

                        // get list of active students and instructors
                        $students = $course->get_students('active', true);
                        $instructors = $course->get_instructors();

                        // log trace info for students
                        mtrace('');
                        mtrace(str_repeat(" ", 8) . '## Students to evaluate: ' . count($students));

                        // PROCESS POSTING RESTRICTION
                        $this->process_student_inactivity_notifications($course, $students, $course->get_student_posting_wait_days_restriction());

                        // PROCESS ON-HOLD RESTRICTION
                        $this->process_onhold_restriction($course, $students, $seniorinstructors, $onholddays);

                        // PROCESS SUSPENSION RESTRICTION
                        $this->process_suspension_restriction($course, $students, $seniorinstructors, $suspensiondays);

                    }

                    // PROCESS NOSHOW REINSTATEMENT
                    $this->process_noshow_reinstatement($course, $seniorinstructors);

                    // PROCESS INSTRUCTOR COMMITMENT NOTIFICATIONS
                    if ($overdueperiod > 0) {
                        mtrace(str_repeat(" ", 8) . '## Instructors to evaluate: ' . count($instructors));
                        $this->process_instructor_notifications($course, $instructors, $seniorinstructors, $overdueperiod);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Process student notifications for overdue lesson completion and availability posting.
     *
     * @param subscriber $course The subscribed course
     * @param array $students array of all course students to be evaluated
     */
    private function process_student_inactivity_notifications(subscriber $course, array $students, int $postingwait) {

        // trace on debug mode
        $this->trace(str_repeat(" ", 8) . '#### INACTIVITY RESTRICTION ' . ($postingwait > 0 ? 'ENABLED' : 'DISABLED') . ' ####');

        if ($postingwait > 0) {
            foreach ($students as $student) {

                // get last booked date, otherwise use last graded date instead
                if (!$student->is_onhold()) {

                    $studentname = participant::get_fullname($student->get_id());
                    $this->trace(str_repeat(" ", 8) .  $studentname);

                    // get status of already being on-hold or student is in active standings
                    $isactive = ($student->has_completed_lessons() || $student->is_newly_joined()) && $student->get_statistics()->get_active_posts_count() > 0;
                    $booked = !empty($student->get_active_booking());

                    // get last activity date, otherwise use last slot date instead
                    $lastactivitydate = max($student->get_last_activity_date(), $student->get_last_slot_date());
                    $lastsessiondate = $student->get_last_session_date();
                    $postingoverduedate = empty($lastsessiondate) ? clone $lastactivitydate : clone $lastsessiondate;
                    $onholddate = clone $lastactivitydate;
                    $today = getdate(time());

                    // add a week to the posting wait period as the overdue date, and on-hold date
                    date_add($postingoverduedate, date_interval_create_from_date_string(($postingwait + LOCAL_BOOKING_OVERDUE_PERIOD) . ' days'));
                    date_add($onholddate, date_interval_create_from_date_string($course->get_on_hold_days_restriction() . ' days'));

                    // POSTING OVERDUE WARNING NOTIFICATION
                    // notify student a week before being placed
                    $this->trace(str_repeat(" ", 12) . 'posting overdue warning date: ' . $postingoverduedate->format('M d, Y'));
                    $this->trace(str_repeat(" ", 12) . 'on-hold date: ' . $onholddate->format('M d, Y'));
                    $message = new notification($course);
                    if (getdate($postingoverduedate->getTimestamp())['yday'] == $today['yday'] && !$isactive && !$booked) {
                        $message->send_inactive_warning($student->get_id(), $lastsessiondate, $onholddate);
                        mtrace(str_repeat(" ", 8) . 'Sending student inactivity warning (10 days inactive after posting wait period)');
                    }
                }
            }
        }
        $this->trace('');
    }

    /**
     * Process on-hold restriction.
     *
     * @param subscriber $course    The subscribed course
     * @param array $students  Array of all course students to be evaluated
     * @param array $seniorinstructors An array of senior instructors to notify
     * @param int $onholddays The number of days for the on-hold restriction
     */
    private function process_onhold_restriction(subscriber $course, array $students, array $seniorinstructors, int $onholddays) {

        $this->trace(str_repeat(" ", 8) . '#### ON-HOLD RESTRICTION ' . ($onholddays > 0 ? 'ENABLED' : 'DISABLED') . ' ####');

        if ($onholddays > 0) {
            foreach ($students as $student) {

                if (!$student->is_onhold()) {

                    $studentname = participant::get_fullname($student->get_id());
                    $this->trace(str_repeat(" ", 8) . $studentname);

                    // get last activity date, otherwise use last slot date instead
                    $lastactivitydate = max($student->get_last_activity_date(), $student->get_last_slot_date());

                    // get status of already being on-hold, kept active, or student is in active standings
                    $keptactive =  $student->is_kept_active();
                    $hasvalidposts = $student->get_statistics()->get_valid_posts_count() > 0;
                    $hasactiveposts = $student->get_statistics()->get_active_posts_count() > 0;
                    $booked = !empty($student->get_active_booking());

                    // on-hold date from last booked session
                    $today = getdate(time());
                    $onholdwarningdate = clone $lastactivitydate;
                    $onholddate = clone $lastactivitydate;
                    $suspenddate = clone $lastactivitydate;
                    date_add($onholdwarningdate, date_interval_create_from_date_string(($onholddays -  7) . ' days'));
                    date_add($onholddate, date_interval_create_from_date_string($course->get_on_hold_days_restriction() . ' days'));
                    date_add($suspenddate, date_interval_create_from_date_string($course->get_suspend_days_restriction() . ' days'));
                    // get the greater of either last session date or last slot date

                    // ON HOLD WARNING NOTIFICATION
                    // log trace info
                    $this->trace(str_repeat(" ", 12) . 'on-hold date: ' . $onholddate->format('M d, Y'));
                    $this->trace(str_repeat(" ", 12) . 'on-hold warning date: ' . $onholdwarningdate->format('M d, Y'));
                    $this->trace(str_repeat(" ", 12) . 'keep active status: ' . ($keptactive ? 'ON' : 'OFF'));
                    // notify student a week before being placed
                    $message = new notification($course);
                    if (getdate($onholdwarningdate->getTimestamp())['yday'] == $today['yday'] && !$keptactive && !$hasactiveposts && !$booked) {
                        $message->send_onhold_warning($student->get_id(), $onholddate);
                        mtrace(str_repeat(" ", 16) . 'Notifying student of becoming on-hold in a week');
                    }

                    // ON-HOLD PLACEMENT NOTIFICATION
                    // place student on-hold and send notification
                    if ($onholddate->getTimestamp() <= time() && !$keptactive && !$hasvalidposts && !$booked) {

                        // add student to on-hold group
                        $onholdgroupid = groups_get_group_by_name($course->get_id(), LOCAL_BOOKING_ONHOLDGROUP);
                        groups_add_member($onholdgroupid, $student->get_id());

                        // send notification of upcoming placement on-hold to student and senior instructor roles
                        if ($message->send_onhold_notification($student->get_id(), $lastactivitydate, $suspenddate, $seniorinstructors)) {
                            $this->trace(str_repeat(" ", 16) . 'Placed \'' . $studentname . '\' on-hold (notified)...');
                        }
                    }
                }
            }
        }
        $this->trace('');
    }

    /**
     * Process suspension restriction.
     *
     * @param subscriber $course    The subscribed course
     * @param array $students  Array of all course students to be evaluated
     * @param array $seniorinstructors An array of senior instructors to notify
     * @param int $suspensiondays The number of days for the suspension restriction
     */
    private function process_suspension_restriction($course, $students, $seniorinstructors, $suspensiondays) {

        // check for suspension restriction is enabled
        $this->trace(str_repeat(" ", 8) . '#### SUSPENSION RESTRICTION ' . ($suspensiondays > 0 ? 'ENABLED' : 'DISABLED') . ' ####');

        if ($suspensiondays > 0) {
            foreach ($students as $student) {

                if ($student->is_onhold()) {

                    // get suspension date, otherwise use last graded date instead
                    $studentname = participant::get_fullname($student->get_id());

                    // Suspension (unenrolment) date as per the last activity date and days of inactivity to suspension.
                    $lastactivitydate = max($student->get_last_activity_date(), $student->get_last_slot_date());
                    $suspenddate = clone $lastactivitydate;

                    date_add($suspenddate, date_interval_create_from_date_string($suspensiondays . ' days'));

                    // SUSPENSION NOTIFICATION
                    // suspend when passed on-hold by 9x wait days process suspension and notify student and senior instructor roles
                    if ($suspenddate->getTimestamp() <= time() && !$student->is_kept_active()) {

                        $studentname = participant::get_fullname($student->get_id());
                        $this->trace(str_repeat(" ", 8) . $studentname);
                        $this->trace(str_repeat(" ", 12) . 'suspension date: ' . $suspenddate->format('M d, Y'));

                        // suspend the student from the course
                        if ($student->suspend()) {
                            $this->trace(str_repeat(" ", 16) . 'Suspended!');
                            // send notification of unenrolment from the course and senior instructor roles
                            $message = new notification($course);
                            if ($message->send_suspension_notification($student->get_id(), $lastactivitydate, $seniorinstructors)) {
                                mtrace(str_repeat(" ", 16) . 'Student notified of suspension');
                            }
                        }
                    }
                }
            }
        }
        $this->trace('');
    }

    /**
     * Process instructor inactivity notifications.
     *
     * @param subscriber $course      The subscribed course
     * @param instructor $instructor  The instructor to be evaluated
     * @param array $seniorinstructors An array of senior instructors to notify
     * @param int $overdueperiod The number of days for the instructor overdue notification
     */
    private function process_instructor_notifications($course, $instructors, $seniorinstructors, $overdueperiod) {

        // check for suspension restriction is enabled
        $this->trace(str_repeat(" ", 8) . '#### INSTRUCTOR OVERDUE NOTIFICATION ' . ($overdueperiod > 0 ? 'ENABLED' : 'DISABLED') . ' ####');
        if ($overdueperiod > 0) {
            foreach ($instructors as $instructor) {

                $instructorname = participant::get_fullname($instructor->get_id());
                $this->trace(str_repeat(" ", 12) . $instructorname);

                // get instructor last booked session, otherwise use the last login for date compare
                $lastsessiondate = $instructor->get_last_booked_date();
                if (!empty($lastsessiondate)) {
                    // get days since last session
                    $interval = $lastsessiondate->diff(new DateTime('@' . time()));
                    $dayssincelast = $interval->format('%d');

                    // check if overdue period had past without a grading and send a notification each time this interval passes
                    $sendnotification = ($dayssincelast % $overdueperiod) == 0 && $dayssincelast >= $overdueperiod;
                    $status = get_string('emailoverduestatus', 'local_booking', $lastsessiondate->format('M d, Y'));
                    $this->trace(str_repeat(" ", 12) . 'last session: ' . $lastsessiondate->format('M d, Y'));

                    // notify the instructors of overdue status
                    if ($sendnotification) {
                        $message = new notification($course);
                        $message->send_session_overdue_notification($instructor->get_id(), $status, $seniorinstructors);
                        mtrace(str_repeat(" ", 16) . 'inactivity notification sent (retry=' . round($dayssincelast / $overdueperiod) . ')...');
                    }
                }
                else {
                    $this->trace(str_repeat(" ", 12) . 'last session: NONE ON RECORD!');
                }
            }
        } else {
            $this->trace(str_repeat(" ", 12) . 'instructor overdue notifications disabled.');
        }
        mtrace('');
    }

    /**
     * Process suspended students with 2 no-shows that completed their suspension period,
     * then reinstate and notify them.
     *
     * @param subscriber $course    The subscribed course
     * @param array      $seniorinstructors An array of senior instructors to notify
     */
    private function process_noshow_reinstatement($course, $seniorinstructors) {

        $this->trace(str_repeat(" ", 8) . '#### NO-SHOW STUDENTS SUSPENDED REINSTATEMENT ####');

        // evaluate suspended students with 2 no-shows that completed their suspension period
        $students = $course->get_students('suspended');
        foreach ($students as $student) {

            // check the student has 2 no-shows
            $noshows = $student->get_noshow_bookings();
            if (count($noshows) == 2) {

                // the suspended until date timestamp: suspended date + no-show suspension period
                $suspenduntildate = strtotime(LOCAL_BOOKING_NOSHOWSUSPENSIONPERIOD . ' day', array_values($noshows)[0]->starttime);

                // reinstate after suspension priod had passed
                if ($suspenduntildate <= time()) {

                    // reinstate the student
                    $student->suspend(false);
                    $exerciseid = array_values($noshows)[0]->exerciseid;

                    // notify the student and senior instructors of reinstatement
                    $message = new notification($course);
                    $message->send_noshow_reinstatement_notification($student, $exerciseid, $seniorinstructors);
                    mtrace(str_repeat(" ", 16) . 'no-show student reinstated');
                }
            }
        }
        $this->trace('');
    }

    /**
     * Trace message based on debug mode.
     *
     * @param string $tracemessage The message to post to the log
     */
    private function trace($tracemessage) {
        if ($this->debugmode) {
            mtrace($tracemessage);
        }
    }
}
