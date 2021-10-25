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
 * Class for displaying logbook summary and entries view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use core\external\exporter;
use DateTime;
use local_booking\local\logbook\entities\logbook;
use moodle_url;

/**
 * Class for displaying a logbook entry.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logbook_exporter extends exporter {

    /**
     * @var int $courseid
     */
    protected $courseid;

    /**
     * @var int $studentid
     */
    protected $studentid;

    /**
     * Constructor.
     *
     * @param array $data       The form data.
     * @param array $related    The related data.
     */
    public function __construct($data, $related = []) {
        $this->courseid = $data['courseid'];
        $this->studentid = $data['studentid'];

        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'studentname' => [
                'type' => PARAM_RAW
            ],
            'totalflighttime' => [
                'type' => PARAM_RAW
            ],
            'totalsessiontime' => [
                'type' => PARAM_RAW
            ],
            'totalsolotime' => [
                'type' => PARAM_RAW
            ],
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'entries' => [
                'type' => PARAM_RAW,
                'multiple' => true
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        return [
            'entries' => $this->get_logbook_entries($this->courseid, $this->studentid, $output),
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_logbook_entries($courseid, $studentid, renderer_base $output) {
        // get the the logbook of a student
        $logbook = new logbook($courseid, $studentid);
        $logbook->load();
        $logbookentries = $logbook->get_logentries();
        $entries = [];

        // iterate through all the entries and export them
        foreach ($logbookentries as $logbookentry) {
            $data = $logbookentry->__toArray(true);
            $data['courseid'] = $courseid;
            $data['studentid'] = $studentid;
            $entry = new logentry_exporter($data, null, $this->related);
            $entries[] = $entry->export($output);
        }

        return $entries;
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return array(
            'context'=>'context',
        );
    }
}
