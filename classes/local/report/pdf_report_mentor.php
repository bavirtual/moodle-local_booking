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
 * PDF report writer to write a the mentored sessions report to the browser.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\report;

use local_booking\local\participant\entities\participant;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\grade;
use local_booking\local\subscriber\entities\subscriber;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing subscribed courses
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_report_mentor extends pdf_report {

    /**
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     */
    public function __construct(subscriber $course, student $student) {
        parent::__construct($course, $student, 'mentor');
    }

    /**
     * Write the practical examination report content.
     *
     */
    public function WriteContent() {

        // write the parent intro
        parent::WriteContent();

        // get student exercise grades
        $exercisegrades = $this->student->get_exercise_grades();
        $totalexercisegrades = count($exercisegrades);
        $counter = 0;

        // go through student's exercise grades
        foreach ($exercisegrades as $exerciseid => $grade) {
            // write exercise contents
            $this->WriteExerciseContent($exerciseid, $grade);

            // counter to track page counts
            $counter++;
            // break page if not on the last page
            if ($counter < $totalexercisegrades)
                $this->AddPage();
        }
    }


    /**
     * Write the practical examination report content.
     *
     * @param int @exerciseid The exercise id for the grade to be written.
     * @param grade @grade    The exercise grade to be written.
     */
    protected function WriteExerciseContent(int $exerciseid, grade $grade) {

        // write course name
        $this->SetFont($this->fontfamily, 'B', 18);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 0, $this->course->get_exercise_name($exerciseid), 0, 1, 'C', 1);

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
        $examiner = participant::get_fullname($grade->usermodified);
        $html = '<p><br /><span style="font-weight: bold;">' . get_string('instructor', 'local_booking') . ': ' . $examiner . '</span><br />';
        $html .= '<span style="font-weight: bold;">' . get_string('logbookdate', 'local_booking') . ':</span>&nbsp;';
        $html .= '<span style="font-weight: normal;">' . (new \DateTime('@'.$grade->get_dategraded()))->format('M d\, Y') . '</span></p>';
        $this->SetFont($this->fontfamily, 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->writeHTML($html, true, false, true);

        // write logbook entry header for the exercise
        $examexercise = $exerciseid == $this->course->get_graduation_exercise();
        $this->write_logentry_info($exerciseid, $examexercise);

        // get grade and feedback information
        $html = $this->get_grade_info($grade);
        $html .= $this->get_feedback_text($grade);
        $this->SetTextColor(0, 0, 0);
        $this->Ln($examexercise ? 140 : 200);
        $this->writeHTML($html, true, false, true);
    }
}
