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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_booking_get_bookings_view' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'get_bookings_view',
        'description' => 'Retrieve progression and instructor active bookings',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_save_booking' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'save_booking',
        'description' => 'Save a session booking',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_cancel_booking' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'cancel_booking',
        'description' => 'Cancel intructor active booking',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_override_restriction' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'override_wait_restriction',
        'description' => 'Overrides wait restriction for a student',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_get_weekly_view' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'get_weekly_view',
        'description' => 'Fetch the weekly view data for a calendar',
        'type' => 'read',
        'capabilities' => '',
        'ajax' => true,
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_save_slots' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'save_slots',
        'description' => 'Save marked availability slots',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_delete_slots' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'delete_slots',
        'description' => 'Delete slots of a user week for a course',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_submit_create_update_form' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'submit_create_update_form',
        'description' => 'submit or create logbook entry form elements',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_get_logentry_by_id' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'get_logentry_by_id',
        'description' => 'Fetch the logbook entry by its id',
        'type' => 'read',
        'capabilities' => '',
        'ajax' => true,
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'local_booking_delete_logentry' => array(
        'classname' => 'local_booking_external',
        'classpath' => '/local/booking/externallib.php',
        'methodname' => 'delete_logentry',
        'description' => 'Delete a logbook entry by its id',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true,
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);

$services = array(
    'Session Booking retrieve instructor active bookings web service'  => array(
        'functions' => array('local_booking_get_bookings_view'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'get_bookings_view',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Session Booking save booked session web service'  => array(
        'functions' => array('local_booking_save_booking'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'save_booking',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Cancel a session booking for an instructor with a student web service'  => array(
        'functions' => array('local_booking_cancel_booking'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'cancel_booking',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Overrides wait restriction for a student web service'  => array(
        'functions' => array('local_booking_override_restriction'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'override_wait_restriction',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Student Availability save slots web service'  => array(
        'functions' => array('local_booking_save_slots'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'save_slots',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Student Availability delete slots web service'  => array(
        'functions' => array('local_booking_delete_slots'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'delete_slots',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Student Availability delete slots web service'  => array(
        'functions' => array('local_booking_submit_create_update_form'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'submit_create_update_form',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Session Booking retrieve logbook entry by id web service'  => array(
        'functions' => array('local_booking_get_logentry_by_id'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'get_logentry_by_id',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),

    'Session Booking delete logbook entry by id web service'  => array(
        'functions' => array('local_booking_delete_logentry'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 1,         // if 0, then token linked to this service won't work
        'restrictedusers' => 0,
        'shortname' => 'delete_logentry',
        'downloadfiles' => 0,
        'uploadfiles' => 0
    ),
);
