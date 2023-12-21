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

require_once($CFG->dirroot . '/group/lib.php');

use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\session\data_access\booking_vault;
use local_booking\local\session\data_access\grading_vault;

class examiner extends participant {

    /**
     * Constructor.
     *
     * @param subscriber $course The subscribing course the student is enrolled in.
     * @param int $examiner The examiner id.
     */
    public function __construct(subscriber $course, int $examinerid) {
        parent::__construct($course, $examinerid);
        $this->is_student = false;
    }

    /**
     * Activates the instructor if inactive.
     */
    public function activate() {
        $groupid = groups_get_group_by_name($this->course->get_id(), LOCAL_BOOKING_INACTIVEGROUP);
        return groups_remove_member($groupid, $this->userid);
    }
}