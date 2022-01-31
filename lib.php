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

defined('MOODLE_INTERNAL') || die();

use local_booking\external\bookings_exporter;
use local_booking\external\assigned_students_exporter;
use local_booking\external\instructor_participation_exporter;
use local_booking\external\logbook_exporter;
use local_booking\external\logentry_exporter;
use local_booking\local\session\entities\booking;
use local_booking\local\slot\entities\slot;
use local_booking\local\message\notification;
use local_booking\local\logbook\forms\create as create_logentry_form;
use local_booking\local\logbook\forms\create as update_logentry_form;
use local_booking\external\week_exporter;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\logbook\entities\logentry;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;

/**
 * LOCAL_BOOKING_RECENCYWEIGHT - constant value for session recency weight multipler
 */
define('LOCAL_BOOKING_RECENCYWEIGHT', 10);
/**
 * LOCAL_BOOKING_SLOTSWEIGHT - constant value for session availability slots weight multipler
 */
define('LOCAL_BOOKING_SLOTSWEIGHT', 50);
/**
 * LOCAL_BOOKING_ACTIVITYWEIGHT - constant value for course activity weight multipler
 */
define('LOCAL_BOOKING_ACTIVITYWEIGHT', 1);
/**
 * LOCAL_BOOKING_COMPLETIONWEIGHT - constant value for lesson completion weight multipler
 */
define('LOCAL_BOOKING_COMPLETIONWEIGHT', 10);
/**
 * LOCAL_BOOKING_MINLANES - constant value for minimum number of lanes in a weekday in the availability view
 */
define('LOCAL_BOOKING_MINLANES', 4);
/**
 * LOCAL_BOOKING_MAXLANES - constant value for maximum number of student slots shown in parallel a day
 */
define('LOCAL_BOOKING_MAXLANES', 20);
/**
 * LOCAL_BOOKING_FIRSTSLOT - default value of the first slot of the day
 */
define('LOCAL_BOOKING_FIRSTSLOT', 8);
/**
 * LOCAL_BOOKING_LASTSLOT - default value of the first slot of the day
 */
define('LOCAL_BOOKING_LASTSLOT', 23);
/**
 * LOCAL_BOOKING_WEEKSLOOKAHEAD - default value of the first slot of the day
 */
define('LOCAL_BOOKING_WEEKSLOOKAHEAD', 5);
/**
 * LOCAL_BOOKING_PASTDATACUTOFF - default value of days in processing past data (i.e. past grades)
 */
define('LOCAL_BOOKING_PASTDATACUTOFF', 730); // 365
/**
 * LOCAL_BOOKING_ONHOLDGROUP - constant string value for students placed on-hold for group quering purposes
 */
define('LOCAL_BOOKING_ONHOLDGROUP', 'OnHold');
/**
 * LOCAL_BOOKING_KEEPACTIVE - constant string value for students to stay active even if they match on-hold criteria
 */
define('LOCAL_BOOKING_KEEPACTIVE', 'Keep Active');
/**
 * LOCAL_BOOKING_INACTIVEGROUP - constant string value for inactive instructors for group quering purposes
 */
define('LOCAL_BOOKING_INACTIVEGROUP', 'Inactive Instructors');
/**
 * LOCAL_BOOKING_GRADUATESGROUP - constant string value for graduated students for group quering purposes
 */
define('LOCAL_BOOKING_GRADUATESGROUP', 'Graduates');
/**
 * LOCAL_BOOKING_SLOTCOLOR - constant for standard slot color
 */
define('LOCAL_BOOKING_SLOTCOLOR', '#00e676');
/**
 * LOCAL_BOOKING_INSTRUCTORROLE - constant for instructor role shortname
 */
define('LOCAL_BOOKING_INSTRUCTORROLE', 'instructor');
/**
 * LOCAL_BOOKING_SENIORINSTRUCTORROLE - constant for instructor role shortname
 */
define('LOCAL_BOOKING_SENIORINSTRUCTORROLE', 'seniorinstructor');
/**
 * LOCAL_BOOKING_FLIGHTTRAININGMANAGERROLE - constant for instructor role shortname
 */
