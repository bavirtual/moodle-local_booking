/* eslint-disable no-nested-ternary */
/* eslint-disable complexity */
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
 * This module handles logbook entry form.
 *
 * @module     local_booking/modal_logentry_form
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core_form/events',
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
    FormEvents,
    Str,
    Notification,
    CustomEvents,
    Modal,
    ModalRegistry,
    Fragment,
    LogbookEvents,
    Repository,
    Selectors,
) {

var registered = false;
var SELECTORS = {
    ADVANCED_FORM: '[data-form-type="other"]',
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
    this.contextId = null;
    this.courseId = null;
    this.userId = null;
    this.logentryId = null;
    this.flightDate = null;
    this.exerciseId = null;
    this.sessionId = null;
    this.pirepLookupId = null;
    this.hasfindpirep = false;
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
 * Get the current context id, if any.
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
 * Get the current course id, if any.
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
 * Get the current exercise id, if any.
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
 * Set the session id to the given value.
 *
 * @method setSessionId
 * @param {int} id The session id
 */
    ModalLogEntryForm.prototype.setSessionId = function(id) {
    this.sessionId = id;
};

/**
 * Get the current session id, if any.
 *
 * @method getSessionId
 * @return {int|null} The session id
 */
ModalLogEntryForm.prototype.getSessionId = function() {
    return this.sessionId;
};

/**
 * Check if the modal has an session id.
 *
 * @method hasSessionId
 * @return {bool}
 */
    ModalLogEntryForm.prototype.hasSessionId = function() {
    return this.sessionId !== null;
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
 * Get the current user id, if any.
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
 * Get the current logentry id, if any.
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
 * Get the current start time, if any.
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
 * Set the flight type of the logentry to the given value.
 *
 * @method setFlightType
 * @param {string} flighttype The flight type (training/solo/check)
 */
 ModalLogEntryForm.prototype.setFlightType = function(flighttype) {
    this.flightType = flighttype;
};

/**
 * Get flight type of the logentry.
 *
 * @method getFlightType
 * @return {string} The flight type (training/solo/check)
 */
ModalLogEntryForm.prototype.getFlightType = function() {
    return this.flightType;
};

/**
 * Check if the modal has an logentry id.
 *
 * @param  {bool} hasfindpirep  Whether find PIREP is enabled
 * @method hasFindPIREP
 * @return {bool}
 */
 ModalLogEntryForm.prototype.hasFindPIREP = function(hasfindpirep) {
    if (typeof hasfindpirep !== 'undefined') {
        this.hasfindpirep = hasfindpirep;
    }
    return this.hasfindpirep;
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

    // Get exercise name
    this.titlePromise = Repository.getExerciseName(this.courseId, this.exerciseId)
        .then(function(response) {
            // Handle the response
            return response.exercisename;
        })
    .fail(Notification.exception);

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

    if (this.hasSessionId()) {
        args.sessionid = this.getSessionId();
    }

    if (typeof formData !== 'undefined') {
        args.formdata = formData;
    }

    // Get the content of the modal
    this.bodyPromise = Fragment.loadFragment('local_booking', 'logentry_form', this.getContextId(), args);

    // Set the body data using the promise
    this.setBody(this.bodyPromise);

    this.bodyPromise.then(function() {
        // Add Find PIREP button
        if (this.hasFindPIREP()) {
            if (!$('#id_error2_p1pirep').length) {
                let p1pirep = $('#id_p1pirep');
                let pirepdiv = p1pirep.parent();

                // Append PIREP search group
                pirepdiv.prepend('<div class="input-group" id="id_pirepgroup">');
                $('#id_pirepgroup').append(p1pirep);
                $('#id_pirepgroup').append('<div class="input-group-append" id="id_find_pirep">');
                $('#id_find_pirep').append('<button type="button" class="btn btn-primary search-icon"><i class="icon ' +
                    'fa fa-search fa-fw " aria-hidden="true"></i></button>');
            }
        }

        // Hide/show elements set training type
        this.doDynamicDisplay();
        this.enableButtons();
        this.setInputMask();
        this.registerChangeListeners();

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
 * Sets the input mask for all masked elements.
 *
 * @method setInputMask
 */
ModalLogEntryForm.prototype.setInputMask = function() {
    // Mask flight times < 5hrs and departure/arrival times to 24hr format
    // Mask flight time elements based on training type
    if ($(Selectors.bookingwrapper).data('trainingtype') == "Dual") {
        Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_dualtime"));
    } else if ($(Selectors.bookingwrapper).data('trainingtype') == "Multicrew") {
        Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_multipilottime"));

        // TODO: Instructor logentry edit:
        // Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_copilottime"));
    }
    // Check for the flight type before masking related elements
    if ($("input[name='flighttypehidden']").val() == 'check') {
        Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_picustime"));

        // TODO: Instructor logentry edit:
        // Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_checkpilottime"));
    } else if ($("input[name='flighttypehidden']").val() == 'solo') {
        Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_flighttime"));
    } else {
        Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_groundtime"));
    }

    // TODO: Instructor logentry edit:
    // Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_pictime"));
    // Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_instructortime"));

    // Mask remaining elements
    Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_flighttime"));
    Inputmask({"regex": "^([01]?[0-9]|2[0-3]):[0-5][0-9]"}).mask(document.getElementById("id_deptime"));
    Inputmask({"regex": "^([01]?[0-9]|2[0-3]):[0-5][0-9]"}).mask(document.getElementById("id_arrtime"));
    Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_nighttime"));
    Inputmask({"regex": "^([0]?[0-4]):([0-5]?[0-9])$"}).mask(document.getElementById("id_ifrtime"));
    Inputmask({"regex": "[0-9]"}).mask(document.getElementById("id_landingsp1day"));
    Inputmask({"regex": "[0-9]"}).mask(document.getElementById("id_landingsp1night"));

    // Check for new logentries noting landings of both instructor and student
    if (this.getLogentryId() == 0) {
        Inputmask({"regex": "[0-9]"}).mask(document.getElementById("id_landingsp2day"));
        Inputmask({"regex": "[0-9]"}).mask(document.getElementById("id_landingsp2night"));
    }
};

/**
 * Sets the input mask for all masked elements.
 *
 * @method registerChangeListeners
 */
ModalLogEntryForm.prototype.registerChangeListeners = function() {

    // PIREP search trigger
    if (this.hasFindPIREP()) {
        var pirepbutton = $('#id_find_pirep');
        pirepbutton.on('click', function(e) {
            if (!isNaN($('#id_p1pirep').val())) {
                return this.getPIREPData(e);
            }
        }.bind(this));
    }

    // Update elements based on selected flighttype (Training/Solo)
    var flighttype = $('input[name="flighttype"]');
    flighttype.on('change', function() {
        // Assign the value selected in the radio buttons to the hidden flight type element
        $("input[name='flighttypehidden']").val($("input[name='flighttype']:checked").val());
        this.doDynamicDisplay();
        this.applyFlightTimes();
    }.bind(this));

    // Update flight times when the test result status is changed
    var passfail = $('input[name="passfail"]');
    passfail.on('change', function() {
        this.doDynamicDisplay();
        this.applyFlightTimes();
    }.bind(this));

    // Update flight times when the test result status is changed
    var flightrule = $('input[name="flightrule"]');
    flightrule.on('change', function() {
        this.doDynamicDisplay();
        this.applyFlightTimes();
    }.bind(this));

    // The onchange property has to be set due to the flighttime element being masked
    flighttime = document.getElementById("id_flighttime");
    flighttime.onchange = function() {
        return this.applyFlightTimes(true);
    }.bind(this);
};

/**
 * Get and populate log entry data from
 * the server's PIREP lookup service.
 *
 * @method getPIREPData
 * @param  {object} e The triggered event
 * @return {object} pirep of the logentry
 */
 ModalLogEntryForm.prototype.getPIREPData = function(e) {
    var loadingContainer = this.getFooter().find(SELECTORS.LOADING_ICON_CONTAINER);
    rule = $(Selectors.bookingwrapper).data('trainingtype');
    pirepdiv = $('#id_p1pirep').parent();
    pirep = $('#id_p1pirep').val();
    courseid = this.getCourseId();
    exerciseid = this.getExerciseId();
    sessionid = this.getSessionId();

    if (pirep != '') {
        loadingContainer.removeClass('hidden');
        return Repository.findPirep(pirep, courseid, $('#id_p1id').val(), exerciseid)
            .then(function(response) {
                // Clean up any past client side errors
                $('#id_p1pirep').removeClass('is-invalid');

                // Handle the response
                if (response.result) {
                    // Get found message
                    if (!$('#id_valid_p1pirep').length) {
                        Str.get_string('pirepfound', 'local_booking').then(function(string) {
                            pirepdiv.append('<div class="form-control-feedback valid-feedback" id="id_valid_p1pirep" ' +
                            'tabindex="0" style="">' + string + '</div>');
                            $('#id_p1pirep').addClass('is-valid');
                            return string;
                        })
                        .fail(Notification.exception);
                    }

                    // Update elements with PIREP returned data depending on
                    // solo flight status and flight rule (Dual/Multicrew)
                    var d = new Date(response.logentry.flightdate * 1000),
                        month = '' + (d.getMonth() + 1),
                        day = '' + d.getDate(),
                        year = d.getFullYear(),
                        time = response.logentry.deptime,
                        hour = time.substring(0, 2),
                        minute = time.substring(time.indexOf(':') + 1, time.length);
                    // Fill remaining data
                    $('#id_flightdate_day').val(day);
                    $('#id_flightdate_month').val(month);
                    $('#id_flightdate_year').val(year);
                    $('#id_flightdate_hour').val(hour);
                    $('#id_flightdate_minute').val(minute);
                    $('input[name="linkedpirep"]').val(response.logentry.linkedpirep);
                    $('#id_flighttime').val(response.logentry.flighttime);
                    $('#id_depicao').val(response.logentry.depicao);
                    $('#id_arricao').val(response.logentry.arricao);
                    $('#id_deptime').val(response.logentry.deptime);
                    $('#id_arrtime').val(response.logentry.arrtime);
                    $('#id_callsign').val(response.logentry.callsign);
                    $('#id_aircraft').val(response.logentry.aircraft);
                    $('#id_aircraftreg').val(response.logentry.aircraftreg);
                    $('#id_enginetype').val(response.logentry.enginetype);
                    $('#id_route').val(response.logentry.route);
                    $('#id_fstd').val(response.logentry.fstd);
                    this.doDynamicDisplay();
                    this.applyFlightTimes();
                } else {
                    // Display inline error for the PIREP then clear it and give it focus
                    $('#id_p1pirep').addClass('is-invalid');
                    if (!$('#id_error2_p1pirep').length) {
                        pirepdiv.append('<div class="form-control-feedback invalid-feedback" id="id_error2_p1pirep" ' +
                            'tabindex="0" style="">' + response.warnings[0].message + '</div>');
                        $('#id_p1pirep').val('');
                        $('#id_p1pirep').focus();
                    } else {
                        $('#id_error2_p1pirep').show();
                    }
                    // Make sure the find button is always after the P1 PIREP element
                    if (this.hasFindPIREP()) {
                        $('#id_p1pirep').parent().each(function() {
                            $('#id_find_pirep').insertAfter($('#id_p1pirep'), this);
                        });
                    }
                }

                return;
            }.bind(this))
            .always(function() {
                // Regardless of success or error we should always stop
                // the loading icon and re-enable the buttons.
                loadingContainer.addClass('hidden');
                e.preventDefault();
                e.stopPropagation();

                return;
            })
            .fail(Notification.exception);
    } else {
        return '';
    }
};

/**
 * Apply default values set flight operation
 * Dual vs Multicrew taking solo flights in conisderation
 *
 * Populates a log book entry with a modal form data.
 *
 * @param {bool} force Force applying flight times
 * @method applyFlightTimes
 */
 ModalLogEntryForm.prototype.applyFlightTimes = function(force) {

    const rule = $(Selectors.bookingwrapper).data('trainingtype');
    var flighttype = $("input[name='flighttypehidden']").val(),
        flighttime = $('#id_flighttime').val(),
        passfail = $("input[name='passfail']:checked").val(),
        ifr = $("input[name='flightrule']:checked").val() == 'ifr',
        editmode = this.getLogentryId() != 0,
        newentry = !editmode;

    // Check the training rule type
    if (newentry || force) {
        if (flighttype == 'training' || (flighttype == 'check' && passfail == 'fail')) {
            $('#id_dualtime').val(rule == 'Dual' ? flighttime : '');
            $('#id_ifrtime').val(ifr ? flighttime : '');
            $('#id_multipilottime').val(rule == 'Multicrew' ? flighttime : '');
            $('#id_copilottime').val(rule == 'Multicrew' ? flighttime : '');
            $('#id_checkpilottime').val(flighttype == 'check' ? flighttime : '');
            $('#id_picustime').val('');
        } else if (flighttype == 'solo') {
            $('#id_ifrtime').val(ifr ? flighttime : '');
            $('#id_dualtime').val('');
            $('#id_multipilottime').val('');
            $('#id_copilottime').val('');
            $('#id_picustime').val('');
        } else if (flighttype == 'check' || passfail == 'pass') {
            $('#id_ifrtime').val(ifr ? flighttime : '');
            $('#id_picustime').val(flighttime);
            $('#id_checkpilottime').val(flighttime);
            $('#id_multipilottime').val(rule == 'Multicrew' ? flighttime : '');
            $('#id_dualtime').val('');
            $('#id_copilottime').val('');
        }
    }
 };

/**
 * Apply default values set flight operation
 * Dual vs Multicrew taking solo flights in conisderation
 *
 * Populates a log book entry with a modal form data.
 *
 * @method doDynamicDisplay
 */
 ModalLogEntryForm.prototype.doDynamicDisplay = function() {

    const rule = $(Selectors.bookingwrapper).data('trainingtype');
    var flighttype = $("input[name='flighttypehidden']").val(),
        passfail = $("input[name='passfail']:checked").val(),
        ifr = $("input[name='flightrule']:checked").val() == 'ifr',
        editmode = this.getLogentryId() != 0;

    // Toggle the display of elements depending on flight type
    var toggle = function(div, element, show, value, force = false) {
        if (typeof div !== 'undefined' && typeof element !== 'undefined') {
            // Check expanded status of Advanced elements
            var ariaexpanded = this.getForm().find('[aria-expanded="true"]').attr('aria-expanded');
            if (ariaexpanded || force) {
                if (show) {
                    $(div).slideDown('fast');
                } else {
                    $(div).slideUp('fast');
                }
            }

            // Process the passed value
            if (typeof value !== 'undefined') {
                $(element).val(value);
            }
        }
    }.bind(this);

    // Set dynamic element labels
    var setLabel = function(element, editlabelkey, labelkey) {
        if (editmode) {
            Str.get_string(editlabelkey, 'local_booking').then(function(label) {
                $(element).text(label);
                return label;
            }).fail(Notification.exception);
        } else {
            Str.get_string(labelkey, 'local_booking').then(function(label) {
                $(element).text(label);
                return label;
            }).fail(Notification.exception);
        }
    };

    // TODO: Instructor logentry edit:
    // // Toggle PIC time in new and edit
    // toggle('#fitem_id_pictime', '#id_pictime', $('#id_pictime').val());

    // Check the training rule type
    if (flighttype == 'training' || (flighttype == 'check' && passfail == 'fail')) {

        // Set P1 id and label, and handle edit mode
        if (flighttype == 'training' || editmode) {
            p1label = rule == 'Dual' ? 'p1dual' : 'p1multicrew';
        } else {
            p1label = 'examiner';
        }

        setLabel("label[for='id_p1pirep']", 'pirep', flighttype == 'training' ? 'instpirep' : 'examinerpirep');
        setLabel("label[for='id_p1id']", p1label, p1label);

        // Toggle showing elements conditionally for training flight and failed check flights
        toggle('#fitem_id_p2id', '#id_p2id', true);
        toggle('#fitem_id_dualtime', '#id_dualtime', rule == 'Dual');
        toggle('#fitem_id_groundtime', '#id_groundtime', true, $('#id_groundtime').val(), true);
        toggle('#fitem_id_ifrtime', '#id_ifrtime', ifr);
        toggle('#fitem_id_nighttime', '#id_nighttime', ifr);
        toggle('#fitem_id_multipilottime', '#id_multipilottime', rule == 'Multicrew');
        toggle('#fitem_id_copilottime', '#id_copilottime', rule == 'Multicrew');

        // Toggle hiding elements
        toggle('#fitem_id_checkpilottime', '#id_checkpilottime', false);
        toggle('#fitem_id_picustime', '#id_picustime', false);

        // TODO: Instructor logentry edit:
        // // Toggle instructor time in new and edit
        // toggle('#fitem_id_instructortime', '#id_instructortime', ((newentry && flighttype != 'check') || (editmode &&
        //     $('#id_instructortime').val() != '')), editmode || flighttype == 'check' ?
        //     $('#id_instructortime').val() ? $('#id_instructortime').val() : '' : flighttime);
        // TODO: Instructor logentry edit:
        // toggle('#fitem_id_checkpilottime', '#id_checkpilottime', false, '');

    } else if (flighttype == 'solo') {
        // Set P1 PIREP label for student solo flights, and handle edit mode
        setLabel("label[for='id_p1pirep']", 'logbooksolopirep', 'logbooksolopirep');
        setLabel("label[for='id_p1id']", 'p2dual', 'p2dual');
        if (!editmode) {
            $('#id_p1id').val($('#id_p2id').val());
        }

        // Toggle showing elements for solo flight
        toggle('#fitem_id_ifrtime', '#id_ifrtime', ifr);
        $('#id_landingsp1day').val('1');
        $('#id_landingsp2day').val('0');

        // Toggle hiding elements for solo flights
        toggle('#fitem_id_p2id', '#id_p2id', false, $('#id_p2id').val(), true);
        toggle('#fitem_id_groundtime', '#id_groundtime', false, '', true);
        toggle('#fitem_id_dualtime', '#id_dualtime', false);
        toggle('#fitem_id_multipilottime', '#id_multipilottime', false);
        toggle('#fitem_id_copilottime', '#id_copilottime', false);
        toggle('#fitem_id_picustime', '#id_picustime', false);
        toggle('#fgroup_id_landingsp2', '#id_landingsp2', false);

        // TODO: Instructor logentry edit:
        // toggle('#fitem_id_instructortime', '#id_instructortime', false);
        // TODO: Instructor logentry edit:
        // toggle('#fitem_id_checkpilottime', '#id_checkpilottime', false);

    } else if (flighttype == 'check' || passfail == 'pass') {
        // Get id and label for P1 and PIREP
        setLabel("label[for='id_p1pirep']", 'pirep', 'examinerpirep');
        setLabel("label[for='id_p1id']", 'examiner', 'examiner');

        // Toggle showing elements for passsed check flight
        toggle('#fitem_id_p2id', '#id_p2id', true);
        toggle('#fitem_id_ifrtime', '#id_ifrtime', ifr);
        toggle('#fitem_id_multipilottime', '#id_multipilottime', rule == 'Multicrew');
        toggle('#fitem_id_picustime', '#id_picustime', true);

        // Toggle hiding elements for passed check flight
        toggle('#fitem_id_dualtime', '#id_dualtime', false);
        toggle('#fitem_id_copilottime', '#id_copilottime', false);

        // TODO: Instructor logentry edit:
        // toggle('#fitem_id_instructortime', '#id_instructortime', false);
        toggle('#fitem_id_checkpilottime', '#id_checkpilottime', false);
    }
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
    this.setSessionId(null);
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

    // Set ground time to 0 for none training flights as it is a required a value
    if (!($("input[name='flighttypehidden']").val() == 'training')) {
        $('#id_groundtime').val("00:00");
    }

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
        + '&exerciseid=' + this.exerciseId + '&sessionid=' + this.sessionId + '&userid=' + this.userId;

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
                // check if the logentry is from the prgression view
                // logentry from the confirmation view
                // The hide function unsets the form data so grab this before the hide.
                var isExisting = this.hasLogentryId();

                // No problemo! Our work here is done.
                this.hide();

                // Trigger the appropriate logbook event so that the view can be updated.
                if (isExisting) {
                    $('body').trigger(LogbookEvents.logentryupdated, [response.logentry]);
                } else {
                    $('body').trigger(LogbookEvents.logentrycreated, [response.logentry]);
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
        FormEvents.notifyFormSubmittedByJavascript(this.getForm()[0]);

        this.save();

        // Stop the form from actually submitting and prevent it's
        // propagation because we have already handled the event.
        e.preventDefault();
        e.stopPropagation();
    }.bind(this));

    // Register a lister to Update elements for Advanced section when expanded, after its loaded
    this.getModal().on('click', 'a[aria-expanded]', function() {
        setTimeout(function() {
            this.doDynamicDisplay();
         }.bind(this), 100);
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
