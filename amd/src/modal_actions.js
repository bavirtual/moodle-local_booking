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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/str',
    'core/notification',
    'core/modal_events',
    'core/pending',
    'local_booking/repository',
    'local_booking/modal_delete',
    'local_booking/modal_warning',
    'local_booking/events',
    'local_booking/selectors',
    'local_booking/booking_actions',
],
function(
    $,
    Str,
    Notification,
    ModalEvents,
    Pending,
    Repository,
    ModalDelete,
    ModalWarning,
    BookingSessions,
    BookingSelectors,
    BookingActions,
) {

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
    var confirmDeletion = (logentryId, userId, courseId, cascade) => {
        var pendingPromise = new Pending('local_booking/booking_actions:confirmDeletion');
        var deleteStrings = [
            {
                key: 'deletelogentry',
                component: 'local_booking'
            },
        ];

        var deletePromise;
        deleteStrings.push({
            key: 'confirmlogentrydelete',
            component: 'local_booking'
        });


        deletePromise = ModalDelete.create();

        var stringsPromise = Str.get_strings(deleteStrings);

        // Setup modal delete prompt form
        var finalPromise = $.when(stringsPromise, deletePromise)
        .then(function(strings, deleteModal) {
            deleteModal.setRemoveOnClose(true);
            deleteModal.setTitle(strings[0]);
            deleteModal.setBody(strings[1]);

            deleteModal.show();

            deleteModal.getRoot().on(ModalEvents.save, function() {
                var pendingPromise = new Pending('local_booking/booking_actions:initModal:deletedlogentry');
                // eslint-disable-next-line promise/no-nesting
                Repository.deleteLogentry(logentryId, userId, courseId, cascade)
                    .then(function() {
                        $('body').trigger(BookingSessions.logentrydeleted, [logentryId, false]);
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
     * Displays a warning message.
     *
     * @method  showWarning
     * @param   {String} message The warning message to display.
     * @param   {String} data    Any additional message parameters.
     * @return  {Promise}
     */
    var showWarning = (message, data) => {
        var pendingPromise = new Pending('local_booking/booking_actions:showWarning');
        var warningPromise;
        var warningStrings = [
            {
                key: message + 'title',
                component: 'local_booking'
            },
            {
                key: message,
                component: 'local_booking',
                param: data
            }];


        warningPromise = ModalWarning.create();

        var stringsPromise = Str.get_strings(warningStrings);

        // Setup modal delete prompt form
        var finalPromise = $.when(stringsPromise, warningPromise)
        .then(function(strings, warningModal) {
            warningModal.setRemoveOnClose(true);
            warningModal.setTitle(strings[0]);
            warningModal.setBody(strings[1]);
            warningModal.show();

            return warningModal;
        })
        .then(function(modal) {
            pendingPromise.resolve();

            return modal;
        })
        .catch(Notification.exception);

        return finalPromise;
    };

    /**
     * Register the listeners required to delete the logentry.
     *
     * @method  registerDelete
     * @param   {jQuery} root
     */
     var registerDelete = (root) => {
        root.on('click', BookingSelectors.actions.deleteLogentry, function(e) {
            // Fetch the logentry title, and pass them into the new dialogue.
            const target = e.target;
            let logentrySource = root.find(BookingSelectors.logentryitem),
            logentryId = logentrySource.data('logentryId') ||
                target.closest(BookingSelectors.containers.summaryForm).dataset.logentryId,
            userId = logentrySource.data('userId') || target.closest(BookingSelectors.containers.summaryForm).dataset.userId,
            courseId = logentrySource.data('courseId') || $(BookingSelectors.logbookwrapper).data('courseid'),
            cascade = logentrySource.data('cascade') || target.closest(BookingSelectors.containers.summaryForm).dataset.cascade;

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
     const registerRedirect = function(root) {
        root.on('click', BookingSelectors.actions.gotoFeedback, function(e) {
            // Call redirect to assignment feedback page
            BookingActions.gotoFeedback(root, e);

            e.preventDefault();
        });
    };

    return {
        registerDelete: registerDelete,
        registerRedirect: registerRedirect,
        showWarning: showWarning
    };
});
