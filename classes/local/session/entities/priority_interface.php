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
 * Interface for a priority class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface priority_interface {

    /**
     * Get Session Recency in days for a particular student
     *
     * @return int  $recencydays    The number of days since last session
     */
    public function get_recency_days();

    /**
     * Get course activity for a student from the logs.
     *
     * @return int  $activitycount  The number of activity events in the log.
     */
    public function get_activity_count(bool $normalized = true);

    /**
     * Get course activity for a student from the logs.
     *
     * @return int  $completions    The number of lesson completions.
     */
    public function get_completions();

    /**
     * Get total prioritization score for the student
     *
     * @return int  $score      The total prioritization score
     */
    public function get_score();
}