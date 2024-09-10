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
 * Class containing Moodle callback functions and plugin constants
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/group/lib.php');

use local_booking\local\logbook\form\create as create_logentry_form;
use local_booking\local\logbook\form\create as update_logentry_form;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\subscriber\entities\subscriber;

/**
 * LOCAL_BOOKING_DASHBOARDPAGESIZE - constant value for the instructor dashboard page size
 */
define('LOCAL_BOOKING_DASHBOARDPAGESIZE', 50);
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
 * LOCAL_BOOKING_WEEKSLOOKAHEAD - default value of the first slot of the day
 */
define('LOCAL_BOOKING_WEEKSLOOKAHEAD', 5);
/**
 * LOCAL_BOOKING_OVERDUE_PERIOD - days from posting wait period to sent student inactivity warning
 */
define('LOCAL_BOOKING_OVERDUE_PERIOD', 10);
/**
 * LOCAL_BOOKING_PASTDATACUTOFF - default value of days in processing past data (i.e. past grades) - (3 years)
 */
define('LOCAL_BOOKING_PASTDATACUTOFF', 1095);
/**
 * LOCAL_BOOKING_NOSHOWPERIOD - constant for the period within which student no-shows are evaluated
 */
define('LOCAL_BOOKING_NOSHOWPERIOD', 90);
/**
 * LOCAL_BOOKING_NOSHOWSUSPENSIONPERIOD - constant for the period of suspension due to no-show
 */
define('LOCAL_BOOKING_NOSHOWSUSPENSIONPERIOD', 1);
/**
 * LOCAL_BOOKING_ONHOLDGROUP - constant string value for students placed on-hold for group quering purposes
 */
define('LOCAL_BOOKING_ONHOLDGROUP', 'OnHold');
/**
 * LOCAL_BOOKING_KEEPACTIVEGROUP - constant string value for students to stay active even if they match on-hold criteria
 */
define('LOCAL_BOOKING_KEEPACTIVEGROUP', 'Keep Active');
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
 * LOCAL_BOOKING_INSTRUCTORROLE - constant for Instructor role shortname
 */
define('LOCAL_BOOKING_INSTRUCTORROLE', 'instructor');
/**
 * LOCAL_BOOKING_SENIORINSTRUCTORROLE - constant for the Senior Instructor role shortname
 */
define('LOCAL_BOOKING_SENIORINSTRUCTORROLE', 'seniorinstructor');
/**
 * LOCAL_BOOKING_FLIGHTTRAININGMANAGERROLE - constant for the Flight Training Manager role shortname
 */
define('LOCAL_BOOKING_FLIGHTTRAININGMANAGERROLE', 'flighttrainingmanager');
/**
 * LOCAL_BOOKING_EXAMINERROLE - constant for the Examiner role shortname
 */
define('LOCAL_BOOKING_EXAMINERROLE', 'examiner');
/**
 * LOCAL_BOOKING_FAILINGPERCENTAGE - constant for the percentage of failing grade for rubric assessments
 */
define('LOCAL_BOOKING_FAILINGPERCENTAGE', .33);
/**
 * LOCAL_BOOKING_PASTDATACUTOFFDAYS - Past cutoff date (timestamp) for data retrieval
 */
define('LOCAL_BOOKING_PASTDATACUTOFFDAYS', LOCAL_BOOKING_PASTDATACUTOFF * 60 * 60 * 24);
/**
 * LOCAL_BOOKING_SLOTCOLORS - constant array for slot colors for students availability grid
 */
define('LOCAL_BOOKING_SLOTCOLORS', [
    "red"         => "#d50000",
    "green"       => "#689f38",
    "yellow"      => "#ffeb3b",
    "deep orange" => "#ff3d00",
    "lime"        => "#aeea00",
    "dark green"  => "#1b5e20",
    "blue"        => "#2962ff",
    "light blue"  => "#0091ea",
    "orange"      => "#ff6d00",
    "deep purple" => "#9fa8da",
    "pink"        => "#fce4ec",
    "light green" => "#00e676",
    "dark blue"   => "#0d47a1",
    "teal"        => "#00897b",
    "light purple"=> "#c5cae9",
    "brown"       => "#5d4037",
    "light indigo"=> "#dcedc8",
    "light cyan"  => "#b2ebf2",
    "dark purple" => "#4a148c",
    "light yellow"=> "#ffff00"
]);

/**
 * This function extends the navigation with the booking item
 *
 * @param global_navigation $navigation The global navigation node to extend
 */
