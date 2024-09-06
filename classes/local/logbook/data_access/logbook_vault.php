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

namespace local_booking\local\logbook\data_access;

defined('MOODLE_INTERNAL') || die();

use local_booking\local\logbook\entities\logbook;
use local_booking\local\logbook\entities\logentry;
use stdClass;

class logbook_vault implements logbook_vault_interface {

    /** Bookings table name for the persistent. */
    const DB_LOGBOOKS = 'local_booking_logbooks';

    /** Course Modules table name for the persistent. */
    const DB_COURSE_MODULES = 'course_modules';

    /** Course Sections table name for the persistent. */
    const DB_COURSE_SECTIONS = 'course_sections';

    /**
     * Get a user's logbook.
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param logbook   $logbook    The logbook_interface of for all entries.
     * @param bool      $allentries Whether to get entries for all courses
     * @return logentries[]     Array of logentry_interfaces.
     */
    public static function get_logbook(int $courseid, int $userid, $logbook, $allentries = false) {
        global $DB;

        $coursestatement = !$allentries ? 'courseid = :courseid AND' : '';
        $logbookentries = [];
        $sql = 'SELECT lb.id, lb.courseid, lb.exerciseid, lb.sessionid, lb.userid, lb.pirep, lb.callsign,
                    lb.flighttype, lb.flightdate, lb.depicao, lb.deptime, lb.arricao, lb.arrtime,
                    lb.aircraft, lb.aircraftreg, lb.enginetype, lb.route, lb.multipilottime, lb.p1id, lb.p2id,
                    lb.landingsday, lb.landingsnight, lb.groundtime, lb.flighttime, lb.nighttime, lb.ifrtime,
                    lb.pictime, lb.copilottime, lb.dualtime, lb.instructortime, lb.picustime, lb.checkpilottime,
                    lb.fstd, lb.remarks, lb.linkedlogentryid, lb.createdby, lb.timecreated, lb.timemodified
                FROM {' . self::DB_LOGBOOKS . '} lb
                INNER JOIN {' . self::DB_COURSE_MODULES . '} cm ON cm.id = lb.exerciseid
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                WHERE ' . $coursestatement . '
                    userid = :userid
                    AND cm.deletioninprogress = 0
                ORDER BY lb.flightdate DESC';

        $param = ['courseid'=>$courseid, 'userid'=>$userid];
        $logentryrecs = $DB->get_records_sql($sql, $param);
        foreach ($logentryrecs as $logentryrec) {
            $logbookentries[] = self::get_logentry_instance($logentryrec, $logbook->create_logentry());
        }

        return $logbookentries;
    }

    /**
     * Get a specific logbook entry.
     *
     * @param int $userid       The logentry user id.
     * @param int $courseid     The id of the course in context.
     * @param logbook $logbook  The logbook_interface of for all entries.
     * @param int $logentryid   The logentry id.
     * @param int $exerciseid   The logentry with exericse id.
     * @param int $sessionid    The logentry with session id.
     * @return logentry         A logentry_insterface.
     */
    public static function get_logentry(int $userid, int $courseid, $logbook, int $logentryid = 0, int $exerciseid = 0, int $sessionid = 0) {
        global $DB;

        $logentry = $logbook->create_logentry();
        $conditions = [];
        if ($logentryid != 0) {
            $conditions = ['id'=>$logentryid];
         } else if ($exerciseid != 0) {
            $conditions = [
                'courseid'=>$courseid,
                'exerciseid'=>$exerciseid,
                'userid'=>$userid
            ];
        } else if  ($sessionid != 0) {
            $conditions = [
                'courseid'=>$courseid,
                'sessionid'=>$sessionid,
                'userid'=>$userid
            ];
        }
        $logentryrecs = $DB->get_records(static::DB_LOGBOOKS, $conditions,'id DESC','*',0,1);
        if ($logentryrecs)
            $logentry = self::get_logentry_instance(array_values($logentryrecs)[0], $logentry);

        return $logentry;
    }

    /**
     * Create a user's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param logentry  $logentry   A logbook entry of the user.
     * @return int      The log entry id.
     */
    public static function insert_logentry(int $courseid, int $userid, logentry $logentry) {
        global $DB;

        $logentryobj = self::get_logentryrecobj($courseid, $userid, $logentry);

        return $DB->insert_record(static::DB_LOGBOOKS, $logentryobj);
    }

