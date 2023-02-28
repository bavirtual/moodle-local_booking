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
 * Class for data access of course participants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\data_access;

use DateTime;
use local_booking\local\participant\entities\instructor;

require_once($CFG->dirroot . "/lib/completionlib.php");

class participant_vault implements participant_vault_interface {

    /**
     * Process user enrollments table name.
     */
    const DB_USER = 'user';

    /**
     * Process modules table name.
     */
    const DB_MODULES = 'modules';

    /**
     * Process user role table name.
     */
    const DB_ROLE = 'role';

    /**
     * Process user role assignment table name.
     */
    const DB_ROLE_ASSIGN = 'role_assignments';

    /**
     * Process user info data table name for the simulator.
     */
    const DB_USER_DATA = 'user_info_data';

    /**
     * Process user info data table name for the simulator.
     */
    const DB_USER_FIELD = 'user_info_field';

    /**
     * Process user enrollments table name.
     */
    const DB_USER_ENROL = 'user_enrolments';

    /**
     * Process  enrollments table name.
     */
    const DB_ENROL = 'enrol';

    /**
     * Process groups table name for on-hold group.
     */
    const DB_GROUPS = 'groups';

    /**
     * Process groups members table name for on-hold students.
     */
    const DB_GROUPS_MEM = 'groups_members';

    /**
     * Process course modules table name.
     */
    const DB_COURSE_MODS = 'course_modules';

    /**
     * Process course sections table name.
     */
    const DB_COURSE_SECTIONS = 'course_sections';

    /**
     * Process course completion table name.
     */
    const DB_COURSE_COMPLETIONS = 'course_completions';

    /**
     * Process user assignments table name.
     */
    const DB_ASSIGN = 'assign';

    /**
     * Process user assignment grades table name.
     */
    const DB_ASSIGN_GRADES = 'assign_grades';

    /**
     * Process all user grades table name.
     */
    const DB_GRADES = 'grade_grades';

    /**
     * Process grade items table name.
     */
    const DB_GRADE_ITEMS = 'grade_items';

    /**
     * Process grading instances table name.
     */
    const DB_GRADING_INS = 'grading_instances';

    /**
     * Process grading form rubric fillings table name.
     */
    const DB_GRADING_FIL = 'gradingform_rubric_fillings';

    /**
     * Process grading form rubric levels table name.
     */
    const DB_GRADING_LEVELS = 'gradingform_rubric_levels';

    /**
     * Process grading form rubric criteria table name.
     */
    const DB_GRADING_CRITERIA = 'gradingform_rubric_criteria';

    /**
     * Process quiz table name.
     */
    const DB_SCALE = 'scale';

    /**
     * Process quiz table name.
     */
    const DB_QUIZ = 'quiz';

    /**
     * Process quiz attempts table name.
     */
    const DB_QUIZ_ATTEMPTS = 'quiz_attempts';

    /**
     * Process lesson completion in timer table.
     */
    const DB_LESSON_TIMER = 'lesson_timer';

    /**
     * Past cutoff date (timestamp) for data retrieval.
     */
    const PASTDATACUTOFFDAYS = LOCAL_BOOKING_PASTDATACUTOFF * 60 * 60 * 24;

    /**
     * Process course sections table name.
     */
    const DB_FILES = 'files';

