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
 * Confirms a student; the confirmation is performed by the student
 * and a notification email is sent to the instructor.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\message\notification;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$courseid     = required_param('courseid', PARAM_INT);
$exerciseid   = required_param('exeid', PARAM_INT);
$studentid    = required_param('userid', PARAM_INT);
$instructorid = required_param('insid', PARAM_INT);
$confirm      = optional_param('confirm', 0, PARAM_BOOL);

$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:availabilityview', $context);

if ($studentid != $USER->id) {
    throw new moodle_exception('errorconfirmownbooking', 'local_booking');
}

$urlparams = [
    'courseid' => $courseid,
    'exeid' => $exerciseid,
    'userid' => $studentid,
    'insid' => $instructorid,
];
$PAGE->set_url(new moodle_url('/local/booking/confirm.php', $urlparams));
$PAGE->set_context($context);

// Email confirmation links land here without a sesskey. Show a confirm step first.
if (!$confirm) {
    $confirmurl = new moodle_url('/local/booking/confirm.php', $urlparams + [
        'confirm' => 1,
        'sesskey' => sesskey(),
    ]);
    $cancelurl = new moodle_url('/local/booking/availability.php', ['courseid' => $courseid]);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('bookingconfirmask', 'local_booking'), $confirmurl, $cancelurl);
    echo $OUTPUT->footer();
    die;
}

require_sesskey();

$result = -1;
$time = time();
$week = (int) date('W', time());

$url = new moodle_url('/local/booking/availability.php', [
    'courseid'  => $courseid,
    'userid'    => $studentid,
    'time'      => $time,
    'week'      => $week,
    'confirm'   => true,
]);
$subscriber = get_course_subscriber_context($url->out(false), $courseid);

$booking = new booking(0, $courseid, $studentid, $exerciseid);
$booking->load();

$strdata = [];
if (!empty($booking->get_id())) {
    $sessiondatetime = new DateTime('@' . ($booking->get_slot())->get_starttime());
    $sessiondatetimestr = $sessiondatetime->format('D M j\, H:i');
    $strdata = [
        'exercise'  => $subscriber->get_exercise($exerciseid)->name,
        'instructor'=> student::get_fullname($instructorid),
        'status'    => ucwords(get_string('statusbooked', 'local_booking')),
        'sessiondate'=> $sessiondatetimestr,
    ];
    if ($booking->confirm(get_string('bookingconfirmmsg', 'local_booking', $strdata))) {
        $message = new notification($subscriber);
        $result = $message->send_instructor_notification($studentid, $exerciseid, $sessiondatetime, $instructorid);
    }
    $time = ($booking->get_slot())->get_starttime();
    $week = ($booking->get_slot())->get_week();
}

if ($result == 1) {
    \core\notification::SUCCESS(get_string('bookingconfirmsuccess', 'local_booking', $strdata));
} else if ($result == 0) {
    \core\notification::ERROR(get_string('bookingconfirmunable', 'local_booking'));
} else if ($result == -1) {
    \core\notification::SUCCESS(get_string('nobookingtoconfirm', 'local_booking'));
}

redirect($url);
