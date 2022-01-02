/* eslint-disable no-undef */
/* eslint-disable no-unused-vars */
/* eslint-disable require-jsdoc */
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
 * This module handles flight rules for Logbook entries.
 *
 * @module     local_booking/flightrules
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
  * Register event listeners for the logentries.
  *
  * @param {string} rule        The rule to apply.
  * @param {string} sourceField The source time field.
  */

function applyFlightRules(rule) {
  var flighttime = $('#id_pictime').val();
  var soloflight = $('#id_soloflight').prop('checked'); //val();

  // Check the training rule type
  switch (rule) {
    case 'Dual':
      // Duplicate P1 time for the student and instructor
      if (!soloflight) {
        $('#id_dualtime').val(flighttime);
        $('#id_instructortime').val(flighttime);
      }
      break;

    case 'Multicrew':
      // Duplicate P1 time for the student and instructor
      if (!soloflight) {
        $('#id_multipilot').val(flighttime);
        $('#id_copilot').val(flighttime);
      }
      break;

    case 'Solo':
      // Hide all irrelevant time and set required value
      // client verification to 0 where appropriate.
      $("#id_sessiontime").toggle();
      $("#id_sessiontime").val(0);
      $("#id_instructortime").toggle();
      $("#id_p2pirep").toggle();
      $("#id_checkpilottime").toggle();


      if ($('#id_dualtime').length) {
        $("#id_dualtime").val(0);
        $("#id_dualtime").toggle();
      }

      if ($('#id_multipilottime').length) {
        $("#id_multipilottime").val(0);
        $("#id_multipilottime").toggle();
      }

      if ($('#id_copilottime').length) {
        $("#id_copilottime").val(0);
        $("#id_copilottime").toggle();
      }
      break;
  }
}