    /**
     * Update a user's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param logentry  $logentry   A logbook entry of the user.
     * @return bool     result of the database update operation.
     */
    public static function update_logentry(int $courseid, int $userid, logentry $logentry){
        global $DB;

        $logentryobj = self::get_logentryrecobj($courseid, $userid, $logentry, false);

        return $DB->update_record(static::DB_LOGBOOKS, $logentryobj);
    }

    /**
     * Delete a logbook entry by id with its associated entries.
     *
     * @param int   $logentryid         The logbook entry id to be deleted.
     * @param int   $linkedlogentryid   The associated logbook entry id to be deleted.
     * @return bool result of the database update operation.
     */
    public static function delete_logentry(int $logentryid, int $linkedlogentryid = 0) {
        global $DB;

        // start a transaction
        $transaction = $DB->start_delegated_transaction();

        $result = $DB->delete_records(static::DB_LOGBOOKS, ['id'=>$logentryid]);
        if (!empty($linkedlogentryid))
            $result = $result && $DB->delete_records(static::DB_LOGBOOKS, ['id'=>$linkedlogentryid]);

        if ($result)
            $transaction->allow_commit();
        else
            $transaction->rollback(new \moodle_exception(get_string('errordelete', 'local_booking')));

        return $result;
    }

    /**
     * Insert/Update then link two logentries.
     *
     * @param int courseid
     * @param logentry $logentry1
     * @param logentry $logentry2
     * @return bool
     */
    public static function save_linked_logentries(int $courseid, logentry $logentry1, logentry $logentry2) {
        global $DB;
        $result = true;

        // start a transaction
        $transaction = $DB->start_delegated_transaction();

        // determine if the entries new and need to be inserted or in edit and need to be updated
        if (!empty($logentry1->get_id()) && !empty($logentry2->get_id())) {
            $result = ($logentry1id = self::update_logentry($courseid, $logentry1->get_userid(), $logentry1));
            $logentry1->set_id($logentry1id);
            if ($result) {
                $logentry2->set_linkedlogentryid($logentry1id);
                $result = ($logentry2id = self::update_logentry($courseid, $logentry2->get_userid(), $logentry2));
                $logentry2->set_id($logentry2id);
            }
        } else {
            // insert instructor entry, then student's then update instructor with the student entry id
            $result = $logentry1id = self::insert_logentry($courseid, $logentry1->get_p1id(), $logentry1);
            $logentry1->set_id($logentry1id);
            $logentry2->set_linkedlogentryid($logentry1id);
            $logentry2->set_pirep($logentry1->get_linkedpirep());
            $logentry2->get_linkedpirep($logentry1->get_pirep());
            if ($result) {
                $result = ($logentry2id = self::insert_logentry($courseid, $logentry2->get_userid(), $logentry2));
                $logentry2->set_id($logentry2id);
            }
        }

        // link first logentry
        if ($result)
            $result = $DB->execute('UPDATE {' . static::DB_LOGBOOKS . '} SET linkedlogentryid=' . $logentry2id . ' WHERE id=' . $logentry1id);

        if ($result)
            $result = $transaction->allow_commit();
        else
            $transaction->rollback(new \moodle_exception(get_string('errorlinking', 'local_booking')));

        return $result;
    }

