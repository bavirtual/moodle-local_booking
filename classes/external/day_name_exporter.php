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
 * Contains event class for displaying the day name.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;

/**
 * Class for displaying the day names view.
 *
 * @package   core_calendar
 * @copyright 2017 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class day_name_exporter extends exporter {

    /**
     * @var int $dayno The day number.
     */
    protected $dayno;

    /**
     * @var int $dayno The day number.
     */
    protected $dayofmonth;

    /**
     * @var string $shortname The formatted short name of the day.
     */
    protected $shortname;

    /**
     * @var string $fullname The formatted full name of the day.
     */
    protected $fullname;

    /**
     * Constructor.
     *
     * @param int $dayno The day number.
     * @param array $names The list of names.
     */
    public function __construct($dayno, $dayofmonth, $names) {
        $data = $names + ['dayno' => $dayno] +  ['dayofmonth' => $dayofmonth] ;

        parent::__construct($data, []);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'dayno' => [
                'type' => PARAM_INT,
            ],
            'dayofmonth' => [
                'type' => PARAM_RAW,
            ],
            'shortname' => [
                // Note: The calendar type class has already formatted the names.
                'type' => PARAM_RAW,
            ],
            'fullname' => [
                // Note: The calendar type class has already formatted the names.
                'type' => PARAM_RAW,
            ],
        ];
    }
}
