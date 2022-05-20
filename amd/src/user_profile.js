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
 * Controls the message preference page.
 *
 * @module     local_booking/administration
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/str',
    'core/notification',
    'local_booking/repository',
    'local_booking/selectors'
],
function(
    $,
    Str,
    Notification,
    Repository,
    Selectors
) {

    /**
     * Set the endorsed message.
     *
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {string} endorse     Endorse true/false.
     * @param  {object} root        The root element.
     * @method setEndorsement
     */
     const setEndorsement = function(courseId, userId, endorse, root) {
        // Get endorsement information (endorser, date, and message) from template
        let userProfile = root.find(Selectors.userprofilewrapper),
        endorser = userProfile.data('endorser'),
        endorsedate = (new Date()).toDateString(),
        endorsestr = endorse ? 'endorsementmgs' : 'skilltestendorse';

        // Process endorsement message
        let endorsemsgPromise = Str.get_string(endorsestr, 'local_booking', {endorser: endorser, endorsedate: endorsedate});
        endorsemsgPromise.then(function(message) {
            // Set endorsement message
            $('#endorsement-label').html(message);
            return message;
        }.bind(this))
        .fail(Notification.exception);

        // Persist endorsement in user preferences
        processUserPreference('endorse', endorse, courseId, userId, 'endorse');
        processUserPreference('endorser', endorse ? endorser : '', courseId, userId, 'endorse');
        processUserPreference('endorsedate', endorse ? endorsedate : '', courseId, userId, 'endorse');

        // Set Skill Form button status
        $('#skillform').prop('disabled', !endorse);
    };

    /**
     * Process the user setting preference depending on the passed
     * preference and value pairs.
     *
     * @param  {string} preference  The  preferencekey of the setting.
     * @param  {string} value       The value data.
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {string} element     The element to handl GUI.
     * @method processUserPreference
     * @return {bool}
     */
     const processUserPreference = function(preference, value, courseId, userId, element) {
        return Repository.updateUserPreferences(preference, value, courseId, userId)
        .then()
        .always(function() {
            Notification.fetchNotifications();
        })
        .fail(function(ex) {
            Notification.exception(ex);
            // Handle toggle failure
            $('#' + element).prop('checked', !$('#' + element).prop('checked'));
            return;
        });
    };

    /**
     * Process the user suspension status.
     *
     * @param  {bool}   suspend     Suspend true/false.
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @method processSuspendedStatus
     * @return {bool}
     */
     const processSuspendedStatus = function(suspend, courseId, userId) {
        return Repository.updateSuspendedStatus(suspend, courseId, userId)
        .then()
        .always(function() {
            Notification.fetchNotifications();
        })
        .fail(function(ex) {
            Notification.exception(ex);
            // Handle toggle failure
            $('#suspended').prop('checked', !$('#suspended').prop('checked'));
            return;
        });
    };

    /**
     * Process the user group membership status on-hold and keep active.
     *
     * @param  {string} key         The key of the setting.
     * @param  {string} value      Join or leave true/false.
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {object} root        The root element.
     * @method processGroup
     * @return {bool}
     */
     const processGroup = function(key, value, courseId, userId, root) {
        // Get the group name from the template
        let userProfile = root.find(Selectors.userprofilewrapper),
        group = userProfile.data(key + 'group');

        return Repository.updateGroup(group, value, courseId, userId)
        .then()
        .always(function() {
            Notification.fetchNotifications();
        })
        .fail(function(ex) {
            Notification.exception(ex);
            // Handle toggle failure
            $('#' + key).prop('checked', !$('#' + key).prop('checked'));
            return;
        });
    };

    /**
     * Create all of the event listeners for the message preferences page.
     *
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {string} key         The key of the setting.
     * @param  {string} value       Setting value.
     * @param  {object} root        The root element.
     * @method processSetting
     */
     const processSetting = function(courseId, userId, key, value, root) {

        // Show progressing icon
        startLoading($('#' + key + '-region'));

        // Process the different toggle actions
        switch (key) {
            case 'endorse':
                // Process student endorsement and handle UI
                setEndorsement(courseId, userId, value, root);
                break;
            case 'availabilityoverride':
                // Process availability override in user preferences and handle UI
                processUserPreference(key, value, courseId, userId, key);
                break;
            case 'suspend':
                // Toggle enrolment status suspension on/off and handle UI
                processSuspendedStatus(value, courseId, userId);
                break;
            case 'onhold':
            case 'keepactive':
                // Process keep active in user preferences and handle UI
                processGroup(key, value, courseId, userId, root);
                break;
        }

        // Stop showing progressing icon
        stopLoading($('#' + key + '-region'));
    };

    /**
     * Create all of the event listeners for the message preferences page.
     *
     * @param  {object} root    The root element.
     * @method registerEventListeners
     */
    const registerEventListeners = function(root) {

        var userProfile = root.find(Selectors.userprofilewrapper),
        courseId = userProfile.data('courseid'),
        userId = userProfile.data('userid');

        // Handle endorsement toggle clicks
        $('#endorse').click(function() {
            processSetting(courseId, userId, 'endorse', this.checked, root);
        });

        // Handle suspension toggle clicks
        $('#suspended').click(function() {
            processSetting(courseId, userId, 'suspend', this.checked);
        });

        // Handle on-hold toggle clicks
        $('#onhold').click(function() {
            processSetting(courseId, userId, 'onhold', this.checked, root);
        });

        // Handle keep active toggle clicks
        $('#keepactive').click(function() {
            processSetting(courseId, userId, 'keepactive', this.checked, root);
        });

        // Handle restriction override toggle clicks
        $('#restrictionoverride').click(function() {
            processSetting(courseId, userId, 'availabilityoverride', this.checked, root);
        });
    };

    /**
     * Set the element state to loading.
     *
     * @method  startLoading
     * @param   {object} root The container element
     */
    const startLoading = (root) => {
        const loadingIconContainer = root.find(Selectors.containers.loadingIcon);
        loadingIconContainer.removeClass('hidden');
    };

    /**
     * Unset the element state of loading.
     *
     * @method  stopLoading
     * @param   {object} root The container element
     */
    const stopLoading = (root) => {
        const loadingIconContainer = root.find(Selectors.containers.loadingIcon);
        loadingIconContainer.addClass('hidden');
    };

    return {
        init: function(root) {
            root = $(root);
            registerEventListeners(root);
        }
    };
});
