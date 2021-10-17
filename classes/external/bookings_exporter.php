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
use local_booking\local\session\entities\priority;
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
     * @var array $exercises An array of excersice ids and names for the course.
     */
    protected $exercises = [];

    /**
     * @var array $activestudents An array of active student info for the course.
     */
    protected $activestudents = [];

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
     */
    public function __construct($data, $related) {

        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $data['courseid'],
                'time' => time(),
            ]);

        $data['url'] = $url->out(false);
        $data['contextid'] = $related['context']->id;
        $this->subscribedcourse = new subscriber($data['courseid']);
        $this->exercises = $this->subscribedcourse->get_exercises();

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

        $this->activestudents = $this->prioritze($this->subscribedcourse->get_active_students());

        $i = 0;
        $totaldays = 0;
        foreach ($this->activestudents as $student) {
            $i++;
            $sequencetooltip = [
                'score'     => $student->priority->get_score(),
                'recency'   => $student->priority->get_recency_days(),
                'slots'     => $student->priority->get_slot_count(),
                'activity'  => $student->priority->get_activity_count(false),
                'completion'=> $student->priority->get_completions(),
            ];

            $waringflag = $this->get_warning($student->priority->get_recency_days());
            $data = [];
            $data = [
                'sequence'        => $i,
                'sequencetooltip' => get_string('sequencetooltip', 'local_booking', $sequencetooltip),
                'studentid'       => $student->get_id(),
                'studentname'     => $student->get_name(),
                'dayssincelast'   => $student->dayssincelast,
                'overduewarning'  => $waringflag == self::OVERDUEWARNING,
                'latewarning'     => $waringflag == self::LATEWARNING,
                'simulator'       => $student->get_simulator(),
            ];
            $studentexporter = new booking_student_exporter($data, $this->data['courseid'], [
                'context' => \context_system::instance(),
                'courseexercises' => $this->exercises,
            ]);
            $activestudentsexports[] = $studentexporter->export($output);
            $totaldays += $student->priority->get_recency_days();
        }
        $this->averagewait = ceil($totaldays / $i);

        return $activestudentsexports;
    }

    /**
     * Prioritize the list of active students
     * based on highest scores.
     *
     * @param   array   $activestudents
     */
    protected function prioritze($activestudents) {
        // Get student booking priority
        foreach ($activestudents as $student) {
            $priority = new priority($this->data['courseid'], $student->get_id());
            $student->priority = $priority;
            $student->dayssincelast = $priority->get_recency_days();
        }

        usort($activestudents, function($st1, $st2) {
            return $st1->priority->get_score() < $st2->priority->get_score();
        });

        return $activestudents;
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

        $instructor = new instructor($this->data['courseid'], $USER->id);
        $bookings = $instructor->get_bookings(0, true);
        foreach ($bookings as $booking) {
            $bookingexport = new booking_mybookings_exporter(['booking'=>$booking], $this->related);
            $bookingexports[] = $bookingexport->export($output);
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
