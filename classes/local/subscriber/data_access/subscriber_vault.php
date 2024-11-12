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
 * Contains event class for displaying the week view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\subscriber\data_access;

class subscriber_vault implements subscriber_vault_interface {

    /** Core tables. */
    const DB_USER = 'user';
    const DB_ROLE = 'role';
    const DB_ROLE_ASSIGN = 'role_assignments';

    /** Course table & modules. */
    const DB_COURSE_CUSTOM_FIELD = 'customfield_field';
    const DB_COURSE_CUSTOM_DATA = 'customfield_data';
    const DB_COURSE_SECTIONS = 'course_sections';
    const DB_COURSE_MODS = 'course_modules';
    const DB_MODS = 'modules';
    const DB_COURSE_COMP = 'course_completions';
    const DB_COURSE_MODS_COMP = 'course_modules_completion';

    /** Enrolment tables. */
    const DB_ENROL = 'enrol';
    const DB_USER_ENROL = 'user_enrolments';

    /** Grading tables. */
    const DB_ASSIGN_GRADE = 'assign_grades';
    const DB_GRADE_GRADE = 'grade_grades';
    const DB_GRADE_ITEMS = 'grade_items';

    /** Grouping tables. */
    const DB_GROUP = 'groups';

    /** Booking tables. */
    const DB_STATS = 'local_booking_stats';

    /**
     * Retreive a data point from the stats table
     *
     * @param int    $courseid  The course id
     * @param int    $userid    The user id
     * @param string $stat      The stat field being update
     * @return bool             The result
     */
    public static function get_subscriber_stat(int $courseid, int $userid, string $stat) {
        global $DB;

        $sql = "SELECT $stat AS value FROM {" . self::DB_STATS . "} WHERE courseid = :courseid AND userid = :userid";

        $params = [
            'userid'   => $userid,
            'courseid' => $courseid
        ];

        return $DB->get_record_sql($sql, $params)->value;
    }

    /**
     * Updates the stats table with a specific value
     *
     * @param int    $courseid  The course id
     * @param int    $userid    The user id
     * @param string $stat      The stat field being update
     * @param string $value     The field value being update
     * @return bool             The result
     */
    public static function update_subscriber_stat(int $courseid, int $userid, string $stat, $value) {
        global $DB;

        // insert record on enrolment where $value is the first course exercise, otherwise update based on the field to be updated w/ the value
        $sql = "INSERT IGNORE INTO {" . self::DB_STATS . "} (userid, courseid, lessonscomplete, lastsessiondate, currentexerciseid, nextexerciseid)
                VALUES ($userid, $courseid, 0, 0, 0, $value) " . (!empty($stat) ? "
                ON DUPLICATE KEY UPDATE
                    $stat = :value" : "");

        $params = [
            'userid'   => $userid,
            'courseid' => $courseid,
            'value'    => $value
        ];

        return $DB->execute($sql, $params);
    }

