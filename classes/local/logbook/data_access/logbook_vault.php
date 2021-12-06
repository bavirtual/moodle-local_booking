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
     * @return logentries[]     Array of logentry_interfaces.
     */
    public static function get_logbook(int $courseid, int $userid, $logbook = null) {
        global $DB;

        $logbook = [];
        $sql = 'SELECT lb.id, lb.courseid, lb.exerciseid, lb.userid, lb.flighttimemins,
                    lb.sessiontimemins, lb.soloflighttimemins, lb.aircrafticao, lb.callsign,
                    lb.picid, lb.sicid, lb.pirep, lb.fromicao, lb.toicao, lb.timemodified
                FROM {' . self::DB_LOGBOOKS . '} lb
                INNER JOIN {' . self::DB_COURSE_MODULES . '} cm ON cm.id = lb.exerciseid
                INNER JOIN {' . self::DB_COURSE_SECTIONS . '} cs ON cs.id = cm.section
                WHERE userid = :userid
                ORDER BY lb.timemodified DESC';

        $param = ['userid'=>$userid];
        $logentryrecs = $DB->get_records_sql($sql, $param);
        foreach ($logentryrecs as $logentryrec) {
            $logbook[] = self::get_logentry_instance($logentryrec, $logbook);
        }

        return $logbook;
    }

    /**
     * Get a specific logbook entry.
     *
     * @param int $userid       The logentry user id.
     * @param int $courseid     The id of the course in context.
     * @param int $logentryid   The logentry id.
     * @param int $exerciseid   The logentry with exericse id.
     * @param logbook   $logbook    The logbook_interface of for all entries.
     * @return logentry         A logentry_insterface.
     */
    public static function get_logentry(int $userid, int $courseid, int $logentryid = 0, int $exerciseid = 0, $logbook) {
        global $DB;

        $conditions = $logentryid!=0 ? ['id'=>$logentryid] : [
            'courseid'=>$courseid,
            'exerciseid'=>$exerciseid,
            'userid'=>$userid
        ];
        $logentryrec = $DB->get_record(static::DB_LOGBOOKS, $conditions);

        return self::get_logentry_instance($logentryrec, $logbook);
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

        $logentryobj = self::get_logentryrecobj($courseid, $userid, $logentry);

        return $DB->update_record(static::DB_LOGBOOKS, $logentryobj);
    }

    /**
     * Update a user's logbook entry
     *
     * @param int       $courseid   The course id associated with the logbook.
     * @param int       $userid     The user id associated with the logbook.
     * @return array    $totalflighttime, $totalsessiontime, $totalsolotime
     */
    public static function get_logbook_summary(int $courseid, int $userid) {
        global $DB;

        $sql = 'SELECT SUM(flighttimemins) as totalflighttime,
                    SUM(sessiontimemins) as totalsessiontime,
                    SUM(soloflighttimemins) as totalsolotime
                FROM {' . self::DB_LOGBOOKS .'}
                WHERE courseid = :courseid
                AND userid = :userid';

        $params = [
            'courseid' => $courseid,
            'userid' => $userid
        ];
        $summary = $DB->get_record_sql($sql, $params);
        $totalflighttime = $summary->totalflighttime;
        $totalsessiontime = $summary->totalsessiontime;
        $totalsolotime = $summary->totalsolotime;

        return [$totalflighttime, $totalsessiontime, $totalsolotime];
    }

    /**
     * Delete a logbook entry by id
     *
     * @param int   $logentryid   The logbook entry id to be deleted.
     * @return bool result of the database update operation.
     */
    public static function delete_logentry($logentryid) {
        global $DB;

        return $DB->delete_records(static::DB_LOGBOOKS, ['id'=>$logentryid]);
    }

    /**
     * Create an object to be persisted
     *
     * @param logentry  $logentry       A logbook entry of the user.
     * @return stdClass $logentryobj    The log entry object for persistence.
     */
    protected static function get_logentryrecobj(int $courseid, int $userid, logentry $logentry) {
        $logentryobj = new stdClass();

        $logentryobj->id = $logentry->get_id();
        $logentryobj->courseid = $courseid;
        $logentryobj->exerciseid = $logentry->get_exerciseid();
        $logentryobj->userid = $userid;
        $logentryobj->flighttimemins = $logentry->get_flighttimemins();
        $logentryobj->soloflighttimemins = $logentry->get_soloflighttimemins();
        $logentryobj->sessiontimemins = $logentry->get_sessiontimemins();
        $logentryobj->aircrafticao = $logentry->get_aircraft();
        $logentryobj->picid = $logentry->get_picid();
        $logentryobj->sicid = $logentry->get_sicid();
        $logentryobj->pirep = $logentry->get_pirep();
        $logentryobj->callsign = $logentry->get_callsign();
        $logentryobj->fromicao = $logentry->get_fromicao();
        $logentryobj->toicao = $logentry->get_toicao();
        $logentryobj->timemodified = $logentry->get_sessiondate();

        return $logentryobj;
    }

    /**
     * Create an logentry instance from a database recordobject to be persisted
     *
     * @param object $dataob: A data record representing a logbook entry.
     * @return logentry $logentryobj: The log entry instance.
     */
    protected static function get_logentry_instance($dataobj, $logbook) {
        $logentry = new logentry($logbook);
        $logentry->set_id($dataobj->id);
        $logentry->set_exerciseid($dataobj->exerciseid);
        $logentry->set_flighttimemins($dataobj->flighttimemins);
        $logentry->set_sessiontimemins($dataobj->sessiontimemins);
        $logentry->set_soloflighttimemins($dataobj->soloflighttimemins);
        $logentry->set_picid($dataobj->picid);
        $logentry->set_sicid($dataobj->sicid);
        $logentry->set_aircraft($dataobj->aircrafticao);
        $logentry->set_callsign(!empty($dataobj->callsign)?$dataobj->callsign:'');
        $logentry->set_pirep(!empty($dataobj->pirep)?$dataobj->pirep:'');
        $logentry->set_fromicao(!empty($dataobj->fromicao)?$dataobj->fromicao:'');
        $logentry->set_toicao(!empty($dataobj->toicao)?$dataobj->toicao:'');
        $logentry->set_sessiondate($dataobj->timemodified);

        return $logentry;
    }
}