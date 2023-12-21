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
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\entities;

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/grade/grade_grade.php');
require_once($CFG->dirroot . '/grade/grading/lib.php');

use assign;
use local_booking\local\session\data_access\grading_vault;
use local_booking\local\participant\entities\participant;

defined('MOODLE_INTERNAL') || die();
define('MOODLE_REPOSITORY_ID', 4);

/**
 * Class representing a grade for course exercise session.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade extends \grade_grade {

    /**
     * @var \stdClass $gradeinfo The grade info class.
     */
    public $gradeinfo;

    /**
     * @var string $gradername The instructor name grading.
     */
    public $gradername;

    /**
     * @var array $attempts The grade attempts.
     */
    public $attempts;

    /**
     * @var \assign $assign The assignment associated with this grade.
     */
    protected $assign;

    /**
     * @var \stdClass $usergrade The user grade item associated with this grade.
     */
    protected $usergrade;

    /**
     * @var int $exerciseid The exercise id for the grade.
     */
    protected $exerciseid;

    /**
     * @var \stored_file $feedbackfile The grade attached feedback file.
     */
    protected $feedbackfile;

    /**
     * @var bool $hasrubric Whether the grade has rubric grading.
     */
    protected $hasrubric;

    /**
     * Constructor.
     *
     * @param {object}  $coursemodgrade The grader user id of the grade.
     * @param int       $userid         The user id of the student of the grade.
     * @param int       $exerciseid     The exercise id of the grade.
     * @param bool      $force          Whether to force loading the grade object even before a final grade is assigned.
     * @param bool      $getattempts    Whether to retrieve students attempts for the exercise.
     */
    public function __construct(int $gradeitemid, int $userid, int $exerciseid, bool $force = false, bool $getattempts = false) {
        parent::__construct(array('userid'=>$userid, 'itemid'=>$gradeitemid));

        if (!empty($this->finalgrade) || $force) {
            $this->exerciseid = $exerciseid;
            $this->gradername = participant::get_fullname($this->usermodified ?: $userid);
            $this->load_grade_item();
            $this->gradeinfo = ((object) grade_get_grades(
                $this->grade_item->courseid,
                $this->grade_item->itemtype,
                $this->grade_item->itemmodule,
                $this->grade_item->iteminstance,
                $userid))->items[0];

            if ($this->grade_item->itemmodule == 'quiz') {
                $this->attempts = quiz_get_user_attempts($this->grade_item->iteminstance, $userid);
            }

            // get attempts if requested
            if ($getattempts) {
                $this->attempts = grading_vault::get_student_exercise_attempts($this->grade_item->courseid, $userid, $exerciseid);
                // get the grade nmae for each attempt
                foreach ($this->attempts as $attempt) {
                    $attempt->gradename = $this->get_grade_name($attempt->grade);
                }
            }
        }
    }

    /**
     * Get the exercise assignment.
     *
     * @return \assign
     */
    public function get_assignment() {

        // get the assignment to get the associated feedback comments
        if (!isset($this->assign)) {
            list ($course, $cm) = get_course_and_cm_from_cmid($this->exerciseid, 'assign');
            $this->assign = new \assign($this->get_context(), $cm, $course);
        }

        return $this->assign;
    }

    /**
     * Get the studnet grade exercise id of the grade.
     *
     * @return int
     */
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    /**
     * Get the studnet user id of the grade.
     *
     * @return int
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * Get the user grade item for the grade.
     *
     * @param int $attempt The assignment grade attempt to be evaluate.
     * @return stdClass
     */
    public function get_user_grade_attempt(int $attempt = 0) {
        if (!isset($this->usergrade)) {
            // find the attempt last successful attempt otherwise return the latest grade submission attempt
            $this->usergrade = $this->get_assignment()->get_user_grade($this->userid, false, $attempt);
        }

        return $this->usergrade;
    }

    /**
     * Get subscribing course grading item for a module
     *
     * @param int      $courseid The subscribing course id
     * @param \cm_info $mod      The exercise module requiring the grade item
     * @return array
     */
    public static function get_grading_item(int $courseid, \cm_info $mod) {
        // get grading items for all modules
        $params = array('itemtype' => 'mod',
            'itemmodule' => $mod->modname,
            'iteminstance' => $mod->instance,
            'courseid' => $courseid,
            'itemnumber' => 0);

        return \grade_item::fetch($params);
    }

    /**
     * Get grade name
     *
     * @param int $finalgrade The final grade
     * @return array
     */
    public function get_grade_name(int $finalgrade) {
        // get grading items for all modules
        $scale = grading_vault::get_exercise_gradeitem_scale($this->grade_item->scaleid);
        return trim($scale[$finalgrade-1]);
    }

    /**
     * Get the student's grade feedback comments.
     *
     * @return string
     */
    public function get_feedback_comments() {
        return $this->gradeinfo->grades[$this->userid]->str_feedback;
    }

    /**
     * Get the grade feedback file.
     *
     * @param string $component The assignment component
     * @param string $filearea  The assignment file area
     * @param string $itemid    The assignment grade item
     * @param bool   $path      Whether to return the path or the Stored_file
     * @param int    $attempt The assignment grade attempt to be evaluate.
     * @return string
     */
    public function get_feedback_file(string $component, string $filearea, string $itemid = '', $path = true, int $attempt = 0) {
        global $COURSE;

        $retval = null;
        if (!isset($this->feedbackfile)) {

            // get the grade item id
            $itemid = $itemid ?: $this->get_user_grade_attempt($attempt)->id;

            // get the file storage object
            $fs = get_file_storage();

            // get grade feedback files from storage
            $files = $fs->get_area_files($this->get_context()->id, $component, $filearea, $itemid);

            // get the right stored file record
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (get_class($file) == 'stored_file' && $file->get_filename() != '.') {
                        // verify the correct file name convension otherwise the file is not an evaluation form
                        $evalfiletemplate = pathinfo($COURSE->subscriber->get_examinerformfile(LOCAL_BOOKING_EVALUATIONFORM)['filename'], PATHINFO_FILENAME);
                        if (substr($file->get_filename(), 0, strlen($evalfiletemplate)) == $evalfiletemplate) {
                            $this->feedbackfile = $file;
                            break;
                        }
                    }
                };
                unset($files);

                // get the path or the stored file
                if ($this->feedbackfile)
                    $retval = $path ? $fs->get_file_system()->get_local_path_from_storedfile($this->feedbackfile) : $this->feedbackfile;
            }
        }

        return $retval;

    }

    /**
     * Get the grade feedback file.
     *
     * @param string $feedbackfile The feedback file path & name to be uploaded
     * @param bool   $path         Whether to return the path or the Stored_file
     * @param int    $attempt The assignment grade attempt to be evaluate.
     * @return string|\stored_file
     */
    public function save_feedback_file(string $feedbackfile, $path = true, int $attempt = 0) {
        global $USER;

        $fs = get_file_storage();
        $context = \context_user::instance($USER->id);
        $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area($draftitemid, $context->id, 'assignfeedback_file', 'feedback_files', 1);

        $stagedfile = array(
            'contextid' => $context->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => basename($feedbackfile)
        );

        // upload the file
        $file = $fs->create_file_from_pathname($stagedfile, $feedbackfile);

        // Create formdata.
        $data = new \stdClass();
        $data->{'files_' . $this->userid . '_filemanager'} = $draftitemid;

        // This is the first time that we are submitting feedback, so it is modified.
        $plugin = $this->get_assignment()->get_feedback_plugin_by_type('file');
        $plugin->is_feedback_modified($this->get_user_grade_attempt($attempt), $data);
        // Save the feedback.
        $plugin->save($this->get_user_grade_attempt($attempt), $data);
        $this->get_feedback_file('assignfeedback_file', 'feedback_files', '', $path);

        return $path ? $fs->get_file_system()->get_local_path_from_storedfile($this->feedbackfile) : $this->feedbackfile;
    }

    /**
     * Get the student's rubric grade info.
     *
     * @param int    $attempt The assignment grade attempt to be evaluate.
     * @return string[]
     */
    public function get_graderubric(int $attempt = 0) {

        $rubric = array();

        $gradingmgr = get_grading_manager($this->get_context(), 'mod_' . $this->gradeinfo->itemmodule, 'submissions');

        // get the grading instances from the assignment grade
        if ($controller = $gradingmgr->get_active_controller()) {

            $itemid = $this->get_assignment()->get_user_grade($this->userid, false, $attempt)->id;
            $instances = $controller->get_active_instances($itemid);

            // for each grading instance get the rubric information in an array
            foreach ($instances as $instance) {

                // get the criteria for the rubric questions for this grade
                $criteria = $instance->get_controller()->get_definition()->rubric_criteria;

                // get the filled values by the instructor for this grade
                $values = $instance->get_rubric_filling();

                // fill the return data from the criteria and values
                foreach ($criteria as $idx => $criterion) {
                    $criterianame = $criteria[$idx]['description'];
                    $criteriagrade = $criteria[$idx]['levels'][$values['criteria'][$idx]['levelid']]['definition'];
                    $criteriafeedback = $values['criteria'][$idx]['remark'];
                    $rubric[$idx] = array(
                        'name'     => $criterianame,
                        'grade'    => $criteriagrade,
                        'feedback' => $criteriafeedback
                    );
                }
            }
        }

        return $rubric;
    }

    /**
     * Whether the grade has rubric grading.
     *
     * @return bool
     */
    public function has_rubric() {

        if (!isset($this->hasrubric)){

            // get the context for the grading manager
            $gradingmgr = get_grading_manager($this->get_context(), 'mod_' . $this->gradeinfo->itemmodule, 'submissions');
            $this->hasrubric = $gradingmgr->get_active_method() == 'rubric';
        }

        return $this->hasrubric;
    }
}
