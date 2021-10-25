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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        viewSelector: 'div[data-region="view-selector"]',
        actions: {
            edit: '[data-action="edit"]',
            deleteLogentry: '[data-action="delete"]',
            viewLogEntry: '[data-action="view-logentry"]',
            gotoFeedback: '[data-action="feedback"]',
        },
        today: '.today',
        day: '[data-region="day"]',
        calendarwrapper: '.calendarwrapper',
        progressionwrapper: '.progressionwrapper',
        mybookingswrapper: '.mybookingswrapper',
        table: '.calendartable',
        logentryItem: '[data-type="logentry"]',
        links: {
            navLink: '.calendarwrapper .arrow_link',
        },
        containers: {
            loadingIcon: '[data-region="overlay-icon-container"]',
        },
    };
});
