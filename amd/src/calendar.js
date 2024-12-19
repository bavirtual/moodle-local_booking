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
 * This module is the highest level module for the calendar. It is
 * responsible for initialising all of the components required for
 * the calendar to run. It also coordinates the interaction between
 * components by listening for and responding to different events
 * triggered within the calendar UI.
 *
 *  Improvised from core_calendar.
 *
 * @module     local_booking/calendar
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
            'jquery',
            'local_booking/calendar_view_manager',
            'local_booking/calendar_actions',
            'local_booking/events',
            'local_booking/selectors',
        ],
        function(
            $,
            CalendarViewManager,
            CalendarActions,
            BookingEvents,
            Selectors,
        ) {

    /**
     * Register event listeners for the module.
     *
     * @method  registerEventListeners
     * @param   {object} root The calendar root element
     */
    const registerEventListeners = function(root) {
        // Get action type of the current week view or booking
        const action = root.find(Selectors.wrappers.calendarwrapper).data('action');
        const body = $('body');


        // Process previous/next week navigation links
        root.on('click', Selectors.links.navLink, (e) => {
            const courseId = root.find(Selectors.wrappers.calendarwrapper).data('courseid'),
                link = e.currentTarget;

            CalendarViewManager.changeWeek(root, link.href, link.dataset.year, link.dataset.week, link.dataset.time, courseId);
            e.preventDefault();
        });

        // Refresh calendar after a booking or slots are saved
        body.on(BookingEvents.slotssaved, function(e) {
            const courseId = root.find(Selectors.wrappers.calendarwrapper).data('courseid'),
                dataData = e.eventData;

            CalendarViewManager.changeWeek(root, dataData.url, dataData.year, dataData.week, dataData.time, courseId);
        });

        // Save slot postings or bookings on dirty calendar response
        body.on(BookingEvents.yesEvent + ' ' + BookingEvents.noEvent, function(e) {
            const courseId = root.find(Selectors.wrappers.calendarwrapper).data('courseid'),
                dataData = e.eventData;

            if (e.type == BookingEvents.yesEvent) {
                if (action == 'book') {
                    CalendarActions.saveBookedSlot(root, e);
                } else {
                    CalendarActions.saveWeekSlots(root, e);
                }
            }
            CalendarViewManager.changeWeek(root, dataData.url, dataData.year, dataData.week, dataData.time, courseId);
        });

        // Listen the click on the Save button.
        root.on('click', Selectors.regions.savebutton, function() {
            CalendarActions.saveWeekSlots(root);
        });

        // Listen the click on the Save Booking button.
        root.on('click', Selectors.regions.bookbutton, function() {
            CalendarActions.saveBookedSlot(root);
        });

        // Listen the click on the Copy button.
        root.on('click', Selectors.regions.copybutton, function() {
            CalendarActions.copySlots(root);
        });

        // Listen the click on the Paste button.
        root.on('click', Selectors.regions.pastebutton, function() {
            CalendarActions.pasteSlots(root);
        });

        // Listen the click on the Clear button.
        root.on('click', Selectors.regions.clearbutton, function() {
            CalendarActions.clearWeekSlots();
        });

        // Listen the mouse down on the calendar grid posting.
        root.on('mousedown', Selectors.regions.day, function(e) {
            CalendarActions.setPosting(true);
            CalendarActions.postSlots(root, action, $(e.target));
            e.preventDefault();
        });

        // Listen the mouse down on the calendar grid posting.
        root.on('mouseover', Selectors.regions.day, function(e) {
            CalendarActions.postSlots(root, action, $(e.target));
            e.preventDefault();
        });

        // Listen the mouse down on the calendar grid posting.
        root.on('mouseup', Selectors.regions.day, function() {
            CalendarActions.setPosting(false);
        });

        // Start listening for calendar slot posting changes.
        window.addEventListener('beforeunload', beforeUnloadHandler);

        // Remove loading
        const loadingIconContainer = root.find(Selectors.containers.loadingIcon);
        loadingIconContainer.addClass('hidden');
    };


    /**
     * Handle the beforeunload event.
     *
     * @method
     * @param   {Event} e
     * @returns {string|null}
     * @private
     */
    const beforeUnloadHandler = e => {
        // Check if the calendar is dirty
        if (CalendarActions.isDirty()) {
            return e.preventDefault();
        }

        // Attaching an event handler/listener to window or document's beforeunload event prevents browsers from using
        // in-memory page navigation caches, like Firefox's Back-Forward cache or WebKit's Page Cache.
        // Remove the handler.
        window.removeEventListener('beforeunload', beforeUnloadHandler);

        return null;
    };

    return {
        init: function(root) {
            root = $(root);
            CalendarViewManager.startLoading(root);
            registerEventListeners(root);
            CalendarViewManager.stopLoading(root);
        }
    };
});
