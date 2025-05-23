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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_badges\badge;
use local_booking\local\participant\entities\student;

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

// define session booking plugin subscriber globally
$subscriber = get_course_subscriber_context($url->out(false), $courseid);

// get the graduating student
$student = new student($subscriber, $studentid);
$title = $student->get_name()  . ' ' . get_string('coursecompletion', 'local_booking');

// check if student evaluation is required and if so whether the student has been evaluated
if ($subscriber->requires_skills_evaluation()) {

// check if student evaluation is required and if so whether the student has been evaluated
if ($COURSE->subscriber->requires_skills_evaluation()) {

    // verify credentials, if the certifier is not the same as the examiner throw invalid permissions error
    $exerciseid = $COURSE->subscriber->get_graduation_exercise();
    $grade = $student->get_grade($exerciseid, true);
    $lastattempt = (count($grade->attempts) ?: 1) - 1;
    $examinerid = $grade->attempts[$lastattempt]->grader;
  
    if ($examinerid != $USER->id)
        throw new \Error(get_string('errorcertifiernotexaminer', 'local_booking'));

    // perform student certification actions
    if (!$student->graduated(true)) {
        // send badges
        $badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid, '', '' , 0, 0);

        foreach ($badges as $coursebadge) {

            $badgeid = $coursebadge->id;
            $badge = new badge($badgeid);

            // check for manual criteria badges (awarded manually by the examiner here)
            if (array_search(BADGE_CRITERIA_TYPE_MANUAL, array_column($badge->get_criteria(), 'criteriatype'))) {

                // get badge roles
                $acceptedroles = array_keys($badge->criteria[BADGE_CRITERIA_TYPE_MANUAL]->params);

                // check if the badge can be awarded by the examiner
                if (!empty($acceptedroles)) {

                    // verify the badge is active
                    if (!$badge->is_active()) {
                        throw new Error(get_string('donotaward', 'badges'));
                    }

                    // process manual award of the badge
                    if (process_manual_award($studentid, $USER->id, $acceptedroles[0], $badgeid)) {
                        // If badge was successfully awarded, review manual badge criteria.
                        $data->crit = $badge->criteria[BADGE_CRITERIA_TYPE_MANUAL];
                        badges_award_handle_manual_criteria_review($data);
                        $badgecount++;
                    }
                }
            }
        }


        // set notifications flag so a notification is sent about the student's graduation
        $student->set_progress_flag(LOCAL_BOOKING_PROGFLAGS['NOTIFYGRAD'], true);

        // add student to graduates group
        $groupid = groups_get_group_by_name($courseid, LOCAL_BOOKING_GRADUATESGROUP);
        groups_add_member($groupid, $studentid);

        // graduate student
        $subscriber->force_student_course_completion($studentid);
    }

    // output certification message
    $data = [
        'courseid'         => $courseid,
        'userid'           => $studentid,
        'fullname'         => $student->get_name(),
        'courseshortname'  => $subscriber->get_shortname(),
        'attempt'          => $lastattempt+1
    ];
    $hascongratsmsg = !empty($subscriber->gradmsgsubject) && !empty($subscriber->gradmsgbody);
    $gradmsg = get_string('graduationconfirmation', 'local_booking', $data);
    $gradmsg .= $badgecount > 0 ? get_string('graduationconfirmationbadges', 'local_booking', $data) : '';
    $gradmsg .= $hascongratsmsg ? get_string('graduationconfirmationnotify'.$subscriber->participantstonotify, 'local_booking') : '';
    $gradmsg .= '</ul>';

    // output graduation process status
    $navbartext = $student->get_fullname($studentid);

    $PAGE->navbar->add($navbartext);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($context);
    $PAGE->set_title($COURSE->shortname . ': ' . $title);
    $PAGE->set_heading($title);
    $PAGE->add_body_class('path-local-booking');

    $renderer = $PAGE->get_renderer('local_booking');

    echo $OUTPUT->header();
    echo $renderer->start_layout();
    echo html_writer::start_tag('div');
    echo $gradmsg;
    echo html_writer::end_tag('div');
    echo $renderer->complete_layout();
    echo $OUTPUT->footer();

} else {

    // redirect to the user's profile page
    redirect($url);

}

