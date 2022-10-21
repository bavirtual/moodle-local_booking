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

require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Class for inserting calendar events
 * in Moodle Calendar for booked sessions.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle_calendar implements calendar_interface {

    /**
     * Create an event based on its type.
     *
     * @param event $event  The event to add
     * @return bool $result The event creation result where applicable
     */
    public function add(event $event) {

        // create an event to create a moodle calendar event
        $moodleevent = new \stdClass();
        $moodleevent->userid = $event->userid;
        $moodleevent->type = CALENDAR_EVENT_TYPE_STANDARD; // This is used for events we only want to display on the calendar, and are not needed on the block_myoverview.
        $moodleevent->name = substr($event->name,0,20);
        $moodleevent->description = array('format'=>FORMAT_HTML, 'text'=>$event->body);
        $moodleevent->format = FORMAT_HTML;
        $moodleevent->timestart = $event->start;
        $moodleevent->visible = instance_is_visible('scorm', $event->exercise);
        $moodleevent->timeduration = $event->end - $event->start;

        $result = \calendar_event::create($moodleevent, false);
        return !empty($result);
    }
}