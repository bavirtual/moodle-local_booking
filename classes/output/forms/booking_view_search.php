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

namespace local_booking\output\forms;

defined('MOODLE_INTERNAL') || die();

 /**
  * Always include formslib
  */
require_once($CFG->dirroot.'/lib/formslib.php');

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
        $students = isset($this->_customdata['students']) ? $this->_customdata['students'] : null;

        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();

        // Empty string so that the element doesn't get rendered.
        $mform->addElement('header', 'general', '');

        $options = array(
            'multiple' => false,
            'placeholder' => get_string('search:typetosearch', 'local_booking'),
        );
        // add students to the select criteria
        $mform->addElement('autocomplete', 'userids', get_string('search'), $students, $options);
        $mform->addElement('submit', 'submitbutton', get_string('search'));

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('local_booking/booking_view_manager');
    }
}
