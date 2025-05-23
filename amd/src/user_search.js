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
 * Allow the user to search for users within the booking area.
 *
 * @module    local_booking/user_search
 * @author    Mustafa Hajjar (mustafa.hajjar)
 * @copyright BAVirtual.co.uk © 2024
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import UserSearch from 'core_user/comboboxsearch/user';
import * as Repository from 'local_booking/repository';

// Define our standard lookups.
const selectors = {
    component: '.user-search',
    courseid: '[data-region="courseid"]',
};
const component = document.querySelector(selectors.component);
const courseID = component.querySelector(selectors.courseid).dataset.courseid;

export default class User extends UserSearch {

    /**
     * Construct the class.
     * @param {string} baseUrl The base URL for the page.
     */
    constructor(baseUrl) {
        super();
        this.baseUrl = baseUrl;
    }

    static init(baseUrl) {
        return new User(baseUrl);
    }

    /**
     * Get the data we will be searching against in this component.
     *
     * @returns {Promise<*>}
     */
    fetchDataset() {
        return Repository.userFetch(courseID).then((r) => r.users);
    }

    /**
     * Build up the view all link.
     *
     * @returns {string|*}
     */
    selectAllResultsLink() {
        return this.baseUrl;
    }

    /**
     * Build up the link that is dedicated to a particular result.
     *
     * @param {Number} userID The ID of the user selected.
     * @returns {string|*}
     */
    selectOneLink(userID) {
        const url = new URL(this.baseUrl);
        url.searchParams.set('studentid', userID);
        url.searchParams.set('filter', 'any');
        return url.toString();
    }
}
