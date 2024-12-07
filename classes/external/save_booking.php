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

use DateTime;
use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_booking\local\message\notification;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_booking extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'bookedslot'  => new external_single_structure(
                        array(
                            'starttime' => new external_value(PARAM_INT, 'booked slot start time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'endtime' => new external_value(PARAM_INT, 'booked slot end time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'year' => new external_value(PARAM_INT, 'booked slot year', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'week' => new external_value(PARAM_INT, 'booked slot week', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                        ), 'booking'),
                'courseid'    => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'exerciseid'  => new external_value(PARAM_INT, 'The exercise id', VALUE_DEFAULT),
                'studentid'   => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Save booked slots. Delete existing ones for the user then update
     * any existing slots if applicable with slot values
     *
     * @param {object} $bookedslot array containing booked slots.
     * @param int $exerciseid The exercise the session is for.
     * @param int $studentid The student id associated with the slot.
     * @param int $refslotid The session slot associated.
     * @return array array of slots created.
     */
    public static function execute($slottobook, $courseid, $exerciseid, $studentid) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
                'bookedslot' => $slottobook,
                'courseid'   => $courseid,
                'exerciseid' => $exerciseid,
                'studentid'  => $studentid
                )
            );

        // set the subscriber object
        $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

        $instructorid = $USER->id;

        // add a new tentatively booked slot for the student.
        $sessiondata = [
            'exercise'  => $subscriber->get_exercise($exerciseid)->name,
            'instructor'=> student::get_fullname($instructorid),
            'status'    => ucwords(get_string('statustentative', 'local_booking')),
        ];

        // add new booking and update slot
        $studentslot = new slot(0,
            $studentid,
            $courseid,
            $slottobook['starttime'],
            $slottobook['endtime'],
            $slottobook['year'],
            $slottobook['week'],
            get_string('statustentative', 'local_booking'),
            get_string('bookinginfo', 'local_booking', $sessiondata)
        );

        $newbooking = new booking(0, $courseid, $studentid, $exerciseid, $studentslot,'', $instructorid);
        $result = $newbooking->save();

        if ($result) {
            // remove restriction override for the user
            set_user_preference('local_booking_' .$courseid . '_availabilityoverride', false, $studentid);

            // remove instructor from inactive group where applicable
            $instructor = new instructor($subscriber, $instructorid);
            if (!$instructor->is_active()) {
                $instructor->activate();
            }

            // send emails to both student and instructor
            $sessionstart = new DateTime('@' . $slottobook['starttime']);
            $sessionend = new DateTime('@' . $slottobook['endtime']);
            $message = new notification($subscriber);
            if ($message->send_booking_notification($studentid, $exerciseid, $sessionstart, $sessionend)) {
                $message->send_instructor_confirmation($studentid, $exerciseid, $sessionstart, $sessionend);
            }
            $sessiondata['sessiondate'] = $sessionstart->format('D M j\, H:i');
            $sessiondata['studentname'] = student::get_fullname($studentid);
            \core\notification::SUCCESS(get_string('bookingsavesuccess', 'local_booking', $sessiondata));
        } else {
            \core\notification::WARNING(get_string('bookingsaveunable', 'local_booking'));
        }

        return array(
            'result' => $result,
            'warnings' => array()
        );
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
