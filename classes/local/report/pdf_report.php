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
 * PDF report writer to write a dynamic report to the browser.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\report;

require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/lib.php');

use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\logbook\entities\logbook;
use local_booking\local\session\entities\grade;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing subscribed courses
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_report extends \pdf {

    /**
     * @var subscriber $course The subscribed course.
     */
    protected $course;

    /**
     * @var student $student The student for the report.
     */
    protected $student;

    /**
     * @var logbook $logbook The student's logbook for the report.
     */
    protected $logbook;

    /**
     * @var string $reporttype The report type.
     */
    protected $reporttype;

    /**
     * @var string $fontfamily The fontfamily for the report.
     */
    protected $fontfamily;

    /**
     * @var array $titlebkgrnd Contains title cell RGB background colors.
     */
    protected $titlebkgrnd;

    /**
     * @var string $title The report's title.
     */
    protected $title;

    /**
     * @var boolean $includevatsimlogo Whether to print the VATSIM logo on the report.
     */
    protected $includevatsimlogo;

    /**
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     * @param string $reporttype The report type.
     */
    public function __construct(subscriber $course, student $student, string $reporttype, bool $includevatsimlogo = false) {
        parent::__construct('P', 'px');

        // set report attributes
        $this->course = $course;
        $this->student = $student;
        $this->reporttype = $reporttype;
        $this->includevatsimlogo = $includevatsimlogo;
        $this->title = get_config('local_booking', 'atoname') . ' ' . get_string($reporttype . 'report', 'local_booking');

        $this->fontfamily = 'helvetica';
        $this->SetTitle($this->title);
        $this->SetAuthor(get_config('local_booking', 'atoname'));
        $this->SetCreator('local/booking/report.php');
        $this->SetKeywords(get_string('pluginname', 'local_booking') . ', PDF');
        $this->SetSubject(get_string($reporttype . 'reportsubject', 'local_booking'));
        $this->SetMargins(50, 80);

        $this->setPrintHeader(true);
        $this->setHeaderMargin(32);
        $this->setHeaderFont(array($this->fontfamily, 'b', 10));
        $this->setHeaderData('', 150, get_string('flighttraining', 'local_booking'), $course->get_fullname());

        $this->setPrintFooter(true);
        $this->setFooterMargin(50);
        $this->setFooterFont(array($this->fontfamily, '', 8));

    }

    /**
     * Overrides the TCPDF header method to write custom image URL.
     *
     */
    public function Header() {
        global $PAGE, $CFG;

        parent::Header();

        // add the logo to the page header
        $logo = $PAGE->get_renderer('local_booking')->get_logo_url(null, 150);
        $this->Image($logo->out(false), 50, 34, 148, 18);

        // add VATSIM logo
        if ($this->includevatsimlogo) {
            $vatsimlogo = new \moodle_url($CFG->httpswwwroot .  '/local/booking/pix/vatsim_logo.png');
            $this->Image($vatsimlogo->out(false), 408, 25, 148, 40);
        }
    }

    /**
     * Generate the report and output it to the browser.
     *
     * @param bool $coverpage      Whether to include a coverpage that doesn't have a logo
     */
    public function Generate(bool $coverpage = false) {

        // add the first report page
        if (!$coverpage) $this->AddPage();

        // write the report contents
        $this->WriteContent();

        // output the report to the browser
        $this->Output();
    }

    /**
     * Write the report intro section.
     *
     */
    public function WriteContent() {
        // write intro section
        // title background RGB colors
        if (empty($this->titlebkgrnd))
            $this->titlebkgrnd = array('R' => 100, 'G' => 149, 'B' => 237);

        // report name
        $titlebkgrnd = (object) $this->titlebkgrnd;
        $this->SetTextColor(255,255,255);
        $this->SetFillColor($titlebkgrnd->R, $titlebkgrnd->G, $titlebkgrnd->B);
        $this->SetFont($this->fontfamily, 'B', 24);
        $this->Cell(0, 50, get_string($this->reporttype . 'report', 'local_booking'), 0, 1, 'C', 1);

        // exam name
        $this->SetFont($this->fontfamily, '', 12);
        $this->SetTextColor(0,0,0);
        $reportdate = (new \DateTime())->format('M j\, Y \- H:i');
        $intro = '<p style="font-size: small;"><strong>' . get_string('reportdate', 'local_booking') . ':</strong> ' . $reportdate . '</p>';
        $this->writeHTML($intro, true, false, true);
        $this->SetTextColor(144, 145, 145);
        $intro = '<h5>' . get_config('local_booking', 'atoname') . ' ' . get_string('trainingaudit', 'local_booking') . '</h5>';
        $this->Ln(20);
        $this->writeHTML($intro, true, false, true);
    }

    /**
     * Write the logbook entry html.
     *
     * @param  int  $exerciseid  The assignment id.
     * @param  bool $examentry   Whether the entry is associated with an exam.
     * @return string
     */
    protected function write_logentry_info(int $exerciseid, bool $examentry = false) {

        // load the logbook if needed
        if (!isset($this->logbook)) {
            $this->logbook = new logbook($this->course->get_id(), $this->student->get_id());
            $this->logbook->load();
        }

        // get the the logentry for the practical exam
        $logbooksummary = (object) $this->logbook->get_summary_upto_exercise($exerciseid, true);
        $logentry = $this->logbook->get_logentry_by_exericseid($exerciseid);

        // add entries to flight time array
        $flighttimes = array();
        $flighttimes['flighttime'][0]       = !empty($logentry) ? $logentry->get_flighttime(false) : '';
        $flighttimes['flighttime'][1]       = !empty($logbooksummary->totalflighttime) ? $logbooksummary->totalflighttime : '';

        // times to be displayed depending on the training type and whether the log entry is an skill/command flight
        if ($this->course->trainingtype == 'Dual' && !$examentry) {
            $flighttimes['dualtime'][0]      = !empty($logentry) ? ($logentry->get_dualtime(false) ?: '') : '';
            $flighttimes['dualtime'][1]      = !empty($logbooksummary->totaldualtime) ? $logbooksummary->totaldualtime : '';
        } else if ($this->course->trainingtype == 'Multicrew' && !$examentry) {
            $flighttimes['multipilottime'][0]= !empty($logentry) ? ($logentry->get_multipilottime(false) ?: '') : 0;
            $flighttimes['multipilottime'][1]= !empty($logbooksummary->totalmultipilottime) ? $logbooksummary->totalmultipilottime : '';
        } else if ($examentry) {
            $flighttimes['picustime'][0]     = !empty($logentry) ? ($logentry->get_picustime(false) ?: '') : '';
            $flighttimes['picustime'][1]     = !empty($logbooksummary->totalpicustime) ? $logbooksummary->totalpicustime : '';
        }

        $flighttimes['ifrtime'][0]       = !empty($logentry) ? ($logentry->get_ifrtime(false) ?: '') : '';
        $flighttimes['ifrtime'][1]       = !empty($logbooksummary->totalifrtime) ? $logbooksummary->totalifrtime : '';

        // skip for exams
        if (!$examentry) {
            $flighttimes['groundtime'][0]    = !empty($logentry) ? ($logentry->get_groundtime(false) ?: '') : '';
            $flighttimes['groundtime'][1]    = !empty($logbooksummary->totalgroundtime) ? $logbooksummary->totalgroundtime : '';
            $flighttimes['sessionlength'][0] = !empty($logentry) ? $logentry->get_totalsessiontime(false) : '';
            $flighttimes['sessionlength'][1] = !empty($logbooksummary->totalsessiontime) ? $logbooksummary->totalsessiontime : '';
        }

        $flighttimes['deparr'][0]        = !empty($logentry) ? $logentry->get_depicao() . '/' . $logentry->get_arricao() : '';
        $flighttimes['deparr'][1]        = '';

        // logbook information
        $html = '<table width="400px" cellspacing="2" cellpadding="2">';
        $html .= '<tr style="border: 1px solid black; border-style: dotted;">';
        $html .= '<td></td><td style="font-weight: bold; width: 100px">' . get_string('time');
        $html .= '</td><td style="font-weight: bold; width: 100px">' . get_string('cumulative', 'local_booking') . '</td></tr>';
        foreach ($flighttimes as $key => $flightdata) {
            $html .= '<tr style="border: 1px solid black; border-style: dotted;">';
            $html .= '<td><strong>' . ucfirst(get_string($key, 'local_booking')) . '</strong></td><td>' . $flightdata[0] . '</td><td>' . $flightdata[1] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table><br />';

        // write the flight logbook entries table
        $this->SetFont($this->fontfamily, '', 12);
        $this->SetTextColor(72, 79, 87);
        // place the logbook entry table differently from 1st page
        $pgYpos = $this->PageNo() == 1 ? ($examentry ? 370 : 320) : ($examentry ? 150 : 220);
        $this->writeHTMLCell(0, 0, 50, $pgYpos, $html, array('LRTB' => array(
            'width' => 1,
            'dash'  => 1,
            'color' => array(144, 145, 145)
        )));
    }

    /**
     * Get the grade information.
     *
     * @param  grade $grade   The assignment grade.
     * @param  int   $attempt The grade attempt.
     * @return string
     */
    protected function get_grade_info(grade $grade, int $attempt = 0) {

        $html = '<strong>' . get_string('gradescore', 'local_booking') . ':</strong>&nbsp;&nbsp;';

        // check for attempts grade
        if (!empty($grade->attempts) && count($grade->attempts) > 1) {
            $html .= $grade->get_grade_name($grade->attempts[$attempt]->grade) . '<br />';
        } else {
            $html .= $grade->gradeinfo->grades[$this->student->get_id()]->str_long_grade . '<br />';
        }

        // get rubric grading if available
        if ($grade->has_rubric()) {
            $rubricinfo = $grade->get_graderubric();
            $html .= '<table width="500px" cellspacing="2" cellpadding="2">';
            $html .= '<tr style="border: 1px solid black; border-style: solid;">';
            $html .= '<td style="font-weight: bold; text-decoration: underline; font-size: 10px; width: 225px">' . get_string('skill', 'local_booking') . '</td>';
            $html .= '<td style="font-weight: bold; text-decoration: underline; font-size: 10px; width: 50px">' . get_string('grade', 'local_booking') . '</td>';
            $html .= '<td style="font-weight: bold; text-decoration: underline; font-size: 10px; width: 225px">' . get_string('feedback', 'local_booking') . '</td></tr>';

            foreach ($rubricinfo as $rubric) {
                $html .= '<tr style="border: 1px solid black; border-style: dotted; font-size: 10px;">';
                $html .= '<td>' . $rubric['name'] . '</td><td>' . $rubric['grade'] . '</td><td>' . $rubric['feedback'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table><br />';
        }

        return $html;
    }

    /**
     * Clean feedback text
     *
     * @param  grade $grade   The grade containing the feedback text comment
     * @param  int   $attempt The grade attempt.
     * @return string
     */
    protected function get_feedback_text(grade $grade, int $attempt = 0) {

        $html = '<br /><strong>' . get_string('feedback', 'local_booking') . ':</strong>';

        // check for attempts grade
        if (!empty($grade->attempts) && count($grade->attempts) > 1) {
            $feedbackcomments = $grade->attempts[$attempt]->commenttext;
        } else {
            $feedbackcomments = $grade->get_feedback_comments();
        }

        // process inline attachements or image tags, if exist
        if (strpos($feedbackcomments, 'pluginfile.php')) {

            // iterate through image tags
            $tags = explode('<img ', $feedbackcomments);
            foreach ($tags as $key => $tag) {

                // skip first part that excludes an img tag
                if ($key==0)
                    continue;

                // breakdown the pluginfile.php img tag, get the link and clean it
                $imgtagend = strpos($tag, '>') + 1;
                $imgurlstart = strpos($tag, '"', strpos($tag, 'src=')) + 1;
                $imgurlend = strpos($tag, '"', $imgurlstart);
                $imgurl = substr($tag, $imgurlstart, $imgurlend - $imgurlstart);

                // get file path and check if the file exists for attached images
                if (strpos($tag, 'pluginfile.php')) {
                    $urlparts = explode('/', $tag);
                    $component = $urlparts[array_search('pluginfile.php', $urlparts) + 2];
                    $area = $urlparts[array_search('pluginfile.php', $urlparts) + 3];
                    $itemid = $urlparts[array_search('pluginfile.php', $urlparts) + 4];
                    $imgurlnew = $grade->get_feedback_file($component, $area, $itemid);
                    if (file_exists($imgurlnew))
                        $tags[$key] = str_replace($imgurl, $imgurlnew, $tag);
                    else
                        $tags[$key] = 'img_not_found>' . substr($tag, $imgtagend);
                }

            }

            // put the comment string back together
            $feedbackcomments = implode('<img ', $tags);
            $feedbackcomments = str_replace('<img img_not_found>', '[Image not found]', $feedbackcomments);
        }

        // clean the feedback text from any characters violating global encoding which will break tcpdf
        $feedbackcommentarr = str_split($feedbackcomments);
        array_walk($feedbackcommentarr, function(&$value, $key) use (&$feedbackcommentarr){
            if (!mb_ord($value))
                $feedbackcommentarr[$key] = '';
        });

        $html .= implode($feedbackcommentarr);

        // return the feedback comment text
        return $html;
    }
}
