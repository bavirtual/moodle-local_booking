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

require_once($CFG->dirroot.'/local/booking/lib/fpdm/fpdm.php');

use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\participant\entities\examiner;
use local_booking\local\participant\entities\student;
use local_booking\local\session\entities\grade;
use FPDM;

defined('MOODLE_INTERNAL') || die();

/**
 * Opens the examiner's evaluation form after the skill test
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_report_skilltest extends pdf_report {

    /**
     * @var int $attempt The attempt associated with the skill test.
     */
    protected $attempt;

    /**
     * Constructor.
     *
     * @param subscriber $course    The report course.
     * @param student    $student   The student for the report.
     * @param int        $attempt   The exam attempt number.
     */
    public function __construct(subscriber $course, student $student, int $attempt) {
        parent::__construct($course, $student, 'evalform');
        $this->attempt = $attempt;
    }

    /**
     * Generate the report and output it to the browser.
     *
     * @param bool $coverpage      Whether to include a coverpage that doesn't have a logo
     */
    public function Generate(bool $coverpage = false) {

        $grade = $this->student->get_grade($this->course->get_graduation_exercise(), true);

        // generate the examiner evaluation form if it doesn't exist
        if (!$pdffilename = $grade->get_feedback_file('assignfeedback_file', 'feedback_files', '', true, $this->attempt)) {
            if (!$outputform = $this->generate_evaluation_form($grade)) {
                // throw new \Error(get_string('errorexaminerevalformunable', 'local_booking'));
                return;
            } else {
                // upload the form to the graded exercise
                $pdffilename = $grade->save_feedback_file($outputform, $this->attempt);
            }
        }

        $filename = pathinfo($pdffilename)['filename'];
        $path = pathinfo($pdffilename)['dirname'];

        try {
            $pdf = new FPDM($pdffilename);

        } catch (\Exception $e) {
            // try again and fail if still not fixed
            try {

                // attempt to fix the none standard FPDM file
                exec("pdftk $pdffilename output $path/fixed.pdf");
                exec("mv $path/fixed.pdf $pdffilename");
                $pdf = new FPDM($pdffilename);

            } catch (\Exception $e) {

                // output error and redirect to file location
                echo get_string('errorredirecttofile', 'local_booking');
                $redirecturl = '/pluginfile.php/' . $grade->get_context()->id .'/assignfeedback_file/feedback_files/' . $grade->get_user_grade_attempt($this->attempt)->id . '/' . $filename;
                redirect(new moodle_url($redirecturl, ['forcedownload'=>'1']));
                die('<b>FPDF-Merge Error:</b> '.$e->getMessage());
            }

        }
        $pdf->Output();
    }

    /**
     * Generate the Examiner Evaluation Form
     *
     * @param grade $grade    The exam grade
     * @return bool true/false
     */
    public function generate_evaluation_form(grade $grade) {
        try {
            // check for logentry recorded before proceeding
            if (!$logentry = $this->student->get_logbook(true)->get_logentry_by_exericseid($grade->get_exerciseid())) {
                throw new \Error(get_string('errorexaminerevalmissinglogentry', 'local_booking'));
            }

            $examiner = new examiner($this->course, $grade->attempts[$this->attempt]->grader);
            $examdate = new \DateTime('@'.$logentry->get_deptime());
            $rubricgrade = $grade->get_graderubric($this->attempt);

            // verify the grade has rubric, otherwise legacy grading
            if (count($rubricgrade)) {
                $rubric = array_keys($rubricgrade);

                // FDF header & footer
                $fdfheader = "%FDF-1.2\r\n1 0 obj\r\n<<\r\n/FDF << /Fields [\r\n";
                $fdffooter = "] >> >>\r\nendobj\r\ntrailer\r\n<</Root 1 0 R>>\r\n%%EOF";

                // form header section
                $fdfdata = [];
                $fdfdata['ATO Name'] = get_config('local_booking', 'atoname');
                $fdfdata['Name'] = $this->student->get_name(false);
                $fdfdata['CID'] = $this->student->get_profile_field('VATSIMID');
                $fdfdata['Date of Test'] = $examdate->format('Y-m-d');
                $fdfdata['Attempt'] = $this->attempt+1;
                $fdfdata['Examiner Name'] = $examiner->get_name(false);
                $fdfdata['Examiner CID'] = $examiner->get_profile_field('VATSIMID');
                $fdfdata['A/C Type'] = $logentry->get_aircraft();
                $fdfdata['Method of Examination'] = $logentry->get_fstd();
                $fdfdata['Overall Result'] = $grade->attempts[$this->attempt]->grade >= $grade->grade_item->gradepass ? 'Satsifactory (Pass)' : 'Unsatsifactory (Fail)';

                // form grades and comments to build form based on field mapping
                $configfilename = $this->course->get_examinerformfile(LOCAL_BOOKING_EVALUATIONFORMCONFIG)['moodlefile'];
                $formmappings = $this->course->get_booking_config(LOCAL_BOOKING_EVALUATIONFORM, true, $configfilename);

                // throw error if the format entered in the course settings is incorrect based on matching mappings counts (minus keys)
                $mappingcntr = 0;
                foreach ($formmappings as $mapping) {
                    $mappingcntr += count(array_filter($mapping, function($maps) {
                        return $maps != 'result_field';
                    }, \ARRAY_FILTER_USE_KEY));
                }
                if ($mappingcntr != count($rubricgrade))
                    throw new \Error(get_string('errorexaminerevalmapping', 'local_booking'));

                // map grades and comments form sections to student grades & feedback comments
                foreach ($formmappings as $section) {
                    $failedcntr = 0;
                    $idx = 0;
                    foreach ($section as $config => $assessment) {
                        if ($config != 'result_field') {
                            $idx = array_search($config, array_column($rubricgrade, 'name'));
                            if ($idx !== false) {
                                $fdfdata[$assessment['grade_field']] = $rubricgrade[$rubric[$idx]]["grade"];
                                $fdfdata[$assessment['comment_field']] = $rubricgrade[$rubric[$idx]]["feedback"];
                                if ($rubricgrade[$rubric[$idx]]["grade"] == 'Unsatisfactory')
                                    $failedcntr++;
                            }
                        }
                    }
                    // average the result count for sections with more than one assessment (subract 1 for the result which is part of the section array)
                    if ((count($section)-1) > 1) {
                        $fdfdata[$section['result_field']] = round($failedcntr / count($section), 2, PHP_ROUND_HALF_DOWN) > LOCAL_BOOKING_FAILINGPERCENTAGE ? 'Unsatisfactory' : 'Satisfactory';
                    } else {
                        $fdfdata[$section['result_field']] = $rubricgrade[$rubric[$idx]]["grade"];
                    }
                }

                // build FDF form content
                $fdfcontent = '';
                foreach ($fdfdata as $field => $value) {
                    $fdfcontent .= "<</T($field)/V($value)>>\r\n";
                }

                // create the evaluation form file
                $content = $fdfheader . $fdfcontent . $fdffooter;
                $fdffile = tempnam(sys_get_temp_dir(), gethostname());
                file_put_contents($fdffile, $content);

                // merging the FDF data file with the examiner evaluation form
                $templateform = $this->course->get_examinerformfile(LOCAL_BOOKING_EVALUATIONFORM);
                $templateformfile = $templateform['moodlefile'];
                $evaluationform = str_replace('.pdf', '_' . str_replace(' ', '_', $this->student->get_name(false)).'.pdf', '/tmp/' . $templateform['filename']);
                exec("pdftk $templateformfile fill_form $fdffile output $evaluationform flatten", $output, $result_code);

                // Removing the FDF file as we don't need it anymore
                unlink($fdffile);

                return $result_code == 0 ? $evaluationform : false;
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Delete the generated evaluation form file part of clean up
     *
     * @param string $evaluationformfile The filename to unlink (delete)
     */
    public function unlink(string $evaluationformfile) {
        exec("rm -f $evaluationformfile");
    }
}
