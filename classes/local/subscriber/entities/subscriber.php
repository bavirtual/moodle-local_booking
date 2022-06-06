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
require_once($CFG->dirroot . '/group/lib.php');

use local_booking\local\subscriber\data_access\subscriber_vault;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\participant;
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
     * @var \context_course $context The subscribed course context.
     */
    protected $context;

    /**
     * @var int $course The global course.
     */
    protected $course;

    /**
     * @var int $courseID The subscribed course.
     */
    protected $courseid;

    /**
     * @var string $fullname The subscribed course fullname.
     */
    protected $fullname;

    /**
     * @var string $shortname The subscribed course shortname.
     */
    protected $shortname;

    /**
     * @var array $activestudents An array of course active students.
     */
    protected $activestudents;

    /**
     * @var array $activeinstructorss An array of course active instructors.
     */
    protected $activeinstructors;

    /**
     * Constructor.
     *
     * @param string $courseid  The description's value.
     */
    public function __construct($courseid) {
        global $COURSE;
        $this->context = \context_course::instance($courseid);
        $this->course = $COURSE;
        $this->courseid = $courseid;
        $this->fullname = $COURSE->fullname;
        $this->shortname = $COURSE->shortname;
        $this->ato = get_booking_config('ATO');

        // define course custom fields globally
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $customfields = $handler->get_instance_data($courseid, true);

        foreach ($customfields as $customfield) {
            $cat = $customfield->get_field()->get_category()->get('name');

            if ($cat == $this->ato->name) {
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
                    // get the field value checking dropdown selects as well
                    $value = $customfield->get_field()->get('type') == 'select' ? $customfield->export_value() : $customfield->get_value();
                }

                $this->{$customfield->get_field()->get('shortname')} = $value;
            }
        }

        if ($this->subscribed)
            // verify groups exist
            if (!$this->verify_groups())
                throw new \Exception('Unable to create needed course groups.');
    }

    /**
     * Get the subscriber's course id.
     *
     * @return int $courseid
     */
    public function get_id() {
        return $this->courseid;
    }

    /**
     * Get the subscriber's course context.
     *
     * @return \context_course $context
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Get the subscriber's course fullname.
     *
     * @return string $fullname
     */
    public function get_fullname() {
        return $this->fullname;
    }

    /**
     * Get the subscriber's course shortname.
     *
     * @return string $shortname
     */
    public function get_shortname() {
        return $this->shortname;
    }

    /**
     * Set the subscriber's course shortname.
     *
     * @param string $shortname
     */
    public function set_shortname(string $shortname) {
        $this->shortname = $shortname;
    }

    /**
     * Get an active participant.
     *
     * @param int $participantid A participant user id.
     * @param bool $populate     Whether to get the participant data.
     * @param bool $active       Whether the participant is active.
     * @return participant       The participant object
     */
    public function get_participant(int $participantid, bool $populate = false, bool $active = true) {
        // instantiate the participant object
        $participant = new participant($this, $participantid);

        if ($populate) {
            $participantrec = participant_vault::get_participant($this->courseid, $participantid, $active);
            if (!empty($participantrec->userid))
                $participant->populate($participantrec);
        }

        return $participant;
    }

    /**
     * Get all senior instructors for the course.
     *
     * @return {Object}[]   Array of course's senior instructors.
     */
    public function get_active_participants() {
        $participants = array_merge(participant_vault::get_students($this->courseid), participant_vault::get_active_instructors($this->courseid));
        return $participants;
    }

    /**
     * Get a student.
     *
     * @param int  $studentid   A participant user id.
     * @param bool $populate    Whether to get the student data.
     * @param bool $active      Whether the student has an active enrolment.
     * @return student          The student object
     */
    public function get_student(int $studentid, bool $populate = false, bool $active = true) {
        // instantiate the participant object
        $student = new student($this, $studentid);

        if ($populate) {
            $studentrec = participant_vault::get_participant($this->courseid, $studentid, $active);
            if (!empty($studentrec->userid))
                $student->populate($studentrec);
        }

        return $student;
    }

    /**
     * Get an active student.
     *
     * @param int $studentid    A specific student for booking confirmation
     * @return student $student The active student object.
     */
    public function get_active_student(int $studentid) {
        $student = (!empty($this->activestudents) && !empty($studentid) && array_key_exists($studentid, $this->activestudents)) ? $this->activestudents[$studentid] : null;

        if (empty($student)) {
            $studentrec = participant_vault::get_active_student($this->courseid, $studentid);
            $colors = (array) get_booking_config('colors', true);

            // add a color for the student slots from the config.json file for each student
            if (!empty($studentrec->userid)) {
                $student = new student($this, $studentrec->userid);
                $student->populate($studentrec);
                $student->set_slot_color(count($colors) > 0 ? array_values($colors)[1 % LOCAL_BOOKING_MAXLANES] : LOCAL_BOOKING_SLOTCOLOR);
                $this->activestudents[$studentid] = $student;
            }
        }

        return $student;
    }

    /**
     * Get all students active, inactive, and suspended.
     *
     * @return array $allstudents Array of active students.
     */
    public function get_all_students() {
        $allstudents = [];
        $studentrecs = participant_vault::get_students($this->courseid, true, true);
        $colors = (array) get_booking_config('colors', true);

        // add a color for the student slots from the config.json file for each student
        $i = 0;
        foreach ($studentrecs as $studentrec) {
            $student = new student($this, $studentrec->userid);
            $student->populate($studentrec);
            $student->set_slot_color(count($colors) > 0 ? array_values($colors)[$i % LOCAL_BOOKING_MAXLANES] : LOCAL_BOOKING_SLOTCOLOR);
            $allstudents[] = $student;
            $i++;
        }
        $this->allstudents = $allstudents;

        return $this->allstudents;
    }

    /**
     * Get all active students.
     *
     * @param bool $includeonhold    Whether to include on-hold students as well
     * @return array $activestudents Array of active students.
     */
    public function get_active_students(bool $includeonhold = false) {
        $activestudents = [];
        $studentrecs = participant_vault::get_students($this->courseid, $includeonhold);
        $colors = (array) get_booking_config('colors', true);

        // add a color for the student slots from the config.json file for each student
        $i = 0;
        foreach ($studentrecs as $studentrec) {
            $student = new student($this, $studentrec->userid);
            $student->populate($studentrec);
            $student->set_slot_color(count($colors) > 0 ? array_values($colors)[$i % LOCAL_BOOKING_MAXLANES] : LOCAL_BOOKING_SLOTCOLOR);
            $activestudents[] = $student;
            $i++;
        }
        $this->activestudents = $activestudents;

        return $this->activestudents;
    }

    /**
     * Get an active instructor.
     *
     * @param int $instructorid An instructor user id.
     * @return instructor       The instructor object
     */
    public function get_active_instructor(int $instructorid) {
        $instructor = (!empty($this->activeinstructors) && !empty($instructorid) && array_key_exists($instructorid, $this->activeinstructors)) ? $this->activeinstructors[$instructorid] : null;

        if (empty($instructor)) {
            // instantiate the instructor object and add to the list of activeinstructors
            $instructor = new instructor($this, $instructorid);
            $this->activeinstructors[$instructorid] = $instructor;
        }

        return $instructor;
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
            $instructor = new instructor($this, $instructorrec->userid);
            $instructor->populate($instructorrec);
            $activeinstructors[] = $instructor;
        }
        $this->activeinstructors = $activeinstructors;

        return $this->activeinstructors;
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
     * Returns the course graduation exercise as specified in the settings
     * otherwise retrieves the last exercise.
     *
     *
     * @return int The last exericse id
     */
    public function get_graduation_exercise() {
        $exerciseid = subscriber_vault::get_subscriber_exercise_by_name($this->courseid, $this->skilltestexercise);

        // check if there is an exercise specified in the settings otherwise default to last exercise
        if (empty($exerciseid))
            $exerciseid = subscriber_vault::get_subscriber_last_exercise($this->courseid);

        return $exerciseid;
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

    /**
     * Retrieves the total number of modules in a course.
     *
     * @return int
     */
    public function get_modules_count() {
        return subscriber_vault::get_subscriber_modules_count($this->courseid);
    }

    /**
     * Checks if there is a database integration
     * for the specified passed key.
     *
     * @param string $key The key associated with the integration.
     * @return bool
     */
    public static function has_integration($key) {
        $integrations = get_booking_config('integrations');
        return !empty($integrations->$key->enabled);
    }

    /**
     * Returns an array of records from integrated database
     * that matches the passed criteria.
     *
     * @param string $key    The key associated with the integration.
     * @param string $target The target data structure of the integration.
     * @param string $value  The data selection criteria
     * @return array
     */
    public static function get_integrated_data($key, $data, $value) {
        global $CFG;
        $record = null;

        // get the integration object from settings
        $integrations = get_booking_config('integrations');

        // Moodle user/password must have read access to the target host, database, and tables
        $conn = new \mysqli($integrations->$key->host, $CFG->dbuser, $CFG->dbpass, $integrations->$key->db);

        $target = $integrations->$key->data;
        $fieldnames = array_keys((array) $target->$data->fields);
        $fields = implode(',', (array) $target->$data->fields);
        $table = $target->$data->table;
        $keyfield = $target->$data->key;

        if (!$conn->connect_errno) {
            $sql = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE ' . $keyfield . ' = "' . $value . '"';
            // Return name of current default database
            if ($result = $conn->query($sql)) {
                $values = $result->fetch_row();
                if (!empty($values))
                    $record = array_combine( $fieldnames, $values);
                $result->close();
            }
            $conn->close();
        } else {
            throw new \Exception(get_string('errordbconnection', 'local_booking') . $conn->connect_error);
        }

        return $record;
    }

    /**
     * Verifies custom groups are exist otherwise create them.
     *
     * @return bool
     */
    protected function verify_groups() {
        $onholdgroupid = true;
        $inactivegroupid = true;
        $graduatesgroupid = true;

        // check if LOCAL_BOOKING_ONHOLDGROUP exists otherwise create it
        $groupid = groups_get_group_by_name($this->courseid, LOCAL_BOOKING_ONHOLDGROUP);
        if (empty($groupid)) {
            $data = new \stdClass();
            $data->courseid = $this->courseid;
            $data->name = LOCAL_BOOKING_ONHOLDGROUP;
            $data->description = get_string('grouponholddesc', 'local_booking');
            $data->descriptionformat = FORMAT_HTML;
            $onholdgroupid = groups_create_group($data);
        }

        // check if LOCAL_BOOKING_INACTIVEGROUP exists otherwise create it
        $groupid = groups_get_group_by_name($this->courseid, LOCAL_BOOKING_INACTIVEGROUP);
        if (empty($groupid)) {
            $data = new \stdClass();
            $data->courseid = $this->courseid;
            $data->name = LOCAL_BOOKING_INACTIVEGROUP;
            $data->description = get_string('groupinactivedesc', 'local_booking');
            $data->descriptionformat = FORMAT_HTML;
            $inactivegroupid = groups_create_group($data);
        }

        // check if LOCAL_BOOKING_GRADUATESGROUP exists otherwise create it
        $groupid = groups_get_group_by_name($this->courseid, LOCAL_BOOKING_GRADUATESGROUP);
        if (empty($groupid)) {
            $data = new \stdClass();
            $data->courseid = $this->courseid;
            $data->name = LOCAL_BOOKING_GRADUATESGROUP;
            $data->description = get_string('groupgraduatesdesc', 'local_booking');
            $data->descriptionformat = FORMAT_HTML;
            $graduatesgroupid = groups_create_group($data);
        }

        // check if LOCAL_BOOKING_GRADUATESGROUP exists otherwise create it
        $groupid = groups_get_group_by_name($this->courseid, LOCAL_BOOKING_KEEPACTIVEGROUP);
        if (empty($groupid)) {
            $data = new \stdClass();
            $data->courseid = $this->courseid;
            $data->name = LOCAL_BOOKING_KEEPACTIVEGROUP;
            $data->description = get_string('groupkeepactivedesc', 'local_booking');
            $data->descriptionformat = FORMAT_HTML;
            $graduatesgroupid = groups_create_group($data);
        }

        return !empty($onholdgroupid) && !empty($inactivegroupid) && !empty($graduatesgroupid);
    }
}
