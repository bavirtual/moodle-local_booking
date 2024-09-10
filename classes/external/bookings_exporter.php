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
     * Warning flag of an overdue session (orange)
     */
    const OVERDUEWARNING = 1;

    /**
     * Warning flag of a late session past overdue (red)
     * Warning flag of a late session past overdue (red)
     */
    const LATEWARNING = 2;

    /**
     * @var student[] $students list to export.
     */
    public $activestudentsexports = [];

    /**
     * @var subscriber $subscriber The subscribing course.
     */
    protected $course;
    protected $course;

    /**
     * @var instructor $instructor The viewing instructor.
     */
    protected $instructor;

    /**
     * @var int $studentid the user id of the student being booked, applicable in confirmation only.
     */
    protected $studentid = 0;

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
     * @var int $averagewait The average wait time for students.
     */
    protected $averagewait;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {

        $this->course = $related['subscriber'];
        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $this->course->get_id(),
                'time' => time(),
            ]);

        $data['url'] = $url->out(false);
        $data['contextid'] = $related['context']->id;
        $data['courseid'] = $this->course->get_id();
        $this->viewtype = $data['view'];
        $this->modules = $this->course->get_modules();
        $data['trainingtype'] = $this->course->trainingtype;
        $data['findpirepenabled'] = $this->course->has_integration('external_data', 'pireps');
        $this->instructor = key_exists('instructor', $data) ? $data['instructor'] : null;
        $this->studentid = $data['studentid'];
        $this->filter = !empty($data['filter']) ? $data['filter'] : 'active';
        $data['visible'] = 0;

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
                'default' => 0,
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
            'scoresort' => [
                'type' => \PARAM_BOOL,
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
            'restrictionsenabled' => [
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
            'coursemodules' => base_view::get_modules($output, $this->course, $options),
            'activestudents'=> $this->get_students($output),
            'scoresort'         => $this->data['sorttype'] == 's',
            'avgwait'       => $this->averagewait,
            'showaction'    => $this->filter == 'active',
            'showactive'    => $this->filter == 'active' || empty($this->filter) ? 'checked' : '',
            'showonhold'    => $this->filter == 'onhold' ? 'checked' : '',
            'showgraduates' => $this->filter == 'graduates' ? 'checked' : '',
            'showsuspended' => $this->filter == 'suspended' ? 'checked' : '',
            'showallcourses'=> !empty(\get_user_preferences('local_booking_1_xcoursebookings', false, !empty($this->instructor) ? $this->instructor->get_id() : 0)),
            'restrictionsenabled'=> intval($this->course->onholdperiod) > 0,
            'studentinfo'=> get_string('studentinfo' . $this->filter, 'local_booking'),
        ];

        return $return;
    }

    /**
     * Get the list of day names for display, re-ordered from the first day
     * of the week.
     *
     * @param   renderer_base $output
     * @return  student_exporter[]
     */
    protected function get_students($output) {

        // get all active students for the instructor dashboard view (sessions) or a single student of the interim step (confirm)
        if ($this->studentid == 0) {
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
            $this->activestudents = $this->course->get_students($filter, false, false, true, $this->data['page']);

        } elseif ($this->viewtype == 'confirm') {
            $this->activestudents[] = $this->course->get_student($this->bookingstudentid);
            $this->activestudents[] = $this->course->get_student($this->bookingstudentid);
        }

        $i = 0;
        $totaldays = 0;
        $context = \context_system::instance();
        $context = \context_system::instance();
        foreach ($this->activestudents as $student) {
            $i++;

            // data for the student's exporter
            $waringflag = $this->get_warning($this->filter == 'active' || $this->filter == 'onhold' ?  $student->get_priority()->get_recency_days() : -1);
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
                    $sequencetooltip = ['tag' => get_string('tag_' . $student->get_status(), 'local_booking')];
                } elseif ($sorttype == 's') {
                    $sequencetooltip = [
                        'score'     => $student->get_priority()->get_score(),
                        'recency'   => $student->get_priority()->get_recency_days(),
                        'slots'     => $student->get_total_posts(),
                        'activity'  => $student->get_priority()->get_activity_count(false),
                        'completion'=> $student->get_priority()->get_completions(),
                        'score'     => $student->get_priority()->get_score(),
                        'recency'   => $student->get_priority()->get_recency_days(),
                        'slots'     => $student->get_priority()->get_slot_count(),
                        'activity'  => $student->get_priority()->get_activity_count(false),
                        'completion'=> $student->get_priority()->get_completions(),
                    ];
                }
                $data['tag'] = $student->get_status();
                $data['sequencetooltip'] = get_string('sequencetooltip_' . (!empty($sorttype) ? $sorttype : 'a'), 'local_booking', $sequencetooltip);
            }

            $studentexporter = new booking_student_exporter($data, [
                'context'       => $context,
                'context'       => $context,
                'coursemodules' => $this->modules,
                'subscriber'    => $this->course,
                'filter'        => $this->filter,
            ]);
            $this->activestudentsexports[] = $studentexporter->export($output);
            $totaldays += $this->filter == 'active' || $this->filter == 'onhold' ?  $student->get_priority()->get_recency_days() : 0;
        }
        $this->averagewait = !empty($totaldays) ? ceil($totaldays / $i) : 0;

        return $this->activestudentsexports;
    }

    /**
     * Get a warning flag related to
     * when the student took the last
     * session 3x wait is overdue, and
     * 4x wait is late.
     *
     * @param   int $dayssincelast  Days since last booking
     * @return  int $warning        The warning flag
     */
    protected function get_warning($dayssincelast) {
        $warning = 0;
        $waitdays = intval($this->course->postingwait);
        $onholdperiod = intval($this->course->onholdperiod);
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

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
            'subscriber' => 'local_booking\local\subscriber\entities\subscriber',
        );
    }
}
