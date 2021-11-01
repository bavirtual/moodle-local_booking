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
    public static function add_to_moodle_calendar(object $eventdata) {
        // send an event to create a moodle calendar event
    }

    /**
     * Add a Google calendar event with
     * the booking session event information.
     *
     * @param object $eventdata The event's data.
     * @param string $code      The code required to get a token to create the event.
     * @return int $eventid     The code required created event id.
     */
    public static function add_to_google_calendar(object $eventdata, string $code) {
        // get the code if it's not there otherwise create the event
        if (empty($code)) {
            redirect(google_calendar_api::get_login_uri($eventdata->redirecturi));
        }
        else {
            // Get access token
            $token = google_calendar_api::get_token($eventdata->redirecturi, $code);

            // Get user calendar timezone
            $eventdata->calendarid = 'primary';
            $eventdata->timezone = google_calendar_api::get_user_timezone($token);

            // Create event on primary calendar
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
     * @return bool             The event message id.
     */
    public static function add_to_live_calendar(object $eventdata, string $code, int $passedstate) {
        // set a random 5 digit number for the state in user preferences
        $state = get_user_preferences('local_booking_liveauthstate');
        if (empty($state)) {
            set_user_preference('local_booking_showlocaltime', mt_rand(10000, 99999));
        }

        // get the code if it's not there otherwise create the event
        if (empty($code)) {
            redirect(live_calendar_api::get_login_uri($eventdata->redirecturi, $state));
        }
        elseif ($passedstate == $state) {
            $token = live_calendar_api::get_token($eventdata->redirecturi, $code, $state);
            $eventsurl = get_booking_config('live_events_url');
            $startdatetime = (new \DateTime('@' . $eventdata->sessionstart))->format('Y-m-d\TH\:i\:s');
            $startdateend = (new \DateTime('@' . $eventdata->sessionend))->format('Y-m-d\TH\:i\:s');
            $location = $eventdata->venue;

            $data_json = '{
                "name": "'. $eventdata->eventname .'",
                "description": "'. $eventdata->eventdescription .'",
                "start_time": "' . $startdatetime . ':00",
                "end_time": "' . $startdateend . ':00",
                "location": "' . $location . '",
                "is_all_day_event": false,
                "availability": "busy",
                "visibility": "public"
            }';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $eventsurl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token,
            "Content-length: ".strlen($data_json))
            );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            return $result;
        }
    }

    /**
     * Sets the event content for
     * event name and event description.
     *
     * @param object $eventdata The event's data.
     * @param string $requester The event calendar requester.
     */
    public static function set_event_content(object $eventdata, string $requester) {
        // identify the requester being instructor 'i' or student 's' to customize the event description
        if ($requester == 'i') {
            $eventdata->eventname = get_string('emailnotify', 'local_booking', $eventdata);
            $eventdata->eventdescription = get_string('emailconfirmnmsg', 'local_booking', $eventdata);
        }
        elseif ($requester == 's') {
            get_string('emailconfirmsubject', 'local_booking', $eventdata);
            $eventdata->eventdescription = get_string('emailconfirmmsg', 'local_booking', $eventdata);
        }
    }
}