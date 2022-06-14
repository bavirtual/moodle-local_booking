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

use pdf;
use assign;
use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class representing subscribed courses
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_report extends pdf {

    /**
     * @var subscriber $course The subscribed course.
     */
    protected $course;

    /**
     * @var student $student The student for the report.
     */
    protected $student;

    /**
     * @var string $reporttype The report type.
     */
    protected $reporttype;

    /**
     * @var string $fontfamily The fontfamily for the report.
     */
    protected $fontfamily;

    /**
     * @var arrau $titlebkgrnd Contains title cell RGB background colors.
     */
    protected $titlebkgrnd;

    /**
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     * @param string $reporttype The report type.
     */
    public function __construct(subscriber $course, student $student, string $reporttype) {
        parent::__construct('P', 'px');

        $this->fontfamily = 'helvetica';
        $this->SetTitle($course->ato->name . ' ' . get_string($reporttype . 'report', 'local_booking'));
        $this->SetAuthor($course->ato->name);
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

        // set report attributes
        $this->course = $course;
        $this->student = $student;
        $this->reporttype = $reporttype;
    }

    /**
     * Overrides the TCPDF header method to write custom image URL.
     *
     */
    public function Header() {
        global $PAGE;

        parent::Header();

        // add the logo to the page header
        $logo = $PAGE->get_renderer('local_booking')->get_logo_url(null, 150);
        $this->Image($logo->out(false), 50, 34, 148, 18);
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
        $intro = '<h5>' . $this->course->ato->name . ' ' . get_string('trainingaudit', 'local_booking') . '</h5>';
        $this->Ln(20);
        $this->writeHTML($intro, true, false, true);
    }

    /**
     * Get the practical examination assignment feedback comment text.
     *
     * @param int $exerciseid  The assignment id.
     */
    protected function get_feedback_text(int $exerciseid) {

        // get course and associate module to find the practical exam skill test assignment
        list ($course, $cm) = get_course_and_cm_from_cmid($exerciseid, 'assign');

        // set context for the module and other requirements by the assignment
        $context = \context_module::instance($cm->id);

        // get the practical exam assignment to get the associated feedback comments
        $assign = new assign($context, $cm, $course);

        // instantiate the feedback plugin
        $feedback = new \assign_feedback_comments($assign,'comments');

        // get the grade associated with the assignment
        $grade = $assign->get_user_grade($this->student->get_id(), false, 0);

        // get the feedback plugin for the assignment
        $feedbackplugins = $assign->get_feedback_plugins();

        // find the assignment feedback comments plugin
        array_walk($feedbackplugins, function($item) use (&$feedback) {
            if (get_class($item) == 'assign_feedback_comments')
                return $feedback = $item;
        });

        // get the feedback comment object
        $feedbackcomment = $feedback->get_feedback_comments($grade->id);

        // return the feedback comment text
        return $feedbackcomment->commenttext;
    }
}
