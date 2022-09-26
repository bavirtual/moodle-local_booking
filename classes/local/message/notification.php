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

namespace local_booking\local\message;

use local_booking\local\participant\entities\instructor;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/booking/lib.php');

/**
 * Class for sending session booking notifications.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification extends \core\message\message {

    /**
     * Constructor.
     *
     */
    public function __construct() {
        global $COURSE;

        $this->courseid          = $COURSE->id;
        $this->component         = 'local_booking';
        $this->userfrom          = \core_user::get_noreply_user();
        $this->fullmessageformat = FORMAT_MARKDOWN;
        $this->notification      = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $this->smallmessage      = '';
    }

    /**
     * Sends an email notifying the student of the instructor's booking
     *
     * @param int       $studentid the student id receiving the message.
     * @param int       $exerciseid the exercise id relating to the session.
     * @param Datetime  $sessionstart the start date time object for the session.
     * @param Datetime  $sessionend the end date time object for the session.
     * @return bool     The notification message id.
     */
    public function send_booking_notification($studentid, $exerciseid, $sessionstart, $sessionend) {
        global $USER, $COURSE;

        // notification message data
        $data = self::get_notification_data(null,
            $COURSE->id,
            $COURSE->shortname,
            $USER->id,
            $studentid,
            $exerciseid,
            $sessionstart->getTimestamp(),
            $sessionend->getTimestamp(),
            's');

        $this->name              = 'booking_notification';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailnotify', 'local_booking', $data);
        $this->fullmessage       = get_string('emailnotifymsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailnotifyhtml', 'local_booking', $data) . get_string('emailnotifycalendarshtml', 'local_booking', $data);
        $this->contexturl        = $data->confirmurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');
        $this->set_additional_content('email', array('*' => array(
            'footer' => get_string('bookingfooter', 'local_booking', $data))));

        return message_send($this) != 0;
    }

    /**
     * Sends an email confirming booking made by the instructor
     *
     * @param int       $studentid the student id receiving the message.
     * @param int       $exerciseid the exercise id relating to the session.
     * @param Datetime  $sessionstart the start date time object for the session.
     * @param Datetime  $sessionend the end date time object for the session.
     * @return bool     The notification message id.
     */
    public function send_instructor_confirmation($studentid, $exerciseid, $sessionstart, $sessionend) {
        global $USER, $COURSE;

        // confirmation message data
        $data = self::get_notification_data(null,
            $COURSE->id,
            $COURSE->shortname,
            $USER->id,
            $studentid,
            $exerciseid,
            $sessionstart->getTimestamp(),
            $sessionend->getTimestamp(),
            'i');

        $this->name              = 'booking_confirmation';
        $this->userto            = $USER->id;
        $this->subject           = get_string('emailconfirmsubject', 'local_booking', $data);
        $this->fullmessage       = get_string('emailconfirmmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailconfirmhtml', 'local_booking', $data) . get_string('emailconfirmcalendarshtml', 'local_booking', $data) ;
        $this->contexturl        = $data->bookingurl;
        $this->contexturlname    = get_booking_config('ato')->name . ' ' . get_string('pluginname', 'local_booking');
        $this->set_additional_content('email', array('*' => array(
            'footer' => get_string('bookingfooter', 'local_booking', $data))));

        return message_send($this) != 0;
    }

    /**
     * Sends an email notifying the instructor of
     * student confirmation of booked session
     *
     * @param int       $courseid the course id.
     * @param int       $studentid the student id sending the notification message.
     * @param int       $exerciseid the exercise id relating to the session.
     * @param Datetime  $sessiondatetime the date time object for the session.
     * @param int       $instructorid the instructor id receiving the message.
     * @return bool     The notification message id.
     */
    public function send_instructor_notification($courseid, $studentid, $exerciseid, $sessiondatetime, $instructorid) {
        global $COURSE;

        // notification message data
        $data = (object) array(
            'coursename'    => $COURSE->shortname,
            'student'       => student::get_fullname($studentid),
            'sessiondate'   => $sessiondatetime,
            'exercise'      => subscriber::get_exercise_name($exerciseid),
            'courseurl'     => (new \moodle_url('/course/view.php', array(
                'id'        => $courseid)))->out(false),
            'assignurl'     => (new \moodle_url('/mod/assign/index.php', array(
                'id'        => $courseid)))->out(false),
            'exerciseurl'   => (new \moodle_url('/mod/assign/view.php', array(
                'id'        => $exerciseid)))->out(false),
            'bookingurl'        => (new \moodle_url('/local/booking/view.php', array('courseid'=>$courseid)))->out(false),
        );

        $this->name              = 'instructor_notification';
        $this->userto            = $instructorid;
        $this->subject           = get_string('emailinstconfirmsubject', 'local_booking', $data);
        $this->fullmessage       = get_string('emailinstconfirmnmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailinstconfirmhtml', 'local_booking', $data);
        $this->contexturl        = $data->bookingurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');
        $this->set_additional_content('email', array('*' => array(
            'footer' => get_string('bookingfooter', 'local_booking', $data))));

        return message_send($this) != 0;
    }

    /**
     * Sends an email notifying the student of
     * the cancelled session.
     *
     * @param int       $studentid the student id sending the notification message.
     * @param int       $exerciseid the exercise id relating to the session.
     * @param Datetime  $sessiondatetime the date time object for the session.
     * @param string    $comment the comment sent by the instructor to the student.
     * @return bool     The notification message id.
     */
    public function send_session_cancellation($studentid, $exerciseid, $sessiondate, $comment) {
        global $USER, $COURSE;

        // notification message data
        $data = (object) array(
            'coursename'    => $COURSE->shortname,
            'instructor'    => instructor::get_fullname($USER->id),
            'sessiondate'   => $sessiondate->format('l M j \a\t H:i \z\u\l\u'),
            'exercise'      => subscriber::get_exercise_name($exerciseid),
            'comment'       => $comment,
            'courseurl'     => (new \moodle_url('/course/view.php', array('id'=> $COURSE->id)))->out(false),
        );

        $this->name              = 'session_cancellation';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailcancel', 'local_booking', $data);
        $this->fullmessage       = get_string('emailcancelmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailcancelhtml', 'local_booking', $data);
        $this->contexturl        = $data->courseurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        return message_send($this) != 0;
    }

    /**
     * Sends an email warning to the student for
     * being inactive after posting wait period.
     *
     * @param int       $studentid the student id sending the notification message.
     * @param Datetime  $lastbookeddate the date object the student last booked a session.
     * @param Datetime  $onholddate the date object the student is to be put on hold.
     * @param int       $courseid the course id.
     * @param string    $coursename the course name.
     * @return bool     The notification message id.
     */
    public function send_inactive_warning($studentid, $lastbookeddate, $onholddate, $courseid, $coursename) {
        // notification message data
        $data = (object) array(
            'coursename'    => $coursename,
            'lastbookeddate'=> $lastbookeddate->format('M d, Y'),
            'onholddate'    => $onholddate->format('M d, Y'),
            'courseurl'     => (new \moodle_url('/course/view.php', array(
                'id'        => $courseid)))->out(false),
            'assignurl'     => (new \moodle_url('/mod/assign/index.php', array(
                'id'        => $courseid)))->out(false),
            'slotsurl'      => (new \moodle_url('/local/booking/availability.php', array('courseid'=> $courseid)))->out(false),
        );

        $this->name              = 'inactive_warning';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailinactivewarning', 'local_booking', $data);
        $this->fullmessage       = get_string('emailinactivewarningmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailinactivewarninghtml', 'local_booking', $data);
        $this->contexturl        = $data->slotsurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        return message_send($this) != 0;
    }

    /**
     * Sends an email warning to the student of
     * upcoming on-hold date.
     *
     * @param int       $studentid the student id sending the notification message.
     * @param Datetime  $onholddate the date time object the student being put on hold.
     * @param int       $courseid the course id.
     * @param string    $coursename the course name.
     * @return bool     The notification message id.
     */
    public function send_onhold_warning($studentid, $onholddate, $courseid, $coursename) {
        // notification message data
        $data = (object) array(
            'coursename'    => $coursename,
            'onholddate'    => $onholddate->format('M d, Y'),
            'courseurl'     => (new \moodle_url('/course/view.php', array(
                'id'        => $courseid)))->out(false),
            'assignurl'     => (new \moodle_url('/mod/assign/index.php', array(
                'id'        => $courseid)))->out(false),
            'slotsurl'      => (new \moodle_url('/local/booking/availability.php', array('courseid'=> $courseid)))->out(false),
        );

        $this->name              = 'onhold_warning';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailonholdwarning', 'local_booking', $data);
        $this->fullmessage       = get_string('emailonholdwarningmsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailonholdwarninghtml', 'local_booking', $data);
        $this->contexturl        = $data->slotsurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        return message_send($this) != 0;
    }

    /**
     * Sends an email notifying the student
     * of being placed on-hold.
     *
     * @param int       $studentid the student id sending the notification message.
     * @param Datetime  $lastsessiondate the date time of the last session taken by the student.
     * @param Datetime  $suspenddate the date time of the student will be suspended.
     * @param string    $coursename the course name.
     * @param array     $seniorinstructors the list of senior instructors to be copied.
     * @return bool     The notification message id.
     */
    public function send_onhold_notification($studentid, $lastsessiondate, $suspenddate, $coursename, $seniorinstructors) {
        global $COURSE;

        // notification message data
        $data = (object) array(
            'coursename'        => $coursename,
            'lastsessiondate'   => $lastsessiondate->format('M d, Y'),
            'suspenddate'       => $suspenddate->format('M d, Y'),
            'studentname'       => student::get_fullname($studentid),
            'courseurl'         => (new \moodle_url('/course/view.php', array(
                'id'            => $COURSE->id)))->out(false),
            'assignurl'         => (new \moodle_url('/mod/assign/index.php', array(
                'id'            => $COURSE->id)))->out(false),
        );

        $this->name              = 'onhold_notification';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailonholdnotify', 'local_booking', $data);
        $this->fullmessage       = get_string('emailonholdnotifymsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailonholdnotifyhtml', 'local_booking', $data);
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        // send notification then copy senior instructors
        $result = message_send($this) != 0;
        $result = $result && $this->copy_senior_instructors('onhold_notification', 'emailonholdnotify',
            'emailonholdinstnotifymsg', 'emailonholdinstnotifyhtml', $data, $seniorinstructors);

        return $result;
    }

    /**
     * Sends an email notifying the student
     * of being placed on-hold.
     *
     * @param int       $studentid the student id sending the notification message.
     * @param Datetime  $lastsessiondate the date time of the last session taken by the student.
     * @param string    $coursename the course name.
     * @param array     $seniorinstructors the list of senior instructors to be copied.
     * @return bool     The notification message id.
     */
    public function send_suspension_notification($studentid, $lastsessiondate, $coursename, $seniorinstructors) {
        global $COURSE;

        // notification message data
        $data = (object) array(
            'coursename'        => $coursename,
            'studentname'       => student::get_fullname($studentid),
            'lastsessiondate'   => $lastsessiondate->format('M d, Y'),
            'courseurl'         => (new \moodle_url('/course/view.php', array(
                'id'            => $COURSE->id)))->out(false),
            'assignurl'         => (new \moodle_url('/mod/assign/index.php', array(
                'id'            => $COURSE->id)))->out(false),
        );

        $this->name              = 'suspension_notification';
        $this->userto            = $studentid;
        $this->subject           = get_string('emailsuspendnotify', 'local_booking', $data);
        $this->fullmessage       = get_string('emailsuspendnotifymsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailsuspendnotifyhtml', 'local_booking', $data);
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        // send notification then copy senior instructors
        $result = message_send($this) != 0;
        $result = $result && $this->copy_senior_instructors('suspension_notification', 'emailonholdnotify',
            'emailsuspendinstnotifymsg', 'emailsuspendinstnotifyhtml', $data, $seniorinstructors);

        return $result;
    }

    /**
     * Sends an email notifying the instructor
     * of overdue session.
     *
     * @param int       $instructorid the instructor id receiving the notification.
     * @param string    $status the status of instructor activity.
     * @param int       $courseid the course id.
     * @param string    $coursename the course name.
     * @param array     $seniorinstructors the list of senior instructors to be copied.
     * @return bool     The notification message id.
     */
    public function send_session_overdue_notification($instructorid, $status, $courseid, $coursename, $seniorinstructors) {
        // notification message data
        $data = (object) array(
            'coursename'    => $coursename,
            'instructorname'=> instructor::get_fullname($instructorid),
            'status'        => $status,
            'courseurl'     => (new \moodle_url('/course/view.php', array(
                'id'        => $courseid)))->out(false),
            'assignurl'     => (new \moodle_url('/mod/assign/index.php', array(
                'id'        => $courseid)))->out(false),
            'bookingurl'    => (new \moodle_url('/local/booking/view.php', array('courseid'=>$courseid)))->out(false),
        );

        $this->name              = 'sessionoverdue_notification';
        $this->userto            = $instructorid;
        $this->subject           = get_string('emailoverduenotify', 'local_booking', $data);
        $this->fullmessage       = get_string('emailoverduenotifymsg', 'local_booking', $data);
        $this->fullmessagehtml   = get_string('emailoverduenotifyhtml', 'local_booking', $data);
        $this->contexturl        = $data->bookingurl;
        $this->contexturlname    = get_string('studentavialability', 'local_booking');

        // send notification then copy senior instructors
        $result = message_send($this) != 0;
        $result = $result && $this->copy_senior_instructors('sessionoverdue_notification', 'emailoverduenotify',
            'emailoverduenotifyinstmsg', 'emailoverduenotifyinsthtml', $data, $seniorinstructors);

        return $result;
    }

    /**
     * Sends an email notifying the instructor
     * of availability being posted by students.
     *
     * @param array     $instructors the list of instructor ids to receive the notification.
     * @param array     $data data tags.
     * @return bool     The notification message id.
     */
    public function send_availability_posting_notification(array $instructors, array $data) {

        $result = true;

        // loop through the list of instructors to notify
        foreach ($instructors as $instructor) {

            $msg = new notification();
            $msg->name              = 'availabilityposting_notification';
            $msg->userto            = $instructor->get_id();
            $msg->subject           = get_string('emailavailpostingnotify', 'local_booking', $data);
            $msg->fullmessage       = get_string('emailavailpostingnotifymsg', 'local_booking', $data);
            $msg->fullmessagehtml   = get_string('emailavailpostingnotifyhtml', 'local_booking', $data);
            $msg->contexturl        = $data['bookingurl'];
            $msg->contexturlname    = get_booking_config('ato')->name . ' ' . get_string('pluginname', 'local_booking');
            $msg->set_additional_content('email', array('*' => array(
                'footer' => get_string('bookingfooter', 'local_booking', $data))));

            // send notification then copy senior instructors
            $result = $result && message_send($msg) != 0;

        }

        return $result;
    }

    /**
     * Sends an email notifying the instructors with
     * a new student recommendation.
     *
     * @param array     $instructors  A list of all instructors.
     * @param array     $data data tags.
     * @return bool     The notification message id.
     */
    public function send_recommendation_notification($instructors, array $data) {

        $result = true;

        // sent to all except the graduating student
        foreach ($instructors as $instructor) {

            $msg = new notification();
            $msg->name              = 'recommendation_notification';
            $msg->userto            = $instructor->get_id();
            $msg->subject           = get_string('emailrecommendationnotify', 'local_booking', $data);
            $msg->fullmessage       = get_string('emailrecommendationnotifymsg', 'local_booking', $data);
            $msg->fullmessagehtml   = get_string('emailrecommendationnotifyhtml', 'local_booking', $data);
            $msg->contexturl        = $data['bookingurl'];
            $msg->contexturlname    = get_booking_config('ato')->name . ' ' . get_string('pluginname', 'local_booking');
            $msg->set_additional_content('email', array('*' => array(
                'footer' => get_string('bookingfooter', 'local_booking', $data))));

            $result = $result && (message_send($msg) != 0);
        }

        return $result;
    }

    /**
     * Sends an email notifying the students and instructors
     * of a newly graduating student.
     *
     * @param array     $coursemembers  A list of all course members.
     * @param array     $data data tags.
     * @return bool     The notification message id.
     */
    public function send_graduation_notification($coursemembers, array $data) {

        $result = true;

        // sent to all except the graduating student
        foreach ($coursemembers as $coursemember) {

            if ($coursemember->get_id() != $data['graduateid']) {
                $msg = new notification();
                $msg->name              = 'graduation_notification';
                $msg->userto            = $coursemember->get_id();
                $msg->subject           = get_string('emailgraduationnotify', 'local_booking', $data);
                $msg->fullmessage       = get_string('emailgraduationnotifymsg', 'local_booking', $data);
                $msg->fullmessagehtml   = get_string('emailgraduationnotifyhtml', 'local_booking', $data);

                $result = $result && (message_send($msg) != 0);
            }
        }

        return $result;
    }

    /**
     * Copies the instructor on communications.
     *
     * @param string    $msgid the message id string.
     * @param string    $msgtext the text body of the message.
     * @param string    $msghtml the HTML body of the message
     * @param array     $msgdata the message parameters.
     * @param array     $seniorinstructors the list of senior instructors.
     * @return int      The copied message id
     */
    public function copy_senior_instructors($msgid, $msgsubject, $msgtext, $msghtml, $msgdata, $seniorinstructors){
        $result = true;
        // get senior instructor role users and send them notifications regardin
        foreach ($seniorinstructors as $seniorinstructor) {
            $ccstaffmsg = new notification();
            $ccstaffmsg->name              = $msgid;
            $ccstaffmsg->userto            = $seniorinstructor->get_id();
            $ccstaffmsg->subject           = get_string($msgsubject, 'local_booking', $msgdata);
            $ccstaffmsg->fullmessage       = get_string($msgtext, 'local_booking', $msgdata);
            $ccstaffmsg->fullmessagehtml   = get_string($msghtml, 'local_booking', $msgdata);

            $result = $result && (message_send($ccstaffmsg) != 0);
        }
        return $result;
    }

    /**
     * Returns an array of variables used in
     * notification message to student and instructor.
     *
     * @param array     $params the list of parameters.
     * @param int       $courseid the course id.
     * @param string    $coursename the course short name.
     * @param int       $instructorid the instructor user id.
     * @param int       $studentid the studentid user id.
     * @param int       $exerciseid the exercise id relating to the session.
     * @param int       $sessionstart the start date time of the session.
     * @param int       $sessionend the  end date time of the session.
     * @return object   The array of data for the message.
     */
    public static function get_notification_data($params = null,
        $courseid = 0,
        $coursename = '',
        $instructorid = 0,
        $studentid = 0,
        $exerciseid = 0,
        $sessionstart = 0,
        $sessionend = 0,
        $requester = 'i') {

        // parse parameters if exists
        if (!empty($params)) {
            $courseid = $params['id'];
            $coursename= $params['name'];
            $instructorid = $params['inst'];
            $studentid = $params['std'];
            $exerciseid = $params['extid'];
            $sessionstart = $params['tstart'];
            $sessionend = $params['tend'];
            $requester = $params['req'];
        }
        else {
            // ics download file array
            $params = array(
            'id'    => $courseid,
            'name'  => $coursename,
            'extid' => $exerciseid,
            'inst'  => $instructorid,
            'std'   => $studentid,
            'req'   => $requester,
            'tstart'=> $sessionstart,
            'tend'  => $sessionend);
        }

        // notification message data
        $data = (object) array(
            'courseid'      => $courseid,
            'coursename'    => $coursename,
            'instructorid'  => $instructorid,
            'instructor'    => instructor::get_fullname($instructorid),
            'studentid'     => $studentid,
            'student'       => student::get_fullname($studentid),
            'sessiondate'   => (new \DateTime('@'.$sessionstart))->format('l M j \a\t H:i \z\u\l\u'),
            'sessionstart'  => $sessionstart,
            'sessionend'    => $sessionend,
            'exerciseid'    => $exerciseid,
            'exercise'      => subscriber::get_exercise_name($exerciseid),
            'requester'     => $requester,
            'courseurl'     => (new \moodle_url('/course/view.php', array('id'=> $courseid)))->out(false),
            'assignurl'     => (new \moodle_url('/mod/assign/index.php', array('id'=> $courseid)))->out(false),
            'exerciseurl'   => (new \moodle_url('/mod/assign/view.php', array('id'=> $exerciseid)))->out(false),
            'bookingurl'    => (new \moodle_url('/local/booking/view.php', array('courseid'=>$courseid)))->out(false),
            'confirmurl'    => (new \moodle_url('/local/booking/confirm.php', array(
                'courseid'  => $courseid,
                'exeid'     => $exerciseid,
                'userid'    => $studentid,
                'insid'     => $instructorid)))->out(false),
            'icsurl'        => (new \moodle_url('/local/booking/calendar.php', array_merge($params,['action'=>'i'])))->out(false),
            'googleurl'     => (new \moodle_url('/local/booking/calendar.php', array_merge($params,['action'=>'g'])))->out(false),
            'pixrooturl' => (new \moodle_url('/local/booking/pix'))->out(false),
            'liveurl'       => (new \moodle_url('/local/booking/calendar.php', array_merge($params,['action'=>'l'])))->out(false),
        );

        return $data;
   }
}