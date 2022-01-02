/* eslint-disable babel/new-cap */
/* eslint-disable no-undef */
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
 * This module handles logbook entry form
 * Improvised from core_calendar.
 *
 * @module     local_booking/modal_logentry_form
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/event',
        'core/str',
        'core/notification',
        'core/custom_interaction_events',
        'core/modal',
        'core/modal_registry',
        'core/fragment',
        'local_booking/events',
        'local_booking/repository',
        'local_booking/selectors',
    ],
    function(
        $,
        Event,
        Str,
        Notification,
        CustomEvents,
        Modal,
        ModalRegistry,
        Fragment,
        LogbookEvents,
        Repository,
        BookingSelectors,
    ) {

    var registered = false;
    var SELECTORS = {
        SAVE_BUTTON: '[data-action="save"]',
        LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
    };

    /**
     * Constructor for the Modal.
     *
     * @param {object} root The root jQuery element for the modal
     */
    var ModalLogEntryForm = function(root) {
        Modal.call(this, root);
        this.logentryId = null;
        this.flightDate = null;
        this.exerciseId = null;
        this.courseId = null;
        this.contextId = null;
        this.userId = null;
        this.isAdditional = null;
        this.reloadingBody = false;
        this.reloadingTitle = false;
        this.saveButton = this.getFooter().find(SELECTORS.SAVE_BUTTON);
    };

    ModalLogEntryForm.TYPE = 'local_booking-modal_logentry_form';
    ModalLogEntryForm.prototype = Object.create(Modal.prototype);
    ModalLogEntryForm.prototype.constructor = ModalLogEntryForm;

    /**
     * Set the context id to the given value.
     *
     * @method setContextId
     * @param {Number} id The context id
     */
    ModalLogEntryForm.prototype.setContextId = function(id) {
        this.contextId = id;
    };

    /**
     * Retrieve the current context id, if any.
     *
     * @method getContextId
     * @return {Number|null} The context id
     */
    ModalLogEntryForm.prototype.getContextId = function() {
        return this.contextId;
    };

    /**
     * Set the course id to the given value.
     *
     * @method setCourseId
     * @param {int} id The course id
     */
    ModalLogEntryForm.prototype.setCourseId = function(id) {
        this.courseId = id;
    };

    /**
     * Retrieve the current course id, if any.
     *
     * @method getCourseId
     * @return {int|null} The course id
     */
    ModalLogEntryForm.prototype.getCourseId = function() {
        return this.courseId;
    };

    /**
     * Check if the modal has an course id.
     *
     * @method hasCourseId
     * @return {bool}
     */
    ModalLogEntryForm.prototype.hasCourseId = function() {
        return this.courseId !== null;
    };

    /**
     * Set the exercise id to the given value.
     *
     * @method setExerciseId
     * @param {int} id The exercise id
     */
        ModalLogEntryForm.prototype.setExerciseId = function(id) {
        this.exerciseId = id;
    };

    /**
     * Retrieve the current exercise id, if any.
     *
     * @method getExerciseId
     * @return {int|null} The exercise id
     */
    ModalLogEntryForm.prototype.getExerciseId = function() {
        return this.exerciseId;
    };

    /**
     * Check if the modal has an exercise id.
     *
     * @method hasExerciseId
     * @return {bool}
     */
        ModalLogEntryForm.prototype.hasExerciseId = function() {
        return this.exerciseId !== null;
    };

    /**
     * Set the user id to the given value.
     *
     * @method setUserId
     * @param {int} id The user id
     */
        ModalLogEntryForm.prototype.setUserId = function(id) {
        this.userId = id;
    };

    /**
     * Retrieve the current user id, if any.
     *
     * @method getUserId
     * @return {int|null} The user id
     */
    ModalLogEntryForm.prototype.getUserId = function() {
        return this.userId;
    };

    /**
     * Check if the modal has an user id.
     *
     * @method hasUserId
     * @return {bool}
     */
        ModalLogEntryForm.prototype.hasUserId = function() {
        return this.userId !== null;
    };

    /**
     * Set the logentry id to the given value.
     *
     * @method setLogentryId
     * @param {int} id The logentry id
     */
    ModalLogEntryForm.prototype.setLogentryId = function(id) {
        this.logentryId = id;
    };

    /**
     * Retrieve the current logentry id, if any.
     *
     * @method getLogentryId
     * @return {int|null} The logentry id
     */
    ModalLogEntryForm.prototype.getLogentryId = function() {
        return this.logentryId;
    };

    /**
     * Check if the modal has an logentry id.
     *
     * @method hasLogentryId
     * @return {bool}
     */
    ModalLogEntryForm.prototype.hasLogentryId = function() {
        return this.logentryId !== null && this.logentryId != 0;
    };

    /**
     * Set the start time to the given value.
     *
     * @method setFlightDate
     * @param {int} time The session date time
     */
    ModalLogEntryForm.prototype.setFlightDate = function(time) {
        this.flightDate = time;
    };

    /**
     * Retrieve the current start time, if any.
     *
     * @method getFlightDate
     * @return {int|null} The start time
     */
    ModalLogEntryForm.prototype.getFlightDate = function() {
        return this.flightDate;
    };

    /**
     * Check if the modal has session date time.
     *
     * @method hasFlightDate
     * @return {bool}
     */
    ModalLogEntryForm.prototype.hasFlightDate = function() {
        return this.flightDate !== null;
    };

    /**
     * Set the modal isAdditional variable for new logbook
     * entry triggered from the booking confrimation view.
     *
     * @method setAdditionalEntry
     * @param {bool} additionalEntry Indicates additional logbook entry.
     */
    ModalLogEntryForm.prototype.setAdditionalEntry = function(additionalEntry) {
        this.isAdditional = additionalEntry;
    };

    /**
     * Check if the logbook entery is an additional entry
     * triggered from the booking confirmation view.
     *
     * @method isAdditionalEntry
     * @return {bool}
     */
    ModalLogEntryForm.prototype.isAdditionalEntry = function() {
        return this.isAdditional;
    };

    /**
     * Get the form element from the modal.
     *
     * @method getForm
     * @return {object}
     */
    ModalLogEntryForm.prototype.getForm = function() {
        return this.getBody().find('form');
    };

    /**
     * Disable the buttons in the footer.
     *
     * @method disableButtons
     */
    ModalLogEntryForm.prototype.disableButtons = function() {
        this.saveButton.prop('disabled', true);
    };

    /**
     * Enable the buttons in the footer.
     *
     * @method enableButtons
     */
    ModalLogEntryForm.prototype.enableButtons = function() {
        this.saveButton.prop('disabled', false);
    };

    /**
     * Reload the title for the modal to the appropriate value
     * depending on whether we are creating a new log book entry
     * or editing an existing one.
     *
     * @method reloadTitleContent
     * @return {object} A promise resolved with the new title text
     */
    ModalLogEntryForm.prototype.reloadTitleContent = function() {
        if (this.reloadingTitle) {
            return this.titlePromise;
        }

        this.reloadingTitle = true;

        if (this.hasLogentryId()) {
            this.titlePromise = Str.get_string('editlogentry', 'local_booking');
        } else {
            this.titlePromise = Str.get_string('newlogentry', 'local_booking');
        }

        this.titlePromise.then(function(string) {
            this.setTitle(string);
            return string;
        }.bind(this))
        .always(function() {
            this.reloadingTitle = false;
            return;
        }.bind(this))
        .fail(Notification.exception);

        return this.titlePromise;
    };

    /**
     * Send a request to the server to get the logentry_form in a fragment
     * and render the result in the body of the modal.
     *
     * If serialised form data is provided then it will be sent in the
     * request to the server to have the form rendered with the data. This
     * is used when the form had a server side error and we need the server
     * to re-render it for us to display the error to the user.
     *
     * @method reloadBodyContent
     * @param {string} formData The serialised form data
     * @return {object} A promise resolved with the fragment html and js from
     */
    ModalLogEntryForm.prototype.reloadBodyContent = function(formData) {
        if (this.reloadingBody) {
            return this.bodyPromise;
        }

        this.reloadingBody = true;
        this.disableButtons();

        var args = {};

        if (this.hasUserId()) {
            args.userid = this.getUserId();
        }

        if (this.hasLogentryId()) {
            args.logentryid = this.getLogentryId();
        }

        if (this.hasFlightDate()) {
            args.flightdate = this.getFlightDate();
        }

        if (this.hasCourseId()) {
            args.courseid = this.getCourseId();
        }

        if (this.hasExerciseId()) {
            args.exerciseid = this.getExerciseId();
        }

        if (typeof formData !== 'undefined') {
            args.formdata = formData;
        }

        // Get the content of the modal
        this.bodyPromise = Fragment.loadFragment('local_booking', 'logentry_form', this.getContextId(), args);

        this.setBody(this.bodyPromise);

        this.bodyPromise.then(function() {
            this.enableButtons();

            // Mask flight times < 5hrs and departure/arrival times to 24hr format
            $(document).ready(function() {
                if ($(BookingSelectors.bookingwrapper).data('trainingtype') == "Dual") {
                    Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_dualtime"));
                } else {
                    Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_multipilottime"));
                    Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_copilottime"));
                }
                Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_sessiontime"));
                Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_pictime"));
                Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_instructortime"));
                Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_checkpilottime"));
                Inputmask({"regex": "^([01]?[0-9]|2[0-3]):[0-5][0-9]"}).mask(document.getElementById("id_deptime"));
                Inputmask({"regex": "^([01]?[0-9]|2[0-3]):[0-5][0-9]"}).mask(document.getElementById("id_arrtime"));
                Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_nighttime"));
                Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_ifrtime"));
                Inputmask({"regex": "[0-9]"}).mask(document.getElementById("id_landingsday"));
                Inputmask({"regex": "[0-9]"}).mask(document.getElementById("id_landingsnight"));
              });

            return;
        }.bind(this))
        .fail(Notification.exception)
        .always(function() {
            this.reloadingBody = false;
            return;
        }.bind(this))
        .fail(Notification.exception);

        return this.bodyPromise;
    };

    /**
     * Reload both the title and body content.
     *
     * @method applyFlightRules
     * @param  {string} rule The rule to be applied
     */
    ModalLogEntryForm.prototype.applyFlightRules = function(rule) {
        window.console.log('working for ' + rule);
    };

    /**
     * Reload both the title and body content.
     *
     * @method reloadAllContent
     * @return {object} promise
     */
    ModalLogEntryForm.prototype.reloadAllContent = function() {
        return $.when(this.reloadTitleContent(), this.reloadBodyContent());
    };

    /**
     * Kick off a reload the modal content before showing it. This
     * is to allow us to re-use the same modal for creating and
     * editing different log book entries within the booking view page.
     *
     * We do the reload when showing the modal rather than hiding it
     * to save a request to the server if the user closes the modal
     * and never re-opens it.
     *
     * @method show
     */
    ModalLogEntryForm.prototype.show = function() {
        this.reloadAllContent();
        Modal.prototype.show.call(this);
    };

    /**
     * Clear the logentry id from the modal when it's closed so
     * that it is loaded fresh next time it's displayed.
     *
     * The logentry id will be set by the calling code if it wants
     * to edit a specific log entry.
     *
     * @method hide
     */
    ModalLogEntryForm.prototype.hide = function() {
        Modal.prototype.hide.call(this);
        this.setLogentryId(null);
        this.setFlightDate(null);
        this.setContextId(null);
        this.setCourseId(null);
        this.setExerciseId(null);
    };

    /**
     * Get the serialised form data.
     *
     * @method getFormData
     * @return {string} serialised form data
     */
    ModalLogEntryForm.prototype.getFormData = function() {
        return this.getForm().serialize();
    };

    /**
     * Send the form data to the server to create or update
     * a log book entry.
     *
     * If there is a server side validation error then we re-request the
     * rendered form (with the data) from the server in order to get the
     * server side errors to display.
     *
     * On success the modal is hidden and the page is reloaded so that the
     * new log book entry will display in the booking view user tooltip.
     *
     * @method save
     * @return {object} A promise
     */
    ModalLogEntryForm.prototype.save = function() {
        var invalid,
            loadingContainer = this.saveButton.find(SELECTORS.LOADING_ICON_CONTAINER);

        // Now the change events have run, see if there are any "invalid" form fields.
        invalid = this.getForm().find('[aria-invalid="true"]');

        // If we found invalid fields, focus on the first one and do not submit via ajax.
        if (invalid.length) {
            invalid.first().focus();
            return;
        }

        loadingContainer.removeClass('hidden');
        this.disableButtons();

        var formData = this.getFormData();
        var formArgs = 'contextid=' + this.contextId + '&courseid=' + this.courseId
            + '&exerciseid=' + this.exerciseId + '&userid=' + this.userId;

        // Send the form data to the server for processing.
        // eslint-disable-next-line consistent-return
        return Repository.submitCreateUpdateLogentryForm(formArgs, formData)
            .then(function(response) {
                if (response.validationerror) {
                    // If there was a server side validation error then
                    // we need to re-request the rendered form from the server
                    // in order to display the error for the user.
                    this.reloadBodyContent(formData);
                    return;
                } else {
                    // Check whether this was a new logbook entry or not.
                    // check if the logentry is from the prgression view or an additional
                    // logentry from the confirmation view
                    // The hide function unsets the form data so grab this before the hide.
                    var isExisting = this.hasLogentryId();
                    var isAddedEntry = this.isAdditionalEntry();

                    // No problemo! Our work here is done.
                    this.hide();

                    // Trigger the appropriate logbook event so that the view can be updated.
                    if (isExisting) {
                        $('body').trigger(LogbookEvents.updated, [response.logentry]);
                    } else if (!isAddedEntry) {
                        $('body').trigger(LogbookEvents.created, [response.logentry]);
                    }
                }

                return;
            }.bind(this))
            .always(function() {
                // Regardless of success or error we should always stop
                // the loading icon and re-enable the buttons.
                loadingContainer.addClass('hidden');
                Notification.fetchNotifications();
                this.enableButtons();

                return;
            }.bind(this))
            .fail(Notification.exception);
        };

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    ModalLogEntryForm.prototype.registerEventListeners = function() {
        // Apply parent event listeners.
        Modal.prototype.registerEventListeners.call(this);

        // When the user clicks the save button we trigger the form submission. We need to
        // trigger an actual submission because there is some JS code in the form that is
        // listening for this event and doing some stuff (e.g. saving draft areas etc).
        this.getModal().on(CustomEvents.events.activate, SELECTORS.SAVE_BUTTON, function(e, data) {
            this.getForm().submit();
            data.originalEvent.preventDefault();
            e.stopPropagation();
        }.bind(this));

        // Catch the submit event before it is actually processed by the browser and
        // prevent the submission. We'll take it from here.
        this.getModal().on('submit', function(e) {
            Event.notifyFormSubmitAjax(this.getForm()[0]);

            this.save();

            // Stop the form from actually submitting and prevent it's
            // propagation because we have already handled the event.
            e.preventDefault();
            e.stopPropagation();
        }.bind(this));
    };

    // Automatically register with the modal registry the first time this module is imported so that you can create modals
    // of this type using the modal factory.
    if (!registered) {
        ModalRegistry.register(ModalLogEntryForm.TYPE, ModalLogEntryForm, 'local_booking/modal_logentry_form');
        registered = true;
    }

    return ModalLogEntryForm;
});
