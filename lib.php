<?php

/**
 * Booking Plugin
 *
 * @package    local_booking
 * @author     Mustafa Hajjar
 * @copyright  BAVirtual.co.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \local_booking\external\progression_exporter;

/**
 * Process user  table name.
 */
const DB_USER = 'user';

/**
 * Process assign table name.
 */
const DB_ASSIGN = 'assign';

/**
 * Process course modules table name.
 */
const DB_COURSE_MODULES = 'course_modules';


/**
 * Get the calendar view output.
 *
 * @param   \calendar_information $calendar The calendar being represented
 * @param   string  $view The type of calendar to have displayed
 * @param   bool    $includenavigation Whether to include navigation
 * @param   bool    $skipevents Whether to load the events or not
 * @param   int     $lookahead Overwrites site and users's lookahead setting.
 * @return  array[array, string]
 */
function get_progression_view($courseid, $categoryid) {
    global $PAGE;

    $renderer = $PAGE->get_renderer('local_booking');

    $template = 'local_booking/progress_detailed';
    $data = [
        'courseid' => $courseid,
        'categoryid' => $categoryid,
    ];

    $progression = new progression_exporter($data, ['context' => \context_system::instance()]);
    $data = $progression->export($renderer);

    return [$data, $template];
}

/**
 * Returns full username
 *
 * @return string  The full BAV username (first, last, and BAWID)
 */
function get_fullusername($studentid) {
    global $DB;

    // Get the student's grades
    $sql = 'SELECT ' . $DB->sql_concat('u.firstname', '" "',
                'u.lastname', '" "', 'u.alternatename') . ' AS username
            FROM {' . DB_USER . '} u
            WHERE u.id = ' . $studentid;

    return $DB->get_record_sql($sql)->username;
}

/**
 * Returns exercise assignment name
 *
 * @return string  The BAV exercise name.
 */
function get_exercise_name($exerciseid) {
    global $DB;

    // Get the student's grades
    $sql = 'SELECT a.name AS exercisename
            FROM {' . DB_ASSIGN . '} a
            INNER JOIN {' . DB_COURSE_MODULES . '} cm on a.id = cm.instance
            WHERE cm.id = ' . $exerciseid;

    return $DB->get_record_sql($sql)->exercisename;
}

/**
 * Returns course id of the passed course
 *
 * @return string  The BAV exercise name.
 */
function get_course_id($exerciseid) {
    global $DB;

    // Get the student's grades
    $sql = 'SELECT cm.course AS courseid
            FROM {' . DB_COURSE_MODULES . '} cm
            WHERE cm.id = ' . $exerciseid;

    return $DB->get_record_sql($sql)->courseid;
}

/**
 * Sends an email notifying the student
 *
 * @return int  The notification message id.
 */
function send_booking_notification($studentid, $exerciseid, $sessiondate) {
    global $USER;

    // notification message data
    $data = (object) array(
        'instructor'    => get_fullusername($USER->id),
        'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
        'exercise'      => get_exercise_name($exerciseid),
        'confirmurl'    => (new \moodle_url('/local/availability/'))->out(false),
    );

    $message = new \core\message\message();
    $message->component = 'local_booking';
    $message->name = 'booking_notification';
    $message->userfrom = core_user::get_noreply_user();
    $message->userto = $studentid;
    $message->subject = get_string('emailnotify', 'local_booking');
    $message->fullmessage = get_string('emailnotifymsg', 'local_booking', $data);
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml = get_string('emailnotifyhtml', 'local_booking', $data);
    $message->smallmessage = get_string('emailnotifymsgsmall', 'local_booking');
    $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
    $message->contexturl = $data->confirmurl;
    $message->contexturlname = get_string('studentavialability', 'local_booking');
    $content = array('*' => array('header' => ' testing ', 'footer' => ' testing '));
    $message->set_additional_content('email', $content);

    // Actually send the message
    return message_send($message);
}

/**
 * Sends an email confirming booking to the instructor
 *
 * @return int  The notification message id.
 */
function send_booking_confirmation($studentid, $exerciseid, $sessiondate) {
    global $USER;

    // confirmation message data
    $data = (object) array(
        'student'       => get_fullusername($studentid),
        'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
        'exercise'      => get_exercise_name($exerciseid),
        'bookingurl'    => (new \moodle_url('/local/booking/'))->out(false),
    );

    $message = new \core\message\message();
    $message->component = 'local_booking';
    $message->name = 'booking_confirmation';
    $message->userfrom = core_user::get_noreply_user();
    $message->userto = $USER->id;
    $message->subject = get_string('emailconfirm', 'local_booking');
    $message->fullmessage = get_string('emailconfirmnmsg', 'local_booking', $data);
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml = get_string('emailconfirmhtml', 'local_booking', $data);
    $message->smallmessage = get_string('pluginname', 'local_booking');
    $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
    $message->contexturl = $data->bookingurl;
    $message->contexturlname = get_string('pluginname', 'local_booking');
    $content = array('*' => array('header' => ' testing header ', 'footer' => ' testing footer'));
    $message->set_additional_content('email', $content);

    // Actually send the message
    return message_send($message);
}

/**
 * This function extends the navigation with the booking item
 *
 * @param global_navigation $navigation The global navigation node to extend
 */

function local_booking_extend_navigation(global_navigation $navigation) {
    global $COURSE;

    $systemcontext = context_course::instance($COURSE->id);

    if (has_capability('local/booking:view', $systemcontext)) {
    // $node = $navigation->find('booking', navigation_node::TYPE_CUSTOM);
        $node = $navigation->find('booking', navigation_node::NODETYPE_LEAF);
        if (!$node && $COURSE->id!==SITEID) {
            $parent = $navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
            $node = navigation_node::create(get_string('booking', 'local_booking'), new moodle_url('/local/booking/view.php', array('courseid'=>$COURSE->id)));
            $node->key = 'booking';
            $node->type = navigation_node::NODETYPE_LEAF;
            $node->forceopen = true;
            $node->icon = new  pix_icon('i/emojicategorytravelplaces', '');  // e/table_props  e/split_cells

            $parent->add_node($node);
        }
    }
}
