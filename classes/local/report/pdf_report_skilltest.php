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

require_once($CFG->dirroot.'/local/booking/fpdm/fpdm.php');

use local_booking\local\participant\entities\student;
use local_booking\local\subscriber\entities\subscriber;
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
     * Constructor.
     *
     * @param subscriber $course The report course.
     * @param student $student   The student for the report.
     */
    public function __construct(subscriber $course, student $student) {
        parent::__construct($course, $student, 'examiner');
    }

    /**
     * Generate the report and output it to the browser.
     *
     * @param bool $coverpage      Whether to include a coverpage that doesn't have a logo
     */
    public function Generate(bool $coverpage = false) {

        $pdffilename = $this->get_feedback_filepath();
        $patharr = explode('/' , $pdffilename);
        $filename = array_pop($patharr);
        $path = implode('/', $patharr);

        try {

            $pdf = new FPDM($pdffilename);

        } catch (\Exception $e) {

            // attempt to fix the none standard FPDM file
            $pdftk = get_booking_config('pdftkpath');
            exec("$pdftk $pdffilename output $path/fixed.pdf");
            exec("mv $path/fixed.pdf $pdffilename");

            // try again and fail if still not fixed
            try {
                $pdf = new FPDM($pdffilename);
            } catch (\Exception $e) {
                die('<b>FPDF-Merge Error:</b> '.$e->getMessage());
            }

        }
        $pdf->Output();
    }
}
