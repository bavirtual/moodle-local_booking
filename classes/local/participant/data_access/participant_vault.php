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
     * Process user assignments table name.
     */
    const DB_ASSIGN = 'assign';

    /**
     * Process user assignment grades table name.
     */
    const DB_GRADES = 'assign_grades';

    /**
     * Process quiz table name.
     */
    const DB_QUIZ = 'quiz';

    /**
     * Process quiz grades table name.
     */
    const DB_QUIZ_GRADES = 'quiz_grades';

    /**
     * Process lesson completion in timer table.
     */
    const DB_LESSON_TIMER = 'lesson_timer';

    /**
     * @int $pastdatacutoff timestamp of past data in the system for a student.
     */
    protected $pastdatacutoff;

    /**
     * Constructor.
     *
     */
    public function __construct() {
        // get the past data cutoff timestamp (seconds)
        $this->pastdatacutoff = time() - LOCAL_BOOKING_PASTDATACUTOFF * 60 * 60 * 24;
    }

    /**
     * Get all active students from the database.
     *
     * @param int $courseid     The course id.
     * @param bool $studentid   A specific student for booking confirmation
     * @return {Object}         Array of database records.
     */
    public static function get_active_student(int $courseid, int $studentid = 0) {
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
                    AND u.id = :studentid
                    AND ra.contextid = :contextid
                    AND r.shortname = :role
                    AND ue.status = 0';

        $params = [
            'courseid'  => $courseid,
            'studentid' => $studentid,
            'contextid' => \context_course::instance($courseid)->id,
            'role'      => 'student'
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get all active students from the database.
     *
     * @param int $courseid         The course id.
     * @param bool $includeonhold   Whether to include on-hold students as well
     * @return {Object}[]           Array of database records.
     */
    public static function get_active_students(int $courseid, bool $includeonhold = false) {
        global $DB;

        // return $DB->get_records_sql($sql, $params);
        $onhold_clause = $includeonhold ? '' : ' OR g.name = "' . LOCAL_BOOKING_ONHOLDGROUP . '"';

        $sql = 'SELECT u.id AS userid, ' . $DB->sql_concat('u.firstname', '" "',
                        'u.lastname', '" "', 'u.alternatename') . ' AS fullname,
                        ue.timecreated AS enroldate, en.courseid AS courseid, u.lastlogin AS lastlogin
                    FROM {' . self::DB_USER . '} u
                    INNER JOIN {' . self::DB_ROLE_ASSIGN . '} ra on u.id = ra.userid
                    INNER JOIN {' . self::DB_ROLE . '} r on r.id = ra.roleid
                    INNER JOIN {' . self::DB_USER_ENROL . '} ue on ra.userid = ue.userid
                    INNER JOIN {' . self::DB_ENROL . '} en on ue.enrolid = en.id
                    WHERE en.courseid = :courseid
                        AND ra.contextid = :contextid
                        AND r.shortname = :role
                        AND ue.status = 0
                        AND u.deleted != 1
                        AND u.id NOT IN (
                            SELECT userid
                            FROM {' . self::DB_GROUPS_MEM . '} gm
                            INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                            WHERE g.courseid = :gcourseid AND (g.name = "' . LOCAL_BOOKING_GRADUATESGROUP . '"
                            ' . $onhold_clause . '))';

        $params = [
            'courseid'  => $courseid,
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
    public static function get_active_instructors(int $courseid, bool $courseadmins = false) {
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
     * Get grades for a specific student.
     *
     * @param int       $courseid  The course id.
     * @param int       $studentid The student id.
     * @return grade[]  A student grades.
     */
    public function get_student_assignment_grades(int $courseid, int $studentid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT cm.id AS exerciseid, a.id AS assignid,
                    ag.userid, MAX(ag.grade) AS grade, a.grade AS totalgrade,
                    MAX(ag.timemodified) AS gradedate, m.name AS exercisetype,
                    MAX(u.id) AS instructorid, ' . $DB->sql_concat('u.firstname', '" "',
                    'u.lastname', '" "', 'u.alternatename') . ' AS instructorname
                FROM {' . self::DB_GRADES . '} ag
                INNER JOIN {' . self::DB_ASSIGN . '} a ON ag.assignment = a.id
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm ON a.id = cm.instance
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
                INNER JOIN {' . self::DB_USER . '} u ON ag.grader = u.id
                WHERE m.name = :assign
                    AND cm.course = :courseid
                    AND ag.userid = :studentid
                    AND ag.grade > 0
                    AND ag.timemodified > ' . $this->pastdatacutoff . '
                GROUP BY exerciseid, assignid, exercisetype
                ORDER BY cs.section';

        $params = [
            'assign' => 'assign',
            'courseid'  => $courseid,
            'studentid'  => $studentid
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get grades for a specific student.
     *
     * @param int       $studentid  The student id.
     * @return grade[]  A student quizes.
     */
    public function get_student_quizes_grades(int $courseid, int $studentid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT cm.id AS exerciseid, qg.quiz AS assignid,
                    qg.userid, qg.grade, q.grade AS totalgrade,
                    qg.timemodified AS gradedate,
                    0 AS instructorid, \'\' AS instructorname,
                    m.name AS exercisetype
                FROM {' . self::DB_QUIZ_GRADES . '} qg
                INNER JOIN {' . self::DB_QUIZ . '} q on q.id = qg.quiz
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm on qg.quiz = cm.instance
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                INNER JOIN {' . self::DB_MODULES . '} as m ON m.id = cm.module
                WHERE m.name = :quiz
                    AND cm.course = :courseid
                    AND qg.userid = :studentid
                ORDER BY cs.section';

        $params = [
            'quiz' => 'quiz',
            'courseid'  => $courseid,
            'studentid'  => $studentid
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get student's enrolment date.
     *
     * @param int       $studentid  The student id in reference
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date(int $courseid, int $studentid) {
        global $DB;

        $sql = 'SELECT ue.timecreated
                FROM {' . self::DB_USER_ENROL . '} ue
                INNER JOIN {' . self::DB_ENROL . '} e ON e.id = ue.enrolid
                WHERE e.courseid = :courseid
                    AND ue.userid = :studentid';

        $params = [
            'courseid' => $courseid,
            'studentid'  => $studentid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param int   $courseid   The course the student is being unenrolled from.
     * @param int   $studentid  The student id in reference
     * @return bool             The result of the suspension action.
     */
    public function suspend(int $courseid, int $studentid) {
        global $DB;

        $sql = 'UPDATE {' . static::DB_USER_ENROL . '} ue
                INNER JOIN {' . static::DB_ENROL . '} e ON e.id = ue.enrolid
                SET ue.status = 1
                WHERE e.courseid = :courseid
                    AND ue.userid = :studentid';

        $params = [
            'courseid' => $courseid,
            'studentid'  => $studentid
        ];

        return $DB->execute($sql, $params);
    }

    /**
     * Returns full username
     *
     * @param int       $userid The user id.
     * @param bool      $includealternate Whether to include the user's alternate name.
     * @return string   $fullusername The full participant username
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
     * Returns custom field value from the user's profile
     *
     * @param int $courseid         The course id
     * @param int $participantid    The user id
     * @param string $field         The field name associated with the rquested data
     * @return string               The full participatn username
     */
    public static function get_customfield_data(int $courseid, int $userid, string $field) {
        global $DB;

        // Look for ATO category
        $category = $DB->get_record('user_info_category', array('name'=>get_booking_config('ATO')));
        $categoryid = 0;

        if (empty($category)) {
            return '';
        } else {
            $categoryid = $category->id;
        }

        $sql = 'SELECT uid.data
                FROM {' . self::DB_USER_DATA . '} uid
                INNER JOIN {' . self::DB_USER_FIELD . '} uif ON uif.id = uid.fieldid
                WHERE uif.shortname = :field
                AND uid.userid = :userid
                AND uif.categoryid = :categoryid';

        $params = [
            'field' => $field,
            'userid' => $userid,
            'categoryid' => $categoryid
        ];

        $customfieldobj = $DB->get_record_sql($sql, $params);

        return empty($customfieldobj->data) ? '' : $customfieldobj->data;;
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
                FROM {' . self::DB_GRADES . '} ag
                INNER JOIN {' . self::DB_COURSE_MODS . '} cm ON cm.instance = ag.assignment
                WHERE cm.course = :courseid
                AND ' . $usertypesql . ' = :userid
                AND ag.timemodified > ' . $this->pastdatacutoff . '
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
     * @param   int     The student id
     * @param   int     The course id
     * @param   int     The upcoming next exercise id
     * @return  bool    Whether the lessones were completed or not.
     */
    public function get_student_lessons_complete($studentid, $courseid, $nextexercisesection) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT cm.id, cm.course, cm.module, cm.instance, cs.section
                FROM {' . self::DB_COURSE_MODS .'} cm
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                INNER JOIN {' . self::DB_MODULES . '} as m ON m.id = cm.module
                WHERE cm.course = :courseid
                AND cs.section <= :nextexercisesection
                AND m.name = "lesson"
                AND cm.instance NOT IN (SELECT lt.lessonid
                    FROM {' . self::DB_LESSON_TIMER . '} lt
                    WHERE lt.userid = :studentid
                    AND lt.completed = :completion)
                ORDER BY cs.section ASC';

        $params = [
            'courseid' => $courseid,
            'studentid'  => $studentid,
            'nextexercisesection'  => $nextexercisesection,
            'completion'  => COMPLETION_COMPLETE
        ];

        $lessons_incompleted = $DB->get_records_sql($sql, $params);

        return count($lessons_incompleted) == 0;
    }

    /**
     * Returns the next upcoming exercise id
     * for the student and its associated course section.
     *
     * @param   int     The course id
     * @param   int     The student id
     * @return  array   The next exercise id and associated course section
     */
    public function get_next_student_exercise($courseid, $studentid) {
        global $DB;
        $result = [0,0];

        // Get first record of exercises not completed yet
        $sql = 'SELECT cm.id AS nextexerciseid, cs.section AS section
                FROM {' . self::DB_COURSE_MODS .'} cm
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
                WHERE cm.course = :courseid
                    AND m.name = :assign
                    AND cm.instance NOT IN (SELECT ag.assignment
                                            FROM {' . self::DB_GRADES . '} ag
                                            WHERE ag.userid = :studentid
                                            AND ag.grade != -1
                                            AND ag.timemodified > ' . $this->pastdatacutoff . ')
                ORDER BY cs.section asc
                LIMIT 1';

        $params = [
            'courseid' => $courseid,
            'assign' => 'assign',
            'studentid'  => $studentid
        ];

        $rs = $DB->get_records_sql($sql, $params);

        // check for last exercise in the course
        if (!empty($rs))
            $result = [current($rs)->nextexerciseid, current($rs)->section];

        return $result;
    }
}