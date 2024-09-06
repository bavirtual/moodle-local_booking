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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\subscriber\entities\subscriber;
use local_booking\output\views\booking_view;

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Set up the page.
$categoryid = optional_param('categoryid', null, PARAM_INT);
$courseid   = optional_param('courseid', SITEID, PARAM_INT);
$course     = get_course($courseid);
$studentid  = optional_param('userid', 0, PARAM_INT);
$sorttype   = optional_param('sort', '', PARAM_ALPHA);
$action     = optional_param('action', 'book', PARAM_ALPHA);
$filter     = optional_param('filter', 'active', PARAM_ALPHA);
$page       = optional_param('page', 0, PARAM_INT);
$context     = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:view', $context);

$url = new moodle_url('/local/booking/view.php');
$url->param('courseid', $courseid);
$PAGE->set_url($url);

// Flight rules library RobinHerbots-Inputmask library to mask flight times in the Log Book modal form
$PAGE->requires->jquery();
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/booking/js/inputmask/dist/jquery.inputmask.min.js'), true);

$navbartext = $action == 'book' ? get_string('bookingdashboard', 'local_booking') : get_string('bookingsessionselection', 'local_booking');
$PAGE->navbar->add($navbartext);
$PAGE->set_pagelayout('admin');   // wide page layout
$PAGE->set_title($COURSE->shortname . ': ' . get_string('pluginname', 'local_booking'), 'local_booking');
$PAGE->set_heading(get_string('bookingdashboard', 'local_booking'), 'local_booking');
$PAGE->add_body_class('path-local-booking');

// instructor object
if (empty($COURSE->subscriber)) {
    $COURSE->subscriber = new subscriber($courseid);
}
$instructor = $COURSE->subscriber->get_instructor($USER->id);

// get booking view data
$data = [
    'instructor' => $instructor,
    'studentid'  => $studentid,
    'action'     => $action,
    'view'       => 'sessions',
    'sorttype'   => $sorttype,
    'filter'     => $filter,
    'page'       => $page,
];
// get booking view
$bookingview = new booking_view($data, ['subscriber'=>$COURSE->subscriber, 'context'=>$context]);

echo $OUTPUT->header();
echo $bookingview->get_renderer()->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));
echo $bookingview->output();
echo html_writer::end_tag('div');
echo $bookingview->get_renderer()->complete_layout();
echo $OUTPUT->footer();
