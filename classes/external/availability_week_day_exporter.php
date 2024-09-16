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
 * Contains week day class for displaying the day in availability week view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\participant\entities\student;
use renderer_base;

/**
 * Class for displaying the day on month view.
 *
 * @package   core_calendar
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_week_day_exporter extends availability_day_exporter {

    /**
     * @var {object} $slotdata dataobjects.
     */
    protected $slotdata;

    /**
     * @var bool $groupview Whether this is a single or all group view.
     */
    protected $groupview;

    /**
     * Constructor.
     *
     * @param \calendar_information $calendar The calendar information for the period being displayed
     * @param mixed $data Either an stdClass or an array of values.
     * @param array $related Related objects.
     */
    public function __construct(\calendar_information $calendar, $groupview, $data, $slot, $related) {
        parent::__construct($calendar, $data, $related);
        // Fix the url for today to be based on the today timestamp
        // rather than the calendar_information time set in the parent
        // constructor.
        $this->url->param('time', $this->data[0]);
        $this->slotdata     = $slot;
        $this->groupview    = $groupview;
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        $return = parent::define_properties();
        $return = array_merge($return, [
            // These are additional params.
            'istoday' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'isweekend' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'daytitle' => [
                'type' => PARAM_RAW,
            ],
        ]);

        return $return;
    }
    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        $return = parent::define_other_properties();
        $return = array_merge($return, [
            'slotavailable' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'slotmarked' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'slotbooked' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'slotstatus' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'slotstatustooltip' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'slotcolor' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'groupview' => [
                'type' => PARAM_BOOL,
                'default' => '',
            ],
        ]);

        return $return;
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {

        // Check if there is a slot that matches
        $slotstatus = '';
        $slotstatustooltip = '';
        if ($this->slotdata['slot'] != null) {
            $slotstatus = $this->slotdata['slot']->slotstatus ?: 'selected';
            // add student name tooltip in group view
            if ($this->groupview) {
                $studentname = student::get_fullname($this->slotdata['slot']->userid);
                $slotstatustooltip = $studentname . '<br/>';
            }
            $slotstatustooltip .= $this->slotdata['slot']->bookinginfo ?: '';

        }

        $return = parent::get_other_values($output);

        $return['slotavailable']    = $this->slotdata['slotavailable'];
        $return['slotmarked']       = $this->slotdata['slot'] != null;
        $return['slotbooked']       = $this->slotdata['slot'] != null ? !empty($this->slotdata['slot']->slotstatus) : false;
        $return['slotstatus']       = $slotstatus;
        $return['slotstatustooltip']= $slotstatustooltip;
        $return['groupview']        = $this->groupview;
        $return['slotcolor']        = $this->slotdata['slot'] != null ? $this->slotdata['slot']->slotcolor : 'white';

        return $return;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'type' => '\core_calendar\type_base',
        ];
    }
}
