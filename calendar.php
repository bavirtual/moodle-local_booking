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
 * Redirects calendar posting to various calender providers
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use local_booking\local\calendar\event;
use local_booking\local\calendar\calendar_helper;
use local_booking\local\subscriber\entities\subscriber;


$courseid = optional_param('id', 0, PARAM_INT);
// TODO: end time should include the last hour
$extendendtime = empty(optional_param('oauth2code', null, PARAM_RAW));

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);

// define subscriber globally
if (empty($COURSE->subscriber)) {
    $COURSE->subscriber = new subscriber($courseid);
}

// get the event from the URL parameters.
$event = new event((object) $_GET, $extendendtime);

// add an event to the target provider denoted in type paramenter
if ($event->type == 'ics') {
    $event->download();
} else {
    $calendar = calendar_helper::get_calendar($event->type);
    $calendar->add($event);
}
