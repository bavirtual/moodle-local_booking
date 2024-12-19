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
 * This module handles session booking and logentry operations
 * including CRUD and UI events.
 *
 * @module     local_booking/booking_actions
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'local_booking/events',
    'local_booking/selectors',
],
function(
    $,
    BookingEvents,
    Selectors,
) {

    /**
     * Redirect to exercise (assignment) grading page.
     *
     * @method  gotoFeedback
     * @param   {object} root
     * @param   {object} e
     */
     var gotoFeedback = (root, e) => {
        let Source = root.find(Selectors.logentryitem),
            courseId, exerciseId, userId;

        // Call redirect to assignment feedback page
        if (Source.length !== 0) {
            // Get from logentry modal
            courseId = Source.data('courseId');
            exerciseId = Source.data('exerciseId');
            userId = Source.data('userId');
        } else {
            // Get from closest dashboard session clicked
            Source = $(e.target).closest(Selectors.regions.session);
            courseId = $(Selectors.wrappers.bookingwrapper).data('courseid');
            exerciseId = Source.data('exerciseId');
            userId = Source.data('studentId');
        }

        // Trigger redirect to feedback
        $('body').trigger(BookingEvents.gotoFeedback, [exerciseId]);

        // Redirect to the grading and feedback page
        location.href = M.cfg.wwwroot + '/local/booking/assign.php?courseid=' + courseId +
                '&exeid=' + exerciseId + '&rownum=0&userid=' + userId + '&passed=1';
    };

    return {
        gotoFeedback: gotoFeedback
    };
});
