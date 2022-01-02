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
     * @var int $totalgrade The final grade.
     */
    protected $totalgrade;

    /**
     * Constructor.
     *
     * @param int       $graderid       The grader user id of this grade.
     * @param string    $gradername     The grader name of this grade.
     * @param int       $studentid      The user id of the student of this grade.
     * @param string    $studentname    The student name of this grade.
     * @param int       $gradedate      The date of this grade.
     * @param int       $grade          The final grade.
     */
    public function __construct(
        $exerciseid     = 0,
        $exercisetype   = 'assign',
        $graderid       = 0,
        $gradername     = '',
        $studentid      = 0,
        $studentname    = '',
        $gradedate      = 0,
        $finalgrade     = 0,
        $totalgrade     = 0
        ) {
        $this->exerciseid   = $exerciseid;
        $this->exercisetype = $exercisetype;
        $this->graderid     = $graderid;
        $this->gradername   = $gradername;
        $this->studentid    = $studentid;
        $this->studentname  = $studentname;
        $this->gradedate    = $gradedate;
        $this->finalgrade   = $finalgrade;
        $this->totalgrade   = $totalgrade;
    }

    // Getter functions
    public function get_exerciseid() {
        return $this->exerciseid;
    }

    public function get_exercisetype() {
        return $this->exercisetype;
    }

    public function get_graderid() {
        return $this->graderid;
    }

    public function get_gradername() {
        return $this->gradername;
    }

    public function get_studentid() {
        return $this->studentid;
    }

    public function get_studentname() {
        return $this->studentname;
    }

    public function get_gradedate() {
        return $this->gradedate;
    }

    public function get_finalgrade() {
        return $this->finalgrade;
    }

    public function get_total_grade() {
        return $this->totalgrade;
    }

    // setter functions

    public function set_exerciseid(int $exerciseid) {
        $this->exerciseid = $exerciseid;
    }

    public function set_exercisetype(string $exercisetype) {
        $this->exercisetype = $exercisetype;
    }

    public function set_graderid(int $graderid) {
        $this->graderid = $graderid;
    }

    /**
     * Get the grader name of the grade.
     *
     * @param string
     */
    public function set_gradername(string $gradername) {
        $this->gradername = $gradername;
    }

    /**
     * Get the studnet user id of the grade.
     *
     * @param int
     */
    public function set_studentid(int $studentid) {
        $this->studentid = $studentid;
    }

    /**
     * Get the studnet name of the grade.
     *
     * @param string
     */
    public function set_studentname(string $studentname) {
        $this->studentname = $studentname;
    }

    /**
     * Get the date timestamp of the grade.
     *
     * @param int
     */
    public function set_gradedate(int $gradedate) {
        $this->gradedate = $gradedate;
    }

    /**
     * Get the final grade.
     *
     * @param int
     */
    public function set_finalgrade(int $finalgrade) {
        $this->finalgrade = $finalgrade;
    }
}
