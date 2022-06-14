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
     * @var student $user The user of the profile
     */
    protected $user;

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
        $this->user = $data['user'];
        $data['userid'] = $this->user->get_id();

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
        $moodleuser = \core_user::get_user($this->user->get_id(), 'timezone');
        $customfields = profile_user_record($this->user->get_id());

        // student current lesson
        list($exerciseid, $currentsection) = $this->user->get_exercise(false);
        $currentlesson = get_section_name($this->courseid, $currentsection);

        // module completion information
        $usermods = $this->user->get_priority()->get_completions();
        $coursemods = $COURSE->subscriber->get_modules_count();
        $modsinfo = [
            'usermods' => $usermods,
            'coursemods' => $coursemods,
            'percent' => round(($usermods*100)/$coursemods)
        ];

        // qualified (next exercise is the course's last exercise) and tested status
        $grades = $this->user->get_exercises();
        list($exerciseid, $currentsection) = $this->user->get_exercise(true);
        $testexerciseid = $COURSE->subscriber->get_graduation_exercise();
        $tested = !empty($grades[$testexerciseid]);
        $qualified = $exerciseid == $testexerciseid || $this->user->is_member_of(LOCAL_BOOKING_GRADUATESGROUP) || $tested;
        $endorsed = get_user_preferences('local_booking_' .$this->courseid . '_endorse', false, $this->user->get_id());
        $hasexams = count($this->user->get_quizes()) > 0;

        // endorsement information
        $endorsementmgs = array();
        if ($endorsed) {
            $endorserid = get_user_preferences('local_booking_' . $this->courseid . '_endorser', '', $this->user->get_id());
            $endorser = !empty($endorserid) ? participant::get_fullname($endorserid) : get_string('notfound', 'local_booking');
            $endorseronts = !empty($endorserid) ? get_user_preferences('local_booking_' . $this->courseid . '_endorsedate', '', $this->user->get_id()) : time();
            $endorsementmgs = [
                'endorser' => $endorser,
                'endorsedate' =>  (new \Datetime('@'.$endorseronts))->format('M j\, Y')
            ];
        }

        // moodle profile url
        $moodleprofile = new moodle_url('/user/view.php', [
            'id' => $this->user->get_id(),
            'course' => $this->courseid,
        ]);

        // Course activity section
        $lastlogindate = $this->user->get_last_login_date();
        $lastlogindate = !empty($lastlogindate) ? $lastlogindate->format('M j\, Y') : '';
        $lastgradeddate = $this->user->get_last_graded_date();
        $lastgradeddate = !empty($lastgradeddate) ? $lastgradeddate->format('M j\, Y') : '';

        // log in as url
        $loginas = new moodle_url('/course/loginas.php', [
            'id' => $this->courseid,
            'user' => $this->user->get_id(),
            'sesskey' => sesskey(),
        ]);

        // student skill test recommendation letter
        $recommendationletterlink = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $this->user->get_id(),
            'report' => 'recommendation',
        ]);

        // student outline report
        $outlinereporturl = new moodle_url('/report/outline/user.php', [
            'id' => $this->user->get_id(),
            'course' => $this->courseid,
            'mode' => 'outline',
        ]);

        // student complete report
        $completereporturl = new moodle_url('/report/outline/user.php', [
            'id' => $this->user->get_id(),
            'course' => $this->courseid,
            'mode' => 'complete',
        ]);

        // student logbook
        $logbookurl = new moodle_url('/local/booking/logbook.php', [
            'courseid' => $this->courseid,
            'userid' => $this->user->get_id()
        ]);

        // student mentor report
        $mentorreporturl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $this->user->get_id(),
            'report' => 'mentor',
        ]);

        // student theory exam report
        $theoryexamreporturl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $this->user->get_id(),
            'report' => 'theoryexam',
        ]);

        // student practical exam report
        $practicalexamreporturl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $this->user->get_id(),
            'report' => 'practicalexam',
        ]);

        // student skill test form
        $examinerurl = new moodle_url('/local/booking/report.php', [
            'courseid' => $this->courseid,
            'userid' => $this->user->get_id(),
            'report' => 'examiner',
        ]);

        $return = [
            'fullname'                 => $this->user->get_name(),
            'timezone'                 => $moodleuser->timezone == '99' ? $CFG->timezone : $moodleuser->timezone,
            'sim1'                     => $customfields->simulator,
            'sim2'                     => $customfields->simulator2,
            'moodleprofileurl'         => $moodleprofile->out(false),
            'recency'                  => $this->user->get_priority()->get_recency_days(),
            'courseactivity'           => $this->user->get_priority()->get_activity_count(false),
            'slots'                    => $this->user->get_priority()->get_slot_count(),
            'modulescompleted'         => get_string('modscompletemsg', 'local_booking', $modsinfo),
            'enroldate'                => $this->user->get_enrol_date()->format('M j\, Y'),
            'lastlogin'                => $lastlogindate,
            'lastgraded'               => $lastgradeddate,
            'lastlesson'               => $currentlesson,
            'lastlessoncompleted'      => $this->user->has_completed_lessons() ? get_string('yes') : get_string('no'),
            'qualified'                => $qualified,
            'endorsed'                 => $endorsed,
            'endorser'                 => $USER->id,
            'endorsername'             => \local_booking\local\participant\entities\participant::get_fullname($USER->id),
            'endorsementlocked'        => !empty($endorsed) && $endorsed && $endorserid != $USER->id,
            'endorsementmgs'           => get_string($endorsed ? 'endorsementmgs' : 'skilltestendorse', 'local_booking', $endorsementmgs),
            'recommendationletterlink' => $recommendationletterlink->out(false),
            'suspended'                => !$this->user->is_active(),
            'onholdrestrictionenabled' => $COURSE->subscriber->onholdperiod != 0,
            'onhold'                   => $this->user->is_member_of(LOCAL_BOOKING_ONHOLDGROUP),
            'onholdgroup'              => LOCAL_BOOKING_ONHOLDGROUP,
            'keepactive'               => $this->user->is_member_of(LOCAL_BOOKING_KEEPACTIVEGROUP),
            'keepactivegroup'          => LOCAL_BOOKING_KEEPACTIVEGROUP,
            'waitrestrictionenabled'   => $COURSE->subscriber->postingwait != 0,
            'restrictionoverride'      => get_user_preferences('local_booking_' .$this->courseid . '_availabilityoverride', false, $this->user->get_id()),
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
            'tested'                   => $tested,
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
