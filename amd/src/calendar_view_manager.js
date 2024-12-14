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
 * A javascript module to handler booking view changes.
 * Improvised from core_calendar.
 *
 * @module     local_booking/calendar_view_manager
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Templates from 'core/templates';
import Notification from 'core/notification';
import SlotActions from 'local_booking/slot_actions';
import ModalActions from 'local_booking/modal_actions';
import * as Repository from 'local_booking/repository';
import * as Selectors from 'local_booking/selectors';

/**
 * Handle changes to the current calendar view.
 *
 * @method  changeWeek
 * @param   {object} root The container element
 * @param   {string} url The calendar url to be shown
 * @param   {number} year Year
 * @param   {number} week week
 * @param   {number} time The timestamp of the beginning current week
 * @param   {number} courseId The id of the course associated with the calendar shown
 * @return  {promise}
 */
export const changeWeek = (root, url, year, week, time, courseId) => {

    // Check if the calendar is dirty and suggest saving
    if (SlotActions.isDirty()) {
        ModalActions.showWarning('slotsnotsaved', {year, week, time, courseId}, 'yesno');
        SlotActions.clean();
    }

    // Go to the requested week
    return renderCalendar(root, year, week, time, courseId)
        .then((...args) => {
            if (url.length && url !== '#') {
                window.history.pushState({}, '', url);
            }
            return args;
        });
};

/**
 * Renders the action bar
 *
 * @param {object} root The root element.
 * @param   {number} year Year
 * @param   {number} week week
 * @param   {number} time The timestamp of the beginning current week
 * @param   {number} courseId The id of the course associated with the calendar shown
 * @method  renderActionbar
 */
async function renderCalendar(root, year, week, time, courseId) {
    const weekviewTarget = root.find(Selectors.wrappers.weekwrapper);
    const weekviewTemplate = weekviewTarget.attr('data-template');
    const actionbarTarget = root.find(Selectors.wrappers.actionbarwrapper);
    const actionbarTemplate = actionbarTarget.attr('data-template');

    let exportContext = await refreshWeekContent(root, year, week, time, courseId);

    // Render action bar
    Templates.render(actionbarTemplate, exportContext)
        .then((html, js) => {
            return Templates.replaceNode(actionbarTarget, html, js);
        })
        .fail(Notification.exception);

    // Render week's calendar
    Templates.render(weekviewTemplate, exportContext)
        .then((html, js) => {
            return Templates.replaceNode(weekviewTarget, html, js);
        }).always(() => {
            SlotActions.setPasteState(root);
            stopLoading(root);
        })
        .fail(Notification.exception);

    return;
}

/**
 * Refresh the week content.
 *
 * @method  refreshWeekContent
 * @param   {object} root The root element.
 * @param   {number} year Year
 * @param   {number} week week
 * @param   {number} time The timestamp of the beginning current week
 * @param   {number} courseId The id of the course associated with the calendar shown
 * @return  {promise}
 */
const refreshWeekContent = (root, year, week, time, courseId) => {
    startLoading(root);

    const target = $(Selectors.wrappers.calendarwrapper),
          action = target.data('action'),
          view = target.data('viewall') ? 'all' : 'user',
          studentId = target.data('student-id'),
          exerciseId = target.data('exercise-id');
    time = time == 0 ? Date.now() / 1000 : time;
    M.util.js_pending([root.get('id'), year, week, courseId].join('-'));

    let actionbarContext = Repository.getCalendarWeekData(year, week, time, courseId, action, view, studentId, exerciseId)
        .then(context => {
            return context;
        })
        .always(() => {
            M.util.js_complete([root.get('id'), year, week, courseId].join('-'));
        })
        .fail(Notification.exception);

     return actionbarContext;
};

/**
 * Set the element state to loading.
 *
 * @method  startLoading
 * @param   {object} root The container element
 */
 export const startLoading = (root) => {
    const loadingIconContainer = root.find(Selectors.containers.loadingIcon);
    loadingIconContainer.removeClass('hidden');
};

/**
 * Remove the loading state from the element.
 *
 * @method  stopLoading
 * @param   {object} root The container element
 */
export const stopLoading = (root) => {
    const loadingIconContainer = root.find(Selectors.containers.loadingIcon);
    loadingIconContainer.addClass('hidden');
};
