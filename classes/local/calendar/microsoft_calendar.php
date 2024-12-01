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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\calendar;

use local_booking\local\service\microsoft_calendar_rest;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for inserting calendar events
 * in Microsoft Calendar for booked sessions.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class microsoft_calendar implements calendar_interface {

    /**
     * Microsoft calendar scope
     */
    const MICROSOFT_CALENDAR_URL = 'https://outlook.live.com/calendar/0/view/month/';

    /**
     * Create a Microsoft Outlook Live event.
     *
     * @param event $event  The event to add
     * @return bool  $result The event creation result where applicable
     */
    public function add(event $event) {

        if ($client = calendar_helper::get_client($event, 'microsoft')) {

            // set event parameters
            $params = [
                'subject'   => $event->name,
                'body'      => json_encode(array('contentType'=>'HTML', 'content'=>strtr($event->body, '"', '\''))),
                'start'     => json_encode(array('dateTime'=>$event->startDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
                'end'       => json_encode(array('dateTime'=>$event->endDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC')),
                'location'  => json_encode(array('displayName'=>get_string('sessionvenue', 'local_booking')))
            ];

            $rawparams = [
                'subject'   => $event->name,
                'body'      => array('contentType'=>'HTML', 'content'=>strtr($event->body, '"', '\'')),
                'start'     => array('dateTime'=>$event->startDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC'),
                'end'       => array('dateTime'=>$event->endDateTime->format('Y-m-d\TH\:i\:s'),'timeZone'=>'UTC'),
                'location'  => array('displayName'=>get_string('sessionvenue', 'local_booking'))
            ];

            // call the Microsoft calendar rest api
            try {
                $service = new microsoft_calendar_rest($client);
                $result = $service->call('add', $params, json_encode($rawparams));
            } catch (\Exception $e) {
                throw $e;
            }

            // redirect to Microsoft calendar upon successful creation of a calendar event
            if (!empty($result)) {
                redirect(new \moodle_url(self::MICROSOFT_CALENDAR_URL . $event->startDateTime->format('Y\/m\/d')));
            }
        } else {
            throw new \Exception(get_string('erroroutlookcreateevent', 'local_booking'));
        }
    }
}