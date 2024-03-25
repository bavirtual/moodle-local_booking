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
 * This module handles session booking and logentry view changes.
 *
 * @module     local_booking/booking_view_manager
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as Str from 'core/str';
import Templates from 'core/templates';
import Notification from 'core/notification';
import Pending from 'core/pending';
import ModalEvents from 'core/modal_events';
import ModalLogentrySummaryForm from 'local_booking/modal_logentry_summary';
import * as Repository from 'local_booking/repository';
import * as Selectors from 'local_booking/selectors';

/**
 * Refresh student progression, mybookings, and myassignees content.
 *
 * @method  refreshBookingsContent
 * @param   {object} root The root element.
 * @param   {number} courseId The id of the course associated with the progression view shown
 * @param   {number} categoryId The id of the category associated with the progression view shown
 * @param   {object} target The element being replaced. If not specified, the bookingwrapper is used.
 * @param   {string} filter The filter to show students, inactive (including graduates), suspended, and default to active.
 * @return  {promise}
 */
export const refreshBookingsContent = (root, courseId, categoryId, target = null, filter = null) => {
    startLoading(root);

    const template = root.attr('data-template'),
        mybookingstarget = root.find(Selectors.mybookingswrapper);
    target = target || root.find(Selectors.bookingwrapper);
    courseId = courseId || root.find(Selectors.bookingwrapper).data('courseid');
    filter = filter || 'active';
    var bookingsContext;
    M.util.js_pending([root.get('id'), courseId, categoryId].join('-'));
    return Repository.getBookingsData(courseId, filter)
        .then((context) => {
            bookingsContext = context;
            return Templates.render(template, context);
        })
        .then((html, js) => {
            return Templates.replaceNode(target, html, js);
        })
        .then(() => {
            return Templates.render('local_booking/my_bookings', bookingsContext);
        })
        .then((html, js) => {
            return Templates.replaceNode(mybookingstarget, html, js);
        })
        .always(() => {
            M.util.js_complete([root.get('id'), courseId, categoryId].join('-'));
            return stopLoading(root);
        })
        .fail(Notification.exception);
};

/**
 * Render the logentry new/edit modal.
 *
 * @method  renderLogentryModal
 * @param   {object} root       The container element
 * @param   {object} e          The triggered event.
 * @param   {Number} LogentryFormPromise  The Logentry form promise.
 * @param   {object} target     The target element.
 * @param   {Number} contextId  The course context id of the logentry.
 * @param   {number} courseId   The graded session course id.
 * @param   {number} userId     The graded session user id.
 * @param   {number} logentryId The graded session logbook entry id.
 * @param   {bool}   isNew      Whether the render is for edit.
 * @param   {string} template   The source template for edits.
 * @returns {promise}
 */
 export const renderLogentryModal = (root, e, LogentryFormPromise, target, contextId, courseId,
    userId, logentryId, isNew, template) => {
    const pendingPromise = new Pending('local_booking/booking_view_manager:renderLogentryModal');

    return LogentryFormPromise
    .then(function(modal) {

        // Show the logentry form modal form when the user clicks on a session
        // in the 'Instructor dashboard' page to add or edit a logentry
        // eslint-disable-next-line promise/no-nesting
        LogentryFormPromise.then(function(modal) {
            var logegntrySession, flightDate, exerciseId, sessionId, flightType, findpirepenabled;

            // Sel elements not meant for new or additional logentries
            if (isNew) {
                // From booking_session_exporter
                logegntrySession = target.closest(Selectors.actions.viewLogEntry);
                flightDate = logegntrySession.dataset.flightDate;
                exerciseId = logegntrySession.dataset.exerciseId;
                sessionId = logegntrySession.dataset.sessionId;
                flightType = logegntrySession.dataset.flightType;
                findpirepenabled = $(Selectors.bookingwrapper).data('findpirep');
            } else {
                if (template == 'local_booking/logbook_std') {
                    // From logbook
                    let logegntrySession = target.closest(Selectors.containers.summaryForm);
                    flightDate = logegntrySession.dataset.flightDate;
                    exerciseId = logegntrySession.dataset.exerciseId;
                    sessionId = logegntrySession.dataset.sessionId;
                    flightType = logegntrySession.dataset.flightType;
                } else {
                    // From logentry_view
                    logegntrySession = root.find(Selectors.containers.summaryForm);
                    flightDate = logegntrySession.data('flight-date');
                    exerciseId = logegntrySession.data('exercise-id');
                    sessionId = logegntrySession.data('session-id');
                    flightType = logegntrySession.data('flight-type');
                    findpirepenabled = logegntrySession.data('find-pirep');
                }
            }

            // Set form properties
            modal.setContextId(contextId);
            modal.setCourseId(courseId);
            modal.setUserId(userId);
            modal.setLogentryId(logentryId);
            modal.setExerciseId(exerciseId);
            modal.setSessionId(sessionId);
            modal.setFlightDate(flightDate);
            modal.setFlightType(flightType);
            modal.hasFindPIREP(findpirepenabled);

            // Handle hidden event.
            modal.getRoot().on(ModalEvents.hidden, function() {
                // Destroy when hidden.
                modal.destroy();
            });

            modal.show();
            e.stopImmediatePropagation();
            return false;
        }).catch(Notification.exception);
        return modal;
    })
    .then(function(modal) {
        pendingPromise.resolve();
        return modal;
    })
    .catch(Notification.exception);
 };

/**
 * Render the logentry summary modal.
 *
 * @method  renderLogentrySummaryModal
 * @param   {number} courseId The graded session course id.
 * @param   {number} userId The graded session user id.
 * @param   {number} logentryId The graded session logbook entry id.
 * @returns {promise}
 */
 export const renderLogentrySummaryModal = (courseId, userId, logentryId) => {
    const pendingPromise = new Pending('local_booking/booking_view_manager:renderLogentrySummaryModal');

    // Booking repository promise.
    return Repository.getLogentryById(logentryId, courseId, userId)
    .then((getEventResponse) => {
        if (!getEventResponse.logentry) {
            throw new Error(Str.get_string('errorlogentryfetch', 'local_booking') + logentryId);
        }

        return getEventResponse.logentry;
    })
    .then(logentryData => {
        // Build the modal parameters from the logentry data.
        const modalParams = {
            title: Str.get_string('logentry', 'local_booking'),
            body: Templates.render('local_booking/logentry_summary_body', logentryData)
        };

        // Create the modal.
        return ModalLogentrySummaryForm.create(modalParams);
    })
    .then(modal => {
        // Handle hidden event.
        modal.getRoot().on(ModalEvents.hidden, function() {
            // Destroy when hidden.
            modal.destroy();
        });
        // Finally, render the modal!
        modal.show();

        return modal;
    })
    .then(modal => {
        pendingPromise.resolve();

        return modal;
    })
    .catch(Notification.exception);
};

/**
 * Set the element state to loading.
 *
 * @method  startLoading
 * @param   {object} root The container element
 */
 export const startLoading = (root) => {
    const loadingIconContainer = root.find(Selectors.containers.loadingIcon);
    loadingIconContainer.removeClass('hidden');

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('disabled', 'disabled');
    });
};

/**
 * Remove the loading state from the element.
 *
 * @method  stopLoading
 * @param   {object} root The container element
 */
export const stopLoading = (root) => {
    const loadingIconContainer = root.find(Selectors.containers.loadingIcon);
    loadingIconContainer.addClass('hidden');

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('enabled', 'enabled');
    });
};
