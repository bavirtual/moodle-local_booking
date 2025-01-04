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
 * This module handles additional logentry modal form action.
 *
 * @module     local_booking/modal_actions
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as Str from 'core/str';
import Notification from 'core/notification';
import ModalEvents from 'core/modal_events';
import Pending from 'core/pending';
import Repository from 'local_booking/repository';
import ModalDelete from 'local_booking/modal_delete';
import ModalWarning from 'local_booking/modal_warning';
import BookingSessions from 'local_booking/events';
import BookingActions from 'local_booking/booking_actions';
import Selectors from 'local_booking/selectors';

/**
 * Prepares the action for the summary modal's delete action.
 *
 * @method  confirmDeletion
 * @param   {Number} logentryId The ID of the logentry.
 * @param   {Number} userId   The user of the logentry.
 * @param   {Number} courseId The course of the logentry.
 * @param   {bool}   cascade  Whether to cascade delete linked logentries.
 * @return  {Promise}
 */
export const confirmDeletion = (logentryId, userId, courseId, cascade) => {

    let pendingPromise = new Pending('local_booking/booking_actions:confirmDeletion');
    let deleteStrings = [
        {
            key: 'deletelogentry',
            component: 'local_booking'
        },
    ];

    let deletePromise;
    deleteStrings.push({
        key: 'confirmlogentrydelete',
        component: 'local_booking'
    });


    deletePromise = ModalDelete.create();

    let stringsPromise = Str.get_strings(deleteStrings);

    // Setup modal delete prompt form
    let finalPromise = $.when(stringsPromise, deletePromise)
        .then(function(strings, deleteModal) {
            deleteModal.setRemoveOnClose(true);
            deleteModal.setTitle(strings[0]);
            deleteModal.setBody(strings[1]);

            deleteModal.show();

            deleteModal.getRoot().on(ModalEvents.save, function () {
                let pendingPromise = new Pending('local_booking/booking_actions:initModal:deletedlogentry');
                // eslint-disable-next-line promise/no-nesting
                Repository.deleteLogentry(logentryId, userId, courseId, cascade)
                    .then(function() {
                        $('body').trigger(BookingSessions.logentryDeleted, [logentryId, false]);
                        return;
                    })
                    .then(pendingPromise.resolve)
                    .always(function() {
                        Notification.fetchNotifications();
                    })
                    .catch(Notification.exception);
            });

            return deleteModal;
        })
        .then(function(modal) {
            pendingPromise.resolve();

            return modal;
        })
        .catch(Notification.exception);

    return finalPromise;
};

/**
 * Displays a warning modal with the specified message and title.
 *
 * @param {string} message - The message to display in the modal.
 * @param {string} title - The title of the modal.
 * @param {Object} [data={}] - Additional data to pass to the modal.
 * @param {Object} [options=null] - Options to customize the modal.
 * @param {string} [options.buttonType='ok'] - The type of buttons to display ('ok' or 'yesno').
 * @param {string} [options.buttonDefault='ok'] - The default button ('ok' or 'no').
 * @param {boolean} [options.stopExecution=true] - Whether to stop execution.
 * @param {string|null} [options.customEvent=null] - Custom event to trigger.
 * @param {boolean} [options.fromComponent=false] - Whether the warning is from a component.
 * @returns {Promise} - A promise that resolves when the modal is shown.
 */
export const showWarning = (message, title, data = {}, options = null) => {

    title ??= 'wanringtitle';
    options ??= {};
    options.buttonType ??= 'ok';
    options.buttonDefault ??= 'ok';
    options.stopExecution ??= true;
    options.customEvent ??= null;

    // Setup modal footer
    let footer = '<button type="button" class="btn btn-primary" data-action="ok">Ok</button>';
    if (options.buttonType == 'yesno') {
        footer = '<button type="button" class="btn ' + (options.buttonDefault == 'no' ?
            'btn-primary' : 'btn-secondary') + '" data-action="no">No</button>';
        footer += '<button type="button" class="btn ' + (options.buttonDefault != 'no' ?
            'btn-primary' : 'btn-secondary') + '" data-action="yes">Yes</button>';
    }

    let pendingPromise = new Pending('local_booking/booking_actions:showWarning');
    let warningPromise = ModalWarning.create();
    let stringsPromise, finalPromise;
    let warningStrings = [{}];

    /**
     * Setup modal warning prompt form
     * @param {Array} warningStrings - The warning strings to display.
     */
    async function setupModalWarningPromptForm(warningStrings) {
        try {
            if (options.fromComponent) {
                // Add title and message to the warning strings
                warningStrings = [{key: title, component: 'local_booking'}];
                warningStrings.push({key: message, component: 'local_booking', param: data});

                // Get strings for the warning modal
                stringsPromise = Str.get_strings(warningStrings);
            }

            const warningModal = await warningPromise;

            if (stringsPromise) {
                const strings = await stringsPromise;
                title = strings[0];
                message = strings[1];
            }

            warningModal.setRemoveOnClose(true);
            warningModal.setTitle(title);
            warningModal.setBody(message);
            warningModal.setData(data);
            warningModal.setCustomEvent(options.customEvent);
            warningModal.setFooter(footer);
            warningModal.show();

            pendingPromise.resolve();
            return warningModal;
        } catch (error) {
            Notification.exception(error);
        }
        return null;
    }

    // Call the async function
    setupModalWarningPromptForm(warningStrings);

    return finalPromise;
};

/**
 * Register the listeners required to delete the logentry.
 *
 * @method  registerDelete
 * @param   {jQuery} root
 */
export const registerDelete = (root) => {

    root.on('click', Selectors.actions.deleteLogentry, function(e) {
        // Fetch the logentry title, and pass them into the new dialogue.
        const target = e.target;
        let logentrySource = root.find(Selectors.logentryitem),
            logentryId = logentrySource.data('logentryId') ||
                target.closest(Selectors.containers.summaryForm).dataset.logentryId,
            userId = logentrySource.data('userId') || target.closest(Selectors.containers.summaryForm).dataset.userId,
            courseId = logentrySource.data('courseId') || $(Selectors.wrappers.logbookwrapper).data('courseid'),
            cascade = logentrySource.data('cascade') || target.closest(Selectors.containers.summaryForm).dataset.cascade;

        confirmDeletion(logentryId, userId, courseId, cascade);

        e.preventDefault();
    });
};

/**
 * Register the listeners required to redirect to
 * exercise (assignment) grading page.
 *
 * @method  registerRedirect
 * @param   {jQuery} root
 */
export const registerRedirect = function(root) {
    root.on('click', Selectors.actions.gotoFeedback, function(e) {
        // Call redirect to assignment feedback page
        BookingActions.gotoFeedback(root, e);

        e.preventDefault();
    });
};
