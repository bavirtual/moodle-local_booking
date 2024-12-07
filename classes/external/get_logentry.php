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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/booking/lib.php');

use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_booking\exporters\logentry_exporter;
use local_booking\local\logbook\entities\logbook;
use local_booking\output\views\logentry_view;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_logentry extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'logentryid'  => new external_value(PARAM_INT, 'The logbook entry id', VALUE_DEFAULT),
                'courseid'  => new external_value(PARAM_INT, 'The course id in context', VALUE_DEFAULT),
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve a logbook entry by id.
     *
     * @param int $logentryid The logbook entry id.
     * @param int $courseid The course id in context.
     * @param int $userid The user user id in context.
     * @return array array of slots created.
     */
    public static function execute(int $logentryid, int $courseid, int $userid) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'logentryid' => $logentryid,
                'courseid' => $courseid,
                'userid' => $userid,
                )
            );

        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

        $logentry = (new logbook($params['courseid'], $params['userid']))->get_logentry($params['logentryid']);
        $data = array('subscriber'=>$subscriber, 'logentry' => $logentry, 'view' => 'summary', 'canedit' => $subscriber->get_instructor($USER->id)->is_instructor()) + $params;
        $entry = new logentry_view($data, ['subscriber'=>$subscriber, 'context'=>$subscriber->get_context()]);

        return array('logentry' => $entry->get_exported_data(), 'warnings' => array());
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function execute_returns() {
        $logentrystructure = logentry_exporter::get_read_structure();

        return new external_single_structure(array(
            'logentry' => $logentrystructure,
            'warnings' => new external_warnings()
            )
        );
    }
}
