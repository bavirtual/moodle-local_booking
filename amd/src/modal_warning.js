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
 * Generic warning modal.
 *
 * @module     local_booking/modal_warning
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as CustomEvents from 'core/custom_interaction_events';
import Modal from 'core/modal';
import ModalEvents from 'local_booking/events';
import Selectors from 'local_booking/selectors';

/**
 * Constructor for the Modal.
 *
 * @class
 * @param {object} root The root jQuery element for the modal
 */
export default class ModalWarning extends Modal {
    static TYPE = 'local_booking-modal_warning';

    constructor(root) {
        super(root);
        this.data = null;
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

        // Handle OK button event
        this.getModal().on(CustomEvents.events.activate, Selectors.regions.okbutton, function(e, data) {
            let okEvent = $.Event(ModalEvents.okEvent, {'eventData': this.data});
            this.getRoot().trigger(okEvent, this);

            if (!okEvent.isDefaultPrevented()) {
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));

        // Handle YES button event
        this.getModal().on(CustomEvents.events.activate, Selectors.regions.yesbutton, function(e, data) {
            let yesEvent = $.Event(ModalEvents.yesEvent, {'eventData': this.data});
            this.getRoot().trigger(yesEvent, this);

            if (!yesEvent.isDefaultPrevented()) {
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));

        // Handle NO button event
        this.getModal().on(CustomEvents.events.activate, Selectors.regions.nobutton, function() {
            let noEvent = $.Event(ModalEvents.noEvent, {'eventData': this.data});
            this.getRoot().trigger(noEvent, this);

            if (!noEvent.isDefaultPrevented()) {
                this.hide();
            }
        }.bind(this));
    }

    /**
     * Set custom data object to attach to events.
     *
     * @param  {array} data Any additional message parameters.
     * @method setData
     */
    setData(data) {
        this.data = data;

    }

    /**
     * Get custom data object to attach to events.
     *
     * @method setData
     */
    getData() {
        return this.data;

    }
}

ModalWarning.registerModalType();