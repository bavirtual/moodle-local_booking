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

namespace local_booking\navigation\views;

use renderable;
use renderer_base;
use moodle_page;
use navigation_node;
use templatable;

/**
 * Abstract class for the Session booking tertiary navigation. The class initialises the page and type class variables.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_action_bar implements renderable, templatable {

    /**
     * @var moodle_page $page The context we are operating within.
     */
    protected $page;

    /**
     * @var string $type The type of page being rendered.
     */
    protected $type;

    /**
     * standard_action_bar constructor.
     *
     * @param moodle_page $page
     * @param string $type
     */
    public function __construct(moodle_page $page, string $type) {
        $this->page = $page;
        $this->type = $type;
    }

    /**
     * The template that this tertiary nav should use.
     *
     * @return string
     */
    abstract public function get_template(): string;
}
