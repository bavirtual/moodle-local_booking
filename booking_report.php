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
 * Assignment feedback redirect
 * Clears preset filters and redirects to correct exercise
 * for instructor provided feedback submission
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (captainmoose)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use local_booking\exporters\dashboard_student_stats_exporter;
use local_booking\local\session\entities\booking;

// Get parameters
$bookingid = required_param('bookingid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

// Security checks
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('local/booking:view', $context);

// define session booking plugin subscriber globally
$subscriber = get_course_subscriber_context($url->out(false), $courseid);

// Set up the page
$PAGE->set_url(new moodle_url('/local/booking/booking_report.php', [
    'bookingid' => $bookingid,
    'courseid' => $courseid
    ]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('checklistgradingreport', 'local_booking'));
$PAGE->set_heading(get_string('checklistgradingreport', 'local_booking'));
$PAGE->set_pagelayout('standard');

// Add custom body class for scoped styling
$PAGE->add_body_class('local-booking-report');

// Add the custom CSS for this page only
$PAGE->requires->css('/local/booking/styles/stats_report.css');

// Get booking data - implement these functions in your lib.php
$booking = new booking($bookingid);
$student = $subscriber->get_student($studentid);
$sessions = $student->get_exercise_grades();
$competencies = get_student_competencies($booking->studentid);
$signoffs = get_student_signoffs($booking->studentid);

// Create exporter
$exporter = new dashboard_student_stats_exporter([
    'bookingid' => $bookingid,
    'courseid' => $courseid,
    'studentid' => $student->id,
    'lastupdated' => userdate(time(), '%d %b %Y'),
], [
    'context' => $context,
    'student' => $student,
    'booking' => $booking,
    'sessions' => $sessions,
    'competencies' => $competencies,
    'signoffs' => $signoffs,
]);

$data = $exporter->export($OUTPUT);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_booking/dashboard_booking_student_stats', $data);
echo $OUTPUT->footer();
