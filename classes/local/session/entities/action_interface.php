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
 * Interface for a action class.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface action_interface {
    /**
     * Get the type of the action.
     *
     * @return string
     */
    public function get_type();

    /**
     * Get the URL of the action.
     *
     * @return \moodle_url
     */
    public function get_url();

    /**
     * Get the name of the action.
     *
     * @return string
     */
    public function get_name();

    /**
     * Get the action's tooltip explaining its status.
     *
     * @return string
     */
    public function get_tooltip();

    /**
     * Get the action's status.
     *
     * @return boolean the action's status
     */
    public function is_enabled();
}
