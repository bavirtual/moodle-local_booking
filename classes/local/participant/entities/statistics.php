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

namespace local_booking\local\participant\entities;

use completion_info;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\slot\data_access\slot_vault;
use local_booking\local\participant\entities\student;

require_once($CFG->libdir . '/completionlib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing student statistics in booking.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class statistics implements statistics_interface {

    // Constant as a divider to normalize log entry counts
    const NORMALIZER = 10;

    /**
     * @var student  $student   The student related to the statistics.
     */
    protected $student;

    /**
     * @var int  $recencydays The number of days since last session.
     */
    protected $recencydays;

    /**
     * @var int  $activitycount The number of activity events in the log with the normalizer divided.
     */
    protected $activitycount;

    /**
     * @var int  $activitycountraw  The number of activity events in the log.
     */
    protected $activitycountraw;

    /**
     * @var int $total_posts The student's total number of availability posted.
     */
    protected $total_posts;

    /**
     * @var int  $completions    The number of lesson completions.
     */
    protected $completions;

    /**
     * Constructor.
     *
     * @param student $student The student related to the statistics.
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
            $activity = participant_vault::get_student_activity_count($this->student->get_courseid(), $this->student->get_id());
            $this->activitycount = floor($activity / self::NORMALIZER);
            $this->activitycountraw = $activity;
        }

        return $normalized ? $this->activitycount : $this->activitycountraw;
    }

    /**
     * Returns the total number of active posts.
     *
     * @return int The number of active posts
     */
    public function get_all_posts_count() {

        if (!isset($this->total_posts))
            $this->total_posts = slot_vault::get_slot_count($this->student->get_courseid(), $this->student->get_id(), 'all');

        return $this->total_posts;
    }

    /**
     * Returns the total number of active posts.
     *
     * @return int The number of active posts
     */
    public function get_active_posts_count() {

        return slot_vault::get_slot_count($this->student->get_courseid(), $this->student->get_id());
    }

    /**
     * Returns the number of posts based on course on-hold restriction.
     *
     * @return int The number of valid posts
     */
    public function get_valid_posts_count() {

        $subscriber = $this->student->get_course();
        $onholddays = $subscriber->get_on_hold_days_restriction();

        return slot_vault::get_slot_count(
            $this->student->get_courseid(),
            $this->student->get_id(),
            'valid',
            $onholddays
        );
    }

    /**
     * Get the count of completed courses exercises (assignment activities) for the associated student.
     *
     * @return int  $completions    The number of lesson completions.
     */
    public function get_completed_exercise_count() {

        if (!isset($this->completions)) {
            $completedexercises = 0;
            $subscriber = $this->student->get_course();
            $course = $subscriber->get_course();
            $exercises = $subscriber->get_exercises();
            $coursecompletion = new completion_info($course);

            // get the completion status of all exercises (assignment activities)
            foreach ($exercises as $exercise) {
                // get the completion status of the exercise
                $completions = $coursecompletion->get_data($exercise, false, $this->student->get_id());
                if ($completions->completionstate == COMPLETION_COMPLETE_PASS) {
                    $completedexercises++;
                }
            }
            $this->completions = $completedexercises;
       }
        return $this->completions;
    }
}
