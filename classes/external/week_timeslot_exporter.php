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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use DateTime;
use local_booking\local\participant\entities\student;
use renderer_base;

/**
 * Class for displaying each timeslot in the day of the week view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class week_timeslot_exporter extends exporter {

    /**
     * @var \calendar_information $calendar The calendar to be rendered.
     */
    protected $calendar;

    /**
     * @var student $student - The student id for the view.
     */
    protected $student;

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
     * @var array $maxlanes - The maximum amount of lanes required to fit in a day.
     */
    protected $maxlanes;

    /**
     * @var int $hour - A timeslot hour for the work_exporter objects.
     */
    protected $hour;

    /**
     * @var array $days - An array of day_exporter objects.
     */
    protected $days = [];

    /**
     * @var array $groupview - The type of UI view being requested.
     */
    protected $groupview;

    /**
     * @var string $bookview - The type of booking UI view being requested.
     */
    protected $bookview;

    /**
     * @var bool $alreadybooked - Whether the student is already booked by another instructor already.
     */
    protected $alreadybooked;

    /**
     * Constructor.
     *
     * @param \calendar_information $calendar The calendar information for the period being displayed
     * @param array $data       Data needed to process global values
     * @param array $daydata    Data needed to process day export
     * @param array $weeklanes  Contains the week lanes information
     * @param array $related Related objects.
     */
    public function __construct(\calendar_information $calendar, $data, $daydata, $weeklanes, $related) {
        $this->calendar      = $calendar;
        $this->weeklanes     = $weeklanes;
        $this->student       = $data['student'];
        $this->days          = $data['days'];
        $this->groupview     = $data['groupview'];
        $this->bookview      = $data['bookview'];
        $this->maxlanes      = $data['maxlanes'];
        $this->alreadybooked = $data['alreadybooked'];
        $this->timeslot      = $daydata['timeslot'];
        $this->usertimeslot  = $daydata['usertimeslot'];
        $this->hour          = $daydata['hour'];

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
            $resticted = $this->day_restricted($daydata);

            // get slots in all lanes even if a slot is empty (not posted by a student, booked, nor tentative)
            for ($laneindex = 0; $laneindex < $this->maxlanes && $laneindex < LOCAL_BOOKING_MAXLANES; $laneindex++) {
                // assign the lane slots to the corrsponding day lane
                $laneslots = count($daylanes) > $laneindex ? $daylanes[$laneindex] : null;

                $slotdaydata['istoday']     = $this->is_today($daydata);
                $slotdaydata['isweekend']   = $this->is_weekend($daydata);
                $slotdaydata['daytitle']    = get_string('dayeventsnone', 'calendar', userdate($daydata[0], get_string('strftimedayshort')));
                $slotdata['slotavailable']  = !$resticted && !$this->alreadybooked;
                $slotdata['slot']           = $this->get_slot_info($laneslots, $slotdaydata);

                $day = new week_day_exporter($this->calendar, $this->groupview, $slotdaydata, $slotdata, $this->related);

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
    protected function get_slot_info($studentslots, $weekdate) {
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
     * of week lookahead bounds for students
     *
     * @param  array $date array of the day to be evaluated
     * @return  bool
     */
    protected function day_restricted($date) {
        $now = $this->related['type']->timestamp_to_date_array(time());
        $today = $this->related['type']->timestamp_to_date_array(gmmktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']));

        // can't mark in the past, the day had passed i.e. yesterday
        $datepassed = true;
        $datepassed = $datepassed && $today['year'] >= $date['year'];
        $datepassed = $datepassed && $today['yday'] >= $date['yday'];

        // can't mark before x days from last booked session (durnig wait days) for student view
        $lastsessionwait = true;
        $hasrestrictionwaiver = false;
        if ($this->groupview || $this->bookview) {
            $lastsessionwait = false;
        } else {
            $hasrestrictionwaiver = (bool) get_user_preferences('local_booking_' . $this->calendar->courseid . '_availabilityoverride', false, $this->student->get_id());
            if (!$hasrestrictionwaiver) {
                $nextsessiondt = $this->student->get_next_allowed_session_date();
                $nextsessiondate = $this->related['type']->timestamp_to_date_array($nextsessiondt->getTimestamp());
                $lastsessionwait = $lastsessionwait && $nextsessiondate['year'] >= $date['year'];
                $lastsessionwait = $lastsessionwait && $nextsessiondate['yday'] >= $date['yday'];
            } else {
                $lastsessionwait = !$hasrestrictionwaiver;
            }
        }

        // future week is not beyond the set lookahead number of weeks
        $currentyearweekno = (int)date('W', time());
        $futureyearweekno = (int)date('W', $date[0]);
        $weekslookahead = (get_config('local_booking', 'weeksahead')) ? get_config('local_booking', 'weeksahead') : LOCAL_BOOKING_WEEKSLOOKAHEAD;
        $yeardate = new DateTime();
        $yeardate->setISODate($today['year'], 53);
        $yearweeks = ($yeardate->format("W") === "53" ? 53 : 52);
        $beyondlookahead = (($futureyearweekno + (($date['year'] - $today['year']) * $yearweeks)) - $currentyearweekno ) > $weekslookahead;

        // lookahead setting is not unlimited
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
