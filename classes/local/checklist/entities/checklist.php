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
 * Checklist entity class
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\checklist\entities;

use local_booking\local\checklist\data_access\checklist_vault;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a course checklist.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class checklist implements checklist_interface {

    /**
     * @var $_data The checklist data record.
     */
    private $_data = [];

    /**
     * Set called when writing to an inaccessible or non-existent property
     *
     * @param string $name The property name
     * @param mixed $value The value to set
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * Get called when reading from an inaccessible or non-existent property
     *
     * @param string $name The property name
     * @return mixed The property value if set, or null if not set
     */
    public function __get($name) {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return null;
    }

    /**
     * Populate checklist from record
     *
     * @param array $record The record containing checklist data
     */
    public function populate($record) {
        foreach ($record as $key => $value) {
            $this->_data[$key] = $value;
        }
        return;
    }

    /**
     * Get items of this checklist and checked ones for student when requested
     *
     * @param int|null $studentid The student ID for whom to get checked items
     * @return array The list of checklist items
     */
    public function get_items($studentid = null) {
        if (!isset($this->id)) {
            return [];
        }

        // Check if items are already loaded in the checklist data
        $items = $this->_data['items'];
        if (!$items) {
            $items = checklist_vault::get_checklist_items($this->id);
        }

        // If student ID is provided, get checked items and mark them
        if ($studentid) {
            $checkeditems = checklist_vault::get_checked_items($this->id, $studentid);
            foreach ($items as $item) {
                $item->studentcheck = $checkeditems ? $checkeditems->usertimestamp : 0;
                $item->teachercheck = $checkeditems ? $checkeditems->teachermark : CHECKLIST_TEACHERMARK_UNDECIDED;
                $item->teachertimestamp = $checkeditems ? $checkeditems->teachertimestamp : 0;
                $item->checkid = $checkeditems ? $checkeditems->id : 0;
            }
        }
        return $items;
    }

    /**
     * Update a checklist item for a student
     *
     * @param int $itemid The ID of the checklist item to update
     * @param int $studentid The student ID for whom to update the item
     * @param int $teacherid The teacher ID updating the item
     * @param int $state The new state of the checklist item
     * @return bool True if the update was successful, false otherwise
     */
    public static function update_checklist_item($itemid, $studentid, $teacherid, $state) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/checklist/lib.php');

        $checklist = checklist_vault::update_course_checklist_item($itemid, $studentid, $teacherid, $state);

        // Update completion if needed
        checklist_update_grades($checklist, $studentid);

        return !empty($checklist);
    }
}
