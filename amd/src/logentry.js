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
 * This module handles Logentry form registration and promises.
 *
 * @module     local_booking/logentry
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Pending from 'core/pending';
import ModalFactory from 'core/modal_factory';
import LogentryEvents from 'local_booking/events';
import ModalLogentryEditForm from 'local_booking/modal_logentry_form';
import * as Selectors from 'local_booking/selectors';

const ViewManager = require('local_booking/booking_view_manager');

/**
 * Register event listeners for the logentries.
 *
 * @param {object} root The root element.
 */
 const registerEventListeners = (root) => {

    // Get promise for the logentry form for create and edit
    const contextId = $(Selectors.bookingwrapper).data('contextid'),
    courseId = $(Selectors.bookingwrapper).data('courseid');

    if (contextId) {
        // Listen the click on the progression table of sessions.
        root.on('click', Selectors.actions.viewLogEntry, function(e) {
            let logentryId = $(this).attr('data-logentry-id'),
            userId = $(this).attr('data-student-id');

            // A logentry needs to be created or edite, show the modal form.
            e.preventDefault();
            // We've handled the event so stop it from bubbling
            // and causing the day click handler to fire.
            e.stopPropagation();

            if (logentryId == 0) {
                registerLogentryEditForm(e, contextId, courseId, userId, logentryId, false);
            } else {
                registerLogentrySummaryForm(contextId, courseId, userId, logentryId);
            }
            e.stopImmediatePropagation();
        });
    }
};

/**
 * Register the form and listeners required for
 * creating and editing logentries.
 *
 * @method registerLogentryEditForm
 * @param  {object} e          The triggered event.
 * @param  {Number} contextId  The course context id of the logentry.
 * @param  {Number} courseId   The course id of the logentry.
 * @param  {Number} userId     The user id the logentry belongs to.
 * @param  {Number} logentryId The logentry id.
 * @param  {bool}   editMode   Whether to register for edit mode.
 */
 const registerLogentryEditForm = (e, contextId, courseId, userId, logentryId, editMode) => {
    const LogentryFormPromise = ModalFactory.create({
        type: ModalLogentryEditForm.TYPE,
        large: true
    });

    const target = e.target;
    const pendingPromise = new Pending('local_booking/registerLogentryEditForm');

    ViewManager.renderLogentryModal(e, LogentryFormPromise, target, contextId, courseId, userId, logentryId, editMode)
    .then(pendingPromise.resolve())
    .catch();
 };

/**
 * Register the form and listeners required for
 * viewing the logentry summary form.
 *
 * @method registerLogentrySummaryForm
 * @param  {Number} contextId  The course context id of the logentry.
 * @param  {Number} courseId   The course id of the logentry.
 * @param  {Number} userId     The user id the logentry belongs to.
 * @param  {Number} logentryId The logentry id.
 */
const registerLogentrySummaryForm = (contextId, courseId, userId, logentryId) => {
    const pendingPromise = new Pending('local_booking/registerLogentrySummaryForm');

    if (logentryId) {
        ViewManager.renderLogentrySummaryModal(courseId, userId, logentryId)
        .then(function(modal) {
            $('body').on(LogentryEvents.editLogentry, function(e, userId, logentryId) {
                registerLogentryEditForm(e, contextId, courseId, userId, logentryId, true);
                e.stopImmediatePropagation();
            });
            return modal;
        })
        .then(pendingPromise.resolve())
        .catch();
    } else {
        pendingPromise.resolve();
    }
};

export const init = (root) => {
    registerEventListeners(root);
};

