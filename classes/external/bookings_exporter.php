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
 * Session Booking Plugin
 * Class for displaying students progression and instructor active bookings.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use local_booking\local\participant\entities\instructor;
use local_booking\local\subscriber\entities\subscriber;
use renderer_base;
use moodle_url;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package   local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bookings_exporter extends exporter {

    /**
     * Warning flag of an overdue session
     */
    const OVERDUEWARNING = 1;

    /**
     * Warning flag of a late session past overdue
     */
    const LATEWARNING = 2;

    /**
     * @var string $viewtype The view type requested: session booking or session confirmation
     */
    protected $viewtype;

    /**
     * @var array $exercises An array of excersice ids and names for the course.
     */
    protected $exercises = [];

    /**
     * @var array $activestudents An array of active student info for the course.
     */
    protected $activestudents = [];

    /**
     * @var int $bookingstudentid the user id of the student being booked, applicable in confirmation only.
     */
    protected $bookingstudentid = 0;

    /**
     * @var subscriber $subscribedcourse The subscribing course.
     */
    protected $subscribedcourse;

    /**
     * @var int $averagewait The average wait time for students.
     */
    protected $averagewait;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     * @param int   $studentid optional parameter for confirming a student booking.
     */
    public function __construct($data, $related, $studentid = 0) {

        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $data['courseid'],
                'time' => time(),
            ]);

        $data['url'] = $url->out(false);
        $data['contextid'] = $related['context']->id;
        $this->viewtype = $data['view'];
        $this->subscribedcourse = new subscriber($data['courseid']);
        $this->exercises = $this->subscribedcourse->get_exercises();
        if ($this->viewtype == 'confirm')
            $this->bookingstudentid = $studentid;

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'url' => [
                'type' => PARAM_URL,
            ],
            'contextid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'courseid' => [
                'type' => PARAM_INT,
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
            'exercises' => [
                'type' => exercise_name_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'activestudents' => [
                'type' => booking_student_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'activebookings' => [
                'type' => booking_mybookings_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'avgwait' => [
                'type' => PARAM_INT,
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
            'exercises'  => $this->get_exercises($output),
            'activestudents' => $this->get_students($output),
            'activebookings' => $this->get_bookings($output),
            'avgwait' => $this->averagewait,
        ];

        return $return;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
            'exercises' => 'stdClass[]?',
        );
    }

    /**
     * Retrieves exercises for the course
     *
     * @return array
     */
    protected function get_exercises($output) {
        // get titles from the course custom fields exercise titles array
        $exercisesexports = [];

        $titlevalue = array_values($this->subscribedcourse->exercisetitles);
        foreach($this->exercises as $exercise) {
            // break down each setting title by <br/> tag, until a better way is identified
            $customtitle = array_shift($titlevalue);
            $exercise->title = !empty($customtitle) ? $customtitle : $exercise->exercisename;
            $data = [
                'exerciseid'    => $exercise->exerciseid,
                'exercisename'  => $exercise->exercisename,
                'exercisetitle' => $exercise->title,
            ];

            $exercisename = new exercise_name_exporter($data);
            $exercisesexports[] = $exercisename->export($output);
        }

        return $exercisesexports;
    }

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  student_exporter[]
     */
    protected function get_students($output) {
        $activestudentsexports = [];

        // get all active students or student to be confirmed (session booking or booking confirmation)
        if ($this->viewtype == 'sessions') {
            // get the user preference for the student progression sort type by s = score or a = availability
            $sorttype = $this->data['sorttype'];
            if (empty($sorttype)) {
                $sorttype = get_user_preferences('local_booking_sorttype', 'a');
            } else {
                set_user_preferences(array('local_booking_sorttype'=>$sorttype));
            }
            $this->activestudents = $this->prioritize($this->subscribedcourse->get_active_students(), $sorttype);
        } elseif ($this->viewtype == 'confirm') {
            $this->activestudents[] = $this->subscribedcourse->get_active_student($this->bookingstudentid);
        }

        $i = 0;
        $totaldays = 0;
        foreach ($this->activestudents as $student) {
            $i++;
            $sequencetooltip = [
                'score'     => $student->get_priority()->get_score(),
                'recency'   => $student->get_priority()->get_recency_days(),
                'slots'     => $student->get_priority()->get_slot_count(),
                'activity'  => $student->get_priority()->get_activity_count(false),
                'completion'=> $student->get_priority()->get_completions(),
            ];

            $waringflag = $this->get_warning($student->get_priority()->get_recency_days());
            $data = [
                'sequence'        => $i,
                'sequencetooltip' => get_string('sequencetooltip', 'local_booking', $sequencetooltip),
                'studentid'       => $student->get_id(),
                'studentname'     => $student->get_name(),
                'dayssincelast'   => $student->get_priority()->get_recency_days(),
                'recencytooltip'  => $student->get_priority()->get_recency_info(),
                'overduewarning'  => $waringflag == self::OVERDUEWARNING,
                'latewarning'     => $waringflag == self::LATEWARNING,
                'simulator'       => $student->get_simulator(),
                'view'            => $this->viewtype,
            ];
            $studentexporter = new booking_student_exporter($data, $this->data['courseid'], [
                'context' => \context_system::instance(),
                'courseexercises' => $this->exercises,
            ]);
            $activestudentsexports[] = $studentexporter->export($output);
            $totaldays += $student->get_priority()->get_recency_days();
        }
        $this->averagewait = ceil($totaldays / $i);

        return $activestudentsexports;
    }

    /**
     * Prioritize the list of active students depending on
     * sortying type requested, either by ordered segments or
     * student priority score.  The scored type sorts the list
     * by highest to loest priority score, where by availability
     * type orderes the list by recency days then number of posts
     * into three sequential segments:
     *  1. students that have posted slots and completed lessons
     *  2. students that have no posted slots but completed lessons
     *  3. students that have not completed lessons
     *
     * @param   string  $sorttype       The sort type 'score' vs 'availability'
     * @param   array   $activestudents The ordered list of active students
     */
    protected function prioritize($activestudents, $sorttype) {
        $postedcompleted = [];
        $nopostcompleted = [];
        $notcompleted = [];
        $finallist = [];

        if ($sorttype == 'a') {
            // // order active students by: price ASC the inStock DESC s
            // usort($activestudents, function($st1, $st2) {
            //     return ;
            //     if ($st1->get_priority()->get_recency_days() === $st2->get_priority()->get_recency_days()) {
            //         return $st1->get_priority()->get_slot_count() === $st2->get_priority()->get_slot_count();
            //     }
            //     return $st1->get_priority()->get_recency_days() <=> $st2->get_priority()->get_recency_days();
            // });

            // filtering students that have posted slots and completed lessons
            $postedcompleted = array_filter($activestudents, function($std) {
                return $std->has_completed_lessons() && $std->get_priority()->get_slot_count() > 0;
            });
            // order active students by: price ASC the inStock DESC s
            usort($postedcompleted, function($st1, $st2) {
                if ($st1->get_priority()->get_recency_days() === $st2->get_priority()->get_recency_days()) {
                    return $st2->get_priority()->get_slot_count() <=> $st1->get_priority()->get_slot_count();
                }
                return $st2->get_priority()->get_recency_days() <=> $st1->get_priority()->get_recency_days();
            });

            // filtering students that have no posted slots but completed lessons
            $nopostcompleted = array_filter($activestudents, function($std) {
                return $std->has_completed_lessons() && $std->get_priority()->get_slot_count() == 0;
            });
            // order active students by: price ASC the inStock DESC s
            usort($nopostcompleted, function($st1, $st2) {
                return $st2->get_priority()->get_recency_days() <=> $st1->get_priority()->get_recency_days();
            });

            // filtering students that have not completed lessons
            $notcompleted = array_filter($activestudents, function($std) {
                return !$std->has_completed_lessons();
            });
            // order active students by: price ASC the inStock DESC s
            usort($notcompleted, function($st1, $st2) {
                return $st2->get_priority()->get_recency_days() <=> $st1->get_priority()->get_recency_days();
            });

            $finallist = array_merge($postedcompleted, $nopostcompleted, $notcompleted);

        } elseif ($sorttype == 's') {
            // Get student booking priority
            usort($activestudents, function($st1, $st2) {
                return $st1->get_priority()->get_score() < $st2->get_priority()->get_score();
            });
            $finallist = $activestudents;
        }

        return $finallist;
    }

    /**
     * Get the list of all instructor bookings
     * of the week.
     *
     * @param   renderer_base $output
     * @return  mybooking_exporter[]
     */
    protected function get_bookings($output) {
        global $USER;
        $bookingexports = [];

        // get active bookings if the view is session booking
        if ($this->viewtype == 'sessions') {
            $instructor = new instructor($this->data['courseid'], $USER->id);
            $bookings = $instructor->get_bookings(0, true);
            foreach ($bookings as $booking) {
                $bookingexport = new booking_mybookings_exporter(['booking'=>$booking], $this->related);
                $bookingexports[] = $bookingexport->export($output);
            }
        }

        return $bookingexports;
    }

    /**
     * Get a warning flag related to
     * when the student took the last
     * session 3x wait is overdue, and
     * 4x wait is late.
     *
     * @param   int $studentid  The student id
     * @return  int $flag       The delay flag
     */
    protected function get_warning($dayssincelast) {
        $warning = 0;
        $waitdays = get_config('local_booking', 'nextsessionwaitdays') ? get_config('local_booking', 'nextsessionwaitdays') : LOCAL_BOOKING_DAYSFROMLASTSESSION;

        if ($dayssincelast >= ($waitdays * LOCAL_BOOKING_SESSIONOVERDUEMULTIPLIER) &&  $dayssincelast < ($waitdays * LOCAL_BOOKING_SESSIONLATEMULTIPLIER)) {
            $warning = self::OVERDUEWARNING;
        } else if ($dayssincelast >= ($waitdays * LOCAL_BOOKING_SESSIONLATEMULTIPLIER)) {
            $warning = self::LATEWARNING;
        }

        return $warning;
    }
}