    /**
     * Updates the stats table with a lastest lesson completed
     *
     * @param int    $courseid  The course id
     * @param int    $userid    The user id
     * @return bool             The result
     */
    public static function update_subscriber_lessonscomplete_stat(int $courseid, int $userid) {
        global $DB;

        // get last recorded completed lesson
        $lastlessonsql = "UPDATE mdl_local_booking_stats bs SET lessonscomplete =
                            (
                            SELECT IF(COUNT(modid)>0,0,1)
                            FROM (
                                SELECT ROW_NUMBER() OVER w AS row_num, cm.id AS modid, cm.course, m.name
                                FROM {" . self::DB_COURSE_MODS . "} cm
                                INNER JOIN {" . self::DB_COURSE_SECTIONS . "} s ON s.id = cm.section
                                INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                                WHERE m.name IN ('assign','lesson') WINDOW w AS (ORDER BY s.section, LOCATE(cm.id, s.sequence))
                                ) d
                            WHERE d.course = $courseid AND d.name = 'lesson' AND
                                row_num <
                                (
                                    SELECT row_num2
                                    FROM
                                    (
                                        SELECT ROW_NUMBER() OVER w AS row_num2, cm.id AS modid, cm.course
                                        FROM {" . self::DB_COURSE_MODS . "} cm
                                        INNER JOIN {" . self::DB_COURSE_SECTIONS . "} s ON s.id = cm.section
                                        INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                                        WHERE m.name IN ('assign','lesson') WINDOW w AS (ORDER BY s.section, LOCATE(cm.id, s.sequence))
                                    ) r
                                    WHERE r.modid = bs.nextexerciseid AND r.course = $courseid
                                ) AND
                                modid NOT IN
                                (
                                    SELECT cm.id
                                    FROM {" . self::DB_COURSE_MODS_COMP . "} cmc
                                    INNER JOIN {" . self::DB_COURSE_MODS . "} cm ON cm.id = cmc.coursemoduleid
                                    INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                                    INNER JOIN {" . self::DB_COURSE_SECTIONS . "} cs ON cs.id = cm.section
                                    WHERE cmc.userid = $userid AND cm.course = $courseid AND cmc.completionstate >= 1 AND m.name = 'lesson'
                                )
                            )
                          WHERE bs.courseid = $courseid AND bs.userid = $userid";

        return $DB->execute($lastlessonsql);
    }

    /**
     * Get a based on its id
     *
     * @param int   $courseid The course id
     * @return bool Whether the course is subscribed or not
     */
    public static function is_course_enabled(int $courseid) {
        global $DB;

        $sql = "SELECT value FROM {" . self::DB_COURSE_CUSTOM_DATA . "} cd INNER JOIN {" . self::DB_COURSE_CUSTOM_FIELD . "} cf ON cf.id = cd.fieldid WHERE cf.shortname = :subscribename AND cd.instanceid = :courseid";
        $enabled = $DB->get_record_sql($sql, ['subscribename'=>'subscribed', 'courseid'=>$courseid]);
        return $enabled->value;
    }

    /**
     * Checks the stats table to check if the subscribed course has any student status or not.
     *
     * @param int   $courseid The course id
     * @return bool Whether the course is subscribed or not
     */
    public static function course_stats_exist(int $courseid) {
        global $DB;

        // get active users enrolled in the specified course
        $sql = 'SELECT u.id AS enrolled
                FROM {' . self::DB_USER . '} u
                INNER JOIN {' . self::DB_USER_ENROL . '} ue ON ue.userid = u.id
                INNER JOIN {' . self::DB_ENROL . '} e ON e.id = ue.enrolid
                LEFT JOIN {' . self::DB_ROLE_ASSIGN . '} ra ON ra.userid = u.id
                INNER JOIN {' . self::DB_ROLE . '} r ON r.id = ra.roleid
                LEFT JOIN {' . self::DB_COURSE_COMP . '} cc ON cc.userid = ra.userid AND cc.course = e.courseid
                WHERE e.courseid = :courseid
                    AND ue.status = 0
                    AND ra.contextid = :contextid
                    AND archetype = :studentrole
                    AND cc.timecompleted IS NULL
                    AND u.deleted = 0
                    AND u.suspended = 0';
        $activeenrols = array_keys($DB->get_records_sql($sql, ['contextid'=>\context_course::instance($courseid)->id, 'courseid'=>$courseid, 'studentrole'=>'student']));

        // get users enrolled with stats in the specified course
        $sql = 'SELECT userid AS enrolled FROM {' . self::DB_STATS .'} WHERE courseid = :courseid';
        $enroledstats = array_keys($DB->get_records_sql($sql, ['courseid'=>$courseid]));

        // cross check that all active enrolled users have stats records
        $nostatenrols = array_diff($activeenrols, $enroledstats);

        return empty($nostatenrols);
    }

