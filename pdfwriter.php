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
 * Exports a PDF file to the browser for reports.
 *  Mentor report
 *  Theory examination report
 *  Practical Examination report,
 *  Skills test ride form.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\report\pdf_report_mentor;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\report\pdf_report_theoryexam;
use local_booking\local\report\pdf_report_practicalexam;
use local_booking\local\report\pdf_report_recommendletter;

// Standard GPL and phpdocs
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir.'/pdflib.php');

// Set up the page.
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$userid = optional_param('userid', 0, PARAM_INT);
$reporttype = optional_param('report', 'mentor', PARAM_RAW);

$url = new moodle_url('/local/booking/view.php');
$url->param('courseid', $courseid);
$url->param('userid', $userid);
$url->param('report', $reporttype);

$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:view', $context);

$PAGE->set_url($url);

// header page information
if (empty($COURSE->subscriber))
    $COURSE->subscriber = new subscriber($courseid);

$student = $COURSE->subscriber->get_student($userid, true);

// create and output the pdf report
switch ($reporttype) {
    case 'theoryexam':
        $theoryexamreport = new pdf_report_theoryexam($COURSE->subscriber, $student);
        $theoryexamreport->Generate();
        break;
    case 'mentor':
        $mentorreport = new pdf_report_mentor($COURSE->subscriber, $student);
        $mentorreport->Generate();
        break;
    case 'practicalexam':
        $practicalreport = new pdf_report_practicalexam($COURSE->subscriber, $student);
        $practicalreport->Generate();
        break;
    case 'recommendation':
        $recommendationreport = new pdf_report_recommendletter($COURSE->subscriber, $student);
        $recommendationreport->Generate();
        break;
}

exit();
