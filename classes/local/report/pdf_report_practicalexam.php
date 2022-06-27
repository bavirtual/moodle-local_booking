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
 * PDF report writer to write the practical exam report to the browser.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\report;

require_once($CFG->dirroot . '/mod/assign/locallib.php');

use local_booking\local\logbook\entities\logbook;
use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing subscribed courses
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_report_practicalexam extends pdf_report {

    /**
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     */
    public function __construct(subscriber $course, student $student) {
        parent::__construct($course, $student, 'practicalexam');
    }

    /**
     * Write the practical examination report content.
     *
     */
    public function WriteContent() {

        // write the parent intro
        parent::WriteContent();

        // get the exercise id (assignment id) for the practical exam assignment
        $exerciseid = $this->course->get_graduation_exercise();

        // write course name
        $this->SetFont($this->fontfamily, 'B', 18);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 0, $this->course->get_exercise_name($exerciseid), 0, 1, 'C', 1);

        // get the the logentry for the practical exam
        $logbook = new logbook($this->course->get_id(), $this->student->get_id());
        $logbook->load();
        $logbooksummary = (object) $logbook->get_summary(true);
        $logentry = $logbook->get_logentry_by_exericseid($exerciseid);

        // add entries to flight time array
        $flighttimes = array();
        $flighttimes['dualtime'][0] = !empty($logentry) ? $logentry->get_dualtime(false) : 0;
        $flighttimes['dualtime'][1] = $logbooksummary->totaldualtime;
        $flighttimes['solotime'][0] = !empty($logentry) ? $logentry->get_pictime(false) : 0;
        $flighttimes['solotime'][1] = $logbooksummary->totalpictime;
        $flighttimes['groundtime'][0] = !empty($logentry) ? $logentry->get_groundtime(false) : 0;
        $flighttimes['groundtime'][1] = $logbooksummary->totalgroundtime;
        $flighttimes['landingsday'][0] = !empty($logentry) ? $logentry->get_landingsday() : 0;
        $flighttimes['landingsday'][1] = $logbooksummary->totallandingsday;
        $flighttimes['sessionlength'][0] = !empty($logentry) ? $logentry->get_totaltime(false) : 0;
        $flighttimes['sessionlength'][1] = '';

        // write student name and VATSIM ID
        $this->SetTextColor(0,0,0);
        $this->SetFont($this->fontfamily, '', 12);
        $this->Ln(10);
        $vatsimid = $this->student->get_profile_field('VATSIMID');
        $html = '<h3>' . $this->student->get_name() . '</h3>';
        $html .= '<span style="font-size: small;">' . get_string('vatsimid', 'local_booking') . ': ';
        $html .= (!empty($vatsimid) ? $vatsimid : get_string('vatsimidmissing', 'local_booking')) . '</span>';
        $this->writeHTML($html, true, false, true);

        // examiner information
        $examiner = '';
        if (!empty($logentry))
            $examiner = participant::get_fullname($logentry->get_p1id());
        $html = '<p><h4>' . get_string('examiner', 'local_booking') . ': ' . $examiner . '</h4></p>';
        $this->SetFont($this->fontfamily, 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->writeHTML($html, true, false, true);

        // logbook information
        $html = '<table width="400px" cellspacing="2" cellpadding="2">';
        $html .= '<tr style="border: 1px solid black; border-style: dotted;">';
        $html .= '<td></td><td style="font-weight: bold; width: 100px">' . ucfirst(get_string('flighttime', 'local_booking'));
        $html .= '</td><td style="font-weight: bold; width: 100px">' . get_string('cumulative', 'local_booking') . '</td></tr>';
        foreach ($flighttimes as $key => $flightdata) {
            $html .= '<tr style="border: 1px solid black; border-style: dotted;">';
            $html .= '<td><strong>' . get_string($key, 'local_booking') . '</strong></td><td>' . $flightdata[0] . '</td><td>' . $flightdata[1] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table><br />';

        // write the flight logbook entries table
        $this->SetFont($this->fontfamily, '', 12);
        $this->SetTextColor(72, 79, 87);
        $this->writeHTMLCell(0, 0, 50, 300, $html, array('LRTB' => array(
            'width' => 1,
            'dash'  => 1,
            'color' => array(144, 145, 145)
        )));

        // feedback comments
        $html = '<strong>' . get_string('feedback', 'local_booking') . ':</strong>';
        $html .= $this->get_feedback_text($exerciseid);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(160);
        $this->writeHTML($html, true, false, true);
    }
}
