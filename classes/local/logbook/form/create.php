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

namespace local_booking\local\logbook\form;

use ArrayObject;
use DateTimeImmutable;
use local_booking\local\logbook\entities\logbook;
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

        // check for new entry
        if (!empty($logentry)) {
            $newentry = $logentry->get_id() == 0;
        } else {
            $newentry = true;
        }

        $mform->setDisableShortforms();
        $mform->disable_form_change_checker();

        // Empty string so that the element doesn't get rendered.
        $mform->addElement('header', 'general', '');

        // display a list of all exercises when no exercise is selected
        $exerciseempty = $this->_customdata['exerciseid'] == 0;
        if ($exerciseempty) {
            $exercises[] = '';
            $exercises = $exercises + $COURSE->subscriber->get_exercises();
            $this->add_element($mform, 'exercises', [$exercises]);
        }

        // identify the flight type based on the exercise
        $graduationexerciseid = $COURSE->subscriber->get_graduation_exercise();
        $flighttype = $newentry ? ($this->_customdata['exerciseid'] == $graduationexerciseid ? 'check' : 'training') : $logentry->get_flighttype();
        $this->add_default_hidden_elements($mform, $COURSE->subscriber->trainingtype, $flighttype, $graduationexerciseid);
        $this->add_elements($mform, $COURSE->subscriber, $logentry, $flighttype, $exerciseempty);

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('local_booking/logentry_modal_form');
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
        $exercisedate = booking::get_exercise_date($data['courseid'], $data['userid'], $data['exerciseid']);
        $exercisedatets = 0;

        if (!empty($exercisedate)) {
            $exercisedatets = $exercisedate->getTimestamp();
        }

        if ($exercisedatets != 0 && array_key_exists('flighttype', $data) && array_key_exists('flightdate', $data)) {
            if ($data['flightdate'] < $exercisedatets && $data['flighttype'] != 'solo' )
                $errors['flightdate'] = get_string('errorinvaliddate', 'local_booking');
        }

        // validate the flight departure is after arrival time if exists
        if (!empty($data['deptime']) && !empty($data['arrtime'])) {
            if ($data['arrtime'] <= $data['deptime'])
                $errors['arrtime'] = get_string('errorinvalidarrtime', 'local_booking');
        }

        return $errors;
    }

    /**
     * Set the list of element in the form based on
     * the training type.
     *
     * @param MoodleQuickForm $mform
     * @param subscriber $subscriber
     * @param logentry $logentry
     * @param string $flighttype
     * @param bool $exerciseempty
     */
    protected function add_elements($mform, $subscriber, $logentry, $flighttype, $exerciseempty) {
        global $USER;
        $integratedpireps = $subscriber->has_integration('external_data', 'pireps');
        $newlogentry = empty($logentry) || empty($logentry->get_id());
        $graduationexerciseid = $subscriber->get_graduation_exercise();

        // set core logentry data
        if ($newlogentry) {
            $flightdate = time();
            if (array_key_exists('flightdate', $this->_customdata)) {
                $flightdate = is_numeric($this->_customdata['flightdate']) ? $this->_customdata['flightdate'] :
                    (new DateTimeImmutable(str_replace("- ", "", $this->_customdata['flightdate'])))->getTimestamp();
            }
            $p1id = $USER->id;
            $p2id = $this->_customdata['userid'];
        } else {
            $flightdate = $logentry->get_flightdate();
            $p1id = $logentry->get_p1id();
            $p2id = $logentry->get_p2id();
        }

        // P1/PIC instructor id and P2 student id
        $instructors = $subscriber->get_instructors(false,true);
        $p1s = \array_combine(array_keys($instructors), array_column($instructors, 'fullname'));
        $p2 = $subscriber->get_participant($p2id);

        // show flight type first both regular training or check flight, and freeze type selection for solo flight edits w/ a logentry
        $freezeflighttype = $flighttype == 'solo' && $logentry->get_id();
        $this->add_element($mform, 'flighttype', [$exerciseempty, $graduationexerciseid, $freezeflighttype], true, true, null, 'training');
        $this->add_element($mform, 'passfail', [$exerciseempty, $graduationexerciseid]);

        // show pireps only if they there is lookup integration or a PIREP for editing
        if ($integratedpireps || !$newlogentry) {
            $this->add_element($mform, 'pireps', array($integratedpireps, $newlogentry));
        } elseif (!$integratedpireps) {
            // add PIREP group if there's no integration here
            $this->add_element($mform, 'pireps');
        }

        // add primary elements
        $this->add_element($mform, 'flightdate', [$flightdate]);
        $this->add_element($mform, 'p1id', array($p1s, $p1id, $subscriber->trainingtype));
        $this->add_element($mform, 'p2id', array([$p2->get_id()=>$p2->get_name()], $p2id, $subscriber->trainingtype, $flighttype));

        // add primary flight time
        $this->add_element($mform, 'groundtime');
        $this->add_element($mform, 'flighttime');

        // add flight rule (VFR/IFR)
        $flightrule = $subscriber->trainingtype == "Dual" ? 'vfr' : 'ifr';
        $this->add_element($mform, 'flightrule', null, true, true, null, $flightrule);

        // add secondary form elements (more...)
        // add flight time elements based on training type
        $this->add_element($mform, 'ifrtime', null, false);

        // TODO: Instructor logentry edit: $this->add_element($mform, 'pictime', null, false);
        // TODO: Instructor logentry edit: $this->add_element($mform, 'instructortime', null, false);
        if ($subscriber->trainingtype == "Dual") {
            $this->add_element($mform, 'dualtime', null, false);
        } elseif ($subscriber->trainingtype == "Multicrew") {
            $this->add_element($mform, 'multipilottime', null, false);
            $this->add_element($mform, 'copilottime', null, false);
        }

        // add flight time elements in check flights
        $this->add_element($mform, 'checkpilottime', null, false);
        $this->add_element($mform, 'picustime', null, false);

        // default to 30 hr/mins from session time for departure and 1:30 hr/mins from session time for arrival
        $datetime = time();
        if (!empty($flightdate)) {
            $datetime = is_numeric($flightdate) ? $flightdate : strtotime($flightdate);
        }
        $defaultdepttime = logbook::convert_time(($datetime + (30 * 60)), 'TS_TO_TIME');
        $defaultarrttime = logbook::convert_time(($datetime + (90 * 60)), 'TS_TO_TIME');
        $this->add_element($mform, 'departure', array($subscriber->homeicao, $defaultdepttime), false);
        $this->add_element($mform, 'arrival', array($subscriber->homeicao, $defaultarrttime), false);
        $this->add_element($mform, 'aircraft', array($subscriber->aircrafticao, [
            'SE'=>get_string('logbooksedesc', 'local_booking'),
            'ME'=>get_string('logbookmedesc', 'local_booking')
            ], $this->get_enginetype($subscriber)), false);
        $this->add_element($mform, 'nighttime', null, false);
        // add route
        $this->add_element($mform, 'route', null, false);

        // add landings elements for new and edit logentries
        $this->add_element($mform, 'landingsp1', null, false);
        if ($newlogentry)
            $this->add_element($mform, 'landingsp2', null, false);

        // add remaining elements
        $this->add_element($mform, 'callsign', array($this->get_pilot_info('callsign', $subscriber->get_participant($p1id))), false);
        $this->add_element($mform, 'remarks', null, false);
        $this->add_element($mform, 'fstd', array($this->get_pilot_info('simulator', $subscriber->get_participant($flighttype != 'solo' ? $p2id : $p1id))), false);
    }

    /**
     * Set the passed element in the form.
     *
     * @param MoodleQuickForm $mform
     * @param string $element     The element to be set in the form
     * @param mixed  $options     Optional array of configuration data
     * @param bool   $maindisplay Whether to display the element in the main form
     * @param bool   $input       Whether the element is an input or static (label) element
     * @param mixed  $value       The value of a static element
     * @param mixed  $default     The default value
     */
    protected function add_element($mform, $element, $options = null, $maindisplay = true, $input = true, $value = null, $default = '') {

        switch ($element) {
            case 'exercises':
                // Exercises list including final exam (skill test)
                $mform->addElement('select', $element, get_string('exercise', 'local_booking'), $options[0]);
                $mform->addRule($element, get_string('required'), 'required', null, 'client');
                $mform->setType($element, PARAM_INT);
                break;

            case 'flightdate':
                // Flight date field.
                $mform->addElement('date_time_selector', $element, get_string($element, 'local_booking'), array('timezone'=>0));
                $mform->addRule($element, get_string('required'), 'required', null, 'client');
                $mform->setType($element, PARAM_TEXT);
                $mform->setDefault($element, $options[0]);
                break;

            case 'p1id':
                // P1 name select
                $select = $mform->addElement('select', $element, get_string('p1' . strtolower($options[2]), 'local_booking'), $options[0]);
                $select->setSelected($options[1]);
                $mform->setType($element, PARAM_INT);
                $mform->addRule($element, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($element, 'p1' . strtolower($options[2]), 'local_booking');
                $mform->freeze($element);
                break;

            case 'p2id':
                // P2 name select
                global $USER;
                $select = $mform->addElement('select', $element, get_string('p2' . strtolower($options[2]), 'local_booking'), $options[0]);
                $select->setSelected($options[1]);
                $mform->setType($element, PARAM_INT);
                // Check fo solo flights
                if ($options[3] != 'solo') {
                    $mform->addRule($element, get_string('required'), 'required', null, 'client');
                }
                $mform->addHelpButton($element, 'p2' . strtolower($options[2]), 'local_booking');
                if (!is_siteadmin($USER))
                    $mform->freeze($element);
                break;

            case 'flighttype':
                // Solo flight indicator
                $radioarray=array();
                $radioarray[] = $mform->createElement('radio', $element, '', get_string('flighttypetraining', 'local_booking'), 'training');
                $radioarray[] = $mform->createElement('radio', $element, '', get_string('flighttypesolo', 'local_booking'), 'solo');
                $mform->addGroup($radioarray, $element, get_string($element, 'local_booking'), array(' '), false);
                $mform->setType($element, PARAM_TEXT);
                $mform->setDefault($element, $default);
                // when adding from logbook page, show only if a training exercise is selected
                if ($options[0]) {
                    $mform->hideIf($element, 'exercises', 'eq', $options[1]);
                    $mform->hideIf($element, 'exercises', 'eq', '0');
                } else {
                    $mform->hideIf($element, 'flighttype', 'eq', 'check');
                    $mform->hideIf($element, 'trainingtype', 'noteq', 'Dual');
                }
                // freeze chaning flight type in a solo flight edit
                if ($options[2]) {
                    $mform->freeze('flighttype');
                }
                break;

            case 'passfail':
                // Solo flight indicator
                $radioarray=array();
                $radioarray[] = $mform->createElement('radio', $element, '', get_string('checkpassed', 'local_booking'), 'pass');
                $radioarray[] = $mform->createElement('radio', $element, '', get_string('checkfailed', 'local_booking'), 'fail');
                $mform->addGroup($radioarray, $element,  get_string('checkflight', 'local_booking'), array(' '), false);
                $mform->setType($element, PARAM_TEXT);
                $mform->setDefault($element, 'pass');
                // when adding from logbook page, show only if a training exercise is selected
                if ($options[0]) {
                    $mform->hideIf($element, 'exercises', 'noteq', $options[1]);
                } else {
                    $mform->hideIf($element, 'flighttype', 'noteq', 'check');
                }
                break;

            case 'groundtime':
                // Session flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addRule($element, get_string('required'), 'required', null, 'client');
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->setType($element, PARAM_TEXT);
                break;

            case 'flighttime':
                // Flight time duration
                if ($input) {
                    $attributes = array('size' => '5', 'placeholder' => 'hh:mm');
                    $mform->addElement('text', $element, ucfirst(get_string($element, 'local_booking')), $attributes);
                    $mform->addRule($element, get_string('required'), 'required', null, 'client');
                    $mform->addHelpButton($element, $element, 'local_booking');
                    $mform->setType($element, PARAM_TEXT);
                } else {
                    $mform->addElement('static', 'description', get_string($element, 'local_booking'), $value);
                }
                break;

            case 'flightrule':
                // Solo flight indicator
                $radioarray=array();
                $radioarray[] = $mform->createElement('radio', $element, '', get_string('flightrulevfr', 'local_booking'), 'vfr');
                $radioarray[] = $mform->createElement('radio', $element, '', get_string('flightruleifr', 'local_booking'), 'ifr');
                $mform->addGroup($radioarray, $element, get_string($element, 'local_booking'), array(' '), false);
                $mform->setType($element, PARAM_TEXT);
                $mform->setDefault($element, $default);
                break;

            case 'pictime':
                // PIC flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                break;

            case 'instructortime':
                // Instructor flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                break;

            case 'checkpilottime':
                // Examiner flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->hideIf($element, 'passfail', 'noteq', 'pass');
                break;

            case 'picustime':
                // PICUS flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                break;

            case 'dualtime':
                // Dual flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->setType($element, PARAM_TEXT);
                $mform->hideIf($element, 'trainingtype', 'noteq', 'Dual');
                break;

            case 'multipilottime':
                // Multi-pilot flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->hideIf($element, 'trainingtype', 'noteq', 'Multicrew');
                break;

            case 'copilottime':
                // Co-pilot flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->hideIf($element, 'trainingtype', 'noteq', 'Multicrew');
                break;

            case 'pireps':
                // PIREPs group P1 & P2
                // Show integrated PIREP lookup (options[0])
                if ($options[0]) {
                    // Show P1 PIREP vs PIREP depending on whether it's a new entry or not (options[1])
                    $mform->addElement('text', 'p1pirep', get_string(($options[1] ? 'instpirep' : 'pirep'), 'local_booking'),
                        ($options[1] ? 'placeholder="' . get_string('pireplabel', 'local_booking') . '"' : ''));
                    $mform->addRule('p1pirep', get_string('err_numeric', 'form'), 'numeric', null, 'client');
                    if ($options[1])
                        $mform->addHelpButton('p1pirep', 'instpirep', 'local_booking');
                } else {
                    $pireps=array();
                    $pireps[] =& $mform->createElement('text', 'p1pirep', get_string('p1pirep', 'local_booking'));
                    $pireps[] =& $mform->createElement('text', 'p2pirep', get_string('p2pirep', 'local_booking'));
                    $mform->addGroup($pireps, $element, get_string('pirepsgroup', 'local_booking'), '   ', false);
                    $mform->addGroupRule($element, array('p1pirep' => array(array(get_string('err_numeric', 'form'), 'numeric', null, 'client'))));
                    $mform->addGroupRule($element, array('p2pirep' => array(array(get_string('err_numeric', 'form'), 'numeric', null, 'client'))));
                    $mform->setType('p2pirep', PARAM_INT);
                }
                $mform->setType('p1pirep', PARAM_INT);
                break;

            case 'departure':
                // Departure group ICAO/time
                $departure=array();
                $departure[] =& $mform->createElement('text', 'depicao', get_string('depicao', 'local_booking'), 'style="text-transform:uppercase" size="8"');
                $departure[] =& $mform->createElement('text', 'deptime', get_string('deptime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addGroup($departure, $element, get_string('depgroup', 'local_booking'), ' ', false);
                $mform->setDefault('depicao', $options[0]);
                $mform->setDefault('deptime', $options[1]);
                $mform->setType('depicao', PARAM_TEXT);
                $mform->setType('deptime', PARAM_TEXT);
                break;

            case 'arrival':
                // Arrival group ICAO/time
                $arrival=array();
                $arrival[] =& $mform->createElement('text', 'arricao', get_string('arricao', 'local_booking'), 'style="text-transform:uppercase" size="8"');
                $arrival[] =& $mform->createElement('text', 'arrtime', get_string('arrtime', 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->addGroup($arrival, $element, get_string('arrgroup', 'local_booking'), ' ', false);
                $mform->setDefault('arricao', $options[0]);
                $mform->setDefault('arrtime', $options[1]);
                $mform->setType('arricao', PARAM_TEXT);
                $mform->setType('arrtime', PARAM_TEXT);
                break;

            case 'aircraft':
                // Aircraft group type, registration, and engine type
                $aircraft=array();
                $aircraft[] =& $mform->createElement('select', $element, get_string($element, 'local_booking'), $options[0]);
                $aircraft[] =& $mform->createElement('text', 'aircraftreg', get_string('aircraftreg', 'local_booking'), 'style="text-transform:uppercase" size="8"');
                $aircraft[] =& $mform->createElement('select', 'enginetype', get_string('enginetype', 'local_booking'), $options[1]);
                $mform->setDefault('enginetype', $options[2]);
                $mform->addGroup($aircraft, $element, get_string('aircraftgroup', 'local_booking'), ' ', false);
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->setType('aircraftreg', PARAM_TEXT);
                break;

            case 'nighttime':
                // Night flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->hideIf($element, 'flightrule', 'noteq', 'ifr');
                break;

            case 'ifrtime':
                // IFR flight time duration
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'size="5" placeholder="hh:mm"');
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->hideIf($element, 'flightrule', 'noteq', 'ifr');
                break;

            case 'landingsp1':
            case 'landingsp2':
                // Landings group Day/Night
                $landings=array();
                $landings[] =& $mform->createElement('text', $element . 'day', '', 'size="3"');
                $landings[] =& $mform->createElement('text', $element . 'night', '', 'size="3"');
                $mform->addGroup($landings, $element, get_string($element, 'local_booking'), '   ', false);
                $mform->addGroupRule($element, array($element . 'day' => array(array(get_string('errorlandings', 'local_booking'), 'numeric', null, 'client'))));
                $mform->addGroupRule($element, array($element . 'night' => array(array(get_string('errorlandings', 'local_booking'), 'numeric', null, 'client'))));
                $mform->setType($element . 'day', PARAM_INT);
                $mform->setDefault($element . 'day', $element=='landingsp2' ? 1 : null);
                $mform->setType($element . 'night', PARAM_INT);
                // $mform->setDefault($element . 'night', 0);
                $mform->addHelpButton($element, $element, 'local_booking');
                break;

            case 'callsign':
                // Callsign advanced element
                $mform->addElement('text', $element, get_string($element, 'local_booking'), 'style="text-transform:uppercase" ');
                $mform->setType($element, PARAM_TEXT);
                $mform->setDefault($element, $options[0]);
                break;

            case 'route':
                // Route advanced element
                $mform->addElement('textarea', $element, get_string($element, "local_booking"), 'wrap="virtual" rows="5" cols="50"');
                $mform->setType($element, PARAM_TEXT);
                break;

            case 'remarks':
                // Remarks advanced element
                $remarks = get_string($element, "local_booking").'<br/>(1000 characters)';
                $mform->addElement('textarea', $element, $remarks, 'wrap="virtual" rows="20" cols="50"');
                $mform->setType($element, PARAM_TEXT);
                break;

            case 'fstd':
                // FSTD advanced element
                $mform->addElement('text', $element, get_string($element, 'local_booking'));
                $mform->setType($element, PARAM_TEXT);
                $mform->addHelpButton($element, $element, 'local_booking');
                $mform->setDefault($element, $options[0]);
                break;
        }

        // check if the element to be hidden in Advanced section
        if (!$maindisplay)
            $mform->setAdvanced($element);
}

    /**
     * Add the list of hidden elements that should appear in this form each
     * time. These elements will never be visible to the user.
     *
     * @param MoodleQuickForm $mform The mini form
     * @param string $trainingtype   The type of training (Dual or Multi-crew)
     * @param string $flighttype     The flight type (training, solo, or check)
     */
    protected function add_default_hidden_elements($mform, string $trainingtype, string $flighttype, int $graduationexerciseid) {

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'linkedlogentryid');
        $mform->setType('linkedlogentryid', PARAM_INT);
        $mform->setDefault('linkedlogentryid', 0);

        $mform->addElement('hidden', 'linkedpirep');
        $mform->setType('linkedpirep', PARAM_INT);
        $mform->setDefault('linkedpirep', 0);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', 0);

        $mform->addElement('hidden', 'exerciseid');
        $mform->setType('exerciseid', PARAM_INT);
        $mform->setDefault('exerciseid', 0);

        $mform->addElement('hidden', 'graduationexerciseid');
        $mform->setType('graduationexerciseid', PARAM_INT);
        $mform->setDefault('graduationexerciseid', $graduationexerciseid);

        $mform->addElement('hidden', 'sessionid');
        $mform->setType('sessionid', PARAM_INT);
        $mform->setDefault('sessionid', 0);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', 0);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setDefault('contextid', 0);

        $mform->addElement('hidden', 'flighttypehidden');
        $mform->setType('flighttypehidden', PARAM_TEXT);
        $mform->setDefault('flighttypehidden', $flighttype);

        $mform->addElement('hidden', 'trainingtype');
        $mform->setType('trainingtype', PARAM_TEXT);
        $mform->setDefault('trainingtype', $trainingtype);

        $mform->addElement('hidden', 'visible');
        $mform->setType('visible', PARAM_INT);
        $mform->setDefault('visible', 1);
    }

    /**
     * Get pilot information depending on the profile
     * information requested.
     *
     * @param string      $infotype The type of pilot information
     * @param participant $pilot    The pilot user
     * @return string    $callsign The instructor's callsign
     */
    protected function get_pilot_info($infotype, $pilot) {
        $info = '';

        switch ($infotype) {
            case 'callsign':
                $info = $pilot->get_callsign();
                break;
            case 'simulator':
                $info = $pilot->get_simulator();
                break;
        }
        return $info;
    }

    /**
     * Get the engine type of the default aircraft
     *
     * @param subscriber $course   The subscriber course
     * @return array $enginetype The engine type of the default aircraft
     */
    protected function get_enginetype(subscriber $course) {
        $enginetype = 'SE';

        if (!empty($course->aircrafticao)) {
            $aircrafts = (new ArrayObject($course->aircrafticao))->getIterator();
            $aircrafticao = $aircrafts->current();

            if ($course->has_integration('external_data', 'aircraft')) {
                $engintyperec = $course->get_external_data('aircraft', 'aircraftinfo', $aircrafticao);
                $enginetype = $engintyperec['engine_type'] == 'single' ? 'SE' : 'ME';
            }
        }

        return $enginetype;
    }
}
