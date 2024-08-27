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
 * Grading observers.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @category   event
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');

/**
 * Group observers class to listen to graded assignments
 * for clearing previously posted student availability.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @category   event handler
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 use local_booking\local\participant\entities\student;
 use local_booking\local\session\entities\booking;

class grading_observers {

    /**
     * A submission has been graded.
     *
     * @param \mode\assign\submission_graded $event The event.
     * @return void
     */
    public static function submission_graded($event) {

        $courseid = $event->courseid;
        $studentid = $event->relateduserid;
        $exerciseid = $event->contextinstanceid;

        // Respond to submission graded events by deactivating the active booking.
        $booking = new booking(0, $courseid, $studentid, $exerciseid);
        $booking->load();

        // update the booking status from active to inactive
        if ($booking->active())
            $booking->deactivate();

        // revoke 'Keep Active' status
        $groupid = groups_get_group_by_name($courseid, LOCAL_BOOKING_KEEPACTIVEGROUP);
        if (groups_is_member($groupid, $studentid)) {
            groups_remove_member($groupid, $studentid);
        }

    }
}