    /**
     * Get summary of logentries of the entire logbook.
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param int       $examid     The exam id associated with the course.
     * @param  bool     $allcourses The totals of all courses
     * @return object   $totaldualtime, $totalgroundtime, $totalpictime
     */
    public static function get_logbook_summary(int $courseid, int $userid, int $examid, bool $allcourses = false) {
        global $DB;

        $sql = 'SELECT SUM(groundtime) + SUM(flighttime) as totalsessiontime,
                    SUM(groundtime) as totalgroundtime,
                    SUM(flighttime) as totalflighttime,
                    SUM(pictime) as totalpictime,
                    SUM(dualtime) as totaldualtime,
                    SUM(instructortime) as totalinstructortime,
                    SUM(CASE
                        WHEN exerciseid = :examexerciseid AND p1id = :examinerid
                        THEN flighttime
                        ELSE 0
                        END) as totalexaminertime,
                    SUM(picustime) as totalpicustime,
                    SUM(multipilottime) as totalmultipilottime,
                    SUM(copilottime) as totalcopilottime,
                    SUM(nighttime) as totalnighttime,
                    SUM(ifrtime) as totalifrtime,
                    SUM(checkpilottime) as totalcheckpilottime,
                    SUM(landingsday) as totallandingsday,
                    SUM(landingsnight) as totallandingsnight
                FROM {' . self::DB_LOGBOOKS .'}
                WHERE ' . ($allcourses ? '' : 'courseid = :courseid AND ') .
                    'userid = :userid';

        $params = [
            'courseid'       => $courseid,
            'userid'         => $userid,
            'examinerid'     => $userid,
            'examexerciseid' => $examid
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Get summary of logentries up to a specific exercise.
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @param int       $exerciseid The exercise id to sum up to.
     * @return object
     */
    public static function get_logbook_summary_to_exercise(int $courseid, int $userid, int $exerciseid) {
        global $DB;

        // get the section and sequence of the exercise to total all prior logentries
        $sql = 'SELECT cs.section, cs.sequence
                FROM mdl_course_modules cm
                INNER JOIN mdl_course_sections cs ON cs.id = cm.section
                WHERE cs.course = :courseid
                AND cm.id = :exerciseid';
        $sectionrec = $DB->get_record_sql($sql, array('courseid'=>$courseid, 'exerciseid'=>$exerciseid));
        $section = $sectionrec->section;
        if (!empty($sectionrec->sequence))
            $sequence = explode(',', $sectionrec->sequence);

        // slice the sequence until the exercise to exclude exercises after it in the same section
        $sequencearr = array_slice($sequence, array_search($exerciseid, $sequence)+1);

        // get summations up until specified section for the student of a specific course
        $sql = 'SELECT SUM(l.groundtime) + SUM(l.flighttime) as totalsessiontime,
                    SUM(l.groundtime) as totalgroundtime,
                    SUM(l.flighttime) as totalflighttime,
                    SUM(l.pictime) as totalpictime,
                    SUM(l.dualtime) as totaldualtime,
                    SUM(l.instructortime) as totalinstructortime,
                    SUM(l.picustime) as totalpicustime,
                    SUM(l.multipilottime) as totalmultipilottime,
                    SUM(l.copilottime) as totalcopilottime,
                    SUM(l.nighttime) as totalnighttime,
                    SUM(l.ifrtime) as totalifrtime,
                    SUM(l.checkpilottime) as totalcheckpilottime,
                    SUM(l.landingsday) as totallandingsday,
                    SUM(l.landingsnight) as totallandingsnight
                FROM {' . self::DB_LOGBOOKS .'} l
                INNER JOIN {' . self::DB_COURSE_MODULES .'} cm ON cm.id = l.exerciseid
                INNER JOIN {' . self::DB_COURSE_SECTIONS .'} cs ON cs.id = cm.section
                WHERE l.courseid = :courseid
                AND cm.deletioninprogress = 0
                AND l.userid = :userid
                AND cs.section <= :section' . (!empty($sequencearr) ? ' AND exerciseid NOT IN(' . implode(',', $sequencearr) . ')' : '');

        $params = [
            'courseid' => $courseid,
            'userid' => $userid,
            'section' => $section
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Create an object to be persisted
     *
     * @param logentry  $logentry       A logbook entry of the user.
     * @return stdClass $logentryobj    The log entry object for persistence.
     */
    protected static function get_logentryrecobj(int $courseid, int $userid, logentry $logentry, bool $newrec = true) {
        global $USER;
        $logentryobj = new stdClass();

        $logentryobj->id = $logentry->get_id();
        $logentryobj->courseid = $courseid;
        $logentryobj->exerciseid = $logentry->get_exerciseid();
        $logentryobj->sessionid = $logentry->get_sessionid();
        $logentryobj->userid = $userid;
        $logentryobj->flightdate = $logentry->get_flightdate();
        $logentryobj->groundtime = $logentry->get_groundtime();
        $logentryobj->flighttime = $logentry->get_flighttime();
        $logentryobj->p1id = $logentry->get_p1id();
        $logentryobj->p2id = $logentry->get_p2id();
        $logentryobj->pictime = $logentry->get_pictime();
        $logentryobj->dualtime = $logentry->get_dualtime();
        $logentryobj->instructortime = $logentry->get_instructortime();
        $logentryobj->picustime = $logentry->get_picustime();
        $logentryobj->multipilottime = $logentry->get_multipilottime();
        $logentryobj->copilottime = $logentry->get_copilottime();
        $logentryobj->pirep = $logentry->get_pirep();
        $logentryobj->callsign = $logentry->get_callsign();
        $logentryobj->flighttype = $logentry->get_flighttype();
        $logentryobj->fstd = $logentry->get_fstd();
        $logentryobj->depicao = $logentry->get_depicao();
        $logentryobj->deptime = $logentry->get_deptime();
        $logentryobj->arricao = $logentry->get_arricao();
        $logentryobj->arrtime = $logentry->get_arrtime();
        $logentryobj->aircraft = $logentry->get_aircraft();
        $logentryobj->aircraftreg = $logentry->get_aircraftreg();
        $logentryobj->enginetype = $logentry->get_enginetype();
        $logentryobj->route = $logentry->get_route();
        $logentryobj->landingsday = $logentry->get_landingsday();
        $logentryobj->landingsnight = $logentry->get_landingsnight();
        $logentryobj->nighttime = $logentry->get_nighttime();
        $logentryobj->ifrtime = $logentry->get_ifrtime();
        $logentryobj->checkpilottime = $logentry->get_checkpilottime();
        $logentryobj->remarks = $logentry->get_remarks();
        $logentryobj->linkedlogentryid = $logentry->get_linkedlogentryid();
        if ($newrec) {
            $logentryobj->createdby = $USER->id;
            $logentryobj->timecreated = time();
        } else {
            $logentryobj->timemodified = time();
        }

        return $logentryobj;
    }

    /**
     * Create an logentry instance from a database recordobject to be persisted
     *
     * @param object    $dataob      A data record representing a logbook entry.
     * @param logbook   $logbook     The logbook_interface of for all entries.
     * @return logentry $logentryobj The log entry instance.
     */
    protected static function get_logentry_instance($dataobj, $logentry) {
        if ($dataobj) {
            $logentry->set_id($dataobj->id);
            $logentry->set_courseid($dataobj->courseid);
            $logentry->set_exerciseid($dataobj->exerciseid);
            $logentry->set_sessionid($dataobj->sessionid);
            $logentry->set_flightdate($dataobj->flightdate);
            $logentry->set_groundtime($dataobj->groundtime);
            $logentry->set_flighttime($dataobj->flighttime);
            $logentry->set_p1id($dataobj->p1id);
            $logentry->set_p2id($dataobj->p2id);
            $logentry->set_pictime($dataobj->pictime);
            $logentry->set_dualtime($dataobj->dualtime);
            $logentry->set_instructortime($dataobj->instructortime);
            $logentry->set_picustime($dataobj->picustime);
            $logentry->set_multipilottime($dataobj->multipilottime);
            $logentry->set_copilottime($dataobj->copilottime);
            $logentry->set_checkpilottime($dataobj->checkpilottime);
            $logentry->set_pirep($dataobj->pirep);
            $logentry->set_callsign($dataobj->callsign);
            $logentry->set_flighttype($dataobj->flighttype);
            $logentry->set_fstd($dataobj->fstd);
            $logentry->set_depicao($dataobj->depicao);
            $logentry->set_deptime($dataobj->deptime);
            $logentry->set_arricao($dataobj->arricao);
            $logentry->set_arrtime($dataobj->arrtime);
            $logentry->set_aircraft($dataobj->aircraft);
            $logentry->set_aircraftreg($dataobj->aircraftreg);
            $logentry->set_enginetype($dataobj->enginetype);
            $logentry->set_route($dataobj->route);
            $logentry->set_landingsday($dataobj->landingsday);
            $logentry->set_landingsnight($dataobj->landingsnight);
            $logentry->set_nighttime($dataobj->nighttime);
            $logentry->set_ifrtime($dataobj->ifrtime);
            $logentry->set_remarks($dataobj->remarks);
            $logentry->set_linkedlogentryid($dataobj->linkedlogentryid);
        }
        return $logentry;
    }
}