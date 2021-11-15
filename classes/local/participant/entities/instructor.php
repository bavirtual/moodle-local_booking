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
 * Class representing all instructor course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\entities;

use DateTime;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\entities\booking;

class instructor extends participant {

    /**
     * Constructor.
     *
     * @param int $courseid The course id.
     * @param int $instructorid The instructor id.
     */
    public function __construct(int $courseid, int $instructorid) {
        parent::__construct($courseid, $instructorid);
        $this->is_student = false;
    }

    /**
     * Get students assigned to an instructor.
     *
     * @return {Object}[]   Array of students.
     */
    public function get_assigned_students() {
        $assignedstudents = [];
        $studentrecs = $this->vault->get_assigned_students($this->courseid, $this->userid);
        foreach ($studentrecs as $studentrec) {
            $student = new student($this->courseid, $studentrec->userid);
            $student->populate($studentrec);
            $assignedstudents[$student->userid] = $student;
        }
        return $assignedstudents;
    }

    /**
     * Get an instructor's active bookings
     *
     * @return booking[] An array of bookings.
     */
    public function get_bookings(bool $oldestfirst = false) {
        $bookings = [];

        $bookingobjs = booking_vault::get_bookings($this->userid, $oldestfirst);
        foreach ($bookingobjs as $bookingobj) {
            $booking = new booking();
            $booking->load($bookingobj);
            $bookings[] = $booking;
        }
        return $bookings;
    }
}