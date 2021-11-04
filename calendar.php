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

// get URL parameters.
$code = optional_param('code', '', PARAM_RAW);
$state = optional_param('state', '', PARAM_RAW);

// evaluate if the state has json query parameters
if (empty($state)) {
    // get event related URL parameters.
    $eventdata = notification::get_notification_data($_GET);
    $action = optional_param('action', 'i', PARAM_TEXT);
}
else {
    $decodedstate = json_decode(calendar_event::base64_url_decode($state), true);
    // get event related URL parameters.
    $eventdata = notification::get_notification_data($decodedstate);
    $action = $decodedstate['action'];
}
// additional required event data properties
$eventdata->venue       = get_string('sessionvenue', 'local_booking');
$eventdata->redirecturi = $CFG->httpswwwroot . '/local/booking/calendar.php';
$eventdata->sessionend  = $eventdata->sessionend + (60*30);  // add an hour to the event end time
$eventdata->statestring = calendar_event::get_state_string($eventdata, $action);

// Process the action (i = download ics file, g = add to Google calendar, l = add to Windows Live calendar)
switch ($action) {
    case 'i':
        calendar_event::download_ics($eventdata);
        break;
    case 'g':
        calendar_event::add_to_google_calendar($eventdata, $code);
        redirect($CFG->httpswwwroot);
        break;
    case 'l':
        calendar_event::add_to_live_calendar($eventdata, $code, $state);
        break;
}
