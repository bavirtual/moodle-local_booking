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
 * Handles redirect after grading to the checklist grading page.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Redirect handler for post-grading redirect
 *
 * @package    local_booking
 */
class redirect_handler implements renderable, templatable {

    /** @var string Redirect URL */
    private $redirecturl;

    /** @var int Delay in milliseconds */
    private $delay;

    /**
     * Constructor
     *
     * @param string $redirecturl
     * @param int $delay
     */
    public function __construct($redirecturl, $delay = 2000) {
        $this->redirecturl = $redirecturl;
        $this->delay = $delay;
    }

    /**
     * Export for template
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        return (object) [
            'redirecturl' => $this->redirecturl,
            'delay' => $this->delay,
        ];
    }
}
