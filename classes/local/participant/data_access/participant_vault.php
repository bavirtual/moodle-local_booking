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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\participant\data_access;

use DateTime;

require_once($CFG->dirroot . "/lib/completionlib.php");

class participant_vault implements participant_vault_interface {

    // user tables
    const DB_USER = 'user';
    const DB_ROLE = 'role';
    const DB_ROLE_ASSIGN = 'role_assignments';
    const DB_GROUPS = 'groups';
    const DB_GROUPS_MEM = 'groups_members';

    // enrolment tables
    const DB_USER_ENROL = 'user_enrolments';
    const DB_ENROL = 'enrol';

    // course module tables
    const DB_MODULES = 'modules';
    const DB_COURSE_SECTIONS = 'course_sections';
    const DB_COURSE_MODS = 'course_modules';
    const DB_COURSE_COMP = 'course_completions';
    const DB_COURSE_MODS_COMP = 'course_modules_completion';

    // course assignment tables
    const DB_ASSIGN = 'assign';
    const DB_ASSIGN_GRADES = 'assign_grades';
    const DB_LESSON_TIMER = 'lesson_timer';

    // session booking tables
    const DB_BOOKING = 'local_booking_sessions';
    const DB_SLOTS = 'local_booking_slots';
    const DB_STATS = 'local_booking_stats';
    const DB_LOGBOOKS = 'local_booking_logbooks';

    /**
     * Get a participant from the database.
     *
     * @param int  $courseid The course id.
     * @param int  $userid   A specific user.
     * @param string $filter Optional filter.
     * @return {Object}      Array of database records.
     */
    public static function get_participant(int $courseid, int $userid, string $filter = 'active') {
        global $DB;

        $enrolledsql = self::get_sql($courseid, $filter, ($filter == 'any'), 'student', ($filter != 'any'));

        $sql = "SELECT * FROM (SELECT u.id AS userid, $enrolledsql->fields $enrolledsql->from $enrolledsql->where $enrolledsql->groupby) AS participants
                WHERE roles like '%student%' AND userid = $userid $enrolledsql->orderby";

        return $DB->get_record_sql($sql);
    }

    /**
     * Get all active students from the database.
     *
     * @param int $courseid         The course id.
     * @param string $filter        The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold   Whether to include on-hold students as well
     * @param int $offset           The offset record for pagination
     * @param bool $requirescompletion Whether the course has lesson completion restriction
     * @return array Array of database records and total count.
     */
    public static function get_students(
        int $courseid,
        string $filter = 'active',
        bool $includeonhold = false,
        int $offset = 0,
        bool $requirescompletion = true) {

        global $DB;

        // get enrolled students sql object
        $enrolledsql = self::get_sql($courseid, $filter, $includeonhold, 'student', $requirescompletion);

        // sql string for enrolled students
        $sql = "SELECT * FROM (SELECT u.id AS userid, $enrolledsql->fields $enrolledsql->from $enrolledsql->where $enrolledsql->groupby) AS participants
                WHERE roles like '%student%' $enrolledsql->orderby";

        // enrolled students count sql
        $countsql = "SELECT Count(userid) FROM (SELECT u.id AS userid, $enrolledsql->roles_field $enrolledsql->from $enrolledsql->where $enrolledsql->groupby) AS participants
                WHERE roles like '%student%'";

        // get filtered students and their total count
        $count = $DB->count_records_sql($countsql);
        $students = $DB->get_records_sql($sql, null, $offset, LOCAL_BOOKING_DASHBOARDPAGESIZE);

        return [$students, $count];
    }

