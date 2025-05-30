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
 * Class for displaying students profile.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\exporters;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\output\views\base_view;
use renderer_base;
use moodle_url;
use DateTime;

/**
 * Class for displaying student profile page.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class profile_student_exporter extends exporter {

    /**
     * @var subscriber $subscriber The plugin subscribing course
     */
    protected $subscriber;

    /**
     * @var student $student The student user of the profile
     */
    protected $student;

    /**
     * @var int $courseid The id of the active course
     */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student profile data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {
        $this->subscriber = $related['subscriber'];
        $this->courseid = $this->subscriber->get_id();

        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $this->courseid
            ]);

        $data['url'] = $url->out(false);
        $data['contextid'] = $related['context']->id;
        $data['courseid'] = $this->courseid;
        $this->student = $this->subscriber->get_student($data['userid']);

        parent::__construct($data, $related);
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context' => 'context',
            'subscriber' => 'local_booking\local\subscriber\entities\subscriber',
        );
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
            'fleet' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'sim1' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'sim2' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'noshows' => [
                'type' => PARAM_URL,
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
            'endorserid' => [
                'type' => PARAM_INT,
                'optional' => true
            ],
            'endorsementlocked' => [
                'type' => PARAM_BOOL,
            ],
            'endorsementmsg' => [
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
            'postingwait' => [
                'type'  => PARAM_INT,
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
            'forcecompletionurl' => [
                'type' => PARAM_URL,
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
            'coursemodules' => [
                'multiple' => true,
                'type' => [
                    'exerciseid' => [
                        'type' => PARAM_INT,
                    ],
                    'exercisename' => [
                        'type' => PARAM_RAW,
                    ],
                    'exercisetype' => [
                        'type' => PARAM_RAW,
                    ],
                    'exercisetitle' => [
                        'type' => PARAM_RAW,
                    ],
                ]
            ],
            'sessions' => [
                'type' => dashboard_session_exporter::read_properties_definition(),
                'multiple' => true,
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
        global $USER, $CFG;

        // moodle user object
        $studentid = $this->student->get_id();
        $moodleuser = \core_user::get_user($studentid, 'timezone');

        // student current lesson and consider new joiners that have no current exercise, their next exercise is the first
        $exerciseid = $this->student->get_current_exercise()->id;
        $currentlesson = $exerciseid ? array_values($this->subscriber->get_lesson_by_exercise_id($exerciseid))[1] : get_string('none');

        // exercise (module) completion information
        $usermods = $this->student->get_statistics()->get_completed_exercise_count();
        $coursemods = count($this->subscriber->get_modules(true));
        $modsinfo = [
            'usermods' => $usermods,
            'coursemods' => $coursemods,
            'percent' => round(($usermods*100)/$coursemods)
        ];

        // no shows
        $noshows = get_string('none');
        $noshowdates = [];
        if ($noshowslist = array_column($this->student->get_noshow_bookings(), 'starttime')) {
            foreach ($noshowslist as $noshowdate) {
                $noshowdates[] = (new DateTime('@' . $noshowdate))->format('M d, Y');
            }
            $noshows = implode('<br>', $noshowdates);
        }

        // qualified (next exercise is the course's last exercise) and tested status
        $qualified = $this->student->qualified();
        $requiresevaluation = $this->subscriber->requires_skills_evaluation();
        $endorsed = false;
        $endorsementmsg = '';
        $hasexams = count($this->student->get_quizzes_grades()) > 0;

        if ($requiresevaluation) {

            // endorsement information
            $endorsement = $this->student->get_progress_flag(LOCAL_BOOKING_PROGFLAGS['ENDORSE']);
            $data = array();
            if ($endorsement) {
                $endorsed = $endorsement->endorsed;
                $endorserid = $endorsement->endorserid;
                $endorsername = !empty($endorserid) ? participant::get_fullname($endorserid) : get_string('notfound', 'local_booking');
                $endorsedonts = !empty($endorserid) ? $endorsement->endorsedate : time();
                $data = [
                    'endorsername' => $endorsername,
                    'endorsedate' =>  (new DateTime("@$endorsedonts"))->format('M j\, Y')
                ];
                $endorsementmsg = get_string($endorsement ? 'endorsementmsg' : 'skilltestendorsed', 'local_booking', $data);
            }
        }

        // moodle profile url
        $moodleprofile = new moodle_url('/user/view.php', [
            'id' => $studentid,
            'course' => $this->courseid,
        ]);

        // Course activity section
        $lastlogindate = $this->student->get_last_login_date();
        $lastlogindate = !empty($lastlogindate) ? $lastlogindate->format('M j\, Y') : get_string('none');
        $lastgradeddate = $this->student->get_last_graded_date();
        $lastgradeddate = !empty($lastgradeddate) ? $lastgradeddate->format('M j\, Y') : get_string('none');

        // graduation status
        if ($this->student->graduated()) {

            $graduationstatus = get_string('graduated', 'local_booking') . ' ' .  $lastgradeddate;

        } elseif ($this->student->tested()) {

            $graduationstatus = get_string(($this->student->passed() ? 'checkpassed' : 'checkfailed'), 'local_booking') . ' ' .  $this->subscriber->get_graduation_exercise_id(true);

        } else {
            $graduationstatus = ($qualified ? get_string('qualified', 'local_booking') . ' ' .
                $this->subscriber->get_graduation_exercise_id(true) : get_string('notqualified', 'local_booking'));
        }

        // log in as url
        $forcecompletionurl = new moodle_url('/local/booking/profile.php', [
            'courseid' => $this->courseid,
            'userid' => $studentid,
            'forcecompletion' => 1,
        ]);

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

        // session progression options and related exporter data
        $options = [
            'isinstructor' => true,
            'isexaminer'   => true,
            'viewtype'     => 'sessions',
            'readonly'     => true
        ];
        $this->related += [
            'coursemodules' => $this->subscriber->get_modules(true),
            'filter'        => 'active'
        ];

        $return = [
            'fullname'                 => $this->student->get_name(),
            'timezone'                 => $moodleuser->timezone == '99' ? $CFG->timezone : $moodleuser->timezone,
            'fleet'                    => $this->student->get_fleet() ?: get_string('none'),
            'sim1'                     => $this->student->get_simulator(),
            'sim2'                     => $this->student->get_simulator(false),
            'noshows'                  => $noshows,
            'moodleprofileurl'         => $moodleprofile->out(false),
            'recency'                  => $this->student->get_recency_days(),
            'courseactivity'           => $this->student->get_statistics()->get_activity_count(false),
            'slots'                    => $this->student->get_statistics()->get_total_posts(),
            'modulescompleted'         => get_string('modscompletemsg', 'local_booking', $modsinfo),
            'enroldate'                => $this->student->get_enrol_date()->format('M j\, Y'),
            'lastlogin'                => $lastlogindate,
            'lastgraded'               => $lastgradeddate,
            'lastlesson'               => $currentlesson,
            'lastlessoncompleted'      => $this->student->has_completed_lessons() ? get_string('yes') : get_string('no'),
            'graduationstatus'         => $graduationstatus,
            'qualified'                => $qualified,
            'requiresevaluation'       => $requiresevaluation,
            'endorsed'                 => !empty($endorsed),
            'endorserid'               => $USER->id,
            'endorsername'             => participant::get_fullname($USER->id),
            'endorsementlocked'        => !empty($endorsed) && $endorserid != $USER->id,
            'endorsementmsg'           => $endorsementmsg,
            'recommendationletterlink' => $recommendationletterlink->out(false),
            'suspended'                => !$this->student->is_active(),
            'onholdrestrictionenabled' => $this->subscriber->onholdperiod != 0,
            'onhold'                   => $this->student->is_member_of(LOCAL_BOOKING_ONHOLDGROUP),
            'onholdgroup'              => LOCAL_BOOKING_ONHOLDGROUP,
            'keepactive'               => $this->student->is_member_of(LOCAL_BOOKING_KEEPACTIVEGROUP),
            'keepactivegroup'          => LOCAL_BOOKING_KEEPACTIVEGROUP,
            'waitrestrictionenabled'   => $this->subscriber->postingwait != 0,
            'postingwait'              => $this->subscriber->postingwait,
            'restrictionoverride'      => $this->student->get_progress_flag(LOCAL_BOOKING_PROGFLAGS['POSTOVERRIDE']),
            'admin'                    => has_capability('moodle/user:loginas', $this->related['context']),
            'hasexams'                 => $hasexams,
            'forcecompletionurl'       => $forcecompletionurl->out(false),
            'loginasurl'               => $loginas->out(false),
            'outlinereporturl'         => $outlinereporturl->out(false),
            'completereporturl'        => $completereporturl->out(false),
            'logbookurl'               => $logbookurl->out(false),
            'mentorreporturl'          => $mentorreporturl->out(false),
            'theoryexamreporturl'      => $theoryexamreporturl->out(false),
            'practicalexamreporturl'   => $practicalexamreporturl->out(false),
            'tested'                   => $this->student->tested(),
            'coursemodules'            => base_view::get_modules($output, $this->subscriber, $options),
            'sessions'                 => dashboard_student_exporter::get_sessions($output, $this->student, $this->related),
            'comment'                  => $this->student->get_comment(),
        ];

        return $return;
    }
}
