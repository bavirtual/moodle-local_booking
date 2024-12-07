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


use context_course;
use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use local_booking\local\logbook\entities\logbook;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_logentry extends external_api {

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
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
                'courseid'  => new external_value(PARAM_INT, 'The course id in context', VALUE_DEFAULT),
                'cascade'  => new external_value(PARAM_BOOL, 'Whether to ignore cascade delete of linked logentry', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Delete a logbook entry.
     *
     * @param int  $logentryid The logbook entry id.
     * @param int  $userid     The user user id in context.
     * @param int  $courseid   The course id in context.
     * @param bool $cascade   Whether to ignore cascading delete of linked logentry.
     * @return array $result   Whether the logentry was deleted or not.
     */
    public static function execute($logentryid, $userid, $courseid, $cascade) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'logentryid' => $logentryid,
                'courseid' => $courseid,
                'userid' => $userid,
                'cascade' => $cascade,
                )
            );

        $logbook = new logbook($params['courseid'], $params['userid']);
        $result = $logbook->delete($params['logentryid'], $params['cascade']);

        if ($result)
            \core\notification::SUCCESS(get_string('logentrydeletesuccess', 'local_booking'));
        else
            \core\notification::ERROR(get_string('logentrydeletefailed', 'local_booking'));

        return ['result'=>$result, 'warnings'=>array()];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }
}
