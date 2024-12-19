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

use stdClass;
use moodle_url;
use core\output\sticky_footer;
use local_booking\exporters\dashboard_bookings_exporter;
use local_booking\exporters\dashboard_mybookings_exporter;
use local_booking\exporters\dashboard_participation_exporter;

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
     * The number of students in the course
     * @var int $studentcount
     */
    protected $studentcount;

    /**
     * Results page number
     * @var int $page
     */
    protected $page;

    /**
     * Results per page
     * @var int $perpage
     */
    protected $perpage;

    /** @var int Maximum number of students that can be shown on one page */
    protected static $maxperpage = 200;

    /**
     * List of allowed values for 'perpage' setting
     * @var array $validperpage
     */
    protected static $validperpage = [20, 50, 100];

    /**
     * Results for the selected filter
     * @var int $filter
     */
    protected $filter;

    /**
     * List of allowed values for 'filter' setting
     * @var array $validfilter
     */
    protected static $validfilter = ['active', 'onhold', 'graduates', 'suspended'];

    /**
     * logbook view constructor.
     *
     * @param array    $data      The data required for output
     * @param array    $related   The related objects to pass
     */
    public function __construct(array $data, array $related) {
        global $USER;
        parent::__construct($data, $related, '');

        $course = $related['subscriber'];
        $courseid = $course->get_id();
        $this->filter = $this->data['filter'];
        $this->page = $this->data['page'];
        $this->perpage = $this->data['perpage'] ?: get_user_preferences("course-$courseid-$USER->id-perpage", 0, $USER->id);
        set_user_preferences(["course-$courseid-$USER->id-perpage"=>$this->perpage], $USER->id);
        $this->data['perpage'] = $this->perpage;
        $bookings = new dashboard_bookings_exporter($this->data, $this->related);
        $this->exporteddata = $bookings->export($this->renderer);
        $this->studentcount = $course->get_students_count();
    }

    /**
     * Get students progression export
     *
     * @return  ?stdClass
     */
    public function get_student_progression(bool $html = true) {
        global $OUTPUT, $PAGE;
        $output = '';

        // export bookings
        if ($this->data['action'] == 'readonly' || $this->data['action'] == 'book') {

            if ($html) {
                $templatesuffix = !empty($this->data['action']) ? ($this->data['action'] == 'readonly' ? '_readonly' : '') : '';
                $output = parent::output('local_booking/dashboard' . $templatesuffix, $this->exporteddata);
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
    public function get_instructor_bookings(bool $html = true) {

        $output = '';
        if ($this->data['action'] != 'confirm') {

            // get active bookings if the view is session booking
            $mybookings = new dashboard_mybookings_exporter($this->data, $this->related);
            $this->exporteddata = $mybookings->export($this->renderer);

            if ($html) {
                $output = parent::output('local_booking/dashboard_mybookings', $this->exporteddata);
            }

        }
        return $html ? $output : $this->exporteddata;
    }

    /**
     * Get instructor participation export
     *
     * @return  ?stdClass
     */
    public function get_instructor_participation(bool $html = true){

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
     * @return  ?stdClass
     */
    public function get_sticky_footer() {
        global $OUTPUT, $PAGE;

        $output = '';
        // $showpaging = $this->studentcount > LOCAL_BOOKING_DASHBOARDPAGESIZE;

        // render page select
        $perpageselect = $this->get_perpage();

        // render filter select
        $studentfilterselect = $this->get_studentfilter();

        // render paging bar
        $pagingbar = $OUTPUT->paging_bar($this->studentcount, $this->page, $this->perpage, $PAGE->url);

        // render footer content bar
        $footercontent = ['perpageselect' => $perpageselect, 'studentfilterselect' => $studentfilterselect, 'pagingbar' => $pagingbar];
        $footercontent = $OUTPUT->render_from_template('local_booking/dashboard_footer_navigation', $footercontent);

        // show paging bar
        $stickyfooter = new sticky_footer($footercontent);
        $output = $OUTPUT->render($stickyfooter);

        return $output;
    }

    /**
     * Get the per-page select render object
     *
     * @return  array
     */
    public function get_perpage() {
        global $PAGE;

        $url = new moodle_url($PAGE->url);
        $numusers = $this->studentcount;

        // Print per-page dropdown.
        $pagingoptions = self::$validperpage;
        if ($this->perpage) {
            $pagingoptions[] = $this->perpage; // To make sure the current preference is within the options.
        }
        $pagingoptions = array_unique($pagingoptions);
        sort($pagingoptions);
        $pagingoptions = array_combine($pagingoptions, $pagingoptions);
        if ($numusers > self::$maxperpage) {
            $pagingoptions['0'] = self::$maxperpage;
        } else {
            $pagingoptions['0'] = get_string('all');
        }

        $perpagedata = [
            'baseurl' => $url->out(false),
            'options' => []
        ];
        foreach ($pagingoptions as $key => $name) {
            $perpagedata['options'][] = [
                'name' => $name,
                'value' => $key,
                'selected' => $key == $this->perpage,
            ];
        }

        return $perpagedata;

    }

    /**
     * Get the students filter select render object
     *
     * @return  array
     */
    public function get_studentfilter() {
        global $PAGE;

        $url = new moodle_url($PAGE->url);

        // Print per-page dropdown.
        $filteroptions = self::$validfilter;
        if ($this->filter) {
            $filteroptions[] = $this->filter; // To make sure the current preference is within the options.
        }

        $filteroptions = array_unique($filteroptions);
        sort($filteroptions);
        $filteroptions = array_combine($filteroptions, $filteroptions);

        $studentfilterdata = [
            'baseurl' => $url->out(false),
            'options' => []
        ];
        foreach ($filteroptions as $name) {
            $studentfilterdata['options'][] = [
                'name' => $name,
                'value' => $name,
                'selected' => $name == $this->filter,
            ];
        }

        return $studentfilterdata;

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
