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
 * Redirects calendar posting to various calendar providers.
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

$courseid = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$type = required_param('type', PARAM_ALPHA);
$name = required_param('name', PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);
$instid = required_param('instid', PARAM_INT);
$stdid = required_param('stdid', PARAM_INT);
$start = required_param('start', PARAM_INT);
$end = required_param('end', PARAM_INT);
$extendendtime = empty(optional_param('oauth2code', null, PARAM_RAW));

$course = get_course($courseid);
$context = context_course::instance($courseid);

require_login($course, false);
require_capability('local/booking:availabilityview', $context);

// Only the booking participants (or an instructor) may add this event.
if ($USER->id != $userid && $USER->id != $instid && $USER->id != $stdid
        && !has_capability('local/booking:view', $context)) {
    throw new required_capability_exception($context, 'local/booking:view', 'nopermissions', '');
}

$subscriber = get_course_subscriber_context('/local/booking/availability.php', $courseid, true);

$eventinfo = (object) [
    'type' => $type,
    'userid' => $userid,
    'id' => $courseid,
    'name' => $name,
    'cmid' => $cmid,
    'instid' => $instid,
    'stdid' => $stdid,
    'start' => $start,
    'end' => $end,
];

$event = new event($eventinfo, $extendendtime);

if ($event->type == 'ics') {
    $event->download();
} else {
    $calendar = calendar_helper::get_calendar($event->type);
    $calendar->add($event);
}
