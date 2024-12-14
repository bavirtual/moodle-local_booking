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
 * This module is responsible UI selectors for both
 * availability and booking UIs.
 * Improvised from core_calendar.
 *
 * @module     local_booking/selectors
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        viewselector: 'div[data-region="view-selector"]',
        actions: {
            add: '[data-action="add"]',
            edit: '[data-action="edit"]',
            deleteLogentry: '[data-action="delete"]',
            viewLogEntry: '[data-action="view-logentry"]',
            gotoFeedback: '[data-action="feedback"]',
            bookingConfirm: '[data-action="booking-confirm"]',
        },
        logentryitem: '[data-type="logentry"]',
        today: '.today',
        wrappers: {
            actionbarwrapper: '.tertiary-navigation',
            weekwrapper: '.weekwrapper',
            calendarwrapper: '.calendarwrapper',
            bookingwrapper: '.bookingwrapper',
            logbookwrapper: '.logbookwrapper',
            mybookingswrapper: '.mybookingswrapper',
            userprofilewrapper: '.userprofilewrapper',
        },
        logentrymodal: '.modal-body',
        table: '.calendartable',
        links: {
            navLink: '.btn-outline-primary',
        },
        containers: {
            loadingIcon: '[data-region="overlay-icon-container"]',
            summaryForm: '[data-region="summary-modal-container"]',
            logEntry: '[data-region="logentry-container"]',
            logbook: '[data-region="logbook-container"]',
            content: '[data-region="view-content"]',
            loadingPlaceholder: '[data-region="loading-placeholder"]',
        },
        regions: {
            day: '[data-region="day"]',
            daytimeslot: "[data-action='day-time-slot']",
            session: '[data-region="session-entry"]',
            bookingconfirmation: '[data-region="session-booking-confirmation"]',
            slotsweek: '[data-region="slots-week"]',
            okbutton: '[data-action="ok"]',
            yesbutton: '[data-action="yes"]',
            nobutton: '[data-action="no"]',
            savebutton: "[data-region='save-button']",
            bookbutton: "[data-region='book-button']",
            copybutton: "[data-region='copy-button']",
            pastebutton: "[data-region='paste-button']",
            clearbutton: "[data-region='clear-button']",
            searchbutton: '[data-region="search-button"]',
            cancelbutton: '[data-region="cancel-button"]',
            noshowbutton: '[data-region="noshow-button"]',
            studentsfilter: '[data-region="students-filter"]',
        },
        toggle: '[toggle-input]',
        onhold: '[onhold-toggle]',
        expandsection: 'a[aria-expanded]',
    };
});
