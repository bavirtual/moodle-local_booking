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

namespace local_booking\local\calendar;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for adding session booking calendar events.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface calendar_interface {

    // /**
    //  * Microsoft calendar scope
    //  */
    // const MICROSOFT_CALENDAR_URL = 'https://outlook.live.com/calendar/0/view/month/';

    /**
     * Create an event based on its type.
     *
     * @param event $event  The event to add
     * @return bool $result The event creation result where applicable
     */
    public function add(event $even);

    //     $result = true;

    //     switch ($this->eventinfo->type) {

    //         case 'ics':
    //             $this->download_ics();
    //             break;

    //         case 'moodle':
    //             $result = $this->moodle_calendar();
    //             break;

    //         case 'google':
    //             // $this->google_calendar();
    //             break;

    //         case 'microsoft':
    //             // $this->office365_calendar();
    //             break;
    //     }
    //     return $result;
    // }

    // /**
    //  * Output an 'ics' calendar event file for download
    //  * with the booking session event information.
    //  */
    // protected function download_ics() {

    //     $ato = get_booking_config('ato')->name;
    //     $location = get_string('sessionvenue', 'local_booking');
    //     $start = date('Ymd', $this->eventinfo->sessionstart) . 'T' . date('His', $this->eventinfo->sessionstart) . 'Z';
    //     $end = date('Ymd', $this->eventinfo->sessionend) . 'T' . date('His', $this->eventinfo->sessionend) . 'Z';
    //     $atoslug = strtolower(str_replace(array(' ', "'", '.'), array('_', '', ''), $ato));
    //     $calfile = $atoslug . '_' . $this->eventinfo->coursename . '_' . $this->eventinfo->exerciseid;

    //     header('Content-Type: text/Calendar;charset=utf-8');
    //     header('Content-Disposition: inline; filename=' . $calfile . '.ics');
    //     echo "BEGIN:VCALENDAR\n";
    //     echo "VERSION:2.0\n";
    //     echo "PRODID:-//{$ato}//NONSGML {$this->eventinfo->eventname}//EN\n";
    //     echo "METHOD:REQUEST\n"; // requied by Outlook
    //     echo "BEGIN:VEVENT\n";
    //     echo "UID:".date('Ymd') . 'T' . date('His') . "-" . rand() . "-" . $atoslug . "\n"; // required by Outlook
    //     echo "DTSTAMP:".date('Ymd').'T'.date('His')."\n"; // required by Outlook
    //     echo "DTSTART:{$start}\n";
    //     echo "DTEND:{$end}\n";
    //     echo "LOCATION:{$location}\n";
    //     echo "SUMMARY:{$this->eventinfo->eventname}\n";
    //     echo "DESCRIPTION: {$this->eventinfo->eventdesc}\n";
    //     echo "END:VEVENT\n";
    //     echo "END:VCALENDAR\n";

    // }

    // /**
    //  * Add an event to Moodle calendar.
    //  */
    // protected function moodle_calendar() {

    //     // create an event to create a moodle calendar event
    //     $event = new \stdClass();
    //     $event->userid = ($this->eventinfo->requester == 'i' ? $this->eventinfo->instructorid : $this->eventinfo->studentid);
    //     $event->type = CALENDAR_EVENT_TYPE_STANDARD; // This is used for events we only want to display on the calendar, and are not needed on the block_myoverview.
    //     $event->name = substr($this->eventinfo->eventname,0,20);
    //     $event->description = array('format'=>FORMAT_HTML, 'text'=>$this->eventinfo->eventdesc);
    //     $event->format = FORMAT_HTML;
    //     $event->timestart = $this->eventinfo->sessionstart;
    //     $event->visible = instance_is_visible('scorm', $this->eventinfo->exercise);
    //     $event->timeduration = $this->eventinfo->sessionend - $this->eventinfo->sessionstart;

    //     return !empty(\calendar_event::create($event, false));

    // }

    // /**
    //  * Add a Google calendar event with the booking session event information
    //  * using Google APIs Client Library included in Moodle.
    //  */
    // protected function google_calendar() {

    //     if ($this->enabled) {
    //         // get the code if it's not there otherwise create the event
    //         if (!$this->client->is_logged_in()) {
    //             redirect($this->client->get_login_url());
    //         } else {
    //             // construct calendar event details and process cURL.
    //             $startdatetime = new \DateTime('@' . $this->eventinfo->sessionstart);
    //             $startdateend = new \DateTime('@' . $this->eventinfo->sessionend);

    //             $params = [
    //                 'subject'   => $this->eventinfo->eventname,
    //                 'body'      => json_encode(array('contentType'=>'HTML', 'content'=>strtr($this->eventinfo->eventdesc, '"', '\''))),
    //                 'start'     => json_encode(array('dateTime'=>$startdatetime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
    //                 'end'       => json_encode(array('dateTime'=>$startdateend->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
    //                 'location'  => json_encode(array('displayName'=>get_string('sessionvenue', 'local_booking')))
    //             ];

