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

namespace local_booking\local\subscriber\entities;

require_once($CFG->dirroot . '/local/booking/lib.php');

use local_booking\local\subscriber\data_access\subscriber_vault;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\student;

defined('MOODLE_INTERNAL') || die();

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
     * Constructor.
     *
     * @param string $courseid  The description's value.
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;

        // define course custom fields globally
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $customfields = $handler->get_instance_data($courseid);

        foreach ($customfields as $customfield) {
            $cat = $customfield->get_field()->get_category()->get('name');

            if ($cat == get_booking_config('ATO')) {
                // split textarea values into cleaned up array values
                if ($customfield->get_field()->get('type') == 'textarea') {
                    $fieldvalues = array_filter(preg_split('/\n|\r\n?/', format_text($customfield->get_value(), FORMAT_MARKDOWN)));

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
     * @param bool $includeonhold   Whether to include on-hold students as well
     * @return {Object}[]   Array of active students.
     */
    public function get_active_students(bool $includeonhold = false) {
        $activestudents = [];
        $studentrecs = participant_vault::get_active_students($this->courseid, $includeonhold);
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
     * @param bool $courseadmins Indicates whether the instructors returned are part of course admins
     * @return {Object}[]   Array of active instructors.
     */
    public function get_active_instructors(bool $courseadmins = false) {
        $activeinstructors = [];
        $instructorrecs = participant_vault::get_active_instructors($this->courseid, $courseadmins);

        foreach ($instructorrecs as $instructorrec) {
            $instructor = new instructor($this->courseid, $instructorrec->userid);
            $instructor->populate($instructorrec);
            $activeinstructors[] = $instructor;
        }

        return $activeinstructors;
    }

    /**
     * Get subscribing course senior instructors list.
     *
     * @return {Object}[]   Array of active instructors.
     */
    public function get_senior_instructors() {
        return $this->get_active_instructors(true);
    }

    /**
     * Get all senior instructors for the course.
     *
     * @return {Object}[]   Array of course's senior instructors.
     */
    public function get_active_participants() {
        $participants = array_merge(participant_vault::get_active_students($this->courseid), participant_vault::get_active_instructors($this->courseid));
        return $participants;
    }

    /**
     * Returns the subscribed course section name containing the exercise
     *
     * @param int $courseid The course id of the section
     * @param int $exerciseid The exercise id in the course inside the section
     * @return string  The section name of a course associated with the exercise
     */
    public static function get_section_name(int $courseid, int $exerciseid) {
        return subscriber_vault::get_subscriber_section_name($courseid, $exerciseid);
    }

    /**
     * Retrieves exercises for the course
     *
     * @return array
     */
    public function get_exercises() {
        $exercises = [];

        $exerciserecs = subscriber_vault::get_subscriber_exercises($this->courseid);

        foreach ($exerciserecs as $exerciserec) {
            $exerciseitem = $exerciserec->modulename == 'assign' ? (object) [
                'exerciseid'    => $exerciserec->exerciseid,
                'exercisename'  => $exerciserec->assignname
            ] : (object) [
                'exerciseid'    => $exerciserec->exerciseid,
                'exercisename'  => $exerciserec->exam
            ];

            $exercises[$exerciserec->exerciseid] = $exerciseitem;
        }

        return $exercises;
    }

    /**
     * Retrieves the exercise name of a specific exercise
     * based on its id statically.
     *
     * @param int $exerciseid The exercise id.
     * @return string
     */
    public static function get_exercise_name($exerciseid) {
        return subscriber_vault::get_subscriber_exercise_name($exerciseid);
    }
}
