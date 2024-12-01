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
 * This module is responsible for registering listeners
 * for the instructor's 'My bookings' events.
 *
 * @module     local_booking/mybookings
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/str',
        'core/notification',
        'local_booking/booking_view_manager',
        'local_booking/booking_actions',
        'local_booking/events',
        'local_booking/selectors'
    ],
    function(
        $,
        Str,
        Notification,
        ViewManager,
        BookingActions,
        BookingEvents,
        Selectors
    ) {

    /**
     * Listen to and handle any logentry events fired by
     * Logentry and PIREP the modal forms.
     *
     * @method registerMyBookingsEventListeners
     * @param  {object} root The booking root element
     */
     const registerMyBookingsEventListeners = function(root) {
        const body = $('body');

        body.on(BookingEvents.sessioncanceled, function() {
            ViewManager.refreshInstructorBookingsContent(root);
        });

        // Listen to the click on the Cancel booking buttons in 'Instructor dashboard' page.
        root.on('click', Selectors.cancelbutton, function(e) {
            Str.get_string('commentcancel', 'local_booking').then(function(promptMsg) {
                // eslint-disable-next-line no-alert
                const comment = window.prompt(promptMsg);
                if (comment !== null) {
                    BookingActions.cancelBooking(root, e, comment, false);
                }
                return;
            }).catch(Notification.exception);
        });

        // Listen to the click on the 'No-show' booking buttons in 'Instructor dashboard' page.
        root.on('click', Selectors.noshowbutton, function(e) {
            // Get number of no shows
            const noshows = $(e.target).closest(Selectors.noshowbutton).data('noshows');
            // Get the message associated with the number of no-show occurence
            const noShowComment = Str.get_string('commentnoshow' + noshows, 'local_booking').then(function(noshowMsg) {
                return noshowMsg;
            }).catch(Notification.exception);
            // Chain the two retrieved strings in the prompt
            $.when(Str.get_string('commentnoshow', 'local_booking'), noShowComment)
            .then(function(promptMsg, noshowMsg) {
                // eslint-disable-next-line no-alert
                if (window.confirm(promptMsg + '\n\n' + noshowMsg)) {
                    BookingActions.cancelBooking(root, e, null, true);
                }
                return;
            }).catch(Notification.exception);
        });
    };

    /**
     * Register event listeners for logbook entry,
     * and session cancellation in both
     * 'Instructor dashboard' and 'Session selection' pages.
     *
     * @method  registerEventListeners
     * @param   {object} root The booking root element
     */
     const registerEventListeners = function(root) {

        // Register listeners to booking actions
        registerMyBookingsEventListeners(root);
    };

    return {
        init: function(rt) {
            var root = $(rt);
            registerEventListeners(root);
        }
    };
});