    /**
     * Get all active participant from the database.
     *
     * @param int $courseid The course id.
     * @param int $userid   A specific user.
     * @param bool $active  Whether the user is actively enrolled.
     * @return {Object}     Array of database records.
     */
    public static function get_participant(int $courseid, int $userid = 0, bool $active = true) {
        global $DB;

        $activeclause = $active ? ' AND ue.status = 0' : '';
        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ue.timecreated AS enroldate, en.courseid AS courseid, u.lastlogin AS lastlogin
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on u.id = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                WHERE en.courseid = :courseid
                    AND u.id = :userid' . $activeclause;

        $params = [
            'courseid'  => $courseid,
            'userid' => $userid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get all active student from the database.
     *
     * @param int $courseid     The course id.
     * @param bool $userid      A specific student for booking confirmation
     * @return {Object}         Array of database records.
     */
    public static function get_student(int $courseid, int $userid = 0) {
        global $DB;

        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ue.timecreated AS enroldate, en.courseid AS courseid, u.lastlogin AS lastlogin
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on ra.userid = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                WHERE en.courseid = :courseid
                    AND u.id = :userid
                    AND ra.contextid = :contextid
                    AND r.shortname = :role';

        $params = [
            'courseid'  => $courseid,
            'userid' => $userid,
            'contextid' => \context_course::instance($courseid)->id,
            'role'      => 'student'
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get all active students from the database.
     *
     * @param int $courseid         The course id.
     * @param string $filter        The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold   Whether to include on-hold students as well
     * @return {Object}[]           Array of database records.
     */
    public static function get_students(int $courseid, string $filter = 'active', bool $includeonhold = false) {
        global $DB;

        // return $DB->get_records_sql($sql, $params);
        switch ($filter) {
            case 'active':
                $onholdclause = $includeonhold ? '' : ' OR g.name = "' . LOCAL_BOOKING_ONHOLDGROUP . '"';
                $filterclause = 'AND ue.status = 0
                    AND u.id NOT IN (
                        SELECT userid
                        FROM {' . self::DB_GROUPS_MEM . '} gm
                        INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                        WHERE g.courseid = :gcourseid AND (g.name = "' . LOCAL_BOOKING_GRADUATESGROUP . '"
                        ' . $onholdclause . '))';
                break;
            case 'onhold':
                $filterclause = 'AND ue.status = 0
                    AND u.id IN (
                        SELECT userid
                        FROM {' . self::DB_GROUPS_MEM . '} gm
                        INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                        WHERE g.courseid = :gcourseid AND g.name = "' . LOCAL_BOOKING_ONHOLDGROUP . '")';
                break;
            case 'suspended':
                $filterclause = 'AND ue.status = 1 ORDER BY fullname';
                break;
            case 'graduates':
                $filterclause = 'AND ue.status = 0
                    AND u.id IN (
                        SELECT userid
                        FROM {' . self::DB_GROUPS_MEM . '} gm
                        INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                        WHERE g.courseid = :gcourseid AND (g.name = "' . LOCAL_BOOKING_GRADUATESGROUP . '"))';
                break;
        }

        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                        'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                        ue.timecreated AS enroldate, en.courseid AS courseid, u.lastlogin AS lastlogin
                    FROM {' . self::DB_USER . '} u
                    INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                    INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                    INNER JOIN {' . self::DB_USER_ENROL . '} ue on ra.userid = ue.userid
                    INNER JOIN {' . self::DB_COURSE_COMPLETIONS . '} cc on cc.userid = ue.userid
                    INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                    WHERE en.courseid = :courseid
                        AND ra.contextid = :contextid
                        AND r.shortname = :role
                        AND u.deleted != 1
                        AND cc.course = :completioncourseid
                        AND (cc.timecompleted > ' . (time() - self::PASTDATACUTOFFDAYS) . ' OR cc.timecompleted IS NULL) ' . $filterclause;

        $params = [
            'courseid'  => $courseid,
            'completioncourseid'  => $courseid,
            'gcourseid' => $courseid,
            'contextid' => \context_course::instance($courseid)->id,
            'role'      => 'student'
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get all active instructors for the course from the database.
     *
     * @param int $courseid The course id.
     * @param bool $courseadmins Indicates whether the instructor is an admin or not.
     * @return {Object}[]   Array of database records.
     */
    public static function get_instructors(int $courseid, bool $courseadmins = false) {
        global $DB;
        $roles = (!$courseadmins ? '"' . LOCAL_BOOKING_INSTRUCTORROLE . '", ' : '') . '"' .
                LOCAL_BOOKING_SENIORINSTRUCTORROLE . '", "' .
                LOCAL_BOOKING_FLIGHTTRAININGMANAGERROLE . '"';

        $sql = 'SELECT DISTINCT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ue.timemodified AS enroldate, en.courseid AS courseid, u.lastlogin AS lastlogin
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on ra.userid = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                WHERE en.courseid = :courseid
                    AND ra.contextid = :contextid
                    AND r.shortname IN (' . $roles . ')
                    AND ue.status = 0
                    AND u.deleted != 1
                    AND u.lastlogin > ' . (time() - self::PASTDATACUTOFFDAYS) . '
                    AND u.id NOT IN (
                        SELECT userid
                        FROM {' . self::DB_GROUPS_MEM . '} gm
                        INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                        WHERE g.courseid= :gcourseid AND g.name = "' . LOCAL_BOOKING_INACTIVEGROUP . '"
                        )';

        $params = [
            'courseid'  => $courseid,
            'gcourseid' => $courseid,
            'contextid' => \context_course::instance($courseid)->id
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get students assigned to an instructor from the database.
     *
     * @param int $courseid The course in context
     * @param int $userid   The instructor user id
     * @return {Object}[]   Array of database records.
     */
    public function get_assigned_students(int $courseid, int $userid) {
        global $DB;
        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                    ue.timemodified AS enroldate, en.courseid AS courseid, u.lastlogin AS lastlogin
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                INNER JOIN {' . self::DB_USER_ENROL . '} ue on ra.userid = ue.userid
                INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                INNER JOIN {' . self::DB_GROUPS_MEM . '} gm on ue.userid = gm.userid
                INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                WHERE en.courseid = :courseid
                    AND ra.contextid = :contextid
                    AND r.shortname = :role
                    AND ue.status = :status
                    AND g.courseid = :gcourseid
                    AND g.name = :instructorname
                    AND u.lastlogin > ' . (time() - self::PASTDATACUTOFFDAYS) . '
                    AND u.id NOT IN (
                        SELECT userid
                        FROM {' . self::DB_GROUPS_MEM . '} gm
                        INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                        WHERE g.courseid = :g2courseid AND
                        (g.name = "' . LOCAL_BOOKING_ONHOLDGROUP . '"
                        OR g.name = "' . LOCAL_BOOKING_GRADUATESGROUP . '"
                        ))';

        $params = [
            'courseid'  => $courseid,
            'contextid' => \context_course::instance($courseid)->id,
            'role'      => 'student',
            'status'    => 0,
            'gcourseid' => $courseid,
            'g2courseid'=> $courseid,
            'instructorname' => instructor::get_fullname($userid, false)
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get student's enrolment date.
     *
     * @param int       $userid     The student user id in reference
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date(int $courseid, int $userid) {
        global $DB;

        $sql = 'SELECT ue.timecreated
                FROM {' . self::DB_USER_ENROL . '} ue
                INNER JOIN {' . self::DB_ENROL . '} e ON e.id = ue.enrolid
                WHERE e.courseid = :courseid
                    AND ue.userid = :userid';

        $params = [
            'courseid' => $courseid,
            'userid'  => $userid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param int   $courseid   The course the student is being unenrolled from.
     * @param int   $userid     The student user id in reference
     * @param int   $status     The status of the enrolment suspended = 1
     * @return bool             The result of the suspension action.
     */
    public function suspend(int $courseid, int $userid, int $status) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_USER_ENROL . '} ue
                INNER JOIN {' . static::DB_ENROL . '} e ON e.id = ue.enrolid
                SET ue.status = :status
                WHERE e.courseid = :courseid
                    AND ue.userid = :userid';

        $params = [
            'courseid' => $courseid,
            'userid'  => $userid,
            'status'  => $status
        ];

        return $DB->execute($sql, $params);
    }

    /**
     * Returns full username
     *
     * @param int       $userid           The user id.
     * @param bool      $includealternate Whether to include the user's alternate name.
     * @return string   $fullusername     The full participant username
     */
    public static function get_participant_name(int $userid, bool $includealternate = true) {
        global $DB;

        $fullusername = '';
        if ($userid != 0) {
            // Get the full user name
            $sql = 'SELECT ' . $DB->sql_concat('u.firstname', '" "',
                        'u.lastname', '" "', 'u.alternatename') . ' AS bavname, '
                        . $DB->sql_concat('u.firstname', '" "',
                        'u.lastname') . ' AS username
                    FROM {' . self::DB_USER . '} u
                    WHERE u.id = :userid';

            $param = ['userid'=>$userid];
            $userinfo = $DB->get_record_sql($sql ,$param);
            $fullusername = $includealternate ? $userinfo->bavname : $userinfo->username;
        }

        return $fullusername;
    }

    /**
     * Returns the timestamp of the last
     * graded session.
     *
     * @param   int The user id
     * @param   int The course id
     * @return  stdClass The record containing timestamp of the last grading
     */
    public function get_last_graded_date(int $userid, int $courseid, bool $is_student) {
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
     * Returns whether the student complete
     * all sessons prior to the upcoming next
     * exercise.
     *
     * @param   int     The student user id
     * @param   int     The course id
     * @param   int     The next exercise id
     * @return  bool    Whether the lessones were completed or not.
     */
    public function is_student_lessons_complete(int $userid, int $courseid, int $nextexercise) {
        global $DB;

        // get the section containing the next exercise
        $sql = 'SELECT cs.section
                FROM {' . self::DB_COURSE_SECTIONS . '} cs
                INNER JOIN {' . self::DB_COURSE_MODS .'} cm ON cm.section = cs.id
                WHERE cm.id = :exerciseid
                    AND cm.deletioninprogress = 0';
        $nextexercisesection = $DB->get_record_sql($sql, ['exerciseid'=>$nextexercise]);

        // get the student's grades
        $sql = 'SELECT cm.id, cm.course, cm.module, cm.instance, cs.section, cs.sequence
                FROM {' . self::DB_COURSE_MODS .'} cm
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                INNER JOIN {' . self::DB_MODULES . '} as m ON m.id = cm.module
                WHERE cm.course = :courseid
                AND cm.deletioninprogress = 0
                AND cs.section <= :nextexercisesection
                AND m.name = "lesson"
                AND cm.instance NOT IN (SELECT lt.lessonid
                    FROM {' . self::DB_LESSON_TIMER . '} lt
                    WHERE lt.userid = :userid
                    AND lt.completed = :completion)
                ORDER BY cs.section ASC';

        $params = [
            'courseid' => $courseid,
            'userid'  => $userid,
            'nextexercisesection'  => $nextexercisesection->section,
            'completion'  => COMPLETION_COMPLETE
        ];

        $lessonsincompleted = $DB->get_records_sql($sql, $params);
        $lessonscomplete = count($lessonsincompleted) == 0;

        // check the sequence for lessons with multiple assignments to make sure that
        // only lessons prior to the completed exercise are evaluated for completion
        if (!empty($lessonsincompleted)) {
            $incompletesequence = explode(',', array_values($lessonsincompleted)[0]->sequence);
            $lessonscomplete = array_search(array_values($lessonsincompleted)[0]->id, $incompletesequence) > array_search($nextexercise, $incompletesequence);
        }


        return $lessonscomplete;
    }

    /**
     * Updates a user's profile field with a value
     *
     * @param   int    $userid  The student user id
     * @param   string $field   The field to be updated
     * @param   mixed  $value   The value to update to
     * @return  bool            Whether the comment was updated or not.
     */
    public function update_participant_field(int $userid, string $field, $value) {
        global $DB;

        return $DB->set_field('user', $field, $value, array('id' => $userid));
    }
}