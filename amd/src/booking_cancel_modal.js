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
 * Contain the logic for the cancel a booking modal.
 *
 * @module     local_booking/booking_cancel_modal
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as CustomEvents from 'core/custom_interaction_events';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import * as Repository from 'local_booking/repository';
import * as BookingEvents from 'local_booking/events';

const SELECTORS = {
    SAVE_BUTTON: '[data-action="save"]',
    CANCEL_BUTTON: '[data-action="cancel"]',
    LOADING_ICON_CONTAINER: '[data-region="loading-icon-container"]',
};

/**
 * Constructor for the Modal.
 *
 * @class
 * @param {object} root The root jQuery element for the modal
 */
export default class ModalCancel extends Modal {
    static TYPE = 'local_booking-booking_cancel_modal';
    static TEMPLATE = 'local_booking/dashboard_mybookings_cancel_modal';

    constructor(root) {
        super(root);
        this.bookingId = 0;
        this.saveButton = this.getFooter().find(SELECTORS.SAVE_BUTTON);
        this.setRemoveOnClose(true);
    }

    /**
     * Set the booking id.
     *
     * @method setBookingId
     * @param {int} id The booking id
     */
    setBookingId(id) {
        this.bookingId = id;
    }

    /**
     * Save the comments and no-show information if exists
     *
     * @method save
     * @return {object} A promise
     */
    save() {
        let loadingContainer = this.saveButton.find(SELECTORS.LOADING_ICON_CONTAINER);
        loadingContainer.removeClass('hidden');
        this.disableButtons();

        let comment = $("#comment").val();

        // Send the request data to the server for processing.
        return Repository.cancelBooking(this.bookingId, comment, false)
            .then(function(response) {
                if (response.result) {
                    $('body').trigger(BookingEvents.bookingcanceled);
                }
                return;
            })
            .always(function() {
                loadingContainer.addClass('hidden');
                Notification.fetchNotifications();
                return;
            })
            .fail(Notification.exception);

    }

    /**
     * Disable the buttons in the footer.
     *
     * @method disableButtons
     */
    disableButtons() {
        this.saveButton.prop('disabled', true);
    }

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    registerEventListeners() {
        // Apply parent event listeners.
        super.registerEventListeners(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.SAVE_BUTTON, function(e, data) {
            const saveEvent = $.Event(ModalEvents.save);
            this.getRoot().trigger(saveEvent, this);

            if (!saveEvent.isDefaultPrevented()) {
                this.save();
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, function(e, data) {
            const cancelEvent = $.Event(ModalEvents.cancel);
            this.getRoot().trigger(cancelEvent, this);

            if (!cancelEvent.isDefaultPrevented()) {
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));
    }
}

ModalCancel.registerModalType();