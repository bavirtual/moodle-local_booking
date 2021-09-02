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
 * Contain the logic for the quick add or update event modal.
 *
 * @module     local_booking/slot_actions
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core/notification',
    'local_booking/repository',
    'local_booking/view_manager',
    ],
    function(
        $,
        Notification,
        Repository,
        CalendarViewManager
    ) {

    var SELECTORS = {
        CALENDAR_WRAPPER: '[class=calendarwrapper]',
        SLOTS_TABLE: '[data-region="slots-week"]',
        SLOT_DAY: '[data-region="day"]',
        SAVE_BUTTON: '[data-action="save"]',
        PASTE_BUTTON: "[data-region='paste-button']",
        BOOK_BUTTON: "[data-region='book-button']",
        LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
        VIEW_DAY_LINK: "[data-action='view-day-link']",
    };

    var Slots = [];
    var BookedSlot;
    var SlotIndexes = [];

    /**
     * Create the event form modal for creating new events and
     * editing existing events.
     *
     * @method saveWeekSlots
     * @param {object} root The calendar root element
     * @return {object} The create modal promise
     */
     function saveWeekSlots(root) {

        CalendarViewManager.startLoading(root);

        // Get year and week
        const course = root.find(SELECTORS.CALENDAR_WRAPPER).data('courseid');
        const year = root.find(SELECTORS.CALENDAR_WRAPPER).data('year');
        const week = root.find(SELECTORS.CALENDAR_WRAPPER).data('week');

        getUISlots(root);

        // Send the form data to the server for processing.
        return Repository.saveSlots(Slots, course, year, week)
            .then(function(response) {
                if (response.validationerror) {
                    // eslint-disable-next-line no-alert
                    alert('Errors encountered: Unable to save slot!');
                }
                return;
            }
            .bind(this))
            .always(function() {
                Notification.fetchNotifications();
                return CalendarViewManager.stopLoading(root);
            }
            .bind(this))
            .fail(Notification.exception);
    }

    /**
     * Create the event form modal for creating new events and
     * editing existing events.
     *
     * @method saveBookedSlot
     * @param {object} root The calendar root element
     * @return {object} The create modal promise
     */
     function saveBookedSlot(root) {

        CalendarViewManager.startLoading(root);

        // Get exercise id and the user id from the URL
        const course = root.find(SELECTORS.CALENDAR_WRAPPER).data('courseid');
        const exercise = root.find(SELECTORS.CALENDAR_WRAPPER).data('exercise-id');
        const studentid = root.find(SELECTORS.CALENDAR_WRAPPER).data('student-id');

        getUISlots(root, 'book');

        // Send the form data to the server for processing.
        return Repository.saveBookedSlot(BookedSlot, course, exercise, studentid)
            .then(function(response) {
                if (response.validationerror) {
                    // eslint-disable-next-line no-alert
                    alert('Errors encountered: Unable to save slot!');
                }
                return;
            }
            .bind(this))
            .always(function() {
                CalendarViewManager.stopLoading(root);
                // Redirect to bookings view
                location.href = M.cfg.wwwroot + '/local/booking/view.php?courseid=' + course;
                return;
            }
            .bind(this))
            .fail(Notification.exception);
    }

    /**
     * Clear slots for a user per course in week and year
     * given they are not otherwsie booked
     *
     * @method clearWeekSlots
     * @param {object} root The calendar root element
     */
     function clearWeekSlots() {
        $('td').filter(function() {
            if ($(this).data('slot-booked') == 0) {
                $(this).data('slot-marked', 0);
                $(this).removeClass('slot-selected');
            }
        });
        return;
    }

     /**
     * Create the event form modal for creating new events and
     * editing existing events.
     *
     * @method getSlots
     * @param {object} root     The calendar root element
     * @param {String} action   The action for display view/book
     */
     function getUISlots(root, action) {

        const slottype = action == 'book' ? 'slot-booked' : 'slot-marked';
        const year = root.find(SELECTORS.CALENDAR_WRAPPER).data('year');
        const week = root.find(SELECTORS.CALENDAR_WRAPPER).data('week');

        const tableid = root.find(SELECTORS.SLOTS_TABLE).attr('id');
        const head = $('#' + tableid + ' th');
        const colCount = document.getElementById(tableid).rows[0].cells.length;
        let colOffset;

        Slots.length = 0;

        // Get column index for the week days only
        head.each(function() {
            if ($(this).data('region') == 'slot-week-day') {
                colOffset = head.index(this) + 1;
                return false;
            }
        });

        // Get all slots for this week from the UI table
        for (let i = colOffset; i <= colCount; i++) {
            // Get slots for the current day
            const daySlots = $('#' + tableid + ' td:nth-child(' + i + ')').map(function() {
                return [[$(this).data(slottype), $(this).data('slot-timestamp')]];
            }).get();

            // Get each slot in the day (start and end times)
            let aSlot = {};
            // eslint-disable-next-line no-loop-func
            daySlots.forEach(element => {
                if (element[0]) {
                    if (Object.keys(aSlot).length === 0 && aSlot.constructor === Object) {
                        aSlot.starttime = element[1];
                    } else {
                        aSlot.endtime = element[1];
                    }
                } else if (!(Object.keys(aSlot).length === 0 && aSlot.constructor === Object)) {
                    if (slottype == 'slot-marked') {
                        Slots.push(aSlot);
                    } else if (slottype == 'slot-booked') {
                        aSlot.week = week;
                        aSlot.year = year;
                        BookedSlot = aSlot;
                        return;
                    }
                    aSlot = {};
                }
            });
        }
    }

    /**
     * Update the indexes array tracking copied table indexes
     * editing existing events.
     *
     * @param {object} root The calendar root element
     * @method copySlots
     */
    function copySlots(root) {
        SlotIndexes.length = 0;

        $(SELECTORS.SLOT_DAY).each((idx, el) => {
            if ($(el).data('slot-marked')) {
                SlotIndexes.push([el.closest('tr').rowIndex, el.cellIndex]);
            }
        });

        setPasteState(root);

        return;
    }

     /**
     * Set the cells from the CopiedSlotsIndexes to the current table
     *
     * @method pasteSlots
     * @param {object} root The calendar root element
     */
      function pasteSlots(root) {
        const table = document.getElementById(root.find(SELECTORS.SLOTS_TABLE).attr('id'));
        SlotIndexes.forEach((idx) => {
            let slot = table.rows[idx[0]].cells[idx[1]];
            $(slot).data('slot-marked', 1);
            $(slot).addClass('slot-selected', 1);
        });

        return;
     }

     /**
     * Set the cells from the CopiedSlotsIndexes to the current table
     *
     * @method pasteSlots
     * @param {object} root The calendar root element
     */
      function setPasteState(root) {
        if (SlotIndexes.length > 0) {
            root.find(SELECTORS.PASTE_BUTTON).addClass('slot-button-blue').removeClass('slot-button-gray');
        } else {
            root.find(SELECTORS.PASTE_BUTTON).addClass('slot-button-gray').removeClass('slot-button-blue');
        }

        return;
     }

     /**
     * Set the cells from the CopiedSlotsIndexes to the current table
     *
     * @method bookSlots
     * @param {object} root     The calendar root element
     * @param {string} action   The action behind the view
     */
      function setBookState(root, action) {
        getUISlots(root, action);

        if (BookedSlot !== undefined) {
            root.find(SELECTORS.BOOK_BUTTON).addClass('slot-button-blue').removeClass('slot-button-gray');
        } else {
            root.find(SELECTORS.BOOK_BUTTON).addClass('slot-button-gray').removeClass('slot-button-blue');
        }

        return;
     }

     /**
     * Set the cells from the CopiedSlotsIndexes to the current table
     *
     * @method setSlot
     * @param {object} cell     The target event to the clicked slot element
     * @param {object} root     The calendar root element
     * @param {String} action   The target event to the clicked slot element
     */
      function setSlot(cell, root, action) {
        const slotaction = action == 'book' ? 'slot-booked' : 'slot-marked';
        const slotactionclass = action == 'book' ? 'slot-booked' : 'slot-selected';

        if (!$(cell).data(slotaction)) {
            $(cell).addClass(slotactionclass);
        } else {
            $(cell).removeClass(slotactionclass);
        }
        $(cell).data(slotaction, !$(cell).data(slotaction));

        return;
    }

    return {
        saveWeekSlots: saveWeekSlots,
        saveBookedSlot: saveBookedSlot,
        clearWeekSlots: clearWeekSlots,
        pasteSlots: pasteSlots,
        setPasteState: setPasteState,
        setBookState: setBookState,
        copySlots: copySlots,
        setSlot: setSlot,
        Slots: Slots,
        SlotIndexes: SlotIndexes
    };
});