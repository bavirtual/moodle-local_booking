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
 * Main Session Booking plugin view of students progression,
 * instructor booked sessions, and instructor participation view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\output\action_bar;
use local_booking\output\views\booking_view;

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Set up the page.
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$title = get_string('pluginname', 'local_booking');
$page = optional_param('page', 0, PARAM_INT);

$url = new moodle_url('/local/booking/progression.php');
$url->param('courseid', $courseid);

$PAGE->set_url($url);

$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:availabilityview', $context);

// define session booking plugin subscriber globally
$subscriber = get_course_subscriber_context($url->out(false), $courseid);
$instructor = $subscriber->get_instructor($USER->id);


// get students progression view
$data = [
    'studentid' => 0,
    'view'      => 'sessions',
    'sorttype'  => '',
    'filter'    => 'active',
    'action'    => 'readonly',
    'page'      => $page,
];
// get booking view
$bookingview = new booking_view($data, ['subscriber'=>$subscriber, 'context'=>$context]);
$additional = ['course' => $subscriber->get_course(), 'bookingparams'=>$bookingview->get_exportdata()];
$actionbar = new action_bar($PAGE, 'view', $additional);

$navbartext = get_string('bookingprogression', 'local_booking');
$PAGE->navbar->add($navbartext);
$PAGE->set_pagelayout('admin');   // wide page layout
$PAGE->set_title($COURSE->shortname . ': ' . $title);
$PAGE->set_heading($COURSE->fullname);
$PAGE->add_body_class('path-local-booking');

echo $OUTPUT->header();
echo $bookingview->get_renderer()->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));
echo $bookingview->get_renderer()->render_tertiary_navigation($actionbar);
echo $bookingview->get_student_progression();
echo html_writer::end_tag('div');
echo $bookingview->get_renderer()->complete_layout();
echo $OUTPUT->footer();
