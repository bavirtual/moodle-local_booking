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

use local_booking\exporters\dashboard_bookings_exporter;
use local_booking\exporters\dashboard_mybookings_exporter;
use local_booking\exporters\dashboard_participation_exporter;
use stdClass;

/**
 * Class to output instructor dashboard & interim booking views.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_view extends base_view
{

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
    public function __construct(array $data, array $related)
    {
        parent::__construct($data, $related, '');

        $bookings = new dashboard_bookings_exporter($this->data, $this->related);
        $this->exporteddata = $bookings->export($this->renderer);
    }

    /**
     * Get students progression export
     *
     * @return  ?stdClass
     */
    public function get_student_progression(bool $html = true)
    {
        global $OUTPUT, $PAGE;
        $output = '';

        // export bookings
        if ($this->data['action'] == 'readonly' || $this->data['action'] == 'book') {

            if ($html) {
                $output = parent::output('local_booking/dashboard' . ($this->data['action'] == 'readonly' ? '_readonly' : ''), $this->exporteddata);

                // show page bar and search form if required
                $course = $this->related['subscriber'];
                if ($course->get_students_count() > LOCAL_BOOKING_DASHBOARDPAGESIZE) {
                    // show paging bar
                    $output .= $OUTPUT->paging_bar($course->get_students_count(), $this->data['page'], LOCAL_BOOKING_DASHBOARDPAGESIZE, $PAGE->url);
                }
            }

        }

        // booking confirmation page
        if ($this->data['action'] == 'confirm') {
            $output = parent::output('local_booking/dashboard_booking_confirm', $this->exporteddata);
        }

        return $html ? $output : $this->exporteddata;
    }

    /**
     * Get instructor 'My bookings' export
     *
     * @return  ?stdClass
     */
    public function get_instructor_bookings(bool $html = true)
    {

        $output = '';
        if ($this->data['action'] != 'confirm') {

            // get active bookings if the view is session booking
            $mybookings = new dashboard_mybookings_exporter($this->data, $this->related);
            $this->exporteddata = $mybookings->export($this->renderer);

            if ($html) {
                $output = parent::output('local_booking/dashboard_my_bookings', $this->exporteddata);
            }

        }
        return $html ? $output : $this->exporteddata;
    }

    /**
     * Get instructor participation export
     *
     * @return  ?stdClass
     */
    public function get_instructor_participation(bool $html = true)
    {

        $output = '';
        if ($this->data['action'] != 'confirm') {

            if (has_capability('local/booking:participationview', $this->related['context'])) {

                // get active bookings if the view is session booking
                $participation = new dashboard_participation_exporter($this->data, $this->related);
                $this->exporteddata = $participation->export($this->renderer);

            }

            if ($html) {
                $output = parent::output('local_booking/instructor_participation', $this->exporteddata);
            }
        }

        return $html ? $output : $this->exporteddata;
    }

    /**
     * Get instructor participation export
     *
     * @return  \stdClass
     */
    public function get_exportdata() {
        return $this->exporteddata;
    }
}
