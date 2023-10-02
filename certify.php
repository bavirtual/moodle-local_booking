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
use local_booking\local\participant\entities\participant;
use local_booking\local\views\manage_action_bar;
use local_booking\local\participant\entities\participant;
use local_booking\local\views\manage_action_bar;
use local_booking\local\participant\entities\student;
use local_booking\local\report\pdf_report_skilltest;
use local_booking\local\report\pdf_report_skilltest;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\message\notification;
use local_booking\local\message\notification;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->dirroot . '/badges/lib/awardlib.php');

// Get URL parameters.
$courseid  = optional_param('courseid', 0, PARAM_INT);
$studentid = optional_param('userid', 0, PARAM_INT);
$action = optional_param('action', 'certify', PARAM_RAW);

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
$title = $student->get_name()  . ' ' . get_string('coursecompletion', 'local_booking');

// verify credentials, if the certifier is not the same as the examiner throw invalid permissions error
$exerciseid = $COURSE->subscriber->get_graduation_exercise();
$grade = $student->get_grade($exerciseid, true);
$examinerid = $grade->usermodified;

if ($examinerid != $USER->id)
    throw new \Error(get_string('errorcertifiernotexaminer', 'local_booking'));

// check if student evaluation is required and if so whether the student has been evaluated
if ($COURSE->subscriber->requires_skills_evaluation()) {
    // certify the student
    // generate form data file
    if ($action == 'certify' || $action == 'generate') {
        $evaluationform = new pdf_report_skilltest($COURSE->subscriber, $student, 'evalution');
        if (!$outputform = $evaluationform->generate_evaluation_form($grade))
            throw new \Error(get_string('errorexaminerevalformunable', 'local_booking'));

        // upload the form to the graded exercise
        $grade->save_feedback_file($outputform, $student);

        // clean up: remove created evaluation form staged file
        $evaluationform->unlink($outputform);
    }

    // perform student certification actions
    if ($action == 'certify' && !$student->graduated()) {
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

        // flag the student activating graduation notifications
        set_user_preference('local_booking_' . $courseid . '_graduationnotify', true, $studentid);

        // add student to graduates group
        $groupid = groups_get_group_by_name($courseid, LOCAL_BOOKING_GRADUATESGROUP);
        groups_add_member($groupid, $studentid);
    }

    // output certification or sending of evaluation form
    if ($action == 'certify' || $action == 'send') {

        // output congratulatory message
        $navbartext = $student->get_fullname($studentid);
        $PAGE->navbar->add($navbartext);
        $PAGE->set_pagelayout('standard');
        $PAGE->set_context($context);
        $PAGE->set_title($COURSE->shortname . ': ' . $title, 'local_booking');
        $PAGE->set_heading($title, 'local_booking');
        $PAGE->add_body_class('path-local-booking');

        $renderer = $PAGE->get_renderer('local_booking');

        echo $OUTPUT->header();
        echo $renderer->start_layout();
        echo html_writer::start_tag('div');

        // certification action options
        if ($action == 'certify') {
            // output certification message
            $data = [
                'url'             => '/local/booking/report.php',
                'courseid'        => $courseid,
                'userid'          => $studentid,
                'firstname'       => $student->get_name(false, 'first'),
                'fullname'        => $student->get_name(),
                'courseshortname' => $COURSE->subscriber->get_shortname()
            ];
            $certifiedactionbar = new manage_action_bar($PAGE, 'certify', $data);
            echo get_string('graduationconfirmation', 'local_booking', $data);
            echo $renderer->render_tertiary_navigation($certifiedactionbar);

        } elseif ($action == 'send') {

            // get feedback file
            $fs = get_file_storage();
            $feedbackfile = $grade->get_feedback_file('assignfeedback_file', 'feedback_files', '', false);

            // send the form email message
            $examiner = new participant($COURSE->subscriber, $USER->id);
            $data = [
                'vatsimcertuid' => $COURSE->subscriber->get_booking_config('vatsimcertemail'),
                'examinerid'    => $examiner->get_id(),
                'trainingmanagers'=> $COURSE->subscriber->get_flight_training_managers(),
                'vatsimrating'  => $COURSE->subscriber->vatsimrating,
                'studentname'   => $student->get_name(false),
                'studentvatsimid' => $student->get_profile_field('VATSIMID'),
                'coursename'    => $COURSE->subscriber->get_fullname(),
                'examinername'  => $examiner->get_name(false),
                'evaluationformfile' => $fs->get_file_system()->get_local_path_from_storedfile($feedbackfile),
                'evaluationformfilename' => $feedbackfile->get_filename()
            ];
            if (notification::send_evaluationform_notification($data)) {
                echo get_string('graduationemailconfirmation', 'local_booking', [
                    'certbodyemail' => $COURSE->subscriber->get_booking_config('vatsimcertemail'),
                    'examineremail' => $examiner->get_profile_field('email', true),
                    'studentname'   => $student->get_name(false),
                    'vatsimpramsurl'=> $COURSE->subscriber->get_booking_config('vatsimpramsurl'),
                ]);
            } else {
                echo get_string('errorcertificationemail', 'local_booking');
            }
        }

        echo html_writer::end_tag('div');
        echo $renderer->complete_layout();
        echo $OUTPUT->footer();

    } elseif ($action == 'generate') {
        $newevalformurl = new moodle_url('/local/booking/report.php', [
            'courseid' => $courseid,
            'userid'   => $studentid,
            'report'   => 'evalform',
            'action'   => 'generate'
        ]);
        redirect($newevalformurl);
    }
} else {

    // redirect to the user's profile page
    redirect($url);

}

