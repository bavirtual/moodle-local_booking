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
 * The mform for searching students in session booking vew
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\output\form;

defined('MOODLE_INTERNAL') || die();

 /**
  * Always include formslib
  */
require_once($CFG->libdir . '/formslib.php');
\MoodleQuickForm::registerElementType('students_list_autocomplete',
    $CFG->dirroot . '/local/booking/classes/output/form/students_list_autocomplete.php',
    '\\local_booking\\output\\form\\students_list_autocomplete');

/**
 * The mform class for students search and filter in session booking view
 *
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_view_search extends \moodleform {

    /**
     * The form definition
     */
    public function definition() {
        global $PAGE;

        $mform = $this->_form;

        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();

        // Empty string so that the element doesn't get rendered.
        $mform->addElement('header', 'general', '');

        $options = array(
            'multiple' => false,
            'placeholder' => get_string('search:typetosearch', 'local_booking'),
            'courseid' => $this->_customdata['courseid'],
        );
        // add students to the select criteria
        $mform->addElement('students_list_autocomplete', 'userids', get_string('search'), $options);
        $mform->addElement('button', 'searchstudents', get_string('search'), ['data-region'=>'search-button'], ['customclassoverride' => 'btn-primary ml-2']);
        $mform->addElement('button', 'clearsearch', get_string('clear'), ['data-region'=>'clearsearch-button'], ['customclassoverride' => 'btn-secondary ml-2']);

        // add hidden fields
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $this->_customdata['courseid']);

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('local_booking/booking_view_manager');
    }
}
