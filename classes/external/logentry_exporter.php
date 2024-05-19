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
 * Class for displaying logbook entries.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;
use core\external\exporter;
use local_booking\local\participant\entities\participant;

/**
 * Class for displaying a logbook entry.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logentry_exporter extends exporter {

    /**
     * @var logentry_interface $logentry
     */
    protected $logentry;

    /**
     * Constructor.
     *
     * @param array $data       The form data.
     * @param array $related    The related data.
     */
    public function __construct($data, $related = []) {
        $this->logentry = $data['logentry'];
        $nullable = !isset($data['nullable']) || $data['nullable'];

        // add logentry properties to the exporter's data and remove the exiting logentry object. Maintain scalar flightdate.
        $logentrydata = $this->logentry->__toArray(isset($data['view']) && $data['view'] == 'summary', $nullable, (!empty($data['shortdate'])?:false));
        $data += $logentrydata;
        $data['flightdate'] = $logentrydata['flightdate'];
        unset($data['logentry']);

        $data['url'] = new moodle_url('/booking/view', ['courseid'=>$data['courseid']]);
        $data['visible'] = 1;

        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT
            ],
            'courseid' => [
                'type' => PARAM_INT
            ],
            'exerciseid' => [
                'type' => PARAM_INT
            ],
            'userid' => [
                'type' => PARAM_INT
            ],
            'flightdate' => [
                'type' => PARAM_RAW
            ],
            'groundtime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'flighttime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'pictime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'dualtime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'picustime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'instructortime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'multipilottime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'copilottime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'totalsessiontime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'checkpilottime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'aircraft' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'aircraftreg' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'enginetype' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'pirep' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'linkedpirep' => [
                'type' => PARAM_TEXT,
                'optional' => true,
                'default' => '',
            ],
            'callsign' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'depicao' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'arricao' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'deptime' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'arrtime' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'route' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'landingsday' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'landingsnight' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'nighttime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'ifrtime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'fstd' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'remarks' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'flighttype' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'trainingtype' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'hasfindpirep' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => false,
            ],
            'se' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'me' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'isstudent' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => true,
            ],
            'isinstructor' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => true,
            ],
            'canedit' => [
                'type' => PARAM_BOOL,
                'default' => true
            ],
            'visible' => [
                'type' => PARAM_INT
            ],
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'exercisename' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'formattedtime' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'p1label' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'p2label' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'p1name' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'p2name' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'sectionname' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'trainingflight' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'dualops' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => true,
            ],
            'soloflight' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => false,
            ],
            'checkflight' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => false,
            ],
            'passedcheck' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => true,
            ],
            'haspictime' => [
                'type' => PARAM_BOOL,
                'optional' => true,
                'default' => false,
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        global $COURSE;

        $exerciseid = !empty($this->logentry) ? ($this->logentry->get_exerciseid() ?: $this->data['exerciseid']) : $this->data['exerciseid'];
        $flightdate = !empty($this->logentry) ? $this->logentry->get_flightdate(isset($this->data['view']) && $this->data['view'] == 'summary') : $this->data['flightdate'];
        $p1id = !empty($this->logentry) ? $this->logentry->get_p1id() : $this->data['p1id'];
        $p2id = !empty($this->logentry) ? $this->logentry->get_p2id() : $this->data['p2id'];
        $sectionname = !empty($this->logentry) ? '' : array_values($COURSE->subscriber->get_lesson_by_exerciseid($exerciseid))[1];
        $dualops = $this->data['trainingtype'] == 'Dual';
        $haspictime = !empty($this->logentry) ? !empty($this->logentry->get_pictime()) : false;
        $flighttype = !empty($this->logentry) ? $this->logentry->get_flighttype() : 'training';
        $p2label = $dualops ? get_string('p2dual', 'local_booking') : get_string('p2multicrew', 'local_booking');

        // get training flight text
        $passed = false;
        $trainingflight = '';

        if (!empty($this->logentry)) {
            switch ($this->logentry->get_flighttype()) {
                case 'training':
                    $trainingflight = $this->data['courseshortname'] . ' ' . get_string('flighttraining', 'local_booking');
                    $p1label = $dualops ? get_string('p1dual', 'local_booking') : get_string('p1multicrew', 'local_booking') . '&#09;';
                    break;
                case 'solo':
                    $trainingflight = $this->data['courseshortname'] . ' ' . get_string('flightsolo', 'local_booking');
                    $p1label = get_string('p1solo', 'local_booking');
                    break;
                case 'check':
                    $trainingflight = $this->data['courseshortname'] . ' ' . ($dualops ? get_string('flightttestdual', 'local_booking') : get_string('flightttestmc', 'local_booking'));
                    $passed = !empty($this->logentry) ? !empty($this->logentry->get_picustime()) || !empty($this->logentry->get_checkpilottime()) : false;
                    $p1label = $dualops ? get_string('examiner', 'local_booking') : get_string('p1multicrew', 'local_booking') . '&#09;';

                    break;
            }
        }

        return [
            'exercisename' => $COURSE->subscriber->get_exercise_name($exerciseid, $this->logentry->get_courseid()),
            'formattedtime' => $flightdate,
            'p1label' => $p1label,
            'p2label' => $p2label,
            'p1name' => !empty($p1id) ? participant::get_fullname($p1id) : '',
            'p2name' => !empty($p2id) ? participant::get_fullname($p2id) : '',
            'sectionname' => $sectionname,
            'trainingflight' => $trainingflight,
            'dualops' => $dualops,
            'soloflight' => $flighttype == 'solo',
            'checkflight' => $flighttype == 'check',
            'passedcheck' => $passed,
            'haspictime' => $haspictime,
        ];
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context'=>'context',
        );
    }
}
