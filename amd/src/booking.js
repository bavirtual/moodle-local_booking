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
 * This module is responsible for registering listeners
 * for all session booking and logentry events.
 *
 * @module     local_booking/booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/str',
        'core/pending',
        'core/modal_factory',
        'core/notification',
        'local_booking/booking_view_manager',
        'local_booking/booking_actions',
        'local_booking/events',
        'local_booking/modal_logentry_form',
        'local_booking/selectors'
    ],
    function(
        $,
        Str,
        Pending,
        ModalFactory,
        Notification,
        ViewManager,
        BookingActions,
        BookingEvents,
        ModalLogentryEditForm,
        Selectors
    ) {

    /**
     * Listen to and handle any logentry events fired by
     * Logentry and PIREP the modal forms.
     *
     * @method registerBookingEventListeners
     * @param  {object} root The booking root element
     */
     const registerBookingEventListeners = function(root) {
        const body = $('body');

        body.on(BookingEvents.canceled, function() {
            ViewManager.refreshBookingsContent(root);
        });
        body.on(BookingEvents.created, function() {
            ViewManager.refreshBookingsContent(root);
        });
        body.on(BookingEvents.updated, function() {
            ViewManager.refreshBookingsContent(root);
        });
        body.on(BookingEvents.deleted, function() {
            ViewManager.refreshBookingsContent(root);
        });

        // Listen to the click on the Cancel booking buttons in 'Instructor dashboard' page.
        root.on('click', Selectors.cancelbutton, function(e) {
            Str.get_string('commentcancel', 'local_booking').then(function(promptMsg) {
                // eslint-disable-next-line no-alert
                const comment = prompt(promptMsg);
                if (comment !== null) {
                    BookingActions.cancelBooking(root, e, comment, false);
                }
                return;
            }).catch(Notification.exception);
        });

        // Listen to the click on the 'No-show' booking buttons in 'Instructor dashboard' page.
        root.on('click', Selectors.noshowbutton, function(e) {
            // Get number of no shows
            const noshows = $(e.target).closest(Selectors.noshowbutton).data('noshows');
            // Get the message associated with the number of no-show occurence
            const noShowComment = Str.get_string('commentnoshow' + noshows, 'local_booking').then(function(noshowMsg) {
                return noshowMsg;
            }).catch(Notification.exception);
            // Chain the two retrieved strings in the prompt
            $.when(Str.get_string('commentnoshow', 'local_booking'), noShowComment)
            .then(function(promptMsg, noshowMsg) {
                // eslint-disable-next-line no-alert
                if (confirm(promptMsg + '\n\n' + noshowMsg)) {
                    BookingActions.cancelBooking(root, e, null, true);
                }
                return;
            }).catch(Notification.exception);
        });

        // Register the listeners required to redirect to
        // $('input[name="studentsfilter"]').change(function() {
        // $('input[type=radio][name=studentsfilter]').change(function() {
        root.on('change', 'input[type=radio][name=studentsfilter]', function() {
            // Call redirect to assignment feedback page
            ViewManager.refreshBookingsContent(root, 0, 0, null, $('input[name="studentsfilter"]:checked').val());
        });

        // Register the listeners required to redirect to
        root.on('click', Selectors.actions.gotoFeedback, function(e) {
            // Call redirect to assignment feedback page
            BookingActions.gotoFeedback(root, e);

            e.preventDefault();
        });
    };

    /**
     * Register event listeners for session clicks.
     *
     * @param {object} root The root element.
     */
    const registerSessionEventListeners = (root) => {

        // Get promise for the logentry form for create and edit
        const contextId = $(Selectors.bookingwrapper).data('contextid'),
        courseId = $(Selectors.bookingwrapper).data('courseid');

        if (contextId) {
            // Listen the click on the progression table of sessions for a logentry (new/view).
            root.on('click', Selectors.actions.viewLogEntry, function(e) {
                let logentryId = $(this).attr('data-logentry-id'),
                userId = $(this).attr('data-student-id');

                // A logentry needs to be created or edite, show the modal form.
                e.preventDefault();
                // We've handled the event so stop it from bubbling
                // and causing the day click handler to fire.
                e.stopPropagation();

                if (logentryId == 0) {
                    registerLogentryEditForm(null, e, contextId, courseId, userId, logentryId, true);
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
     * @param  {object} root       The root element.
     * @param  {object} e          The triggered event.
     * @param  {Number} contextId  The course context id of the logentry.
     * @param  {Number} courseId   The course id of the logentry.
     * @param  {Number} userId     The user id the logentry belongs to.
     * @param  {Number} logentryId The logentry id.
     * @param  {bool}   isNew      Whether to register for edit mode.
     */
    const registerLogentryEditForm = (root, e, contextId, courseId, userId, logentryId, isNew) => {
        const LogentryFormPromise = ModalFactory.create({
            type: ModalLogentryEditForm.TYPE,
            large: true
        });

        const target = e.target;
        const pendingPromise = new Pending('local_booking/registerLogentryEditForm');

        ViewManager.renderLogentryModal(root, e, LogentryFormPromise, target, contextId, courseId, userId, logentryId, isNew)
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
                $('body').on(BookingEvents.editLogentry, function(e, userId, logentryId) {
                    registerLogentryEditForm(modal.getRoot(), e, contextId, courseId, userId, logentryId);
                    e.stopImmediatePropagation();
                });
                $('body').on(BookingEvents.addLogentry, function(e, userId) {
                    registerLogentryEditForm(modal.getRoot(), e, contextId, courseId, userId, 0);
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

    /**
     * Register event listeners for logbook entry,
     * and session cancellation in both
     * 'Instructor dashboard' and 'Session selection' pages.
     *
     * @method  registerEventListeners
     * @param   {object} root The booking root element
     */
     const registerEventListeners = function(root) {

        // Register listeners to booking actions
        registerBookingEventListeners(root);

        // Register listeners to session click actions
        registerSessionEventListeners(root);
    };

    return {
        init: function(root) {
            var root = $(root);
            registerEventListeners(root);
            ViewManager.stopLoading(root);
        }
    };
});
