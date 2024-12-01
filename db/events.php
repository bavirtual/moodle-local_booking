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
 * Add event handlers of graded submissions for bookings
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @category   event
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => 'core\event\user_enrolment_created',
        'callback' => '\local_booking\observers::user_enrolment_created',
    ),
    array(
        'eventname' => 'core\event\user_enrolment_deleted',
        'callback' => '\local_booking\observers::user_enrolment_deleted',
    ),
    array(
        'eventname' => 'core\event\course_updated',
        'callback' => '\local_booking\observers::course_updated',
    ),
    array(
        'eventname' => '\mod_lesson\event\lesson_ended',
        'callback' => '\local_booking\observers::lesson_ended',
    ),
    array(
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\local_booking\observers::course_module_completion_updated',
    ),
    array(
        'eventname' => '\mod_assign\event\submission_graded',
        'callback' => '\local_booking\observers::submission_graded',
    ),
);
