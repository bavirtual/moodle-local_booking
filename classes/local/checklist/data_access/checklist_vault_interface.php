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
 * Checklist plugin data access interface
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\checklist\data_access;

defined('MOODLE_INTERNAL') || die();

interface checklist_vault_interface
{

    /**
     * Get all checklists in a course, optionally including their items and student progress
     *
     * @param int $courseid
     * @param bool $getitems Whether to get the checklist items as well (takes longer)
     * @param int $studentid The student ID for whom to retrieve checked items
     * @return array of checklist records
     */
    public static function get_course_checklists($courseid, $getitems = false, $studentid = 0);

    /**
     * Get checklist items with student progress
     *
     * @param int $checklistid
     * @param int $studentid (optional)
     * @return array of items with progress
     */
    public static function get_checklist_items($checklistid, $studentid = 0);

    /**
     * Update checklist item for student
     *
     * @param int $itemid
     * @param int $studentid
     * @param int $teacherid
     * @param int $state (CHECKLIST_TEACHERMARK_YES or CHECKLIST_TEACHERMARK_NO)
     * @return object checklist record
     */
    public static function update_course_checklist_item($itemid, $studentid, $teacherid, $state);
}