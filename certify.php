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
 * Graduates a student from the course
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_badges\badge;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\message\notification;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->dirroot . '/badges/lib/awardlib.php');

// Get URL parameters.
$courseid  = optional_param('courseid', 0, PARAM_INT);
$studentid = optional_param('userid', 0, PARAM_INT);

$url = new moodle_url('/local/booking/profile.php');
$url->param('courseid', $courseid);
$url->param('userid', $studentid);

$PAGE->set_url($url);

// set context for the module and other requirements by the assignment
$context = context_course::instance($courseid);

require_login($courseid);
require_capability('local/booking:view', $context);

// get the graduating student
$COURSE->subscriber = new subscriber($courseid);
$student = new student($COURSE->subscriber, $studentid);
$evaluationrequired = !empty($COURSE->subscriber->examinerformurl);
$title = $COURSE->subscriber->get_shortname() . ' ' . get_string('pluginname', 'local_booking');
$title = get_string('pluginname', 'local_booking');

// verify credentials, if the certifier is not the same as the examiner throw invalid permissions error
$examinerid = $student->get_grade($COURSE->subscriber->get_graduation_exercise())->get_graderid();
if ($examinerid != $USER->id)
    throw new Error(get_string('errorcertifiernotexaminer', 'local_booking'));

// check if student evaluation is required and if so whether the student has been evaluated
if ($evaluationrequired && !$student->evaluated()) {

    // redirect to the evaluation form
    $params = ['courseid' => $courseid,'userid' => $studentid, 'report' => 'examiner'];
    redirect(new moodle_url('/local/booking/report.php', $params));

} else if (!$student->graduated()) {

        // send badges
        $badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid, '', '' , 0, 0);

        foreach ($badges as $coursebadge) {

            $badgeid = $coursebadge->id;
            $badge = new badge($badgeid);

            // check for manual criteria badges (awarded manually by the exmainer here)
            if (array_search(BADGE_CRITERIA_TYPE_MANUAL, array_column($badge->get_criteria(), 'criteriatype'))) {

                // get badge roles
                $acceptedroles = array_keys($badge->criteria[BADGE_CRITERIA_TYPE_MANUAL]->params);

                // check if the badge is awardable by the examiner
                if (!empty($acceptedroles)) {

                    // verify the badge is active
                    if (!$badge->is_active()) {
                        throw new Error(get_string('donotaward', 'badges'));
                    }

                    // process manual award of the badge
                    if (process_manual_award($studentid, $USER->id, $acceptedroles[0], $badgeid)) {
                        // If badge was successfully awarded, review manual badge criteria.
                        $data = new stdClass();
                        $data->crit = $badge->criteria[BADGE_CRITERIA_TYPE_MANUAL];
                        $data->userid = $studentid;
                        badges_award_handle_manual_criteria_review($data);
                    }
                }
            }

        }

        // send message to course students and instructors
        $coursemembers = array_merge($COURSE->subscriber->get_students('active', true), $COURSE->subscriber->get_instructors());
        $logbook = new logbook($courseid, $studentid);
        $logbook->load();
        $summary = $logbook->get_summary(true);
        $data = [
            'graduateid'      => $student->get_id(),
            'firstname'       => $student->get_profile_field('firstname', true),
            'fullname'        => $student->get_name(),
            'courseshortname' => $COURSE->subscriber->get_shortname(),
            'coursename'      => $COURSE->subscriber->get_fullname(),
            'exercisename'    => $COURSE->subscriber->get_exercise_name($COURSE->subscriber->get_graduation_exercise()),
            'completiondate'  => date_format($student->get_last_graded_date(), 'F j, Y'),
            'enroldate'       => date_format($student->get_enrol_date(), 'F j, Y'),
            'simulator'       => $student->get_profile_field('simulator'),
            'totalsessions'   => count($student->get_grades()),
            'totalflighthrs'  => $summary->totaltime,
            'totaldualhrs'    => $summary->totaldualtime,
            'totalsolohrs'    => $summary->totalpictime,
            'rating'          => $COURSE->subscriber->vatsimrating,
            'trainingemail'   => $COURSE->subscriber->ato->email,
            'traininglogourl' => $COURSE->subscriber->ato->logo,
            'atoname'         => $COURSE->subscriber->ato->name,
            'atourl'          => $COURSE->subscriber->ato->url,
            'congrats1pic'    => $CFG->wwwroot . '/local/booking/pix/congrats1.png',
            'congrats2pic'    => $CFG->wwwroot . '/local/booking/pix/congrats2.png',
            'calendarpic'     => $CFG->wwwroot . '/local/booking/pix/calendar.svg',
            'planepic'        => $CFG->wwwroot . '/local/booking/pix/book.svg',
            'cappic'          => $CFG->wwwroot . '/local/booking/pix/graduate.svg'
        ];
        $message = new notification();
        $message->send_graduation_notification($coursemembers, $data);

        // add student to graduates group
        $groupid = groups_get_group_by_name($courseid, LOCAL_BOOKING_GRADUATESGROUP);
        groups_add_member($groupid, $studentid);

        // output congratulatory message
        $navbartext = $student->get_fullname($studentid);
        $PAGE->navbar->add($navbartext);
        $PAGE->set_pagelayout('standard');
        $PAGE->set_context($context);
        $PAGE->set_title($title, 'local_booking');
        $PAGE->set_heading($title, 'local_booking');
        $PAGE->add_body_class('path-local-booking');

        $renderer = $PAGE->get_renderer('local_booking');

        echo $OUTPUT->header();
        echo $renderer->start_layout();

        // add next action button
        echo html_writer::start_tag('div', array('class'=>'container d-flex align-items-center justify-content-center mb-2'));
        echo $OUTPUT->render(new single_button(new moodle_url('/local/booking/view.php', ['courseid'=>$courseid]), get_string('back'), 'get', true));
        echo html_writer::end_tag('div');

        // message section
        echo get_string('graduationconfirmation', 'local_booking', $data);

        echo html_writer::end_tag('div');
        echo $renderer->complete_layout();
        echo $OUTPUT->footer();

} else {

    // redirect to the user's profile page
    redirect($url);

}

