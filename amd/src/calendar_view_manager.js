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
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Templates from 'core/templates';
import Notification from 'core/notification';
import CustomEvents from 'core/custom_interaction_events';
import SlotActions from 'local_booking/slot_actions';
import * as Repository from 'local_booking/repository';
import * as Selectors from 'local_booking/selectors';

/**
 * Register event listeners for the module.
 *
 * @method  registerEventListeners
 * @param   {object} root The root element.
 */
 const registerEventListeners = (root) => {
    root = $(root);

    // Process previous/next week navigation links
    root.on('click', Selectors.links.navLink, (e) => {
        const wrapper = root.find(Selectors.calendarwrapper);
        const courseId = wrapper.data('courseid');
        const categoryId = wrapper.data('categoryid');
        const link = e.currentTarget;

        changeWeek(root, link.href, link.dataset.year, link.dataset.week, link.dataset.time, courseId, categoryId);
        e.preventDefault();
    });

    const viewSelector = root.find(Selectors.viewSelector);
    CustomEvents.define(viewSelector, [CustomEvents.events.activate]);
    viewSelector.on(
        CustomEvents.events.activate,
        (e) => {
            e.preventDefault();

            const option = e.target;
            if (option.classList.contains('active')) {
                return;
            }

            const year = option.dataset.year,
                week = option.dataset.week,
                time = option.dataset.time,
                courseId = option.dataset.courseid,
                categoryId = option.dataset.categoryid;

            refreshWeekContent(root, year, week, time, courseId, categoryId, e.target, 'local_booking/availability_calendar')
                .then(() => {
                    return window.history.pushState({}, '', '?view=user');
                }).fail(Notification.exception);
        }
    );

};

/**
 * Refresh the week content.
 *
 * @method  refreshWeekContent
 * @param   {object} root The root element.
 * @param   {number} year Year
 * @param   {number} week week
 * @param   {number} time The timestamp of the begining current week
 * @param   {number} courseId The id of the course associated with the calendar shown
 * @param   {number} categoryId The id of the category associated with the calendar shown
 * @param   {object} target The element being replaced. If not specified, the calendarwrapper is used.
 * @param   {string} template The template to be rendered.
 * @return  {promise}
 */
 export const refreshWeekContent = (root, year, week, time, courseId, categoryId, target = null, template = '') => {
    startLoading(root);

    target = target || root.find(Selectors.calendarwrapper);
    template = template || root.attr('data-template');
    M.util.js_pending([root.get('id'), year, week, courseId].join('-'));

    const action = target.data('action');
    const view = target.data('viewall') ? 'all' : 'user';
    const studentId = target.data('student-id');
    const exerciseId = target.data('exercise-id');
    time = time == 0 ? Date.now() / 1000 : time;

    return Repository.getCalendarWeekData(year, week, time, courseId, categoryId, action, view, studentId, exerciseId)
        .then(context => {
            context.viewingmonth = true;
            return Templates.render(template, context);
        })
        .then((html, js) => {
            return Templates.replaceNode(target, html, js);
        })
        .always(() => {
            M.util.js_complete([root.get('id'), year, week, courseId].join('-'));
            SlotActions.setPasteState(root);
            SlotActions.setSaveButtonState(root, action);
            return stopLoading(root);
        })
        .fail(Notification.exception);
};

/**
 * Handle changes to the current calendar view.
 *
 * @method  changeWeek
 * @param   {object} root The container element
 * @param   {string} url The calendar url to be shown
 * @param   {number} year Year
 * @param   {number} week week
 * @param   {number} time The timestamp of the begining current week
 * @param   {number} courseId The id of the course associated with the calendar shown
 * @param   {number} categoryId The id of the category associated with the calendar shown
 * @return  {promise}
 */
export const changeWeek = (root, url, year, week, time, courseId, categoryId) => {
    return refreshWeekContent(root, year, week, time, courseId, categoryId, null, '')
        .then((...args) => {
            if (url.length && url !== '#') {
                window.history.pushState({}, '', url);
            }
            return args;
        });
};

/**
 * Reload the current month view data.
 *
 * @method  reloadCurrentMonth
 * @param   {object} root The container element.
 * @param   {number} courseId The id of the course associated with the calendar shown
 * @param   {number} categoryId The id of the category associated with the calendar shown
 * @return  {promise}
 */
export const reloadCurrentMonth = (root, courseId = 0, categoryId = 0) => {
    const year = root.find(Selectors.calendarwrapper).data('year');
    const week = root.find(Selectors.calendarwrapper).data('week');
    const time = root.find(Selectors.calendarwrapper).data('time');

    courseId = courseId || root.find(Selectors.calendarwrapper).data('courseid');
    categoryId = categoryId || root.find(Selectors.calendarwrapper).data('categoryid');

    return refreshWeekContent(root, year, week, time, courseId, categoryId, null, '');
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

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('disabled', 'disabled');
    });
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

    $(root).one('submit', function() {
        $(this).find('input[type="submit"]').attr('enabled', 'enabled');
    });
};

export const init = (root, view) => {
    registerEventListeners(root, view);
};