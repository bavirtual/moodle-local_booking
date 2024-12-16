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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core/notification',
    'local_booking/repository',
    'local_booking/calendar_view_manager',
    'local_booking/modal_actions',
    'local_booking/selectors',
    ],
    function(
        $,
        Notification,
        Repository,
        CalendarViewManager,
        ModalActions,
        Selectors,
    ) {

    var Slots = [];
    var BookedSlots = [];
    var SlotIndexes = [];
    let postActive = false;
    let formSaved = true;

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
        const course = root.find(Selectors.wrappers.calendarwrapper).data('courseid');
        const year = root.find(Selectors.wrappers.calendarwrapper).data('year');
        const week = root.find(Selectors.wrappers.calendarwrapper).data('week');
        const minslotperiod = root.find(Selectors.wrappers.calendarwrapper).data('minslotperiod');
        const unixTsHr = 3600;
        const lastMinute = 60;

        // Get marked availability slots
        getSlots(root);

        // Evaluate each slot to ensure it is a minimum of 2hrs if minimum slots is required (minslotperiod!=0)
        let minSlotPeriodMet = true;
        $.map( Slots, function(val) {
            if ((val.endtime - val.starttime + lastMinute) < (minslotperiod * unixTsHr) && minslotperiod != 0) {
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
                    formSaved = true;
                    return;
                })
                .always(function() {
                    Notification.fetchNotifications();
                    return CalendarViewManager.stopLoading(root);
                })
                .fail(Notification.exception);
        } else {
            // Show warning message
            CalendarViewManager.stopLoading(root);
            ModalActions.showWarning('warnminslotperiod', 'warnminslotperiodtitle', minslotperiod, {fromComponent: true});
            return false;
        }
    }

    /**
     * Save marked booking posts.
     *
     * @method saveBookedSlot
     * @param {object} root The calendar root element
     * @return {object} The create modal promise
     */
     async function saveBookedSlot(root) {

            CalendarViewManager.startLoading(root);

            // Get exercise id and the user id from the URL
            const course = $(Selectors.wrappers.calendarwrapper).data('courseid');
            const exercise = $(Selectors.wrappers.calendarwrapper).data('exercise-id');
            const studentid = $(Selectors.wrappers.calendarwrapper).data('student-id');

            // Get marked availability slots
            getSlots(root, 'book');

            // Check if the instructor has conflicting bookings
            if (Array.isArray(BookedSlots) && BookedSlots.length === 1) {
                let hasConflictingBooking = await Repository.hasConflictingBooking(studentid, BookedSlots[0])
                    .then(function(response) {
                        if (response.validationerror) {
                            // eslint-disable-next-line no-alert
                            alert('Errors encountered: Unable to check conflicting bookings!');
                            CalendarViewManager.stopLoading(root);
                        } else {
                            // Check if there are no conflicting messages
                            if (response.result) {
                                ModalActions.showWarning(response.warnings[0].message, 'Warning');
                                CalendarViewManager.stopLoading(root);
                            }
                        }
                        return response.result;
                    })
                    .fail(Notification.exception);

                // Save booking if no conflicting bookings were found
                if (!hasConflictingBooking) {
                    Repository.saveBookedSlot(BookedSlots[0], course, exercise, studentid)
                    .then(function(response) {
                        if (response.validationerror) {
                            // eslint-disable-next-line no-alert
                            alert('Errors encountered: Unable to save slot!');
                            CalendarViewManager.stopLoading(root);
                        } else {
                            clean();
                            // Redirect to bookings view
                            location.href = M.cfg.wwwroot + '/local/booking/view.php?courseid=' + course;
                        }
                        return;
                    })
                    .fail(Notification.exception);
                }
            } else {
                // Show warning message
                CalendarViewManager.stopLoading(root);
                ModalActions.showWarning('warnoneslotmax', 'warnoneslotmaxtitle', null, {fromComponent: true});
            }
        }

    /**
     * Update Slots & BookedSlots with marked availability
     * posts in the calendar view.
     *
     * @method getSlots
     * @param {object} root     The calendar root element
     * @param {String} action   The action for display view/book
     */
    function getSlots(root, action) {

        const slotType = action == 'book' ? 'slot-booked' : 'slot-marked';
        const year = $(Selectors.wrappers.calendarwrapper).data('year');
        const week = $(Selectors.wrappers.calendarwrapper).data('week');
        const minute59 = 3540; // 59 minutes end of slot but before next hour

        const tableId = $(Selectors.regions.slotsweek).attr('id');
        const head = $('#' + tableId + ' th');
        const colCount = document.getElementById(tableId).rows[0].cells.length;
        var colOffset;

        Slots.length = 0;
        BookedSlots.length = 0;

        // Get column index for the start of the week
        head.each(function() {
            if ($(this).data('region') == 'slot-week-day') {
                colOffset = head.index(this) + 1;
                return false;
            }
            return true;
        });

        // Get all slots for this week from the UI table
        for (let i = colOffset; i <= colCount; i++) {
            // Get slots for the current day
            const dayHour = $('#' + tableId + ' td:nth-child(' + i + ')').map(function() {
                return [[$(this).data(slotType), $(this).data('slot-timestamp')]];
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
                    aSlot = addSlot(aSlot, slotType, week, year);
                }

                // Add slot if it ends at the end of the day, edge case handling
                if (isLastElement && !(Object.keys(aSlot).length === 0 && aSlot.constructor === Object)) {
                    aSlot = addSlot(aSlot, slotType, week, year);
                }
            });
        }
    }

    /**
     * Adds a slot to local object array (Slots).
     *
     * @method addSlot
     * @param {object} aSlot The slot to be add to the local object array
     * @param {string} slotType The slot type availability post vs booked
     * @param {int} week The week of the year
     * @param {int} year The year
     * @return {object} empty object
     */
     function addSlot(aSlot, slotType, week = 0, year = 0) {
        if (slotType == 'slot-marked') {
            Slots.push(aSlot);
        } else if (slotType == 'slot-booked') {
            aSlot.week = week;
            aSlot.year = year;
            BookedSlots.push(aSlot);
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

        $(Selectors.regions.day).each((idx, el) => {
            if ($(el).data('slot-marked')) {
                SlotIndexes.push([el.closest('tr').rowIndex, el.cellIndex]);
            }
        });

        setPasteState(root);

        return;
    }

    /**
     * Paste slots by setting the cells from
     * SlotIndexes (copied cells) to the calendar
     *
     * @method pasteSlots
     * @param {object} root The calendar root element
     */
    function pasteSlots(root) {
        if (SlotIndexes.length > 0) {
            const table = document.getElementById(root.find(Selectors.regions.slotsweek).attr('id'));
            SlotIndexes.forEach((idx) => {
                let slot = table.rows[idx[0]].cells[idx[1]];
                $(slot).data('slot-marked', 1);
                $(slot).addClass('slot-selected', 1);
            });
            formSaved = false;
        }

        return;
     }

    /**
     * Clear slots for a user per course in week and year
     * given they are not otherwise booked
     *
     * @method clearWeekSlots
     */
    function clearWeekSlots() {
        $('td').filter(function() {
            if ($(this).data('slot-booked') == 0) {
                $(this).data('slot-marked', 0);
                $(this).removeClass('slot-selected');
            }
            return true;
        });
        formSaved = false;
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
            root.find(Selectors.regions.pastebutton).addClass('btn-primary').removeClass('btn-secondary');
        } else {
            root.find(Selectors.regions.pastebutton).addClass('btn-secondary').removeClass('btn-primary');
        }

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
        const slotAction = action == 'book' ? 'slot-booked' : 'slot-marked';
        const slotActionClass = action == 'book' ? 'slot-booked' : 'slot-selected';

        if (!$(cell).data(slotAction)) {
            $(cell).addClass(slotActionClass);
        } else {
            $(cell).removeClass(slotActionClass);
        }
        $(cell).data(slotAction, !$(cell).data(slotAction));

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
        if (typeof target !== 'undefined' && (postActive || overridePost) &&
            !target.is(Selectors.regions.daytimeslot) && action !== 'all' && action !== '') {
                formSaved = false;
                setSlot(target, action);
        }
    }

    /**
     * Checks whether the calendar has been edited without saving
     *
     * @method isDirty
     * @return {bool} dirtyState  The posting state
     */
    function isDirty() {
        return !formSaved;
    }

    /**
     * Reset the form saved flag so the form is no longer dirty
     *
     * @method clean
     * @return {bool} dirtyState  The posting state
     */
    function clean() {
        formSaved = true;
    }

    return {
        saveWeekSlots: saveWeekSlots,
        saveBookedSlot: saveBookedSlot,
        clearWeekSlots: clearWeekSlots,
        pasteSlots: pasteSlots,
        setPasteState: setPasteState,
        copySlots: copySlots,
        postSlots: postSlots,
        setPosting: setPosting,
        isDirty: isDirty,
        clean: clean,
        Slots: Slots,
        SlotIndexes: SlotIndexes
    };
});