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

        // get course list
        $sitecourses = \get_courses();

        foreach ($sitecourses as $sitecourse) {
            if ($sitecourse->id != SITEID) {
                // check if the course is using Session Booking
                $course = new subscriber($sitecourse->id);

                if (!empty($course->subscribed)) {

                    mtrace('    Course: ' . $sitecourse->shortname . ' (id: ' . $sitecourse->id . ')');

                    // get on-hold, suspension, and instructor overdue restrictins
                    $onholddays = intval($course->onholdperiod);
                    $suspensiondays = intval($course->suspensionperiod);
                    $overdueperiod = intval($course->overdueperiod);

                    // get list of senior instructors for communication
                    $seniorinstructors = $course->get_senior_instructors();

                    // check if any of the restrictions are enabled
                    if ($onholddays > 0 || $suspensiondays > 0 || $overdueperiod > 0) {

                        // get list of active students and instructors
                        $students = $course->get_students('active', true);
                        $instructors = $course->get_instructors();

                        // note students
                        mtrace('    Students to evaluate: ' . count($students));

                        // PROCESS POSTING RESTRICTION
                        $this->process_student_notifications($course, $students);

                        // PROCESS ON-HOLD RESTRICTION
                        $this->process_onhold_restriction($course, $students, $seniorinstructors);

                        // PROCESS SUSPENSION RESTRICTION
                        $this->process_suspension_restriction($course, $students, $seniorinstructors);

                        // note instructors
                        mtrace('    Instructors to evaluate: ' . count($instructors));

                        // PROCESS SUSPENSION RESTRICTION
                        $this->process_instructor_notifications($course, $instructors, $seniorinstructors);

                    } else {
                        mtrace('        Restrictions disabled.');
                    }

                    // PROCESS NOSHOW REINSTATEMENT
                    $this->process_noshow_reinstatement($course, $seniorinstructors);
                }
            }
        }

        return true;
    }

    /**
     * Process overdue lesson completion and availability posting notification.
     *
     * @param subscriber $course    The subscribed course
     * @param array      $students  Array of all course students to be evaluated
     */
    private function process_student_notifications($course, $students) {

        // check if wait-period restriction is enabled
        $postingwait = intval($course->postingwait);
        mtrace('');
        mtrace('        #### POSTING WAIT RESTRICTION ' . ($postingwait > 0 ? 'ENABLED' : 'DISABLED') . ' ####');
        if ($postingwait > 0) {
            foreach ($students as $student) {

                $studentname = participant::get_fullname($student->get_id());
                mtrace('        ' . $studentname);

                // get last booked date, otherwise use last graded date instead
                $lastsessionts = $student->get_last_booking_date();
                if (empty($lastsessionts)) {
                    $lastgradeddate = $student->get_last_graded_date();
                    $lastsessionts = !empty($lastgradeddate) ? $lastgradeddate->getTimestamp() : ($student->get_enrol_date())->getTimestamp();
                }

                if (!empty($lastsessionts)) {
                    // get status of already being on-hold or student is in active standings
                    $alreayonhold = $student->is_onhold();
                    $isactive = $student->has_completed_lessons() && $student->get_total_posts() > 0;

                    // posting overdue date from last booked session
                    $lastsessiondate = new DateTime('@' . $lastsessionts);
                    $onholddate = new DateTime('@' . $lastsessionts);
                    $postingoverduedate = new DateTime('@' . $lastsessionts);
                    $today = getdate(time());

                    // add a week to the posting wait period as the overdue date
                    date_add($postingoverduedate, date_interval_create_from_date_string(($postingwait + LOCAL_BOOKING_OVERDUE_PERIOD) . ' days'));
                    date_add($onholddate, date_interval_create_from_date_string($course->onholdperiod . ' days'));

                    // POSTING OVERDUE WARNING NOTIFICATION
                    // notify student a week before being placed
                    mtrace('            posting overdue warning date: ' . $postingoverduedate->format('M d, Y'));
                    mtrace('            on-hold date: ' . $onholddate->format('M d, Y'));
                    $message = new notification($course);
                    if (getdate($postingoverduedate->getTimestamp())['yday'] == $today['yday'] && !$alreayonhold && !$isactive) {
                        mtrace('        Sending student inactivity warning (10 days inactive after posting wait period)');
                        $message->send_inactive_warning($student->get_id(), $lastsessiondate, $onholddate);
                    }
                } else {
                    mtrace('            last session: NONE ON RECORD!');
                }
            }
        }
    }

    /**
     * Process on-hold restriction.
     *
     * @param subscriber $course    The subscribed course
     * @param array      $students  Array of all course students to be evaluated
     * @param array      $seniorinstructors An array of senior instructors to notify
     */
    private function process_onhold_restriction($course, $students, $seniorinstructors) {

        // check if on-hold restriction is enabled
        $onholddays = intval($course->onholdperiod);
        mtrace('');
        mtrace('        #### ON-HOLD RESTRICTION ' . ($onholddays > 0 ? 'ENABLED' : 'DISABLED') . ' ####');
        if ($onholddays > 0) {
            foreach ($students as $student) {

                $studentname = participant::get_fullname($student->get_id());
                mtrace('        ' . $studentname);

                // get last booked date, otherwise use last graded date instead
                $lastsessionts = $student->get_last_booking_date();
                if (empty($lastsessionts)) {
                    $lastgradeddate = $student->get_last_graded_date();
                    $lastsessionts = !empty($lastgradeddate) ? $lastgradeddate->getTimestamp() : ($student->get_enrol_date())->getTimestamp();
                }

                if (!empty($lastsessionts)) {
                    // get status of already being on-hold, kept active, or student is in active standings
                    $alreayonhold = $student->is_onhold();
                    $keepactive =  $student->is_kept_active();
                    $isactive = $student->has_completed_lessons() && $student->get_total_posts() > 0;
                    $booked = !empty($student->get_active_booking());

                    // on-hold date from last booked session
                    $lastsessiondate = new DateTime('@' . $lastsessionts);
                    $onholddate = new DateTime('@' . $lastsessionts);
                    $suspenddate = new DateTime('@' . $lastsessionts);
                    date_add($onholddate, date_interval_create_from_date_string($course->onholdperiod . ' days'));
                    date_add($suspenddate, date_interval_create_from_date_string($course->suspensionperiod . ' days'));

                    // on-hold warning date: 7 days before on-hold date
                    $onholdwarningdate = new DateTime('@' . $lastsessiondate->getTimestamp());
                    $today = getdate(time());
                    date_add($onholdwarningdate, date_interval_create_from_date_string(($onholddays -  7) . ' days'));

                    // ON HOLD WARNING NOTIFICATION
                    // notify student a week before being placed
                    mtrace('            on-hold date: ' . $onholddate->format('M d, Y'));
                    mtrace('            on-hold warning date: ' . $onholdwarningdate->format('M d, Y'));
                    mtrace('            keep active status: ' . ($keepactive ? 'ON' : 'OFF'));
                    $message = new notification($course);
                    if (getdate($onholdwarningdate->getTimestamp())['yday'] == $today['yday'] && !$alreayonhold && !$keepactive && !$isactive && !$booked) {
                        mtrace('        Notifying student of becoming on-hold in a week');
                        $message->send_onhold_warning($student->get_id(), $onholddate);
                    }

                    // ON-HOLD PLACEMENT NOTIFICATION
                    // place student on-hold and send notification
                    if ($onholddate->getTimestamp() <= time() && !$alreayonhold && !$keepactive && !$isactive && !$booked) {

                        // add student to on-hold group
                        $onholdgroupid = groups_get_group_by_name($course->get_id(), LOCAL_BOOKING_ONHOLDGROUP);
                        groups_add_member($onholdgroupid, $student->get_id());

                        // send notification of upcoming placement on-hold to student and senior instructor roles
                        if ($message->send_onhold_notification($student->get_id(), $lastsessiondate, $suspenddate, $seniorinstructors)) {
                            mtrace('                Placed \'' . $studentname . '\' on-hold (notified)...');
                        }
                    }
                } else {
                    mtrace('            last session: NONE ON RECORD!');
                }
            }
        }
    }

    /**
     * Process suspension restriction.
     *
     * @param subscriber $course    The subscribed course
     * @param array      $students  Array of all course students to be evaluated
     * @param array      $seniorinstructors An array of senior instructors to notify
     */
    private function process_suspension_restriction($course, $students, $seniorinstructors) {

        // check for suspension restriction is enabled
        $suspensiondays = intval($course->suspensionperiod);
        mtrace('');
        mtrace('        #### SUSPENSION RESTRICTION ' . ($suspensiondays > 0 ? 'ENABLED' : 'DISABLED') . ' ####');
        if ($suspensiondays > 0) {
            foreach ($students as $student) {
                $studentname = participant::get_fullname($student->get_id());
                mtrace('        ' . $studentname);

                // get suspension date, otherwise use last graded date instead
                $lastsessionts = $student->get_last_booking_date();
                if (empty($lastsessionts)) {
                    $lastgradeddate = $student->get_last_graded_date();
                    $lastsessionts = !empty($lastgradeddate) ? $lastgradeddate->getTimestamp() : ($student->get_enrol_date())->getTimestamp();
                }

                if (!empty($lastsessionts)) {
                    // Suspension (unenrolment) date is 9x wait period from last session
                    $lastsessiondate = new DateTime('@' . $lastsessionts);
                    $suspenddate = new DateTime('@' . $lastsessionts);
                    $keepactive =  $student->is_kept_active();

                    date_add($suspenddate, date_interval_create_from_date_string($suspensiondays . ' days'));

                    // SUSPENSION NOTIFICATION
                    // suspend when passed on-hold by 9x wait days process suspension and notify student and senior instructor roles
                    mtrace('            suspension date: ' . $suspenddate->format('M d, Y'));
                    $message = new notification($course);
                    if ($suspenddate->getTimestamp() <= time() && !$keepactive) {
                        // unenrol the student from the course
                        if ($student->suspend()) {
                            mtrace('                Suspended!');
                            // send notification of unenrolment from the course and senior instructor roles
                            if ($message->send_suspension_notification($student->get_id(), $lastsessiondate, $seniorinstructors)) {
                                mtrace('                Student notified of suspension');
                            }
                        }
                    }
                } else {
                    mtrace('            last session: NONE ON RECORD!');
                }
            }
        }
    }

    /**
     * Process instructor inactivity notifications.
     *
     * @param subscriber $course      The subscribed course
     * @param instructor $instructor  The instructor to be evaluated
     * @param array      $seniorinstructors An array of senior instructors to notify
     */
    private function process_instructor_notifications($course, $instructors, $seniorinstructors) {

        // check for suspension restriction is enabled
        $overdueperiod = intval($course->overdueperiod);
        mtrace('');
        mtrace('        #### INSTRUCTOR OVERDUE NOTIFICATION ' . ($overdueperiod > 0 ? 'ENABLED' : 'DISABLED') . ' ####');
        if ($overdueperiod > 0) {
            foreach ($instructors as $instructor) {
                $instructorname = participant::get_fullname($instructor->get_id());
                mtrace('        ' . $instructorname);

                // get instructor last booked session, otherwise use the last login for date compare
                $lastsessiondate = $instructor->get_last_booked_date();
                if (!empty($lastsessiondate)) {
                    // get days since last session
                    $interval = $lastsessiondate->diff(new DateTime('@' . time()));
                    $dayssincelast = $interval->format('%d');

                    // check if overdue period had past without a grading and send a notification each time this interval passes
                    $sendnotification = ($dayssincelast % $overdueperiod) == 0 && $dayssincelast >= $overdueperiod;
                    $status = get_string('emailoverduestatus', 'local_booking', $lastsessiondate->format('M d, Y'));
                    mtrace('            last session: ' . $lastsessiondate->format('M d, Y'));

                    // notify the instructors of overdue status
                    if ($sendnotification) {
                        mtrace('                inactivity notification sent (retry=' . round($dayssincelast / $overdueperiod) . ')...');
                        $message = new notification($course);
                        $message->send_session_overdue_notification($instructor->get_id(), $status, $seniorinstructors);
                    }
                }
                else {
                    mtrace('            last session: NONE ON RECORD!');
                }
            }
        } else {
            mtrace('            instructor overdue notifications disabled.');
        }
    }

    /**
     * Process suspended students with 2 no-shows that completed their suspension period,
     * then reinstate and notify them.
     *
     * @param subscriber $course    The subscribed course
     * @param array      $seniorinstructors An array of senior instructors to notify
     */
    private function process_noshow_reinstatement($course, $seniorinstructors) {

        mtrace('');
        mtrace('        #### SUSPENDED NO-SHOW STUDENTS REINSTATEMENT ####');

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
                    mtrace('                no-show student reinstated');
                    $message = new notification($course);
                    $message->send_noshow_reinstatement_notification($student, $exerciseid, $seniorinstructors);
                }
            }
        }
    }
}
