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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
            'jquery',
            'local_booking/calendar_view_manager',
            'local_booking/slot_actions',
            'core_calendar/selectors',
        ],
        function(
            $,
            CalendarViewManager,
            SlotActions,
            CalendarSelectors,
        ) {

    var SELECTORS = {
        ROOT: "[data-region='calendar']",
        DAY: "[data-region='day']",
        SAVE_BUTTON: "[data-region='save-button']",
        BOOK_BUTTON: "[data-region='book-button']",
        COPY_BUTTON: "[data-region='copy-button']",
        PASTE_BUTTON: "[data-region='paste-button']",
        CLEAR_BUTTON: "[data-region='clear-button']",
        LOADING_ICON: '.loading-icon',
        DAY_TIME_SLOT: "[data-action='day-time-slot']",
        CALENDAR_WEEK_WRAPPER: ".calendarwrapper",
        TODAY: '.today',
    };

    /**
     * Register event listeners for the module.
     *
     * @param {object} root The calendar root element
     */
    var registerEventListeners = function(root) {
        // Get action type of the current week view or booking
        const action = $(SELECTORS.CALENDAR_WEEK_WRAPPER).data('action');

        // Listen the click on the Save button.
        root.on('click', SELECTORS.SAVE_BUTTON, function() {
            SlotActions.saveWeekSlots(root);
        });

        // Listen the click on the Save Booking button.
        root.on('click', SELECTORS.BOOK_BUTTON, function() {
            SlotActions.saveBookedSlot(root);
        });

        // Listen the click on the Copy button.
        root.on('click', SELECTORS.COPY_BUTTON, function() {
            SlotActions.copySlots(root);
        });

        // Listen the click on the Copy button.
        root.on('click', SELECTORS.PASTE_BUTTON, function() {
            SlotActions.pasteSlots(root);
        });

        // Listen the click on the Clear button.
        root.on('click', SELECTORS.CLEAR_BUTTON, function() {
            SlotActions.clearWeekSlots();
        });

        // Listen to click on the clickable slot areas/cells
        var contextId = $(SELECTORS.CALENDAR_WEEK_WRAPPER).data('context-id');
        if (contextId) {
            // Bind click events to week calendar days.
            root.on('click', SELECTORS.DAY, function(e) {
                var target = $(e.target);
                // Change marked state
                if (typeof target !== 'undefined') {
                    if (!target.is(SELECTORS.DAY_TIME_SLOT) && action !== 'all') {
                        SlotActions.setSlot(this, root, action);
                        SlotActions.setSaveButtonState(root, action);
                    }
                }

                e.preventDefault();
            });
        }

        // Remove loading
        const loadingIconContainer = root.find(CalendarSelectors.containers.loadingIcon);
        loadingIconContainer.addClass('hidden');

    };

    return {
        init: function(root) {
            root = $(root);
            CalendarViewManager.init(root);
            registerEventListeners(root);
        }
    };
});
