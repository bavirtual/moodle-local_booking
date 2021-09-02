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
 * This module is responsible for handle calendar day and upcoming view.
 *
 * @module     local_booking/calendar
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
        'jquery',
        'core/notification',
        'local_booking/selectors',
        'local_booking/events',
        'local_booking/view_manager',
        'local_booking/crud'
    ],
    function(
        $,
        Notification,
        CalendarSelectors,
        CalendarEvents,
        CalendarViewManager,
        CalendarCrud
    ) {

        var registerEventListeners = function(root, type) {
            var body = $('body');

            CalendarCrud.registerRemove(root);

            var reloadFunction = 'reloadCurrent' + type.charAt(0).toUpperCase() + type.slice(1);

            body.on(CalendarEvents.created, function() {
                CalendarViewManager[reloadFunction](root);
            });
            body.on(CalendarEvents.deleted, function() {
                CalendarViewManager[reloadFunction](root);
            });
            body.on(CalendarEvents.updated, function() {
                CalendarViewManager[reloadFunction](root);
            });

            root.on('change', CalendarSelectors.courseSelector, function() {
                var selectElement = $(this);
                var courseId = selectElement.val();
                CalendarViewManager[reloadFunction](root, courseId, null)
                    .then(function() {
                        // We need to get the selector again because the content has changed.
                        return root.find(CalendarSelectors.courseSelector).val(courseId);
                    })
                    .then(function() {
                        window.history.pushState({}, '', '?course=' + courseId);

                        return;
                    })
                    .fail(Notification.exception);
            });

            body.on(CalendarEvents.filterChanged, function(e, data) {
                var daysWithEvent = root.find(CalendarSelectors.eventType[data.type]);
                if (data.hidden == true) {
                    daysWithEvent.addClass('hidden');
                } else {
                    daysWithEvent.removeClass('hidden');
                }
            });

            var eventFormPromise = CalendarCrud.registerEventFormModal(root);
            CalendarCrud.registerEditListeners(root, eventFormPromise);
        };

        return {
            init: function(root, type) {
                root = $(root);

                CalendarViewManager.init(root, type);
                registerEventListeners(root, type);
            }
        };
    });
