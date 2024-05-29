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
 * Class for displaying instructor profile.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\participant\entities\instructor;
use local_booking\local\logbook\entities\logbook;
use local_booking\output\views\base_view;
use renderer_base;
use moodle_url;

/**
 * Class for displaying instructor profile page.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instructor_profile_exporter extends exporter {

    /**
     * @var subscriber $subscriber The plugin subscribing course
     */
    protected $subscriber;

    /**
     * @var instructor $instructor The instructor user of the profile
     */
    protected $instructor;

    /**
     * @var int $courseid The id of the active course
     */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param mixed $data An array of instructor profile data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related) {

        $url = new moodle_url('/local/booking/view.php', [
                'courseid' => $data['courseid']
            ]);

        $data['url'] = $url->out(false);
        $data['contextid'] = $related['context']->id;
        $data['userid'] = $data['userid'];
        $data['ato'] = get_config('local_booking', 'atoname');
        $this->courseid = $data['courseid'];
        $this->subscriber = $data['subscriber'];
        $this->instructor = $this->subscriber->get_instructor($data['userid'], true);

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
            'ato' => [
                'type' => PARAM_RAW,
                'optional' => true
            ]
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'coursemodules' => [
                'type' => exercise_name_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'sessions' => [
                'type' => exercise_name_exporter::read_properties_definition(),
                'multiple' => true,
            ],
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
            'moodleprofileurl' => [
                'type' => PARAM_URL,
            ],
            'enroldate' => [
                'type' => PARAM_RAW,
            ],
            'lastlogin' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'instructordate' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'examiner' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'examinerdate' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'lastgraded' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'lastbooked' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'logbookurl' => [
                'type' => PARAM_URL,
            ],
            'totalgroundhours' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'totalflighthours' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'totalhours' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'totalatohours' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'totalgradedsessions' => [
                'type' => PARAM_INT,
                'optional' => true
            ],
            'totalbookedsessions' => [
                'type' => PARAM_INT,
                'optional' => true
            ],
            'totalexamhours' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'totalexams' => [
                'type' => PARAM_INT,
                'optional' => true
            ],
            'admin' => [
                'type'  => PARAM_BOOL,
                'default' => false
            ],
            'loginasurl' => [
                'type' => PARAM_URL,
            ],
            'xcoursebookings' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'roles' => [
                'type' => PARAM_RAW,
                'optional' => true
            ],
            'comment' => [
                'type' => PARAM_TEXT,
                'default' => '',
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
        global $CFG;

        // instructor main profile data
        $instructorid = $this->instructor->get_id();
        $moodleuser = \core_user::get_user($instructorid, 'timezone');

        // sessions summary data
        $examid = $this->subscriber->get_graduation_exercise();
        $logbook = $this->instructor->get_logbook(true);
        $summary = $logbook->get_summary(false, false, $examid);

        // get booked session totals
        $bookedsessions = $this->instructor->get_booked_sessions_count();
        $totalbookedsessions = array_sum(array_column($bookedsessions, 'sessions'));
        // get graded session totals
        $gradedsessions = $this->instructor->get_graded_sessions_count();
        $totalexams = $this->instructor->is_examiner() && !empty($gradedsessions[$examid]) ? $gradedsessions[$examid]->sessions : 0;
        $instructorsince = $this->instructor->has_role_since(LOCAL_BOOKING_INSTRUCTORROLE) ?: $this->instructor->has_role_since(LOCAL_BOOKING_SENIORINSTRUCTORROLE);

        // Sessions conducted data
        $options = [
            'isinstructor' => true,
            'isexaminer'   => $this->instructor->is_examiner(),
            'viewtype'     => 'book',
            'readonly'     => false,
            'excludeexams' => !$this->instructor->is_examiner(),
            'excludequizes'=> true
        ];
        $exercisenames = base_view::get_modules($output, $this->subscriber, $options);
        $sessions = $exercisenames;

        // add graded sessions count
        foreach ($sessions as $session) {
            if (array_key_exists($session->exerciseid, $gradedsessions)) {
                $session->gradedcount = $gradedsessions[$session->exerciseid]->sessions;
            } else {
                $session->gradedcount = 0;
            }
        }
        $totalgradedsessions = array_sum(array_column($gradedsessions, 'sessions'));

        // moodle profile url
        $moodleprofile = new moodle_url('/user/view.php', [
            'id'      => $instructorid,
            'course'  => $this->courseid,
        ]);

        // instructor logbook
        $logbookurl = new moodle_url('/local/booking/logbook.php', [
            'courseid' => $this->courseid,
            'userid'   => $instructorid
        ]);

        // log in as url
        $loginas = new moodle_url('/course/loginas.php', [
            'id' => $this->courseid,
            'user' => $instructorid,
            'sesskey' => sesskey(),
        ]);

        // Course activity section
        $lastlogindate  = $this->instructor->get_last_login_date();
        $lastlogindate  = !empty($lastlogindate) ? $lastlogindate->format('M j\, Y') : '';
        $lastgradeddate = $this->instructor->get_last_graded_date();
        $lastgradeddate = !empty($lastgradeddate) ? $lastgradeddate->format('M j\, Y') : '';
        $lastbookeddate = $this->instructor->get_last_booked_date();
        $lastbookeddate = !empty($lastbookeddate) ? $lastbookeddate->format('M j\, Y') : '';

        $return = [
            'fullname'         => $this->instructor->get_name(),
            'timezone'         => $moodleuser->timezone == '99' ? $CFG->timezone : $moodleuser->timezone,
            'fleet'            => $this->instructor->get_fleet() ?: get_string('none'),
            'sim1'             => $this->instructor->get_simulator(),
            'sim2'             => $this->instructor->get_simulator(false),
            'moodleprofileurl' => $moodleprofile->out(false),
            'enroldate'        => $this->instructor->get_enrol_date()->format('M j\, Y'),
            'lastlogin'        => $lastlogindate,
            'instructordate'   => $instructorsince,
            'examiner'         => $this->instructor->is_examiner(),
            'examinerdate'     => $this->instructor->has_role_since(LOCAL_BOOKING_EXAMINERROLE),
            'lastgraded'       => $lastgradeddate,
            'lastbooked'       => $lastbookeddate,
            'logbookurl'       => $logbookurl->out(false),
            'totalgroundhours' => logbook::convert_time($summary->totalgroundtime),
            'totalflighthours' => logbook::convert_time($summary->totalflighttime),
            'totalhours'       => logbook::convert_time($summary->totalgroundtime+$summary->totalflighttime),
            'totalatohours'    => logbook::convert_time($this->instructor->get_ato_hours()),
            'totalbookedsessions'=> $totalbookedsessions,
            'totalexamhours'   => logbook::convert_time($summary->totalexaminertime),
            'totalexams'       => $totalexams,
            'loginasurl'       => $loginas->out(false),
            'admin'            => has_capability('moodle/user:loginas', $this->related['context']),
            'xcoursebookings'  => \get_user_preferences('local_booking_1_xcoursebookings', false, $instructorid),
            'roles'            => strip_tags(get_user_roles_in_course($instructorid, $this->courseid)),
            'coursemodules'    => $exercisenames,
            'sessions'         => $sessions,
            'totalgradedsessions'=> $totalgradedsessions,
            'comment'          => $this->instructor->get_comment(),
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
