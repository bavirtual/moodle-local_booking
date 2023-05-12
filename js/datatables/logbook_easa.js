/* eslint-disable no-undef */
/* eslint-disable no-unused-vars */
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
 * This module handles datatables for the logbook view.
 *
 * @module     local_booking/logbook_easa
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Define column constants
$(document).ready(function() {
    var FLIGHTDATE = 0,
        DEPICAO = 1,
        DEPTIME = 2,
        ARRICAO = 3,
        ARRTIME = 4,
        AIRCRAFT = 5,
        AIRCRAFTREG = 6,
        ENGINETYPE_SE = 7,
        ENGINETYPE_ME = 8,
        MULTIPILOT = 9,
        TOTALTIME = 10,
        P1NAME = 11,
        LANDINGSDAY = 12,
        LANDINGSNIGHT = 13,
        NIGHTTIME = 14,
        IFRTIME = 15,
        PICTIME = 16,
        COPILOTTIME = 17,
        DUALTIME = 18,
        INSTTIME = 19,
        PICUSTIME = 20,
        FSTD = 21,
        REMARKS = 22;

    var table = $('#logbook').DataTable({
        "order": [[0, "desc"]],
        "ordering": true,
        "lengthChange": true,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "autoWidth": "true",
        "columnDefs": [
            {"min-width": "40px", "targets": FLIGHTDATE}, // Column: flightdate
            {"min-width": "35px", "targets": DEPICAO}, // Column: depicao
            {"min-width": "35px", "targets": DEPTIME}, // Column: deptime
            {"min-width": "50px", "targets": ARRICAO}, // Column: arricao
            {"min-width": "45px", "targets": ARRTIME}, // Column: arrtime
            {"min-width": "50px", "targets": AIRCRAFT}, // Column: aircraft
            {"min-width": "50px", "targets": AIRCRAFTREG}, // Column: aircraftreg
            {"min-width": "25px", "className": "dt-center", "targets": ENGINETYPE_SE}, // Column: enginetype 'SE'
            {"min-width": "25px", "className": "dt-center", "targets": ENGINETYPE_ME}, // Column: enginetype 'ME'
            {"min-width": "10px", "targets": MULTIPILOT}, // Column: multipilot
            {"min-width": "10px", "targets": TOTALTIME}, // Column: totaltime
            {"min-width": "130px", "targets": P1NAME}, // Column: p1name
            {"min-width": "35px", "className": "dt-center", "targets": LANDINGSDAY}, // Column: landingsday
            {"min-width": "35px", "className": "dt-center", "targets": LANDINGSNIGHT}, // Column: landingsnight
            {"min-width": "35px", "targets": NIGHTTIME}, // Column: nighttime
            {"min-width": "35px", "targets": IFRTIME}, // Column: ifrtime
            {"min-width": "50px", "targets": PICTIME}, // Column: pictime
            {"min-width": "45px", "targets": COPILOTTIME}, // Column: copilottime
            {"min-width": "45px", "targets": DUALTIME}, // Column: dualtime
            {"min-width": "50px", "targets": INSTTIME}, // Column: instructortime
            {"min-width": "45px", "targets": PICUSTIME}, // Column: picustime
            {"min-width": "15px", "targets": FSTD}, // Column: fstd
            {"min-width": "160px", "targets": REMARKS} // Column: remarks
        ],
        "footerCallback": function(row, data, start, end, display) {
            var api = this.api();

            // Convert to interger to find total
            var intVal = function(i) {
                // Convert time format to number of minutes
                // eslint-disable-next-line no-nested-ternary
                return parseInt((typeof i === 'string') ? (parseInt((i.substring(0, i.indexOf(':'))) * 60) +
                    parseInt(i.substring(i.indexOf(':') + 1, i.length))) : ((typeof i === 'number') ? i : 0));
            };

            // Calculate column Total of the complete result
            var multipilotTotal = api.column(MULTIPILOT, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var grandTotal = api.column(TOTALTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var daylandingsTotal = api.column(LANDINGSDAY, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var nightlandingsTotal = api.column(LANDINGSNIGHT, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var nightimeTotal = api.column(NIGHTTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var ifrtimeTotal = api.column(IFRTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var pictimeTotal = api.column(PICTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var copilottimeTotal = api.column(COPILOTTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var dualtimeTotal = api.column(DUALTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var insttimeTotal = api.column(INSTTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var picustimeTotal = api.column(PICUSTIME, {page: 'current'}).data().reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Convert number of minutes to time
            var timeVal = function(i) {
                var time = i;
                if (i > 0) {
                    var hrs = parseInt(i / 60);
                    hrs = hrs < 10 ? ('00' + hrs).slice(-2) : hrs;
                    var mins = '00' + (i % 60);
                    time = hrs + ':' + mins.slice(-2);
                }
                return time;
            };

            // Update footer by showing the total with the reference of the column index
            $(api.column(MULTIPILOT).footer()).html(multipilotTotal != 0 ? timeVal(multipilotTotal) : '');
            $(api.column(TOTALTIME).footer()).html(timeVal(grandTotal));
            $(api.column(LANDINGSDAY).footer()).html(daylandingsTotal);
            $(api.column(LANDINGSNIGHT).footer()).html(nightlandingsTotal);
            $(api.column(NIGHTTIME).footer()).html(nightimeTotal != 0 ? timeVal(nightimeTotal) : '');
            $(api.column(IFRTIME).footer()).html(ifrtimeTotal != 0 ? timeVal(ifrtimeTotal) : '');
            $(api.column(PICTIME).footer()).html(pictimeTotal != 0 ? timeVal(pictimeTotal) : '');
            $(api.column(COPILOTTIME).footer()).html(copilottimeTotal != 0 ? timeVal(copilottimeTotal) : '');
            $(api.column(DUALTIME).footer()).html(dualtimeTotal != 0 ? timeVal(dualtimeTotal) : '');
            $(api.column(INSTTIME).footer()).html(insttimeTotal != 0 ? timeVal(insttimeTotal) : '');
            $(api.column(PICUSTIME).footer()).html(picustimeTotal != 0 ? timeVal(picustimeTotal) : '');
        },
        dom: 'Blfrtip',
        "processing": true
    });

    table.buttons(0, null).containers().appendTo('#logbook_filter');
});
