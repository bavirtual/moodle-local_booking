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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_function_parameters;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_student_group extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function execute_parameters() {
        // studentid is always current user, so no need to get it from client.
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'studentid' => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
                'groupname' => new external_value(PARAM_RAW, 'The group name to add to', VALUE_DEFAULT),
                'add' => new external_value(PARAM_BOOL, 'Whether to add or remove student from the group', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Add/remove student to/from a group.
     *
     * @param int $courseid The group course
     * @param int $studentid The student user id
     * @param string $groupname The group name to add/remove
     * @param bool $add Wether to add or remove from the group
     * @return array operation result
     */
    public static function execute(int $courseid, int $studentid, string $groupname, bool $add) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'courseid' => $courseid,
                'studentid' => $studentid,
                'groupname' => $groupname,
                'add' => $add
            )
        );

        // get group id from group name
        $groupid = groups_get_group_by_name($params['courseid'], $params['groupname']);

        // add/remove student to group
        if ($params['add']) {
            $result = groups_add_member($groupid, $params['studentid']);
        } else {
            $result = groups_remove_member($groupid, $params['studentid']);
        }

        return array(
            'result' => $result,
            'warnings' => array()
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }
}