function local_booking_extend_navigation_course(navigation_node $navigation) {
    global $COURSE, $USER;

    $courseid = $COURSE->id;
    $context = context_course::instance($courseid);

    // define subscriber globally
    if (empty($COURSE->subscriber))
        $COURSE->subscriber = new subscriber($courseid);

    if ($COURSE->subscriber->subscribed) {
        // for checking if the participant is active
        $participant = $COURSE->subscriber->get_participant($USER->id);

        // Add instructor dashboard node
        if (has_capability('local/booking:view', $context)) {
            $node = $navigation->find('bookings', navigation_node::TYPE_SETTING);
            if (!$node && $courseid!==SITEID) {
                $url = new moodle_url('/local/booking/view.php', array('courseid'=>$courseid));
                $node = navigation_node::create(get_string('bookings', 'local_booking'), $url,
                navigation_node::TYPE_SETTING,
                null,
                null,
                new pix_icon('booking', '', 'local_booking'));
                $navigation->add_node($node);
            }
        }

        // Add student availability navigation and students progression nodes for active participants
        if ($participant->is_active()) {
            if (has_capability('local/booking:availabilityview', $context)) {
                $activeparticipant = true;
                $nodename = '';
                $node = $navigation->find('availability', navigation_node::TYPE_SETTING);

                if (!$node && $courseid!==SITEID) {

                    // form URL and parameters
                    $params = array('courseid'=>$courseid);

                    // get proper link for instructors
                    if (has_capability('local/booking:view', $context)) {
                        $nodename = get_string('availabilityinst', 'local_booking');
                    } else {
                        $student = $COURSE->subscriber->get_student($USER->id);
                        $params['time'] = !empty($student) ? $student->get_next_allowed_session_date()->getTimestamp() : time();
                        // $params['action'] = 'post';
                        $nodename = get_string('availability', 'local_booking');
                        $activeparticipant = !empty($student);
                    }

                    // add the node to active participants
                    if ($activeparticipant) {
                        $url = new moodle_url('/local/booking/availability.php', $params);
                        $node = navigation_node::create($nodename, $url,
                        navigation_node::TYPE_SETTING,
                        null,
                        null,
                        new pix_icon('availability', '', 'local_booking'));
                        $navigation->add_node($node);
                    }
                }
                // show for students only
                if (!$participant->is_instructor()) {
                    $node = $navigation->find('progression', navigation_node::TYPE_SETTING);
                    if (!$node && $courseid!==SITEID) {
                        $url = new moodle_url('/local/booking/progression.php', array('courseid'=>$courseid));
                        $node = navigation_node::create(get_string('bookingprogression', 'local_booking'), $url,
                        navigation_node::TYPE_SETTING,
                        null,
                        null,
                        new pix_icon('progression', '', 'local_booking'));
                        $navigation->add_node($node);
                    }
                }
            }
        }

        // Add student log book navigation node for active participants
        if ($participant->is_active()) {
            if (has_capability('local/booking:logbookview', $context)) {
                $node = $navigation->find('logbook', navigation_node::TYPE_SETTING);
                if (!$node && $courseid!==SITEID) {
                    // form URL and parameters
                    $params = array('courseid'=>$courseid, 'userid'=>$USER->id);
                    $url = new moodle_url('/local/booking/logbook.php', $params);
                    $node = navigation_node::create(ucfirst(get_string('logbookmy', 'local_booking')), $url,
                    navigation_node::TYPE_SETTING,
                    null,
                    null,
                    new pix_icon('logbookmy', '', 'local_booking'));
                    $navigation->add_node($node);
                }
            }
        }
    }
}

/**
 * Fragment to add a new logentry.
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
    $sessionid = $args['sessionid'] ?: 0;
    $userid = $args['userid'] ?: 0;
    $flightdate = $args['flightdate'] ?: 0;

    $formoptions = [
        'context'   => $context,
        'courseid'  => $courseid,
        'exerciseid'=> $exerciseid,
        'sessionid'=> $sessionid,
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
        $data['p1pirep'] = $logentry->get_pirep() ?: '';
        $data['landingsp1day'] = $logentry->get_landingsday();
        $data['landingsp1night'] = $logentry->get_landingsnight();
        if ($logentry->get_flighttype() == 'check')
            $data['passfail'] = !empty($logentry->get_checkpilottime()) || !empty($logentry->get_picustime()) ? 'pass' : 'fail';
        $mform = new update_logentry_form(null, $formoptions, 'post', '', null, true, $formdata);
    } else {
        $logentry = $logbook->create_logentry();
        $formoptions['logentry'] = $logentry;
        $formoptions['exerciseid'] = $exerciseid;
        $formoptions['sessionid'] = $sessionid;
        $mform = new create_logentry_form(null, $formoptions, 'post', '', null, true, $formdata);
        // copy over additional data needed for setting the form
        $data['courseid'] = $courseid;
        $data['userid'] = $userid;
        $data['exerciseid'] = $exerciseid;
        $data['sessionid'] = $sessionid;
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
        'local_booking:availability'    => 'fa-calendar-plus-o',
        'local_booking:book'            => 'fa-plane',
        'local_booking:booking'         => 'fa-plane',
        'local_booking:check'           => 'fa-check',
        'local_booking:copy'            => 'fa-copy',
        'local_booking:download'        => 'fa-download',
        'local_booking:grade'           => 'fa-pencil-square',
        'local_booking:graduate'        => 'fa-graduation-cap',
        'local_booking:info-circle'     => 'fa-info-circle',
        'local_booking:check-circle-o'  => 'fa-check-circle-o',
        'local_booking:logbook'         => 'fa-address-book-o',
        'local_booking:noslot'          => 'fa-calendar-times-o',
        'local_booking:paste'           => 'fa-paste',
        'local_booking:plus-square'     => 'fa-plus-square',
        'local_booking:progression'     => 'fa-tasks',
        'local_booking:question-circle' => 'fa-question-circle',
        'local_booking:save'            => 'fa-save',
        'local_booking:subscribed'      => 'fa-envelope-o',
        'local_booking:trash'           => 'fa-trash',
        'local_booking:unsubscribed'    => 'fa-envelope-open-o',
        'local_booking:user'            => 'fa-user',
        'local_booking:user-times'      => 'fa-user-times',
        'local_booking:window-close'    => 'fa-window-close',
    ];
}