define('LOCAL_BOOKING_FLIGHTTRAININGMANAGERROLE', 'flighttrainingmanager');

/**
 * This function extends the navigation with the booking item
 *
 * @param global_navigation $navigation The global navigation node to extend
 */

function local_booking_extend_navigation(global_navigation $navigation) {
    global $COURSE, $USER;

    $courseid = $COURSE->id;
    $context = context_course::instance($courseid);

    // define subscriber globally
    if (empty($COURSE->subscriber))
        $COURSE->subscriber = new subscriber($courseid);

    if ($COURSE->subscriber->subscribed) {
        // for checking if the participant is active
        $participant = $COURSE->subscriber->get_active_participant($USER->id);

        // Add student log book navigation node for active participants
        if ($participant->is_active()) {
            if (has_capability('local/booking:logbookview', $context)) {
                $node = $navigation->find('logbook', navigation_node::NODETYPE_LEAF);
                if (!$node && $courseid!==SITEID) {
                    // form URL and parameters
                    $params = array('courseid'=>$courseid);
                    $url = new moodle_url('/local/booking/logbook.php', $params);

                    $parent = $navigation->find($courseid, navigation_node::TYPE_COURSE);
                    $node = navigation_node::create(get_string('logbook', 'local_booking'), $url);
                    $node->key = 'logbook';
                    $node->type = navigation_node::NODETYPE_LEAF;
                    $node->forceopen = true;
                    $node->icon = new  pix_icon('logbook', '', 'local_booking');
                    $parent->add_node($node);
                }
            }
        }

        // Add student availability navigation node for active participants
        if ($participant->is_active()) {
            if (has_capability('local/booking:availabilityview', $context)) {
                $activeparticipant = true;
                $nodename = '';
                $node = $navigation->find('availability', navigation_node::NODETYPE_LEAF);
                if (!$node && $courseid!==SITEID) {
                    // form URL and parameters
                    $params = array('courseid'=>$courseid);
                    // view all capability for instructors
                    if (has_capability('local/booking:view', $context)) {
                        $nodename = get_string('availabilityinst', 'local_booking');
                    } else {
                        $student = $COURSE->subscriber->get_active_student($USER->id);
                        $params['time'] = !empty($student) ? $student->get_next_allowed_session_date()->getTimestamp() : time();
                        // $params['action'] = 'post';
                        $nodename = get_string('availability', 'local_booking');
                        $activeparticipant = !empty($student);
                    }

                    if ($activeparticipant) {
                        $url = new moodle_url('/local/booking/availability.php', $params);

                        $parent = $navigation->find($courseid, navigation_node::TYPE_COURSE);
                        $node = navigation_node::create($nodename, $url);
                        $node->key = 'availability';
                        $node->type = navigation_node::NODETYPE_LEAF;
                        $node->forceopen = true;
                        $node->icon = new  pix_icon('availability', '', 'local_booking');
                        $parent->add_node($node);
                    }
                }
            }
        }

        // Add instructor booking navigation node
        if ($participant->is_active()) {
            if (has_capability('local/booking:view', $context)) {
                $node = $navigation->find('bookings', navigation_node::NODETYPE_LEAF);
                if (!$node && $courseid!==SITEID) {
                    $parent = $navigation->find($courseid, navigation_node::TYPE_COURSE);
                    $node = navigation_node::create(get_string('bookings', 'local_booking'), new moodle_url('/local/booking/view.php', array('courseid'=>$courseid)));
                    $node->key = 'bookings';
                    $node->type = navigation_node::NODETYPE_LEAF;
                    $node->forceopen = true;
                    $node->icon = new  pix_icon('booking', '', 'local_booking');
                    $parent->add_node($node);
                }
            }
        }
    }
}

/**
 * Fragment to add a new category.
 *
 * @param array $args The fragment arguments.
 * @return string The rendered mform fragment.
 */
