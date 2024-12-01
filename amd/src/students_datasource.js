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
 * Students datasource.
 *
 * This module is compatible with core/form-autocomplete.
 *
 * @module     local_booking/students_datasource
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    return /** @alias module:local_booking/students_datasource */ {

        /**
         * Get student names.
         *
         * @param {Number} courseId The course id
         * @param {String} wildcard The wildcard search value
         * @return {Promise}
         */
        get_student_names: function(courseId, wildcard) {
            const request = {
                methodname: 'local_booking_get_student_names',
                args: {
                    courseid: courseId,
                    wildcard,
                }
            };

            return Ajax.call([request])[0];
        },

        /**
         * Process the results for auto complete elements.
         *
         * @param {String} selector The selector of the autocomplete element.
         * @param {Array} results An array or results.
         * @return {Array} New array of results.
         */
        processResults: function(selector, results) {
            var options = [];
            $.each(results, function(index, data) {
                options.push({
                    value: data.userid,
                    label: data.fullname
                });
            });
            return options;
        },

        /**
         * Source of data for Ajax element.
         *
         * @param {String} selector The selector of the auto complete element.
         * @param {String} wildcard The wildcard search string.
         * @param {Function} callback A callback function receiving an array of results.
         */
        /* eslint-disable promise/no-callback-in-promise */
        transport: function(selector, wildcard, callback) {
            var el = $(selector),
                courseId = el.data('courseid');

            $('.felement').removeClass('flex-wrap');
            $('.felement').addClass('col-md-12').removeClass('col-md-9').removeClass('flex-wrap');

            if (!courseId) {
                throw new Error('The attribute data-courseid is required on ' + selector);
            }
            this.get_student_names(courseId, wildcard).then(callback).catch(Notification.exception);
        }
    };

});