    /**
     * Adds students stats for a newly enabled course subscriber
     *
     * @param int   $courseid The course id
     * @return bool           Whether the course is subscribed or not
     */
    public static function add_new_subscriber_enrolments(int $courseid) {
        global $DB;

        $contextid = \context_course::instance($courseid)->id;

        // insert all new subscriber students from enrolment
        $result = $DB->execute("INSERT IGNORE INTO {" . self::DB_STATS . "} (userid, courseid, currentexerciseid)
            SELECT u.id, :courseid, 0 FROM {" . self::DB_USER . "} u
            INNER JOIN {" . self::DB_USER_ENROL . "} ue on u.id = ue.userid
            INNER JOIN {" . self::DB_ENROL . "} en on ue.enrolid = en.id
            INNER JOIN {" . self::DB_COURSE_CUSTOM_DATA . "} cd ON cd.instanceid = en.courseid
            INNER JOIN {" . self::DB_COURSE_CUSTOM_FIELD . "} cf ON cd.fieldid = cf.id
            WHERE en.courseid = cd.instanceid AND u.deleted != 1 AND cf.shortname = 'subscribed' AND
                cd.`value` = 1 AND en.courseid = :encourseid AND u.id IN
                (
                SELECT ra.userid FROM {" . self::DB_ROLE_ASSIGN . "} ra
                INNER JOIN {" . self::DB_ROLE . "} r on r.id = ra.roleid
                WHERE ra.contextid = :contextid AND r.archetype = 'student'
                )", ['courseid'=>$courseid, 'encourseid'=>$courseid, 'contextid'=>$contextid]);

        // update stats with each student's current exercise/assignment id for the subscribing course
        $result &= $DB->execute("
            UPDATE {" . self::DB_STATS . "} bs SET currentexerciseid =
                (
                SELECT cm.id FROM {" . self::DB_GRADE_GRADE . "} g
                INNER JOIN {" . self::DB_GRADE_ITEMS . "} gi ON g.itemid = gi.id
                INNER JOIN {" . self::DB_COURSE_MODS . "} cm ON cm.instance = gi.iteminstance
                INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                WHERE g.userid = bs.userid AND gi.courseid = $courseid AND gi.itemmodule = 'assign' AND m.name = 'assign'
                ORDER BY g.timemodified DESC
                LIMIT 1
                )
            WHERE courseid = $courseid");

        // update stats with each student's current exercise/assignment id for the subscribing course
        $result &= $DB->execute("
            UPDATE {" . self::DB_STATS . "} bs SET nextexerciseid =
                (
                SELECT modid FROM
                    (
                        SELECT ROW_NUMBER() OVER w AS row_num, cm.id AS modid, cm.course, m.name
                        FROM {" . self::DB_COURSE_MODS . "} cm
                        INNER JOIN {" . self::DB_COURSE_SECTIONS . "} s ON s.id = cm.section
                        INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                        WHERE m.name = 'assign' AND cm.course = $courseid
                        WINDOW w AS (ORDER BY s.section, LOCATE(cm.id, s.sequence))
                    ) d
                    WHERE d.course = $courseid AND d.name = 'assign' AND row_num >
                    (
                        SELECT row_num2 FROM
                            (
                                SELECT ROW_NUMBER() OVER w AS row_num2, cm.id AS modid, cm.course
                                FROM {" . self::DB_COURSE_MODS . "} cm
                                INNER JOIN {" . self::DB_COURSE_SECTIONS . "} s ON s.id = cm.section
                                INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                                WHERE m.name = 'assign' AND cm.course = $courseid
                                WINDOW w AS (ORDER BY s.section, LOCATE(cm.id, s.sequence))
                            ) r
                        WHERE r.modid = bs.currentexerciseid AND r.course = $courseid
                    ) LIMIT 1
                )
                WHERE courseid = $courseid");

        // Update next exercise for new joiners to the first exercise in the course
        $result &= $DB->execute("
            UPDATE {" . self::DB_STATS . "} bs SET nextexerciseid =
            (
                SELECT cm.id
                FROM {" . self::DB_COURSE_MODS . "} cm
                INNER JOIN {" . self::DB_COURSE_SECTIONS . "} s ON s.id = cm.section
                INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                WHERE cm.course = $courseid AND m.name = 'assign'
                WINDOW w AS (ORDER BY s.section, LOCATE(cm.id, s.sequence)) LIMIT 1
            )
            WHERE courseid = $courseid AND (nextexerciseid = 0 OR nextexerciseid IS NULL)");

        /**
         *  Update stats with each student's current lesson for the subscribing course:
         *    lessonscomplete = count > 0 means the student has pending lessons resulting in false
         *      1- get course lesson mod ids the student completed in the course
         *      2- check against course lessons up to the student's upcoming exercise using row_num
         *      3- eliminate the student's completed lessons, anything remains are pending lessons
         *         making the count > 0.
         * */
        $result &= $DB->execute("
            UPDATE {" . self::DB_STATS . "} bs SET lessonscomplete =
                (
                SELECT IF( COUNT( modid ) > 0, 0, 1)
                FROM
                (
                    SELECT ROW_NUMBER() OVER w AS row_num, cm.id AS modid, cm.course, m.name
                    FROM {" . self::DB_COURSE_MODS . "} cm
                    INNER JOIN {" . self::DB_COURSE_SECTIONS . "} s ON s.id = cm.section
                    INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                    WHERE m.name IN ('assign','lesson') WINDOW w AS (ORDER BY s.section, LOCATE(cm.id, s.sequence))
                ) d
                WHERE d.course = $courseid AND
                d.name = 'lesson' AND
                row_num <
                (
                    SELECT row_num2
                    FROM
                    (
                        SELECT ROW_NUMBER() OVER w AS row_num2, cm.id AS modid, cm.course
                        FROM {" . self::DB_COURSE_MODS . "} cm
                        INNER JOIN {" . self::DB_COURSE_SECTIONS . "} s ON s.id = cm.section
                        INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                        WHERE m.name IN ('assign','lesson') WINDOW w AS (ORDER BY s.section, LOCATE(cm.id, s.sequence))
                    ) r
                    WHERE r.modid = bs.nextexerciseid AND r.course = $courseid
                ) AND
                modid NOT IN
                (
                    SELECT cm.id
                    FROM {" . self::DB_COURSE_MODS_COMP . "} cmc
                    INNER JOIN {" . self::DB_COURSE_MODS . "} cm ON cm.id = cmc.coursemoduleid
                    INNER JOIN {" . self::DB_MODS . "} m ON m.id = cm.module
                    INNER JOIN {" . self::DB_COURSE_SECTIONS . "} cs ON cs.id = cm.section
                    WHERE cmc.userid = bs.userid AND
                        cm.course = $courseid AND
                        cmc.completionstate >= 1
                        AND m.name = 'lesson'
                )
            )
            WHERE bs.courseid = $courseid");

        // // update last session date from grading or enrolment for students that newly joind
        // $result &= $DB->execute("
        //     UPDATE {" . self::DB_STATS . "} bs SET lastsessiondate =
        //         (
        //         SELECT MAX(ag.timemodified) FROM {" . self::DB_ASSIGN_GRADE . "} ag
        //         INNER JOIN {" . self::DB_COURSE_MODS . "} cm ON cm.instance = ag.assignment
        //         WHERE cm.course = $courseid AND cm.deletioninprogress = 0 AND ag.userid = bs.userid
        //         )
        //     WHERE lastsessiondate = 0 AND courseid = $courseid");

        // $result &= $DB->execute("
        //     UPDATE {" . self::DB_STATS . "} bs SET lastsessiondate =
        //         (
        //         SELECT MAX(ue.timecreated) FROM {" . self::DB_USER_ENROL . "} ue
        //         INNER JOIN {" . self::DB_ENROL . "} e ON e.id = ue.enrolid
        //         WHERE e.courseid = :ecourseid AND ue.userid = bs.userid
        //         )
        //     WHERE lastsessiondate = 0 AND courseid = :courseid", ['courseid'=>$courseid, 'ecourseid'=>$courseid]);

        return $result;
    }

    /**
     * Removes user stats data once student is unenroled from the course
     *
     * @param int $courseid The course id
     * @param int $userid   The assign module id
     * @return bool
     */
    public static function delete_subscriber_stat(int $courseid, int $userid) {
        global $DB;

        return $DB->delete_records(self::DB_STATS, ['courseid'=>$courseid, 'userid'=>$userid]);
    }
}