function local_booking_output_fragment_logentry_form($args) {
    global $USER;

    $formdata = [];
    $logentryid = isset($args['logentryid']) ? clean_param($args['logentryid'], PARAM_INT) : null;
    if (!empty($args['formdata'])) {
        parse_str($args['formdata'], $formdata);
    }

    $context = \context_user::instance($USER->id);

    if (WS_SERVER) {
        // Request via WS, ignore sesskey checks in form library.
        $USER->ignoresesskey = true;
    }

    $courseid = $args['courseid'] ?: 0;
    $exerciseid = $args['exerciseid'] ?: 0;
    $userid = $args['userid'] ?: 0;
    $flightdate = $args['flightdate'] ?: 0;

    $formoptions = [
        'context'   => $context,
        'courseid'  => $courseid,
        'exerciseid'=> $exerciseid,
        'userid' => $userid,
        'flightdate' => $flightdate,
    ];

    $logbook = new logbook($courseid, $userid);

    if (!empty($logentryid)) {
        $logentry = $logbook->get_logentry($logentryid);
        $formoptions['logentry'] = $logentry;
        $formdata = $logentry->__toArray(true);
        $data = $formdata;
        $data['flightdate'] = $logentry->get_flightdate();
        $data['p1pirep'] = $logentry->get_pirep();
        $data['landingsp1day'] = $logentry->get_landingsday();
        $data['landingsp1night'] = $logentry->get_landingsnight();
        if ($logentry->get_flighttype() == 'check')
            $data['passfail'] = !empty($logentry->get_checkpilottime()) || !empty($logentry->get_picustime()) ? 'pass' : 'fail';
        $mform = new update_logentry_form(null, $formoptions, 'post', '', null, true, $formdata);
    } else {
        $logentry = $logbook->create_logentry();
        $formoptions['logentry'] = $logentry;
        $mform = new create_logentry_form(null, $formoptions, 'post', '', null, true, $formdata);
        // copy over additional data needed for setting the form
        $data['courseid'] = $courseid;
        $data['userid'] = $userid;
        $data['exerciseid'] = $exerciseid;
    }

    // Add to form data setup arguments
    $mform->set_data($data);

    if (!empty($args['formdata'])) {
        // Show errors if data was received.
        $mform->is_validated();
        // save log book data
    }

    return $mform->render();
}

/**
 * Get icon mapping for font-awesome.
 */
function local_booking_get_fontawesome_icon_map() {
    return [
        'local_booking:availability' => 'fa-calendar-plus-o',
        'local_booking:booking' => 'fa-plane',
        'local_booking:check' => 'fa-check',
        'local_booking:copy' => 'fa-copy',
        'local_booking:info-circle' => 'fa-info-circle',
        'local_booking:logbook' => 'fa-address-book-o',
        'local_booking:paste' => 'fa-paste',
        'local_booking:pencil-square' => 'fa-pencil-square',
        'local_booking:plane' => 'fa-plane',
        'local_booking:plus-square' => 'fa-plus-square',
        'local_booking:question-circle' => 'fa-question-circle',
        'local_booking:save' => 'fa-save',
        'local_booking:subscribed' => 'fa-envelope-o',
        'local_booking:trash' => 'fa-trash',
        'local_booking:unsubscribed' => 'fa-envelope-open-o',
        'local_booking:user' => 'fa-user',
        'local_booking:window-close' => 'fa-window-close',
    ];
}

/**
 * Get the calendar view output.
 *
 * @param   \calendar_information $calendar The calendar being represented
 * @param   array   $actiondata The action type and associated data
 * @param   string  $skipevents Whether to load the events or not
 * @return  array[array, string]
 */
function get_weekly_view(\calendar_information $calendar, $actiondata, $view = 'user') {
    global $PAGE;

    $renderer = $PAGE->get_renderer('core_calendar');
    $type = \core_calendar\type_factory::get_calendar_instance();

    $related = [
        'type' => $type,
        'calendar' => $calendar
    ];

    $week = new week_exporter($actiondata, $view, $related);
    $data = $week->export($renderer);
    $data->viewingmonth = true;
    $template = 'local_booking/calendar_week';

    return [$data, $template];
}

/**
 * Get the student's progression view output.
 *
 * @param   int     $courseid the associated course.
 * @return  array[array, string]
 */
