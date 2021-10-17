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
 * A module to handle CRUD operations within the UI.
 * Improvised from core_calendar.
 *
 * @module     local_booking/booking_actions
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/str',
    'core/notification',
    'core/custom_interaction_events',
    'core/modal',
    'core/modal_registry',
    'core/modal_factory',
    'core/modal_events',
    'core/pending',
    'local_booking/modal_logentry_form',
    'local_booking/repository',
    'local_booking/events',
    'local_booking/modal_delete',
    'local_booking/selectors',
    'local_booking/booking_view_manager',
],
function(
    $,
    Str,
    Notification,
    CustomEvents,
    Modal,
    ModalRegistry,
    ModalFactory,
    ModalEvents,
    Pending,
    ModalLogentryForm,
    Repository,
    BookingEvents,
    ModalDelete,
    BookingSelectors,
    ViewManager,
) {

    /**
     * Cancel a specific booking and trigger update UI event.
     *
     * @method cancelBooking
     * @param {object} root     The My Bookings root element
     * @param {object} e        The click event on the Cancel button
     * @param {string} comment  The click event on the Cancel button
     * @return {object} The create modal promise
     */
    var cancelBooking = (root, e, comment) => {
        ViewManager.startLoading(root);

        var target = e.target;
        // Get course id and booking id
        const courseId = courseId || root.find(BookingSelectors.progressionwrapper).data('courseid');
        const bookingId = target.dataset.bookingid;

        // Send the request data to the server for processing.
        return Repository.cancelBooking(bookingId, comment)
            .then(function(response) {
                if (response.validationerror) {
                    // eslint-disable-next-line no-alert
                    alert(Str.get_string('errorlogentrycancel', 'local_booking'));
                }
                return;
            })
            .always(function() {
                $('body').trigger(BookingEvents.canceled, [root, false]);
                Notification.fetchNotifications();
                ViewManager.stopLoading(root);
            })
            .fail(Notification.exception);
    };

    /**
     * Prepares the action for the summary modal's delete action.
     *
     * @param {Number} logentryId The ID of the logentry.
     * @param {Number} studentId The student of the logentry.
     * @param {Number} courseId The course of the logentry.
     * @return {Promise}
     */
    function confirmDeletion(logentryId, studentId, courseId) {
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


        deletePromise = ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
        });

        var stringsPromise = Str.get_strings(deleteStrings);

        var finalPromise = $.when(stringsPromise, deletePromise)
        .then(function(strings, deleteModal) {
            deleteModal.setRemoveOnClose(true);
            deleteModal.setTitle(strings[0]);
            deleteModal.setBody(strings[1]);
            deleteModal.setSaveButtonText(strings[0]);

            deleteModal.show();

            deleteModal.getRoot().on(ModalEvents.save, function() {
                var pendingPromise = new Pending('local_booking/booking_actions:initModal:deletedlogentry');
                Repository.deleteLogentry(logentryId, studentId, courseId)
                    .then(function() {
                        $('body').trigger(BookingEvents.deleted, [logentryId, false]);
                        return;
                    })
                    .then(pendingPromise.resolve)
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
    }

    /**
     * Register the listeners required to delete the logentry.
     *
     * @param   {jQuery} root
     */
     function registerDelete(root) {
        root.on('click', BookingSelectors.actions.deleteLogentry, function(e) {
            // Fetch the logentry title, and pass them into the new dialogue.
            var logentrySource = root.find(BookingSelectors.logentryItem),
                logentryId = logentrySource.data('logentryId'),
                studentId = logentrySource.data('studentId'),
                courseId = logentrySource.data('courseId');
            confirmDeletion(logentryId, studentId, courseId);

            e.preventDefault();
        });
    }

    /**
     * Register the listeners required to redirect to
     * assignment grading page.
     *
     * @param   {jQuery} root
     */
    function registerRedirect(root) {
        root.on('click', BookingSelectors.actions.gotoFeedback, function(e) {
            // Fetch the logentry title, and pass them into the new dialogue.
            var logentrySource = root.find(BookingSelectors.logentryItem),
                modId = logentrySource.data('exerciseId'),
                userId = logentrySource.data('studentId');
                $('body').trigger(BookingEvents.gotoFeedback, [modId]);

                // Redirect to the grading and feedback page
                location.href = M.cfg.wwwroot + '/mod/assign/view.php?id=' + modId +
                    '&rownum=0&userid=' + userId + '&action=grader';

            e.preventDefault();
        });
    }

    /**
     * Create the logentry form modal for creating new logentries and
     * editing existing logentries.
     *
     * @param {object} root The progression booking root element
     * @return {promise} The create modal promise
     */
     function registerLogentryFormModal(root) {
        var logentryFormPromise = ModalFactory.create({
            type: ModalLogentryForm.TYPE,
            large: true
        });

        root.on('click', BookingSelectors.actions.edit, function(e) {
            e.preventDefault();
            var target = $(e.currentTarget),
                bookingWrapper = target.closest(BookingSelectors.progressionwrapper),
                logentryWrapper = target.closest(BookingSelectors.logentryItem);

            logentryFormPromise.then(function(modal) {
                // When something within the progression booking tells us the user wants
                // to edit an logentry then show the logentry form modal.
                modal.setSessionDate(logentryWrapper.data('sessionDate'));
                modal.setLogentryId(logentryWrapper.data('logentryId'));
                modal.setStudentId(logentryWrapper.data('studentId'));
                modal.setCourseId(bookingWrapper.data('courseId'));
                modal.setContextId(bookingWrapper.data('contextId'));
                modal.show();

                e.stopImmediatePropagation();
                return;
            }).fail(Notification.exception);
        });

        return logentryFormPromise;
    }

    /**
     * Register the listeners required to edit the logentry.
     *
     * @param   {jQuery} root
     * @param   {Promise} logentryFormModalPromise
     * @returns {Promise}
     */
    function registerEditListeners(root, logentryFormModalPromise) {
        var pendingPromise = new Pending('local_booking/booking_actions:registerEditListeners');

        return logentryFormModalPromise
        .then(function(modal) {
            // Show the logentry form modal form when the user clicks
            // on a session in the progression booking view to edit a logentry
            $('body').on(BookingEvents.editLogentry, function(e, logentryId, studentId, sessionDate) {
                var bookingWrapper = root.find(BookingSelectors.progressionwrapper);
                modal.setLogentryId(sessionDate);
                modal.setLogentryId(logentryId);
                modal.setStudentId(studentId);
                modal.setCourseId(bookingWrapper.data('courseid'));
                modal.setContextId(bookingWrapper.data('contextid'));
                modal.show();

                e.stopImmediatePropagation();
            });
            return modal;
        })
        .then(function(modal) {
            pendingPromise.resolve();

            return modal;
        })
        .catch(Notification.exception);
    }

    return {
        registerRedirect: registerRedirect,
        registerDelete: registerDelete,
        cancelBooking: cancelBooking,
        registerLogentryFormModal: registerLogentryFormModal,
        registerEditListeners: registerEditListeners
    };
});
