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
 * for logbook std view events.
 *
 * @module     local_booking/logbook_std
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
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
     * Register event listeners for session clicks.
     *
     * @param {object} root The root element.
     */
    const registerEventListeners = (root) => {

        // Get promise for the logentry form for create and edit
        const contextId = $(Selectors.logbookwrapper).data('contextid'),
        courseId = $(Selectors.logbookwrapper).data('courseid'),
        userId = $(Selectors.logbookwrapper).data('userid');

        if (contextId) {
            // Listen the click on the progression table of sessions for a logentry (new/view).
            root.on('click', Selectors.actions.edit, function(e) {
                // From lib get_logentry_view
                const target = e.target;
                let logegntry = target.closest(Selectors.containers.summaryForm),
                    logentryId = logegntry.dataset.logentryId;

                // A logentry needs to be created or edite, show the modal form.
                e.preventDefault();
                e.stopPropagation();
                registerLogentryEditForm(root, e, contextId, courseId, userId, logentryId);
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
     */
    const registerLogentryEditForm = (root, e, contextId, courseId, userId, logentryId) => {
        const LogentryFormPromise = ModalFactory.create({
            type: ModalLogentryEditForm.TYPE,
            large: true
        });

        const target = e.target;
        const pendingPromise = new Pending('local_booking/registerLogentryEditForm');

        ViewManager.renderLogentryModal(root, e, LogentryFormPromise, target, contextId, courseId, userId,
            logentryId, false, 'local_booking/logbook_std')
        .then(pendingPromise.resolve())
        .catch();
    };

    return {
        init: function(root) {
            var root = $(root);
            registerEventListeners(root);
            ViewManager.stopLoading(root);
        }
    };
});
