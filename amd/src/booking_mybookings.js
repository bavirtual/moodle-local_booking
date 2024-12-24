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
 * @module     local_booking/booking_mybookings
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'local_booking/booking_view_manager',
        'local_booking/booking_actions',
        'local_booking/events',
        'local_booking/selectors'
    ],
    function(
        $,
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

        body.on(BookingEvents.bookingCanceled + " " + BookingEvents.noshowProcessed, function() {
            ViewManager.refreshMyBookingsContent(root);
        });

        // Listen to the click on the Cancel booking buttons in 'Instructor dashboard' page.
        root.on('click', Selectors.regions.cancelbutton, function(e) {
            BookingActions.cancelBooking(root, e);
        });

        // Listen to the click on the 'No-show' booking buttons in 'Instructor dashboard' page.
        root.on('click', Selectors.regions.noshowbutton, function (e) {
            BookingActions.processNoShow(root, e);
        });
    };

    return {
        init: function(rt) {
            var root = $(rt);
            registerMyBookingsEventListeners(root);
        }
    };
});
