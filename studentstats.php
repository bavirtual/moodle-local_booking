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
 * Student stats including booking and checklist items
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (captainmoose)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use local_booking\output\action_bar;
use local_booking\output\views\stats_view;

// Get parameters
$studentid = required_param('studentid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

// Security checks
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('local/booking:view', $context);

// define page url for use in links and redirects
$url = new moodle_url('/local/booking/studentstats.php', ['studentid' => $studentid, 'courseid' => $courseid]);

// define session booking plugin subscriber globally
$subscriber = get_course_subscriber_context($url->out(false), $courseid);

// Set up the page
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('studentstatspage', 'local_booking'));
$PAGE->set_heading(get_string('studentstatspage', 'local_booking'));
$PAGE->set_pagelayout('report');

// Add custom body class for scoped styling
$PAGE->add_body_class('local-booking-report');

// Add the custom CSS for this page only
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/booking/styles/stats_report.css'));

// Create exporter
$data = [
    'studentid' => $studentid,
    'lastupdated' => userdate(time(), '%d %b %Y'),
];
$related = [
    'context' => $context,
    'subscriber' => $subscriber,
];

// get student stats view for a student
$statsview = new stats_view($data, $related);
$actionbar = new action_bar($PAGE, 'stats', ['course'=>$subscriber]);

// output student stats view
echo $OUTPUT->header();

echo $statsview->get_renderer()->start_layout();
// echo $statsview->get_renderer()->render_tertiary_navigation($actionbar);
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));
echo $statsview->output();
echo html_writer::end_tag('div');
echo $statsview->get_renderer()->complete_layout();

echo $OUTPUT->footer();