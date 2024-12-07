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
use local_booking\local\message\notification;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cancel_booking extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(array(
                'bookingid' => new external_value(PARAM_INT, 'The booking id', VALUE_DEFAULT),
                'comment' => new external_value(PARAM_RAW, 'The instructor comment regarding the cancellation', VALUE_DEFAULT),
                'noshow' => new external_value(PARAM_BOOL, 'Whether the cancellation is a no-show or instructor initiated', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Cancel an instructor's booking.
     *
     * @param int $bookingid
     * @param string $comment
     * @param bool $noshow
     * @return array $result
     */
    public static function execute($bookingid, $comment, $noshow) {

        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), array(
            'bookingid' => $bookingid,
            'comment' => $comment,
            'noshow' => $noshow,
            )
        );

        $result = false;
        $msg = '';

        // get the booking to be cancelled
        if (!empty($bookingid)) {

            $booking = new booking($params['bookingid']);
            $booking->load();
            $courseid = $booking->get_courseid();

            // set the subscriber object
            $subscriber = get_course_subscriber_context('/local/booking/', $params['courseid']);

            // cancel the booking
            if ($result = $booking->cancel($noshow)) {

                // suspend the student in the case of repetitive noshows
                if ($noshow) {
                    $student = new student($subscriber, $booking->get_studentid());
                    if (count($student->get_noshow_bookings()) > 1) {
                        $student->suspend();
                    }

                    // send cancellation message
                    $message = new notification($subscriber);
                    $result = $message->send_noshow_notification( $booking, $subscriber->get_senior_instructors());

                } else {

                    // enable restriction override if enabled to allow the student to repost slots sooner
                    if (intval($subscriber->overdueperiod) > 0) {
                        set_user_preference('local_booking_' . $courseid . '_availabilityoverride', true, $booking->get_studentid());
                    }

                    // send cancellation message to both instructor and student
                    $cancellationmessage = new notification($subscriber);
                    $result = $cancellationmessage->send_session_cancellation( $booking, $comment);

                }

                // confirmation Moodle notification to the instructor
                $msg = get_string('bookingcanceledsuccess', 'local_booking', ['studentname'=>student::get_fullname($booking->get_studentid())]);
                $msg .= $noshow ? ' ' . get_string('bookingcanceledsuccesswnoshow', 'local_booking') : '';
            }

        } else {
            $msg = get_string('bookingcancelednotfound', 'local_booking');
        }

        if ($result) {
            \core\notification::SUCCESS($msg);
        } else {
            \core\notification::WARNING($msg ?: get_string('bookingcanceledunable', 'local_booking'));
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
