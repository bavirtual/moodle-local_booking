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
 * PDF report writer to write the theory exam report to the browser.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\report;

require_once($CFG->dirroot . '/mod/assign/locallib.php');

use DateTime;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\participant\entities\instructor;
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
class pdf_report_recommendletter extends pdf_report {

    /**
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     */
    public function __construct(subscriber $course, student $student) {
        parent::__construct($course, $student, 'recommendation');
    }

    /**
     * Write the report content section.
     *
     */
    public function WriteContent() {

        // change title background colors
        $this->titlebkgrnd = array('R' => 0, 'G' => 0, 'B' => 0);

        // write the parent intro
        parent::WriteContent();

        // get course aircraft information
        $aircraftsused = $this->course->aircrafticao;
        $aircraftslabel = array_values($aircraftsused)[0];
        if ($this->course->has_integration('aircraft')) {
            foreach ($aircraftsused as $aircrafticao) {
                $aircraft = (object) $this->course->get_integrated_data('aircraft', 'aircraftinfo', $aircrafticao);
                $aircraftslabel .= ', ' . $aircraft->description;
            }
        }

        // get student logbook data
        $logbook = new logbook($this->course->get_id(), $this->student->get_id());
        $logbooksummary = (object) $logbook->get_summary(true);
        $totaldualtime = $logbooksummary->totaldualtime;
        $totalpictime = $logbooksummary->totalpictime;

        // recommendation letter info
        $endorserid = get_user_preferences('local_booking_' . $this->course->get_id() . '_endorser', '', $this->student->get_id());
        $instructor = new instructor($this->course, $endorserid);

        $recomendedby = !empty($instructor) ? $instructor->get_name() : get_string('notfound', 'local_booking');
        $recomendedbyVATSIM = !empty($instructor) ? $instructor->get_profile_field('VATSIMID') : get_string('notfound', 'local_booking');
        $recomendedonts = !empty($instructor) ? get_user_preferences('local_booking_' . $this->course->get_id() . '_endorsedate', '', $this->student->get_id()) : get_string('notfound', 'local_booking');
        $recomendedon = !empty($recomendedonts) ? (new DateTime('@'.$recomendedonts))->format('M j\, Y') : get_string('notfound', 'local_booking');

        // write student name and VATSIM ID
        $this->SetTextColor(0,0,0);
        $this->SetFont($this->fontfamily, '', 11);
        $this->Ln(25);
        $vatsimid = $this->student->get_profile_field('VATSIMID');

        // start borderless table
        $html = '<table width="600" cellspacing="2" cellpadding="2">';

        // Candidate's details
        $html .= '<tr>';
        $html .= '<td colspan="2" style="font-size: x-large; font-weight: bold; text-decoration: underline">' . get_string('candidatedetails', 'local_booking') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td width="180" style="font-weight: bold;">' . get_string('candidatename', 'local_booking') . ':</td><td>' . $this->student->get_name() . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('vatsimid', 'local_booking') . ':</td><td>' . $vatsimid . '</td>';
        $html .= '</tr><tr><br />';

        // Training Contents
        $html .= '<td colspan="2" style="font-size: x-large; font-weight: bold; text-decoration: underline">' . get_string('trainingcontent', 'local_booking') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('ratinglabel', 'local_booking') . ':</td><td>' . $this->course->get_fullname() . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('datecommenced', 'local_booking') . ':</td><td>' . $this->student->get_enrol_date()->format('M j\, Y') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('datecompleted', 'local_booking') . ':</td><td>' . $this->student->get_last_graded_date()->format('M j\, Y') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('aircrafttypelabel', 'local_booking') . ':</td><td>' . $aircraftslabel . '</td>';
        $html .= '</tr><tr><br />';

        // Candidate Flying Hours
        $html .= '<td colspan="2" style="font-size: x-large; font-weight: bold; text-decoration: underline">' . get_string('candidateflyinghours', 'local_booking') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('dualflight', 'local_booking') . ':</td><td>' . $totaldualtime . ' ' . get_string('hours') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('otherflyingtime', 'local_booking') . ':</td><td>' . $totalpictime . ' ' . get_string('hours') . '</td>';
        $html .= '</tr><tr><br />';

        // Skill Test
        $html .= '<td colspan="2" style="font-size: x-large; font-weight: bold; text-decoration: underline">' . get_string('skilltest', 'local_booking') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('recommendedby', 'local_booking') . ':</td><td>' . $recomendedby . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td style="font-weight: bold;">' . get_string('vatsimid', 'local_booking') . ':</td><td>' . $recomendedbyVATSIM . '</td>';
        $html .= '</tr><tr><br />';

        // Declaration
        $html .= '<td colspan="2" style="font-size: x-large; font-weight: bold; text-decoration: underline">' . get_string('declarationtlabel', 'local_booking') . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td colspan="2">' . get_string('declarationtext', 'local_booking', array('ato' => $this->course->ato->name)) . '</td>';
        $html .= '</tr><tr><br />';

        // Signature
        $html .= '<td><strong>' . get_string('name') . ':</strong> ' . $instructor->get_name(false) . '</td>';
        $html .= '<td><strong>' . get_string('date') . ':</strong> ' . $recomendedon . '</td>';
        $html .= '</tr></table>';

        $this->writeHTML($html, true, false, true);
    }
}
