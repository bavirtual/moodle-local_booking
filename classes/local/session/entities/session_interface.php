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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\entities;

defined('MOODLE_INTERNAL') || die();

/**
 * Interface for a course exercise session class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface session_interface {
    /**
     * Get the grade for this session.
     *
     * @return grade
     */
    public function get_grade();

    /**
     * Get the booking for this session.
     *
     * @return booking
     */
    public function get_booking();

    /**
     * Get the logentry for this session.
     *
     * @return logentry
     */
    public function get_logentry();

    /**
     * Get the status for this session.
     *
     * @return string
     */
    public function get_status();

    /**
     * Get the additional info for this session.
     *
     * @return string
     */
    public function get_info();

    /**
     * Get the date of this session.
     *
     * @return Datetime
     */
    public function get_sessiondate();

    /**
     * Get whether this session has a grade.
     *
     * @return bool
     */
    public function hasgrade();

    /**
     * Get whether the student passed the session.
     *
     * @return bool
     */
    public function haspassed();

    /**
     * Get whether this session has a booking.
     *
     * @return bool
     */
    public function hasbooking();

    /**
     * Get whether this session has not been graded or booked
     *  (i.e. future session).
     *
     * @return bool
     */
    public function empty();
}
