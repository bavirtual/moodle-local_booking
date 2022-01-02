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
 * for all session booking and logentry events.
 *
 * @module     local_booking/booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/str',
        'local_booking/booking_view_manager',
        'local_booking/booking_actions',
        'local_booking/logentry',
        'local_booking/events'
    ],
    function(
        $,
        Str,
        ViewManager,
        BookingActions,
        Logentry,
        BookingSessions
    ) {

    const SELECTORS = {
        CANCEL_BUTTON: "[data-region='cancel-button']",
        OVERRIDE_BUTTON: "[data-region='override-button']",
    };

    /**
     * Listen to and handle any logentry events fired by
     * Logentry and PIREP the modal forms.
     *
     * @method registerBookingEventListeners
     * @param  {object} root The booking root element
     * @param  {object} logentryFormModalPromise A promise reolved with the Logentry form modal
     * @param  {object} pirepFormPromise A promise reolved with the PIREP verification form modal
     */
     const registerBookingEventListeners = function(root) {
        const body = $('body');

        body.on(BookingSessions.canceled, function() {
            ViewManager.refreshInstructorDashboardContent(root);
            ViewManager.refreshMyBookingsContent(root);
        });
        body.on(BookingSessions.created, function() {
            ViewManager.refreshInstructorDashboardContent(root);
        });
        body.on(BookingSessions.updated, function() {
            ViewManager.refreshInstructorDashboardContent(root);
        });
        body.on(BookingSessions.deleted, function() {
            ViewManager.refreshInstructorDashboardContent(root);
        });
    };

    /**
     * Register event listeners for logbook entry,
     * session cancellation, and restriction override actions
     * in both 'Instructor dashboard' and 'Session selection' pages.
     *
     * @method  registerEventListeners
     * @param   {object} root The booking root element
     */
     const registerEventListeners = function(root) {

        // Register listeners to booking actions
        registerBookingEventListeners(root);

        // Listen to the click on the Cancel booking buttons in 'Instructor dashboard' page.
        root.on('click', SELECTORS.CANCEL_BUTTON, function(e) {
            // eslint-disable-next-line no-alert
            Str.get_string('cancellationcomment', 'local_booking').then(function(promptMsg) {
                const comment = prompt(promptMsg);
                if (comment !== null) {
                    BookingActions.cancelBooking(root, e, comment);
                }
                return;
            }).catch(Notification.exception);
        });

        // Listen to the click on the Override button in the 'Session selection' page.
        root.on('click', SELECTORS.OVERRIDE_BUTTON, function() {
            return BookingActions.overrideRestriction(root);
        });
    };

    return {
        init: function(root) {
            root = $(root);
            Logentry.init(root);
            registerEventListeners(root);
        }
    };
});
