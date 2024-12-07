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
 * and an notification email is sent to the instructor.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_booking\local\message\notification;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;
use local_booking\local\subscriber\entities\subscriber;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Get URL parameters.
$courseid     = optional_param('courseid', 0, PARAM_INT);
$exerciseid   = optional_param('exeid', 0, PARAM_INT);
$studentid    = optional_param('userid', 0, PARAM_INT);
$instructorid = optional_param('insid', 0, PARAM_INT);
$context = context_course::instance($courseid);

require_login($courseid, false);
require_capability('local/booking:availabilityview', $context);

$COURSE->subscriber = new subscriber($courseid);
$result = -1;
$time = time();
$week = (int) date('W', time());

// Get the student slot
$booking = new booking(0, $courseid, $studentid, $exerciseid);
$booking->load();

if (!empty($booking->get_id())) {
    // update the booking by the instructor.
    $sessiondatetime = (new DateTime('@' . ($booking->get_slot())->get_starttime()))->format('D M j\, H:i');
    $strdata = [
        'exercise'  => $COURSE->subscriber->get_exercise($exerciseid)->name,
        'instructor'=> student::get_fullname($instructorid),
        'status'    => ucwords(get_string('statusbooked', 'local_booking')),
        'sessiondate'=> $sessiondatetime
    ];
    if ($booking->confirm(get_string('bookingconfirmmsg', 'local_booking', $strdata))) {
        // notify the instructor of the student's confirmation
        $message = new notification($COURSE->subscriber);
        $result = $message->send_instructor_notification($studentid, $exerciseid, $sessiondatetime, $instructorid);
    }
    $time = ($booking->get_slot())->get_starttime();
    $week = ($booking->get_slot())->get_week();
}

if ($result == 1) {
    \core\notification::SUCCESS(get_string('bookingconfirmsuccess', 'local_booking', $strdata));
} elseif ($result == 0) {
    \core\notification::ERROR(get_string('bookingconfirmunable', 'local_booking'));
} elseif ($result == -1) {
    \core\notification::SUCCESS(get_string('nobookingtoconfirm', 'local_booking'));
}

if ($result) {
    // redirect
    $url = new moodle_url('/local/booking/availability.php', array(
        'courseid'  => $courseid,
        'userid'    => $studentid,
        'time'      => $time,
        'week'      => $week,
        'confirm'   => true
    ));

    $PAGE->set_url($url);
    redirect($url);
}
