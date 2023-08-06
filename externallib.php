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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/local/booking/lib.php');

use core_external\external_api;
use core_external\external_value;
use core_external\external_warnings;
use core_external\external_single_structure;
use core_external\external_function_parameters;
use local_booking\external\logentry_exporter;
use local_booking\local\logbook\forms\create as update_logentry_form;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\message\notification;
use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\output\views\booking_view;
use local_booking\output\views\calendar_view;
use local_booking\output\views\logentry_view;

/**
 * Session Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_booking_external extends external_api {

    // Availability slots table name for.
    const DB_SLOTS = 'local_booking_slots';

    /**
     * Sets the course subscriber and context url
     *
     * @param string $url       PAGE url
     * @param int    $courseid  course id for context
     */
    protected static function get_course_subscriber_context(string $url, int $courseid) {
        global $PAGE, $COURSE;

        $context = context_course::instance($courseid);
        self::validate_context($context);
        $PAGE->set_url($url);
        $PAGE->set_context($context);

        // define subscriber globally
        if (empty($COURSE->subscriber)) {
            $COURSE->subscriber = new subscriber($courseid);
        }

        return $COURSE->subscriber;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_bookings_view_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'filter'  => new external_value(PARAM_RAW, 'The results filter', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve instructor's booking.
     *
     * @param int $courseid The course id for context.
     * @param string $filter The filter to show students, inactive (including graduates), suspended, and default to active.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function get_bookings_view(int $courseid, string $filter) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::get_bookings_view_parameters(), array(
                'courseid' => $courseid,
                'filter' => $filter,
                )
            );

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        // data required get data
        $data = [
            'instructor' => $subscriber->get_instructor($USER->id),
            'action'     => 'book',
            'view'       => 'sessions',
            'sorttype'   => '',
            'filter'     => $filter,
        ];

        // get students bookings and progression view
        $bookingview = new booking_view($subscriber->get_context(), $courseid, $data);

        return $bookingview->get_exported_data();
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function get_bookings_view_returns() {
        return \local_booking\external\bookings_exporter::get_read_structure();
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_pilot_logbook_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id in context', VALUE_DEFAULT),
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function get_pilot_logbook_returns() {
        return new external_multiple_structure(logentry_exporter::get_read_structure());
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_logentry_by_id_parameters() {
        return new external_function_parameters(
            array(
                'logentryid'  => new external_value(PARAM_INT, 'The logbook entry id', VALUE_DEFAULT),
                'courseid'  => new external_value(PARAM_INT, 'The course id in context', VALUE_DEFAULT),
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve a logbook entry by its id.
     *
     * @param int $logentryid The logbook entry id.
     * @param int $courseid The course id in context.
     * @param int $userid The user user id in context.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function get_logentry_by_id(int $logentryid, int $courseid, int $userid) {

        // Parameter validation.
        $params = self::validate_parameters(self::get_logentry_by_id_parameters(), array(
                'logentryid' => $logentryid,
                'courseid' => $courseid,
                'userid' => $userid,
                )
            );

        $warnings = array();
        $logentry = (new logbook($courseid, $userid))->get_logentry($logentryid);
        $subscriber = self::get_course_subscriber_context('/local/booking/logbook?courseid=' . $courseid, $courseid);
        $data = array('subscriber'=>$subscriber, 'logentry' => $logentry, 'view' => 'summary') + $params;
        $entry = new logentry_view($subscriber->get_context(), $courseid, $data);

        return array('logentry' => $entry->get_exported_data(), 'warnings' => $warnings);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function get_logentry_by_id_returns() {
        $logentrystructure = logentry_exporter::get_read_structure();

        return new external_single_structure(array(
            'logentry' => $logentrystructure,
            'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_pirep_parameters() {
        return new external_function_parameters(
            array(
                'pirep'  => new external_value(PARAM_TEXT, 'The PIREP id', VALUE_DEFAULT),
                'courseid'  => new external_value(PARAM_INT, 'The cousre id', VALUE_DEFAULT),
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
                'exerciseid'  => new external_value(PARAM_INT, 'The exercise id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve a logbook entry by its id.
     *
     * @param int $logentryid The logbook entry id.
     * @param int $courseid The course id in context.
     * @param int $userid The user id in context.
     * @param int $exerciseid The exerciseid id in context.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function get_pirep($pirep, $courseid, $userid, $exerciseid) {

        // Parameter validation.
        $params = self::validate_parameters(self::get_pirep_parameters(), array(
                'pirep' => $pirep,
                'courseid' => $courseid,
                'userid' => $userid,
                )
            );

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        $result = true;
        $warnings = array();
        $errorcode = '';

        // get PIREP integrated info
        $logentry = (new logbook($courseid, $userid))->create_logentry();
        $pireprec = $subscriber->get_integrated_data('pireps', 'pirepinfo', $pirep);

        if (!empty($pireprec)) {

            // get logentry data from the PIREP record
            $logentry->read($pireprec);

            // get pilot integrated info
            if (subscriber::has_integration('pilots')) {
                // TODO: PHP9 deprecates dynamic properties
                $pilotidtag = 'pilot_id';
                $pilotrec = $subscriber->get_integrated_data('pilots', 'pilotinfo', $logentry->$pilotidtag);
                $alternatename = $pilotrec['alternatename'];

                if (core_user::get_user($userid, 'alternatename')->alternatename == $alternatename) {

                    // get engine type integrated data
                    if ($subscriber->has_integration('aircraft')) {
                        $enginetyperec = $subscriber->get_integrated_data('aircraft', 'aircraftinfo', $logentry->get_aircraft());
                        if (!empty($enginetyperec))
                            $logentry->set_enginetype($enginetyperec['engine_type'] == 'single' ? 'SE' : 'ME');
                    }

                    // export logentry data
                    $data = [
                        'subscriber'=> $subscriber,
                        'logentry'  => $logentry,
                        'courseid'  => $courseid,
                        'userid'    => $userid,
                        'exerciseid'=> $exerciseid,
                        'view'      => 'summary',
                        'nullable'  => false
                    ];
                    $entry = new logentry_view($subscriber->get_context(), $courseid, $data);
                    $data = $entry->get_exported_data();

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
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function get_pirep_returns() {
        $logentrystructure = logentry_exporter::get_read_structure();

        return new external_single_structure(array(
            'logentry' => $logentrystructure,
            'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
            'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function delete_logentry_parameters() {
        return new external_function_parameters(
            array(
                'logentryid'  => new external_value(PARAM_INT, 'The logbook entry id', VALUE_DEFAULT),
                'userid'  => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
                'courseid'  => new external_value(PARAM_INT, 'The course id in context', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Delete a logbook entry.
     *
     * @param int $logentryid The logbook entry id.
     * @param int $userid The user user id in context.
     * @param int $courseid The course id in context.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function delete_logentry($logentryid, $userid, $courseid) {
        global $PAGE;

        // Parameter validation.
        $params = self::validate_parameters(self::delete_logentry_parameters(), array(
                'logentryid' => $logentryid,
                'courseid' => $courseid,
                'userid' => $userid,
                )
            );

        $context = context_course::instance($courseid);
        self::validate_context($context);
        $PAGE->set_url('/local/booking/');

        $logbook = new logbook($courseid, $userid);

        if ($logbook->delete($logentryid))
            \core\notification::SUCCESS(get_string('logentrydeletesuccess', 'local_booking'));
        else
            \core\notification::ERROR(get_string('logentrydeletefailed', 'local_booking'));

        return null;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function delete_logentry_returns() {
        return null;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_weekly_view_parameters() {
        return new external_function_parameters(
            [
                'year' => new external_value(PARAM_INT, 'Year to be viewed', VALUE_REQUIRED),
                'week' => new external_value(PARAM_INT, 'Week to be viewed', VALUE_REQUIRED),
                'time' => new external_value(PARAM_INT, 'Timestamp of the first day of the week to be viewed', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course being viewed', VALUE_DEFAULT, SITEID, NULL_ALLOWED),
                'categoryid' => new external_value(PARAM_INT, 'Category being viewed', VALUE_DEFAULT, null, NULL_ALLOWED),
                'action' => new external_value(PARAM_RAW, 'The action being performed view or book', VALUE_DEFAULT, 'view', NULL_ALLOWED),
                'view' => new external_value(PARAM_RAW, 'The action being performed view or book', VALUE_DEFAULT, 'view', NULL_ALLOWED),
                'studentid' => new external_value(PARAM_INT, 'The user id the slots belongs to', VALUE_DEFAULT, 0, NULL_ALLOWED),
                'exerciseid' => new external_value(PARAM_INT, 'The exercise id the slots belongs to', VALUE_DEFAULT, 0, NULL_ALLOWED),
            ]
        );
    }

    /**
     * Get data for the weekly calendar view.
     *
     * @param   int     $year       The year to be shown
     * @param   int     $week       The week to be shown
     * @param   int     $time       The timestamp of the first day in the week to be shown
     * @param   int     $courseid   The course to be included
     * @param   int     $categoryid The category to be included
     * @param   string  $action     The action to be pefromed if in booking view
     * @param   string  $view       The view to be displayed if user or all
     * @param   int     $studentid  The student id the action is performed on
     * @param   int     $exercise   The exercise id the action is associated with
     * @return  array
     */
    public static function get_weekly_view($year, $week, $time, $courseid, $categoryid, $action, $view, $userid, $exerciseid) {

        // Parameter validation.
        $params = self::validate_parameters(self::get_weekly_view_parameters(), [
            'year'      => $year,
            'week'      => $week,
            'time'      => $time,
            'courseid'  => $courseid,
            'categoryid'=> $categoryid,
            'action'    => $action,
            'view'      => $view,
            'studentid' => $userid,
            'exerciseid'=> $exerciseid,
        ]);

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);
        $calendar = \calendar_information::create($time, $params['courseid'], $params['categoryid']);

        $data = [
            'calendar'  => $calendar,
            'view'      => $view,
            'action'    => $action,
            'student'   => $subscriber->get_participant($userid),
            'exerciseid'=> $exerciseid == null ? 0 : $exerciseid,
        ];

        $calendarview = new calendar_view($subscriber->get_context(), $courseid, $data);

        return $calendarview->get_exported_data();
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function get_weekly_view_returns() {
        return \local_booking\external\week_exporter::get_read_structure();
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function save_booking_parameters() {
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
     * @param int $studentid The student id assocaited with the slot.
     * @param int $refslotid The session slot associated.
     * @return array array of slots created.
     */
    public static function save_booking($slottobook, $courseid, $exerciseid, $studentid) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::save_booking_parameters(), array(
                'bookedslot' => $slottobook,
                'courseid'   => $courseid,
                'exerciseid' => $exerciseid,
                'studentid'  => $studentid
                )
            );

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        $warnings = array();
        $instructorid = $USER->id;

        // add a new tentatively booked slot for the student.
        $sessiondata = [
            'exercise'  => $subscriber->get_exercise_name($exerciseid),
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
            // remove restriciton override for the user
            set_user_preference('local_booking_' .$courseid . '_availabilityoverride', false, $studentid);

            // remove instructor from inactive group where applicable
            $instructor = new instructor($subscriber, $instructorid);
            if (!$instructor->is_active()) {
                $instructor->activate();
            }

            // send emails to both student and instructor
            $sessionstart = new DateTime('@' . $slottobook['starttime']);
            $sessionend = new DateTime('@' . $slottobook['endtime']);
            $message = new notification();
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
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function save_booking_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function cancel_booking_parameters() {
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
     * @return bool $result
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function cancel_booking($bookingid, $comment, $noshow) {
        global $COURSE;

        // Parameter validation.
        $params = self::validate_parameters(self::cancel_booking_parameters(), array(
            'bookingid' => $bookingid,
            'comment' => $comment,
            'noshow' => $noshow,
            )
        );

        $result = false;
        $warnings = array();
        $msg = '';

        // get the booking to be cancelled
        if (!empty($bookingid)) {

            $booking = new booking($bookingid);
            $booking->load();
            $courseid = $booking->get_courseid() ?: $COURSE->id;

            require_login($courseid, false);

            // set the subscriber object
            $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

            // cancel the booking
            if ($result = $booking->cancel($noshow)) {

                // suspend the student in the case of repetetive noshows
                if ($noshow) {
                    $student = new student($subscriber, $booking->get_studentid());
                    if (count($student->get_noshow_bookings()) > 1) {
                        $student->suspend();
                    }

                    // send cancellation message
                    $message = new notification();
                    $result = $message->send_noshow_notification($booking, $subscriber->get_senior_instructors());

                } else {

                    // enable restriction override if enabled to allow the student to repost slots sooner
                    if (intval($subscriber->overdueperiod) > 0) {
                        set_user_preference('local_booking_' . $courseid . '_availabilityoverride', true, $booking->get_studentid());
                    }

                    // send student cancellation message
                    $studentmessage = new notification();
                    $result = $studentmessage->send_session_cancellation($booking, $comment);

                    // send instructor cancellation message
                    $instructormessage = new notification();
                    $result = $instructormessage->send_session_cancellation($booking, $comment);

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
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function cancel_booking_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function is_conflicting_booking_parameters() {
        return new external_function_parameters(
            array(
                'studentid'  => new external_value(PARAM_INT, 'The student id the booking is for', VALUE_DEFAULT),
                'bookedslot'  => new external_single_structure(
                        array(
                            'starttime' => new external_value(PARAM_INT, 'booked slot start time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'endtime' => new external_value(PARAM_INT, 'booked slot end time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'year' => new external_value(PARAM_INT, 'booked slot year', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                            'week' => new external_value(PARAM_INT, 'booked slot week', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                        ), 'booking'),
            )
        );
    }

    /**
     * Checks if the booking conflicts with another booking.
     *
     * @param {object} $bookedslot array containing booked slots.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function is_conflicting_booking($studentid, $slottobook) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::save_booking_parameters(), array(
                'studentid' => $studentid,
                'bookedslot' => $slottobook,
                )
            );

        $result = false;
        $warnings = array();
        $instructorid = $USER->id;

        $conflictingbooking = booking::conflicts($instructorid, $studentid, $slottobook);

        if (!empty($conflictingbooking)) {

            // set the subscriber object
            $subscriber = self::get_course_subscriber_context('/local/booking/', $conflictingbooking->courseid);

            $result = true;
            $warninginfo = [
                'studentname'   => participant::get_fullname($conflictingbooking->studentid),
                'coursename'    => $subscriber->get_shortname(),
                'exercisename'  => $subscriber->get_exercise_name($conflictingbooking->exerciseid),
                'date'          => (new \DateTime('@' . $conflictingbooking->starttime))->format('l M j \a\t H:i \z\u\l\u'),
            ];
            $warnings[] = [
                'warningcode' => 'errorconflictingbooking',
                'message' => get_string('errorconflictingbooking', 'local_booking', $warninginfo)
            ];
        }

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function is_conflicting_booking_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function update_user_preferences_parameters() {
        return new external_function_parameters(array(
                'preference' => new external_value(PARAM_RAW, 'The preference key', VALUE_DEFAULT),
                'value' => new external_value(PARAM_RAW, 'The value of the preference', VALUE_DEFAULT),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid' => new external_value(PARAM_INT, 'The user id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Set the user preferences
     *
     * @param string $preference The preference key of to be set.
     * @param string $value      The value of the preference to be set.
     * @param int $courseid      The course id.
     * @param int $userid        The user id.
     * @return bool $result      The result of the availability override operation.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function update_user_preferences($preference, $value, $courseid, $userid) {

        // Parameter validation.
        $params = self::validate_parameters(self::update_user_preferences_parameters(), array(
            'preference' => $preference,
            'value'      => $value,
            'courseid' => $courseid,
            'userid'     => $userid,
            )
        );

        $msgdata = ['preference' => $preference, 'value' => $value];
        $warnings = array();
        $result = set_user_preference('local_booking_' . $courseid . '_' . $preference, (gettype($value) == 'boolean' ? intval($value) : $value), $userid);

        if (!$result) {
            \core\notification::WARNING(get_string('bookingsetpreferencesunable', 'local_booking', $msgdata));
        }

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function update_user_preferences_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function update_enrolement_status_parameters() {
        return new external_function_parameters(array(
                'status' => new external_value(PARAM_BOOL, 'The suspension status', VALUE_DEFAULT),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid' => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Suspend or unsuspend a user course enrolement.
     *
     * @param bool $status       The suspend status true/false.
     * @param int $courseid      The course id.
     * @param int $studentid     The student id.
     * @return bool $result      The result of the availability override operation.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function update_enrolement_status($status, $courseid, $studentid) {

        // Parameter validation.
        $params = self::validate_parameters(self::update_enrolement_status_parameters(), array(
            'status' => $status,
            'courseid' => $courseid,
            'userid'  => $studentid,
            )
        );

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        // suspend a student
        $student = new student($subscriber, $studentid);
        $result = $student->suspend($status);
        $warnings = array();

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function update_enrolement_status_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function update_user_group_parameters() {
        return new external_function_parameters(array(
                'group' => new external_value(PARAM_RAW, 'The preference key', VALUE_DEFAULT),
                'ismember' => new external_value(PARAM_BOOL, 'The value of the preference', VALUE_DEFAULT),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid' => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Update user group membership add/remove for the course.
     *
     * @param string $group      The user group.
     * @param bool $ismember       Add/remove to/from group.
     * @param int $courseid      The course id.
     * @param int $studentid     The student id.
     * @return bool $result      The result of the availability override operation.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function update_user_group($group, $ismember, $courseid, $studentid) {

        // Parameter validation.
        $params = self::validate_parameters(self::update_user_group_parameters(), array(
            'group' => $group,
            'ismember' => $ismember,
            'courseid' => $courseid,
            'userid'  => $studentid,
            )
        );

        $warnings = array();

        // add/remove student to group
        $groupid = groups_get_group_by_name($courseid, $group);
        if ($ismember) {
            $result = groups_add_member($groupid, $studentid);
        } else {
            $result = groups_remove_member($groupid, $studentid);
        }

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function update_user_group_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function update_profile_comment_parameters() {
        return new external_function_parameters(array(
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'userid' => new external_value(PARAM_INT, 'The student id', VALUE_DEFAULT),
                'comment' => new external_value(PARAM_RAW, 'The comment text', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Update user group membership add/remove for the course.
     *
     * @param int    $courseid   The course id.
     * @param int    $userid     The user id.
     * @param string $comment    The comment text.
     * @return bool $result      The comment save was successful.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function update_profile_comment(int $courseid, int $userid, string $comment) {

        // Parameter validation.
        $params = self::validate_parameters(self::update_profile_comment_parameters(), array(
            'courseid'=> $courseid,
            'userid'  => $userid,
            'comment' => $comment,
            )
        );

        $warnings = array();

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        // add/remove student to group
        $participant = new participant($subscriber, $userid);
        $result = $participant->update_comment($comment);

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function update_profile_comment_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function save_slots_parameters() {
        // Userid is always current user, so no need to get it from client.
        return new external_function_parameters(
            array('slots' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'starttime' => new external_value(PARAM_INT, 'slot start time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                        'endtime' => new external_value(PARAM_INT, 'slot end time', VALUE_DEFAULT, 0, NULL_NOT_ALLOWED),
                    ), 'slot')
                ),
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'year' => new external_value(PARAM_INT, 'The slot year', VALUE_DEFAULT),
                'week' => new external_value(PARAM_INT, 'The slot week', VALUE_DEFAULT)
            )
        );
    }

    /**
     * Save availability slot.
     *
     * @param array $slots A list of slots to create.
     * @param int $courseid the course id associated with the slot.
     * @param int $year the year in which the slot occur.
     * @param int $week the week in which the slot occur.
     * @return array array of slots created.
     * @since Moodle 2.5
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function save_slots($slots, $courseid, $year, $week) {
        global $USER;

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        // Parameter validation.
        $params = self::validate_parameters(self::save_slots_parameters(), array(
                'slots'     => $slots,
                'courseid'  => $courseid,
                'year'      => $year,
                'week'      => $week)
            );

        $student = $subscriber->get_student($USER->id);
        $warnings = array();

        // add new slots after removing previous ones for the week
        $slots = $student->save_slots($params);

        // activate posting notification
        $existingslots = get_user_preferences('local_booking_' . $courseid . '_postingnotify', '', $student->get_id());
        $slotstonotify = $existingslots . (empty($existingslots) ? '' : ',') . $slots;
        set_user_preference('local_booking_' . $courseid . '_postingnotify', $slotstonotify, $student->get_id());

        if (!empty($slots)) {
            \core\notification::SUCCESS(get_string('slotssavesuccess', 'local_booking'));
        } else {
            \core\notification::ERROR(get_string('slotssaveunable', 'local_booking'));
        }

        return array(
            'result' => !empty($slots),
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function save_slots_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function delete_slots_parameters() {
        // Userid is always current user, so no need to get it from client.
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'The course id', VALUE_DEFAULT),
                'year' => new external_value(PARAM_INT, 'The slot year', VALUE_DEFAULT),
                'week' => new external_value(PARAM_INT, 'The slot week', VALUE_DEFAULT)
            )
        );
    }

    /**
     * Delete availability slot.
     *
     * @param array $events A list of slots to create.
     * @return array array of slots created.
     * @since Moodle 2.5
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function delete_slots($courseid, $year, $week) {
        global $USER;

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        // Parameter validation.
        $params = self::validate_parameters(self::delete_slots_parameters(), array(
                'courseid' => $courseid,
                'year' => $year,
                'week' => $week)
            );

        $student = $subscriber->get_student($USER->id);
        $warnings = array();

        // remove all week's slots for the user to avoid updates
        $result = $student->delete_slots($params);

        if ($result) {
            \core\notification::SUCCESS(get_string('slotsdeletesuccess', 'local_booking'));
        } else {
            \core\notification::ERROR(get_string('slotsdeleteunable', 'local_booking'));
        }

        return array(
            'result' => $result,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function delete_slots_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, get_string('processingresult', 'local_booking')),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_exercise_name_parameters() {
        return new external_function_parameters(
            array(
                'courseid'  => new external_value(PARAM_INT, 'The course id in context', VALUE_DEFAULT),
                'exerciseid'  => new external_value(PARAM_INT, 'The exercise id', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Retrieve the name of a course exercise.
     *
     * @param int $courseid   The course id.
     * @param int $exerciseid The exerciser id.
     * @return array array of slots created.
     * @throws moodle_exception if user doesnt have the permission to create events.
     */
    public static function get_exercise_name($courseid, $exerciseid) {

        // Parameter validation.
        $params = self::validate_parameters(self::get_exercise_name_parameters(), array(
                'courseid' => $courseid,
                'exerciseid' => $exerciseid,
                )
            );

        // set the subscriber object
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        $warnings = array();

        return array('exercisename' => $subscriber->get_exercise_name($exerciseid), 'warnings' => $warnings);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description.
     * @since Moodle 2.5
     */
    public static function get_exercise_name_returns() {
        return new external_single_structure(array(
            'exercisename' => new external_value(PARAM_RAW, 'The exercise name', VALUE_DEFAULT),
            'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function submit_create_update_form_parameters() {
        return new external_function_parameters(
            [
                'formargs' => new external_value(PARAM_RAW, 'The arguments from the logentry form'),
                'formdata' => new external_value(PARAM_RAW, 'The data from the logentry form'),
            ]
        );
    }

    /**
     * Handles the logbook entry form submission.
     *
     * @param string $formdata The logentry form data in a URI encoded param string
     * @return array The created or modified logbook entry
     * @throws moodle_exception
     */
    public static function submit_create_update_form($formargs, $formdata) {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::submit_create_update_form_parameters(), ['formargs' => $formargs, 'formdata' => $formdata]);
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
        $subscriber = self::get_course_subscriber_context('/local/booking/', $courseid);

        $formoptions = [
            'context'    => $subscriber->get_context(),
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

                if (property_exists($validateddata, 'flighttype') && $validateddata->flighttype != 'solo') {
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
            $entry = new logentry_view($subscriber->get_context(), $courseid, $viewdata);
            $output = $entry->get_exported_data();

            // send student notification for new logbook entries
            if (!$editing) {
                (new notification())->send_logentry_notification($logentry);
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
     * @return external_description.
     */
    public static function  submit_create_update_form_returns() {
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
