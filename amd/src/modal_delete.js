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
 * Contain the logic for the delete modal.
 * Improvised from core_calendar.
 *
 * @module     local_booking/logentry_delete
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as CustomEvents from 'core/custom_interaction_events';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';

const SELECTORS = {
    DELETE_BUTTON: '[data-action="delete"]',
    CANCEL_BUTTON: '[data-action="cancel"]',
};


/**
 * Constructor for the Modal.
 *
 * @class
 * @param {object} root The root jQuery element for the modal
 */
export default class ModalDelete extends Modal {
    static TYPE = 'local_booking-modal_delete';
    static TEMPLATE = 'local_booking/logentry_delete_modal';

    constructor(root) {
        super(root);
        this.setRemoveOnClose(true);
    }

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    registerEventListeners() {
        // Apply parent event listeners.
        super.registerEventListeners(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.DELETE_BUTTON, function(e, data) {
            const deleteEvent = $.Event(ModalEvents.save);
            this.getRoot().trigger(deleteEvent, this);

            if (!deleteEvent.isDefaultPrevented()) {
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

ModalDelete.registerModalType();