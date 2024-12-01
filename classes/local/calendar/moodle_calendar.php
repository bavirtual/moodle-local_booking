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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/calendar/lib.php');

use \local_booking\local\calendar\event;
use \local_booking\local\session\entities\booking;

/**
 * Class for inserting calendar events
 * in Moodle Calendar for booked sessions.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class moodle_calendar implements calendar_interface {

    /**
     * @var booking $booking The The booking assciated with the event
     */
    protected $booking;

    /**
     * Constructor.
     *
     * @param booking $booking The booking assciated with the event
     */
    public function __construct(booking $booking) {
        $this->booking = $booking;
    }

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

    /**
     * Create events for both instructor and student.
     *
     * @return bool $result The event creation result where applicable
     */
    public function add_events() {

        $instructorevent = $this->get_event($this->booking->get_instructorid());
        $result = $this->add($instructorevent);
        $studentrevent = $this->get_event($this->booking->get_studentid());
        return $result && $this->add($studentrevent);
    }

    /**
     * Deletes an event based on its type.
     *
     * @param event $event  The event to add
     * @return bool $result The event creation result where applicable
     */
    public function delete(event $event) {

        $events = \calendar_get_events($event->start, $event->end, $event->userid, false, $event->courseid);
        if (!empty($events)) {
            $eventid = array_values($events)[0]->id;
            $event = \calendar_event::load($eventid);
            $event->delete();
        }

        return;
    }

    /**
     * Delete instructor and student events
     *
     * @param event $event  The event to add
     * @return bool $result The event creation result where applicable
     */
    public function delete_events() {

        // create instructor calendar event
        $instructorevent = $this->get_event($this->booking->get_instructorid());
        $this->delete($instructorevent);

        // create student calendar event
        $studentevent = $this->get_event($this->booking->get_studentid());
        $this->delete($studentevent);

        return;
    }

    /**
     * Get the total sessions for a user.
     *
     * @param int     $userid  The event's owner user id
     * @return event  The event object
     */
    protected function get_event(int $usedid) {
        global $COURSE;

        // get the event information object
        $eventdata = (object) [
            'type'  => 'moodle',
            'userid'=> $usedid,
            'id'    => $this->booking->get_courseid(),
            'name'  => $COURSE->shortname,
            'cmid'  => $this->booking->get_exerciseid(),
            'instid'=> $this->booking->get_instructorid(),
            'stdid' => $this->booking->get_studentid(),
            'start' => $this->booking->get_slot()->get_starttime(),
            'end'   => $this->booking->get_slot()->get_endtime()
        ];

        return new event($eventdata);
    }
}