function get_bookings_view($courseid, $sorttype = '') {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/bookings';
    $data = [
        'courseid'=>$courseid,
        'view'      => 'sessions',
        'sorttype'  => $sorttype
    ];
    $related = [
        'context'   => \context_course::instance($courseid),
    ];

    $bookings = new bookings_exporter($data, $related);
    $data = $bookings->export($renderer);

    return [$data, $template];
}

/**
 * Get the booking confirmation output view.
 *
 * @param   int     $courseid the associated course.
 * @param   int     $studentid the student user id being confirmed.
 * @return  array[array, string]
 */
function get_booking_confirm_view($courseid, $studentid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/booking';
    $data = [
        'courseid'=>$courseid,
        'view'      => 'confirm'
    ];
    $related = [
        'context'   => \context_course::instance($courseid),
    ];

    $bookings = new bookings_exporter($data, $related, $studentid);
    $data = $bookings->export($renderer);

    return [$data, $template];
}

/**
 * Get the user's log book view output.
 *
 * @param   int     $courseid the associated course.
 * @param   int     $userid the logbook owner user id.
 * @param   string  $templateformat the logbook format.
 * @param   bool    $loadentries whether to load logbook entries or not.
 * @return  array[array, string]
 */
function get_logbook_view($courseid, $userid, $templateformat) {
    global $PAGE, $COURSE;

    $renderer = $PAGE->get_renderer('local_booking');

    if (empty($templateformat)) {
        $templateformat = get_user_preferences('local_booking_logbookformat', 'std');
    } else {
        $setformat = get_user_preferences('local_booking_logbookformat', 'std');
        if ($templateformat != $setformat)
            set_user_preferences(array('local_booking_logbookformat'=>$templateformat));
    }

    // indicate whether to get logbook entries for all courses
    $template = 'local_booking/logbook_' . $templateformat;

    // get summary information (not requested by the webservice)
    $pilot = $COURSE->subscriber->get_active_participant($userid);
    $logbook = $pilot->get_logbook(true, $templateformat == 'easa');
    $totals = (array) $logbook->get_summary(true);
    $data = [
        'courseid'      => $courseid,
        'userid'        => $userid,
        'username'      => $pilot->get_fullname($userid),
        'courseshortname' => $PAGE->course->shortname,
        'logbook'       => $logbook,
        'isstudent'     => $pilot->is_student(),
        'easaformaturl' => $PAGE->url . '&format=easa',
        'stdformaturl'  => $PAGE->url . '&format=std',
        'shortdate'     => $templateformat == 'easa'
    ];

    $logbook = new logbook_exporter($data + $totals, ['context' => \context_course::instance($courseid)]);
    $data = $logbook->export($renderer);

    return [$data, $template];
}

/**
 * Get the logbook entry view output.
 *
 * @param   int      $courseid       The course id
 * @param   int      $userid         The owner of the logentry
 * @param   array    $formdata       Newly created or edited logentry form data
 * @param   logentry $logentry       Newly created or edited logentry
 * @return  array[array, string]     Exporter output
 */
function get_logentry_view(int $courseid, int $userid, array $formdata = null) {
    global $PAGE, $COURSE;

    $context = \context_course::instance($courseid);
    $renderer = $PAGE->get_renderer('local_booking');

    if (!empty($formdata)) {
        $data = $formdata;
    } else {
        // semi populated logentry from PIREP lookup
        $data = [
            'courseid'  => $courseid,
            'userid'  => $userid,
            'exerciseid'  => 0,
        ];
    }

    // add training type to the data sent to the exporter
    $data['trainingtype'] = $COURSE->subscriber->trainingtype;
    $data['isstudent'] = $COURSE->subscriber->get_active_participant($userid)->is_student();
    $data['courseshortname'] = $PAGE->course->shortname;

    $logentryexp = new logentry_exporter($data, ['context' => $context]);
    $data = $logentryexp->export($renderer);
    $template = 'local_booking/modal_logentry_form';

    return [$data, $template];
}

/**
 * Get instructor assigned students view output.
 *
 * @param   int     $courseid the associated course.
 * @return  array[array, string]
 */
