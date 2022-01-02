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
 * Class representing a course exercise session.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session implements session_interface {

    /**
     * @var grade $graded The session grade object.
     */
    protected $grade;

    /**
     * @var booking $booking The session booking object.
     */
    protected $booking;

    /**
     * @var logentry $logentry the student's logentry.
     */
    protected $logentry;

    /**
     * @var string $status The session status.
     */
    protected $status;

    /**
     * @var string $info The session addtional information.
     */
    protected $info;

    /**
     * @var Datetime $sessiondate The date of this session.
     */
    protected $sessiondate;

    /**
     * Constructor.
     *
     * @param grade             $grade          The session grade object.
     * @param booking           $booking        The session booking object.
     * @param logentry          $logentry       The session logentry object.
     * @param string            $status         The session status.
     * @param string            $info           The session additional information.
     * @param Datetime          $sessiondate    The date of this session.
     */
    public function __construct(
        $grade = null,
        $booking = null,
        $logentry = null,
        $status = '',
        $info = '',
        $sessiondate = null
    ) {
        $this->grade = $grade;
        $this->booking = $booking;
        $this->logentry = $logentry;
        $this->status = $status;
        $this->info = $info;
        $this->sessiondate = $sessiondate;
    }

    /**
     * Get the grade for this session.
     *
     * @return grade
     */
    public function get_grade() {
        return $this->grade;
    }

    /**
     * Get the booking for this session.
     *
     * @return booking
     */
    public function get_booking() {
        return $this->booking;
    }

    /**
     * Get the logentry for this session.
     *
     * @return logentry
     */
    public function get_logentry() {
        return $this->logentry;
    }

    /**
     * Get the status for this session.
     *
     * @return string
     */
    public function get_status() {
        return $this->status;
    }

    /**
     * Get the addional info for this session.
     *
     * @return string
     */
    public function get_info() {
        return $this->info;
    }

    /**
     * Get the date of this session.
     *
     * @return Datetime
     */
    public function get_sessiondate() {
        return $this->sessiondate;
    }

    /**
     * Get whether this session has a grade.
     *
     * @return bool
     */
    public function hasgrade() {
        return $this->grade !== null;
    }

    /**
     * Get whether this session has a booking.
     *
     * @return bool
     */
    public function hasbooking() {
        return $this->booking !== null;
    }

    /**
     * Get whether this session has not been graded or booked
     *  (i.e. future session).
     *
     * @return bool
     */
    public function empty() {
        return (!$this->hasbooking() && !$this->hasgrade());
    }
}
