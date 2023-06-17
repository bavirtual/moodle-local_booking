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

require_once($CFG->dirroot . '/local/booking/classes/external/student_profile_exporter.php');

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

        // get user type: instructor|student
        $user = $data['subscriber']->get_participant($data['userid']);
        $role = $data['role'];
        $template = 'local_booking/' . $role .'_profile';

        parent::__construct($context, $courseid, $data, $template);

        // set class properties
        $this->data['courseid'] = $courseid;
        $related = [
            'context'   => $this->context,
        ];

        // dynamically load the right exporter based on role
        $class = $role . '_profile_exporter';
        $profileexporter = "\\local_booking\\external\\$class";
        $profile = new $profileexporter($this->data, $related);
        $this->exporteddata = $profile->export($this->renderer);
    }
}