function get_students_view($courseid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/my_students';
    $data = [
        'courseid'  => $courseid,
    ];

    $students = new assigned_students_exporter($data, ['context' => \context_course::instance($courseid)]);
    $data = $students->export($renderer);

    return [$data, $template];
}

/**
 * Get instructor participation view output.
 *
 * @param   int     $courseid the associated course.
 * @return  array[array, string]
 */
function get_participation_view($courseid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/participation';
    $data = [
        'courseid'  => $courseid,
    ];

    $participation = new  instructor_participation_exporter($data, ['context' => \context_course::instance($courseid)]);
    $data = $participation->export($renderer);

    return [$data, $template];
}

/**
 * Save a new booking for a student by the instructor.
 *
 * @param   array   $params an array containing the webservice parameters
 * @return  bool
 */
function save_booking($params) {
    global $USER;

    $courseid = $params['courseid'] ?: SITEID;
    require_login($courseid, false);

    $result = false;
    $slottobook = $params['bookedslot'];
    $courseid   = $params['courseid'];
    $exerciseid = $params['exerciseid'];
    $studentid  = $params['studentid'];
    $instructorid = $USER->id;

    // add a new tentatively booked slot for the student.
    $sessiondata = [
        'exercise'  => subscriber::get_exercise_name($exerciseid),
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
        set_user_preference('local_booking_availabilityoverride', false, $studentid);

        // send emails to both student and instructor
        $sessionstart = new DateTime('@' . $slottobook['starttime']);
        $sessionend = new DateTime('@' . $slottobook['endtime']);
        $message = new notification();
        if ($message->send_booking_notification($studentid, $exerciseid, $sessionstart, $sessionend)) {
            $message->send_instructor_confirmation($studentid, $exerciseid, $sessionstart, $sessionend);
        }
        $sessiondata['sessiondate'] = $sessionstart->format('D M j\, H:i');
        $sessiondata['studentname'] = student::get_fullname($studentid);
        \core\notification::success(get_string('bookingsavesuccess', 'local_booking', $sessiondata));
    } else {
        \core\notification::warning(get_string('bookingsaveunable', 'local_booking'));
    }

    return $result;
}

/**
 * Confirm a booking for of a tentative session
 *
 * @param   int   $couseid      The course id for this booking.
 * @param   int   $instructorid The instructor id that booked the session.
 * @param   int   $studentid    The student id being of the confirmed session.
 * @param   int   $exerciseid   The exercise id being confirmed.
 * @return  array An array containing the result and confirmation message string.
 */
function confirm_booking($courseid, $instructorid, $studentid, $exerciseid) {
    $result = -1;
    $time = time();
    $week = (int) date('W', time());

    // Get the student slot
    $booking = new booking(0, $courseid, $studentid, $exerciseid);
    $booking->load();

    if (!empty($booking->get_id())) {
        // update the booking by the instructor.
        $sessiondatetime = (new DateTime('@' . ($booking->get_slot())->get_starttime()))->format('D M j\, H:i');
        $strdata = [
            'exercise'  => subscriber::get_exercise_name($exerciseid),
            'instructor'=> student::get_fullname($instructorid),
            'status'    => ucwords(get_string('statusbooked', 'local_booking')),
            'sessiondate'=> $sessiondatetime
        ];
        if ($booking->confirm(get_string('bookingconfirmmsg', 'local_booking', $strdata))) {
            // notify the instructor of the student's confirmation
            $message = new notification();
            $result = $message->send_instructor_notification($courseid, $studentid, $exerciseid, $sessiondatetime, $instructorid);
        }
        $time = ($booking->get_slot())->get_starttime();
        $week = ($booking->get_slot())->get_week();
    }

    if ($result == 1) {
        \core\notification::success(get_string('bookingconfirmsuccess', 'local_booking', $strdata));
    } elseif ($result == 0) {
        \core\notification::ERROR(get_string('bookingconfirmunable', 'local_booking'));
    } elseif ($result == -1) {
        \core\notification::success(get_string('nobookingtoconfirm', 'local_booking'));
    }

    return [$result, $time, $week];
}

/**
 * Cancel an instructor's existing booking
 *
 * @param   int   $booking  The booking id being cancelled.
 * @return  bool  $resut    The cancellation result.
 */
