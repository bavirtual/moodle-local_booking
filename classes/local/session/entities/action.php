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

use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a course exercise session action.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action implements action_interface {

    /**
     * @var string $type The type of this action.
     */
    protected $type;

    /**
     * @var url $url The name of this action.
     */
    protected $url;

    /**
     * @var string $type The name of this action.
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param event_interface  $event  The event to delegate to.
     * @param action_interface $action The action associated with this event.
     */
    public function __construct(string $actiontype, int $userid, int $refid) {
        global $COURSE;

        $actionurl = null;
        $name = '';
        // Load student(s) availability slots
        switch ($actiontype) {
            case 'grade':
                $actionurl = new moodle_url('/mod/assign/view.php', [
                    'id' => $refid,
                    'rownum' => 0,
                    'userid' => $userid,
                    'action' => 'grader',
                ]);
                $name = get_string('grade', 'grades');
                break;
            case 'book':
                $actionurl = new moodle_url('/local/booking/availability.php', [
                    'course' => $COURSE->id,
                    'exid'   => $refid,
                    'userid' => $userid,
                    'action' => 'book',
                    'time'   => (get_first_posted_slot($userid))->getTimestamp(),
                    'view'   => 'user',
                ]);
                $name = get_string('book', 'local_booking');
                break;
            case 'cancel':
                $actionurl = new moodle_url('/local/booking/view.php', [
                    'course' => $COURSE->id,
                ]);
                $name = get_string('bookingcancel', 'local_booking');
                break;
        }

        $this->type = $actiontype;
        $this->url = $actionurl;
        $this->name = $name;
    }

    /**
     * Get the type of the action.
     *
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get the URL of the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * Get the name of the action.
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Set the action type.
     *
     * @param string $type the time to set the action to
     */
    public function set_type(string $type) {
        $this->type = $type;
    }
}