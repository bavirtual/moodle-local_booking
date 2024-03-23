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

use local_booking\local\service\google_calendar_rest;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for inserting calendar events
 * in Google Calendar for booked sessions.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google_calendar implements calendar_interface {

    /**
     * Google calendar URL
     */
    const GOOGLE_CALENDAR_URL = 'https://calendar.google.com/calendar/u/0/r/week/';

    /**
     * Create a Google event.
     *
     * @param event $event  The event to add
     * @return bool  $result The event creation result where applicable
     */
    public function add(event $event) {

        if ($client = calendar_helper::get_client($event, 'google')) {

            // set event parameters
            $params = [
                'summary'    => $event->name,
                'description'=> $event->body,
                'start'      => json_encode(array('dateTime'=>$event->startDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
                'end'        => json_encode(array('dateTime'=>$event->endDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
            ];

            // set event raw parameters
            $rawparams = $params;
            $rawparams['start'] = array('dateTime'=>$event->startDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC');
            $rawparams['end']   = array('dateTime'=>$event->endDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC');

            // call the Google calendar rest api
            try {
                $service = new google_calendar_rest($client);
                $result = $service->call('add', $params, json_encode($rawparams));
            } catch (\Exception $e) {
                throw $e;
            }

            // redirect to Google calendar upon successful creation of a calendar event
            if (!empty($result)) {
                redirect(new \moodle_url(self::GOOGLE_CALENDAR_URL . $event->startDateTime->format('Y\/m\/d')));
            }
        } else {
            throw new \Exception(get_string('errorgooglecreateevent', 'local_booking'));
        }
    }
}