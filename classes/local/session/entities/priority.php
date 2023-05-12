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

use local_booking\local\session\data_access\analytics_vault;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing student priority in booking.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class priority implements priority_interface {

    // Constant as a divider to normalize log entry counts
    const NORMALIZER = 10;

    /**
     * @var int  $studentid     The student id the priority is calculated for
     */
    protected $studentid;

    /**
     * @var int  $score         The total score representing the student's priority
     */
    protected $score;

    /**
     * @var int  $recencydays    The number of days since last session
     */
    protected $recencydays;

    /**
     * @var array  $recencyinfo  An array containing the source of the recency information
     */
    protected $recencyinfo;

    /**
     * @var int  $slotcount      The number of availability slots marked by the student.
     */
    protected $slotcount;

    /**
     * @var int  $activitycount  The number of activity events in the log with the normalizer divided.
     */
    protected $activitycount;

    /**
     * @var int  $activitycountraw  The number of activity events in the log.
     */
    protected $activitycountraw;

    /**
     * @var int  $completions    The number of lesson completions.
     */
    protected $completions;

    /**
     * Constructor.
     *
     * @param int $studentid    The student id related to the priority score.
     * @param array $related Related objects.
     */
    public function __construct(int $courseid, int $studentid) {
        list($this->recencydays, $this->recencyinfo) = analytics_vault::get_session_recency($courseid, $studentid);
        $recencydaysweight = get_config('local_booking', 'recencydaysweight') ? get_config('local_booking', 'recencydaysweight') : LOCAL_BOOKING_RECENCYWEIGHT;

        $this->slotcount = analytics_vault::get_slot_count($courseid, $studentid);
        $slotcountweight = get_config('local_booking', 'slotcountweight') ? get_config('local_booking', 'slotcountweight') : LOCAL_BOOKING_SLOTSWEIGHT;

        $activity = analytics_vault::get_activity_count($courseid, $studentid);
        $this->activitycount = floor($activity/ self::NORMALIZER);
        $this->activitycountraw = $activity;
        $activitycountweight = get_config('local_booking', 'activitycountweight') ? get_config('local_booking', 'activitycountweight') : LOCAL_BOOKING_ACTIVITYWEIGHT;

        $this->completions = analytics_vault::get_lesson_completions($courseid, $studentid);
        $completionsweight = get_config('local_booking', 'completionweight') ? get_config('local_booking', 'completionweight') : LOCAL_BOOKING_COMPLETIONWEIGHT;

        $this->score = ( $this->recencydays * $recencydaysweight ) + ( $this->slotcount * $slotcountweight ) +
                       ( $this->activitycount * $activitycountweight ) + ( $this->completions + $completionsweight );
    }

    /**
     * Get Session Recency in days for a particular student
     *
     * @return int  $recencydays    The number of days since last session
     */
    public function get_recency_days() {
        return $this->recencydays;
    }

    /**
     * Returns an array containing the source of the recency information
     * The array contains the source and date information.
     *
     * @return array  $recencyinfo    Explains the source of the recency information
     */
    public function get_recency_info() {
        $info = '';

        switch ($this->recencyinfo['source']) {
            case 'booking':
                $info = get_string('bookingrecencyfrombooktooltip', 'local_booking', ($this->recencyinfo['date'])->format('j M \'y'));
                break;
            case 'grade':
                $info = get_string('bookingrecencyfromgradetooltip', 'local_booking', ($this->recencyinfo['date'])->format('j M \'y'));
                break;
            case 'enrol':
                $info = get_string('bookingrecencyfromenroltooltip', 'local_booking', ($this->recencyinfo['date'])->format('j M \'y'));
                break;
        }
        return $info;
    }

    /**
     * Get the number of Availability slots marked by the student.
     *
     * @return int  $slotcount      The number of availability slots marked by the student.
     */
    public function get_slot_count() {
        return $this->slotcount;
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @return int  $activitycount  The number of activity events in the log.
     */
    public function get_activity_count(bool $normalized = true) {
        return $normalized ? $this->activitycount : $this->activitycountraw;
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @return int  $completions    The number of lesson completions.
     */
    public function get_completions() {
        return $this->completions;
    }

    /**
     * Get total prioritization score for the student
     *
     * @return int  $score      The total prioritization score
     */
    public function get_score() {
        return $this->score;
    }
}
