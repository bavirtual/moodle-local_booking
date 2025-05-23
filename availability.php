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
 * Session booking availability slots view.
 * Allows students to post slots and instructors
 * to view all slots posted by students.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar
 * @copyright  BAVirtual.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

defined('MOODLE_INTERNAL') || die();

use local_booking\output\action_bar;
use local_booking\output\views\calendar_view;

global $USER, $COURSE;

// Set up the page.
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:availabilityview', $context);

$action =  optional_param('action', null, PARAM_RAW);
$view =  optional_param('view', 'user', PARAM_RAW);
$userid = optional_param('userid', $USER->id, PARAM_INT);
$exerciseid = optional_param('exid', 0, PARAM_INT);
$course = get_course($courseid);
$week = get_string('week', 'local_booking');
$time = optional_param('time', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$url = new moodle_url('/local/booking/availability.php');
$url->param('courseid', $courseid);

// define session booking plugin subscriber globally
$subscriber = get_course_subscriber_context($url->out(false), $courseid);

// view all capability for instructors
if (has_capability('local/booking:view', $context)) {
    $view = $action == 'book' ? 'user' : 'all';
    $student = $userid ? $subscriber->get_student($userid) : null;
} else {
    $student = $subscriber->get_student($USER->id);
    $action = 'post';
    $url->param('time', $student->get_next_allowed_session_date()->getTimestamp());
}

$PAGE->set_url($url);
$PAGE->requires->js_call_amd('local_booking/calendar', 'init', ['#region-main-box']);

// If a day, month and year were passed then convert it to a timestamp. If these were passed
// then we can assume the day, month and year are passed as Gregorian, as no where in core
// should we be passing these values rather than the time. This is done for BC.
if (!empty($day) && !empty($mon) && !empty($year)) {
    if (checkdate($mon, $day, $year)) {
        $time = make_timestamp($year, $mon, $day);
    }
}

// go to next week if we're on the last day of the week and no booking can be made
if (empty($time)) {
    $time =  time();
}

$calendar = \calendar_information::create($time, $courseid);

$PAGE->navbar->add(userdate($time, get_string('weekinyear','local_booking', date('W', $time))));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($COURSE->shortname . ': ' . get_string('pluginname', 'local_booking'));
$PAGE->set_heading($COURSE->fullname);
$PAGE->add_body_class('path-availability');

$calenderrenderer = $PAGE->get_renderer('core_calendar');
$calendar->add_sidecalendar_blocks($calenderrenderer, true);

// get data for calendar view
$data = [
    'calendar'    => $calendar,
    'view'        => $view,
    'action'      => $action,
    'student'     => $student,
    'exerciseid'  => $exerciseid,
    'confirm'     => $confirm,
];

    // get calendar view for Session booking
$calendarview = new calendar_view($data, ['subscriber'=>$subscriber, 'context'=>$context]);
$actionbar = new action_bar($PAGE, 'calendar', ['calendarparams'=>$calendarview->get_exportdata()]);

// output calendar view
echo $OUTPUT->header();
echo $calendarview->get_renderer()->start_layout();
echo $calendarview->get_renderer()->render_tertiary_navigation($actionbar);
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));
echo $calendarview->output();
echo html_writer::end_tag('div');
echo $calendarview->get_renderer()->complete_layout();

echo $OUTPUT->footer();
