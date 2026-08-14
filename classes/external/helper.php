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
 * Shared access-control helpers for external functions.
 *
 * @package    local_booking
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

/**
 * Trait for validating course context and capabilities in web services.
 *
 * @package    local_booking
 */
trait helper {

    /**
     * Validate the course context and require a capability.
     *
     * Moodle AJAX services do not enforce the capabilities declared in
     * db/services.php. Callers must check them in execute().
     *
     * @param int $courseid The course id
     * @param string $capability The required capability
     * @return \context_course
     */
    protected static function validate_course_access(int $courseid, string $capability): \context_course {
        $context = \context_course::instance($courseid);
        self::validate_context($context);
        require_capability($capability, $context);
        return $context;
    }
}
