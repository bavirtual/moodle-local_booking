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
 * A javascript module to handle calendar ajax actions.
 * Improvised from core_calendar.
 *
 * @module     local_booking/repository
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from 'core/ajax';

/**
 * Get sesison booking, my bookings, and my students data to view.
 *
 * @method getBookingsData
 * @param {number} courseId The course id.
 * @param {number} categoryId The category id.
 * @return {promise} Resolved with the month view data.
 */
 export const getBookingsData = (courseId) => {
    const request = {
        methodname: 'local_booking_get_bookings_view',
        args: {
            courseid: courseId,
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Cancel a sepcific booking for a student.
 *
 * @param {int} bookingId   The booking id to cancel
 * @param {string} comment  The booking id to cancel
 * @return {promise}
 */
 export const cancelBooking = (bookingId, comment) => {
    const request = {
        methodname: 'local_booking_cancel_booking',
        args: {
            bookingid: bookingId,
            comment: comment,
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Change the start day for the given event id. The day timestamp
 * only has to be any time during the target day because only the
 * date information is extracted, the time of the day is ignored.
 *
 * @param {string} weekSlots The URL encoded values from the form
 * @param {int} course The id of the associated course
 * @param {int} year The id of the event to update
 * @param {int} week A timestamp for some time during the target day
 * @return {promise}
 */
 export const saveSlots = (weekSlots, course, year, week) => {
    const request = {
        methodname: 'local_booking_save_slots',
        args: {
            slots: weekSlots,
            courseid: course,
            year: year,
            week: week
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Change the start day for the given event id. The day timestamp
 * only has to be any time during the target day because only the
 * date information is extracted, the time of the day is ignored.
 *
 * @param {array} bookedslot    The array of booked slots
 * @param {int} courseId        The course id of the booking
 * @param {int} exerciseId      The exercise id of the associated course
 * @param {int} studentId       The id of the associated user
 * @param {int} slotId          The id of the slot if exists != 0
 * @return {promise}
 */
 export const saveBookedSlot = (bookedslot, courseId, exerciseId, studentId) => {
    const request = {
        methodname: 'local_booking_save',
        args: {
            bookedslot: bookedslot,
            courseid: courseId,
            exerciseid: exerciseId,
            studentid: studentId,
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Change the start day for the given event id. The day timestamp
 * only has to be any time during the target day because only the
 * date information is extracted, the time of the day is ignored.
 *
 * @param {int} course The id of the associated course
 * @param {int} year The id of the event to update
 * @param {int} week A timestamp for some time during the target day
 * @return {promise}
 */
 export const clearSlots = (course, year, week) => {
    const request = {
        methodname: 'local_booking_delete_slots',
        args: {
            courseid: course,
            year: year,
            week: week
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Submit the form data for the logbook entry form.
 *
 * @method submitCreateUpdateForm
 * @param {string} formArgs An array of J URL encoded values from the form
 * @param {string} formData The URL encoded values from the form
 * @return {promise} Resolved with the new or edited logbook entry
 */
 export const submitCreateUpdateForm = (formArgs, formData) => {
    const request = {
        methodname: 'local_booking_submit_create_update_form',
        args: {
            formargs: formArgs,
            formdata: formData
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Get calendar data for the month view.
 *
 * @method getCalendarWeekData
 * @param {number} year Year
 * @param {number} week Week
 * @param {number} time Timestamp
 * @param {number} courseId The course id.
 * @param {number} categoryId The category id.
 * @param {string} action The action type.
 * @param {string} view The view type user/all.
 * @param {number} studentId The id of the associated user.
 * @param {number} exerciseId The exercise id for the booked session.
 * @return {promise} Resolved with the month view data.
 */
export const getCalendarWeekData = (year, week, time, courseId, categoryId, action, view, studentId, exerciseId) => {
    const request = {
        methodname: 'local_booking_get_weekly_view',
        args: {
            year,
            week,
            time,
            courseid: courseId,
            categoryid: categoryId,
            action: action,
            view: view,
            studentid: studentId,
            exerciseid: exerciseId,
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Get a graded session logbook entry by id.
 *
 * @method getLogentryById
 * @param {number} logentryId The logbook entry id.
 * @param {number} courseId The associated course id.
 * @param {number} studentId The student id of entry.
 * @return {promise} Resolved with requested calendar event
 */
 export const getLogentryById = (logentryId, courseId, studentId) => {

    const request = {
        methodname: 'local_booking_get_logentry_by_id',
        args: {
            logentryid: logentryId,
            courseid: courseId,
            studentid: studentId
        }
    };

    return Ajax.call([request])[0];
};
