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
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\entities;

use local_booking\local\session\data_access\analytics_vault;
use local_booking\local\participant\entities\student;

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
     * @var student  $student   The student related to the priority score.
     */
    protected $student;

    /**
     * @var int  $score         The total score representing the student's priority.
     */
    protected $score;

    /**
     * @var int  $recencydays    The number of days since last session.
     */
    protected $recencydays;

    /**
     * @var array  $recencyinfo  An array containing the source of the recency information.
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
     * @param int $student    The student related to the priority score.
     */
    public function __construct(student $student) {
        $this->student = $student;
        $this->recencydays = $student->get_recency_days();
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @return int  $activitycount  The number of activity events in the log.
     */
    public function get_activity_count(bool $normalized = true) {

        if (!isset($this->activitycount)) {
            $activity = analytics_vault::get_activity_count($this->student->get_courseid(), $this->student->get_id());
            $this->activitycount = floor($activity / self::NORMALIZER);
            $this->activitycountraw = $activity;
        }

        return $normalized ? $this->activitycount : $this->activitycountraw;
    }

    /**
     * Get course activity for a student from the logs.
     *
     * @return int  $completions    The number of lesson completions.
     */
    public function get_completions() {
        if (!isset($this->completions)) {
            $this->completions = analytics_vault::get_lesson_completions($this->student->get_courseid(), $this->student->get_id());
        }
        return $this->completions;
    }

    /**
     * Get total prioritization score for the student
     *
     * @return int  $score      The total prioritization score
     */
    public function get_score() {

        if (!isset($this->score)) {
            // get booking plugin configs
            $recencydaysweight = get_config('local_booking', 'recencydaysweight') ? get_config('local_booking', 'recencydaysweight') : LOCAL_BOOKING_RECENCYWEIGHT;
            $activitycountweight = get_config('local_booking', 'activitycountweight') ? get_config('local_booking', 'activitycountweight') : LOCAL_BOOKING_ACTIVITYWEIGHT;
            $slotcountweight = get_config('local_booking', 'slotcountweight') ? get_config('local_booking', 'slotcountweight') : LOCAL_BOOKING_SLOTSWEIGHT;
            $completionsweight = get_config('local_booking', 'completionweight') ? get_config('local_booking', 'completionweight') : LOCAL_BOOKING_COMPLETIONWEIGHT;

            $this->score = ( $this->recencydays * $recencydaysweight ) + ( $this->slotcount * $slotcountweight ) +
                        ( $this->activitycount * $activitycountweight ) + ( $this->completions + $completionsweight );
        }
        return $this->score;
    }
}
