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
 * The mform for creating a logbook entry
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\logbook\forms;

use local_booking\local\participant\entities\participant;
use local_booking\local\session\entities\booking;
use local_booking\local\subscriber\entities\subscriber;

defined('MOODLE_INTERNAL') || die();

 /**
  * Always include formslib
  */
require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The mform class for creating and editing a logbook entry
 *
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create extends \moodleform {

    /**
     * The form definition
     */
    public function definition() {
        global $PAGE, $COURSE;

        $mform = $this->_form;
        $logentry = isset($this->_customdata['logentry']) ? $this->_customdata['logentry'] : null;
        if (!empty($logentry) && !empty($logentry->get_id())) {
            $sessiondate = $logentry->get_sessiondate();
        } else {
            $sessiondate = isset($this->_customdata['sessiondate']) ? $this->_customdata['sessiondate'] : null;
        }
        $courseid = isset($this->_customdata['courseid']) ? $this->_customdata['courseid'] : null;

        // get subscribing course info
        $subscriber = new subscriber($courseid);

        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();

        // Empty string so that the element doesn't get rendered.
        $mform->addElement('header', 'general', '');

        $this->add_default_hidden_elements($mform);

        // Logbook entry date field.
        $mform->addElement('date_selector', 'sessiondate', get_string('sessiondate', 'local_booking'), array('timezone'=>0));
        $mform->addRule('sessiondate', get_string('required'), 'required', null, 'client');
        $mform->setType('sessiondate', PARAM_TEXT);
        $mform->setDefault('sessiondate', $sessiondate);

        // Flight duration
        $mform->addElement('text', 'flighttimemins', get_string('flighttimemins', 'local_booking'), 'size="5" placeholder="hh:mm"');
        $mform->addRule('flighttimemins', get_string('required'), 'required', null, 'client');
        $mform->setType('flighttimemins', PARAM_TEXT);

        // Session duration
        $mform->addElement('text', 'sessiontimemins', get_string('sessiontimemins', 'local_booking'), 'size="5" placeholder="hh:mm"');
        $mform->addRule('sessiontimemins', get_string('required'), 'required', null, 'client');
        $mform->setType('sessiontimemins', PARAM_TEXT);

        // Solo duration
        $mform->addElement('text', 'soloflighttimemins', get_string('soloflighttimemins', 'local_booking'), 'size="5" placeholder="hh:mm"');
        $mform->setType('soloflighttimemins', PARAM_TEXT);

        $picid = !empty($logentry) ? $logentry->get_picid() : 0;
        $picname = !empty($logentry) ? $logentry->get_picname() : '';
        $sicid = !empty($logentry) ? $logentry->get_sicid() : 0;
        $sicname = !empty($logentry) ? $logentry->get_sicname() : '';

        // PIC/SIC name selector
        $pilots = $this->get_pilot_ids($COURSE->id);

        // PIC name select
        $select = $mform->addElement('select', 'picid', get_string('pic', 'local_booking'), $pilots);
        $select->setSelected($picid);

        // SIC name select
        $select = $mform->addElement('select', 'sicid', get_string('sic', 'local_booking'), $pilots);
        $select->setSelected($sicid);

        $select = $mform->addElement('select', 'aircraft', get_string('aircraft', 'local_booking'), $subscriber->aircrafticao);
        $select->setSelected('0');
        $mform->setAdvanced('aircraft');

        // PIREP advanced element
        $mform->addElement('text', 'pirep', get_string('pirep', 'local_booking'));
        $mform->setType('pirep', PARAM_TEXT);
        $mform->setAdvanced('pirep');

        // Callsign advanced element
        $instructorcallsign = $sicid != 0 ? $this->get_callsign($COURSE->id, $sicid) : '';
        $mform->addElement('text', 'callsign', get_string('callsign', 'local_booking'), 'style="text-transform:uppercase" ');
        $mform->setType('callsign', PARAM_TEXT);
        $mform->setDefault('callsign', $instructorcallsign);
        $mform->setAdvanced('callsign');

        // From ICAO advanced element
        $mform->addElement('text', 'fromicao', get_string('fromicao', 'local_booking'), 'style="text-transform:uppercase" ');
        $mform->setType('fromicao', PARAM_TEXT);
        $mform->setDefault('fromicao', $subscriber->homeicao);
        $mform->setAdvanced('fromicao');

        // To ICAO advanced element
        $mform->addElement('text', 'toicao', get_string('toicao', 'local_booking'), 'style="text-transform:uppercase" ');
        $mform->setType('toicao', PARAM_TEXT);
        $mform->setDefault('toicao', $subscriber->homeicao);
        $mform->setAdvanced('toicao');

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('local_booking/modal_logentry_form');
    }

    /**
     * A bit of custom validation for this form
     *
     * @param array $data An assoc array of field=>value
     * @param array $files An array of files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // validate the flight date is not before the booking date
        $exercisedate = booking::get_exercise_date($data['courseid'], $data['studentid'], $data['exerciseid']);
        if (!empty($exercisedate)) {
            if ($data['sessiondate'] < $exercisedate) {
                $errors['sessiondate'] = get_string('errorinvaliddate', 'local_booking');
            }
        }

        return $errors;
    }

    /**
     * Add the list of hidden elements that should appear in this form each
     * time. These elements will never be visible to the user.
     *
     * @param MoodleQuickForm $mform
     */
    protected function add_default_hidden_elements($mform) {
        global $USER;

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        // $mform->addElement('hidden', 'logentryid');
        // $mform->setType('logentryid', PARAM_INT);
        // $mform->setDefault('logentryid', 0);

        $mform->addElement('hidden', 'studentid');
        $mform->setType('studentid', PARAM_INT);
        $mform->setDefault('studentid', 0);

        $mform->addElement('hidden', 'exerciseid');
        $mform->setType('exerciseid', PARAM_INT);
        $mform->setDefault('exerciseid', 0);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', 0);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setDefault('contextid', 0);

        $mform->addElement('hidden', 'visible');
        $mform->setType('visible', PARAM_INT);
        $mform->setDefault('visible', 1);
    }

    /**
     * Get the list of active student pilot ids.
     *
     * @return array $activepilots List of user ids for PIC & SIC pilots
     */
    protected function get_pilot_ids($courseid) {
        $course = new subscriber($courseid);
        $pilots = $course->get_active_participants();

        foreach ($pilots as $pilot) {
            $activepilots[$pilot->userid] = $pilot->fullname;
        }

        return $activepilots;
    }

    /**
     * Get the callsign of the secondary in command
     * commonly the instructor for most flights.
     *
     * @return string $callsign The instructor's callsign
     */
    protected function get_callsign($courseid, $sicid) {
        $pilot = new participant($courseid, $sicid);
        return $pilot->get_callsign();
    }
}
