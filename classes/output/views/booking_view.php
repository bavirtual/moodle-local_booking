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

namespace local_booking\output\views;

use local_booking\external\assigned_students_exporter;
use local_booking\external\bookings_exporter;
use local_booking\external\instructor_participation_exporter;
use stdClass;

/**
 * Class to output instructor dashboard & interim booking views.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_view extends base_view {

    /**
     * @var array $related Related objects necessary to pass along to exporters.
     */
    protected $related;

    /**
     * logbook view constructor.
     *
     * @param \context $context   The course context
     * @param int      $courseid  The course id
     * @param array    $data      The data required for output
     */
    public function __construct(\context $context, int $courseid, array $data) {
        parent::__construct($context, $courseid, $data, 'local_booking/bookings' . ($data['action'] == 'readonly' ? '_readonly' : ''));

        // set class properties
        $this->data['courseid'] = $courseid;
        $this->related = [
            'context'   => $this->context,
        ];

        if ($this->data['action'] == 'readonly' || $this->data['action']=='book') {

            $bookings = new bookings_exporter($this->data, $this->related);
            $this->exporteddata = $bookings->export($this->renderer);

        } elseif ($this->data['action']=='confirm') {

            $this->data['view'] = 'confirm';
            $bookings = new bookings_exporter($this->data, $this->related);
            $this->exporteddata = $bookings->export($this->renderer);

        }
    }

    /**
     * Override parent output method to get Instructor dashboard
     * additional views.
     *
     * @param   ?string   $template     Optional template to be rendered
     * @param   ?stdClass $exporteddata Optional data to be rendered in the template
     * @return  string
     */
    public function output(?string $template = null, ?stdClass $exporteddata = null):string {

        $output = '';

        // select the student progression booking view or the booking confirmation view
        if ($this->data['action']=='confirm') {

            $output = parent::output('local_booking/booking', $this->exporteddata);

        } elseif ($this->data['action']=='book') {

            $output = parent::output();

            // get assigned students if exists
            if (count($this->data['instructor']->get_assigned_students()) > 0) {

                // get instructor's assigned students
                $students = new assigned_students_exporter($this->data, $this->related);
                $exporteddata = $students->export($this->renderer);
                $output .= parent::output('local_booking/my_students', $exporteddata);
            }

            if (has_capability('local/booking:participationview', $this->get_context())) {

                // get instructor participation
                $participation = new instructor_participation_exporter($this->data, $this->related);
                $exporteddata = $participation->export($this->renderer);
                $output .= parent::output('local_booking/participation', $exporteddata);
            }

        } elseif ($this->data['action'] == 'readonly') {

            $output = parent::output();

        }

        return $output;
    }
}
