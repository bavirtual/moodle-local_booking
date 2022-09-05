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
 * Class for access to subscribing course and module data
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\subscriber\data_access;

defined('MOODLE_INTERNAL') || die();

class subscriber_vault implements subscriber_vault_interface {

    /**
     * Process assign table name.
     */
    const DB_ASSIGN = 'assign';

    /**
     * Process quiz table name.
     */
    const DB_QUIZ = 'quiz';

    /**
     * Process  modules table name.
     */
    const DB_MODULES = 'modules';

    /**
     * Process course modules table name.
     */
    const DB_COURSE_MODULES = 'course_modules';

    /**
     * Process course sections table name.
     */
    const DB_COURSE_SECTIONS = 'course_sections';

    /**
     * Process course sections table name.
     */
    const DB_FILES = 'files';

    /**
     * Returns the course section name containing the exercise
     *
     * @param int $courseid The course id of the section
     * @param int $exerciseid The exercise id in the course inside the section
     * @return array  The section name of a course associated with the exercise
     */
    public static function get_subscriber_section(int $courseid, int $exerciseid) {
        global $DB;
        $result = [0,0];

        // Get the full user name
        $sql = 'SELECT cs.id as sectionid, cs.name as sectionname
                FROM {' . self::DB_COURSE_SECTIONS . '} cs
                INNER JOIN {' . self::DB_COURSE_MODULES . '} cm ON cm.section = cs.id
                WHERE cm.id = ' . $exerciseid . '
                AND cm.deletioninprogress = 0
                AND cm.course = ' . $courseid;

        $section = $DB->get_record_sql($sql);

        return [$section->sectionid, $section->sectionname];
    }

    /**
     * Retrieves exercises for the course
     *
     * @param int $courseid                  The course id of the section
     * @return array
     */
    public static function get_subscriber_exercises(int $courseid) {
        global $DB;

        // get assignments for this course based on sorted course topic sections
        $sql = 'SELECT cm.id AS exerciseid, a.name AS exercisename,
                q.name AS exam, m.name AS modulename
                FROM {' . self::DB_COURSE_MODULES . '} cm
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
                LEFT JOIN {' . self::DB_ASSIGN . '} a ON a.id = cm.instance
                LEFT JOIN {' . self::DB_QUIZ . '} q ON q.id = cm.instance
                WHERE cm.course = :courseid
                    AND cm.deletioninprogress = 0
                    AND (m.name = :assign
                        OR m.name = :quiz)
                    AND deletioninprogress = 0
                    ORDER BY cs.section, cm.id;';

        $params = [
            'courseid'         => $courseid,
            'assign'           => 'assign',
            'quiz'             => 'quiz'
        ];
        $recs = $DB->get_records_sql($sql, $params);
        return $recs;
    }

    /**
     * Retrieves the exercise name of a specific exercise
     * based on its id statically.
     *
     * @param int $exerciseid The exercise id.
     * @return string
     */
    public static function get_subscriber_exercise_name(int $exerciseid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT a.name AS exercisename
                FROM {' . self::DB_ASSIGN . '} a
                INNER JOIN {' . self::DB_COURSE_MODULES . '} cm on a.id = cm.instance
                WHERE cm.id = :exerciseid
                AND deletioninprogress = 0;';

        $param = ['exerciseid'=>$exerciseid];

        return $DB->get_record_sql($sql, $param)->exercisename;
    }

    /**
     * Returns the subscribed course exercise by name
     *
     * @param int $courseid The course id of the section
     * @param string $exercisename The graduation exercise name
     * @return int  The exercise id of the graduation last exercise
     */
    public static function get_subscriber_exercise_by_name(int $courseid, string $exercisename) {
        global $DB;

        // Get the exercise id of the course based on the graduation exercise name
        $sql = 'SELECT cm.id AS exerciseid
            FROM {' . self::DB_ASSIGN . '} a
            INNER JOIN {' . self::DB_COURSE_MODULES . '} cm ON cm.instance = a.id
            INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
            WHERE cm.course = :courseid
            AND cm.deletioninprogress = 0
            AND m.name = :assign
            AND a.name = :exercisename';

        $exercises = $DB->get_record_sql($sql, ['courseid' => $courseid, 'assign' => 'assign', 'exercisename' => $exercisename]);

        return !empty($exercises) ? $exercises->exerciseid : 0;
    }

    /**
     * Returns the subscribed course last exercise
     *
     * @param int $courseid The course id of the section
     * @return int  The exercise id of the graduation last exercise
     */
    public static function get_subscriber_last_exercise(int $courseid) {
        global $DB;

        $exerciseid = 0;

        // Get the exercise id of the course based on the graduation exercise name
        $sql = 'SELECT cs.sequence
            FROM {' . self::DB_COURSE_MODULES . '} cm
            INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
            INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
            WHERE cm.course = :courseid
            AND cm.deletioninprogress = 0
            AND m.name = :assign
            ORDER BY cs.section DESC
            LIMIT 1';

        $section = $DB->get_record_sql($sql, ['courseid' => $courseid, 'assign' => 'assign']);

        // get the last exercise in the section's sequence of exercises
        if (!empty($section)) {

            $sequence = explode(',', $section->sequence);

            // handle course modules being deleted
            $sql = 'SELECT cm.id AS deletedid
                FROM {' . self::DB_COURSE_MODULES . '} cm
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                INNER JOIN {' . self::DB_MODULES . '} m ON m.id = cm.module
                WHERE cm.course = :courseid
                AND cm.deletioninprogress != 0
                AND m.name = :assign';

            $deleted = $DB->get_records_sql($sql, ['courseid' => $courseid, 'assign' => 'assign']);
            $deletedmods = array_keys($deleted);

            if (!empty($deletedmods))
                $exerciseid = array_values(array_diff($sequence, $deletedmods))[0];
            else
                $exerciseid = end($sequence);
        }

        return $exerciseid;
    }

    /**
     * Retrieves the number of modules for a specific exercise course.
     *
     * @param int $courseid The course id
     * @return int
     */
    public static function get_subscriber_modules_count(int $courseid) {
        global $DB;

        // Get the student's grades
        $sql = 'SELECT COUNT(cm.id) AS modules
                FROM {' . self::DB_MODULES . '} m
                INNER JOIN {' . self::DB_COURSE_MODULES . '} cm on m.id = cm.module
                WHERE cm.course = :courseid
                    AND cm.deletioninprogress = 0
                    AND m.name = :lesson
                ';


        return $DB->get_record_sql($sql, ['courseid' => $courseid, 'lesson' => 'lesson', ])->modules;
    }

    /**
     * Returns the first file stored for the context id and grade id (itemid)
     *
     * @param int $contextid The context id for the exercise (assignment)
     * @param int $itemid    The context id for the exercise (assignment)
     * @return object  The file record
     */
    public static function get_file_info(int $contextid, int $itemid) {
        global $DB;

        // Get the full user name
        $sql = 'SELECT * FROM {' . self::DB_FILES . '}
                WHERE
                    contextid = :contextid AND
                    itemid = :itemid AND
                    component = :component AND
                    filearea = :filearea AND
                    filesize > 0
                LIMIT 1';

        $params = [
            'contextid' => $contextid,
            'itemid'    => $itemid,
            'component' => 'assignfeedback_file',
            'filearea'  => 'feedback_files'
        ];

        return $DB->get_record_sql($sql, $params);
    }
}