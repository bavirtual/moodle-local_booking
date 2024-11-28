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

        // get the exercise id (assignment id) for the practical exam assignment and its grade
        $exerciseid = $this->course->get_graduation_exercise();
        $grade = $this->student->get_grade($exerciseid, true);
        $attemptcount = count($grade->attempts);

        // write course name
        $this->SetFont($this->fontfamily, 'B', 18);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 0, $this->course->get_exercise_name($exerciseid), 0, 1, 'C', 1);

        // write student name and VATSIM ID
        $this->SetTextColor(0,0,0);
        $this->SetFont($this->fontfamily, '', 12);
        $this->Ln(10);
        $vatsimid = $this->student->get_profile_field('vatsimid') ?: get_string('notfound', 'local_booking');
        $html = '<h3>' . $this->student->get_name() . '</h3>';
        $html .= '<span style="font-size: small;">' . get_string('vatsimid', 'local_booking') . ': ';
        $html .= (!empty($vatsimid) ? $vatsimid : get_string('vatsimidmissing', 'local_booking')) . '</span><br />';
        $html .= '<span style="font-size: small;">' . get_string('attempts', 'local_booking') . ': ';
        $html .= $attemptcount . '</span><br /><hr />';
        $this->writeHTML($html, true, false, true);

        // write each attempt
        $attmptcntr = 0;
        foreach ($grade->attempts as $attempt) {
            // examiner information
            $attmptcntr++;
            $examiner = participant::get_fullname($attempt->grader);
            $html = '';
            if ($attemptcount > 1) {
                $html .= '<p><span style="font-size: small;">' . get_string('attempt', 'local_booking') . ': ' . ($attempt->attemptnumber + 1) . '</span><p/><br />';
            }
            $html .= '<p><span style="font-weight: bold;">' . get_string('instructor', 'local_booking') . ': ' . $examiner . '</span><br />';
            $html .= '<span style="font-weight: bold;">' . get_string('logbookdate', 'local_booking') . ':</span>&nbsp;';
            $html .= '<span style="font-weight: normal;">' . (new \DateTime('@'.$attempt->timemodified))->format('M d\, Y') . '</span></p>';
            $this->SetFont($this->fontfamily, 'B', 12);
            $this->SetTextColor(0, 0, 0);
            $this->writeHTML($html, true, false, true);

            // write logbook entry header for the exercise
            $this->write_logentry_info($exerciseid, true);

            // write grade and feedback information
            $html = $this->get_grade_info($grade, $attempt->attemptnumber);
            $html .= $this->get_feedback_text($grade, $attempt->attemptnumber);
            $this->SetTextColor(0, 0, 0);
            $this->Ln(140);
            $this->writeHTML($html, true, false, true);
            if ($attemptcount > 1 && $attmptcntr < $attemptcount) {
                $this->AddPage();
            }
        }
    }
}
