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
 * Session Booking plugin email messages
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$messageproviders = array(
    // Notify the student that a session has been booked by the instructor.
    'booking_notification' => array(
        'capability' => 'local/booking:studentnotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Confirm to the instructor booking made by him/her.
    'booking_confirmation' => array(
        'capability' => 'local/booking:instructornotification',
    ),

    // Notify instructor of student confirmation of booked session.
    'instructor_notification' => array(
        'capability' => 'local/booking:instructornotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify student of cancelled session.
    'session_cancellation' => array(
        'capability' => 'local/booking:studentnotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify student of being inactive after posting wait period had passed
    'inactive_warning' => array(
        'capability' => 'local/booking:studentnotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify student of upcoming placement on-hold (inactive)
    'onhold_warning' => array(
        'capability' => 'local/booking:studentnotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify student of becoming inactive
    'onhold_notification' => array(
        'capability' => 'local/booking:studentnotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify student of being suspended from the course
    'suspension_notification' => array(
        'capability' => 'local/booking:studentnotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify instructor of session overdue
    'sessionoverdue_notification' => array(
        'capability' => 'local/booking:instructornotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify instructor of availability posting
    'availabilityposting_notification' => array(
        'capability' => 'local/booking:instructornotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify instructor of availability posting
    'recommendation_notification' => array(
        'capability' => 'local/booking:instructornotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),

    // Notify students and instructors of a student's graduation
    'graduation_notification' => array(
        'capability' => 'local/booking:studentnotification',
        'defaults' => array(
            'airnotifier' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
        ),
    ),
);
