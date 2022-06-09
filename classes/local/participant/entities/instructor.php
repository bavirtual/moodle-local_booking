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

use local_booking\local\subscriber\entities\subscriber;

class instructor extends participant {

    /**
     * Constructor.
     *
     * @param subscriber $course The subscribing course the student is enrolled in.
     * @param int $instructorid The instructor id.
     */
    public function __construct(subscriber $course, int $instructorid) {
        parent::__construct($course, $instructorid);
        $this->is_student = false;
    }

    /**
     * Get students assigned to an instructor.
     *
     * @return {Object}[]   Array of students.
     */
    public function get_assigned_students() {
        $assignedstudents = [];
        $studentrecs = $this->vault->get_assigned_students($this->course->get_id(), $this->userid);
        foreach ($studentrecs as $studentrec) {
            $student = $this->course->get_student($studentrec->userid);
            $assignedstudents[$student->userid] = $student;
        }
        return $assignedstudents;
    }

    /**
     * Check if the instructor is an examiner.
     *
     * @return bool Whether the instructor is an examiner or not.
     */
    public function is_examiner() {
        // get skill test context id
        $skilltestid = $this->course->get_graduation_exercise();
        $context = \context_module::instance($skilltestid); //contextid=116
        return has_capability('mod/assign:grade', $context, $this->userid);
    }
}