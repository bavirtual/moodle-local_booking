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

use user_picture;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/user/externallib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use core_user_external;
use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_student_names extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve student names for autocomplete.
     *
     * @param int $courseid The course id for context.
     * @return array Array of student names exported.
     */
    public static function execute(int $courseid) {
        global $PAGE;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'courseid' => $courseid,
                )
            );

        $warnings = [];

        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

        // get extra required fields for user combobox search
        $userfieldsapi = \core_user\fields::for_identity($subscriber->get_context(), false)->with_userpic();
        $extrauserfields = $userfieldsapi->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]);

        // For the returned users, Add a couple of extra fields that we need for the search module.
        $users = array_map(function ($user) use ($PAGE, $extrauserfields) {
            $userforselector = new \stdClass();
            $userforselector->id = $user->id;
            $userforselector->fullname = fullname($user);
            foreach (\core_user\fields::get_name_fields() as $field) {
                $userforselector->$field = $user->$field ?? null;
            }
            $userpicture = new user_picture($user);
            $userpicture->size = 1;
            $userforselector->profileimageurl = $userpicture->get_url($PAGE)->out(false);
            $userpicture->size = 0; // Size f2.
            $userforselector->profileimageurlsmall = $userpicture->get_url($PAGE)->out(false);
            foreach ($extrauserfields as $field) {
                $userforselector->$field = $user->$field ?? null;
            }
            return $userforselector;
        }, $subscriber->get_student_names('active', true, 'student'));
        sort($users);

        return [
            'users' => $users,
            'warnings' => $warnings,
        ];
    }

    /**
     * Returns description of what the users & warnings should return.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'users' => new external_multiple_structure(core_user_external::user_description()),
            'warnings' => new external_warnings(),
        ]);
    }
}
