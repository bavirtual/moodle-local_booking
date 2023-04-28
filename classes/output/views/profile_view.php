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

use local_booking\external\profile_exporter;

/**
 * Class to output student profile view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_view extends base_view {

    /**
     * calendar view constructor.
     *
     * @param \context $context   The course context
     * @param int      $courseid  The course id
     * @param array    $data      The data required for output
     */
    public function __construct(\context $context, int $courseid, array $data) {
        parent::__construct($context, $courseid, $data, 'local_booking/profile');

        // set class properties
        $this->data['courseid'] = $courseid;
        $related = [
            'context'   => $this->context,
        ];
        $profile = new profile_exporter($this->data, $related);
        $this->exporteddata = $profile->export($this->renderer);
    }
}
