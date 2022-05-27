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
 * Class for displaying students profile grades.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use core\external\exporter;
use local_booking\local\participant\entities\student;

/**
 * Class for pdf report content.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_theoryexam_exporter extends exporter {

    /**
     * Constructor.
     *
     * @param mixed $data An array of student progress data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related = []) {
        global $COURSE;

        $student = new student($COURSE->subscriber, $data['userid']);
        $exams = $student->get_exams();
        $scoredata = [
            'ato'   => $COURSE->subscriber->ato,
            'coursename' => $COURSE->subscriber->shortname,
            'studentname' => $student->get_name(),
            'vatsimid' => \core_user::get_user_field_name('vatsimid'),
            'attempts' => $exam->attempts,
            'score' => $exam->score,
        ];

        $scorenote = get_string('mentorreportdesc', 'local_booking', $scoredata);

        // $studentfullname = participant::get_fullname($this->data['userid'], false);
        $data['ato'] = $COURSE->subscriber->ato->name;
        $data['coursename'] = $COURSE->subscriber->fullname;
        $data['courseshortname'] = $COURSE->subscriber->shortname;
        $data['reportdate'] = (new \DateTime())->format('l M j \a\t H:i \z\u\l\u');
        $data['studentname'] = $student->get_name();
        $data['examstart'] = $exam->startdate;
        $data['examend'] = $exam->enddate;
        $data['examduration'] = $exam->duration;
        $data['scoringnote'] = $scorenote;

        parent::__construct($data, $related);
    }


    protected static function define_properties() {
        return [
            'ato' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'coursename' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'courseshortname' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'reportdate' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'fullname' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'examstart' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'examend' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'examduration' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
            'scoringnote' => [
                'type' => PARAM_RAW,
                'optional' => true,
            ],
        ];
    }
}