function cancel_booking($bookingid, $comment) {
    global $COURSE;
    $msg = '';

    // get the booking to be deleted
    if (!empty($bookingid)) {
        $booking = new booking($bookingid);
        $booking->load();
        $courseid = $booking->get_courseid() ?: $COURSE->id;
        $sessiondate = new DateTime('@' . ($booking->get_slot())->get_starttime());

        require_login($courseid, false);

        $result = $booking->delete();

        $cancellationmsg = [
            'studentname' => student::get_fullname($booking->get_studentid()),
            'sessiondate' => $sessiondate->format('D M j\, H:i'),
        ];
        $msg = get_string('bookingcanceledsuccess', 'local_booking', $cancellationmsg);

    } else {
        $msg = get_string('bookingcancelednotfound', 'local_booking');
    }

    if ($result) {
        // send email notification to the student of the booking cancellation
        $message = new notification();
        if ($message->send_session_cancellation($booking->get_studentid(), $booking->get_exerciseid(), $sessiondate, $comment)) {
            \core\notification::success($msg);
        }
    } else {
        \core\notification::warning($msg ?: get_string('bookingcanceledunable', 'local_booking'));
    }

    return $result;
}

/**
 * Overrides wait time restriction for a student, where the
 * restriction is waived until the instructor books a session
 * with that student.
 *
 * @param   int   $studentid  The student id being waived.
 * @return  bool  $resut      The override operation result.
 */
function override_availability_restriction($studentid) {
    $result = false;

    // Get user preference to show local time column
    $result = set_user_preference('local_booking_availabilityoverride', true, $studentid);

    if ($result) {
        \core\notification::success(get_string('bookingavailabilityoverridesuccess', 'local_booking'));
    } else {
        \core\notification::warning(get_string('bookingavailabilityoverrideunable', 'local_booking'));
    }

    return $result;
}

/**
 * Respond to submission graded events
 *
 */
function process_submission_graded_event($courseid, $studentid, $exerciseid) {
    $booking = new booking(0, $courseid, $studentid, $exerciseid);
    $booking->load();
    // update the booking status from active to inactive
    $booking->deactivate();
}

/**
 * Return the days of the week where $date falls in.
 *
 * @return array array of days
 */
function get_week_days($date) {

    $days = [];
    // Calculate which day number is the first day of the week.
    $type = \core_calendar\type_factory::get_calendar_instance();
    $daysinweek = count($type->get_weekdays());
    $week_start = get_week_start($date);

    // add remaining days of the week
    for ($i = 0; $i < $daysinweek; $i++) {
        $days[] = $type->timestamp_to_date_array(date_timestamp_get($week_start), 0);
        date_add($week_start, date_interval_create_from_date_string("1 days"));
    }

    return $days;
}

/**
 * Return the days of the week where $date falls in.
 *
 * @return DateTime array of days
 */
function get_week_start($date) {
    $week_start_date = new DateTime();
    date_timestamp_set($week_start_date, $date[0]);
    $week_start_date->setISODate($date['year'], strftime('%W', $date[0]))->format('Y-m-d');
    return $week_start_date;
}

/**
 * Returns a random color.
 *
 * @return string   hex color
 */
function random_color_part() {
    return str_pad(dechex(mt_rand(20, 235)), 2, '0', STR_PAD_LEFT);
}

/**
 * Returns a hash of the random color for a student slot in group view.
 *
 * @return string   hex color
 */
function random_color() {
    return random_color_part() . random_color_part() . random_color_part();
}

/**
 * Returns the settings from config.xml
 *
 * @return mixed  The requested setting value.
 */
function get_booking_config(string $key, $associative = null) {
    global $CFG;
    $configfile = $CFG->dirroot . '/local/booking/config.json';
    $config = null;
    if (file_exists($configfile)) {
        $jsoncontent = file_get_contents($configfile);
        $configdata = json_decode($jsoncontent, $associative);
        $config = $associative ? $configdata[$key] : $configdata->{$key};
    } else {
        var_dump(get_string('configmissing', 'local_booking', $configfile));
    }
    return $config;
}
