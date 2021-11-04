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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\service;

/**
 * Class for relevant Windows Live Calendar APIs.
 *
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class live_calendar_api
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
		$authurl = get_booking_config('live_auth_url');
		$scope = urlencode(get_booking_config('live_scope'));
		$clientid = get_booking_config('live_client_id');
		$codechallenge = get_booking_config('live_code_challenge');

		$loginurl = $authurl . '?client_id=' . $clientid . '&response_type=code&redirect_uri=' . urlencode($redirecturi) .
			'&response_mode=query&scope=' . $scope . '&state=' . $statestring . '&code_challenge=' . $codechallenge . '&code_challenge_method=plain';

		return $loginurl;
	}

	/**
     * Get the Google access token to add
     * the calendar event.
     *
     * @return array $data The access token data array.
     */
	public static function get_token(string $redirecturi, string $code) {

		$authurl = get_booking_config('live_token_url');
		$clientid = get_booking_config('live_client_id');
		$clientsecret = get_booking_config('live_client_secret');
		$scope = urlencode(get_booking_config('live_scope'));
		$codechallenge = get_booking_config('live_code_challenge');

		$curlPost = 'client_id=' . $clientid . '&code=' . $code . '&scope=' . $scope . '&redirect_uri=' . urlencode($redirecturi) .
			'&client_secret=' . $clientsecret . '&grant_type=authorization_code&code_verifier=' . $codechallenge;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $authurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$data = json_decode(curl_exec($ch), true);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('liveaccesstokenerror', 'local_booking'));

		return $data['access_token'];
	}

    /**
     * Get the user's calendar timezone.
     *
     * @return string $value The calendar timezone.
     */
	public static function get_user_timezone($token) {
		$url_settings = get_booking_config('live_timezone_url');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_settings);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $token));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = json_decode(curl_exec($ch), true);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('livetimezoneerror', 'local_booking'));

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

		$url = get_booking_config('live_calendarlist_url') . http_build_query($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $token));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = json_decode(curl_exec($ch), true);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($httpcode != 200)
			throw new \Exception(get_string('livecalendarlisterror', 'local_booking'));

		return $data['items'];
	}

    /**
     * Add an event to a specific user's calendar.
     *
     * @return int $id The id for the created event.
     */
	public static function add_event($eventdata, $token) {
		$eventsurl = get_booking_config('live_events_url');
		$startdatetime = (new \DateTime('@' . $eventdata->sessionstart))->format('Y-m-d\TH\:i\:s');
		$startdateend = (new \DateTime('@' . $eventdata->sessionend))->format('Y-m-d\TH\:i\:s');
		$location = $eventdata->venue;

		$data_json = '{
			"subject": "'. $eventdata->eventname .'",
			"body": {
				"contentType":"HTML",
				"content":"'. strtr($eventdata->eventdescription, '"', '\'') .'"
			},
			"start": {
				"dateTime": "' . $startdatetime . '",
				"timeZone":"UTC"
			},
			"end": {
				"dateTime": "' . $startdateend . '",
				"timeZone":"UTC"
			},
			"location": {"displayName": "' . $location . '"}
		}';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $eventsurl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Authorization: Bearer " . $token,
			"Content-length: ".strlen($data_json))
			);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($ch));
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($httpcode != 200 && !empty($data->error))
			throw new \Exception($data->error->message);

		redirect(get_booking_config('live_calendar_url'));

		return $data;
	}
}
