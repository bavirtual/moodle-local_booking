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

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use local_booking\local\logbook\entities\logbook;
use local_booking\local\message\notification;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;
use local_booking\local\subscriber\entities\subscriber;

/**
 * A schedule task to send notifications to instructors
 * of student availability postings and recommendations
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notifications_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('tasknotifications', 'local_booking');
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

                    mtrace('    Notifications for course: ' . $sitecourse->shortname . ' (id: ' . $sitecourse->id . ')');

                    // get active students
                    $students = array_merge($course->get_students('active', true), $course->get_students('graduated'));

                    // process notifications for students
                    foreach ($students as $student) {

                        // notify instructors of student recommendation
                        $this->process_recommendations($student, $course);

                        // notify instructors of student availability posting
                        $this->process_availability_postings($student, $course);

                        // notify students and instructors of student graduating
                        $this->process_graduations($student, $course);

                    }
                }
            }
        }

        return true;
    }

    /**
     * Process recommendation notifications.
     *
     * @param student    $student   The student being checked
     * @param subscriber $course    The subscribing course.
     */
    protected function process_recommendations(student $student, subscriber $course) {
        // check if the student has recommendation notification pending in their preferences settings
        $notify = $student->get_progress_flag(LOCAL_BOOKING_PROGFLAGS['NOTIFYENDORSE']);

        if ($notify) {

            // get endorser
            $endorsement = $student->get_progress_flag(LOCAL_BOOKING_PROGFLAGS['ENDORSE']);

            // get message data
            $data = array(
                'coursename'    => $course->get_shortname(),
                'studentname'   => $student->get_name(),
                'firstname'     => $student->get_profile_field('firstname', true),
                'skilltest'     => $course->get_graduation_exercise_id(true),
                'instructorname'=> instructor::get_fullname($endorsement->endorserid),
                'recommendltrurl'=> (new \moodle_url('/local/booking/report.php', array('courseid'=>$course->get_id(), 'userid'=>$student->get_id(), 'report'=>'recommendation')))->out(false),
                'bookingurl'    => (new \moodle_url('/local/booking/view.php', array('courseid'=>$course->get_id())))->out(false),
                'courseurl'     => (new \moodle_url('/course/view.php', array('id'=> $course->get_id())))->out(false),
                'assignurl'     => (new \moodle_url('/mod/assign/index.php', array('id'=> $course->get_id())))->out(false),
                'exerciseurl'   => (new \moodle_url('/mod/assign/view.php', array('id'=> $student->get_current_exercise()->id)))->out(false),
                'exercise'      => $course->get_exercise($student->get_current_exercise()->id)->name,
            );

            // send recommendation message
            $message = new notification($course);
            $message->send_recommendation_notification($data);

            mtrace('                recommendation notifications sent...');

            // reset notification setting
            $student->set_progress_flag(LOCAL_BOOKING_PROGFLAGS['NOTIFYENDORSE'], false);
        }
    }

    /**
     * Process availability posting notifications.
     *
     * @param student    $student   The student being checked
     * @param subscriber $course    The subscribing course.
     */
    protected function process_availability_postings(student $student, subscriber $course) {

        $haspostings = false;
        $postedslots = $student->get_progress_flag(LOCAL_BOOKING_PROGFLAGS['NOTIFYPOSTS']);

        if (!empty($postedslots)) {

            $slotids = explode(',', $postedslots);
            $postingstext = '';
            $postingshtml = '<table style="border-collapse: collapse; width: 400px"><tbody>';
            $previousday = '';

            foreach ($slotids as $slotid) {

                if (!empty($slotid)) {

                    // get each slot posted
                    $slot = $student->get_slot($slotid);

                    // format the availability slots postings
                    if ($haspostings = !empty($slot)) {
                        $startdate = new \DateTime('@'.$slot->starttime);
                        $sameday = $startdate->format('l') == $previousday;
                        $postingstext .= !$sameday ? PHP_EOL . $startdate->format('l M d\: ') : ', ';
                        $postingstext .= $startdate->format(' H:i\z') . ' - ' . (new \DateTime('@'.$slot->endtime))->format('H:i\z');
                        $postingshtml .= '<tr' . (!$sameday ? ' style="border-top: 1pt solid black"' : '') . '><td style="width: 100px">';
                        $postingshtml .= (!$sameday ? $startdate->format('l ') . '</td><td style="width: 100px;">' . $startdate->format('M d') : '&nbsp;</td><td>&nbsp;') . '</td>';
                        $postingshtml .= '<td style="width: 100px">' . $startdate->format('H:i\z') . '</td><td style="width: 100px">';
                        $postingshtml .= (new \DateTime('@'.$slot->endtime))->format('H:i\z') . '</td></tr>';
                        $previousday = $startdate->format('l');

                    }

                }
            }

            if ($haspostings) {
                $postingshtml .= '<tr style="border-top: 1pt solid black"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></tbody></table>';

                // get message data
                $data = array(
                    'courseurl'     => (new \moodle_url('/course/view.php', array('id'=>$course->get_id())))->out(false),
                    'coursename'    => $course->get_shortname(),
                    'assignurl'     => (new \moodle_url('/mod/assign/index.php', array('id'=>$course->get_id())))->out(false),
                    'studentname'   => $student->get_name(),
                    'firstname'     => $student->get_profile_field('firstname', true),
                    'postingstext'  => $postingstext,
                    'postingshtml'  => $postingshtml,
                    'bookingurl'    => (new \moodle_url('/local/booking/availability.php', array(
                        'courseid'      => $course->get_id(),
                        'userid'        => $student->get_id(),
                        'exid'          => $student->get_next_exercise()->id,
                        'action'        => 'book'
                        )))->out(false),
                    'exerciseurl'   => (new \moodle_url('/mod/assign/view.php', array('id'=> $student->get_next_exercise()->id)))->out(false),
                    'exercise'      => $course->get_exercise($student->get_next_exercise()->id)->name,
                );

                $message = new notification($course);
                $message->send_availability_posting_notification($data);

                mtrace('                availability posting notifications sent...');
            }

            // reset notification setting
            $student->set_progress_flag(LOCAL_BOOKING_PROGFLAGS['NOTIFYPOSTS'], '');
        }
    }

    /**
     * Process student graduating notifications.
     *
     * @param student    $student   The student being checked
     * @param subscriber $course    The subscribing course.
     */
    protected function process_graduations(student $student, subscriber $course) {

        if (!empty($course->gradmsgsubject) && !empty($course->gradmsgbody)) {

            $notify = false;
            if ($notifications = $student->get_progress_flag(LOCAL_BOOKING_PROGFLAGS['NOTIFY'])) {
                $notify = property_exists($notifications, 'graduation') ? $notifications->graduation : false;
            }

            if (!empty($notify)) {

                // get message data
                $grade = $student->get_grade($course->get_graduation_exercise_id());
                $examiner = new instructor($course, $grade->usermodified);
                $logbook = new logbook($course->get_id(), $student->get_id());
                $logbook->load();
                $logentry = $logbook->get_logentry_by_exericseid($course->get_graduation_exercise_id());
                $summary = $logbook->get_summary(true);

                // get core recipients CFIs and examiner
                $recipients = $course->get_flight_training_managers(false);
                $recipients[] = $examiner;

                // additional recipients based on course plugin settings
                if ($course->participantstonotify == 1) {

                    // send to all course members or active participants
                    $recipients = array_merge($recipients, $course->get_students('active', true), $course->get_instructors());

                } elseif ($course->participantstonotify == 2) {

                    // send to course members or active participants with the same group as the student
                    // get student groups
                    $groups = groups_get_user_groups($course->get_id(), $student->get_id());
                    $groupids = $groups[0];

                    // add members of the same group as recipients
                    $samegrouprecipients = [];
                    foreach ($groupids as $groupid) {
                        if (!$course->reserved_group($groupid)) {
                            // get all active group members
                            $groupmembers = groups_get_members($groupid);
                            foreach ($groupmembers as $groupmember) {
                                $samegrouprecipients[] = $course->get_participant($groupmember->id);
                            }
                        }
                    }

                    // merge all together
                    $recipients = array_merge($recipients, $samegrouprecipients);
                }

                $data = [
                    'graduateid'      => $student->get_id(),
                    'firstname'       => $student->get_profile_field('firstname', true),
                    'fullname'        => $student->get_name(),
                    'exercisename'    => $course->get_graduation_exercise_id(true),
                    'completiondate'  => (!empty($logentry) ? date_format((new \DateTime('@'.$logentry->get_flightdate())), 'F j, Y') : ''),
                    'enroldate'       => date_format($student->get_enrol_date(), 'F j, Y'),
                    'simulator'       => $student->get_profile_field('simulator'),
                    'totallessons'    => count($course->get_lessons()),
                    'totalsessions'   => booking::get_total_sessions($course->get_id(), $student->get_id()),
                    'totalsessionhrs' => $summary->totalsessiontime,
                    'totalflighthrs'  => $summary->totalflighttime,
                    'totaldualhrs'    => $summary->totaldualtime,
                    'totalmultihrs'   => $summary->totalmultipilottime,
                    'totalcopilothrs' => $summary->totalcopilottime ?: '00:00',
                    'totalpicustime'  => $summary->totalpicustime ?: '00:00',
                    'totalsolohrs'    => $summary->totalpictime ?: '00:00',
                    'rating'          => $course->outcomerating,
                    'examinername'    => $examiner->get_name(false),
                ];

                $message = new notification($course);
                $message->send_graduation_notification($recipients, $data, $course->gradmsgsubject, $course->gradmsgbody);

                mtrace('                graduation notifications sent...');

                // reset notification setting
                $student->set_progress_flag(LOCAL_BOOKING_PROGFLAGS['NOTIFYGRAD'], false);
            }
        }
    }
}
