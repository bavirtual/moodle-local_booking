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
 * Uninstall ATO course custom fields and categories
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @category   Uninstall
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');

use \core_customfield\api;

function xmldb_local_booking_uninstall() {

    delete_course_customfields();

    return true;
}


/**
 * Delete to ATO custom category and fields for all courses
 */
function delete_course_customfields() {
    // get all categories and fields.
    $categories = api::get_categories_with_fields('core_course', 'course', 0);
    foreach ($categories as $coursecategory) {
        // delete ATO category and associated fields
        if ($coursecategory->get('name') == get_booking_config('ATO')->name) {
            // Delete custom ATO category
            api::delete_category($coursecategory);
        }
    }
}