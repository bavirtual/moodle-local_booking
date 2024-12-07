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
 * Core external functions and service definitions.
 *
 * The functions and services defined on this file are
 * processed and registered into the Moodle DB after any
 * install or upgrade operation. All plugins support this.
 *
 * For more information, take a look to the documentation available:
 *     - Webservices API: {@link http://docs.moodle.org/dev/Web_services_API}
 *     - External API: {@link http://docs.moodle.org/dev/External_functions_API}
 *     - Upgrade API: {@link http://docs.moodle.org/dev/Upgrade_API}
 *
 * @package    local_booking
 * @category   webservice
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_booking_cancel_booking' => array(
        'classname' => '\local_booking\external\cancel_booking',
        'classpath' => 'local/booking/external/cancel_booking.php',
        'methodname' => 'execute',
        'description' => 'Cancel instructor active booking',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/booking:view',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_delete_logentry' => array(
        'classname' => '\local_booking\external\delete_logentry',
        'classpath' => 'local/booking/external/delete_logentry.php',
        'methodname' => 'execute',
        'description' => 'Delete a logbook entry by its id',
        'type' => 'write',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_delete_slots' => array(
        'classname' => '\local_booking\external\delete_slots',
        'classpath' => 'local/booking/external/delete_slots.php',
        'methodname' => 'execute',
        'description' => 'Delete slots of a user week for a course',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/booking:availabilityview',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_get_bookings_view' => array(
        'classname' => '\local_booking\external\get_bookings_view',
        'classpath' => 'local/booking/external/get_bookings_view.php',
        'methodname' => 'execute',
        'description' => 'Retrieve student progression view',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/booking:view',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_get_exercise_name' => array(
        'classname' => '\local_booking\external\get_exercise_name',
        'classpath' => 'local/booking/external/get_exercise_name.php',
        'methodname' => 'execute',
        'description' => 'Retrieves a course exercise name',
        'type' => 'read',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_get_instructor_bookings_view' => array(
        'classname' => '\local_booking\external\get_instructor_bookings_view',
        'classpath' => 'local/booking/external/get_instructor_bookings.php',
        'methodname' => 'execute',
        'description' => 'Retrieve instructor active bookings views',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/booking:view',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_get_logentry' => array(
        'classname' => '\local_booking\external\get_logentry',
        'classpath' => 'local/booking/external/get_logentry.php',
        'methodname' => 'execute',
        'description' => 'Fetch the logbook entry by its id',
        'type' => 'read',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_get_pirep' => array(
        'classname' => '\local_booking\external\get_pirep',
        'classpath' => 'local/booking/external/get_pirep.php',
        'methodname' => 'execute',
        'description' => 'Retrieves PIREP information',
        'type' => 'read',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_get_student_names' => array(
        'classname' => '\local_booking\external\get_student_names',
        'classpath' => 'local/booking/external/get_student_names.php',
        'methodname' => 'execute',
        'description' => 'Retrieve student names for autocomplete',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/booking:view',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_get_weekly_view' => array(
        'classname' => '\local_booking\external\get_weekly_view',
        'classpath' => 'local/booking/external/get_weekly_view.php',
        'methodname' => 'execute',
        'description' => 'Fetch the weekly view data for a calendar',
        'type' => 'read',
        'capabilities' => 'local/booking:availabilityview',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_has_conflicting_booking' => array(
        'classname' => '\local_booking\external\has_conflicting_booking',
        'classpath' => 'local/booking/external/has_conflicting_booking.php',
        'methodname' => 'execute',
        'description' => 'Checks for conflicting bookings',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/booking:view',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_save_booking' => array(
        'classname' => '\local_booking\external\save_booking',
        'classpath' => 'local/booking/external/save_booking.php',
        'methodname' => 'execute',
        'description' => 'Save a session booking',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/booking:view',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_save_logentry' => array(
        'classname' => '\local_booking\external\save_logentry',
        'classpath' => 'local/booking/external/save_logentry.php',
        'methodname' => 'execute',
        'description' => 'submit or create logbook entry form elements',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/booking:view',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_save_slots' => array(
        'classname' => '\local_booking\external\save_slots',
        'classpath' => 'local/booking/external/save_slots.php',
        'methodname' => 'execute',
        'description' => 'Save marked availability slots',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/booking:availabilityview',
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_set_student_group' => array(
        'classname' => '\local_booking\external\set_student_group',
        'classpath' => 'local/booking/external/set_student_group.php',
        'methodname' => 'execute',
        'description' => 'Add/remove student to/from a group',
        'type' => 'write',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_set_student_preferences' => array(
        'classname' => '\local_booking\external\set_student_preferences',
        'classpath' => 'local/booking/external/set_student_preferences.php',
        'methodname' => 'execute',
        'description' => 'Persist a student preference',
        'type' => 'write',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_set_suspended_status' => array(
        'classname' => '\local_booking\external\set_suspended_status',
        'classpath' => 'local/booking/external/set_suspended_status.php',
        'methodname' => 'execute',
        'description' => 'Suspended status update for the course',
        'type' => 'write',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
    'local_booking_update_profile_comment' => array(
        'classname' => '\local_booking\external\update_profile_comment',
        'classpath' => 'local/booking/external/update_profile_comment.php',
        'methodname' => 'execute',
        'description' => 'Update user comment field (description) for a user',
        'type' => 'write',
        'capabilities' => 'local/booking:view',
        'ajax' => true,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ),
);
