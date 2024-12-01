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

use local_booking\local\participant\entities\participant;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->libdir . '/google/src/Google/autoload.php');

/**
 * Class for adding session booking calendar events.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event {

    /**
     * @var string $type The type of event (Google, Microsoft, Moodle)
     */
    public $type;

    /**
     * @var int $userid The user id the event belongs to
     */
    public $userid;

    /**
     * @var int $courseid The course id associated with the event
     */
    public $courseid;

    /**
     * @var string $coursename The course name associated with the event
     */
    public $coursename;

    /**
     * @var int $instructorid The instructor id associated with the event
     */
    public $instructorid;

    /**
     * @var participant $instructor The instructor object associated with the event
     */
    public $instructor;

    /**
     * @var int $studentid The student id associated with the event
     */
    public $studentid;

    /**
     * @var participant $student The student object associated with the event
     */
    public $student;

    /**
     * @var int $exerciseid The exercise id associated with the event
     */
    public $exerciseid;

    /**
     * @var string $exercise The exercise name associated with the event
     */
    public $exercise;

    /**
     * @var string $name The event name
     */
    public $name;

    /**
     * @var string $body The event body
     */
    public $body;

    /**
     * @var int $start The event start time (timestamp)
     */
    public $start;

    /**
     * @var int $end The event end time (timestamp)
     */
    public $end;

    /**
     * @var \DateTime $startDateTime The event start datetime (DateTime)
     */
    public $startDateTime;

    /**
     * @var \DateTime $endDateTime The event end datetime (DateTime)
     */
    public $endDateTime;

    /**
     * @var string $sessiondate The event date text
     */
    public $sessiondate;

    /**
     * Constructor.
     *
     * @param \stdClass $eventinfo  The event data object
     * @param bool      $extend     Whether to extend the end time by an hour
     */
    public function __construct(\stdClass $eventinfo, bool $extend = true) {
        global $COURSE;

        // parse parameters if exists
        if (!empty($eventinfo)) {

            $this->type         = $eventinfo->type ?: 'moodle';
            $this->userid       = $eventinfo->userid;
            $this->courseid     = $eventinfo->id;
            $this->coursename   = $eventinfo->name;
            $this->exerciseid   = $eventinfo->cmid;
            $this->exercise     = $COURSE->subscriber->get_exercise_name($eventinfo->cmid);
            $this->instructorid = $eventinfo->instid;
            $this->instructor   = participant::get_fullname($eventinfo->instid);
            $this->studentid    = $eventinfo->stdid;
            $this->student      = participant::get_fullname($eventinfo->stdid);
            $this->start        = $eventinfo->start;
            $this->end          = $eventinfo->end + ($extend ? : 0); // TODO: 1 hr addition to end time is needed as the slot end time is incorrect

            // get DateTime objects
            $this->startDateTime= new \DateTime('@' . $this->start);
            $this->endDateTime  = new \DateTime('@' . $this->end);
            $this->sessiondate  = $this->startDateTime->format('l M j \a\t H:i \z\u\l\u');

            // set event subject and body based on the instructor/student and the HTML/Plain text format
            $this->name = calendar_helper::get_event_content('subject', $this, $this->type == 'ics' ? 'text' : 'html');
            $this->body = calendar_helper::get_event_content('body', $this, $this->type == 'ics' ? 'text' : 'html');
        }

    }

    /**
     * Create an event based on its type.
     *
     * @return bool  $result The event creation result where applicable
     */
    public function download(string $format = 'ics') {

        switch ($format) {

            case 'ics':

                $ato = get_config('local_booking', 'atoname');
                $location = get_string('sessionvenue', 'local_booking');
                $start = date('Ymd', $this->start) . 'T' . date('His', $this->start) . 'Z';
                $end = date('Ymd', $this->start) . 'T' . date('His', $this->end) . 'Z';
                $atoslug = strtolower(str_replace(array(' ', "'", '.'), array('_', '', ''), $ato));
                $calfile = $atoslug . '_' . $this->coursename . '_' . $this->exerciseid;

                header('Content-Type: text/Calendar;charset=utf-8');
                header('Content-Disposition: inline; filename=' . $calfile . '.ics');
                echo "BEGIN:VCALENDAR\n";
                echo "VERSION:2.0\n";
                echo "PRODID:-//{$ato}//NONSGML {$this->name}//EN\n";
                echo "METHOD:REQUEST\n"; // requied by Outlook
                echo "BEGIN:VEVENT\n";
                echo "UID:".date('Ymd') . 'T' . date('His') . "-" . rand() . "-" . $atoslug . "\n"; // required by Outlook
                echo "DTSTAMP:".date('Ymd').'T'.date('His')."\n"; // required by Outlook
                echo "DTSTART:{$start}\n";
                echo "DTEND:{$end}\n";
                echo "LOCATION:{$location}\n";
                echo "SUMMARY:{$this->name}\n";
                echo "DESCRIPTION: {$this->body}\n";
                echo "END:VEVENT\n";
                echo "END:VCALENDAR\n";

                break;
        }
    }
}