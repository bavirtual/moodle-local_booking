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

use local_booking\exporters\logentry_exporter;

/**
 * Class to output logbook entry view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logentry_view extends base_view {

    /**
     * logentry view constructor.
     *
     * @param array    $data      The data required for output
     * @param array    $related   The related objects to pass
     */
    public function __construct(array $data, array $related) {
        parent::__construct($data, $related, 'local_booking/logentry_modal_form');

        // add training type to the data sent to the exporter
        $pilot = $related['subscriber']->get_participant($data['userid']);
        $this->data['courseid'] = $related['subscriber']->get_id();
        $this->data['trainingtype'] = $related['subscriber']->trainingtype;
        $this->data['hasfindpirep'] = $related['subscriber']->has_integration('external_data', 'pireps');
        $this->data['isstudent']    = $pilot->is_student();
        $this->data['isinstructor'] = $pilot->is_instructor();
        $this->data['courseshortname'] = $related['subscriber']->get_shortname();

        // export the logbook
        $logentryexporter = new logentry_exporter($this->data, $this->related);
        $this->exporteddata = $logentryexporter->export($this->renderer);
    }
}
