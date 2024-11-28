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
use renderer_base;
use local_booking_renderer;
use local_booking\external\list_exercise_name_exporter;
use local_booking\local\subscriber\entities\subscriber;

/**
 * Abstract class for the Session booking page view output.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_view {

    /**
     * @var \renderer_base $renderer The course contenxt.
     */
    protected $renderer;

    /**
     * @var array $data The data needed to render the page.
     */
    protected $data;

    /**
     * @var array $related The related objects to pass.
     */
    protected $related;

    /**
     * @var stdClass $exporteddata The exported data from the exporter.
     */
    protected $exporteddata;

    /**
     * @var string $template The template name for the page renderer.
     */
    protected $template;

    /**
     * base view constructor.
     *
     * @param \context  $context   The course context.
     * @param int       $courseid  The course id for context.
     * @param stClass   $data      The data class.
     * @param string    $template  The template used for output.
     */
    public function __construct(array $data, array $related, string $template) {
        global $PAGE;

        $this->renderer = $PAGE->get_renderer('local_booking');
        $this->related = $related;
        $this->data = $data;
        $this->template = $template;
    }

    /**
     * Returns the context object.
     *
     * @return \context
     */
    public function get_context() {
        return $this->related['context'];
    }

    /**
     * Returns the renderer object.
     *
     * @return local_booking_renderer
     */
    public function get_renderer() {
        return $this->renderer;
    }

    /**
     * Returns the data class to be rendered by the template.
     *
     * @return stdClass
     */
    public function get_exported_data() {
        return $this->exporteddata;
    }

    /**
     * Get the view output.
     *
     * @param   ?string   $template     Optional template to be rendered
     * @param   ?stdClass $exporteddata Optional data to be rendered in the template
     * @return  string
     */
    public function output(?string $template = null, ?stdClass $exporteddata = null):string {
        $rendertemplate = $template ?: $this->template;
        $renderdata = $exporteddata ?: $this->exporteddata;
        return $this->renderer->render_from_template($rendertemplate, $renderdata);
    }

    /**
     * Retrieves modules (exercises & quizes) for the course
     *
     * @param renderer_base $output     The renderer for output
     * @param subscriber    $subscriber The subscribing course
     * @param array         $options    The options for selecitng returend modules
     * @return array
     */
    public static function get_modules(renderer_base $output, subscriber $subscriber, array $options) {
        // get titles from the course custom fields exercise titles array
        $modsexport = [];

        $exercisetitles = 'exercisetitles';
        // TODO: PHP9 deprecates dynamic properties
        $titlevalues = array_values($subscriber->$exercisetitles);
        $modules = $subscriber->get_modules(true);

        foreach($modules as $module) {
            // exclude quizes from interim booking view
            if ($options['viewtype'] == 'confirm' && $module->modname == 'quiz') {
                $customtitle = array_shift($titlevalues);
                continue;
            }

            // break down each setting title by <br/> tag, until a better way is identified
            $customtitle = array_shift($titlevalues);
            $title = $customtitle ?: $module->name;
            $data = [
                'exerciseid'    => $module->id,
                'exercisename'  => $module->name,
                'exercisetype'  => $module->modname,
                'exercisetitle' => $title,
            ];

            // show the graduation exercise booking option for examiners only or student view
            if ($options['isinstructor'] || $options['readonly']) {
                if ((has_capability('mod/assign:grade', \context_module::instance($module->id)) &&
                    $options['viewtype'] == 'confirm') || $options['viewtype'] != 'confirm') {
                        $exercisename = new list_exercise_name_exporter($data);
                        $modsexport[] = $exercisename->export($output);
                }
            }
        }

        // pop exams
        if (array_key_exists('excludeexams', $options) && $options['excludeexams']) {
            unset($modsexport[array_search($subscriber->get_graduation_exercise(), array_column($modsexport, 'exerciseid'))]);
        }

        // // pop quizes
        if (array_key_exists('excludequizes', $options) && $options['excludequizes']) {
            $filtered = array_filter($modsexport, function($property) { return ($property->exercisetype == 'assign');});
            $modsexport = array_values($filtered);
        }

        return $modsexport;
    }
}
