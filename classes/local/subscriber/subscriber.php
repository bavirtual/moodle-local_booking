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
 * Subscribed course custom fields information
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\subscriber;

use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\student;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');

/**
 * Class representing subscribed courses
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subscriber implements subscriber_interface {

    /**
     * @var int $course The subscribed course.
     */
    protected $courseid;

    /**
     * @var participant_vault $vault The vault access to the database.
     */
    protected $vault;

    /**
     * Constructor.
     *
     * @param string $courseid  The description's value.
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
        $this->vault = new participant_vault();

        // define course custom fields globally
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $customfields = $handler->get_instance_data($courseid);

        foreach ($customfields as $customfield) {
            $cat = $customfield->get_field()->get_category()->get('name');

            if ($cat == get_booking_config('ATO')) {
                // split textarea values into cleaned up array values
                if ($customfield->get_field()->get('type') == 'textarea') {
                    $fieldvalues = array_filter(explode(PHP_EOL, format_text($customfield->get_value(), FORMAT_MARKDOWN)));

                    // array callback function to strip html
                    array_walk($fieldvalues,
                        function(&$item) {
                            // strp html tags
                            $item = strip_tags($item);
                            // put back <br/> tags if exist for exercise titles
                            $item = str_replace("&lt;br/&gt;", "<br/>", $item);
                        }
                    );
                    $finalvalues = array_combine($fieldvalues, $fieldvalues);
                    $value = $finalvalues;
                } else {
                    $value = $customfield->get_value();
                }

                $this->{$customfield->get_field()->get('shortname')} = $value;
            }
        }
    }

    /**
     * Get all active students.
     *
     * @return {Object}[]   Array of active students.
     */
    public function get_active_students() {
        $activestudents = [];
        $studentrecs = $this->vault->get_active_students($this->courseid);
        $colors = (array) get_booking_config('colors', true);

        // add a color for the student slots from the config.json file for each student
        $i = 0;
        foreach ($studentrecs as $studentrec) {
            $student = new student($this->courseid, $studentrec->userid);
            $student->populate($studentrec);
            $student->set_slot_color(count($colors) > 0 ? array_values($colors)[$i % LOCAL_BOOKING_MAXLANES] : LOCAL_BOOKING_SLOTCOLOR);
            $activestudents[] = $student;
            $i++;
        }

        return $activestudents;
    }

    /**
     * Get all active instructors for the course.
     *
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_instructors() {
        $activeinstructors = [];
        $instructorrecs = $this->vault->get_active_instructors($this->courseid);

        foreach ($instructorrecs as $instructorrec) {
            $instructor = new instructor($this->courseid, $instructorrec->userid);
            $instructor->populate($instructorrec);
            $activeinstructors[] = $instructor;
        }

        return $activeinstructors;
    }

    /**
     * Get all active instructors for the course.
     *
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_participants() {
        $participants = array_merge($this->vault->get_active_students($this->courseid), $this->vault->get_active_instructors($this->courseid));
        return $participants;
    }
}
