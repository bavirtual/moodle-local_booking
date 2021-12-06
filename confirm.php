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

list($result, $time, $week) = confirm_booking($courseid, $instructorid, $studentid, $exerciseid);

if ($result) {
    // redirect
    $url = new moodle_url('/local/booking/availability.php', array(
        'courseid'    => $courseid,
        'userid'    => $studentid,
        'time'      => $time,
        'week'      => $week,
    ));

    $PAGE->set_url($url);
    redirect($url);
}
