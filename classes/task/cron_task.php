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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
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
use local_booking\local\slot\entities\slot;
use local_booking\local\message\notification;
use local_booking\local\subscriber\entities\subscriber;

/**
 * A schedule task for student and instructor status cron.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
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
        return get_string('crontask', 'local_booking');
    }

    /**
     * Run session booking cron.
     */
    public function execute() {

        // get course list
        $sitecourses = get_courses();

        foreach ($sitecourses as $sitecourse) {
            if ($sitecourse->id != SITEID) {
                // check if the course is using Session Booking
                $course = new subscriber($sitecourse->id);
                if (!empty($course->subscribed) && $course->subscribed) {
                    mtrace('');
                    mtrace('    Course id: ' . $sitecourse->id);
                    $message = new notification();

                    // get wait days
                    $waitdays = get_config('local_booking', 'nextsessionwaitdays') ?
                        get_config('local_booking', 'nextsessionwaitdays') :
                        LOCAL_BOOKING_DAYSFROMLASTSESSION;

                    // get active students
                    $students = $course->get_active_students(true);
                    $seniorinstructors = $course->get_senior_instructors();

                    // consider on-hold and suspension candidates
                    mtrace('    Students to evaluate: ' . count($students));
                    foreach ($students as $student) {
                        $studentname = participant::get_fullname($student->get_id());
                        $alreayonhold = $student->is_member_of(LOCAL_BOOKING_ONHOLDGROUP);
                        $keepactive =  $student->is_member_of(LOCAL_BOOKING_KEEPACTIVE);
                        mtrace('        ' . $studentname);

                        // get on-hold date, otherwise use last graded date for on-hold comparison
                        $lastsessionts = slot::get_last_booking($sitecourse->id, $student->get_id());
                        if (empty($lastsessionts)) {
                            $lastgradeddate = $student->get_last_graded_date();
                            $lastsessionts = !empty($lastgradeddate) ? $lastgradeddate->getTimestamp() : ($student->get_enrol_date())->getTimestamp();
                        }

                        if (!empty($lastsessionts)) {
                            $lastsessiondate = new DateTime('@' . $lastsessionts);
                            $onholddate = new DateTime('@' . $lastsessiondate->getTimestamp());
                            // on-hold date is 3x wait period from last session
                            date_add($onholddate, date_interval_create_from_date_string(($waitdays * LOCAL_BOOKING_ONHOLDWAITMULTIPLIER) . ' days'));

                            // on-hold warning date: 7 days before on-hold date
                            $onholdwarningdate = new DateTime('@' . $lastsessiondate->getTimestamp());
                            $today = getdate(time());
                            date_add($onholdwarningdate, date_interval_create_from_date_string((($waitdays * LOCAL_BOOKING_ONHOLDWAITMULTIPLIER) -  7) . ' days'));

                            // Suspension (unenrolment) date is 9x wait period from last session
                            $suspenddate = new DateTime('@' . $onholddate->getTimestamp());
                            date_add($suspenddate, date_interval_create_from_date_string(($waitdays * LOCAL_BOOKING_SUSPENDWAITMULTIPLIER) . ' days'));

                            // ON HOLD WARNING NOTIFICATION
                            // notify student a week before being placed
                            mtrace('            on-hold date: ' . $onholddate->format('M d, Y'));
                            mtrace('            on-hold warning date: ' . $onholdwarningdate->format('M d, Y'));
                            mtrace('            keep active status: ' . $keepactive);
                            if (getdate($onholdwarningdate->getTimestamp())['yday'] == $today['yday'] && !$alreayonhold && !$keepactive) {
                                mtrace('                Notifying student becoming on-hold in a week...');
                                $message->send_onhold_warning($student->get_id(), $onholddate, $sitecourse->id, $sitecourse->shortname);
                            }

                            // ON-HOLD PLACEMENT NOTIFICATION
                            // place student on-hold and send notification
                            if ($onholddate->getTimestamp() <= time() && !$alreayonhold && !$keepactive) {

                                // add student to on-hold group
                                $onholdgroupid = groups_get_group_by_name($sitecourse->id, LOCAL_BOOKING_ONHOLDGROUP);
                                groups_add_member($onholdgroupid, $student->get_id());

                                // send notification of upcoming placement on-hold to student and senior instructor roles
                                if ($message->send_onhold_notification($student->get_id(), $lastsessiondate, $suspenddate, $sitecourse->shortname, $seniorinstructors)) {
                                    mtrace('                Placed \'' . $studentname . '\' on-hold (notified)...');
                                }
                            }

                            // SUSPENSION NOTIFICATION
                            // suspend when passed on-hold by 9x wait days process suspension and notify student and senior instructor roles
                            mtrace('            suspension date: ' . $suspenddate->format('M d, Y'));
                            if ($suspenddate->getTimestamp() <= time()) {
                                // unenrol the student from the course
                                $participant = new participant($sitecourse->id, $student->get_id());
                                if ($participant->set_suspend_status()) {
                                    mtrace('                Suspended!');
                                    // send notification of unenrolment from the course and senior instructor roles
                                    if ($message->send_suspension_notification($student->get_id(), $lastsessiondate, $sitecourse->shortname, $seniorinstructors)) {
                                        mtrace('                Student notified of suspension');
                                    }
                                }
                            }
                        }
                        else {
                            mtrace('            last session: NONE ON RECORD!');
                        }
                    }

                    // get instructors
                    $instructors = $course->get_active_instructors();
                    $sendnotification = false;
                    mtrace('    Instructors: ' . count($instructors));
                    // consider inactive instructors
                    foreach ($instructors as $instructor) {
                        $instructorname = participant::get_fullname($instructor->get_id());
                        mtrace('        ' . $instructorname);

                        // get instructor last booked session, otherwise use the last login for date compare
                        $lastsessiondate = $instructor->get_last_graded_date();
                        if (!empty($lastsessiondate)) {
                            // get days since last session
                            $interval = $lastsessiondate->diff(new DateTime('@' . time()));
                            $dayssincelast = $interval->format('%d');

                            // check if 3x waitdays has past without a booking and send a notification each time this interval passes
                            $sendnotification = ($dayssincelast % ($waitdays * LOCAL_BOOKING_INSTRUCTORINACTIVEMULTIPLIER)) == 0 &&
                                $dayssincelast >= ($waitdays * LOCAL_BOOKING_INSTRUCTORINACTIVEMULTIPLIER);
                            $status = get_string('emailoverduestatus', 'local_booking', $lastsessiondate->format('M d, Y'));
                            mtrace('            last session: ' . $lastsessiondate->format('M d, Y'));

                            // notify the instructors of overdue status
                            if ($sendnotification) {
                                mtrace('                inactivity notification sent (retry=' . round($dayssincelast / $waitdays) . ')...');
                                $message->send_session_overdue_notification($instructor->get_id(), $status, $sitecourse->id, $sitecourse->shortname, $seniorinstructors);
                            }
                        }
                        else {
                            mtrace('            last session: NONE ON RECORD!');
                        }
                    }
                }
            }
        }

        return true;
    }
}
