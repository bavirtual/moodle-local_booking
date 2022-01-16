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
use local_booking\local\subscriber\entities\subscriber;

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

        // add logentry properties to the exporter's data and remove the logentry object
        $data = $this->logentry->__toArray($data['view'] == 'summary', $nullable, (!empty($data['shortdate'])?:false)) + $data;
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
            'sessiontime' => [
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
            'totaltime' => [
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
            'enginetype' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'pirep' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'linkedpirep' => [
                'type' => PARAM_RAW,
                'optional' => true,
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
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'arrtime' => [
                'type' => PARAM_TEXT,
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
            'trainingtype' => [
                'type' => PARAM_TEXT,
                'optional' => true,
            ],
            'soloflight' => [
                'type' => PARAM_BOOL,
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
            'dualops' => [
                'type' => PARAM_BOOL,
                'default' => true,
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
        $exerciseid = !empty($this->logentry) ? $this->logentry->get_exerciseid() : $this->data['exerciseid'];
        $flightdate = !empty($this->logentry) ? $this->logentry->get_flightdate($this->data['view'] == 'summary') : $this->data['flightdate'];
        $p1id = !empty($this->logentry) ? $this->logentry->get_p1id() : $this->data['p1id'];
        $userid = !empty($this->logentry) ? $this->logentry->get_userid() : $this->data['userid'];
        $sectionname = !empty($this->logentry) ? '' : subscriber::get_section_name($this->data['courseid'], $exerciseid);
        $dualops = $this->data['trainingtype'] == 'Dual';

        return [
            'exercisename' => subscriber::get_exercise_name($exerciseid),
            'formattedtime' => $flightdate,
            'p1name' => !empty($p1id) ? participant::get_fullname($p1id) : '',
            'p2name' => !empty($userid) ? participant::get_fullname($userid) : '',
            'sectionname' => $sectionname,
            'dualops' => $dualops,
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
