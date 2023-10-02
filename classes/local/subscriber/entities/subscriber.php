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
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/group/lib.php');

use ArrayObject;
use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing subscribed course to be attached to $COURSE global variable
 * (no course class in Moodle to extend the subscriber class from)
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
     * @var array $courses Moodle courses.
     */
    protected $courses;

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
     * @var course_modinfo $coursemodinfo The Moodle course modules information.
     */
    protected $coursemodinfo;

    /**
     * @var array $activestudents An array of course active students.
     */
    protected $activestudents;

    /**
     * @var array $activeinstructorss An array of course active instructors.
     */
    protected $activeinstructors;

    /**
     * @var int $graduationexercise The exercise id for graduations. Assumes it's the last.
     */
    protected $graduationexercise;

    /**
     * @var array $lessons The subscribing course's lessons (sections).
     */
    protected $lessons;

    /**
     * @var array $resources The subscribing course's content resources.
     */
    public $resources;

    /**
     * @var array $resources The subscribing course's content resources.
     */
    public $resources;

    /**
     * @var array $modules The subscribing course's modules (exercises & quizes).
     */
    protected $modules;

    /**
     * @var array $lessonmods The subscribing course's lesson modules.
     */
    protected $lessonmods;

    /**
     * @var bool $subscribed Whether the course is subscribed to Session Booking plugin.
     */
    public $subscribed;

    /**
     * @var array $exercisetitles An array holding subscribing course exercise titles for grid UI.
     */
    public $exercisetitles;

    /**
     * @var string $vatsimrating The type of VATSIM rating for the subscribing course.
     */
    public $vatsimrating;

    /**
     * @var string $trainingtype The type of training of the subscribing course.
     */
    public $trainingtype;

    /**
     * @var string $homeicao The ICAO code of the training airport.
     */
    public $homeicao;

    /**
     * @var string[] $aircrafticao The ICAO code of the training aircraft.
     */
    public $aircrafticao = [];

    /**
     * @var bool $vatsimintegration Whether the subscribing course has VATSIM integration for examiner evaluation.
     */
    public $vatsimintegration;

    /**
     * @var int $postingwait The period the student needs to wait prior to posting slots again.
     */
    public $postingwait;

    /**
     * @var int $onholdperiod The period the student will stay on hold for.
     */
    public $onholdperiod;

    /**
     * @var int $suspensionperiod The period the student will be in suspension for.
     */
    public $suspensionperiod;

    /**
     * @var int $overdueperiod The period the instructor can stay without booking until their overdue.
     */
    public $overdueperiod;

    /**
     * Constructor.
     *
     * @param string $courseid  The description's value.
     */
    public function __construct($courseid) {

        // check if called at the site level from other than subscribing courses
        if ($courseid == 1) {
            // throw error
            // throw new \Exception(get_string('errorcoresubscriber', 'local_booking') . " [courseid=$courseid]");
            return;
        }

        $this->coursemodinfo = get_fast_modinfo($courseid);
        $this->context = \context_course::instance($courseid);
        $this->course = get_course($courseid);
        $this->courseid = $courseid;
        $this->fullname = $this->course->fullname;
        $this->shortname = $this->course->shortname;

        // filter exercise and quiz modules only
        $cms = $this->coursemodinfo->get_cms();
        $this->lessons = $this->coursemodinfo->get_section_info_all();
        $this->modules = array_filter($cms, function($property) { return ($property->modname == 'assign' || $property->modname == 'quiz');});
        $this->lessonmods = array_filter($cms, function($property) { return ($property->modname == 'lesson');});
        $this->resources = array_filter($cms, function($property) { return ($property->modname == 'resource');});

        // define course custom fields globally
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $customfields = $handler->get_instance_data($courseid, true);

        foreach ($customfields as $customfield) {
            $cat = $customfield->get_field()->get_category()->get('name');

            if ($cat == ucfirst(get_string('pluginname', 'local_booking'))) {
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
                    $value = array_filter($finalvalues);
                } else {
                    // get the field value checking dropdown selects as well
                    $value = $customfield->get_field()->get('type') == 'select' ? $customfield->export_value() : $customfield->get_value();
                }

                $this->{$customfield->get_field()->get('shortname')} = $value;
            }
        }

        // verify that the subscribing course has needed course groups for Session Booking
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
     * Retrieves a Moodle course based on the courseid.
     *
     * @param int  $courseid  The course id.
     * @return string
     */
    public function get_course(int $courseid) {
        if (!isset($this->courses)) {
            $this->courses = \get_courses();
        }

        return $this->courses[$courseid];
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
    public function get_participants() {
        $participants = array_merge(participant_vault::get_students($this->courseid), participant_vault::get_instructors($this->courseid));
        return $participants;
    }

    /**
     * Get a student.
     *
     * @param int  $studentid   A participant user id.
     * @param bool $populate    Whether to get the student data.
     * @param string $filter    the filter to select the student.
     * @return student          The student object
     */
    public function get_student(int $studentid, bool $populate = false, string $filter = 'active') {
        $student = (!empty($this->activestudents) && !empty($studentid) && isset($this->activestudents[$studentid])) ? $this->activestudents[$studentid] : null;

        if (empty($student)) {
            $studentrec = participant_vault::get_student($this->courseid, $studentid);
            $colors = LOCAL_BOOKING_SLOTCOLORS;

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
     * Get students based on filter.
     *
     * @param string $filter       The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold  Whether to include on-hold students as well
     * @return array $activestudents Array of active students.
     */
    public function get_students(string $filter = 'active', bool $includeonhold = false) {
        $activestudents = [];
        $studentrecs = participant_vault::get_students($this->courseid, $filter, $includeonhold);
        $colors = LOCAL_BOOKING_SLOTCOLORS;

        // add a color for the student slots from the config.json file for each student
        $i = 0;
        foreach ($studentrecs as $studentrec) {
            $student = new student($this, $studentrec->userid);
            if ($student->has_role('student') || $student->is_member_of($this->get_id(), $student->get_id(), LOCAL_BOOKING_GRADUATESGROUP)) {
                $student->populate($studentrec);
                $student->set_slot_color(count($colors) > 0 ? array_values($colors)[$i % LOCAL_BOOKING_MAXLANES] : LOCAL_BOOKING_SLOTCOLOR);
                $activestudents[] = $student;
                $i++;
            }
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
    public function get_instructor(int $instructorid) {
        $instructor = (!empty($this->activeinstructors) && !empty($instructorid) && isset($this->activeinstructors[$instructorid])) ? $this->activeinstructors[$instructorid] : null;

        if (empty($instructor)) {
            $instructorrec = participant_vault::get_participant($this->courseid, $instructorid);
            // instantiate the instructor object and add to the list of activeinstructors
            $instructor = new instructor($this, $instructorid);
            $instructor->populate($instructorrec);
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
    public function get_instructors(bool $courseadmins = false) {
        $activeinstructors = [];
        $instructorrecs = participant_vault::get_instructors($this->courseid, $courseadmins);

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
        return $this->get_instructors(true);
    }

    /**
     * Get subscribing course Flight Training Managers.
     *
     * @return array The Flight Training Manager users.
     */
    public function get_flight_training_managers() {
        $mgrs = \get_enrolled_users($this->context, 'moodle/site:approvecourse');
        $activemgrs = array_filter($mgrs, function($v, $k) {
            return $v->suspended == 0;
        }, ARRAY_FILTER_USE_BOTH);
        return $activemgrs;
    }

    /**
     * Retrieves subscribing course modules (exercises & quizes)
     *
     * @return array
     */
    public function get_modules() {
        return $this->modules;
    }

    /**
     * Retrieves subscribing course lessons
     *
     * @return array
     */
    public function get_lessons() {
        return $this->lessons;
    }

    /**
     * Returns the subscribed course lesson by the lesson module id
     *
     * @param int $lessonid The lesson id
     * @return stdClass  The lesson module
     */
    public function get_lesson_module(int $lessonid) {
        return $this->lessonmods[$lessonid];
    }

    /**
     * Returns the subscribed course section id and lesson name that contains the exercise
     *
     * @param int $exerciseid The exercise id in the course inside the section
     * @return array  The section name of a course associated with the exercise
     */
    public function get_lesson_by_exerciseid(int $exerciseid) {
        $idx = array_search($this->modules[$exerciseid]->section, array_column($this->lessons, 'id'));
        return [$this->modules[$exerciseid]->section, $this->lessons[$idx]->name];
    }

    /**
     * Returns the course graduation exercise as specified in the settings
     * otherwise retrieves the last exercise.
     *
     * @param bool $nameonly Whether to return the name instead of the id
     * @return int The last exericse id
     */
    public function get_graduation_exercise(bool $nameonly = false) {
        $modulesIterator = (new ArrayObject($this->modules))->getIterator();
        $modulesIterator->seek(count($this->modules)-1);
        $gradexerciseid = $modulesIterator->current()->id;
        return $nameonly ? $this->get_exercise_name($gradexerciseid) : $gradexerciseid;
    }

    /**
     * Retrieves the exercise name of a specific exercise
     * based on its id statically.
     *
     * @param int  $exerciseid The exercise id.
     * @param int  $courseid   The course id the exercise belongs to.
     * @return string
     */
    public function get_exercise_name(int $exerciseid, int $courseid = 0) {

        // look in another course
        if (!empty($exerciseid)) {
            if ($courseid != 0 && $courseid != $this->courseid) {
                $coursemodinfo = get_fast_modinfo($courseid);
                $mods = $coursemodinfo->get_cms();
                $modname = $mods[$exerciseid]->name;
            } else {
                $modname = $this->modules[$exerciseid]->name;
            }
        } else {
            $modname = get_string('errorexercisemissing', 'local_booking');
        }

        return $modname;
    }

    /**
     * Retrieves an array with the moodle file path and file name of a course file resource.
     *
     * @param  string The resource module name
     * @return array
     */
    public function get_examinerformfile(string $resourcename) {
        global $DB;

        // get evaluation form resource file
        $resourceidx = array_search($resourcename, array_column($this->resources,'name'));
        $cm = array_values($this->resources)[$resourceidx];
        $context = \context_module::instance($cm->id);
        $resource = $DB->get_record('resource', array('id'=>$cm->instance), '*', MUST_EXIST);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);
        if (count($files) < 1) {
            resource_print_filenotfound($resource, $cm, $this->course);
            die;
        } else {
            $file = reset($files);
            $filename = $fs->get_file_system()->get_local_path_from_storedfile($file);
            unset($files);
        }

        return ["filename"=>$file->get_filename(), "moodlefile"=>$filename];
    }

    /**
     * Returns the settings from config.xml
     *
     * @param  string $key      The key to look up the value for
     * @param  bool   $toarray  Whether to converted json file to class or an array
     * @param  string $filename The filename and path of the JSON config file
     * @return mixed  $config   The requested setting value.
     */
    public static function get_booking_config(string $key, bool $toarray = false, string $filename = null) {
        global $CFG;

        $configfile = $filename ?: $CFG->dirroot . '/local/booking/config.json';
        $config = null;

        if (file_exists($configfile)) {

            $jsoncontent = file_get_contents($configfile);
            $configdata = json_decode($jsoncontent, $toarray);
            $config = $toarray ? $configdata[$key] : $configdata->{$key};

        } else {

            var_dump(get_string('configmissing', 'local_booking', $configfile));

        }

        return $config;
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
        $integrations = self::get_booking_config('integrations');

        // Moodle user/password must have read access to the target host, database, and tables
        // TODO: PHP9 deprecates dynamic properties
        $conn = new \mysqli($integrations->$key->host, $CFG->dbuser, $CFG->dbpass, $integrations->$key->db);

        $target = $integrations->$key->data;
        $fieldnames = array_keys((array) $target->$data->fields);
        $fields = implode(',', (array) $target->$data->fields);
        $table = $target->$data->table;
        $primarykey = $target->$data->primarykey;

        if (!$conn->connect_errno) {
            $sql = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE ' . $primarykey . ' = "' . $value . '"';
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
     * Checks if there is a database integration
     * for the specified passed key.
     *
     * @param string $key The key associated with the integration.
     * @return bool
     */
    public static function has_integration($key) {
        $integrations = self::get_booking_config('integrations');
        // TODO: PHP9 deprecates dynamic properties
        return !empty($integrations->$key->enabled);
    }

    /**
     * Updates a setting in the json config.xml
     *
     * @param  string $key      The key to look up the value for
     * @param  string $value    The value to set
     */
    public static function set_booking_config(string $key, string $value) {
        global $CFG;

        $configfile = $CFG->dirroot . '/local/booking/config.json';

        if (file_exists($configfile)) {

            // read config content
            $jsoncontent = file_get_contents($configfile);
            $configdata = json_decode($jsoncontent, true);

            // recursively go through the config data for nested content
            $params = array($key, $value);
            array_walk_recursive($configdata, function(&$recursivevalue, $recursivekey, $params) {
                if ($recursivekey == $params[0])
                    $recursivevalue = $params[1];
            }, $params);

            // write back config content to json file
            $jsoncontent = json_encode($configdata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($CFG->dirroot . '/local/booking/config.json', $jsoncontent);

        } else {

            var_dump(get_string('configmissing', 'local_booking', $configfile));

        }
    }

    /**
     * Checks if the subscribing course require
     * skills evaluation.
     *
     * @return bool
     */
    public function requires_skills_evaluation() {
        return $this->vatsimintegration;
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