    //             $rawparams = [
    //                 'subject'   => $this->eventinfo->eventname,
    //                 'body'      => array('contentType'=>'HTML', 'content'=>strtr($this->eventinfo->eventdesc, '"', '\'')),
    //                 'start'     => array('dateTime'=>$startdatetime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC'),
    //                 'end'       => array('dateTime'=>$startdateend->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC'),
    //                 'location'  => array('displayName'=>get_string('sessionvenue', 'local_booking'))
    //             ];

    //             // call the Microsoft calendar rest api
    //             try {
    //                 $service = new office365_calendar_rest($this->client);
    //                 $result = $service->call('add', $params, json_encode($rawparams));
    //             } catch (\Exception $e) {
    //                 throw $e;
    //             }

    //             // redirect to Microsoft calendar upon successful creation of a calendar event
    //             if (!empty($result))
    //                 redirect(new \moodle_url(self::MICROSOFT_CALENDAR_URL . $startdateend->format('Y\/m\/d')));
    //         }
    //     }

        // $redirecturi = new moodle_url(self::REDIRECTURL);

        // // get the Google client
        // $googleclient = new Google_Client();
        // $googleclient->setApplicationName('Moodle Session Booking');
        // $googleclient->setClientId($this->issuer->get('clientid'));
        // $googleclient->setClientSecret($this->issuer->get('clientsecret'));
        // $googleclient->setRedirectUri($redirecturi->out(false));
        // $googleclient->addScope($this->issuer->get('loginscopes'));
        // $googleclient->setState($this->redirecturl->out_as_local_url(false));

        // if ($this->enabled) {
        //     // get the authentication code if not available
        //     if (empty($this->eventinfo->code)) {
        //         redirect($this->client->get_login_url());
        //     } else {
        //         // authenticate the Google client to get the token from the code
        //         $googleclient->authenticate($this->eventinfo->code);

        //         // setup the Google calendar service
        //         $service = new Google_Service_Calendar($googleclient);
        //         $event = new Google_Service_Calendar_Event();
        //         $start = new Google_Service_Calendar_EventDateTime();
        //         $end = new Google_Service_Calendar_EventDateTime();

        //         // build the event data
        //         $eventstart = new \DateTime('@' . $this->eventinfo->sessionstart);
        //         $eventend = new \DateTime('@' . $this->eventinfo->sessionend);
        //         $start->dateTime = $eventstart->format('Y-m-d\TH\:i\:s');
        //         $start->timeZone = 'UTC';
        //         $end->dateTime = $eventend->format('Y-m-d\TH\:i\:s');
        //         $end->timeZone = 'UTC';
        //         $event->summary = $this->eventinfo->eventname;
        //         $event->description = $this->eventinfo->eventdesc;
        //         $event->setStart($start);
        //         $event->setEnd($end);

        //         // call the Google calendar service
        //         $optParams = array();
        //         $result = $service->events->insert('primary', $event, $optParams);

        //         // redirect to Google calendar upon successfully inserting the new Google calendar event
        //         if (!empty($result))
        //             redirect(new \moodle_url(self::GOOGLE_CALENDAR_URL . $eventstart->format('Y\/m\/d')));
        //     }
        // }
    // }

    /**
     * Add a Microsoft Office 365 Live 'Outlook' calendar event with
     * the booking session event information.
     */
    // protected function office365_calendar() {

    //     if ($this->enabled) {
    //         // get the code if it's not there otherwise create the event
    //         if (!$this->client->is_logged_in()) {
    //             redirect($this->client->get_login_url());
    //         } else {
    //             // construct calendar event details and process cURL.
    //             $startdatetime = new \DateTime('@' . $this->eventinfo->sessionstart);
    //             $startdateend = new \DateTime('@' . $this->eventinfo->sessionend);

    //             $params = [
    //                 'subject'   => $this->eventinfo->eventname,
    //                 'body'      => json_encode(array('contentType'=>'HTML', 'content'=>strtr($this->eventinfo->eventdesc, '"', '\''))),
    //                 'start'     => json_encode(array('dateTime'=>$startdatetime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
    //                 'end'       => json_encode(array('dateTime'=>$startdateend->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
    //                 'location'  => json_encode(array('displayName'=>get_string('sessionvenue', 'local_booking')))
    //             ];

    //             $rawparams = [
    //                 'subject'   => $this->eventinfo->eventname,
    //                 'body'      => array('contentType'=>'HTML', 'content'=>strtr($this->eventinfo->eventdesc, '"', '\'')),
    //                 'start'     => array('dateTime'=>$startdatetime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC'),
    //                 'end'       => array('dateTime'=>$startdateend->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC'),
    //                 'location'  => array('displayName'=>get_string('sessionvenue', 'local_booking'))
    //             ];

    //             // call the Microsoft calendar rest api
    //             try {
    //                 $service = new office365_calendar_rest($this->client);
    //                 $result = $service->call('add', $params, json_encode($rawparams));
    //             } catch (\Exception $e) {
    //                 throw $e;
    //             }

    //             // redirect to Microsoft calendar upon successful creation of a calendar event
    //             if (!empty($result))
    //                 redirect(new \moodle_url(self::MICROSOFT_CALENDAR_URL . $startdateend->format('Y\/m\/d')));
    //         }
    //     }
    // }
}