    /**
     * Get all active participants for a course for UI select controls (ids & fullname)
     *
     * @param int $courseid       The course id.
     * @param string $filter      The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param bool $includeonhold Whether to include on-hold students as well
     * @param string $roles       The roles of the participants
     * @return array              Array of database records.
     */
    public static function get_student_names(int $courseid, string $filter = 'active', bool $includeonhold = false, string $roles = null) {
        global $DB;

        $context = \context_course::instance($courseid);

        // Fields we need from the user table.
        $userfieldsapi = \core_user\fields::for_identity($context)->with_userpic();
        $userfieldssql = $userfieldsapi->get_sql('u', true, '', '', false);
        $enrolledsql = self::get_sql($courseid, $filter, $includeonhold, 'student', false);

        $sql =  "SELECT $userfieldssql->selects FROM {" . self::DB_USER . "} u JOIN ";
        $sql .= "(SELECT u.id AS userid, $enrolledsql->roles_field $enrolledsql->from $enrolledsql->where $enrolledsql->groupby) AS participants ON userid = u.id " .
                "WHERE roles like '%student%'";

        return $DB->get_records_sql($sql);
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

        // get roles
        $roles = $courseadmins ?  LOCAL_BOOKING_SENIORINSTRUCTORROLE . '|' . LOCAL_BOOKING_FLIGHTTRAININGMANAGERROLE : LOCAL_BOOKING_INSTRUCTORROLE;

        // get enrolled students sql object
        $enrolledsql = self::get_sql($courseid, 'active', false, $roles, false);

        // sql string for enrolled students
        $sql = "SELECT * FROM (SELECT u.id AS userid, $enrolledsql->fields $enrolledsql->from $enrolledsql->where $enrolledsql->groupby) AS participants
                WHERE roles REGEXP '$roles' $enrolledsql->orderby";

        $instructors = $DB->get_records_sql($sql);

        return $instructors;
    }

