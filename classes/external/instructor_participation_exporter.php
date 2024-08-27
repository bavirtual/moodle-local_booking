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
 * Class for displaying the session booking instructor participation list.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instructor_participation_exporter extends exporter {

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {

        $url = new \moodle_url('/local/booking/view.php', [
                'courseid' => $data['courseid'],
            ]);

        $data['url'] = $url->out(false);

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'url' => [
                'type' => PARAM_URL,
            ],
            'courseid' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'participation' => [
                'type' => PARAM_RAW,
                'multiple' => true,
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(\renderer_base $output) {
        global $COURSE;
        $courseid = $this->data['courseid'];
        $instructors = $COURSE->subscriber->get_instructors();
        $today = new \DateTime('@'.time());

        $participation = [];
        foreach ($instructors as $instructor) {
            $lastgradeddate = $instructor->get_last_graded_date();
            $lastsessiondate = $instructor->get_last_booked_date();
            $interval = !empty($lastgradeddate) ? date_diff($lastgradeddate, $today) : 0;

            // get instructor instructor role in the course
            $roleobjects = $instructor->get_roles();
            $rolesarray = array_map(fn($role): array => ['id'=>$role->roleid,'name'=>$role->name], $roleobjects);
            $roles = array_combine(array_column($rolesarray, 'id'), array_column($rolesarray, 'name'));
            $rolesintersect = array_intersect_key($COURSE->subscriber->get_roles(), $roles);
            $courserole = implode(', ', array_values($rolesintersect));

            $participation[] = [
                'instructorid' => $instructor->get_id(),
                'instructorname' => $instructor->get_name(),
                'lastsessionts' => !empty($lastgradeddate) ? $lastgradeddate->getTimestamp() : 0,
                'lastgradeddate' => !empty($lastgradeddate) ? $lastgradeddate->format('l M d, Y') : get_string('unknown', 'local_booking'),
                'lastsessiondate' => !empty($lastsessiondate) ? $lastsessiondate->format('l M d, Y') : get_string('unknown', 'local_booking'),
                'elapseddays' => !empty($lastgradeddate) ? $interval->days : '--',
                'roles' => $courserole,
            ];
        }
        array_multisort (array_column($participation, 'lastsessionts'), SORT_DESC, $participation);

        return ['participation' => $participation];
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
        );
    }
}
