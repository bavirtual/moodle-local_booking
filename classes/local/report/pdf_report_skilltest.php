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
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing subscribed courses
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_report_skilltest extends pdf_report {

    /**
     * @var array $alphabets    An alphabets array to sequence each exam subsection (rubrics)
     */
    protected $alphabets;

    /**
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     */
    public function __construct(subscriber $course, student $student) {
        $this->alphabets = range('a', 'z');
        parent::__construct($course, $student, 'examiner');
    }

    /**
     * Write the report content section.
     *
     */
    public function WriteContent() {

        // header information
        $testexerciseid = $this->course->get_graduation_exercise();
        $grades = $this->student->get_exercises();
        $testdatets = $grades[$testexerciseid]->gradedate;
        $testdate = (new \DateTime('@'.$testdatets))->format('d//m//Y');
        $instructor = new instructor($this->course, $grades[$testexerciseid]->instructorid);
        list($sections, $maxsubsections) = $this->student->get_skilltest_assessment();

        // Write Candidate and Examiner Details section
        $this->writeDetailsSection($instructor, $testdate, $testexerciseid);

        // Flight Test section
        $this->writeFlightTestSection($sections, $maxsubsections, $testexerciseid);

        // Approved Training Organization section
        $this->writeATOInfo();

        // Examiner and candidate details
        $this->writeExaminerSection($sections);

        // Examiner Report: Failure test header
        $this->writeFailuresSection($instructor, $sections, $testdate);

        // Appeals section
        $this->writeAppealsSection($sections);
    }

    /**
     * Write Candidate and Examiner Details section table html.
     *
     * @param instructor $instructor     The instructor object
     * @param string     $testdate       Test date
     * @param int        $testexerciseid Test test exercise id
     */
    private function writeDetailsSection(instructor $instructor, string $testdate, int $testexerciseid) {
        // title section
        // start table
        $this->SetTextColor(0,0,0);
        $this->SetFont($this->fontfamily, 'B', 12);

        $title = get_string('examinerreportfor', 'local_booking') . ' ' . $this->course->get_fullname() . ' ' . get_string('skilltest', 'local_booking');

        // Candidate Details section
        $html = '<table width="100%" style="border-collapse: collapse;" cellpadding="2">';
        $html .= '<tr>';
        $html .= '<td colspan="2" height="45" style="border: 1px solid black; font-size: large; font-weight: bold; background-color: #c6d9f0;">' . $title . '</td>';
        $html .= '</tr><tr>';
        $html .= '<td colspan="2" style="border: 1px solid black; font-size: medium; font-weight: bold; background-color: #c6d9f0;">' . get_string('candidatedetails', 'local_booking') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td width="50%" style="border-left: 1px solid black;">' . get_string('name') . ': ' . $this->student->get_name(false) . '</td>';
        $html .= '<td width="50%" style="border-right: 1px solid black;">' . get_string('vatsimid', 'local_booking') . ': ' . $this->student->get_profile_field('VATSIMID') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td width="50%" style="border-left: 1px solid black; border-bottom: 1px solid black;">' . get_string('testdate', 'local_booking') . ': ' . $testdate . '</td>';
        $html .= '<td width="50%" style="border-right: 1px solid black; border-bottom: 1px solid black;">' . get_string('attempt', 'local_booking') . ': ' . $this->student->get_exercise_attempts($testexerciseid) . '</td>';
        $html .= '</tr>';

        // Examiner Details section
        $html .= '<tr>';
        $html .= '<td colspan="2" style="border: 1px solid black; font-size: medium; font-weight: bold; background-color: #c6d9f0;">' . get_string('examinerdetails', 'local_booking') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td width="50%" style="border-left: 1px solid black; border-bottom: 1px solid black;">' . get_string('name') . ': ' . $instructor->get_name(false) . '</td>';
        $html .= '<td width="50%" style="border-right: 1px solid black; border-bottom: 1px solid black;">' . get_string('vatsimid', 'local_booking') . ': ' . $instructor->get_profile_field('VATSIMID') . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $this->writeHTML($html, true, false, true);
    }

    /**
     * Write Flight Test section table html.
     *
     * @param array  $sections       The flight test sections
     * @param int    $maxsubsections The maximum number of subsecitons in each section
     * @param int    $testexerciseid Test test exercise id
     */
    private function writeFlightTestSection(array $sections, int $maxsubsections, int $testexerciseid) {
        // flight information
        $logbook = new logbook($this->course->get_id(), $this->student->get_id());
        $logentry = $logbook->get_logentry(0, $testexerciseid);
        $deptime = new Datetime('@'.$logentry->get_deptime());
        $arrtime = new Datetime('@'.$logentry->get_arrtime());
        $interval = $deptime->diff($arrtime);
        $duration = $interval->format('%H:%I');
        $sectionscount = 0;

        // Flight Test section
        $html = '<p>';
        $html .= '<table width="100%" style="border-collapse: collapse;" cellpadding="2">';
        $html .= '<tr>';
        $html .= '<td colspan="6" height="35" style="border: 1px solid black; font-size: medium; font-weight: bold; ';
        $html .= 'background-color: #c6d9f0;">' . get_string('flightttest', 'local_booking');
        $html .= '<br /><span style="font-size: 10; font-weight: normal; text-align: right">' . get_string('tobecompletedbyexaminer', 'local_booking') . '</span></td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td colspan="6" style="border: 1px solid black;">' . get_string('route', 'local_booking') . ': ';
        $html .= $logentry->get_remarks() . '</td>';
        $html .= '</tr><tr style="font-weight: normal;">';
        $html .= '<td colspan="2" style="border-left: 1px solid black;">' . get_string('aircrafttype', 'local_booking') . ': ' . $logentry->get_aircraft() . '</td>';
        $html .= '<td>' . get_string('blocktimes', 'local_booking') . ':</td>';
        $html .= '<td>' . get_string('departz', 'local_booking') . ':</td>';
        $html .= '<td>' . get_string('arrivez', 'local_booking') . ':</td>';
        $html .= '<td style="border-right: 1px solid black;">' . get_string('total') . ':</td>';
        $html .= '</tr><tr style="font-weight: normal;">';
        $html .= '<td colspan="2" style="border-left: 1px solid black;">' . get_string('registration', 'local_booking') . ': ' . $logentry->get_aircraftreg() . '</td>';
        $html .= '<td></td><td>' . $deptime->format('H:i') . '</td>';
        $html .= '<td>' . $arrtime->format('H:i') . '</td>';
        $html .= '<td style="border-right: 1px solid black;">' . $duration . '</td></tr>';
        $html .= '<tr style="font-weight: normal;">';
        $html .= '<td width="25%" style="border: 1px solid black;">' . get_string('testsections', 'local_booking') . ':</td>';

        // check if there are no sections
        if (empty($maxsubsections))
            $html .= '<td colspan="4" width="75%" style="border: 1px solid black;"></td>';

        // Test sections
        foreach ($sections as $section) {
            if (!empty($section->sequence)) {
                $sectionscount++;
                $html .= '<td width="15%" style="border: 1px solid black; text-align: center;">' . $section->sequence . '</td>';
            }
        }

        $html .= '</tr>';
        $html .= '<tr style="font-weight: normal;">';
        $html .= '<td width="25%" style="border: 1px solid black;">' . get_string('sectionstotake', 'local_booking') . ':</td>';

        // check if there are no sections
        if (empty($maxsubsections))
            $html .= '<td colspan="4" style="border: 1px solid black;"></td>';

        // Test sections to be covered
        foreach ($sections as $section) {
            if (!empty($section->sequence))
                $html .= '<td width="15%" style="vertical-align: middle; font-weight: bold; text-align: center; border: 1px solid black;"><font face="zapfdingbats">4</font></td>';
        }

        $html .= '</tr>';
        $html .= '<tr style="font-weight: normal; background-color: #c6d9f0;">';
        $html .= '<td width="25%" style="border: 1px solid black; font-weight: bold;">' . get_string('results', 'local_booking') . ':</td>';

        // Test sections grades
        // check if there are no sections
        if (empty($maxsubsections))
            $html .= '<td colspan="4" style="border: 1px solid black;">' . array_values($sections)[0]->grade . '</td>';

        // write all sections grades
        foreach ($sections as $section) {
            if (!empty($section->sequence)) {
                $grade = $section->grade >= $section->maxgrade ? get_string('pass', 'local_booking') : get_string('fail', 'local_booking');
                $html .= '<td width="15%" style="border: 1px solid black; text-align: center;">' . $grade . '</td>';
            }
        }
        $html .= '</tr>';

        // Test sub-sections grades
        $incompletefeedbacks = array();
        $incompletesubsections = array();
        $failedsubsections = array();

        // loop through all subsections (rows)
        for ($i = 0; $i < $maxsubsections; $i++) {

            $html .= '<tr>';
            $html .= '<td width="25%" style="font-weight: normal; border: 1px solid black; text-align: right;">(' . $this->alphabets[$i] . ')</td>';

            // loop through all sections (cols)
            foreach ($sections as $section) {

                $grade = '';

                // skip exercises without subsections (non-rubric)
                if (!empty($section->sequence)) {

                    $subsection = count($section->subsections)>$i ? array_values($section->subsections)[$i] : null;

                    // get grade and join subsection feedbacks
                    if (!empty($subsection)) {

                        $grade = $subsection->grade != get_string('fail', 'local_booking') ? get_string('pass', 'local_booking') : get_string('fail', 'local_booking');

                        // get failed subsections
                        if ($subsection->grade == get_string('fail', 'local_booking')) {

                            // concatenate sections not commpleted feedback
                            if (array_key_exists($section->sequence, $failedsubsections))
                                $failedsubsections[$section->sequence] .= '(' . $this->alphabets[$i] . ') ';
                            else
                                $failedsubsections[$section->sequence] = '(' . $this->alphabets[$i] . ') ';
                        }

                        // get subsections not completed
                        if ($subsection->grade == get_string('notcompleted', 'local_booking')) {

                            // concatenate sections not commpleted feedback
                            if (array_key_exists($section->sequence, $incompletesubsections))
                                $incompletesubsections[$section->sequence] .= '(' . $this->alphabets[$i] . ') ';
                            else
                                $incompletesubsections[$section->sequence] = '(' . $this->alphabets[$i] . ') ';

                            // concatenate sections not commpleted
                            if (array_key_exists($section->sequence, $incompletefeedbacks))
                                $incompletefeedbacks[$section->sequence] .= !empty($subsection->feedback) ? $subsection->feedback . ', ' : '' ;
                            else
                                $incompletefeedbacks[$section->sequence] = !empty($subsection->feedback) ? $subsection->feedback . ', ' : '' ;
                        }
                    }

                    // write grade
                    $html .= '<td width="15%" style="font-weight: normal; border: 1px solid black; ';
                    $html .= (empty($grade) ? 'background-color: #c6d9f0; ' : '') . 'text-align: center;"><div style="align: center;">' . $grade . '</div></td>';
                }
            }
            $html .= '</tr>';
        }

        // Re-test sections
        $html .= '<tr style="font-weight: normal; background-color: white;">';
        $html .= '<td width="25%" style="border: 1px solid black;">' . get_string('retestsections', 'local_booking') . ':</td>';

        // check if there are no sections
        if (empty($maxsubsections))
            $html .= '<td colspan="4" style="border: 1px solid black;"></td>';

        foreach ($sections as $section) {
            if (!empty($section->sequence)) {
                $restest = $section->grade >= $section->maxgrade ? '': '4';
                $html .= '<td width="15%" style="vertical-align: middle; font-weight: bold; text-align: center; border: 1px solid black;"><font face="zapfdingbats">' . $restest . '</font></td>';
            }
        }
        $html .= '</tr>';

        // Test sections incomplete feedback
        $html .= '<tr style="font-weight: normal; background-color: white;">';
        $html .= '<td width="25%" style="border: 1px solid black;">' . get_string('testsectionsincomplete', 'local_booking') . ':</td>';

        // check if there are no sections
        if (empty($maxsubsections))
            $html .= '<td colspan="4" style="border: 1px solid black; text-align: center;"></td>';

        // concatenate subsection feedbacks
        foreach ($sections as $section) {

            if (!empty($section->sequence)) {
                $incompletefeedbackstxt = array_key_exists($section->sequence, $incompletefeedbacks) ? $incompletefeedbacks[$section->sequence] : '';
                $html .= '<td width="15%" style="font-weight: normal; border: 1px solid black; text-align: left;">' . substr($incompletefeedbackstxt, 0, -2) . '</td>';
            }
        }
        $html .= '</tr>';

        // Test sections not complete
        $html .= '<tr style="font-weight: normal; background-color: white;">';
        $html .= '<td width="25%" style="border: 1px solid black;">' . get_string('itemsnotcompleted', 'local_booking') . ':</td>';

        // check if there are no sections
        if (empty($maxsubsections))
            $html .= '<td colspan="4" style="border: 1px solid black;"></td>';

        // concatenate subsection feedbacks
        foreach ($sections as $section) {

            if (!empty($section->sequence)) {
                $incompletesubsectionstxt = !empty($incompletesubsections[$section->sequence]) ? $incompletesubsections[$section->sequence] : '';
                $html .= '<td width="15%" style="font-weight: normal; border: 1px solid black; text-align: left;">' . trim($incompletesubsectionstxt) . '</td>';
            }
        }
        $html .= '</tr>';

        // Test sections not complete
        $colspan = !empty($maxsubsections) ? $sectionscount+1 : 5;
        $html .= '<tr style="font-weight: normal; background-color: white;">';
        $html .= '<td colspan="' . $colspan . '" style="border: 1px solid black;">' . get_string('completionconfirmation', 'local_booking');
        $html .= ':  <font face="zapfdingbats">4</font><br />' . get_string('overallresult', 'local_booking') . ': <strong>' . array_values($sections)[0]->grade . '</strong></td>';

        // end Flight Test section
        $html .= '</tr>';
        $html .= '</table>';

        $this->writeHTML($html, true, false, true);
    }

    /**
     * Write ATO information table html.
     *
     */
    private function writeATOInfo() {
        $endorserid = get_user_preferences('local_booking_' . $this->course->get_id() . '_endorser', '', $this->student->get_id());
        $endorsinginstructor = new instructor($this->course, $endorserid);

        // Approved Training Organization section
        $html = '<p>';
        $html .= '<table width="100%" style="border-collapse: collapse; ">';
        $html .= '<tr>';
        $html .= '<td colspan="2" style="border: 1px solid black; font-size: medium; font-weight: bold; background-color: #c6d9f0;">' . get_string('approvedato', 'local_booking') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td width="50%" style="border-left: 1px solid black;">' . get_string('ato', 'local_booking') . ': ' . $this->course->ato->name . ' ' . get_string('flighttraining', 'local_booking') . '</td>';
        $html .= '<td width="50%" style="border-right: 1px solid black;">' . get_string('trainingcompelete', 'local_booking') . ': ' . $this->student->get_last_graded_date()->format('d//m//Y') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td colspan="2" style="border-left: 1px solid black; border-right: 1px solid black; border-bottom: 1px solid black;">' . get_string('recommendedby', 'local_booking') . ': ';
        $html .= $endorsinginstructor->get_name() . ' (' . get_string('vatsimid', 'local_booking') . ': ' . $endorsinginstructor->get_profile_field('VATSIMID') . ')</td>';

        // end Approved Training Organization section
        $html .= '</tr>';
        $html .= '</table>';

        $this->writeHTML($html, true, false, true);
    }

    /**
     * Write Examiner details section table html.
     *
     * @param array $sections   The skill test sections
     */
    private function writeExaminerSection(array $sections) {
        // Examiner Report: Failure test header
        $this->AddPage();
        $html = '<table width="100%" style="border-collapse: collapse; ">';
        $html .= '<tr>';
        $html .= '<td colspan="2" style="border: 1px solid black; font-size: large; font-weight: bold; background-color: #c6d9f0;">' .
        get_string('examinerfailsection', 'local_booking') . ' ' . $this->course->skilltestexercise . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Examiner Report: Failure test header
        $html .= '<p>';
        $html .= '<table width="100%" style="border-collapse: collapse; ">';
        $html .= '<tr style="font-weight: medium; font-weight: bold; background-color: #c6d9f0;">';
        $html .= '<td style="border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">';
        $html .= get_string('failurereasons', 'local_booking') . '</td>';
        $html .= '<td colspan="2" style="border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black; text-align: right;">';
        $html .= get_string('tobecompletedbyexaminer', 'local_booking') . '</td>';
        $html .= '</tr><tr style="background-color: white;">';
        $html .= '<td width="18%" style="border: 1px solid black;">' . get_string('section', 'local_booking') . '</td>';
        $html .= '<td width="18%" style="border: 1px solid black;">' . get_string('subsection', 'local_booking') . '</td>';
        $html .= '<td width="64%" style="border: 1px solid black; text-align: center;">' . get_string('failurereasons', 'local_booking') . '</td>';
        $html .= '</tr>';

        // loop through all sections (cols) for failures
        foreach ($sections as $section) {

            if (!empty($section->sequence)) {
                $failedsubsections = !empty($failedsubsections[$section->sequence]) ? $failedsubsections[$section->sequence] : '';
                $html .= '<tr style="font-weight: normal;">';
                if (!empty($failedsubsections)) {
                    $html .= '<td width="18%" height="50px" style="border: 1px solid black; text-align: center;">' . $section->sequence . '</td>';
                    $html .= '<td width="18%" height="50px" style="border: 1px solid black; text-align: center;">' . trim($failedsubsections) . '</td>';
                    $html .= '<td width="64%" height="50px" style="border: 1px solid black; text-align: left;">' . $section->feedback . '</td>';
                } else {
                    $html .= '<td width="18%" height="50px" style="border: 1px solid black; text-align: center;"></td>';
                    $html .= '<td width="18%" height="50px" style="border: 1px solid black; text-align: center;"></td>';
                    $html .= '<td width="64%" height="50px" style="border: 1px solid black; text-align: left;"></td>';
                }
                $html .= '</tr>';
            }
        }
        // end Examiner Report: Failure test sections
        $html .= '</table>';

        $this->writeHTML($html, true, false, true);
    }

    /**
     * Write Examiner Failures section table html.
     *
     * @param instructor $endorsinginstructor   The endorsing instructor object
     * @param array      $sections              The skill test sections
     * @param string     $testdate              Test date
     */
    private function writeFailuresSection(instructor $instructor, array $sections, string $testdate) {
        // Failures section: Futher training
        $index = array_search(get_string('furthertraining', 'local_booking'), array_column($sections, 'name'));
        $exercises = array_values($sections);
        $furthertraining = $exercises[$index];
        $html = '<table width="100%" style="border-collapse: collapse; ">';
        $html .= '<tr>';
        $html .= '<td style="border-left: 1px solid black; border-top: 1px solid black;">'. get_string('furthertraining', 'local_booking') . ':</td>';
        $html .= '<td style="font-weight: normal; border-top: 1px solid black;">';
        $html .= get_string('mandatory', 'local_booking') . ' ' . (($furthertraining->grade == get_string('mandatory', 'local_booking')) ? '<font face="zapfdingbats">4</font>' : '') . '</td>';
        $html .= '<td style="font-weight: normal; border-top: 1px solid black;">';
        $html .= get_string('recommended', 'local_booking') . ' ' . (($furthertraining->grade == get_string('recommended', 'local_booking')) ? '<font face="zapfdingbats">4</font>' : '') . '</td>';
        $html .= '<td style="font-weight: normal; border-top: 1px solid black; border-right: 1px solid black;">';
        $html .= get_string('none') . ' ' . (($furthertraining->grade == get_string('notrequired', 'local_booking')) ? '<font face="zapfdingbats">4</font>' : '') . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="2" style="border-left: 1px solid black; border-bottom: 1px solid black;">'. get_string('specifictraining', 'local_booking') . ':</td>';
        $html .= '<td colspan="2" style="font-weight: normal; border-right: 1px solid black; border-bottom: 1px solid black;">'. $furthertraining->feedback . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Examiner's Details section
        $html .= '<p>';
        $html .= '<table width="100%" style="border-collapse: collapse;" cellpadding="2">';
        $html .= '<tr>';
        $html .= '<td colspan="2" style="border: 1px solid black; font-size: medium; font-weight: bold; background-color: #c6d9f0;">' . get_string('examinerdetails', 'local_booking') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td width="50%" style="border-left: 1px solid black; border-top: 1px solid black;">' . get_string('name') . ': ' . $instructor->get_name(false) . '</td>';
        $html .= '<td width="50%" style="border-right: 1px solid black; border-top: 1px solid black;">' . get_string('vatsimid', 'local_booking') . ': ' . $instructor->get_profile_field('VATSIMID') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td colspan="2" style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black;">';
        $html .= get_string('testdate', 'local_booking') . ': ' . $testdate . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="2" style="border: 1px solid black; font-size: medium; font-weight: bold; background-color: #c6d9f0;">' . get_string('candidatedetails', 'local_booking') . '</td>';
        $html .= '</tr><tr style="font-size: 12; font-weight: normal; background-color: white;">';
        $html .= '<td width="50%" style="border-left: 1px solid black; border-bottom: 1px solid black;">' . get_string('name') . ': ' . $this->student->get_name(false) . '</td>';
        $html .= '<td width="50%" style="border-right: 1px solid black; border-bottom: 1px solid black;">' . get_string('vatsimid', 'local_booking') . ': ' . $this->student->get_profile_field('VATSIMID') . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Exam Report copies
        $html .= '<p>';
        $html .= '<table width="100%" style="border-collapse: collapse;" cellpadding="2">';
        $html .= '<tr>';
        $html .= '<td style="border: 1px solid black; font-size: medium;">' . get_string('examreportcopies', 'local_booking') . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $this->writeHTML($html, true, false, true);
    }

    /**
     * Write Appeals section table html.
     *
     * @param array      $sections              The skill test sections
     */
    private function writeAppealsSection(array $sections) {
        // Appeals section
        $this->AddPage();
        $html = '<table width="100%" style="border-collapse: collapse; page-break-inside:auto" cellpadding="2">';
        $html .= '<tr>';
        $html .= '<td colspan="4" style="border-left: 1px solid black; border-top: 1px solid black; border-right: 1px solid black; font-size: medium; font-weight: bold;">';
        $html .= get_string('appeals', 'local_booking') . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="4" style="border-left: 1px solid black; border-bottom: 1px solid black; border-right: 1px solid black;  font-size: medium; font-weight: normal;">';
        $html .= get_string('appealstext', 'local_booking') . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="4" style="border: 1px solid black; font-size: medium;">' . get_string('checklistuse', 'local_booking') . '</td>';
        $html .= '</tr>';

        // create two arrays each for one of the two table columns containing all sections and subsections sequentially
        $allsections = [];

        // loop through all sections to get a single array of sections and subsections sequentially
        foreach ($sections as $section) {
            // skip exercises without subsections (non-rubric)
            if (!empty($section->sequence)) {

                $i = 0;
                $allsections[] = '[SECTION]' . $section->name;
                $subsections = $section->subsections;

                foreach ($subsections as $subsection) {
                    $allsections[] = '(' . $this->alphabets[$i++] . ')|' . $subsection->name;
                }
            }
        }

        // split the array into two columns
        $splitidx = round(count($allsections)/2);
        $leftcol = array_slice($allsections, 0, $splitidx);
        $rightcol = array_slice($allsections, $splitidx);
        $tabularsections = $this->combine_sections($leftcol, $rightcol);

        foreach ($tabularsections as $leftcell => $rightcell) {
            $leftissection = strstr($leftcell, '[SECTION]') != '';
            $rightissection = strstr($rightcell, '[SECTION]') != '';
            $html .= '<tr style="page-break-inside:avoid; page-break-after:auto;">';

            // left column
            if ($leftissection)
                $html .= '<td colspan="2" style="border: 2px solid black; font-size: medium; background-color: #c6d9f0;">' . str_replace('[SECTION]', '', $leftcell) . '</td>';
            else{
                $cellvalues = explode('|', $leftcell);
                $html .= '<td width="5%" style="border: 1px solid black; font-size: medium; font-weight: normal; text-align: center;">' . $cellvalues[0] . '</td>';
                $html .= '<td width="45%"  style="border: 1px solid black; font-size: medium; font-weight: normal;">' . $cellvalues[1] . '</td>';
            }

            // left column
            if ($rightissection)
                $html .= '<td colspan="2" style="border: 2px solid black; font-size: medium; background-color: #c6d9f0;">' . str_replace('[SECTION]', '', $rightcell) . '</td>';
            else {
                $cellvalues = !empty($rightcell) ? explode('|', $rightcell) : $cellvalues = ['', ''];
                $html .= '<td width="5%" style="border: 1px solid black; font-size: medium; font-weight: normal; text-align: center;">' . $cellvalues[0] . '</td>';
                $html .= '<td width="45%" style="border: 1px solid black; font-size: medium; font-weight: normal;">' . $cellvalues[1] . '</td>';
            }

            $html .= '</tr>';
        }

        // footnote *
        $html .= '<tr>';
        $html .= '<td colspan="4" style="border: 1px solid black; font-size: medium;">' . get_string('footnote', 'local_booking') . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $this->writeHTML($html, true, false, true);
    }

    /**
     * Combine section and subsection descriptions into
     * a single array containing right and left table
     * columns sequentially.
     *
     * @param array $leftcol    The left table column section description.
     * @param array $rightcol   The right table column section description.
     *
     * @return array $joined    The joined two columns array (key=>value) pairs
     */
    private function combine_sections($leftcol, $rightcol) {
        $joined=[];
        $i = 0;
        foreach ($leftcol as $item) {
            $joined[$item] = !empty($rightcol[$i]) ? $rightcol[$i] : '';
            $i++;
        }
        return $joined;
    }

    /**
     * Overrides the TCPDF header method to write custom image URL.
     *
     */
    public function Footer()
    {
        parent::Footer();

        $footer = get_string('skilltestformver', 'local_booking') . '<br />';
        $footer .= get_string('copyright', 'local_booking', array('ato'=>$this->course->ato->name, 'year'=>(new \Datetime('@'.time()))->format('Y')));
        $this->SetY(-50);
        // Set font
        $this->SetFont('helvetica', '', 10);
        // Page number
        $this->writeHTMLCell(0, 0, '', '', $footer, 0, 0, false,true, "C", true);
    }
}
