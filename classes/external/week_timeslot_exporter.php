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
 * Contains event class for displaying the day on month view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use DateTime;
use renderer_base;

/**
 * Class for displaying the day on month view.
 *
 * @package   local_booking
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class week_timeslot_exporter extends exporter {

    /**
     * @var \calendar_information $calendar The calendar to be rendered.
     */
    protected $calendar;

    /**
     * @var array $timeslot - A timeslot for the work_exporter objects.
     */
    protected $timeslot;

    /**
     * @var int $studentid - The student id for the view.
     */
    protected $studentid;

    /**
     * @var array $usertimeslot - The user local time timeslot in the user timezone.
     */
    protected $usertimeslot;

    /**
     * @var array $weeklanes - The array containing week day lanes of slots.
     */
    protected $weeklanes;

    /**
     * @var array $maxlanes - The maximum amount of lanes required to fit in a day.
     */
    protected $maxlanes;

    /**
     * @var array $hour - A timeslot hour for the work_exporter objects.
     */
    protected $hour;

    /**
     * @var array $days - An array of day_exporter objects.
     */
    protected $days = [];

    /**
     * Constructor.
     *
     * @param \calendar_information $calendar The calendar information for the period being displayed
     * @param mixed $data Either an stdClass or an array of values.
     * @param array $related Related objects.
     */
    public function __construct(\calendar_information $calendar, $data, $weeklanes, $related) {
        $this->calendar      = $calendar;
        $this->weeklanes     = $weeklanes;
        $this->timeslot      = $data['timeslot'];
        $this->studentid     = $data['studentid'];
        $this->usertimeslot  = $data['usertimeslot'];
        $this->hour          = $data['hour'];
        $this->days          = $data['days'];
        $this->groupview     = $data['groupview'];
        $this->maxlanes      = $data['maxlanes'];


        parent::__construct([], $related);

    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            // These are additional params.
            'timeslot' => [
                'type' => PARAM_RAW,
            ],
            'localtimeslot' => [
                'type' => PARAM_RAW,
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
            'days' => [
                'type' => week_day_exporter::read_properties_definition(),
                'multiple' => true,
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

        $return = [
            'days'          => $this->get_days($output),
            'timeslot'      => $this->timeslot,
            'localtimeslot' => $this->usertimeslot,
        ];

        return $return;
    }

    /**
     * Get the list of days
     * of the week.
     *
     * @return  $days[]
     */
    protected function get_days($output) {
        $days = [];
        $type = $this->related['type'];

        // get the days and their slots in each hour timeslot
        foreach ($this->days as $daydata) {
            // get this day's data basedon GMT time
            $slotdaydata = $type->timestamp_to_date_array(gmmktime($this->hour, 0, 0, $daydata['mon'], $daydata['mday'], $daydata['year']));
            $daylanes = $this->weeklanes[$daydata['wday']];

            // get slots in all lanes even if they're empty
            for ($laneindex = 0; $laneindex < $this->maxlanes && $laneindex < LOCAL_BOOKING_MAXLANES; $laneindex++) {
                // assign the lane slots to the corrsponding day lane
                $laneslots = count($daylanes) > $laneindex ? $daylanes[$laneindex] : null;

                $slotdaydata['istoday']     = $this->is_today($daydata);
                $slotdaydata['isweekend']   = $this->is_weekend($daydata);
                $slotdaydata['daytitle']    = get_string('dayeventsnone', 'calendar', userdate($daydata[0], get_string('strftimedayshort')));
                $slotdata['slotavailable']  = !$this->is_slot_unavailable($daydata);
                $slotdata['slot']           = $this->getSlotinfo($laneslots, $slotdaydata);

                $day = new week_day_exporter($this->calendar, $this->groupview, $slotdaydata, $slotdata, [
                    'type' => $this->related['type'],
                ]);

                $days[] = $day->export($output);
            }
        }

        return $days;
    }

    /**
     * Get the slot with timestamp falling on the week date.
     *
     * @return  {object}    Database record representing the slot record
     */
    protected function getSlotinfo($studentslots, $weekdate) {
        $slot = null;
        // loop through week's timeslots to see if the slot marked by student
        if (!empty($studentslots)) {
            foreach ($studentslots as $savedslot) {
                if ($weekdate[0] >= intval($savedslot->starttime)  && $weekdate[0] <= intval($savedslot->endtime)) {
                    $slot = $savedslot;
                }
            }
        }

        return $slot;
    }

    /**
     * Checks if the slot date is out
     * of week lookahead bounds
     *
     * @return  bool
     */
    protected function is_slot_unavailable($date) {
        $now = $this->related['type']->timestamp_to_date_array(time());
        $today = $this->related['type']->timestamp_to_date_array(gmmktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']));

        // can't mark in the past, the day had passed
        $datepassed = true;
        $datepassed = $datepassed && $today['year'] >= $date['year'];
        $datepassed = $datepassed && $today['yday'] >= $date['yday'];

        // can't mark before x days from last booked session. x set in settings
        $lastsessionwait = true;
        if (!$this->groupview) {
            $nextsessiondt = get_next_allowed_session_date($this->studentid);
            $nextsessiondate = $this->related['type']->timestamp_to_date_array($nextsessiondt->getTimestamp());
            $lastsessionwait = $lastsessionwait && $nextsessiondate['year'] >= $date['year'];
            $lastsessionwait = $lastsessionwait && $nextsessiondate['yday'] >= $date['yday'];

        } else {
            $lastsessionwait = false;
        }

        // future week is not beyond the set lookahead number of weeks
        $currentyearweekno = strftime('%W', time());
        $futureyearweekno = strftime('%W', $date[0]);
        $weekslookahead = (get_config('local_booking', 'weeksahead')) ? get_config('local_booking', 'weeksahead') : LOCAL_BOOKING_WEEKSLOOKAHEAD;
        $yeardate = new DateTime();
        $yeardate->setISODate($today['year'], 53);
        $yearweeks = ($yeardate->format("W") === "53" ? 53 : 52);
        $beyondlookahead = (($futureyearweekno + (($date['year'] - $today['year']) * $yearweeks)) - $currentyearweekno ) > $weekslookahead;

        // lookaahead setting is not unlimited
        $unlimited = $weekslookahead == 0;

        return $datepassed || $lastsessionwait || ($beyondlookahead && !$unlimited);
    }

    /**
     * Checks if the date
     * is today.
     *
     * @param   int     The date to compare against
     * @return  bool
     */
    protected function is_today($date) {
        $today = $this->related['type']->timestamp_to_date_array(time());
        $istoday = true;
        $istoday = $istoday && $today['year'] == $date['year'];

        return $istoday && $today['yday'] == $date['yday'];
    }

    /**
     * Checks if the date
     * is today.
     *
     * @return  bool
     */
    protected function is_weekend($date) {
        global $CFG;

        $weekend = CALENDAR_DEFAULT_WEEKEND;
        if (isset($CFG->calendar_weekend)) {
            $weekend = intval($CFG->calendar_weekend);
        }
        $numberofdaysinweek = count($this->days);

        return !!($weekend & (1 << ($date['wday'] % $numberofdaysinweek)));
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