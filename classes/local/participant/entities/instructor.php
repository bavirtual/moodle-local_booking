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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\entities;

require_once($CFG->dirroot . '/group/lib.php');

use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\data_access\grading_vault;

class instructor extends participant {

    /**
     * Constructor.
     *
     * @param $course The subscribing course the student is enrolled in.
     * @param int $instructorid The instructor id.
     */
    public function __construct($course, int $instructorid) {
        parent::__construct($course, $instructorid);
        $this->is_student = false;
    }

    /**
     * Get a breakdown of conducted sessions count per exercise.
     *
     * @param  $exerciseid  Optional exercises id.
     * @return array   Array of sessions conducted count per module.
     */
    public function get_booked_sessions_count() {
        return booking_vault::get_user_total_booked_sessions($this->course->get_id(), $this->userid);
    }

    /**
     * Get a breakdown of conducted sessions count per exercise.
     *
     * @param  $exerciseid  Optional exercises id.
     * @return array   Array of sessions conducted count per module.
     */
    public function get_graded_sessions_count() {
        return grading_vault::get_user_total_graded_sessions($this->course->get_id(), $this->userid);
    }

    /**
     * Activates the instructor if inactive.
     */
    public function activate() {
        $groupid = groups_get_group_by_name($this->course->get_id(), LOCAL_BOOKING_INACTIVEGROUP);
        return groups_remove_member($groupid, $this->userid);
    }
}