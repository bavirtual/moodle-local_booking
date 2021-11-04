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
 * Calendar event management
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\message;

use local_booking\local\service\google_calendar_api;
use local_booking\local\service\live_calendar_api;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Class for adding session booking calendar events.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calendar_event{

    /**
     * Output an 'ics' calendar event file for download
     * with the booking session event information.
     *
     * @param object $eventdata The object for the event data
     */
    public static function download_ics(object $eventdata) {
        calendar_event::set_event_content($eventdata);
        // get event details
        $ato = get_booking_config('ATO');
        $location = $eventdata->venue;
        $start = date('Ymd', $eventdata->sessionstart) . 'T' . date('His', $eventdata->sessionstart) . 'Z';
        $end = date('Ymd', $eventdata->sessionend) . 'T' . date('His', $eventdata->sessionend) . 'Z';
        $atoslug = strtolower(str_replace(array(' ', "'", '.'), array('_', '', ''), $ato));
        $calfile = $atoslug . '_' . $eventdata->coursename . '_' . $eventdata->exerciseid;

        header('Content-Type: text/Calendar;charset=utf-8');
        header('Content-Disposition: inline; filename=' . $calfile . '.ics');
        echo "BEGIN:VCALENDAR\n";
        echo "VERSION:2.0\n";
        echo "PRODID:-//{$ato}//NONSGML {$eventdata->eventname}//EN\n";
        echo "METHOD:REQUEST\n"; // requied by Outlook
        echo "BEGIN:VEVENT\n";
        echo "UID:".date('Ymd') . 'T' . date('His') . "-" . rand() . "-" . $atoslug . "\n"; // required by Outlook
        echo "DTSTAMP:".date('Ymd').'T'.date('His')."\n"; // required by Outlook
        echo "DTSTART:{$start}\n";
        echo "DTEND:{$end}\n";
        echo "LOCATION:{$location}\n";
        echo "SUMMARY:{$eventdata->eventname}\n";
        echo "DESCRIPTION: {$eventdata->eventdescription}\n";
        echo "END:VEVENT\n";
        echo "END:VCALENDAR\n";
    }

    /**
     * Add an event to Moodle calendar.
     *
     * @param object $eventdata The object for the event data
     */
    public static function add_to_moodle_calendar(object $eventdata, string $requester = 'i') {
        $eventdata->requester = $requester;
        calendar_event::set_event_content($eventdata);
        // create an event to create a moodle calendar event
        $event = new \stdClass();
        $event->userid = ($eventdata->requester == 'i' ? $eventdata->instructorid : $eventdata->studentid);
        $event->type = CALENDAR_EVENT_TYPE_STANDARD; // This is used for events we only want to display on the calendar, and are not needed on the block_myoverview.
        $event->name = substr($eventdata->eventname,0,20);
        $event->description = array('format'=>FORMAT_HTML, 'text'=>$eventdata->eventdescription);
        $event->format = FORMAT_HTML;
        $event->timestart = $eventdata->sessionstart;
        $event->visible = instance_is_visible('scorm', $eventdata->exercise);
        $event->timeduration = ($eventdata->sessionend + (60*60)) - $eventdata->sessionstart; // add an hour to the end time

        \calendar_event::create($event);
    }

    /**
     * Add a Google calendar event with
     * the booking session event information.
     *
     * @param object $eventdata The event's data.
     * @param string $code      The code required to get a token to create the event.
     * @return int   $eventid   The calendar event id.
     */
    public static function add_to_google_calendar(object $eventdata, string $code) {
        calendar_event::set_event_content($eventdata);
        // get the code if it's not there otherwise create the event
        if (empty($code)) {
            redirect(google_calendar_api::get_login_uri($eventdata->redirecturi, self::base64_url_encode($eventdata->statestring)));
        }
        else {
            // Get access token
            $token = google_calendar_api::get_token($eventdata->redirecturi, $code);

            // Create event on primary calendar
            $eventdata->calendarid = 'primary';
            $eventid = google_calendar_api::add_event($eventdata, $token);

            return $eventid;
        }
    }

    /**
     * Add a Windows Live calendar event with
     * the booking session event information.
     *
     * @param object $eventdata The event's data.
     * @param string $code      The code required to get a token to create the event.
     * @param string $code      The code required to get a token to create the event.
     * @return int   $eventid   The calendar event id.
     */
    public static function add_to_live_calendar(object $eventdata, string $code) {
        calendar_event::set_event_content($eventdata);
        // get the code if it's not there otherwise create the event
        if (empty($code)) {
            redirect(live_calendar_api::get_login_uri($eventdata->redirecturi, self::base64_url_encode($eventdata->statestring)));
        }
        else {
            $token = live_calendar_api::get_token($eventdata->redirecturi, $code);
            $eventid = live_calendar_api::add_event($eventdata, $token);

            return $eventid;
        }
    }

    /**
     * Sets the event content for
     * event name and event description.
     *
     * @param object $eventdata The event's data.
     * @param string $requester The event calendar requester.
     */
    public static function set_event_content(object $eventdata) {
        // identify the requester being instructor 'i' or student 's' to customize the event description
        if ($eventdata->requester == 'i') {
            $eventdata->eventname = get_string('emailconfirmsubject', 'local_booking', $eventdata);
            $eventdata->eventdescription = get_string('emailconfirmhtml', 'local_booking', $eventdata);
        }
        elseif ($eventdata->requester == 's') {
            $eventdata->eventname = get_string('emailnotify', 'local_booking', $eventdata);
            $eventdata->eventdescription = get_string('emailnotifyhtml', 'local_booking', $eventdata);
        }
    }

    /**
     * Returns a json string containing
     * query parameters embeded in the state.
     *
     * @param object $eventdata The event data object containing query parameters.
     * @return string           The json string containing query parameters.
     */
    public static function get_state_string(object $eventdata, string $action) {
        $jsonstate = '{"action":"' . $action . '",
            "id":' . $eventdata->courseid . ',
            "name":"' . $eventdata->coursename . '",
            "extid":' . $eventdata->exerciseid . ',
            "inst":' . $eventdata->instructorid . ',
            "std":' . $eventdata->studentid . ',
            "req":"' . $eventdata->requester . '",
            "tstart":' . $eventdata->sessionstart . ',
            "tend":' . $eventdata->sessionend . '}';

        return $jsonstate;
    }

    /**
     * Replaces url query parameters to be
     * encoded in the state paramter.
     *
     * @param string $statestring   The url query string to be encoded.
     * @return string               The converted and encoded string.
     */
    public static function base64_url_encode(string $statestring) {
        return strtr(base64_encode($statestring), '+/=', '-_,');
    }

    /**
     * Decodes the encoded state string and converts it
     * into a query string.
     *
     * @param string $statestring   The encoded state string to be decoded.
     * @return string               The url query string encoded.
     */
    public static function base64_url_decode(string $statestring) {
        return base64_decode(strtr($statestring, '-_,', '+/='));
    }
}