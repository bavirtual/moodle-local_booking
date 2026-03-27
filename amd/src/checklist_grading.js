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
 * This module is the highest level module for the calendar. It is
 * responsible for initializing all of the components required for
 * the calendar to run. It also coordinates the interaction between
 * components by listening for and responding to different events
 * triggered within the calendar UI.
 *
 *  Checklist view management.
 *
 * @module     local_booking/checklist
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define([
    'jquery',
    'local_booking/repository',
    'core/notification'
    ],
    function(
            $,
            Repository,
            Notification
    ) {

    return {
        init: function() {
            this.attachEventHandlers();
        },

        attachEventHandlers: function() {
            var self = this;

            // Handle check button clicks
            $(document).on('click', '.toggle-input', function(e) {
                var $toggle = $(this);
                var itemid = $toggle.data('itemid');
                var state = $('#item-' + itemid).prop('checked') ? 1 : 0; // Default to CHECKLIST_TEACHERMARK_UNDECIDED

                // Auto-save (or you can save on button click)
                self.saveChecklistItem(e, itemid, state);
            });
        },

        saveChecklistItem: function(e, itemid, state) {
            var self = this;
            var toggle = $(e.currentTarget);
            var courseid = new URLSearchParams(window.location.search).get('courseid');
            var studentid = new URLSearchParams(window.location.search).get('userid');
            var bookingid = new URLSearchParams(window.location.search).get('bookingid');

            return Repository.saveChecklistItem(courseid, studentid, bookingid, itemid, state)
            .then(function(response) {
                if (response.success) {
                    $('#teacher-timestamp-' + itemid).text(response.timestamp);
                    $('#progress-text-' + response.checklistid).text(response.count);
                    $('#progress-percent-' + response.checklistid).text(response.progress);
                    self.showCallout(toggle, response.message);
                }
                return;
            })
            .always(function() {
                Notification.fetchNotifications();
                return;
            })
            .fail(Notification.exception);
        },

        showCallout: function(toggle, message) {
            var callout = $('#callout');

            // 1. Set the dynamic text from the response
            callout.text(message || "Saved!"); // Fallback to "Saved!" if message is empty

            // Momentarily show it so we can measure its height
            // (jQuery can't measure height on display:none elements)
            callout.css({
                visibility: 'hidden',
                display: 'block',
                position: 'fixed'
            });

            // Using getBoundingClientRect for more reliable fixed positioning
            var rect = toggle[0].getBoundingClientRect();
            var calloutHeight = callout.outerHeight();

            // Position and animate
            callout.css({
                top: (rect.top - calloutHeight - 10) + 'px',
                left: rect.left + 'px',
                visibility: 'visible',
                display: 'none' // Set back to none so fadeIn() can work
            });

            // Show and fade out
            callout.stop(true, true).fadeIn(200).delay(300).fadeOut(600);
        },
    };
});
