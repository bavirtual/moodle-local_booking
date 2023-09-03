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
 * Course specific reporting for:
 *  Mentor report
 *  Theory examination report
 *  Practical Examination report,
 *  Skills test examiner evaluation form.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\views\manage_action_bar;

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Set up the page.
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$userid = optional_param('userid', 0, PARAM_INT);
$reporttype = optional_param('report', 'mentor', PARAM_RAW);
$action = optional_param('action', '', PARAM_RAW);
$title = get_string($reporttype . 'report', 'local_booking');

$url = new moodle_url('/local/booking/view.php');
$url->param('courseid', $courseid);
$url->param('userid', $userid);
$url->param('report', $reporttype);

$PAGE->set_url($url);

$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:view', $context);

$COURSE->subscriber = new subscriber($courseid);

$navbartext = participant::get_fullname($userid);
$PAGE->navbar->add($navbartext);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_title($COURSE->shortname . ': ' . $title, 'local_booking');
$PAGE->set_heading($title, 'local_booking');
$PAGE->add_body_class('path-local-booking');

$renderer = $PAGE->get_renderer('local_booking');

echo $OUTPUT->header();
echo $renderer->start_layout();

// check for exam processing action
if ($reporttype == 'evalform') {
    $tertiarynavadditional = [];

    // show evaluation form message
    $student = new student($COURSE->subscriber, $userid);
    $examinerid = $student->get_grade($COURSE->subscriber->get_graduation_exercise())->usermodified;
    $exerciseid = $COURSE->subscriber->get_graduation_exercise();
    $fname = mb_substr($student->get_name(false, 'first'), 0, 1);
    $lname = mb_substr($student->get_name(false, 'last'), 0, 1);
    $tertiarynavadditional['skilltestgradeurl'] = $CFG->httpswwwroot . "/mod/assign/view.php?action=grading&id=$exerciseid&tifirst=$fname&tilast=$lname";
    $tertiarynavadditional['isexaminer'] = $examinerid == $USER->id;
    $tertiarynavadditional['regenerate'] = $action == 'generate';
    $evalformmsgbar = new manage_action_bar($PAGE, 'evalform', $tertiarynavadditional);
    echo $renderer->render_tertiary_navigation($evalformmsgbar);

    // show evaluation form edit button menu for the examiner only
    if ($examinerid == $USER->id) {
        // prepare the tertiary navigation menu section
        $tertiarynavadditional['courseid'] = $courseid;
        $tertiarynavadditional['userid'] = $userid;
        $tertiarynavadditional['exeid'] = $COURSE->subscriber->get_graduation_exercise();
        $tertiarynavadditional['vatsimemail'] = $COURSE->subscriber->get_booking_config('vatsimcertemail');
        $tertiarynavadditional['skilltesturl'] = '/local/booking/assign.php';
        $tertiarynavadditional['generateformurl'] = '/local/booking/certify.php';
        $tertiarynavadditional['sendformurl'] = '/local/booking/certify.php';

        // output evaluation form examiner action bar
        $examineractionbar = new manage_action_bar($PAGE, 'examiner', $tertiarynavadditional);
        echo $renderer->render_tertiary_navigation($examineractionbar);
    }
}

// report section
echo html_writer::start_tag('div', array('class'=>'heightcontainer'));

// show loading icon
echo html_writer::script('document.onreadystatechange = function () {
    var state = document.readyState
    if (state == "interactive") {
        document.getElementById("report").style.visibility="hidden";
    } else if (state == "complete") {
        setTimeout(function(){
            document.getElementById("loadingicon").remove();
            document.getElementById("report").style.visibility="visible";
        },1000);
    }
  }
');
echo html_writer::start_tag('div', array('id'=>'loadingicon', 'style'=>'height: 500px; text-align: center; line-height:150px;'));
echo html_writer::start_tag('i', array('class'=>'fa fa-circle-o-notch fa-spin', 'style'=>'font-size:48px', 'title'=>'Loading', 'aria-label'=>'Loading'));
echo html_writer::end_tag('i');
echo html_writer::end_tag('div');

// embed the pdf report
$reporturl = new moodle_url('/local/booking/pdfwriter.php');
$reporturl->param('courseid', $courseid);
$reporturl->param('userid', $userid);
$reporturl->param('report', $reporttype);

echo html_writer::tag('embed', '', array('id'=>'report', 'src'=>$reporturl->out(false), 'type'=>'application/pdf', 'height'=>'1200', 'width'=>'100%'));

echo html_writer::end_tag('div');
echo $renderer->complete_layout();
echo $OUTPUT->footer();
