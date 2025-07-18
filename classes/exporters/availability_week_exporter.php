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
 * Contains class for displaying the week view in the availability calendar.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\exporters;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use core_calendar\external\date_exporter;
use calendartype_gregorian\structure;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\session\entities\booking;
use renderer_base;
use moodle_url;
use DateTime;
use DateTimeZone;

/**
 * Class for displaying the week view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_week_exporter extends exporter {

    /**
     * @var \calendar_information $calendar The calendar to be rendered.
     */
    protected $calendar;

    /**
     * @var array $days An array of availability_week_timeslot_exporter objects.
     */
    protected $days = [];

    /**
     * @var array $maxlanes - The maximum amount of lanes required to fit in a day.
     */
    protected $maxlanes;

    /**
     * @var int $year The year based on the calendar's timestamp.
     */
    protected $year;

    /**
     * @var int $weekofyear A number representing the week of the year.
     */
    protected $weekofyear;

    /**
     * @var array $GMTdate An array of GMT date for the week.
     */
    protected $GMTdate;

    /**
     * @var int $firstdayofweek The first day of the week.
     */
    protected $firstdayofweek;

    /**
     * @var subscriber $course The course being viewed.
     */
    protected $course;

    /**
     * @var student $student The student where the week slots being viewed.
     */
    protected $student;

    /**
     * @var booking $activebooking The student's active booking if available.
     */
    protected $activebooking;

    /**
     * @var array $action The action data being performed.
     */
    protected $actiondata;

    /**
     * @var string $view The view type for the availability slot grid.
     */
    protected $view;

    /**
     * @var moodle_url $url The URL for the week page.
     */
    protected $url;

    /**
     * Constructor.
     *
     * @param array $actiondata associated with the booking action.
     * @param string $view the type of view (single student or all)
     * @param array $related Related objects.
     */
    public function __construct($actiondata, $related) {
        global $CFG, $USER;

        // Use core calendar and action
        $calendar = $related['calendar'];
        $type = $related['type'];
        $this->calendar = $calendar;
        $this->course = $related['subscriber'];
        $this->actiondata = $actiondata;
        $this->view = $actiondata['view'];

        // identify the student to view the slots (single student view 'user' or 'all' students)
        $activebookinginstrname = '';
        $studentposts = 0;
        $studentid = 0;
        $fullname = '';
        if ($actiondata['view'] == 'user') {
            $this->student = $actiondata['student'];
            if ($actiondata['action'] != 'book') {
                // push notification if the student is already booked
                if (!empty($this->student->get_active_booking()) && !$actiondata['confirm']) {
                    \core\notification::INFO(get_string('alreadybooked', 'local_booking'));
                }
            }

            // get student active booking and instructor id if exists
            if (!empty($this->student->get_active_booking())) {
                $this->activebooking = $this->student->get_active_booking();
                $this->activebooking = $activebookinginstrname = participant::get_fullname($this->activebooking->get_instructorid());
            }
        }

        if (!empty($this->student)) {
            $studentposts = $this->student->get_statistics()->get_total_posts();
            $studentid = $this->student->get_id();
            $fullname = $this->student->get_name();
        }

        // Get current week of the year and GMT date
        $this->weekofyear = (int)date('W', $this->calendar->time);
        $this->firstdayofweek = $type->get_starting_weekday();
        $calendarday = $type->timestamp_to_date_array($this->calendar->time);
        $this->GMTdate = $type->timestamp_to_date_array(gmmktime(0, 0, 0, $calendarday['mon'], $calendarday['mday'], $calendarday['year']));
        $this->days = $this->get_week_days($this->GMTdate, $type);

        // Update the url with time, course, and category info
        $this->url = new moodle_url('/local/booking/availability.php', [
            'time' => $calendar->time,
            'week' => $this->weekofyear,
            'year' => $calendarday['year'],
            'courseid' => $this->calendar->course->id
        ]);

        $exerciseid = $this->actiondata['exerciseid'];
        $data = [
            'url'         => $this->url->out(false),
            'username'    => $fullname,
            'posts'       => $studentposts,
            'action'      => $this->actiondata['action'],
            'studentid'   => $studentid,
            'exerciseid'  => $exerciseid,
            'exercisename'=> $this->course->get_exercise($exerciseid)->name,
            'confirm'     => $actiondata['confirm'],
            'editing'     => $this->view == 'user' && $this->actiondata['action'] != 'book',    // Editing is not allowed if user id is passed for booking
            'alreadybooked' => !empty($this->activebooking),
            'alreadybookedmsg' => !empty($activebookinginstrname) ? get_string('activebookingmsg', 'local_booking', $activebookinginstrname) : '',
            'minslotperiod' => $this->course->minslotperiod,
            'groupview'   => $this->view == 'all',                                              // Group view no editing or booking buttons
            'viewallurl'  => $CFG->httpswwwroot . '/local/booking/availability.php?courseid=' . $this->calendar->courseid . '&view=all',
            'hiddenclass' => $this->actiondata['action'] != 'book' && $this->view == 'user',
            'visible'     => $this->actiondata['action'] != 'book' && $this->view == 'user',
            'justify'     => 'justify-content-' . ($this->view == 'all' ? 'center' : 'left'),
        ];

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
        ];
    }
    protected static function define_properties() {
        return [
            'url' => [
                'type' => PARAM_URL,
            ],
            'username' => [
                'type' => PARAM_RAW,
                'default' => null,
            ],
            'posts' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'action' => [
                'type' => PARAM_RAW,
            ],
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'exerciseid' => [
                'type' => PARAM_INT,
            ],
            'exercisename' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'confirm' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'editing' => [
                'type' => PARAM_BOOL,
            ],
            'alreadybooked' => [
                'type' => PARAM_BOOL,
            ],
            'alreadybookedmsg' => [
                'type' => PARAM_RAW,
                'default' => null,
            ],
            'minslotperiod' => [
                'type' => PARAM_INT,
            ],
            'groupview' => [
                'type' => PARAM_BOOL,
            ],
            'viewallurl' => [
                'type' => PARAM_RAW,
            ],
            'hiddenclass' => [
                'type'  => PARAM_BOOL,
            ],
            'visible' => [
                'type' => PARAM_BOOL,
            ],
            'justify' => [
                'type'  => PARAM_RAW,
                'default'  => null,
            ],
            'calendarview' => [
                'type' => PARAM_BOOL,
                'default' => true,
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
            'contextid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'courseid' => [
                'type' => PARAM_INT,
            ],
            'date' => [
                'type' => date_exporter::read_properties_definition(),
            ],
            'daynames' => [
                'type' => day_name_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'timeslots' => [
                'type' => availability_week_timeslot_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'maxlanes' => [
                'type' => PARAM_INT,
            ],
            'weekofyear' => [
                'type' => PARAM_INT,
            ],
            'periodname' => [
                'type' => PARAM_RAW,
            ],
            'previousperiod' => [
                'type' => date_exporter::read_properties_definition(),
            ],
            'previousweek' => [
                'type' => PARAM_INT,
            ],
            'previousweekts' => [
                'type' => PARAM_INT,
            ],
            'previousperiodname' => [
                'type' => PARAM_RAW,
            ],
            'previousperiodlink' => [
                'type' => PARAM_URL,
            ],
            'nextperiod' => [
                'type' => date_exporter::read_properties_definition(),
            ],
            'nextweek' => [
                'type' => PARAM_INT,
            ],
            'nextweekts' => [
                'type' => PARAM_INT,
            ],
            'nextperiodname' => [
                'type' => PARAM_RAW,
            ],
            'nextperiodlink' => [
                'type' => PARAM_URL,
            ],
            'larrow' => [
                // The left arrow defined by the theme.
                'type' => PARAM_RAW,
            ],
            'rarrow' => [
                // The right arrow defined by the theme.
                'type' => PARAM_RAW,
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

        // notify student if wait period restriction since last session is not up yet
        if ($this->actiondata['action'] != 'book' && $this->view == 'user') {

            // show a notification if the user is on-hold
            if ($this->student->is_onhold()) {
                \core\notification::ERROR(get_string('studentonhold', 'local_booking'));
            }
            if ($this->course->requires_lesson_completion() && !$this->student->has_completed_lessons() && !$this->student->is_newly_joined()) {
                \core\notification::WARNING(get_string('lessonsincomplete', 'local_booking', \implode(', ', $this->student->get_pending_lessons(true))));
            }
        }
        $date = $this->related['type']->timestamp_to_date_array($this->calendar->time);

        // Get previous and next periods navigation information
        list($previousperiod, $previousperiodlink) = $this->get_period($date, '-');
        list($nextperiod, $nextperiodlink) = $this->get_period($date, '+');

        /** @var \core_renderer $output */
        $return = [
            'contextid' => \context_course::instance($this->course->get_id())->id,
            'courseid' => $this->course->get_id(),
            // week data
            'daynames' => $this->get_day_names($output),
            'weekofyear' => (int)$this->weekofyear,
            'timeslots' => $this->get_time_slots($output),
            // day slots data
            'maxlanes' => $this->maxlanes <= LOCAL_BOOKING_MAXLANES ? $this->maxlanes : LOCAL_BOOKING_MAXLANES,
            'date' => (new date_exporter($date))->export($output),
            // navigation data
            'periodname' => get_string('weekinyear', 'local_booking', date('W', $this->calendar->time)),
            'previousperiod' => (new date_exporter($previousperiod))->export($output),
            'previousweek' => (int)date('W', $previousperiod[0]),
            'previousweekts' => $previousperiod[0],
            'previousperiodname' => get_string('weekinyear','local_booking', date('W', $previousperiod[0])),
            'previousperiodlink' => $previousperiodlink->out(false),
            'nextperiod' => (new date_exporter($nextperiod))->export($output),
            'nextweek' => (int)date('W', $nextperiod[0]),
            'nextweekts' => $nextperiod[0],
            'nextperiodname' => get_string('weekinyear','local_booking', date('W', $nextperiod[0])),
            'nextperiodlink' => $nextperiodlink->out(false),
            // TODO: PHP9 deprecates dynamic methods
            'larrow' => $output->larrow(),
            'rarrow' => $output->rarrow(),
        ];

        return $return;
    }

    /**
     * Return the days of the week where $date falls in.
     *
     * @param  array $date First day of the week GMT date
     * @param  structure $type base calendar type
     * @return array array of days
     */
    protected function get_week_days($date, structure $type) {
        $days = [];
        // Calculate which day number is the first day of the week.
        $type = \core_calendar\type_factory::get_calendar_instance();
        $daysinweek = count($type->get_weekdays());

        // get the start of the week
        $week_start_date = new DateTime();
        date_timestamp_set($week_start_date, $date[0]);
        $week_start_date->modify('this week');

        // The number of weeks allowed for posting from plugin settings
        $weekslookahead = get_config('local_booking', 'weeksahead');

        // add remaining days of the week
        for ($i = 0; $i < $daysinweek; $i++) {
            $day = $type->timestamp_to_date_array(date_timestamp_get($week_start_date), 0);
            date_add($week_start_date, date_interval_create_from_date_string("1 days"));
            $day['restricted'] = $this->day_restricted($day, $type, $weekslookahead);
            $days[] = $day;
        }

        return $days;
    }

    /**
     * Checks if the slot date is out
     * of week lookahead bounds for students
     *
     * @param  array $date array of the day to be evaluated
     * @param  structure $type base calendar type
     * @param  int $weekslookahead number of weeks allowed for posting
     * @return  bool
     */
    protected function day_restricted(array $date, structure $type, int $weekslookahead) {

        // restrict posting on days in the past
        $now = new DateTime('@' . gmmktime(0, 0, 0));
        $targetdate = new DateTime('@' . $date[0]);
        $datedifference = (date_diff($now, $targetdate))->format('%R%a');
        $datepassed = $datedifference < 0;

        // when course posting wait restriction is enabled, a day prior to that wait restriction period is restricted
        $hasrestrictionwaiver = !empty($this->student) ? $this->student->has_restriction_waiver() : false;
        $lastsessionwait = $this->view != 'all' && $this->actiondata['action'] != 'book' && !$hasrestrictionwaiver;
        false;
        if ($lastsessionwait) {
            $hasrestrictionwaiver = (bool) $this->student->get_progress_flag(LOCAL_BOOKING_PROGFLAGS['POSTOVERRIDE']);
            if (!$hasrestrictionwaiver) {
                $nextsessiondt = $this->student->get_next_allowed_session_date();
                $nextsessiondate = $type->timestamp_to_date_array($nextsessiondt->getTimestamp());
                $lastsessionwait = $lastsessionwait && $nextsessiondate['year'] >= $date['year'];
                $lastsessionwait = $lastsessionwait && $nextsessiondate['yday'] >= $date['yday'];
            } else {
                $lastsessionwait = !$hasrestrictionwaiver;
            }
        }

        // lookahead is the allowed number of future weeks where posting is allowed
        $beyondlookahead = $datedifference > ($weekslookahead * count($type->get_weekdays()));

        // also ensure weeks lookahead setting is not disabled (unlimited)
        $unlimited = !is_null($weekslookahead)  &&  $weekslookahead == 0;

        // restrict posting on days in the past, beyond lookahead, or prior to last session wait period
        $restricted = $datepassed || $lastsessionwait || ($beyondlookahead && !$unlimited);

        return $restricted;
    }

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  day_name_exporter[]
     */
    protected function get_day_names(renderer_base $output) {
        $weekdaynames = $this->related['type']->get_weekdays();
        $daysinweek = count($weekdaynames);

        $daynames = [];
        for ($i = 0; $i < $daysinweek; $i++) {
            // Bump the currentdayno and ensure it loops.
            $dayno = ($i + $this->firstdayofweek + $daysinweek) % $daysinweek;
            $dayofmonthname = $this->days[$i]['mday'] . '/' . $this->days[$i]['mon'];
            $dayname = new day_name_exporter($dayno, $dayofmonthname, $weekdaynames[$dayno]);

            $daynames[] = $dayname->export($output);
        }

        return $daynames;
    }

    /**
     * Get the list of day hours in 24hr format for display
     * of the week.
     *
     * @return  $timeslots[]
     */
    protected function get_time_slots(renderer_base $output) {

        // Get daily slots from settings
        $firstsessionhour = get_config('local_booking', 'firstsession');
        $lastsessionhour = get_config('local_booking', 'lastsession');

        // Get user timezone offset
        $usertz = new DateTimeZone(usertimezone());
        $usertime = new DateTime("now", $usertz);
        $usertimezoneoffset = (int)$usertz->getOffset($usertime) / 3600;

        // get the lanes containing student(s) slots
        $weeklanes = $this->get_week_slot_lanes($this->course);

        $data = [
            'student'   => $this->student,
            'daysdata'  => $this->days,
            'maxlanes'  => $this->maxlanes,
            'groupview' => $this->view == 'all',
            'bookview'  => $this->actiondata['action'] == 'book',
            'alreadybooked'  => !empty($this->activebooking)
        ];

        $timeslotdata = new \stdClass();
        $timeslotdata->weeklanes = $weeklanes;
        $related = $this->related;
        $related['timeslotdata'] = $timeslotdata;

        $slots = [];
        for ($i = $firstsessionhour; $i <= $lastsessionhour; $i++) {
            $daydata = new \stdClass();
            $daydata->timeslot = substr('00' . $i, -2) . ':00';
            $daydata->usertimeslot = substr('00' . ($i + $usertimezoneoffset) % 24, -2) . ':00';
            $daydata->hour = $i;
            $related['timeslotdata']->daydata = $daydata;
            $timeslot = new availability_week_timeslot_exporter($this->calendar, $data, $related);

            $slots[] = $timeslot->export($output);
        }

        return $slots;
    }

    /**
     * Returns a list of week day lanes
     * containing student(s) slots.
     *
     * @return array
     */
    protected function get_week_slot_lanes(subscriber $course) {
        // show minimum lanes for all students view or 1 lane for user view
        $this->maxlanes = $this->view == 'all' ? LOCAL_BOOKING_MINLANES : 1;
        $weeklanes = [];
        $studentsslots = [];

        // check students that have slot(s) on each day
        if (!empty($this->student)) {
            $studentsslots[$this->student->get_id()] = $this->student->get_slots($this->weekofyear, $this->GMTdate['year']);
        } else {
            // check students that have slot(s) on this day
            $students = $course->get_students();
            foreach ($students as $student) {
                $studentsslots[$student->get_id()] = $student->get_slots($this->weekofyear, $this->GMTdate['year']);
            }
        }

        // go through the week stacking slots
        foreach ($this->days as $daydata) {

            $daylanes = [];
            $laneindex = 0;
            $lastslotuserid = 0;

            // go through all students slots to identify if a lane should be stacked or switched
            foreach ($studentsslots as $studentslots) {

                // check students that have slot(s) on this day
                foreach ($studentslots as $slot) {
                    // skip if the student doesn't have a slot on this day
                    if (getdate($slot->starttime)['wday'] == $daydata['wday']) {
                        // lookup an empty lane that can take the slot w/o overlap
                        $emptylaneidx = $this->find_empty_space($slot, $daylanes, $lastslotuserid);
                        if ($emptylaneidx == -1) {
                            // no empty space found
                            $laneindex++;
                            $daylanes[$laneindex][] = $slot;
                        } else {
                            $daylanes[$emptylaneidx][] = $slot;
                        }
                        $lastslotuserid = $slot->userid == $lastslotuserid ? $lastslotuserid : $slot->userid;
                        // evaluate max number of lanes
                        $this->maxlanes = $laneindex + 1 > $this->maxlanes ? $laneindex + 1 : $this->maxlanes;
                    }
                }
            }
            $weeklanes[$daydata['wday']] = $daylanes;
        }

        return $weeklanes;
    }

    /**
     * Returns a the lane index where an empty spot
     * is found, otherwise returns false (0).
     *
     * @param object    slot object to checked for conflict
     * @param array     An array of day lanes with previous slots
     * @return bool
     */
    protected function find_empty_space($savedslot, $daylanes, $lastslotuserid) {
        $emptylaneidx = -1;
        $laneidx = 0;

        // make sure there are lanes in the day
        if (count($daylanes) != 0) {
            // go through all lanes of the day
            foreach ($daylanes as $lane) {
                // go throuh all slots in the lane
                foreach ($lane as $laneslot) {
                    // does the saved slot start-to-end time conflict with existing lane slots
                    if ((($savedslot->starttime >= $laneslot->starttime && $savedslot->starttime <= $laneslot->endtime) ||
                        ($laneslot->starttime >= $savedslot->starttime && $laneslot->starttime <= $savedslot->endtime)) &&
                        // ok to overlap slots if the previous slot student is the same as this booked slot's
                        ($savedslot->userid != $lastslotuserid)) {
                            break 2;
                    }
                }
                // assign the current lane index to the empty one as there is no overlap on this lane: $savedslot->userid != $laneslot->userid)
                $emptylaneidx = $laneidx;
                $laneidx++;
            }
        } else { $emptylaneidx = 0; }

        return $emptylaneidx;
    }

    /**
     * Get the previous and next week timestamps
     * and URL for navigation links.
     *
     * @return array The previous and next week's timestamp.
     */
    protected function get_period($date, $nextprev) {
        $perioddate = date_create();
        date_timestamp_set($perioddate, $date[0]);
        $perioddate->modify($nextprev . '7 days');
        $newperioddate = $this->related['type']->timestamp_to_date_array(date_timestamp_get($perioddate));

        $periodlink = new moodle_url($this->url);
        $periodlink->param('time', $newperioddate[0]);
        $periodlink->param('week', (int)date('W', $newperioddate[0]));
        $periodlink->param('year', (int)$this->GMTdate['year']);
        $periodlink->param('view', $this->view);
        $periodlink->param('action', $this->actiondata['action']);

        // Pass the user and exercise ids for booking actions
        if ($this->actiondata['action'] == 'book') {
            $periodlink->param('userid', $this->actiondata['student']->get_id());
            $periodlink->param('exid', $this->actiondata['exerciseid']);
        }

        return [$newperioddate, $periodlink];
    }
}
