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

namespace local_booking\local\views;

use moodle_url;
use renderer_base;
use single_button;
use moodle_page;

/**
 * Class manage_action_bar - Display the action bar
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_action_bar extends base_action_bar {

    /**
     * @var array $additional Additional criteria for navigation selection.
     */
    protected $additional;

    /**
     * manage_action_bar constructor
     *
     * @param moodle_page $page      The page object
     * @param string      $type      The page type rendering the action bar
     * @param array      $additional The page type rendering the action bar
     */
    public function __construct(moodle_page $page, string $type, ?array $additional = null) {
        $this->additional = $additional;
        parent::__construct($page, $type);
    }

    /**
     * The template that this tertiary nav should use.
     *
     * @return string
     */
    public function get_template(): string {
        return 'local_booking/action_bar';
    }

    /**
     * Export the action bar
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $elements = [];

        switch ($this->type) {
            case 'availability':
                $elements = $this->generate_availability_navigation();
                break;

            case 'interim-booking':
                $elements = $this->generate_interim_booking_navigation();
                break;

            case 'logbook':
                $elements = $this->generate_logbook_navigation();
                break;

            case 'profile':
                $elements = $this->generate_profile_navigation();
                break;
        }

        return $this->export_elements($output, $elements);
    }

    /**
     * Get actions for availability page to post availability slots to be displayed
     * in the tertiary navigation.
     *
     * @return array
     */
    protected function generate_availability_navigation(): array {

        $elements = [];
        $access = has_capability('local/booking:view', $this->page->context) ? LOCAL_BOOKING_INSTRUCTORROLE : 'student';
        $groupview = optional_param('view', 'user', PARAM_RAW);
        $attributes = ['class'=>'slot-button-gray', 'data-region'=>'book-button', 'id'=>'book_button'];

        // availability posting actions for students
        if ($access == LOCAL_BOOKING_INSTRUCTORROLE && !$groupview) {

            $elements['button'] = new single_button(new moodle_url('/local/booking/availability'), get_string('booksave', 'local_booking'), 'submit', single_button::BUTTON_PRIMARY, $attributes);

        } else if ($access == 'student') {

            $elements['button'] = new single_button(new moodle_url('/local/booking/availability', $attributes), get_string('back'), 'get');
            $elements['button'] = new single_button(new moodle_url('/local/booking/availability', $attributes), get_string('back'), 'get');
            $elements['button'] = new single_button(new moodle_url('/local/booking/availability', $attributes), get_string('back'), 'get');
            $elements['button'] = new single_button(new moodle_url('/local/booking/availability', $attributes), get_string('back'), 'get');

        }
        return $elements;
    }

    /**
     * Get actions for the interim booking page to select the availability slots to be displayed
     * in the tertiary navigation.
     *
     * @return array
     */
    protected function generate_interim_booking_navigation(): array {
        $buttons = [];
        $buttons[] = $this->get_back_button('dashboard');
        $attributes = ['data-region'=>'back-button', 'id'=>'continue_button'];

        $buttons[] = new single_button(new moodle_url('/local/booking/availability.php', $attributes), get_string('continue'), 'post', single_button::BUTTON_PRIMARY, $attributes);

        return $buttons;
    }

    /**
     * Get actions for the logbook page for both EASA and standard course
     * formats to be displayed in the tertiary navigation.
     *
     * @return array
     */
    protected function generate_logbook_navigation(): array {
        global $COURSE;

        $buttons = [];

        $easabuttonlabel     = get_string('logbookformateasa', 'local_booking') . ' ' . get_string('logbook', 'local_booking');

        $easabutton          = new single_button(new moodle_url($this->page->url->out(), ['format'=>'easa']), $easabuttonlabel, 'post', single_button::BUTTON_PRIMARY);
        $easabutton->tooltip = get_string('logbookformateasatip', 'local_booking');
        $buttons[]           = $easabutton;

        $stdbuttonlabel     = $COURSE->shortname . ' ' . get_string('logbook', 'local_booking');

        $stdbutton          = new single_button(new moodle_url($this->page->url->out(), ['format'=>'std']), $stdbuttonlabel, 'post', single_button::BUTTON_PRIMARY);
        $stdbutton->tooltip = get_string('logbookformatcourse', 'local_booking');
        $buttons[]          = $stdbutton;

        return $buttons;
    }

    /**
     * Get actions for the profile page navigation elements
     * to be displayed in the tertiary navigation.
     *
     * @return array
     */
    protected function generate_profile_navigation(): array {
        return [$this->get_back_button('profile')];
    }

    /**
     * Get actions for the profile page navigation elements
     * to be displayed in the tertiary navigation.
     *
     * @param string $pagetag The tag for the page to go back to
     * @return single_button
     */
    protected function get_back_button(string $pagetag): single_button {
        global $COURSE;
        $params = ['courseid'=>$COURSE->id];
        $pagefile = '';

        switch ($pagetag) {
            case 'dashboard':
            case 'profile':
                $pagefile = 'view.php';
                break;

            case 'report':
                $pagefile = 'profile.php';
                $params += ['userid'=>$this->additional['userid']];
                break;
        }
        $attributes = ['data-region'=>'back-button', 'id'=>'back_button'];

        $backbutton = new single_button(new moodle_url($pagefile, $params), get_string('back'), 'get', single_button::BUTTON_PRIMARY, $attributes);

        return $backbutton;
    }

    /**
     * Export elements to rendered string to be displayed
     * in the tertiary navigation.
     *
     * @param renderer_base $output
     * @param array         $elements
     * @return array
     */
    protected function export_elements(renderer_base $output, array $elements): array {

        foreach ($elements as $key => $element) {
            $elements[$key] = (object) ['navitem' => $output->render($element)];
        }

        return ['renderedcontent' => $elements];
    }
}

/**
 * Class for outputing renderable simple text label in the tertiary navigation bar.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_label implements \renderable {

    /**
     * @var string Button label
     */
    public $html;

    /**
     * Constructor
     * @param string $html button text
     */
    public function __construct(string $html) {
        $this->html = $html;
    }

    /**
     * Export data.
     *
     * @param renderer_base $output Renderer.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {

        $data = new \stdClass();
        $data->id = \html_writer::random_id('text_label');
        $data->html = $this->html;

        return $data;
    }
}
