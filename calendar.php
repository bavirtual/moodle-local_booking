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

use local_booking\local\message\calendar_event;
use local_booking\local\message\notification;

// Get URL parameters.
$courseid   = optional_param('id', 0, PARAM_INT);
$coursename = optional_param('name', '', PARAM_TEXT);
$exerciseid = optional_param('extid', 0, PARAM_INT);
$instructorid = optional_param('inst', 0, PARAM_INT);
$studentid  = optional_param('std', 0, PARAM_INT);
$requester  = optional_param('req', 'i', PARAM_TEXT);
$eventstart = optional_param('tstart', 0, PARAM_INT);
$eventend   = optional_param('tend', 0, PARAM_INT);
$action     = optional_param('action', 'i', PARAM_TEXT);
$code       = optional_param('code', '', PARAM_RAW);
$state      = optional_param('state', '', PARAM_RAW);

// check for a redirect from authentication provider
$context = context_course::instance($courseid);

require_login($courseid, false);
require_capability('local/booking:availabilityview', $context);

$eventdata = notification::get_notification_data(
    $courseid,
    $coursename,
    $instructorid,
    $studentid,
    $exerciseid,
    $eventstart,
    $eventend,
    $requester);

$eventdata->courseid    = $courseid;
$eventdata->instructorid= $instructorid;
$eventdata->studentid   = $studentid;
$eventdata->exerciseid  = $exerciseid;
$eventdata->sessionend  = $eventdata->sessionend + 60*60;  // add an hour to the end
$eventdata->venue       = get_string('sessionvenue', 'local_booking');
$eventdata->redirecturi = $CFG->httpswwwroot . '/local/booking/calendar.php?action=g&id=' . $courseid . '&name=' . $coursename .
    '&extid=' . $exerciseid . '&inst=' . $instructorid . '&std=' . $studentid . '&req=' . $requester . '&tstart=' . $eventstart . '&tend=' . $eventend;

calendar_event::set_event_content($eventdata, $requester);

// Process the action (i = download ics file, g = add to Google calendar, l = add to Windows Live calendar)
switch ($action) {
    case 'i':
        calendar_event::download_ics($eventdata);
        break;
    case 'g':
        calendar_event::add_to_google_calendar($eventdata, $code);
        break;
    case 'l':
        calendar_event::add_to_live_calendar($eventdata, $code, $state);
        break;
}
