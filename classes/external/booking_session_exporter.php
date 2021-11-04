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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use DateTime;
use core\external\exporter;
use local_booking\local\participant\entities\instructor;
use local_booking\local\session\entities\session;
use local_booking\local\session\entities\grade;

/**
 * Class for displaying each session in progression view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_session_exporter extends exporter {

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
        $this->session = $this->get_session($data);

        $data = [
            'studentid'     => $data['studentid'],
            'exerciseid'    => $data['exerciseid'],
            'sessionstatus' => $this->session->get_status(),
            'sessiondate'   => !$this->session->empty() ? (!empty($this->session->get_sessiondate()) ? $this->session->get_sessiondate()->format('j M \'y') : 'null') : '',
            'sessionts'     => !$this->session->empty() ? (!empty($this->session->get_sessiondate()) ? $this->session->get_sessiondate()->getTimestamp() : 0) : 0,
            'sessionempty'  => $this->session->empty(),
            'sessiontooltip'=> $this->session->get_info(),
            'logentryid'    => !empty($data['logentry']) ? $data['logentry']->get_id() : 0,
        ];
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
            'sessionstatus' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessiondate' => [
                'type' => PARAM_RAW,
                'default' => '',
            ],
            'sessionts' => [
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
            ],
            'booked' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'tentative' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'canlogentry' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'logentrymissing' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'isquiz' => [
                'type' => PARAM_BOOL,
                'default' => false,
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
            'graded'        => $this->session->hasgrade(),
            'booked'        => $this->session->hasbooking() && $this->session->get_booking()->confirmed(),
            'tentative'     => $this->session->hasbooking() && !$this->session->get_booking()->confirmed(),
            'canlogentry'   => $this->session->hasgrade() && $this->session->get_grade()->get_exercisetype() != 'quiz',
            'logentrymissing' => empty($this->data['logentryid']) && $this->session->hasgrade() && $this->session->get_grade()->get_exercisetype() != 'quiz',
            'isquiz'        => $this->session->hasgrade() && $this->session->get_grade()->get_exercisetype() == 'quiz'
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
     * Returns the grade matching the passed data exercise id.
     *
     * @return grade
     */
    protected function get_grade($data) {
        $type = \core_calendar\type_factory::get_calendar_instance();
        $grade = null;
        // Get student's grade for this session if available
        if (count($data['grades']) > 0) {
            if (array_search($data['exerciseid'], array_column($data['grades'], 'exerciseid')) !== false) {
                $grade = new grade(
                        $data['exerciseid'],
                        $data['grades'][$data['exerciseid']]->exercisetype,
                        $data['grades'][$data['exerciseid']]->instructorid,
                        $data['grades'][$data['exerciseid']]->instructorname,
                        $data['studentid'],
                        $data['studentname'],
                        $type->timestamp_to_date_array($data['grades'][$data['exerciseid']]->gradedate),
                        $data['grades'][$data['exerciseid']]->grade,
                        $data['grades'][$data['exerciseid']]->totalgrade);
            }
        }
        return $grade;
    }

    /**
     * Returns a new session from the passed data exercise id.
     *
     * @return session
     */
    protected function get_session($data) {
        $grade = $this->get_grade($data);
        $booking = !empty($data['booking']->get_id()) && $data['booking']->get_exerciseid()==$data['exerciseid'] ? $data['booking'] : null;

        // collect session information
        $sessionstatus = '';
        $sessiontooltip = '';
        // get grade info of this session if available
        if ($grade !== null) {
            $sessionstatus = 'graded';
            $sessiondate = new \DateTime('@' . $grade->get_gradedate()[0]);
            $gradeinfo = [
                'instructor'  => $grade->get_gradername(),
                'sessiondate' => $sessiondate->format('j M \'y'),
                'grade'       => intval($grade->get_finalgrade()) . (!empty($grade->get_total_grade()) ? '/' . intval($grade->get_total_grade()) : '')
            ];
            $sessiontooltip = $grade->get_exercisetype() == 'assign' ? get_string('sessiongradeddby', 'local_booking', $gradeinfo) :
                get_string('sessiongradeexampass', 'local_booking', $gradeinfo);
        // get booking info of this session if a booking is available
        } else if (!empty($booking)) {
            $sessionstatus = $booking->confirmed() ? 'booked' : 'tentative';
            $infostatus = $booking->confirmed() ? 'statusbooked' : 'statustentative';
            $sessiondate = new DateTime('@' . $booking->get_slot()->get_starttime());
            $bookinginfo = [
                'instructor'    => instructor::get_fullname($booking->get_instructorid()),
                'sessiondate'   => !empty($sessiondate) ? $sessiondate->format('D, M d \@Hi\z') : 'null',
                'bookingstatus' => ucwords(get_string($infostatus, 'local_booking')),
            ];
            $sessiontooltip = get_string('sessionbookedby', 'local_booking', $bookinginfo);
        } else {
            // default session date to now
            $sessiondate = new \DateTime('@' . time());
        }

        // create session object
        $session = new session($grade, $booking, $sessionstatus, $sessiontooltip, $sessiondate);

        return $session;
    }
}
