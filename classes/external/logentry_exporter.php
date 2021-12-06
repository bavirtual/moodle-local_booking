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
use DateTime;
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
    public function __construct($data, $logentry = null, $related = []) {
        $this->logentry = $logentry;

        if (!empty($logentry)) {
            // convert flight time duration, session duration, and
            // solo flight duration to their equivalent int values
            $data['flighttimemins'] = $logentry->get_flighttimemins();
            $data['sessiontimemins'] = $logentry->get_sessiontimemins();
            $data['soloflighttimemins'] = !empty($data['soloflighttimemins']) ? $logentry->get_soloflighttimemins() : 0;

            // process data for new and edit logbook entry
            if (empty($logentry->get_id())) {
                // convert sessiondate to timestamp format
                $sessiondatestr = $data['sessiondate']['month'] . '/' .
                $data['sessiondate']['day'] . '/' .
                $data['sessiondate']['year'];
                $sessiondate = DateTime::createFromFormat('m/d/Y', $sessiondatestr);
            } else {
                $sessiondate = new DateTime('@' . $logentry->get_sessiondate());
                $data += $logentry->__toArray();
                $data['url'] = new moodle_url('/booking/view', ['courseid'=>$data['courseid']]);
            }
            $data['sessiondate'] = $sessiondate->getTimestamp();
        }
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
            'sessiondate' => [
                'type' => PARAM_INT
            ],
            'flighttimemins' => [
                'type' => PARAM_RAW
            ],
            'sessiontimemins' => [
                'type' => PARAM_INT
            ],
            'soloflighttimemins' => [
                'type' => PARAM_INT
            ],
            'aircraft' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'pirep' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'callsign' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'fromicao' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'toicao' => [
                'type' => PARAM_RAW,
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
                'type' => PARAM_RAW
            ],
            'formattedtime' => [
                'type' => PARAM_RAW
            ],
            'flighttime' => [
                'type' => PARAM_RAW
            ],
            'sessiontime' => [
                'type' => PARAM_RAW
            ],
            'soloflighttime' => [
                'type' => PARAM_RAW
            ],
            'picname' => [
                'type' => PARAM_RAW
            ],
            'sicname' => [
                'type' => PARAM_RAW
            ],
            'sectionname' => [
                'type' => PARAM_RAW
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
        $sessiondate = !empty($this->logentry) ? $this->logentry->get_sessiondate(true) : $this->data['sessiondate'];
        $flighttimemins = !empty($this->logentry) ? $this->logentry->get_flighttimemins(false) : $this->data['flighttimemins'];
        $sessiontimemins = !empty($this->logentry) ? $this->logentry->get_sessiontimemins(false) : $this->data['sessiontimemins'];
        $soloflighttimemins = !empty($this->logentry) ? $this->logentry->get_soloflighttimemins(false) : $this->data['soloflighttimemins'];
        $picid = !empty($this->logentry) ? $this->logentry->get_picid() : $this->data['picid'];
        $sicid = !empty($this->logentry) ? $this->logentry->get_sicid() : $this->data['sicid'];
        $sectionname = !empty($this->logentry) ? '' : subscriber::get_section_name($this->data['courseid'], $exerciseid);

        return [
            'exercisename' => subscriber::get_exercise_name($exerciseid),
            'formattedtime' => $sessiondate,
            'flighttime' => $flighttimemins,
            'sessiontime' => $sessiontimemins,
            'soloflighttime' => $soloflighttimemins,
            'picname' => participant::get_fullname($picid),
            'sicname' => participant::get_fullname($sicid),
            'sectionname' => $sectionname,
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
