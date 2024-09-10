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
 * Class for displaying availability view exercise names.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;

/**
 * Class for displaying each instructor booking in the instructor dashboard view.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_instructor_booking_exporter extends exporter {

    /**
     * Constructor.
     *
     * @param array $names The list of exercise names.
     */
    public function __construct($data) {
        parent::__construct($data, []);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'bookingid' => [
                'type' => PARAM_INT,
            ],
            'studentid' => [
                'type' => \PARAM_INT,
            ],
            'studentname' => [
                'type' => PARAM_RAW,
            ],
            'exerciseid' => [
                'type' => \PARAM_INT,
            ],
            'noshows' => [
                'type' => \PARAM_INT,
            ],
            'exercise' => [
                'type' => \PARAM_RAW,
            ],
            'sessiondate' => [
                'type' => \PARAM_RAW,
            ],
            'starttime' => [
                'type' => \PARAM_RAW,
            ],
            'endtime' => [
                'type' => \PARAM_RAW,
            ],
            'actionname' => [
                'type' => \PARAM_RAW,
            ],
            'actionurl' => [
                'type' => \PARAM_RAW,
            ],
            'coursename' => [
                'type' => \PARAM_RAW,
            ],
        ];
    }
}
