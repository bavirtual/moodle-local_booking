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

use local_booking\external\bookings_exporter;
use local_booking\external\booking_mybookings_exporter;
use local_booking\external\assigned_students_exporter;
use local_booking\external\instructor_participation_exporter;
use local_booking\output\forms\booking_view_search;
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
     * @param array    $data      The data required for output
     * @param array    $related   The related objects to pass
     */
    public function __construct(array $data, array $related) {
        parent::__construct($data, $related, 'local_booking/bookings' . ($data['action'] == 'readonly' ? '_readonly' : ''));

        // export bookings
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
        global $OUTPUT, $PAGE;
        $output = '';

        // select the student progression booking view or the booking confirmation view
        if ($this->data['action']=='confirm') {

            $output = parent::output('local_booking/booking', $this->exporteddata);

        } elseif ($this->data['action']=='book') {

            // get student progression output
            $output = parent::output();

            // show page bar if required
            $course = $this->related['subscriber'];
            if ($course->get_students_count() > LOCAL_BOOKING_DASHBOARDPAGESIZE) {
                $searchform = new booking_view_search(null, array('students' => $course->get_students_for_select()),'post','',array('id'=>'searchform'));
                $output .= $searchform->render();
                $output .= $OUTPUT->paging_bar($course->get_students_count(), $this->data['page'], LOCAL_BOOKING_DASHBOARDPAGESIZE, $PAGE->url);
            }

            // get active bookings if the view is session booking
            $mybookings = new booking_mybookings_exporter($this->data, $this->related);
            $exporteddata = $mybookings->export($this->renderer);
            $output .= parent::output('local_booking/my_bookings', $exporteddata);

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
                $output .= parent::output('local_booking/instructor_participation', $exporteddata);
            }

        } elseif ($this->data['action'] == 'readonly') {

            $output = parent::output();

        }

        return $output;
    }
}
