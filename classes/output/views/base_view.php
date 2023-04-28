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
use local_booking_renderer;
;

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
     * @var course_context $context The course contenxt.
     */
    protected $context;

    /**
     * @var int $courseid The course id.
     */
    protected $courseid;

    /**
     * @var \renderer_base $renderer The course contenxt.
     */
    protected $renderer;

    /**
     * @var array $data The data needed to render the page.
     */
    protected $data;

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
    public function __construct(\context $context, int $courseid, array $data, string $template) {
        global $PAGE;

        $this->renderer = $PAGE->get_renderer('local_booking');
        $this->context = $context;
        $this->courseid = $courseid;
        $this->data = $data;
        $this->template = $template;
    }

    /**
     * Returns the context object.
     *
     * @return \context
     */
    public function get_context() {
        return $this->context;
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
}
