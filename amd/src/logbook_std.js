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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/pending',
        'core/templates',
        'core/notification',
        'local_booking/booking_view_manager',
        'local_booking/modal_actions',
        'local_booking/events',
        'local_booking/logentry_modal_form',
        'local_booking/repository',
        'local_booking/selectors'
    ],
    function(
        $,
        Pending,
        Templates,
        Notification,
        ViewManager,
        ModalActions,
        BookingEvents,
        ModalLogentryEditForm,
        Repository,
        Selectors
    ) {

    /**
     * Register event listeners for session clicks.
     *
     * @param {object} root The root element.
     */
    const registerEventListeners = (root) => {

        // Listen to logentry created events
        $('body').on(BookingEvents.logentrycreated, function(e, logentry) {
            // Refresh logbook
            refreshNewLogentryContent(root, logentry);
        });

        // Listen to logentry updated events
        $('body').on(BookingEvents.logentryupdated, function(e, logentry) {
            // Refresh logbook
            refreshLogentryContent(root, logentry);
        });

        // Listen to logentry deleted event
        $('body').on(BookingEvents.logentrydeleted, function(e, logentryid) {
            // Remove logentry from the logbook
            $('#logentry_' + logentryid).slideUp(300);
            e.stopImmediatePropagation();
        });

        // Get promise for the logentry form for create and edit
        const contextId = $(Selectors.wrappers.logbookwrapper).data('contextid'),
        courseId = $(Selectors.wrappers.logbookwrapper).data('courseid'),
        userId = $(Selectors.wrappers.logbookwrapper).data('userid');

        if (contextId) {
            // Listen the edit click of a logbook entry.
            root.on('click', Selectors.actions.edit, function(e) {
                // From logbook
                const target = e.target;
                let logegntry = target.closest(Selectors.containers.summaryForm),
                    logentryId = logegntry.dataset.logentryId;

                // A logentry needs to be edited, show the modal form.
                e.preventDefault();
                e.stopPropagation();
                registerLogentryEditForm(root, e, contextId, courseId, userId, logentryId);
                e.stopImmediatePropagation();
            });

            // Listen the edit click of a logbook entry.
            root.on('click', Selectors.actions.add, function(e) {
                // A logentry needs to be created, show the modal form.
                e.preventDefault();
                e.stopPropagation();
                registerLogentryAddForm(root, e, contextId, courseId, userId);
                e.stopImmediatePropagation();
            });
        }
    };

    /**
     * Refresh the logbook entry edited.
     *
     * @method  refreshNewLogentryContent
     * @param   {object} root     The root element.
     * @param   {object} logentry The updated logentry.
     * @return  {promise}
     */
    const refreshNewLogentryContent = (root, logentry) => {

        const courseId = $(Selectors.wrappers.logbookwrapper).data('courseid'),
        userId = $(Selectors.wrappers.logbookwrapper).data('userid');

        M.util.js_pending(root.get('id') + '-' + courseId);
        return Repository.getLogentryById(logentry.id, courseId, userId)
            .then((response) => {
                return Templates.render('local_booking/logbook_std_logentry', response.logentry);
            })
            .then((html) => {
                $('#logbook-summary').after(html);
                root.find('.logbook-shadow1:first').hide().slideDown(300);
                return;
            })
            .always(() => {
                M.util.js_complete(root.get('id') + '-' + courseId);
                return;
            })
            .fail(Notification.exception);
    };

    /**
     * Refresh the logbook entry edited.
     *
     * @method  refreshLogentryContent
     * @param   {object} root     The root element.
     * @param   {object} logentry The updated logentry.
     * @return  {promise}
     */
    const refreshLogentryContent = (root, logentry) => {
        let card = $('#cardid_' + logentry.id);

        showPlaceholder(card);

        const courseId = $(Selectors.wrappers.logbookwrapper).data('courseid'),
        userId = $(Selectors.wrappers.logbookwrapper).data('userid');

        M.util.js_pending(root.get('id') + '-' + courseId);
        return Repository.getLogentryById(logentry.id, courseId, userId)
            .then((response) => {
                return Templates.render('local_booking/logbook_std_detail', response.logentry);
            })
            .then((html, js) => {
                return Templates.replaceNode(card, html, js);
            })
            .always(() => {
                M.util.js_complete(root.get('id') + '-' + courseId);
                return showContent(card);
            })
            .fail(Notification.exception);
    };

    /**
     * Register the form and listeners required for
     * creating logentries.
     *
     * @method registerLogentryAddForm
     * @param  {object} root       The root element.
     * @param  {object} e          The triggered event.
     * @param  {Number} contextId  The course context id of the logentry.
     * @param  {Number} courseId   The course id of the logentry.
     * @param  {Number} userId     The user id the logentry belongs to.
     */
    const registerLogentryAddForm = (root, e, contextId, courseId, userId) => {
        const LogentryFormPromise = ModalLogentryEditForm.create();
        const target = e.target;
        const pendingPromise = new Pending('local_booking/registerLogentryEditForm');

        ViewManager.renderLogentryEditForm(root, e, LogentryFormPromise, target, contextId, courseId, userId,
            0, true, 'local_booking/logbook_std')
        .then(pendingPromise.resolve())
        .catch(window.console.error);
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
        const LogentryFormPromise = ModalLogentryEditForm.create();
        const target = e.target;
        const pendingPromise = new Pending('local_booking/registerLogentryEditForm');

        ViewManager.renderLogentryEditForm(root, e, LogentryFormPromise, target, contextId, courseId, userId,
            logentryId, false, 'local_booking/logbook_std')
        .then(pendingPromise.resolve())
        .catch(window.console.error);
    };

    /**
     * Show the empty message when no logentries are found.
     *
     * @param {object} card The card element for the logentry view.
     */
    const showPlaceholder = function(card) {
        card.find(Selectors.containers.loadingPlaceholder).removeClass('hidden');
        card.find(Selectors.containers.content).addClass('hidden');
    };

    /**
     * Show the empty message when no logentries are found.
     *
     * @param {object} card The card element for the logentry view.
     */
    const showContent = function(card) {
        card.find(Selectors.containers.content).removeClass('hidden');
        card.find(Selectors.containers.loadingPlaceholder).addClass('hidden');
    };

    return {
        init: function(rt) {
            var root = $(rt);
            registerEventListeners(root);
            ModalActions.registerDelete(root);
            ViewManager.stopLoading(root);
        }
    };
});
