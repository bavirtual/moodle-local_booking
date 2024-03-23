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
 * Logbook entries view for pilots.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\participant\entities\participant;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\views\manage_action_bar;
use local_booking\output\views\logbook_view;

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

defined('MOODLE_INTERNAL') || die();

// Set up the page.
global $COURSE, $USER;

$categoryid = optional_param('categoryid', null, PARAM_INT);
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);
$username = participant::get_fullname($userid);
$format = optional_param('format', '', PARAM_TEXT);
$course = get_course($courseid);
$title = $USER->id == $userid ? get_string('logbookmy', 'local_booking') : (get_string('pilotlogbook', 'local_booking') . ' - ' . $username);

$params = array('courseid'=>$courseid, 'userid'=>$userid);
$url = new moodle_url('/local/booking/logbook.php', $params);

$PAGE->set_url($url);

$context = context_course::instance($courseid);

// basic access check
require_login($course, false);
require_capability('local/booking:logbookview', $context);

// deny access if not an instructor and not view own logbook
if (!has_capability('local/booking:view', $context) && $USER->id != $userid) {
    throw new required_capability_exception($context, $capability, 'nopermissions', '');
}

// define subscriber globally
if (empty($COURSE->subscriber)) {
    $COURSE->subscriber = new subscriber($courseid);
}

// add jquery, logbook_easa.js for EASA datatable, and RobinHerbots-Inputmask library to mask flight times in the Log Book modal form
$PAGE->requires->jquery();
$PAGE->requires->js(new \moodle_url($CFG->wwwroot . '/local/booking/js/datatables/logbook_easa.js'));
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/local/booking/js/inputmask/dist/jquery.inputmask.min.js'), true);

// add js and css requires from config
$datatablecdns = $COURSE->subscriber->get_booking_config('datatables', true);
if (!empty($datatablecdns)) {
    $jscdns = $datatablecdns['js'];
    $csscdns = $datatablecdns['css'];
    // get all js CDNs
    foreach ($jscdns as $jscdn) {
        $PAGE->requires->js(new \moodle_url($jscdn), true);
    }
    // get all css CDNs
    foreach ($csscdns as $csscdn) {
        $PAGE->requires->css(new \moodle_url($csscdn));
    }
}

$PAGE->navbar->add($USER->id == $userid ? get_string('logbookmy', 'local_booking') : ucfirst(get_string('logbook', 'local_booking')));
$PAGE->set_pagelayout('admin'); // wide page layout
$PAGE->set_title($COURSE->shortname . ': ' . $title, 'local_booking');
$PAGE->set_heading($title, 'local_booking');
$PAGE->add_body_class('path-local-booking');

// set user preference for logbook template format
if (empty($format)) {
    $format = get_user_preferences('local_booking_logbookformat', 'std');
} else {
    $setformat = get_user_preferences('local_booking_logbookformat', 'std');
    if ($format != $setformat)
        set_user_preferences(array('local_booking_logbookformat'=>$format));
}

// get logbook view data
$pilot   = $COURSE->subscriber->get_participant($userid);
$editor  = $COURSE->subscriber->get_instructor($USER->id);
$logbook = $pilot->get_logbook(true, $format == 'easa');
$totals  = (array) $logbook->get_summary(true, $format == 'easa', $COURSE->subscriber->get_graduation_exercise());
$data    = [
    'contextid'     => $context->id,
    'courseid'      => $courseid,
    'userid'        => $userid,
    'username'      => $pilot->get_fullname($userid),
    'courseshortname' => $COURSE->shortname,
    'logbook'       => $logbook,
    'isstudent'     => $pilot->is_student(),
    'isinstructor'  => $pilot->is_instructor(),
    'isexaminer'    => $pilot->is_examiner(),
    'canedit'       => $editor->is_instructor(),
    'hasfindpirep'  => $COURSE->subscriber->has_integration('pireps'),
    'format'        => $format,
    'easaformaturl' => $PAGE->url . '&format=easa',
    'stdformaturl'  => $PAGE->url . '&format=std',
    'shortdate'     => $format == 'easa'
];
// get logbook view
$logbookview = new logbook_view($context, $courseid, $data + $totals);
$actionbar = new manage_action_bar($PAGE, 'logbook');

// output logbook page
echo $OUTPUT->header();
echo $logbookview->get_renderer()->start_layout();
echo $logbookview->get_renderer()->render_tertiary_navigation($actionbar);
echo $logbookview->output();
echo $logbookview->get_renderer()->complete_layout();
echo $OUTPUT->footer();
