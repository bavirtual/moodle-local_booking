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

use local_booking\local\subscriber\entities\subscriber;

global $USER, $COURSE;

// Set up the page.
$categoryid = optional_param('categoryid', null, PARAM_INT);
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
$pluginname = $course->shortname . ' ' . get_config('local_booking', 'atoname') . ' ' . get_string('pluginname', 'local_booking');
$title = get_string('weeklytitle', 'local_booking');
$week = get_string('week', 'local_booking');
$time = optional_param('time', 0, PARAM_INT);

$url = new moodle_url('/local/booking/availability.php');
$url->param('courseid', $courseid);

// view all capability for instructors
if (has_capability('local/booking:view', $context)) {
    $view = $action == 'book' ? 'user' : 'all';
    // $url->param('view', $view);
} else {
    // define subscriber globally
    if (empty($COURSE->subscriber))
        $COURSE->subscriber = new subscriber($courseid);

    $student = $COURSE->subscriber->get_student($USER->id);
    $action = 'post';
    $url->param('time', $student->get_next_allowed_session_date()->getTimestamp());
    // $url->param('action', $action);
}

$PAGE->set_url($url);

// If a day, month and year were passed then convert it to a timestamp. If these were passed
// then we can assume the day, month and year are passed as Gregorian, as no where in core
// should we be passing these values rather than the time. This is done for BC.
if (!empty($day) && !empty($mon) && !empty($year)) {
    if (checkdate($mon, $day, $year)) {
        $time = make_timestamp($year, $mon, $day);
    }
}

if (empty($time)) {
    $time = time();
}

$calendar = calendar_information::create($time, $courseid, !empty($categoryid) ? $categoryid : $course->category);

$PAGE->navbar->add(userdate($time, get_string('strftimeweekinyear','local_booking')));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pluginname, 'local_booking');
$PAGE->set_heading($pluginname, 'local_booking');// . ' course id='  . $courseid);
$PAGE->add_body_class('path-availability');

$template = 'local_booking/calendar_week';
$renderer = $PAGE->get_renderer('core_calendar');
$calendar->add_sidecalendar_blocks($renderer, true, null);

echo $OUTPUT->header();
echo $renderer->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));

// action data for booking view
$actiondata = [
    'action'     => $action,
    'student'    => $COURSE->subscriber->get_participant($userid),
    'exerciseid' => $exerciseid,
    ];

list($data, $template) = get_weekly_view($calendar, $actiondata, $view);
echo $renderer->render_from_template($template, $data);

echo html_writer::end_tag('div');
echo $renderer->complete_layout();
echo $OUTPUT->footer();
