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

namespace local_booking\output\views;

use local_booking\exporters\checklist_grading_exporter;

/**
 * Class to output checklist view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class checklist_view extends base_view {

    /**
     * checklist view constructor.
     *
     * @param array    $data      The data required for output
     * @param array    $related   The related objects to pass
     */
    public function __construct(array $data, array $related) {
        parent::__construct($data, $related, 'local_booking/checklist_grading');

        // export the checklist
        $checklistexporter = new checklist_grading_exporter($data, $related);
        $this->exporteddata = $checklistexporter->export($this->renderer);
    }
}
