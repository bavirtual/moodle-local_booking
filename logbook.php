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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

defined('MOODLE_INTERNAL') || die();

// Set up the page.
global $COURSE, $USER;

$userid = $USER->id;
$categoryid = optional_param('categoryid', null, PARAM_INT);
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$format = optional_param('format', null, PARAM_TEXT);
$course = get_course($courseid);
$title = $course->shortname . ' ' . get_string('logbook', 'local_booking');
$title = get_string('logbook', 'local_booking');

$params = array('courseid'=>$courseid);
$url = new moodle_url('/local/booking/logbook.php', $params);

$PAGE->set_url($url);

$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:logbookview', $context);

$PAGE->requires->jquery();
// RobinHerbots-Inputmask library to mask flight times in the Log Book modal form
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/booking/js/inputmask-5/dist/jquery.inputmask.min.js'), true);

$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js'), true);
$PAGE->requires->css(new \moodle_url('https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css'));
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js'), true);
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js'), true);
$PAGE->requires->css(new \moodle_url('https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css'));
$PAGE->requires->js(new \moodle_url($CFG->wwwroot . '/local/booking/js/datatables/logbook.js'));

$PAGE->navbar->add(get_string('logbook', 'local_booking'));
$PAGE->set_pagelayout('standard');  // otherwise use 'standard' layout
$PAGE->set_title($title, 'local_booking');
$PAGE->set_heading($title, 'local_booking');
$PAGE->add_body_class('path-local-booking');

$renderer = $PAGE->get_renderer('local_booking');

echo $OUTPUT->header();
echo $renderer->start_layout();

list($data, $template) = get_logbook_view($courseid, $userid, $format, true);
echo $renderer->render_from_template($template, $data);

echo $renderer->complete_layout();
echo $OUTPUT->footer();
