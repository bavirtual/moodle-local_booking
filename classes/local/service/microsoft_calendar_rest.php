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
 * Windows Live calendar APIs based on GoogleCalendarApi for PHP
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\service;

/**
 * Class for relevant Outlook Live Office 365 Calendar APIs.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class microsoft_calendar_rest extends \core\oauth2\rest {

    /**
     * Define the functions of the rest API.
     *
     * @return array
     */
    public function get_api_functions() {
        return [
            'add' => [
                'endpoint' => 'https://graph.microsoft.com/v1.0/me/events',
                'method'   => 'post',
                'args' 	   => [
					'subject'  => PARAM_RAW,
					'body'     => PARAM_RAW,
					'start'	   => PARAM_RAW,
					'end'  	   => PARAM_RAW,
					'location' => PARAM_RAW,
                ],
                'response' => 'json'
            ],
        ];
    }
}
