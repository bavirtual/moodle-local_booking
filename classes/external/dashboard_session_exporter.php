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
 * Class for displaying student sessions (assignments).
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use ArrayObject;
use \DateTime;
use core\external\exporter;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\participant;
use local_booking\local\session\entities\session;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;

/**
 * Class for displaying each session in progression view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_session_exporter extends exporter {

    /**
     * @var student $student An object representing the student.
     */
    protected $student;

    /**
     * @var session $session An object containing session info.
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param mixed $data An array of exercise data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        $this->student = $data['student'];

        // process sessions
        if (!empty($this->session = $this->get_session($data))) {
            $data = [
                'courseid'      => $this->student->get_courseid(),
                'studentid'     => $this->student->get_id(),
                'exerciseid'    => $data['exerciseid'],
                'sessionid'     => $this->session->get_id(),
                'flighttype'    => $data['flighttype'],
                'sessionstatus' => $this->session->get_status(),
                'sessiondatets' => !empty($this->session->get_sessiondate()) ? ($this->session->get_sessiondate())->getTimestamp() : '',
                'sessionempty'  => empty($this->session),
                'sessiontooltip'=> $this->session->get_info(),
                'logentryid'    => !empty($this->session->get_logentry()) ? $this->session->get_logentry()->get_id() : 0,
            ];
        }
        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'studentid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'exerciseid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'sessionid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'flighttype' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessionstatus' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessiondatets' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'sessionempty' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'sessiontooltip' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'logentryid' => [
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
            'graded' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'passed' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'status' => [
                'type' => PARAM_RAW,
                'default' => 'graded',
            ],
            'canlogentry' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'logentrymissing' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'sessionicon' => [
                'type' => \PARAM_RAW,
                'default' => 'info-circle',
            ],
            'isquiz' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'quizpassed' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'lastbookingts' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'marknoposts' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'noposts' => [
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
    protected function get_other_values(\renderer_base $output) {
        $return = [];

        $marknoposts = false;
        $noposts = '';

        if ($this->related['filter'] == 'active' || $this->related['filter'] == 'onhold') {
            // get student posts for active and onhold students
            $nextexercise = $this->student->get_next_exercise();
            $noposts = ($nextexercise == $this->data['exerciseid'] && $this->student->get_total_posts() == 0) ? get_string('bookingnoposts', 'local_booking') : '';
        }

        if (!empty($this->session)) {
            $graded = $this->session->hasgrade();
            $logentrymissing = $this->is_logentry_missing();
            $lastbookingdate = $logentrymissing ?
                slot::get_last_booked_slot_date($this->data['courseid'], $this->student->get_id()) :
                $this->session->get_sessiondate()->getTimestamp();

            // consider 'No posts' tag when a 'no-show' occurs and the session is cancelled
            if ($this->session->isnoshow())
                $noposts = get_string('bookingnoposts', 'local_booking');

            $return = [
                'graded'        => $graded,
                'passed'        => $this->session->haspassed(),
                'status'        => $this->session->get_status(),
                'canlogentry'   => $graded && !$this->session->isquiz(),
                'logentrymissing' => $logentrymissing,
                'sessionicon'   => !empty($this->session->get_id()) ? 'info-circle' : 'check-circle-o',
                'isquiz'        => $this->session->isquiz(),
                'quizpassed'    => $this->session->isquiz() && $this->session->haspassed(),
                'lastbookingts' => $lastbookingdate
            ];
        }

        $marknoposts = !empty($noposts) && (empty($this->session) || ($this->session->isnoshow() && !$graded));

        $return += [
            'marknoposts'   => $marknoposts,
            'noposts'       => $noposts
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
            'context'=>'context',
            'coursemodules'=>'cm_info[]?',
            'subscriber'=>'local_booking\local\subscriber\entities\subscriber',
            'filter'=>'string',
        );
    }

    /**
     * Returns a new session from the passed data exercise id.
     *
     * @param array $data       The data array containing session information
     * @return session $session The current booking session
     */
    protected function get_session($data) {

        $grade = isset($data['grades'][$data['exerciseid']]) ? $data['grades'][$data['exerciseid']] : null;
        $booking = $this->find_booking($data['bookings'], $data['exerciseid']);
        $logentry = !empty($grade) && !empty($booking) ? $data['logbook']->get_logentry(0, 0, $booking->get_id()) : null;

        // collect session information
        $session = null;
        $sessiondate = null;
        $sessionstatus = '';
        $sessiontooltip = '';

        // get grade info of this session if available
        if ($grade !== null) {
            $sessiondate = new DateTime('@' . (!empty($booking) ? $booking->get_slot()->get_starttime() : $grade->get_dategraded()));
            $sessionstatus = 'graded';

            $gradeinfo = [
                'instructor'  => participant::get_fullname(!empty($booking) ? $booking->get_instructorid() : $grade->usermodified),
                'gradedate'   => (new DateTime('@' . $grade->get_dategraded()))->format('j M \'y'),
                'sessiondate' => !empty($sessiondate) ? $sessiondate->format('j M \'y') . '<br/>' : '',
                'grade'       => intval($grade->finalgrade) . (!empty($grade->get_grade_max()) ? '/' . intval($grade->get_grade_max()) : '')
            ];

            // get session tooltip for passing & progressing grades, and exam grades
            if ($grade->grade_item->itemmodule == 'assign') {
                if ($grade->is_passed()) {
                    $sessiontooltip =  get_string(!empty($booking) ? 'sessiongradedby' : 'sessiongradednosession', 'local_booking', $gradeinfo);
                } else {
                    $sessiontooltip =  get_string('sessionprogressing', 'local_booking', $gradeinfo);
                    $sessionstatus = 'objective-not-met';
                }
            } else {
                $sessiontooltip =  get_string('sessiongradeexampass', 'local_booking', $gradeinfo);
            }
        }

        // get booking info of this session if a booking is available - overrides grade
        if (!empty($booking)) {
            $sessiondate = new DateTime('@' . $booking->get_slot()->get_starttime());

            if ($booking->active()) {
                $sessionstatus = $this->student->is_active() && $booking->confirmed() ? 'booked' : 'tentative';
                $infostatus = $booking->confirmed() ? 'statusbooked' : 'statustentative';
                $bookinginfo = [
                    'instructor'    => instructor::get_fullname($booking->get_instructorid()),
                    'sessiondate'   => !empty($sessiondate) ? $sessiondate->format('D, M d \@Hi\z') : 'null',
                    'bookingstatus' => ucwords(get_string($infostatus, 'local_booking')),
                ];
                $sessiontooltip = get_string('sessionbookedby', 'local_booking', $bookinginfo);
            }
        }

        // create session object
        if (!empty($grade) || !empty($booking))
            $session = new session($grade, $booking, $logentry, $sessionstatus, $sessiontooltip, $sessiondate);

        return $session;
    }

    /**
     * Returns the booking matching the passed data exercise id.
     *
     * @param array    $bookings   The array of bookings
     * @param int      $exerciseid The exercise id matching the session
     * @return booking $booking    The booking object for the session
     */
    protected function find_booking($bookings, $exerciseid) {
        $booking = null;

        // Get student's booking for this session if available
        if (count($bookings) > 0) {
            $bookingsarr = array_filter($bookings,
                function ($b) use (&$exerciseid) {
                    return $b->get_exerciseid() == $exerciseid;
                }
            );
            if (count($bookingsarr) > 0) {
                $bookingsIterator = (new ArrayObject($bookingsarr))->getIterator();
                $bookingsIterator->seek(count($bookingsarr)-1);
                $booking = $bookingsIterator->current();
            }
        }
        return $booking;
    }
    /**
     * Whether the session is missing a log entry.
     *
     * @return bool
     */
    protected function is_logentry_missing() {
        $has_logentry = $this->session->haslogentry();
        $has_grade = $this->session->hasgrade();
        $is_quiz = $has_grade ? $this->session->get_grade()->grade_item->itemmodule == 'quiz' : false;
        $is_solo = !empty($this->session->get_logentry()) ? $this->session->get_logentry()->get_flighttype() == 'solo' : false;

        return !$has_logentry && !$is_solo && $has_grade && !$is_quiz;
    }
}
