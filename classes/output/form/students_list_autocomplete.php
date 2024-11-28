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
 * Student search selector field.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\output\form;

use MoodleQuickForm_autocomplete;

global $CFG;
require_once($CFG->libdir . '/form/autocomplete.php');


/**
 * Form field type for choosing a student from an autocomplete list.
 *
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class students_list_autocomplete extends MoodleQuickForm_autocomplete {

    /**
     * Constructor.
     *
     * @param string $elementName Element name
     * @param mixed $elementLabel Label(s) for an element
     * @param array $options Options to control the element's display
     *                       Valid options are:
     *                       - multiple bool Whether or not the field accepts more than one values.
     *                       - placeholder string text as a place prior to choosing a student.
     */
    public function __construct($elementName = null, $elementLabel = null, $options = array()) {

        if (!empty($options['courseid'])) {
            $options = array('data-courseid' => $options['courseid']) + $options;
        }

        $options = array(
            'ajax' => 'local_booking/students_datasource',
            ) + $options;
        parent::__construct($elementName, $elementLabel, array(), $options);
    }

    /**
     * Set the value of this element.
     *
     * @param  string|array $value The value to set.
     * @return boolean
     */
    public function setValue($value) {
        global $DB;
        $ids = array();

        if (empty($ids)) {
            return $this->setSelected(array());
        }

        // Logic here is simulating API.
        $toselect = array();
        $users = $DB->get_records('user', $toselect, array('id', 'firstname', 'lastname', 'alternatename'), IGNORE_MISSING);
        // $frameworks = competency_framework::get_records_select("id $insql", $inparams, 'shortname');
        foreach ($users as $user) {
            $this->addOption($user->firstname . ' ' . $user->lastname . ' ' . $user->alternatename, $user->id);
            array_push($toselect, $user->id);
        }

        return $this->setSelected($toselect);
    }
}