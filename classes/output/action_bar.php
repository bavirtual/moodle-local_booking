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

namespace local_booking\output;

use moodle_url;
use renderer_base;
use single_button;
use moodle_page;

/**
 * Class action_bar - Display the action bar
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_bar extends base_action_bar {

    /**
     * @var array $additional Additional criteria for navigation selection.
     */
    protected $additional;

    /**
     * action_bar constructor
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

        switch ($this->type) {
            case 'book':
            case 'view':
                $elements = $this->generate_booking_navigation();
                break;

            case 'confirm':
                $elements = $this->generate_confirm_booking_navigation($output);
                break;

            case 'calendar':
                $elements = $this->generate_calendar_navigation($output);
                break;

            case 'logbook':
                $elements = $this->generate_logbook_navigation($output);
                break;

            case 'report':
                $elements = $this->generate_report_navigation();
                break;
        }

        // return $this->export_elements($output, $elements);
        return $elements;
    }

    /**
     * Get actions for the session booking page for labels, student search,
     * and sort options in the tertiary navigation.
     *
     * @return array
     */
    protected function generate_booking_navigation(): array {
        $elements = (array) $this->additional['bookingparams'];
        $elements['justify'] = 'justify-content-left';
        $elements['bookingview'] = true;
        $elements['userselect'] = $this->type == 'book' ? $this->users_selector($this->additional['studentid']) : false;

        return $elements;
    }

    /**
     * Get actions for the confirm booking page to display student available slots
     * and confirm booking in the tertiary navigation.
     *
     * @param renderer_base $output
     * @return array
     */
    protected function generate_confirm_booking_navigation(renderer_base $output): array {

        $elements = (array) $this->additional['bookingparams'];
        $elements['justify'] = 'justify-content-left';
        $elements['posts'] = $this->additional['bookingparams']->activestudents[0]->posts;
        $elements['confirmview'] = true;

        return $elements;
    }

    /**
     * Get actions for availability page to manage availability slots
     * in the tertiary navigation.
     *
     * @param renderer_base $output
     * @return array
     */
    protected function generate_calendar_navigation(renderer_base $output): array {
        return (array) $this->additional['calendarparams'];
    }

    /**
     * Get actions for the logbook page for both EASA and standard course
     * formats to be displayed in the tertiary navigation.
     *
     * @param renderer_base $output
     * @return array
     */
    protected function generate_logbook_navigation(renderer_base $output): array {

        $buttons = [];
        $easabuttonlabel     = get_string('logbookformateasa', 'local_booking') . ' ' . get_string('logbook', 'local_booking');
        $easabutton          = new single_button(new moodle_url($this->page->url->out(), ['format'=>'easa']), $easabuttonlabel, 'post', single_button::BUTTON_PRIMARY);
        $easabutton->tooltip = get_string('logbookformateasatip', 'local_booking');
        $buttons[]           = $easabutton;

        $stdbuttonlabel     = $this->additional['course']->get_shortname() . ' ' . get_string('logbook', 'local_booking');
        $stdbutton          = new single_button(new moodle_url($this->page->url->out(), ['format'=>'std']), $stdbuttonlabel, 'post', single_button::BUTTON_PRIMARY);
        $stdbutton->tooltip = get_string('logbookformatcourse', 'local_booking');
        $buttons[]          = $stdbutton;

        $elements = $this->export_elements($output, $buttons);
        $elements['justify'] = 'justify-content-center';

        return $elements;
    }

    /**
     * Get actions for the report page navigation
     * to be displayed in the tertiary navigation.
     *
     * @return array
     */
    protected function generate_report_navigation(): array {
        return [$this->get_back_button('report')];
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

        $pagefile = "/local/booking/$pagetag.php";
        $params += ['userid'=>$this->additional['userid']];

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

        $lastitem = array_key_last($elements);
        foreach ($elements as $key => $element) {
            $elements[$key] = (object) [
                'navitem' => $output->render($element),
                'lastitem' => $key == $lastitem
            ];
        }

        return ['renderedcontent' => $elements];
    }
    /**
     * Renders the user selector trigger element.
     *
     * @param int|null $userid The user ID.
     * @param int|null $groupid The group ID.
     * @return string The raw HTML to render.
     */
    protected function users_selector(int $userid = null, ?int $groupid = null): string {
        global $PAGE;

        $subscriber = $this->additional['course'];
        $course = $subscriber->get_course();
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $resetlink = new moodle_url('/local/booking/view.php', ['courseid' => $course->id]);
        $baseurl = new moodle_url('/local/booking/view.php', ['courseid' => $course->id]);
        $usersearch = $userid ? $subscriber->get_student($userid)->get_name() : '';
        $PAGE->requires->js_call_amd('local_booking/user_search', 'init', [$baseurl->out(false)]);

        if ($userid) {
            $user = \core_user::get_user($userid);
            $usersearch = fullname($user);
        }

        return $courserenderer->render(
            new \core_course\output\actionbar\user_selector(
                course: $course,
                resetlink: $resetlink,
                userid: $userid,
                groupid: $groupid,
                usersearch: $usersearch
            )
        );
    }
}

/**
 * Class for outputting renderable simple text label in the tertiary navigation bar.
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
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {

        $data = new \stdClass();
        $data->id = \html_writer::random_id('text_label');
        $data->html = $this->html;

        return $data;
    }
}
