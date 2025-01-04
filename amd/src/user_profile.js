/* eslint-disable no-bitwise */
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
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
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
     * Create all of the event listeners for the message preferences page.
     *
     * @method registerEventListeners
     * @param  {object} root    The root element.
     */
    const registerEventListeners = function(root) {

        var userProfile = root.find(Selectors.wrappers.userprofilewrapper),
        courseId = userProfile.data('courseid'),
        userId = userProfile.data('userid');

        // Handle endorsement toggle clicks
        $('#endorsed').click(function() {
            processSetting(courseId, userId, 'endorsed', this.checked, root);
        });

        // Handle suspension toggle clicks
        $('#suspended').click(function() {
            processSetting(courseId, userId, 'suspend', this.checked);
        });

        // Handle on-hold toggle clicks
        $('#onhold').click(function() {
            // Add to OnHold group then trigger Keep Active if successful
            processSetting(courseId, userId, 'onhold', this.checked, root)
                .then((response) => {
                    if (0 !== response) {
                        // Toggle 'Keep Alive' so the student is not automatically placed on-hold again
                        $('#keepactive').prop("checked", !this.checked);
                        return processSetting(courseId, userId, 'keepactive', !this.checked, root);
                    }
                    return true;
                })
                .fail(Notification.exception);
        });

        // Handle keep active toggle clicks
        $('#keepactive').click(function() {
            processSetting(courseId, userId, 'keepactive', this.checked, root);
        });

        // Handle restriction override toggle clicks
        $('#overrideminslotperiod').click(function() {
            processSetting(courseId, userId, 'overrideminslotperiod', this.checked, root);
        });

        // Handle show cross-course bookings toggle clicks
        $('#xcoursebookings').click(function() {
            processSetting(courseId, userId, 'xcoursebookings', this.checked, root);
        });

        // Handle save comment click
        $('#save_comment_button').click(function() {
            updateComment(courseId, userId, root);
        });
    };

    /**
     * Create all of the event listeners for the message preferences page.
     *
     * @method processSetting
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {string} key         The key of the setting.
     * @param  {string} value       Setting value.
     * @param  {object} root        The root element.
     */
    const processSetting = function(courseId, userId, key, value, root) {

        // Show progressing icon
        startLoading($('#' + key + '-region'));

        let response;
        // Process the different toggle actions
        switch (key) {
            case 'endorsed':
                // Process student endorsement and handle UI
                response = setEndorsement(courseId, userId, value, root);
                break;
            case 'xcoursebookings':
                // Process availability override in user preferences and handle UI, site level courseid=1
                response = processUserPreference(key, value, 1, userId, key);
                break;
            case 'overrideminslotperiod':
                // Process availability override in user preferences and handle UI
                response = processProgressFlag(key, value, courseId, userId, key);
                break;
            case 'suspend':
                // Toggle enrolment status suspension on/off and handle UI
                response = processSuspendedStatus(value, courseId, userId);
                break;
            case 'onhold':
            case 'keepactive':
                // Process keep active in user preferences and handle UI
                response = processGroup(key, value, courseId, userId, root);
                break;
        }

        // Stop showing progressing icon
        stopLoading($('#' + key + '-region'));

        return response;
    };

    /**
     * Set the endorsed message.
     *
     * @method setEndorsement
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {string} endorse     Endorse true/false.
     * @param  {object} root        The root element.
     */
     const setEndorsement = function(courseId, userId, endorse, root) {
        // Get endorsement information (endorser, date, and message) from template
        let userProfile = root.find(Selectors.wrappers.userprofilewrapper),
        endorserName = userProfile.data('endorsername'),
        endorserId = userProfile.data('endorserid'),
        endorseDate = new Date(),
        endorsedOn = endorseDate.toDateString(),
        endorseDateTS = Math.round(endorseDate.getTime() / 1000),
        endorsestr = endorse ? 'endorsementmsg' : 'skilltestendorsed';

        // Process endorsement message
        let endorseMsgPromise = Str.get_string(endorsestr, 'local_booking', {endorsername: endorserName, endorsedate: endorsedOn});
        endorseMsgPromise.then(function(message) {
            // Set endorsement message
            $('#endorsement-label').html(message);
            // Show/hide recommendation letter link
            if (endorse) {
                $('#endorsement-letter').removeClass('hidden');
            } else {
                $('#endorsement-letter').addClass('hidden');
            }
            return message;
        })
        .fail(Notification.exception);

        // Set endorsement object
        const endorsement = {endorsed: endorse, endorserid: endorserId, endorsedate: endorseDateTS};

        // Process endorsement and notification to be setEndorsement
        let result = processProgressFlag('endorsement', JSON.stringify(endorsement), courseId, userId, 'endorsed');
        result &= processProgressFlag('notifications.endorsement', endorse, courseId, userId);

        return result;
    };

    /**
     * Process student progress update.
     *
     * @method processProgressFlag
     * @param  {string} progressKey The progress key.
     * @param  {string} value       The value data.
     * @param  {string} courseId    The associated course id.
     * @param  {string} studentId   The student.
     * @param  {string} element     The element to handle GUI.
     * @return {bool}
     */
     const processProgressFlag = function(progressKey, value, courseId, studentId, element = null) {

        return Repository.setProgressFlag(progressKey, value, courseId, studentId)
        .then(function(result) {
            return result.saved;
        })
        .always(function() {
            Notification.fetchNotifications();
        })
        .fail(function(ex) {
            Notification.exception(ex);
            // Handle toggle failure
            if (element !== null) {
                $('#' + element).prop('checked', !$('#' + element).prop('checked'));
            }

            return true;
        });
    };

    /**
     * Process the user setting preference depending on the passed
     * preference and value pairs.
     *
     * @method processUserPreference
     * @param  {string} preference  The preference key of the setting.
     * @param  {string} value       The value data.
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {string} element     The element to handle GUI.
     * @return {bool}
     */
     const processUserPreference = function(preference, value, courseId, userId, element) {

        return Repository.setUserPreferences(preference, value, courseId, userId)
        .then(function(result) {
            return result.saved;
        })
        .always(function() {
            Notification.fetchNotifications();
        })
        .fail(function(ex) {
            Notification.exception(ex);
            // Handle toggle failure
            $('#' + element).prop('checked', !$('#' + element).prop('checked'));
            return true;
        });
    };

    /**
     * Process the user suspension status.
     *
     * @method processSuspendedStatus
     * @param  {bool}   suspend     Suspend true/false.
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @return {bool}
     */
     const processSuspendedStatus = function(suspend, courseId, userId) {
        // eslint-disable-next-line promise/valid-params
        return Repository.updateSuspendedStatus(suspend, courseId, userId)
        .then()
        .always(function() {
            Notification.fetchNotifications();
        })
        .fail(function(ex) {
            Notification.exception(ex);
            // Handle toggle failure
            $('#suspended').prop('checked', !$('#suspended').prop('checked'));
            return true;
        });
    };

    /**
     * Process the user group membership status on-hold and keep active.
     *
     * @method processGroup
     * @param  {string} key      The key of the setting.
     * @param  {bool}   add      Join or leave true/false.
     * @param  {string} courseId The course id for suspension.
     * @param  {string} userId   The user id to be suspended.
     * @param  {object} root     The root element.
     * @return {bool}
     */
    const processGroup = function(key, add, courseId, userId, root) {

        // Get the group name from the template
        const userProfile = root.find(Selectors.wrappers.userprofilewrapper),
        groupName = userProfile.data(key + 'group');

        // Add or remove the user from the group
        return Repository.groupAddRemove(courseId, userId, groupName, add)
            .then(function(response) {
                return response.result;
            })
            .always(function() {
                Notification.fetchNotifications();
            })
            .fail(function(ex) {
                Notification.exception(ex);
                // Handle toggle failure
                $('#' + key).prop('checked', !$('#' + key).prop('checked'));
                return false;
            });
    };

    /**
     * Create all of the event listeners for the message preferences page.
     *
     * @param  {string} courseId    The course id for suspension.
     * @param  {string} userId      The user id to be suspended.
     * @param  {object} root        The root element.
     * @method processSetting
     * @return {bool}
     */
     const updateComment = function(courseId, userId, root) {

        // Show progressing icon
        startLoading(root);
        const comment = $('#comment').val();

        // Save the comment
        return Repository.updateProfileComment(courseId, userId, comment)
        .then(function(response) {
            // Add success status element if necessary
            let result = response.result;
            // eslint-disable-next-line promise/no-nesting
            Str.get_string((result ? 'commentsaved' : 'commentnotsaved'), 'local_booking').then(function(string) {
                // Show the status for a little bit
                $('#status').addClass('comment-status-' + (result ? 'success' : 'error'));
                $('#status').removeClass('comment-status-' + (!result ? 'success' : 'error'));
                $('#status').text(string).slideDown(1000).delay(2000).slideUp(1000);
                return true;
            })
            .fail(Notification.exception);
            return false;
        })
        .always(function() {
            Notification.fetchNotifications();
            // Stop showing progressing icon
            stopLoading(root);
        })
        .fail(Notification.exception);
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
