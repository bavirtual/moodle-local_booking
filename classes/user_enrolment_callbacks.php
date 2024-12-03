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

namespace local_booking;

use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;

/**
 * Hook callbacks to get the enrolment information.
 *
 * @package    local_booking
 * @copyright  2024 Mustafa Hajjar <mustafa.hajjar>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_enrolment_callbacks {

    /**
     * Callback for the user_enrolment hook.
     *
     * @param \core_enrol\hook\after_user_enrolled $hook
     */
    public static function user_enrolment_created(\core_enrol\hook\after_user_enrolled $hook): void {

        $instance = $hook->get_enrolinstance();
        // check if the user is enroled to a subscribing course
        if (subscriber::is_subscribed($instance->courseid)) {
            // check for enrolment role
            if (get_all_roles()[$hook->roleid]->archetype == 'student') {
                $student = new student($instance->courseid, $hook->get_userid());
                $nextexerciseid = $student->get_next_exercise()->id;
                $student->update_statistic('nextexerciseid', $nextexerciseid);
            }
        }
    }

    /**
     * Callback for the user_enrolment hook.
     *
     * @param \core_enrol\hook\after_user_enrolled $hook
     */
    public static function user_enrolment_deleted(\core_enrol\hook\before_user_enrolment_removed $hook): void {

        $instance = $hook->enrolinstance;
        // check if the user is enroled to a subscribing course
        if (subscriber::is_subscribed($instance->courseid)) {
            // check for enrolment role
            $participant = new participant($instance->courseid, $hook->get_userid());
            if ($participant->is_student()) {
                subscriber::delete_enrolment_stats($instance->courseid, $hook->get_userid());
            }
        }
    }
}
