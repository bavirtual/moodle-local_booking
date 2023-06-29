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
 * Student session booking specific profile for instructors
 * containing basic student course related information, administration,
 * and reporting functions.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\participant\entities\participant;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\output\views\profile_view;

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Set up the page.
$categoryid = optional_param('categoryid', null, PARAM_INT);
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$userid = optional_param('userid', 0, PARAM_INT);

$url = new moodle_url('/local/booking/view.php');
$url->param('courseid', $courseid);

$PAGE->set_url($url);

$context = context_course::instance($courseid);
$title = get_string('profile' . (current(get_user_roles($context, $userid))->shortname!='student' ? 'instructor' : 'student'), 'local_booking');

// basic access check
require_login($course, false);
require_capability('local/booking:view', $context);

// define subscriber globally
if (empty($COURSE->subscriber)) {
    $COURSE->subscriber = new subscriber($courseid);
}

$navbartext = participant::get_fullname($userid);
$PAGE->navbar->add($navbartext);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($COURSE->shortname . ': ' . $title . ' - ' . participant::get_fullname($userid), 'local_booking');
$PAGE->set_heading($title . ' - ' . participant::get_fullname($userid), 'local_booking');
$PAGE->add_body_class('path-local-booking');

// get student profile view
$profileview = new profile_view($context, $courseid, ['subscriber'=>$COURSE->subscriber, 'userid'=>$userid, 'role'=>($role?'instructor':'student')]);

// output profile page
echo $OUTPUT->header();
echo $profileview->get_renderer()->start_layout();
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));
echo $profileview->output();
echo html_writer::end_tag('div');
echo $profileview->get_renderer()->complete_layout();
echo $OUTPUT->footer();
