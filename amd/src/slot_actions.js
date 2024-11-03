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
    'local_booking/calendar_view_manager',
    'local_booking/modal_actions',
    ],
    function(
        $,
        Notification,
        Repository,
        CalendarViewManager,
        ModalActions,
    ) {

    var SELECTORS = {
        CALENDAR_WRAPPER: '[class=calendarwrapper]',
        SLOTS_TABLE: '[data-region="slots-week"]',
        SLOT_DAY: '[data-region="day"]',
        SAVE_BUTTON: '[data-region="save-button"]',
        PASTE_BUTTON: "[data-region='paste-button']",
        BOOK_BUTTON: "[data-region='book-button']",
        DAY_TIME_SLOT: "[data-action='day-time-slot']",
        LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
    };

    var Slots = [];
    var BookedSlot;
    var SlotIndexes = [];
    var postActive = false;

    /**
     * Save marked availability posts.
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
        const minslotperiod = root.find(SELECTORS.CALENDAR_WRAPPER).data('minslotperiod');
        const unixtshr = 3600;
        const lastminute = 60;


        // Get marked availability slots
        getUISlots(root);

        // Evaluate each slot to ensure it is a minimum of 2hrs if minimum slots is required (minslotperiod!=0)
        let minSlotPeriodMet = true;
        $.map( Slots, function(val) {
            if ((val.endtime - val.starttime + lastminute) < (minslotperiod * unixtshr) && minslotperiod != 0) {
                minSlotPeriodMet = false;
            }
        });

        if (minSlotPeriodMet) {
            let serverCall = null;
            if (Slots.length != 0) {
                serverCall = Repository.saveSlots(Slots, course, year, week);
            } else {
                serverCall = Repository.clearSlots(course, year, week);
            }

            // Send a request to the server to clear slots.
            return serverCall
                .then(function(response) {
                    if (response.validationerror) {
                        // eslint-disable-next-line no-alert
                        alert('Errors encountered: Unable to process availability posting action!');
                    }
                    return;
                }
                )
                .always(function() {
                    Notification.fetchNotifications();
                    return CalendarViewManager.stopLoading(root);
                }
                )
                .fail(Notification.exception);
        } else {
            // Show warning message
            CalendarViewManager.stopLoading(root);
            ModalActions.showWarning('minslotperiodwarning', minslotperiod);
        }
    }

    /**
     * Save marked booking posts.
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

        // Get marked availability slots
        getUISlots(root, 'book');

        // Check if the instructor has conflicting bookings
        return Repository.isConflictingBookings(studentid, BookedSlot)
            .then(function(response) {
                if (response.validationerror) {
                    // eslint-disable-next-line no-alert
                    alert('Errors encountered: Unable to check conflicting bookings!');
                } else {
                    // Check if there are no conflicting messages
                    if (response.result) {
                        // eslint-disable-next-line no-alert
                        alert(response.warnings[0].message);
                    } else {
                        // No conflicting bookings, save the booking.
                        // eslint-disable-next-line promise/no-nesting
                        return Repository.saveBookedSlot(BookedSlot, course, exercise, studentid)
                            .then(function(response) {
                                if (response.validationerror) {
                                    // eslint-disable-next-line no-alert
                                    alert('Errors encountered: Unable to save slot!');
                                } else {
                                    // Redirect to bookings view
                                    location.href = M.cfg.wwwroot + '/local/booking/view.php?courseid=' + course;
                                }
                                return;
                            }
                            )
                            .always(function() {
                                CalendarViewManager.stopLoading(root);
                                return;
                            }
                            )
                            .fail(Notification.exception);
                    }
                    return !response.result;
                }
                return false;
            }
            )
            .always(function() {
                CalendarViewManager.stopLoading(root);
            })
            .fail(Notification.exception);
        }

    /**
     * Update Slots & BookedSlots with marked availability
     * posts in the calendar view.
     *
     * @method getSlots
     * @param {object} root     The calendar root element
     * @param {String} action   The action for display view/book
     */
    function getUISlots(root, action) {

        const slottype = action == 'book' ? 'slot-booked' : 'slot-marked';
        const year = root.find(SELECTORS.CALENDAR_WRAPPER).data('year');
        const week = root.find(SELECTORS.CALENDAR_WRAPPER).data('week');
        const minute59 = 3540; // 59 minutes end of slot but befor next hour

        const tableid = root.find(SELECTORS.SLOTS_TABLE).attr('id');
        const head = $('#' + tableid + ' th');
        const colCount = document.getElementById(tableid).rows[0].cells.length;
        let colOffset;

        Slots.length = 0;
        // Get column index for the start of the week
        head.each(function() {
            if ($(this).data('region') == 'slot-week-day') {
                colOffset = head.index(this) + 1;
                return false;
            }
        });

        // Get all slots for this week from the UI table
        for (let i = colOffset; i <= colCount; i++) {
            // Get slots for the current day
            const dayHour = $('#' + tableid + ' td:nth-child(' + i + ')').map(function() {
                return [[$(this).data(slottype), $(this).data('slot-timestamp')]];
            }).get();

            // Get each slot in the day (start and end times)
            let aSlot = {};

            // Check each day (column) and record marked slot start-end times
            dayHour.forEach((hourSlot, index) => {
                let isLastElement = index == dayHour.length - 1;

                // Check if the slot is marked to record start or end time in marked sequence
                if (hourSlot[0]) {
                    if (Object.keys(aSlot).length === 0 && aSlot.constructor === Object) {
                        aSlot.starttime = hourSlot[1];
                        aSlot.endtime = hourSlot[1] + minute59;
                    } else {
                        aSlot.endtime = hourSlot[1] + minute59;
                    }

                // Add the slot if it has start and end, and this slot is empty => slot sequence ended
                } else if (!(Object.keys(aSlot).length === 0 && aSlot.constructor === Object)) {
                    aSlot = addSlot(aSlot, slottype, week, year);
                }

                // Add slot if it ends at the end of the day
                if (isLastElement && !(Object.keys(aSlot).length === 0 && aSlot.constructor === Object)) {
                    aSlot = addSlot(aSlot, slottype, week, year);
                }
            });
        }
    }

    /**
     * Adds a slot to local object array (Slots).
     *
     * @method addSlot
     * @param {object} aSlot The slot to be add to the local object array
     * @param {string} slottype The slot type availability post vs booked
     * @param {int} week The week of the year
     * @param {int} year The year
     * @return {object} empty object
     */
     function addSlot(aSlot, slottype, week = 0, year = 0) {
        if (slottype == 'slot-marked') {
            Slots.push(aSlot);
        } else if (slottype == 'slot-booked') {
            aSlot.week = week;
            aSlot.year = year;
            BookedSlot = aSlot;
        }
        return {};
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
     * Paste slots by seting the cells from
     * SlotIndexes (copied cells) to the calendar
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
        setSaveButtonState(root, 'post');

        return;
     }

    /**
     * Clear slots for a user per course in week and year
     * given they are not otherwsie booked
     *
     * @method clearWeekSlots
     * @param {object} root The week calendar root element
     */
    function clearWeekSlots(root) {
        $('td').filter(function() {
            if ($(this).data('slot-booked') == 0) {
                $(this).data('slot-marked', 0);
                $(this).removeClass('slot-selected');
            }
            return true;
        });
        setSaveButtonState(root, 'post', true);
        return;
    }

    /**
     * Set the cells from the copied SlotIndexes to the current table
     *
     * @method setPasteState
     * @param {object} root The week calendar root element
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
     * Set the save buttons state to enabled/disabled based
     * on user calendar time slot selection (cells) in
     * the week calendar
     *
     * @method setSaveButtonState
     * @param {object} root      The calendar root element
     * @param {string} action    The action behind the view
     * @param {bool} forceenable Enable save button
     */
    function setSaveButtonState(root, action, forceenable) {
        // Get marked availability slots
        getUISlots(root, action);

        const enabled = 'slot-button-blue';
        const disabled = 'slot-button-gray';
        const SaveButton = root.find(action == 'book' ? SELECTORS.BOOK_BUTTON : SELECTORS.SAVE_BUTTON);

        let state = forceenable ||
                (action == 'book' && BookedSlot !== undefined && BookedSlot.length !== 0) ||
                (action == 'post' && Slots !== undefined && Slots.length !== 0);

        SaveButton.addClass(state ? enabled : disabled).removeClass(!state ? enabled : disabled);
        SaveButton.prop('disabled', !state);

        return;
     }

    /**
     * Set the cells from the CopiedSlotsIndexes to the current table
     *
     * @method setSlot
     * @param {object} cell     The copied cell
     * @param {String} action   The action mode: book|post
     */
    function setSlot(cell, action) {
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

    /**
     * Set cells selected and save buttons state
     *
     * @method setPosting
     * @param {bool} state  The posting state
     */
    function setPosting(state) {
        postActive = state;
    }

    /**
     * Set cells selected and save buttons state
     *
     * @method postSlots
     * @param {object} root     The calendar root element
     * @param {String} action   The action behind the active view
     * @param {object} target   The target event object (cell)
     * @param {String} overridePost The override flag for posting state
     */
    function postSlots(root, action, target, overridePost = false) {
        // Change marked state
        if (typeof target !== 'undefined' && (postActive || overridePost)) {
            if (!target.is(SELECTORS.DAY_TIME_SLOT) && action !== 'all' && action !== '') {
                setSlot(target, action);
                setSaveButtonState(root, action);
            }
        }

        return;
    }

    return {
        saveWeekSlots: saveWeekSlots,
        saveBookedSlot: saveBookedSlot,
        clearWeekSlots: clearWeekSlots,
        pasteSlots: pasteSlots,
        setPasteState: setPasteState,
        setSaveButtonState: setSaveButtonState,
        copySlots: copySlots,
        postSlots: postSlots,
        setPosting: setPosting,
        Slots: Slots,
        SlotIndexes: SlotIndexes
    };
});