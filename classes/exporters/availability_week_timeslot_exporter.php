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
 * Contains timeslot class for displaying the time slots in
 * the availability calendar week view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\exporters;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use DateTime;
use renderer_base;

/**
 * Class for displaying each timeslot in the day of the week view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_week_timeslot_exporter extends exporter {

    /**
     * @var \calendar_information $calendar The calendar to be rendered.
     */
    protected $calendar;

    /**
     * @var array $timeslot - A timeslot for the work_exporter objects.
     */
    protected $timeslot;

    /**
     * @var array $usertimeslot - The user local time timeslot in the user timezone.
     */
    protected $usertimeslot;

    /**
     * @var array $weeklanes - The array containing week day lanes of slots.
     */
    protected $weeklanes;

    /**
     * @var int $hour - A timeslot hour for the work_exporter objects.
     */
    protected $hour;

    /**
     * Constructor.
     *
     * @param \calendar_information $calendar The calendar information for the period being displayed
     * @param array $data       Data needed to process global values
     * @param array $related Related objects.
     */
    public function __construct(\calendar_information $calendar, $data, $related) {
        $this->calendar      = $calendar;
        $this->weeklanes     = $related['timeslotdata']->weeklanes;
        $this->timeslot      = $related['timeslotdata']->daydata->timeslot;
        $this->usertimeslot  = $related['timeslotdata']->daydata->usertimeslot;
        $this->hour          = $related['timeslotdata']->daydata->hour;

        parent::__construct($data, $related);
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'type' => '\core_calendar\type_base',
            'timeslotdata' => '\\stdClass',
        ];
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
                'type' => availability_week_day_exporter::read_properties_definition(),
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
        foreach ($this->data['daysdata'] as $daydata) {

            // get this day's data basedon GMT time
            $daytimestamp = $type->timestamp_to_date_array(gmmktime($this->hour, 0, 0, $daydata['mon'], $daydata['mday'], $daydata['year']));
            $daylanes = $this->weeklanes[$daydata['wday']];

            // get slots in all lanes even if a slot is empty (not posted by a student, booked, nor tentative)
            for ($laneindex = 0; $laneindex < $this->data['maxlanes'] && $laneindex < LOCAL_BOOKING_MAXLANES; $laneindex++) {
                // assign the lane slots to the corresponding day lane
                $laneslots = count($daylanes) > $laneindex ? $daylanes[$laneindex] : null;

                $data = [
                    'courseid'     => $this->calendar->course->id,
                    'istoday'      => $this->is_today($daydata),
                    'isweekend'    => $this->is_weekend($daydata),
                    'daytitle'     => get_string('strftimedayshort'),
                    'slotavailable'=> !$daydata['restricted'] && !$this->data['alreadybooked'],
                    'slot'         => $this->get_slot($laneslots, $daytimestamp),
                    'timestamp'    => $daytimestamp[0],
                    'groupview'    => $this->data['groupview']
                ];

                $day = new availability_week_day_exporter( $data, $this->related);

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
    protected function get_slot($studentslots, $weekdate) {
        $slot = null;
        // loop through week's timeslots to see if the slot marked by student
        if (!empty($studentslots)) {
            foreach ($studentslots as $savedslot) {
                if ($weekdate[0] >= intval($savedslot->starttime)  && $weekdate[0] < intval($savedslot->endtime)) {
                    $slot = $savedslot;
                }
            }
        }

        return $slot;
    }

    /**
     * Checks if the date is today.
     *
     * @param  array The date to compare against
     * @return bool
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
        $numberofdaysinweek = count($this->data['daysdata']);

        return !!($weekend & (1 << ($date['wday'] % $numberofdaysinweek)));
    }
}
