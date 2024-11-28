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

use ArrayObject;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing subscribed courses
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_report_theoryexam extends pdf_report {

    /**
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     */
    public function __construct(subscriber $course, student $student) {
        parent::__construct($course, $student, 'theoryexam');
    }

    /**
     * Write the report content section.
     *
     */
    public function WriteContent() {

        // write the parent intro
        parent::WriteContent();

        // get the the exams for a user
        $studentexams = $this->student->get_quize_grades();
        $vatsimid = $this->student->get_profile_field('vatsimid') ?: get_string('notfound', 'local_booking');

        // iterate through all the attempts
        foreach ($studentexams as $exam) {
            // student exam details
            $scoredata = [
                'ato'   => get_config('local_booking', 'atoname'),
                'coursename' => $this->course->get_shortname(),
                'studentname' => $this->student->get_name(false),
                'vatsimid' => $vatsimid,
                'attempts' => count($exam->attempts),
                'score' => intval($exam->finalgrade),
                'total' => intval($exam->get_grade_max()),
                'percent' => intval(($exam->finalgrade / $exam->get_grade_max()) * 100),
            ];
            $scorenote = get_string('mentorreportdesc', 'local_booking', $scoredata);

            // theory exam report information
            $attempts = (new ArrayObject($exam->attempts))->getIterator();
            if (count($exam->attempts) > 0) {
                $attempts->seek(count($exam->attempts)-1);
                $starttime = new \Datetime('@' . $attempts->current()->timestart);
                $endtime = new \Datetime('@' . $attempts->current()->timefinish);
                $interval = $starttime->diff($endtime);
                $duration = $interval->format('%H:%I:%S');
                } else {
                $starttime = $endtime = new \Datetime('@' . $exam->timemodified);
                $duration = 'Manually graded!';
            }

            $this->SetTextColor(255,255,255);
            $this->SetFillColor(100,149,237);
            $this->SetFont($this->fontfamily, 'B', 18);
            $this->Cell(0, 0, $exam->grade_item->itemname, 0, 1, 'C', 1);

            // write student name and VATSIM ID
            $this->SetTextColor(0,0,0);
            $this->SetFont($this->fontfamily, '', 12);
            $this->Ln(50);
            $vatsimid = $this->student->get_profile_field('vatsimid') ?: get_string('notfound', 'local_booking');
            $html = '<h3>' . $this->student->get_name() . '</h3>';
            $html .= '<span style="font-size: small;">' . get_string('vatsimid', 'local_booking') . ': ';
            $html .= (!empty($vatsimid) ? $vatsimid : get_string('vatsimidmissing', 'local_booking')) . '</span>';
            $this->writeHTML($html, true, false, true);

            // write exam information
            $html = '<br /><p>';
            $html = '<table width="300px" cellspacing="2" cellpadding="2">';
            $html .= '<tr><td style="font-weight: bold; width: 100">' . get_string('gradescore', 'local_booking') . ':</td>';
            $html .= '<td style="width: 200">' . intval($exam->finalgrade) . ' / ' . intval($exam->get_grade_max()) . '</td></tr><br />';
            $html .= '<tr><td style="font-weight: bold; width: 100">' . get_string('examdate', 'local_booking') . ':</td>';
            $html .= '<td style="width: 200">' . $starttime->format('F m\, Y') . '</td></tr>';
            $html .= '<tr><td style="font-weight: bold; width: 100">' . get_string('examstart', 'local_booking') . ':</td>';
            $html .= '<td style="width: 200">' . $starttime->format('H:i \z') . '</td></tr>';
            $html .= '<tr><td style="font-weight: bold; width: 100">' . get_string('examend', 'local_booking') . ':</td>';
            $html .= '<td style="width: 200">' . $endtime->format('H:i \z') . '</td></tr>';
            $html .= '<tr><td style="font-weight: bold; width: 100">' . get_string('duration', 'local_booking') . ':</td>';
            $html .= '<td style="width: 200">' . $duration . '</td></tr></table></p>';
            $html .= '<p>' . $scorenote . '</p>';

            $this->Ln(30);
            $this->writeHTML($html, true, false, true);
        }
    }
}
