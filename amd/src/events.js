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
 * This module handles logbook entry events
 * Improvised from core_calendar.
 *
 * @module     local_booking/events
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        slotssaved: 'booking-events:slots_saved',
        bookingcanceled: 'booking-events:booking_canceled',
        logentrycreated: 'booking-events:logentry_created',
        logentrydeleted: 'booking-events:logentry_deleted',
        logentryupdated: 'booking-events:logentry_updated',
        addLogentry: 'booking-events:add_logentry',
        editLogentry: 'booking-events:edit_logentry',
        viewUpdated: 'booking-events:view_updated',
        gotoFeedback: 'booking-events:goto_feedback',
        yesEvent: 'booking-events:response_yes',
        noEvent: 'booking-events:response_no',
        okEvent: 'booking-events:response_ok',
    };
});
