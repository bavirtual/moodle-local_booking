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

namespace local_booking\local\views;

use core\navigation\views\secondary as core_secondary;

/**
 * Class secondary_navigation_view to reorder
 * secondary menu navigation
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary extends core_secondary {
    /**
     * Define a custom secondary nav order/view
     *
     * @return array
     */
    protected function get_default_module_mapping(): array {
        return [
            self::TYPE_SETTING => [
                'bookings' => 1,
            ],
            self::TYPE_CUSTOM => [
                'availabilityinst' => 2,
            ],
            self::TYPE_CUSTOM => [
                'logbookmy' => 3,
            ],
        ];
    }
}
