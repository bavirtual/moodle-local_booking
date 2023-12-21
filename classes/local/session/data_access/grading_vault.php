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
 * Class for data access of student grading
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\session\data_access;

require_once($CFG->dirroot . "/lib/completionlib.php");

class grading_vault implements grading_vault_interface {

    /**
     * Course modules table name.
     */
    const DB_COURSE_MODS = 'course_modules';

    /**
     * User assignment grades table name.
     */
    const DB_ASSIGN_GRADES = 'assign_grades';

    /**
     * User assignment feedback comments table name.
     */
    const DB_ASSIGN_FEEDBACK = 'assignfeedback_comments';

    /**
     * Module scale table name
     */
    const DB_SCALE = 'scale';

    /**
     * Past cutoff date (timestamp) for data retrieval.
     */
    const PASTDATACUTOFFDAYS = LOCAL_BOOKING_PASTDATACUTOFF * 60 * 60 * 24;

    /**
     * Returns the timestamp of the last
     * graded session.
     *
     * @param   int The user id
     * @param   int The course id
     * @return  stdClass The record containing timestamp of the last grading
     */
    public static function get_last_graded_date(int $userid, int $courseid, bool $is_student) {
        global $DB;

        // parameter for the grades being retrieved: the student graded by instructor or grader grades
        $usertypesql = $is_student ? 'grader != -1 AND userid' : 'grader';
        // Get the student's grades
        $sql = 'SELECT timemodified
                FROM {' . self::DB_ASSIGN_GRADES . '} ag
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm ON cm.instance = ag.assignment
                WHERE cm.course = :courseid
                AND cm.deletioninprogress = 0
                AND ' . $usertypesql . ' = :userid
                AND ag.timemodified > ' . (time() - self::PASTDATACUTOFFDAYS) . '
                ORDER BY timemodified DESC
                LIMIT 1';

        $params = [
            'courseid' => $courseid,
            'userid'  => $userid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get an array of graded session count for each exercise for the user.
     *
     * @param  int The user id
     * @param  int The course id
     * @return int
     */
    public static function get_user_total_graded_sessions(int $courseid, int $userid) {
        global $DB;

        $sql = 'SELECT cm.id AS exerciseid, count(cm.id) AS sessions FROM {' . static::DB_COURSE_MODS . '} cm
        INNER JOIN {' . static::DB_ASSIGN_GRADES . '} ai ON ai.assignment = cm.instance
        WHERE cm.course = :courseid
            AND ai.grader = :userid
        GROUP BY ai.grader, cm.instance, cm.id';

        $params = [
            'courseid'  => $courseid,
            'userid'    => $userid
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get list of student grades for an exercise attempts.
     *
     * @param  int $courseid   The course id
     * @param  int $userid     The user id
     * @param  int $exerciseid The exercise id that was attempted
     * @return array
     */
    public static function get_student_exercise_attempts(int $courseid, int $userid, int $exerciseid) {
        global $DB;

        $sql = 'SELECT ag.attemptnumber, ag.grader, ag.timemodified, ag.grade, ac.grade AS itemid, ac.commenttext
                FROM {' . self::DB_ASSIGN_GRADES . '} ag
                INNER JOIN {' . self::DB_ASSIGN_FEEDBACK . '} ac ON ac.grade = ag.id
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm ON cm.instance = ac.assignment
                WHERE cm.course = :courseid
                    AND ag.userid = :userid
                    AND cm.id = :exerciseid';

        $params = [
            'courseid'  => $courseid,
            'userid'    => $userid,
            'exerciseid'=> $exerciseid
        ];

        // return attempts found in descending order starting with last attempt first
        $attempts = $DB->get_records_sql($sql, $params);
        \arsort($attempts);
        return $attempts;
    }

    /**
     * Get an array of a grade item scale grades
     *
     * @param  int $scaleid    The scale id for the exercise
     * @return array
     */
    public static function get_exercise_gradeitem_scale(int $scaleid) {
        global $DB;

        $sql = 'SELECT scale FROM {' . self::DB_SCALE . '}
                WHERE id = :scaleid';
        $scale = explode(',', $DB->get_record_sql($sql, ['scaleid'=>$scaleid])->scale);
        return $scale;
    }
}