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
 * Checklist sign-off page
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/booking/lib.php');
require_once($CFG->dirroot . '/mod/checklist/lib.php');

use local_booking\output\action_bar;
use local_booking\output\views\checklist_view;

// Get parameters
$courseid = required_param('courseid', PARAM_INT);
$studentid = optional_param('userid', 0, PARAM_INT);
$bookingid = optional_param('bookingid', 0, PARAM_INT);
$exerciseid   = optional_param('exeid', 0, PARAM_INT);
$sessionpassed= optional_param('passed', 1, PARAM_INT);

// Security checks
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('mod/checklist:updateother', $context); // Instructor capability

$url = new moodle_url('/local/booking/checklist_grading.php', [
    'courseid' => $courseid,
    'studentid' => $studentid,
    'bookingid' => $bookingid
]);

// define session booking plugin subscriber globally
$subscriber = get_course_subscriber_context($url->out(false), $courseid);
$student = $subscriber->get_student($studentid);

// Set up the page
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('checklistgrading', 'local_booking'));
$PAGE->set_heading(format_string($subscriber->get_course()->fullname));
$PAGE->set_pagelayout('standard');

// Add custom body class
$PAGE->add_body_class('local-booking-checklist-grading');

// Add CSS and JavaScript
$PAGE->requires->css('/local/booking/styles/checklist_grading.css');
$PAGE->requires->js_call_amd('local_booking/checklist_grading', 'init');

// Get checklists with items for the student
$checklists = $subscriber->get_checklists(true, false, $studentid);
// $assignurl = new moodle_url(, ['courseid' => $courseid, 'userid' => $studentid, 'exeid' => $exerciseid, 'passed' => $sessionpassed]);

// Create exporter
$data = [
    'courseid' => $courseid,
    'studentid' => $studentid,
    'bookingid' => $bookingid,
    'exeid' => $exerciseid,
    'passed' => $sessionpassed,
    'grading' => $bookingid !== 0, // Used to conditionally show action bar buttons
    'assignurl' => '/local/booking/assign.php',//$assignurl->out(false),
    'hasstudent' => !empty($studentid),
    'studentname' => $student->get_name(),
    'studentpicture' => $OUTPUT->user_picture($student->get_user(), [
        'size' => 50,
        'class' => 'studentpicture'
    ])
];
$related = [
    'context' => $context,
    'subscriber' => $subscriber,
    'student' => $student,
    'checklists' => $checklists,
];

// get checklist view
$checklistview = new checklist_view($data, $related);
$actionbar = new action_bar($PAGE, 'checklist', ['course' => $subscriber, 'checklistparams' => $data]);

echo $OUTPUT->header();
echo $checklistview->get_renderer()->start_layout();
echo $checklistview->get_renderer()->render_tertiary_navigation($actionbar);
echo $checklistview->output();
echo $checklistview->get_renderer()->complete_layout();
echo $OUTPUT->footer();
