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
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\participant\entities\instructor;
use local_booking\output\views\base_view;
use renderer_base;
use moodle_url;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bookings_exporter extends exporter {

    /**
     * Warning flag of an overdue session (orange)
     */
    const OVERDUEWARNING = 1;

    /**
     * Warning flag of a late session past overdue (red)
     */
    const LATEWARNING = 2;

    /**
     * @var subscriber $subscriber The subscribing course.
     */
    protected $course;

    /**
     * @var instructor $instructor The viewing instructor.
     */
    protected $instructor;

    /**
     * @var string $viewtype The view type requested: session booking or session confirmation
     */
    protected $viewtype;

    /**
     * @var string $filter The filter of the students list
     */
    protected $filter;

    /**
     * @var array $modules An array of excersice and quiz ids and names for the course.
     */
    protected $modules;

    /**
     * @var array $activestudents An array of active student info for the course.
     */
    protected $activestudents = [];

    /**
     * @var int $bookingstudentid the user id of the student being booked, applicable in confirmation only.
     */
    protected $bookingstudentid = 0;

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
    public function __construct($data, $related) {
        global $COURSE;

        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $data['courseid'],
                'time' => time(),
            ]);

        $data['url'] = $url->out(false);
        $data['contextid'] = $related['context']->id;
        $this->viewtype = $data['view'];
        $this->course = $COURSE->subscriber;
        $this->modules = $this->course->get_modules();
        $data['trainingtype'] = $this->course->trainingtype;
        $data['findpirepenabled'] = $this->course->has_integration('pireps');
        $this->instructor = key_exists('instructor', $data) ? $data['instructor'] : null;
        if ($this->viewtype == 'confirm')
            $this->bookingstudentid = $data['studentid'];
        $this->filter = !empty($data['filter']) ? $data['filter'] : 'active';
        $data['visible'] = true;

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
            'trainingtype' => [
                'type' => PARAM_RAW,
            ],
            'findpirepenabled' => [
                'type' => PARAM_BOOL,
            ],
            'visible' => [
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
            'coursemodules' => [
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
            'showaction' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'showactive' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'showonhold' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'showgraduates' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'showsuspended' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'showallcourses' => [
                'type' => \PARAM_BOOL,
                'default' => false,
            ],
            'studentinfo' => [
                'type' => PARAM_RAW,
                'default' => '',
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

        $options = [
            'isinstructor' => !empty($this->instructor),
            'isexaminer'   => !empty($this->instructor) ? $this->instructor->is_examiner() : false,
            'viewtype'     => $this->viewtype,
            'readonly'     => $this->data['action'] == 'readonly'
        ];

        $return = [
            'coursemodules'  => base_view::get_modules($output, $this->course, $options),
            'activestudents' => $this->get_students($output),
            'activebookings' => $this->get_bookings($output),
            'avgwait' => $this->averagewait,
            'showaction' => $this->filter == 'active',
            'showactive' => $this->filter == 'active' || empty($this->filter) ? 'checked' : '',
            'showonhold' => $this->filter == 'onhold' ? 'checked' : '',
            'showgraduates' => $this->filter == 'graduates' ? 'checked' : '',
            'showsuspended' => $this->filter == 'suspended' ? 'checked' : '',
            'showallcourses'=> !empty(\get_user_preferences('local_booking_1_xcoursebookings', false, !empty($this->instructor) ? $this->instructor->get_id() : 0)),
            'studentinfo'=> get_string('studentinfo' . $this->filter, 'local_booking'),
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
        );
    }

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  student_exporter[]
     */
    protected function get_students($output) {
        global $COURSE;
        $activestudentsexports = [];

        // get all active students for the instructor dashboard view (sessions) or a single student of the interim step (confirm)
        if ($this->viewtype == 'sessions') {
            // get the user preference for the student progression sort type by s = score or a = availability
            $sorttype = $this->data['sorttype'];
            $filter = $this->filter;

            // get sorted preference
            if (empty($sorttype)) {
                $sorttype = get_user_preferences('local_booking_sorttype', 'a');
            } else {
                set_user_preferences(array('local_booking_sorttype'=>$sorttype));
            }

            // get the students list based on the requested filter for active or on-hold
            $studentslist = $this->course->get_students($filter);
            $this->activestudents = $this->filter != 'suspended' ? $this->sort_students($studentslist, $sorttype) : $studentslist;

        } elseif ($this->viewtype == 'confirm') {
            $this->activestudents[] = $this->course->get_student($this->bookingstudentid);
        }

        $i = 0;
        $totaldays = 0;
        $context = \context_system::instance();
        foreach ($this->activestudents as $student) {
            $i++;

            // data for the student's exporter
            $waringflag = $this->get_warning($this->filter == 'active' || $this->filter == 'onhold' ?  $student->get_priority()->get_recency_days() : -1);
            $data = [
                'sequence'        => $i,
                'instructor'      => $this->instructor,
                'student'         => $student,
                'overduewarning'  => $waringflag == self::OVERDUEWARNING,
                'latewarning'     => $waringflag == self::LATEWARNING,
                'view'            => $this->viewtype,
            ];

            // get tooltip
            if (!empty($sorttype) && $this->filter == 'active') {
                if ($sorttype == 'a') {
                    $sequencetooltip = ['tag' => get_string('tag_' . $student->tag, 'local_booking')];
                } elseif ($sorttype == 's') {
                    $sequencetooltip = [
                        'score'     => $student->get_priority()->get_score(),
                        'recency'   => $student->get_priority()->get_recency_days(),
                        'slots'     => $student->get_priority()->get_slot_count(),
                        'activity'  => $student->get_priority()->get_activity_count(false),
                        'completion'=> $student->get_priority()->get_completions(),
                    ];
                }
                $data['tag'] = $student->tag;
                $data['sequencetooltip'] = get_string('sequencetooltip_' . (!empty($sorttype) ? $sorttype : 'a'), 'local_booking', $sequencetooltip);
            }

            $studentexporter = new booking_student_exporter($data, [
                'context'       => $context,
                'coursemodules' => $this->modules,
                'course'        => $this->course,
                'filter'        => $this->filter,
            ]);
            $activestudentsexports[] = $studentexporter->export($output);
            $totaldays += $this->filter == 'active' || $this->filter == 'onhold' ?  $student->get_priority()->get_recency_days() : 0;
        }
        $this->averagewait = !empty($totaldays) ? ceil($totaldays / $i) : 0;

        return $activestudentsexports;
    }

    /**
     * Sort the list of active students depending on the sort type
     * (student score or availability).  The student score type orders
     * the list by highest priority score to lowest, where Availability
     * sort type orders the list by recency days then number of posts
     * into three sequential segments, with each sorted by session recency:
     *  1. students that posted slots and completed lessons
     *  2. students that completed lessons but have no posted slots
     *  3. students that have not completed lessons
     *
     * @param   string  $sorttype       The sort type 'score' vs 'availability'
     * @param   array   $activestudents The ordered list of active students
     */
    protected function sort_students($activestudents, $sorttype) {
        $posts_completed = [];
        $noposts_completed = [];
        $not_completed = [];
        $finallist = [];

        // sort depending on filter
        if ($this->filter == 'graduates') {
            $groupid = groups_get_group_by_name($this->course->get_id(), LOCAL_BOOKING_GRADUATESGROUP);
            $graduates = groups_get_members($groupid, 'u.id, gm.timeadded');

            // get the graduation dates
            foreach ($activestudents as $student) {
                if (\array_key_exists($student->get_id(), $graduates)) {
                    $student->set_graduated_date($graduates[$student->get_id()]->timeadded);
                }
            }

            // sort all graduates by their graduation date descending
            uasort($activestudents, function($st1, $st2) {
                return $st2->get_graduated_date(true) <=> $st1->get_graduated_date(true);
            });

            $finallist = $activestudents;

        } elseif ($sorttype == 'a') {
            // filtering students that have posted slots and completed lessons
            $posts_completed = array_filter($activestudents, function($std) {
                if ($std->has_completed_lessons() && $std->get_priority()->get_slot_count() > 0 && empty($std->get_active_booking())) {
                    $std->tag = 'posts_completed';
                }
                return property_exists($std, 'tag') ? $std->tag == 'posts_completed' : false;
            });
            // order active students by: recency days then posted slots
            uasort($posts_completed, function($st1, $st2) {
                if ($st1->get_priority()->get_recency_days() === $st2->get_priority()->get_recency_days()) {
                    return $st2->get_priority()->get_slot_count() <=> $st1->get_priority()->get_slot_count();
                }
                return $st2->get_priority()->get_recency_days() <=> $st1->get_priority()->get_recency_days();
            });

            // filtering students that have no posted slots but completed lessons
            $noposts_completed = array_filter($activestudents, function($std) {
                if (($std->has_completed_lessons() && $std->get_priority()->get_slot_count() == 0) || !empty($std->get_active_booking())) {
                    $std->tag = 'noposts_completed';
                }
                return property_exists($std, 'tag') ? $std->tag == 'noposts_completed' : false;
            });
            // order active students by session recency
            uasort($noposts_completed, function($st1, $st2) {
                return $st2->get_priority()->get_recency_days() <=> $st1->get_priority()->get_recency_days();
            });

            // filtering students that have not completed lessons
            $not_completed = array_filter($activestudents, function($std) {
                if (!$std->has_completed_lessons()) {
                    $std->tag = 'not_completed';
                }
                return property_exists($std, 'tag') ? $std->tag == 'not_completed' : false;
            });
            // order active students that has not completed ground lessons modules by recency days
            uasort($not_completed, function($st1, $st2) {
                return $st2->get_priority()->get_recency_days() <=> $st1->get_priority()->get_recency_days();
            });

            // merge all three arrays
            $finallist = $posts_completed + $noposts_completed + $not_completed;

        } elseif ($sorttype == 's') {
            // Get student booking priority
            uasort($activestudents, function($st1, $st2) {
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
        global $USER, $COURSE;
        $bookingexports = [];

        // get active bookings if the view is session booking
        if ($this->viewtype == 'sessions') {
            $instructor = $this->course->get_instructor($USER->id);
            $bookings = $instructor->get_bookings(false, true, true);
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
        global $COURSE;
        $warning = 0;
        $waitdays = intval($this->course->postingwait);
        $onholdperiod = intval($this->course->onholdperiod);

        // Color code amber and red for inactivity one week after waitperiod
        // since last session (amber) and one week before on-hold date (red)
        if ($waitdays > 0 && $onholdperiod > 0) {
            if (($dayssincelast > ($waitdays + 7)) &&  $dayssincelast < ($onholdperiod - 7)) {
                $warning = self::OVERDUEWARNING;
            } else if ($dayssincelast >= ($onholdperiod - 7)) {
                $warning = self::LATEWARNING;
            }
        }

        return $warning;
    }
}
