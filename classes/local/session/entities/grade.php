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

use local_booking\local\participant\entities\participant;
use stdClass;

defined('MOODLE_INTERNAL') || die();

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
     * @var array $attempts The grade attempts.
     */
    public $attempts;

    /**
     * @var \assign $assign The assignment associated with this grade.
     */
    protected $assign;

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
     */
    public function __construct(int $gradeitemid, int $userid, int $exerciseid) {
        parent::__construct(array('userid'=>$userid, 'itemid'=>$gradeitemid));

        if (!empty($this->finalgrade)) {
            $this->exerciseid = $exerciseid;
            $this->gradername = participant::get_fullname($this->usermodified);
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

            // get grade mark from the course's scale
            // $scale = get_scale($coursemodgrade->scaleid);
            $scale = null;


                // $params = array('itemtype' => 'mod',
                //     'itemmodule' => 'assign',
                //     'iteminstance' => $this->course->get_modules()[$coursemodid]->instance,
                //     'courseid' => $this->course->get_id(),
                //     'itemnumber' => 0);
                // $gradeitem = \grade_item::fetch($params);

            // if (!empty($scale)) {
            //     $this->scale = explode(',', $coursemodgrade->scale);
            //     $this->grademark = $this->scale[intval($this->finalgrade)-1];
            // } else {
            //     $this->grademark = intval($this->finalgrade) . '/' . intval($this->totalgrade);
            // }
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
     * Get the studnet user id of the grade.
     *
     * @return int
     */
    public function get_userid() {
        return $this->userid;
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
     * @return string
     */
    public function get_feedback_file(string $component, string $filearea, string $itemid = '') {

        $path = '';
        if (!isset($this->feedbackfile)) {
            // get the grade item id
            $itemid = $itemid ?: $this->get_assignment()->get_user_grade($this->userid, false, 0)->id;

            // get the file storage object
            $fs = get_file_storage();

            // get grade feedback files from storage
            $files = $fs->get_area_files($this->get_context()->id, $component, $filearea, $itemid);
            $feedbackfile = new \stored_file($fs, new stdClass());

            // get the right stored file record
            if (!empty($files)) {
                array_walk($files, function($item) use (&$feedbackfile) {
                    if (get_class($item) == 'stored_file' && $item->get_filename() != '.')
                        return $feedbackfile = $item;
                });

                // get the path
                $this->feedbackfile = $feedbackfile;
                $path = $fs->get_file_system()->get_local_path_from_storedfile($this->feedbackfile);
            }
        }

        return $path;

    }

    /**
     * Get the student's rubric grade info.
     *
     * @return string[]
     */
    public function get_graderubric() {

        $rubric = array();

        $gradingmgr = get_grading_manager($this->get_context(), 'mod_' . $this->gradeinfo->itemmodule, 'submissions');

        // get the grading instances from the assignment grade
        if ($controller = $gradingmgr->get_active_controller()) {

            $itemid = $this->get_assignment()->get_user_grade($this->userid, false, 0)->id;
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
     * Wether the grade has rubric grading.
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
