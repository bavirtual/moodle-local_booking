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
 * Google calendar APIs based on GoogleCalendarApi for PHP
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\service;

/**
 * Class for relevant Google Calendar APIs.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google_calendar_api
{
    /**
     * Get the url required to get the code for
	 * the token so the user can authorize access.
     *
	 * @param string $redirecturi	The base redirect url
	 * @param string $statestring	The return url parameters encoded
     * @return string $loginurl 	The login uri to get the authentication code.
     */
	public static function get_login_uri(string $redirecturi, string $statestring) {
		$authurl = get_booking_config('google_auth_url');
		$scope = urlencode(get_booking_config('google_scope_url'));
		$clientid = get_booking_config('google_client_id');

		$loginurl = $authurl . '?scope=' . $scope . '&redirect_uri=' . urlencode($redirecturi) . '&response_type=code&client_id=' .
				$clientid . '&access_type=online' . '&state=' . $statestring;

		return $loginurl;
	}

    /**
     * Get the Google access token to add
     * the calendar event.
     *
     * @return string $redirecturi	The redirect url.
     * @return string $code			The code required to get the token.
     */
	public static function get_token($redirecturi, $code) {
		$url = get_booking_config('google_auth_token_url');
		$clientid = get_booking_config('google_client_id');
		$clientsecret = get_booking_config('google_client_secret');

		$curlPost = 'client_id=' . $clientid . '&redirect_uri=' . $redirecturi . '&client_secret=' . $clientsecret . '&code='. $code . '&grant_type=authorization_code';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$data = json_decode(curl_exec($ch), true);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('googleaccesstokenerror', 'local_booking'));

		return $data['access_token'];
	}

    /**
     * Get the user's calendar timezone.
     *
     * @return string $value The calendar timezone.
     */
	public static function get_user_timezone($token) {
		$url_settings = get_booking_config('google_timezone_url');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_settings);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $token));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = json_decode(curl_exec($ch), true);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('googletimezoneerror', 'local_booking'));

		return $data['value'];
	}

    /**
     * Get the user's calendars to pick
	 * the calendar to add the event to.
     *
     * @return array $items The list of user calendars.
     */
	public static function get_calendar_list($token) {
		$params = array();

		$params['fields'] = 'items(id,summary,timeZone)';
		$params['minAccessRole'] = 'owner';

		$url = get_booking_config('google_calendarlist_url') . http_build_query($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $token));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = json_decode(curl_exec($ch), true);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('googlecalendarlisterror', 'local_booking'));

		return $data['items'];
	}

    /**
     * Add an event to a specific user's calendar.
     *
     * @return int $id The id for the created event.
     */
	public static function add_event($eventdata, $token) {
		$eventsurl = get_booking_config('google_calendars_url') . $eventdata->calendarid . '/events';

		// construct calendar event details and process cURL.
		$eventstart = new \DateTime('@' . $eventdata->sessionstart);
		$eventend = new \DateTime('@' . $eventdata->sessionend);
		$curlPost['summary'] = $eventdata->eventname;
		$curlPost['description'] = $eventdata->eventdescription;
		$curlPost['start'] = array('dateTime' => $eventstart->format('Y-m-d\TH\:i\:s'), 'timeZone' => 'UTC');
		$curlPost['end'] = array('dateTime' => $eventend->format('Y-m-d\TH\:i\:s'), 'timeZone' => 'UTC');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $eventsurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $token, 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
		$data = json_decode(curl_exec($ch), true);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('googlecreateeventerror', 'local_booking'));

		redirect(get_booking_config('google_calendar_url') . $eventstart->format('Y\/m\/d'));
		return $data['id'];
	}
}