    /**
     * Get student's enrolment date.
     *
     * @param int       $userid     The student user id in reference
     * @return DateTime $enroldate  The enrolment date of the student.
     */
    public function get_enrol_date(int $courseid, int $userid) {
        global $DB;

        $sql = 'SELECT ue.timecreated AS enroldate, ue.timemodified AS suspenddate
                FROM {' . self::DB_USER_ENROL . '} ue
                INNER JOIN {' . self::DB_ENROL . '} e ON e.id = ue.enrolid
                WHERE e.courseid = :courseid
                    AND ue.userid = :userid
                ORDER BY ue.timecreated DESC LIMIT 1';

        $params = [
            'courseid' => $courseid,
            'userid'  => $userid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Returns the timestamp of the last
     * graded session.
     *
     * @param   int The user id
     * @param   int The course id
     * @return  \stdClass The record containing timestamp of the last grading
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
                AND ag.timemodified > ' . (time() - LOCAL_BOOKING_PASTDATACUTOFFDAYS) . '
                ORDER BY timemodified DESC
                LIMIT 1';

        $params = [
            'courseid' => $courseid,
            'userid'  => $userid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Returns the list of completed lesson ids
     * for a student in a course.
     *
     * @param   int     The student user id
     * @param   int     The course id
     * @return  array   List of completed lesson ids
     */
    public function get_student_completed_lesson_ids(int $userid, int $courseid) {
        global $DB;

        $sql = "SELECT GROUP_CONCAT(cm.id ORDER BY cs.section, LOCATE(cm.id, cs.sequence))
                FROM {" . self::DB_COURSE_MODS_COMP . "} cmc
                INNER JOIN {" . self::DB_COURSE_MODS . "} cm ON cm.id = cmc.coursemoduleid
                INNER JOIN {" . self::DB_MODULES . "} m ON m.id = cm.module
                INNER JOIN {" . self::DB_COURSE_SECTIONS . "} cs ON cs.id = cm.section
                WHERE
                    cmc.userid = :userid AND
                    cm.course = :courseid AND
                    cmc.completionstate >= " . COMPLETION_COMPLETE . " AND
                    m.name = 'lesson'";

        $params = [
            'userid'  => $userid,
            'courseid' => $courseid
        ];

        return $DB->get_record_sql($sql, $params);

    }

    /**
     * Returns the list of incomplete lessons for a student
     * prior to the upcoming next exercise.
     *
     * @param   int     The student user id
     * @param   int     The course id
     * @param   int     The next exercise id
     * @return  array   List of incomplete lesson mod ids
     */
    public function get_student_incomplete_lesson_ids(int $userid, int $courseid, int $nextexercise) {
        global $DB;

        $nextexercisesection = self::get_course_next_exercise_section($nextexercise);

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
                    AND lt.completed < ' . COMPLETION_COMPLETE .')
                ORDER BY cs.section ASC';

        $params = [
            'courseid' => $courseid,
            'userid'  => $userid,
            'nextexercisesection'  => $nextexercisesection->section,
        ];

        $lessonsnotcompleted = $DB->get_records_sql($sql, $params);

        // check the sequence for lessons with multiple assignments to make sure that
        // only lesson modules prior to the completed exercise are evaluated for completion
        $incompletesequence = [];
        if (!empty($lessonsnotcompleted)) {
            $incompletesequence = explode(',', array_values($lessonsnotcompleted)[0]->sequence);
            // check if any of the lesson modules incomplete are in the list prior to the next exercise
            $priortonext = array_slice($incompletesequence, 0, array_search($nextexercise, $incompletesequence));
            $incompletesequence = array_intersect($priortonext, array_keys($lessonsnotcompleted));
        }

        return $incompletesequence;
    }

    /**
     * Returns the section containing the next exercise.
     *
     * @return \stdClass Section
     */
    private static function get_course_next_exercise_section(int $nextexercise) {
        global $DB;

        // get the section containing the next exercise
        $sql = 'SELECT cs.section
                FROM {' . self::DB_COURSE_SECTIONS . '} cs
                INNER JOIN {' . self::DB_COURSE_MODS .'} cm ON cm.section = cs.id
                WHERE cm.id = :exerciseid
                    AND cm.deletioninprogress = 0';

        return $DB->get_record_sql($sql, ['exerciseid'=>$nextexercise]);
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

    /**
     * Suspends the student's enrolment to a course.
     *
     * @param int   $courseid   The course the student is being unenrolled from.
     * @param int   $userid     The student user id in reference
     * @param int   $status     The status of the enrolment suspended = 1
     * @return bool             The result of the suspension action.
     */
    public function suspend(int $courseid, int $userid, int $status) {
        global $DB, $USER;

        $sql = 'UPDATE {' . static::DB_USER_ENROL . '} ue
                INNER JOIN {' . static::DB_ENROL . '} e ON e.id = ue.enrolid
                SET ue.status = :status, ue.timemodified = UNIX_TIMESTAMP(), ue.modifierid = ' . $USER->id . '
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
     * Get sql query object to retrieve participants from the database.
     *
     * @param  int    $courseid           The course id.
     * @param  string $filter             The filter to show students, inactive (including graduates), suspended, and default to active.
     * @param  bool   $includeonhold      Whether to include on-hold students as well
     * @param  string $roles              The roles of the participants
     * @param  bool   $requirescompletion Whether the course has lesson completion restriction
     * @return object The query SQL object
     */
    private static function get_sql(
        int $courseid,
        string $filter = 'active',
        bool $includeonhold = false,
        string $roles = 'student',
        bool $requirescompletion = true) {

        $isstudent = !empty($roles) && $roles == 'student';

        $sql = [
            'fields'  => self::get_sql_select_fields($roles, $courseid),
            'roles_field'  => self::sql_roles_field($courseid),
            'from'    => self::get_sql_from($isstudent),
            'where'   => self::get_sql_where($filter, $courseid, $isstudent, $includeonhold),
            'groupby' => self::get_sql_groupby(),
            'orderby' => self::get_sql_orderby($isstudent, $filter == 'suspended', $requirescompletion)
        ];

        return (object) $sql;
    }

    /**
     * Get select fields.
     *
     * @param  string $roles   The roles of the participants
     * @param  int    $course  The course id
     * @return string $selects The SELECT SQL query string
     */
    private static function get_sql_select_fields(string $roles, int $courseid) {
        global $DB;

        // basic user fields
        $selects = 'u.lastlogin, ' . $DB->sql_concat('u.firstname', '" "', 'u.lastname', '" "', 'u.alternatename') . ' AS fullname, ';

        // course roles
        $selects .= !empty($roles) ? self::sql_roles_field($courseid) . ', ': '';

        // get student statistics if the role is a student
        $corestudentfields = 's.lessonscomplete, s.lastsessiondate, s.currentexerciseid, s.nextexerciseid, cc.timecompleted AS graduateddate,
                        IF(s.lastsessiondate IS NULL OR s.lastsessiondate = 0, ue.timecreated, s.lastsessiondate) AS waitdate, ';

        $selects .= !empty($roles) && $roles == 'student' ?
                $corestudentfields .
                self::sql_booked_field() . ' AS booked, ' .
                self::sql_hasactiveposts_field() . ' AS hasactiveposts, '
                :
                self::sql_maxbooktime_var() . ', ' .
                self::sql_maxlogentrytime_var() . ', ' .
                'IF(@maxlogentrytime > @maxbooktime, @maxlogentrytime, @maxbooktime) AS lastsessiondate, ';

        // include user enrolment fields
        $selects .= 'en.courseid AS courseid, ue.timecreated AS enroldate, ue.timemodified AS suspenddate, ue.status AS enrolstatus';

        return $selects;
    }

    /**
     * Get roles for participants.
     *
     * @param  int $course The course id
     * @return string The SELECT roles field SQL query string
     */
    private static function sql_roles_field(int $courseid) {

        $context = \context_course::instance($courseid);
        $rolesclause = "(SELECT GROUP_CONCAT(shortname) FROM {" . self::DB_ROLE_ASSIGN . "} ra " .
                        "INNER JOIN {" . self::DB_ROLE . "} r ON r.id = ra.roleid " .
                        "WHERE ra.userid = u.id AND ra.contextid = $context->id) AS roles";

        return $rolesclause;
    }
    /**
     * Get from tables for enroled participants.
     * Include stats for students.
     *
     * @param  bool $isstudent Whether the participant is a student
     * @return string $selects The FROM SQL query string
     */
    private static function get_sql_from(bool $isstudent) {

        // inner select from tables statement
        $from =  'FROM {' . self::DB_USER . '} u ';
        $from .= 'INNER JOIN {' . self::DB_USER_ENROL . '} ue ON ue.userid = u.id ';
        $from .= 'INNER JOIN {' . self::DB_ENROL . '} en ON en.id = ue.enrolid ';

        // get status for student role
        if ($isstudent) {
            $from .= 'LEFT OUTER JOIN {' . self::DB_STATS . '} s ON s.userid = u.id AND s.courseid = en.courseid ';
            $from .= 'LEFT JOIN {' . self::DB_COURSE_COMP . '} cc ON cc.userid = u.id AND cc.course = en.courseid ';
        }

        return $from;
    }

    /**
     * Get where clause base on filter criteria.
     * Include stats for students.
     *
     * @param  string $filter        The enroled participants filter
     * @param  string $courseid      The course id
     * @param  bool   $isstudent     Whether the participant is a student
     * @param  string $includeonhold Whether or not include on-hold students
     * @return string $where         The WHERE SQL query string
     */
    private static function get_sql_where(string $filter, int $courseid, bool $isstudent, bool $includeonhold = false) {

        $where = " WHERE en.courseid = $courseid AND u.deleted != 1 AND u.suspended = 0 ";

        if ($filter == 'active')
            return $where .= self::sql_active_participants($courseid, $isstudent, $includeonhold);

        if ($filter == 'onhold')
            return $where .= self::sql_onhold_participants($courseid);

        if ($filter == 'suspended')
            return $where .= ' AND ue.status = 1';

        if ($filter == 'graduates')
            return $where .= self::sql_graduated_participants($courseid);

        return $where;
    }

    /**
     * Get group by clause base on user id.
     *
     * @return string The GROUP BY SQL query string
     */
    private static function get_sql_groupby(){
        return ' GROUP BY u.id';
    }

    /**
     * Get ORDER BY SQL clause sort order.
     *
     * @param  bool $isstudent          Whether the participant is a student
     * @param  bool $suspended          Whether the participant is a student
     * @param  bool $requirescompletion Whether or not include on-hold students
     * @return string $orderby The WHERE SQL query string
     */
    private static function get_sql_orderby(bool $isstudent, bool $suspended, bool $requirescompletion){

        // evaluate order by for suspended students
        if ($suspended)
            return ' ORDER BY suspenddate, userid DESC';

        // return order by for students and whether the course require lesson completion
        return ' ORDER BY ' . ($isstudent ? ($requirescompletion ? 'lessonscomplete DESC,' : '') .
            ' hasactiveposts DESC, booked DESC, waitdate ASC' : 'lastsessiondate DESC');

    }

    /**
     * Get active participants clause.
     *
     * @param  int  $courseid
     * @param  bool $isstudent
     * @param  string $includeonhold Whether or not include on-hold students
     * @return string SQL query string
     */
    private static function sql_active_participants(int $courseid, bool $isstudent, bool $includeonhold = false){

        // active participants and students that didn't graduate
        $activeparticipantssql = ' AND ue.status = 0' . ($isstudent ? ' AND cc.timecompleted IS NULL' : '');

        // include on-hold students or inactive participants to include onhold
        $activeparticipantssql .= $includeonhold ? '' : ' AND u.id NOT IN
                (
                    SELECT userid
                    FROM {' . self::DB_GROUPS_MEM . '} gm
                    INNER JOIN {' . self::DB_GROUPS . '} g on g.id = gm.groupid
                    WHERE g.courseid = ' . $courseid . ' AND g.name = "' . ($isstudent ? LOCAL_BOOKING_ONHOLDGROUP : LOCAL_BOOKING_INACTIVEGROUP) . '"
                ) ';
        return $activeparticipantssql;
    }

    /**
     * Get On-Hold student participants clause.
     *
     * @param  int  $courseid
     * @return string SQL query string
     */
    private static function sql_onhold_participants(int $courseid){
        return " AND ue.status = 0 AND
            cc.timecompleted IS NULL AND u.id IN
            (
                SELECT userid
                FROM {" . self::DB_GROUPS_MEM . "} gm
                INNER JOIN {" . self::DB_GROUPS . "} g on g.id = gm.groupid
                WHERE g.courseid = $courseid AND g.name = '" . LOCAL_BOOKING_ONHOLDGROUP . "'
            )";
    }

    /**
     * Get graduated student participants clause.
     *
     * @param  int  $courseid
     * @return string SQL query string
     */
    private static function sql_graduated_participants(int $courseid){
        return " AND ue.status = 0 AND ( cc.timecompleted IS NOT NULL OR u.id IN
            (
                SELECT userid
                FROM {" . self::DB_GROUPS_MEM . "} gm
                INNER JOIN {" . self::DB_GROUPS . "} g on g.id = gm.groupid
                WHERE g.courseid = $courseid AND g.name = '" . LOCAL_BOOKING_GRADUATESGROUP . "'
            ))";
    }

    /**
     * Get current booking status for the student.
     *
     * @return string SQL query string
     */
    private static function sql_booked_field(){
        return '@hasbooking :=
            (
                SELECT b.active FROM {' . self::DB_STATS . '} st
                LEFT OUTER JOIN {' . self::DB_BOOKING . '} b ON b.studentid = st.userid AND b.courseid = st.courseid
                WHERE b.studentid = u.id AND b.courseid = en.courseid
                ORDER BY b.id DESC LIMIT 1
            )';
    }

    /**
     * Get hasactiveposts field SQL clause.
     *
     * @return string SQL query string
     */
    private static function sql_hasactiveposts_field(){
        return 'IF(
          (
            SELECT MAX(a.starttime) FROM {' . self::DB_SLOTS . '} a
            WHERE a.userid = u.id AND a.courseid = en.courseid
          ) > UNIX_TIMESTAMP(), IF(@hasbooking=1, 0, 1), 0)';
    }

    /**
     * Get date of the last booking the instructor made.
     *
     * @return string SQL query string
     */
    private static function sql_maxbooktime_var(){
        return '@maxbooktime :=
            (
                SELECT MAX(b.timemodified) FROM {' . self::DB_BOOKING . '} b
                WHERE b.userid = u.id AND b.courseid = en.courseid
            )';
    }

    /**
     * Get date of the last conducted flight date the instructor made.
     *
     * @return string SQL query string
     */
    private static function sql_maxlogentrytime_var(){
        return '@maxlogentrytime :=
            (
                SELECT MAX(l.flightdate) FROM {' . self::DB_LOGBOOKS . '} l
                WHERE l.userid = u.id AND l.courseid = en.courseid
            )';
    }
}