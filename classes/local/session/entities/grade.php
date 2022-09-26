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
require_once($CFG->dirroot . '/grade/grading/lib.php');

use local_booking\local\participant\data_access\participant_vault;
use local_booking\local\participant\entities\participant;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing a grade for course exercise session.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade implements grade_interface {

    /**
     * @var int $exerciseid The course exercise id of this grade.
     */
    protected $exerciseid;

    /**
     * @var context $context The context associated with this grade.
     */
    protected $context;

    /**
     * @var assign $assign The assignment associated with this grade.
     */
    protected $assign;

    /**
     * @var string $exercisetype The course exercise type of this grade.
     */
    protected $exercisetype;

    /**
     * @var int $graderid The grader user id of this grade.
     */
    protected $graderid;

    /**
     * @var string $gradername The grader name of this grade.
     */
    protected $gradername;

    /**
     * @var int $studentid The user id of the student of this grade.
     */
    protected $studentid;

    /**
     * @var string $studentname The student name of this grade.
     */
    protected $studentname;

    /**
     * @var int $gradedate The date of this grade.
     */
    protected $gradedate;

    /**
     * @var int $finalgrade The final grade.
     */
    protected $finalgrade;

    /**
     * @var string $grademark The final grade mark.
     */
    protected $grademark;

    /**
     * @var string[] $scale The grade scale.
     */
    protected $scale;

    /**
     * @var bool $hasrubric Whether the grade has rubric grading.
     */
    protected $hasrubric;

    /**
     * @var int $totalgrade The final grade.
     */
    protected $totalgrade;

    /**
     * Constructor.
     *
     * @param {object}  $exercisegrade  The grader user id of this grade.
     * @param int       $studentid      The user id of the student of this grade.
     */
    public function __construct(object $exercisegrade, int $studentid) {
        $this->exerciseid     = $exercisegrade->exerciseid;
        $this->exercisetype   = $exercisegrade->exercisetype;
        $this->graderid       = $exercisegrade->instructorid;
        $this->gradername     = $exercisegrade->instructorname;
        $this->studentid      = $studentid;
        $this->studentname    = participant::get_fullname($studentid);
        $this->gradedate      = $exercisegrade->gradedate;
        $this->finalgrade     = $exercisegrade->grade;
        $this->totalgrade     = $exercisegrade->totalgrade;

        // get grade mark from the course's scale
        if (!empty($exercisegrade->scale)) {
            $this->scale = explode(',', $exercisegrade->scale);
            $this->grademark = $this->scale[intval($this->finalgrade)-1];
        } else {
            $this->grademark = intval($this->finalgrade) . '/' . intval($this->totalgrade);
        }
    }

    /**
     * Get the exercise module context.
     *
     * @return \context
     */
    public function get_context() {

        if (!isset($this->context)) {
            // get context associated with the module
            $this->context = \context_module::instance($this->exerciseid);
        }

        return $this->context;
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
            $this->assign = new \assign($this->context, $cm, $course);
        }

        return $this->assign;
    }

    /**
     * Get the course exercise id for the grade.
     *
     * @return \grade
     */
    public function get_grade() {

        if (!isset($this->grade)) {
            $this->grade = $this->get_assignment()->get_user_grade($this->studentid, false);
        }

        return $this->grade;
    }

    /**
     * Get the course exercise id for the grade.
     *
     * @return int
     */
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    /**
     * Get the course exercise type for the grade.
     *
     * @return string
     */
    public function get_exercisetype() {
        return $this->exercisetype;
    }

    /**
     * Get the grader user id of the grade.
     *
     * @return int
     */
    public function get_graderid() {
        return $this->graderid;
    }

    /**
     * Get the grader name of the grade.
     *
     * @return string
     */
    public function get_gradername() {
        return $this->gradername;
    }

    /**
     * Get the studnet user id of the grade.
     *
     * @return int
     */
    public function get_studentid() {
        return $this->studentid;
    }

    /**
     * Get the studnet name of the grade.
     *
     * @return string
     */
    public function get_studentname() {
        return $this->studentname;
    }

    /**
     * Get the date timestamp of the grade.
     *
     * @return int
     */
    public function get_gradedate() {
        return $this->gradedate;
    }

    /**
     * Get the student's final grade.
     *
     * @return int
     */
    public function get_finalgrade() {
        return $this->finalgrade;
    }

    /**
     * Get the student's final grade mark.
     *
     * @return string
     */
    public function get_grademark() {
        return $this->grademark;
    }

    /**
     * Get the total grade or passing grade of the assignment.
     *
     * @return int
     */
    public function get_totalgrade() {
        return $this->totalgrade;
    }

    /**
     * Get the student's grade feedback comments.
     *
     * @return string
     */
    public function get_feedback_comments() {

        // call Moodle standard grading to get the feedback comments for this grade
        $course = $this->get_assignment()->get_course();
        $instance = $this->get_assignment()->get_instance();
        $feedback = (object) grade_get_grades($course->id, 'mod', 'assign', $instance->id, $this->studentid);
        $feedbackcomment = $feedback->items[0]->grades[$this->studentid];

        return $feedbackcomment->str_feedback;
    }

    /**
     * Get the grade feedback file.
     *
     * @param string $component The assignment component
     * @param string $filearea  The assignment file area
     * @return string
     */
    public function get_feedback_file(string $component, string $filearea) {

         $path = '';

         // get the file record
         $filerec = participant_vault::get_student_feedback_file_info($this->get_context()->id, $this->get_grade()->id, $component, $filearea);
         if (!empty($filerec)) {
             $file = new \stored_file(get_file_storage(), (object) [
                 'contenthash' => $filerec->contenthash,
                 'filesize' => $filerec->filesize,
             ]);

             $fs = get_file_storage();
             $path = $fs->get_file_system()->get_local_path_from_storedfile($file);
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

        // get the grading manager
        if (!isset($this->context)) {
            // get course and associate module to find the practical exam skill test assignment
            list ($course, $cm) = get_course_and_cm_from_cmid($this->exerciseid, 'assign');
            $this->context = \context_module::instance($cm->id);
        }

        $gradingmgr = get_grading_manager($this->context, 'mod_' . $this->exercisetype, 'submissions');

        // get the grading instances from the assignment grade
        if ($controller = $gradingmgr->get_active_controller()) {

            // get the practical exam assignment to get the associated feedback comments
            if (!isset($this->assign)) {
                $this->assign = new \assign($this->context, $cm, $course);
            }

            $grade = $this->assign->get_user_grade($this->studentid, false);
            $instances = $controller->get_active_instances($grade->id);

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
                // // discard the 'Overall Grade' instance criteria
                // array_pop($rubric);
            }
        }

        return $rubric;
    }

    /**
     * Is a passing grade.
     *
     * @param bool
     */
    public function is_passinggrade() {
        // evaluate for Evaluation scale (totalgrade<0) and student passing with a grade of 3 or greater
        // or if Point Rubric scale (totalgrade>0) and student passed by achieving total grade.
        return ($this->totalgrade < 0 && $this->finalgrade >= 3) || ($this->totalgrade > 0 && $this->finalgrade == $this->totalgrade);
    }

    /**
     * Wether the grade has rubric grading.
     *
     * @return bool
     */
    public function has_rubric() {

        if (!isset($this->hasrubric)){

            // get the context for the grading manager
            list ($course, $cm) = get_course_and_cm_from_cmid($this->exerciseid, $this->exercisetype);
            $context = \context_module::instance($cm->id);
            $gradingmgr = get_grading_manager($context, 'mod_' . $this->exercisetype, 'submissions');
            $this->hasrubric = $gradingmgr->get_active_method() == 'rubric';
        }

        return $this->hasrubric;
    }
}
