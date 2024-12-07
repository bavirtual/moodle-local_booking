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
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_booking\exporters\logentry_exporter;
use local_booking\local\logbook\form\create as update_logentry_form;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\message\notification;
use local_booking\output\views\logentry_view;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_logentry extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'formargs' => new external_value(PARAM_RAW, 'The arguments from the logentry form'),
                'formdata' => new external_value(PARAM_RAW, 'The data from the logentry form'),
            ]
        );
    }

    /**
     * Handles the logbook entry form submission (add/edit).
     *
     * @param string $formdata The logentry form data in a URI encoded param string
     * @return array The created or modified logbook entry
     */
    public static function execute($formargs, $formdata) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), ['formargs' => $formargs, 'formdata' => $formdata]);
        $args = [];
        $data = [];

        parse_str($params['formargs'], $args);
        parse_str($params['formdata'], $data);

        if (WS_SERVER) {
            // Request via WS, ignore sesskey checks in form library.
            $USER->ignoresesskey = true;
        }

        $courseid = $args['courseid'];
        $exerciseid = $args['exerciseid'];
        $userid = $args['userid'];
        $editing = !empty($data['id']);


        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $courseid);

        $formoptions = [
            'subscriber' => $subscriber,
            'courseid'   => $courseid,
            'userid'     => $userid,
            'exerciseid' => $exerciseid,
        ];

        // if the operation is an update, get the logentry
        if ($editing) {
            $logentryuser = $subscriber->get_participant($userid);
            $logbook = new logbook($courseid, $userid);
            $logentry = $logbook->get_logentry($data['id']);
            $formoptions['logentry'] = $logentry;
        }

        // get the form data and persist the new entry(s)
        $mform = new update_logentry_form(null, $formoptions, 'post', '', null, true, $data);
        if ($validateddata = $mform->get_data()) {
            // for entry update, populate logentry then save
            if ($editing) {
                $logentry->populate($validateddata, $logentryuser->is_instructor(), true);
                $logentry->save();
            // for new entries, populate instructor and student logentries then save
            } else {
                // add student logentry
                $studentlogbook = new logbook($courseid, $userid);
                $studentlogentry = $studentlogbook->create_logentry();
                $studentlogentry->populate($validateddata);

                if (property_exists($validateddata, 'flighttypehidden') && $validateddata->flighttypehidden != 'solo') {
                    // add instructor logentry, the user creating the entry is always the instructor
                    $instructorlogbook = new logbook($courseid, $USER->id);
                    $instructorlogentry = $instructorlogbook->create_logentry();
                    $instructorlogentry->populate($validateddata, true);
                    logbook::save_linked_logentries($courseid, $instructorlogentry, $studentlogentry);
                } else {
                    $studentlogentry->save();
                }

                // logentry for the exporter either student or instructor logentry would do
                $logentry = $studentlogentry;
            }

            // get exporter output for return values
            $viewdata = ['subscriber'=>$subscriber, 'logentry'=>$logentry, 'userid'=>$userid];
            $entry = new logentry_view($viewdata, ['subscriber'=>$subscriber, 'context'=>$subscriber->get_context()]);
            $output = $entry->get_exported_data();

            // send student notification for new logbook entries
            if (!$editing) {
                (new notification($subscriber))->send_logentry_notification($logentry);
            }

            \core\notification::SUCCESS(get_string('logentrysavesuccess', 'local_booking'));

            return [ 'logentry' => $output ];
        } else {
            \core\notification::ERROR(get_string('logentrysaveunable', 'local_booking'));
            return [ 'validationerror' => true ];
        }
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     *
     */
    public static function  execute_returns() {
        $logentrystructure = logentry_exporter::get_read_structure();
        $logentrystructure->required = VALUE_OPTIONAL;

        return new external_single_structure(
            array(
                'logentry' => $logentrystructure,
                'validationerror' => new external_value(PARAM_BOOL, 'Invalid form data', VALUE_DEFAULT, false),
            )
        );
    }
}
