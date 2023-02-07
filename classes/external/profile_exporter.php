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
 * Class for displaying students progression and instructor active bookings.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use renderer_base;
use moodle_url;

/**
 * Class for displaying instructor's booked sessions view.
 *
 * @package   local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_exporter extends exporter {

    /**
     * @var student $student The user of the profile
     */
    protected $student;

    /**
     * @var int $courseid The id of the active course
     */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {

        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $data['courseid']
            ]);

        $data['url'] = $url->out(false);
        $data['contextid'] = $related['context']->id;
        $this->courseid = $data['courseid'];
        $this->student = $data['user'];
        $data['userid'] = $this->student->get_id();

        parent::__construct($data, $related);
    }

    protected static function define_properties() {
        return [
            'url' => [
                'type' => PARAM_URL,
            ],
            'contextid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'courseid' => [
                'type' => PARAM_INT,
            ],
            'userid' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'fullname' => [
                'type' => PARAM_RAW,
            ],
            'timezone' => [
                'type' => PARAM_RAW,
            ],
            'sim1' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'sim2' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'moodleprofileurl' => [
                'type' => PARAM_URL,
            ],
            'recency' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'courseactivity' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'slots' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'modulescompleted' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'enroldate' => [
                'type' => PARAM_RAW,
            ],
            'lastlogin' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'lastgraded' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'lastlesson' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'lastlessoncompleted' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'graduationstatus' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'qualified' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'endorsed' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'endorsername' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'endorser' => [
                'type' => PARAM_INT,
                'optional' => true
            ],
            'endorsementlocked' => [
                'type' => PARAM_BOOL,
            ],
            'endorsementmgs' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'recommendationletterlink' => [
                'type' => PARAM_URL,
            ],
            'suspended' => [
                'type'  => PARAM_BOOL,
            ],
            'onholdrestrictionenabled' => [
                'type'  => PARAM_BOOL,
            ],
            'onhold' => [
                'type'  => PARAM_BOOL,
            ],
            'onholdgroup' => [
                'type'  => PARAM_RAW,
            ],
            'keepactive' => [
                'type'  => PARAM_BOOL,
            ],
            'keepactivegroup' => [
                'type'  => PARAM_RAW,
            ],
            'waitrestrictionenabled' => [
                'type'  => PARAM_BOOL,
            ],
            'restrictionoverride' => [
                'type'  => PARAM_BOOL,
            ],
            'admin' => [
                'type'  => PARAM_BOOL,
            ],
            'hasexams' => [
                'type'  => PARAM_BOOL,
            ],
            'requiresevaluation' => [
                'type'  => PARAM_BOOL,
            ],
            'loginasurl' => [
                'type' => PARAM_URL,
            ],
            'outlinereporturl' => [
                'type' => PARAM_URL,
            ],
            'completereporturl' => [
                'type' => PARAM_URL,
            ],
            'logbookurl' => [
                'type' => PARAM_URL,
            ],
            'mentorreporturl' => [
                'type' => PARAM_URL,
            ],
            'theoryexamreporturl' => [
                'type' => PARAM_URL,
            ],
            'practicalexamreporturl' => [
                'type' => PARAM_URL,
            ],
            'tested' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'examinerreporturl' => [
                'type' => PARAM_URL,
            ],
            'comment' => [
                'type' => PARAM_TEXT,
                'defaul' => '',
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        global $COURSE, $USER, $CFG;

        // moodle user object
        $studentid = $this->student->get_id();
        $moodleuser = \core_user::get_user($studentid, 'timezone');
        $customfields = profile_user_record($studentid);

        // student current lesson
        $exerciseid = $this->student->get_current_exercise();
        $currentlesson = array_values($COURSE->subscriber->get_lesson($exerciseid))[1];

        // module completion information
        $usermods = $this->student->get_priority()->get_completions();
        $coursemods = count($COURSE->subscriber->get_modules());
        $modsinfo = [
            'usermods' => $usermods,
            'coursemods' => $coursemods,
            'percent' => round(($usermods*100)/$coursemods)
        ];

        // qualified (next exercise is the course's last exercise) and tested status
        $qualified = $this->student->qualified();
        $requiresevaluation = $COURSE->subscriber->requires_skills_evaluation();
        $endorsed = false;
        $endorsementmsg = '';
        $hasexams = count($this->student->get_quize_grades()) > 0;

        if ($requiresevaluation) {

            // endorsement information
            $endorsed = get_user_preferences('local_booking_' .$this->courseid . '_endorse', false, $studentid);
            $endorsementmgs = array();
            if ($endorsed) {
                $endorserid = get_user_preferences('local_booking_' . $this->courseid . '_endorser', '', $studentid);
                $endorser = !empty($endorserid) ? participant::get_fullname($endorserid) : get_string('notfound', 'local_booking');
                $endorseronts = !empty($endorserid) ? get_user_preferences('local_booking_' . $this->courseid . '_endorsedate', '', $studentid) : time();
                $endorsementmgs = [
                    'endorser' => $endorser,
                    'endorsedate' =>  (new \Datetime('@'.$endorseronts))->format('M j\, Y')
                ];
                $endorsementmsg = get_string($endorsed ? 'endorsementmgs' : 'skilltestendorse', 'local_booking', $endorsementmgs);
            }
        }

        // moodle profile url
        $moodleprofile = new moodle_url('/user/view.php', [
            'id' => $studentid,
            'course' => $this->courseid,
        ]);

        // Course activity section
        $lastlogindate = $this->student->get_last_login_date();
        $lastlogindate = !empty($lastlogindate) ? $lastlogindate->format('M j\, Y') : '';
        $lastgradeddate = $this->student->get_last_graded_date();
        $lastgradeddate = !empty($lastgradeddate) ? $lastgradeddate->format('M j\, Y') : '';

        // graduation status
        if ($this->student->graduated()) {

            $graduationstatus = get_string('graduated', 'local_booking') . ' ' .  $lastgradeddate;

        } elseif ($this->student->tested()) {

            $graduationstatus = get_string('checkpassed', 'local_booking') . ' ' .  $COURSE->subscriber->get_graduation_exercise(true);

        } else {
            $graduationstatus = ($qualified ? get_string('qualified', 'local_booking') . ' ' .
                $COURSE->subscriber->get_graduation_exercise(true) : get_string('notqualified', 'local_booking'));
        }

        // log in as url
        $loginas = new moodle_url('/course/loginas.php', [
            'id' => $this->courseid,
            'user' => $studentid,
            'sesskey' => sesskey(),
        ]);

        // student skill test recommendation letter
        $recommendationletterlink = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $studentid,
            'report' => 'recommendation',
        ]);

        // student outline report
        $outlinereporturl = new moodle_url('/report/outline/user.php', [
            'id' => $studentid,
            'course' => $this->courseid,
            'mode' => 'outline',
        ]);

        // student complete report
        $completereporturl = new moodle_url('/report/outline/user.php', [
            'id' => $studentid,
            'course' => $this->courseid,
            'mode' => 'complete',
        ]);

        // student logbook
        $logbookurl = new moodle_url('/local/booking/logbook.php', [
            'courseid' => $this->courseid,
            'userid' => $studentid
        ]);

        // student mentor report
        $mentorreporturl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $studentid,
            'report' => 'mentor',
        ]);

        // student theory exam report
        $theoryexamreporturl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $studentid,
            'report' => 'theoryexam',
        ]);

        // student practical exam report
        $practicalexamreporturl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $studentid,
            'report' => 'practicalexam',
        ]);

        // student skill test form
        $examinerurl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $studentid,
            'report' => 'examiner',
        ]);

        $return = [
            'fullname'                 => $this->student->get_name(),
            'timezone'                 => $moodleuser->timezone == '99' ? $CFG->timezone : $moodleuser->timezone,
            'sim1'                     => $customfields->simulator,
            'sim2'                     => $customfields->simulator2,
            'moodleprofileurl'         => $moodleprofile->out(false),
            'recency'                  => $this->student->get_priority()->get_recency_days(),
            'courseactivity'           => $this->student->get_priority()->get_activity_count(false),
            'slots'                    => $this->student->get_priority()->get_slot_count(),
            'modulescompleted'         => get_string('modscompletemsg', 'local_booking', $modsinfo),
            'enroldate'                => $this->student->get_enrol_date()->format('M j\, Y'),
            'lastlogin'                => $lastlogindate,
            'lastgraded'               => $lastgradeddate,
            'lastlesson'               => $currentlesson,
            'lastlessoncompleted'      => $this->student->has_completed_lessons() ? get_string('yes') : get_string('no'),
            'graduationstatus'         => $graduationstatus,
            'qualified'                => $qualified,
            'requiresevaluation'       => $requiresevaluation,
            'endorsed'                 => $endorsed,
            'endorser'                 => $USER->id,
            'endorsername'             => \local_booking\local\participant\entities\participant::get_fullname($USER->id),
            'endorsementlocked'        => !empty($endorsed) && $endorsed && $endorserid != $USER->id,
            'endorsementmgs'           => $endorsementmsg,
            'recommendationletterlink' => $recommendationletterlink->out(false),
            'suspended'                => !$this->student->is_active(),
            'onholdrestrictionenabled' => $COURSE->subscriber->onholdperiod != 0,
            'onhold'                   => student::is_member_of($this->courseid, $studentid, LOCAL_BOOKING_ONHOLDGROUP),
            'onholdgroup'              => LOCAL_BOOKING_ONHOLDGROUP,
            'keepactive'               => student::is_member_of($this->courseid, $studentid,LOCAL_BOOKING_KEEPACTIVEGROUP),
            'keepactivegroup'          => LOCAL_BOOKING_KEEPACTIVEGROUP,
            'waitrestrictionenabled'   => $COURSE->subscriber->postingwait != 0,
            'restrictionoverride'      => get_user_preferences('local_booking_' . $this->courseid . '_availabilityoverride', false, $studentid),
            'admin'                    => has_capability('moodle/user:loginas', $this->related['context']),
            'hasexams'                 => $hasexams,
            'loginasurl'               => $loginas->out(false),
            'outlinereporturl'         => $outlinereporturl->out(false),
            'completereporturl'        => $completereporturl->out(false),
            'logbookurl'               => $logbookurl->out(false),
            'mentorreporturl'          => $mentorreporturl->out(false),
            'theoryexamreporturl'      => $theoryexamreporturl->out(false),
            'practicalexamreporturl'   => $practicalexamreporturl->out(false),
            'examinerreporturl'        => $examinerurl->out(false),
            'tested'                   => $this->student->tested(),
            'comment'                  => $this->student->get_comment(),
        ];

        return $return;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
            'exercises' => 'stdClass[]?',
        );
    }
}
