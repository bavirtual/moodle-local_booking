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
 * A javascript module to handle summary modal.
 * Improvised from core_calendar.
 *
 * @module     local_booking/modal_logentry_summary
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/custom_interaction_events',
    'core/modal',
    'core/modal_registry',
    'core/modal_events',
    'local_booking/modal_actions',
    'local_booking/events',
],
function(
    $,
    CustomEvents,
    Modal,
    ModalRegistry,
    ModalEvents,
    ModalActions,
    BookingEvents,
) {

    var registered = false;
    var SELECTORS = {
        ROOT: "[data-region='summary-modal-container']",
        ADD_BUTTON: '[data-action="add"]',
        EDIT_BUTTON: '[data-action="edit"]',
        DELETE_BUTTON: '[data-action="delete"]',
        FEEDBACK_BUTTON: '[data-action="feedback"]',
    };

    /**
     * Constructor for the Modal.
     *
     * @param {object} root The root jQuery element for the modal
     */
    var ModalLogentrySummary = function(root) {
        Modal.call(this, root);
    };

    ModalLogentrySummary.TYPE = 'local_booking-logentry_summary';
    ModalLogentrySummary.prototype = Object.create(Modal.prototype);
    ModalLogentrySummary.prototype.constructor = ModalLogentrySummary;

    /**
     * Get the feedback button element from the footer. The button is cached
     * as it's not expected to change.
     *
     * @method getFeedbackButton
     * @return {object} button element
     */
     ModalLogentrySummary.prototype.getFeedbackButton = function() {
        if (typeof this.feedbackButton == 'undefined') {
            this.feedbackButton = this.getFooter().find(SELECTORS.FEEDBACK_BUTTON);
        }

        return this.feedbackButton;
    };

    /**
     * Get the add logentry button element from the footer. The button is cached
     * as it's not expected to change.
     *
     * @method getAddButton
     * @return {object} button element
     */
    ModalLogentrySummary.prototype.getAddButton = function() {
        if (typeof this.addButton == 'undefined') {
            this.addButton = this.getFooter().find(SELECTORS.ADD_BUTTON);
        }

        return this.addButton;
    };

    /**
     * Get the edit button element from the footer. The button is cached
     * as it's not expected to change.
     *
     * @method getEditButton
     * @return {object} button element
     */
    ModalLogentrySummary.prototype.getEditButton = function() {
        if (typeof this.editButton == 'undefined') {
            this.editButton = this.getFooter().find(SELECTORS.EDIT_BUTTON);
        }

        return this.editButton;
    };

    /**
     * Get the delete button element from the footer. The button is cached
     * as it's not expected to change.
     *
     * @method getDeleteButton
     * @return {object} button element
     */
    ModalLogentrySummary.prototype.getDeleteButton = function() {
        if (typeof this.deleteButton == 'undefined') {
            this.deleteButton = this.getFooter().find(SELECTORS.DELETE_BUTTON);
        }

        return this.deleteButton;
    };

    /**
     * Get the id for the logbook entry being shown in this modal. This value is
     * not cached because it will change depending on which logbook entry is
     * being displayed.
     *
     * @method getLogentryId
     * @return {int}
     */
    ModalLogentrySummary.prototype.getLogentryId = function() {
        return this.getBody().find(SELECTORS.ROOT).attr('data-logentry-id');
    };

    /**
     * Get the id for the logbook entry being shown in this modal. This value is
     * not cached because it will change depending on which logbook entry is
     * being displayed.
     *
     * @method getUserId
     * @return {int}
     */
     ModalLogentrySummary.prototype.getUserId = function() {
        return this.getBody().find(SELECTORS.ROOT).attr('data-user-id');
    };

    /**
     * Get the exercise id for the logbook entry being shown in this modal.
     *
     * @method getExerciseId
     * @return {int}
     */
     ModalLogentrySummary.prototype.getExerciseId = function() {
        return this.getBody().find(SELECTORS.ROOT).attr('data-exercise-id');
    };

    /**
     * Get the exercise id for the logbook entry being shown in this modal.
     *
     * @method getFlightDate
     * @return {int}
     */
     ModalLogentrySummary.prototype.getFlightDate = function() {
        return this.getBody().find(SELECTORS.ROOT).attr('data-flight-date');
    };

    /**
     * Get the title for the logentry being shown in this modal. This value is
     * not cached because it will change depending on which logentry is
     * being displayed.
     *
     * @method getLogentryTitle
     * @return {String}
     */
     ModalLogentrySummary.prototype.getLogentryTitle = function() {
        return this.getBody().find(SELECTORS.ROOT).attr('data-logentry-title');
    };

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    ModalLogentrySummary.prototype.registerEventListeners = function() {
        // Apply parent event listeners.
        Modal.prototype.registerEventListeners.call(this);

        // We have to wait for the modal to finish rendering in order to ensure that
        // the data-logentry-id property is available to use in the modal.
        M.util.js_pending('local_booking/modal_logentry_summary:registerEventListeners:bodyRendered');
        this.getRoot().on(ModalEvents.bodyRendered, function() {
            this.getModal().data({
                logentryTitle: this.getLogentryTitle(),
                logentryId: this.getLogentryId(),
            })
            .attr('data-type', 'logentry');
            ModalActions.registerDelete(this.getModal());
            ModalActions.registerRedirect(this.getModal());
            M.util.js_complete('local_booking/modal_logentry_summary:registerEventListeners:bodyRendered');
        }.bind(this));

        $('body').on(BookingEvents.deleted, function() {
            // Close the dialogue on delete.
            this.hide();
        }.bind(this));

        $('body').on(BookingEvents.gotoFeedback, function() {
            // Close the dialogue before going to the feedback page.
            this.hide();
        }.bind(this));

        CustomEvents.define(this.getAddButton(), [
            CustomEvents.events.activate
        ]);

        CustomEvents.define(this.getEditButton(), [
            CustomEvents.events.activate
        ]);

        this.getAddButton().on(CustomEvents.events.activate, function(e, data) {
            // When the edit button is clicked we fire an event for the booking UI to handle edit.
            $('body').trigger(BookingEvents.addLogentry, [this.getUserId()]);

            // There is nothing else for us to do so let's hide.
            this.hide();

            // We've handled this event so no need to propagate it.
            e.preventDefault();
            e.stopPropagation();
            data.originalEvent.preventDefault();
            data.originalEvent.stopPropagation();
        }.bind(this));

        this.getEditButton().on(CustomEvents.events.activate, function(e, data) {
            // When the edit button is clicked we fire an event for the booking UI to handle edit.
            $('body').trigger(BookingEvents.editLogentry, [this.getUserId(), this.getLogentryId()]);

            // There is nothing else for us to do so let's hide.
            this.hide();

            // We've handled this event so no need to propagate it.
            e.preventDefault();
            e.stopPropagation();
            data.originalEvent.preventDefault();
            data.originalEvent.stopPropagation();
        }.bind(this));
    };

    // Automatically register with the modal registry the first time this module is imported so that you can create modals
    // of this type using the modal factory.
    if (!registered) {
        ModalRegistry.register(ModalLogentrySummary.TYPE, ModalLogentrySummary, 'local_booking/logentry_summary_modal');
        registered = true;
    }

    return ModalLogentrySummary;
});
