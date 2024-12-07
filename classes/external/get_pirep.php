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

use core_user;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/booking/lib.php');


use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_booking\exporters\logentry_exporter;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\output\views\logentry_view;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_pirep extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'pirep'  => new external_value(PARAM_TEXT, 'The PIREP id', VALUE_DEFAULT),
                'courseid'  => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
                'exerciseid'  => new external_value(PARAM_INT, 'The exercise id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve a PIREP by id if there is a PIREPs database integration.
     *
     * @param int $logentryid The logbook entry id.
     * @param int $courseid The course id in context.
     * @param int $userid The user id in context.
     * @param int $exerciseid The exerciseid id in context.
     * @return array array of slots created.
     */
    public static function execute($pirep, $courseid, $userid, $exerciseid) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'pirep' => $pirep,
                'courseid' => $courseid,
                'userid' => $userid,
                )
            );

        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

        $result = true;
        $warnings = array();
        $errorcode = '';

        // get PIREP integrated info
        $logentry = (new logbook($params['courseid'], $params['userid']))->create_logentry();
        $pireprec = $subscriber->get_external_data('pireps', 'pirepinfo', $params['pirep']);

        if (!empty($pireprec)) {

            // get logentry data from the PIREP record
            $logentry->read($pireprec);

            // get pilot integrated info
            if (subscriber::has_integration('external_data', 'pilots')) {
                $pilotrec = $subscriber->get_external_data('pilots', 'pilotinfo', $logentry->get_pilotid());
                $alternatename = $pilotrec['alternatename'];

                if (core_user::get_user($userid, 'alternatename')->alternatename == $alternatename) {

                    // get engine type integrated data
                    if ($subscriber->has_integration('external_data', 'aircraft')) {
                        $enginetyperec = $subscriber->get_external_data('aircraft', 'aircraftinfo', $logentry->get_aircraft());
                        if (!empty($enginetyperec))
                            $logentry->set_enginetype($enginetyperec['engine_type'] == 'single' ? 'SE' : 'ME');
                    }

                    // export logentry data
                    $data = [
                        'subscriber'=> $subscriber,
                        'logentry'  => $logentry,
                        'exerciseid'=> $exerciseid,
                        'view'      => 'summary',
                        'nullable'  => false
                    ];
                    $data += $params;
                    $entry = new logentry_view($data, ['subscriber'=>$subscriber, 'context'=>$subscriber->get_context()]);
                    $data = $entry->get_exported_data();
                    $warnings[] = [
                        'item' => $pirep,
                        'warningcode' => '0',
                        'message' => get_string('pirepfound' . (!empty($logentry->get_linkedpirep()) ? '' : 'notlinked'), 'local_booking')
                    ];

                } else {
                    $result = false;
                    $errorcode = 'errorp1pirepwrongpilot';
                }
            } else {
                $result = false;
                $errorcode = 'errorp1pirepnopilotintegration';
            }
        } else {
            $result = false;
            $errorcode = 'errorp1pirepnotfound';
        }

        if (!$result) {
            // get empty logentry for returns structure
            $data = $logentry->__toArray(false, false) + $params;
            $data['canedit'] = $subscriber->get_instructor($userid)->is_instructor();
            $data['visible'] = 1;
            // set the warring message
            $warnings[] = [
                'item' => $pirep,
                'warningcode' => $errorcode,
                'message' => get_string($errorcode, 'local_booking')
            ];
        }

        return array('logentry' => $data, 'result' => $result, 'warnings' => $warnings);
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
            'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
            'warnings' => new external_warnings()
            )
        );
    }
}
