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
 * Calendar event helper trait
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\local\calendar;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for rest calendars.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calendar_helper {

    /**
     * Moodle OAuth2 redirect URL
     */
    const REDIRECTURL = '/admin/oauth2callback.php';

    /**
     * Session Booking calendar callback URL
     */
    const SESSION_BOOKING_CALLBACK = '/local/booking/calendar.php';

    /**
     * Retrieve calendar issuers based on including the text calendar
     * in the issuer's name.
     *
     * @return \core\oauth2\issuer[]  $issuers
     */
    public static function get_calendar_providers() {

        // get the Google issuer record
        $issuers = [];
        $allissuers = \core\oauth2\api::get_all_issuers();

        foreach ($allissuers as $anissuer) {
            if (strpos(strtolower($anissuer->get('name')), 'calendar') && $anissuer->get('enabled')) {
                $issuers[] = $anissuer;
            }
        }

        return $issuers;
    }

    /**
     * Retrieve the issuer (provider).
     *
     * @param string $provider  The event provider
     * @return \core\oauth2\issuer  $issuer
     */
    public static function get_calendar_provider(string $provider) {

        // get the Google issuer record
        $issuer = null;
        $issuers = self::get_calendar_providers();

        foreach ($issuers as $anissuer) {
            if ($anissuer->get('servicetype') == $provider) {
                $issuer = $anissuer;
                break;
            }
        }

        return $issuer;
    }

    /**
     * Retrieve the issuer's (provider) client.
     *
     * @param event  $event     The event info
     * @param string $provider  The event provider
     * @return \core\oauth2\client  $client
     */
    public static function get_client(event $event, string $provider) {
        global $CFG;

        $issuer = self::get_calendar_provider($provider);
        if ($issuer) {

            // parameters in the redirect URL to be used in calling the issuer rest service
            $params = [
                'sesskey' => sesskey(),
                'type'    => $provider,
                'userid'  => $event->userid,
                'id'      => $event->courseid,
                'name'    => $event->coursename,
                'cmid'    => $event->exerciseid,
                'instid'  => $event->instructorid,
                'stdid'   => $event->studentid,
                'start'   => $event->start,
                'end'     => $event->end
            ];

            // setup the return URL and get the issuer's client
            $redirecturl = new moodle_url($CFG->httpswwwroot . self::SESSION_BOOKING_CALLBACK, $params);
            $client = \core\oauth2\api::get_user_oauth_client($issuer, $redirecturl, $issuer->get('loginscopes'));

            // get the code if it's not there otherwise create the event
            if (!$client->is_logged_in()) {

                // authenticate the client
                redirect($client->get_login_url());

            }
        } else {
            return false;
        }

        return $client;
    }

    /**
     * Get a calendar object using the type.
     *
     * @param string $type The OAuth issuer of the calendar type (google, microsoft...).
     * @param bool $autorefresh Should the client support the use of refresh tokens to persist access across sessions.
     * @return calendar
     */
    public static function get_calendar(string $type) {
        $class = self::get_client_classname($type);
        $calendar = new $class($type);

        return $calendar;
    }

    /**
     * Get the event client classname for the event info based on type.
     *
     * @param string $type The OAuth issuer type (google, microsoft...).
     * @return string The classname for the session booking event custom client.
     */
    protected static function get_client_classname(?string $type): string {

        // Default core client class.
        $classname = 'local_booking\\local\\calendar\\';

        if (!empty($type)) {
            $typeclassname = $classname . $type . '_calendar';
            if (class_exists($typeclassname)) {
                $classname = $typeclassname;
            }
        }

        return $classname;
    }

    /**
     * Get the event's subject (name).
     *
     * @param string  $section  The section to be retrieved (subject/body)
     * @param object  $data     The parameters embeded in the body string
     * @param string  $format           The content fromat (html/plain text)
     * @return string $content
     */
    public static function get_msg_content(string $section, object $data, string $format = 'html') {
        return self::get_content($section, $data, $format, true);
    }

    /**
     * Get the event's subject (name).
     *
     * @param string  $section  The section to be retrieved (subject/body)
     * @param object  $data     The parameters embeded in the body string
     * @param string  $format           The content fromat (html/plain text)
     * @return string $content
     */
    public static function get_event_content(string $section, object $data, string $format = 'html') {

        // get additional parameters
        $additionalparams = [
            'courseurl'     => (new \moodle_url('/course/view.php', array('id'=> $data->courseid)))->out(false),
            'assignurl'     => (new \moodle_url('/mod/assign/index.php', array('id'=> $data->courseid)))->out(false),
            'exerciseurl'   => (new \moodle_url('/mod/assign/view.php', array('id'=> $data->exerciseid)))->out(false),
            'bookingurl'    => (new \moodle_url('/local/booking/view.php', array('courseid'=>$data->courseid)))->out(false),
            'confirmurl'    => (new \moodle_url('/local/booking/confirm.php', array('courseid'=>$data->courseid,'exeid'=>$data->exerciseid,
                'userid'=>$data->studentid,'insid'=>$data->instructorid)))->out(false),
        ];
        $params = array_merge($additionalparams, (array) $data);

        return self::get_content($section, (object) $params, $format);
    }

    /**
     * Get the event's subject (name).
     *
     * @param string  $section          The section to be retrieved (subject/body)
     * @param object  $data             The parameters embeded in the body string
     * @param string  $format           The content fromat (html/plain text)
     * @param bool    $hascalendarlinks Whether to include calendar links in the body
     * @return string $content
     */
    protected static function get_content(string $section, object $data, string $format = 'html', bool $hascalendarlinks = false) {

        $content = '';

        // instructor content vs student content
        $instructorcontent = $data->userid == $data->instructorid;

        // get subject/body content
        if ($section == 'subject') {
            // get the section
            $content = get_string($instructorcontent ? 'emailconfirmsubject' : 'emailnotifysubject', 'local_booking', $data);

        } else if ($section == 'body') {

            $calendarlinks = '';
            if ($format == 'html') {
                $content = get_string($instructorcontent ? 'emailconfirmhtml' : 'emailnotifyhtml', 'local_booking', $data);

                // check for including calendar links
                if ($hascalendarlinks) {
                    // begining of calendars link
                    $calendarlinks = get_string('calendarshtmlstart', 'local_booking', $data);

                    $providers = self::get_calendar_providers();
                    foreach($providers as $provider) {
                        // calendars link if available, always includes ics event download link
                        $calendarlinks .= get_string('calendarshtml' . $provider->get('servicetype'), 'local_booking', $data) ?: '';
                    }
                    $calendarlinks .= get_string('calendarshtmlics', 'local_booking', $data);

                    // ending of calendars link
                    $calendarlinks .= get_string('calendarshtmlend', 'local_booking', $data);

                    // put all together
                    $content .= $calendarlinks;
                }

            } else if ($format == 'text') {
                $content = get_string($instructorcontent ? 'emailconfirmmsg' : 'emailnotifymsg', 'local_booking', $data);
            }
        }

        return $content;
    }
}