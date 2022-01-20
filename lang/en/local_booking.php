<?php
/**
 * Session Booking plugin en language file
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Booking
$string['activestudents'] = 'Active students progression';
$string['averagewaittime'] = 'Avg wait days';
$string['book'] = 'Book';
$string['bookingactive'] = 'My active bookings';
$string['bookingavailabilityposts'] = 'Availability posted';
$string['bookingavailabilityoverridesuccess'] = 'Wait restriction override saved.';
$string['bookingavailabilityoverrideunable'] = 'Unable to process wait restriction override.';
$string['bookingcalics'] = 'Download .ics file';
$string['bookingcalgoogle'] = 'Add to Google calendar';
$string['bookingcalyahoo'] = 'Add to Yahoo calendar';
$string['bookingcalwinlive'] = 'Add to Windows Live calendar';
$string['bookingcancel'] = 'Cancel';
$string['bookingcanceledsuccess'] = 'Booking with \'{$a->studentname}\' cancelled!';
$string['bookingcanceledunable'] = 'Unable to cancel booking!';
$string['bookingcancelednotfound'] = 'Booking not found!';
$string['bookingconfirmmsg'] = '{$a->status} session on \'{$a->exercise}\' with instructor {$a->instructor}';
$string['bookingconfirmsuccess'] = 'Booking confirmed for \'{$a->exercise}\' with {$a->instructor} on {$a->sessiondate} zulu';
$string['bookingconfirmunable'] = 'Unable to confirm booking!';
$string['bookingdashboard'] = 'Instructor dashboard';
$string['bookingfooter'] = '<p style="font-family:sans-serif"><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href="{$a->bookingurl}">Session Booking</a></p>';
$string['bookinginfo'] = '{$a->status} session on \'{$a->exercise}\' with instructor {$a->instructor}';
$string['bookingnoposts'] = 'No posts';
$string['bookingoverride'] = 'Override';
$string['bookingoverridelabel'] = 'Restriction wait period';
$string['bookingoverridetooltip'] = 'Allows the student to post availability and override the restriction period';
$string['bookingkeepactivetrue'] = 'Keep active ON';
$string['bookingkeepactivefalse'] = 'Keep active OFF';
$string['bookingkeepactivelabel'] = 'On-hold list';
$string['bookingkeepactivetooltip'] = 'Forces the student to stay on the active students list';
$string['bookingrecencyfrombooktooltip'] = 'Last booking<br/>{$a}';
$string['bookingrecencyfromenroltooltip'] = 'No booking<br/>days since enrollment<br/>{$a}';
$string['bookingrecencyfromgradetooltip'] = 'No booking<br/>days since last graded<br/>{$a}';
$string['bookings'] = 'Session booking';
$string['bookingsavesuccess'] = 'Booking saved for \'{$a->exercise}\' with {$a->studentname} on {$a->sessiondate} zulu';
$string['bookingsaveunable'] = 'Unable to save booking!';
$string['bookingsessionselection'] = 'Session selection';
$string['bookingsortby'] = 'Sort by';
$string['bookingsortbyscore'] = 'score';
$string['bookingsortbyscoretooltip'] = 'Sorts by student score';
$string['bookingsortbyavailability'] = 'availability';
$string['bookingsortbyavailabilitytooltip'] = 'Sorts by students with availability posts, then no posts, then incomplete lessons';
$string['booksave'] = 'Save Booking';
$string['cancellationcomment'] = 'Please provide the student with a short comment on this cancellation:';
$string['configmissing'] = 'Failed to open {$a}';
$string['crontask'] = 'Background processing for session booking';
$string['customfielddual'] = 'Dual';
$string['customfieldmulticrew'] = 'Multicrew';
$string['elapseddays'] = 'Elapsed Days';
$string['enroldate'] = 'Enrol Date';
$string['exercise'] = 'Exercise';
$string['grade'] = 'Grade';
$string['incompletelessontooltip'] = 'The student has not completed ground lesson';
$string['instructors'] = 'Instructors';
$string['lastsessiondate'] = 'Last VFC Flight/Lesson';
$string['participation'] = 'Instructor participation';
$string['pluginname'] = 'Session Booking';
$string['progression'] = 'Students Progression';
$string['mystudents'] = 'My assigned students';
$string['nextlesson'] = 'Next Lesson';
$string['nobookingtoconfirm'] = 'You have no booking to confirm.';
$string['role'] = 'Role';
$string['sequencetooltip'] = 'Score: {$a->score}<br/>Last session: {$a->recency} days<br/>Course activity: {$a->activity} views
<br/>Availability: {$a->slots} posts<br/>Completion: {$a->completion} lessons';
$string['sessionaction'] = 'Action';
$string['sessiondate'] = 'Session Date';
$string['sessionbookedby'] = '{$a->sessiondate}<br/>{$a->bookingstatus}<br/>{$a->instructor}';
$string['sessiongradedby'] = '{$a->sessiondate}Graded by:<br/>{$a->instructor}<br/>On: {$a->gradedate}';
$string['sessiongradeexampass'] = 'Exam Results<br/>Grade: {$a->grade}<br/>Date: {$a->gradedate}';
$string['sessionvenue'] = 'Discord';
$string['simulator'] = 'Sim';
$string['slots'] = 'slots';
$string['statusbooked'] = 'confirmed';
$string['statustentative'] = 'tentative';
$string['students'] = 'Students';
$string['studentavialability'] = 'Student availability';
$string['unknown'] = 'No grading record!';
$string['zulutime'] = 'Zulu';

// Availability posting
$string['availability'] = 'My availability';
$string['availabilityinst'] = 'Student availability';
$string['availabilityallview'] = 'View everyone\'s availability';
$string['buttonsave'] = 'Save';
$string['buttoncopy'] = 'Copy';
$string['buttonpaste'] = 'Paste';
$string['buttonclear'] = 'Clear';
$string['defaultmake'] = 'Make default';
$string['defaultload'] = 'Load default';
$string['checkpassed'] = 'passed';
$string['checkfailed'] = 'failed';
$string['firstsession'] = 'First session time';
$string['firstsessiondesc'] = 'first allowable session time';
$string['flightsim'] = 'Flight Simulation';
$string['lastsession'] = 'Last session time';
$string['lastsessiondesc'] = 'last allowable session time';
$string['lessonsincomplete'] = 'Incomplete lessons(s): Please complete pending lessons, otherwise instructors will not see your availability.';
$string['nextsessionwaitdays'] = 'Wait period:';
$string['nextsessionwaitdaysdesc'] = 'waiting period used to restrict availability posting before x days had passed since student\'s last session';
$string['local'] = 'Local';
$string['loading'] = 'Loading slots...';
$string['processingresult'] = 'The processing result';
$string['save'] = 'Save Availability';
$string['slotsdeletesuccess'] = 'Slots deleted';
$string['slotsdeleteunable'] = 'Unable to delete slots!';
$string['slotssavesuccess'] = 'Slots saved';
$string['slotssaveunable'] = 'Unable to save slots!';
$string['slotsstatusbooked'] = 'Booked';
$string['slotsstatustentative'] = 'Tentative';
$string['strftimeweekinyear'] = 'Week %W';
$string['studentonhold'] = 'You are currently on-hold and instructors will NOT see your postings. Please contact your instructor to be re-activated.';
$string['wait'] = 'Wait Days';
$string['week'] = 'Week';
$string['weeklytitle'] = 'Weekly Availability';
$string['weeksahead'] = 'Availability posting weeks lookahead';
$string['weeksaheaddesc'] = 'allowable weeks lookahead of availability posting. 0=Unlimited';
$string['weekprev'] = 'Previous week';
$string['weeknext'] = 'Next week';

// Logbook
$string['aircraft'] = 'Aircraft';
$string['aircraft_help'] = 'Aircraft type, registration, and engine type';
$string['aircraftgroup'] = 'Aircraft (type/reg\'n/eng.)';
$string['aircraftreg'] = 'Aircraft Registration';
$string['arrgroup'] = 'Arrival (ICAO/time)';
$string['arricao'] = 'Arrival ICAO';
$string['arrtime'] = 'Arrival time (zulu)';
$string['callsign'] = 'Callsign';
$string['checkflight'] = 'Check flight';
$string['checkpilottime'] = 'Examiner time';
$string['checkpilottime_help'] = 'Flight time logged for the examiner as the check pilot';
$string['copilottime'] = 'Copilot time';
$string['copilottime_help'] = 'Flight time logged for the copilot time in a multi-crewed flights';
$string['confirmlogentrydelete'] = 'Are you sure you want to delete this entry?';
$string['deletelogentry'] = 'Delete entry';
$string['depgroup'] = 'Departure (ICAO/time)';
$string['deptime'] = 'Departure time (zulu)';
$string['dualtime'] = 'Dual time';
$string['dualtime_help'] = 'Flight time logged for the student in training flights';
$string['depicao'] = 'Departure ICAO';
$string['editlogentry'] = 'Editing Logbook entry';
$string['enginetype'] = 'Engine type';
$string['errordelete'] = 'Failed to delete logentry';
$string['errorinvaliddate'] = 'Flight date cannot be before booked session date';
$string['errorinvalidarrtime'] = 'Arrival date/time must be greater than departure date/time';
$string['errorlandings'] = 'Number from 1-9';
$string['errorlinking'] = 'Failed to link instructor/student logentries';
$string['errorlogentrycancel'] = 'Errors encountered: Unable to cancel booking!';
$string['errorlogentryfetch'] = 'Error encountered while trying to fetch logbook entry with ID: ';
$string['errorp1pirepnotfound'] = ' - PIREP not found.';
$string['errorp1pirepnopilotintegration'] = ' - No pilot lookup integration.';
$string['errorp1pirepwrongpilot'] = ' - PIREP does not belong to P1.';
$string['feedback'] = 'Feedback';
$string['flightcopilottimetooltip'] = 'Flight time logged for the co-pilot in a multi-crewed flights';
$string['flightdate'] = 'Flight date';
$string['flightdate2'] = 'flight date';
$string['flighttime'] = 'flight time';
$string['flighttypetraining'] = 'Training';
$string['flighttypesolo'] = 'Solo';
$string['flighttypecheck'] = 'Check';
$string['fstd'] = 'FSTD';
$string['fstd_help'] = 'Flight Simulation Training Device qualification';
$string['ifrtime'] = 'IFR flight time';
$string['ifrtime_help'] = 'Operational IFR time is the portion of the flight flown under Instrument Flight Rules, logged for all pilots flying the aircraft';
$string['instpirep'] = 'Instructor PIREP';
$string['instpirep_help'] = 'Used to look up instructor and student PIREPs';
$string['instructortime'] = 'Instructor time';
$string['instructortime_help'] = 'Flight time logged for the instructor in training flights';
$string['landings'] = 'Landings (day/night)';
$string['landings_help'] = 'The number day and night landings for each flight';
$string['landingsp1'] = 'Landings P1 (day/night)';
$string['landingsp1_help'] = 'The number day and night landings for P1 for each flight';
$string['landingsp2'] = 'Landings P2 (day/night)';
$string['landingsp2_help'] = 'The number day and night landings for P2 for each flight';
$string['logbook'] = 'My logbook';
$string['logbookaircraft'] = 'Aircraft';
$string['logbookarr'] = 'Arrival';
$string['logbookcopilot'] = 'Co-Pilot';
$string['logbookdate'] = 'Date';
$string['logbookday'] = 'Day';
$string['logbookdep'] = 'Departure';
$string['logbookdual'] = 'Dual';
$string['logbookformat'] = 'format';
$string['logbookformateasa'] = 'EASA';
$string['logbookformatsimple'] = 'Simple';
$string['logbookfunc'] = 'Function';
$string['logbookfstd'] = 'FSTD';
$string['logbookifr'] = 'IFR';
$string['logbookistr'] = 'Instr-uctor';
$string['logbookmakemodel'] = 'Make, Model';
$string['logbookme'] = 'ME';
$string['logbookmultipilot'] = 'Multi-Pilot';
$string['logbooknight'] = 'Night';
$string['logbookops'] = 'Operational';
$string['logbookp1name'] = 'Name(s) PIC';
$string['logbookpic'] = 'PIC';
$string['logbookpicus'] = 'PICUS';
$string['logbookplace'] = 'Place';
$string['logbookreg'] = 'Reg\'n';
$string['logbookremarks'] = 'Remarks';
$string['logbookse'] = 'SE';
$string['logbooksinglepilot'] = 'Single-Pilot';
$string['logbooksummary'] = 'LOGBOOK SUMMARY';
$string['logbooktime'] = 'Time';
$string['logbooktotaltime'] = 'Total Time';
$string['logentry'] = 'Logbook Entry';
$string['logentry2'] = 'Log entry';
$string['logentrymissing'] = 'No logbook entry!';
$string['logentrydeletesuccess'] = 'Logbook entry deleted';
$string['logentrydeletefailed'] = 'Unable to delete logbook entry';
$string['logentrysavesuccess'] = 'Logbook entry saved';
$string['logentrysaveunable'] = 'Unable to save logbook entry';
$string['multipilottime'] = 'Multi-pilot time';
$string['multipilottime_help'] = 'Flight time logged for each pilot in multi-crew flights';
$string['nighttime'] = 'Night flight time';
$string['nighttime_help'] = 'Operational night time is the portion of the flight flown in night time. Logged for the PIC and copilots';
$string['newlogentry'] = 'New Logbook entry';
$string['p1'] = 'P1 (instructor)';
$string['p1solo'] = 'P1 (solo)';
$string['p2'] = 'P2 (student)';
$string['p1pirep'] = 'P1 PIREP';
$string['p2pirep'] = 'P2 PIREP';
$string['pictime'] = 'PIC time';
$string['pictime_help'] = 'Flight time logged for the pilot in command (PIC). As per the EASA <a href="https://www.easa.europa.eu/sites/default/files/dfu/Part-FCL.pdf">Part-FLC</a>
    [pg30]. "the applicant for or the holder of a pilot licence may log as PIC time all solo flight time, flight time as SPIC and flight time under supervision..."';
$string['picustime'] = 'PICUS time';
$string['picustime_help'] = 'Flight time logged by the pilot in command under supervision (PICUS), which is the FO or the student that passes a checkride';
$string['pilot'] = 'Pilot';
$string['pilotlogbook'] = 'Pilot\'s Logbook';
$string['pirep'] = 'PIREP';
$string['pirepfound'] = ' - PIREP found.';
$string['pirepsgroup'] = 'PIREPs (P1/P2)';
$string['remarks'] = 'Remarks';
$string['groundtime'] = 'Ground time';
$string['groundtime_help'] = 'The ground training (brief & debrief) overall time';
$string['flighttype'] = 'Flight type';
$string['flighttype_help'] = 'A regular training flight, a solo flight, or a check flight (line check / check ride)';
$string['totalcopilottime'] = 'Total Co-pilot flight time';
$string['totaldualtime'] = 'Total dual flight time';
$string['totalifrtime'] = 'Total IFR flight time';
$string['totalinstructortime'] = 'Total instructor flight time';
$string['totallandingsday'] = 'Total day landings';
$string['totallandingsnight'] = 'Total night landings';
$string['totalmultipilottime'] = 'Total Multi-pilot flight time';
$string['totalnighttime'] = 'Total night flight time';
$string['totalpictime'] = 'Total PIC flight time';
$string['totalpicustime'] = 'Total PICUS flight time';
$string['totalgroundtime'] = 'Total ground time';
$string['verifypirep'] = 'Verify PIREP';

// integrations
$string['errordbconnection'] = "Failed to connect to the database: ";

// capabilities
$string['booking:availabilityview'] = 'View availability posting';
$string['booking:instructornotification'] = 'Student notifications';
$string['booking:logbookview'] = 'View pilot logbook';
$string['booking:participationview'] = 'View instructor participation';
$string['booking:studentnotification'] = 'Instructor notifications';
$string['booking:view'] = 'View session bookings';

// message providers
$string['messageprovider:booking_notification'] = 'Session booked notification';
$string['messageprovider:booking_confirmation'] = 'Booked session instructor confirmation';
$string['messageprovider:instructor_notification'] = 'Student confirmation of booked session';
$string['messageprovider:session_cancellation'] = 'Student session cancellation notification';
$string['messageprovider:onhold_warning'] = 'Student on-hold warning notification';
$string['messageprovider:onhold_notification'] = 'Student placed on-hold notification';
$string['messageprovider:suspension_notification'] = 'Student suspended notification';
$string['messageprovider:sessionoverdue_notification'] = 'Instructor session overdue notification';

// email to student: session tentative
$string['emailnotify'] = '{$a->coursename} session booking notification: \'{$a->exercise}\'';
$string['emailnotifymsg'] = '{$a->instructor} has booked a session for your availability on {$a->sessiondate} for \'{$a->exercise}\'.
Please confirm this booking by clicking on this link: {$a->confirmurl}';
$string['emailnotifyhtml'] = '<div style="font-family:sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p>{$a->instructor} has booked a session for your availability on <strong>{$a->sessiondate}
    </strong> for \'<i>{$a->exercise}</i>\'.</p><p>Please <a href=\'{$a->confirmurl}\'>confirm</a> this booking.</p>';
$string['emailnotifycalendarshtml'] = '<p style="font-size: .9em;">Add a reminder to your calendar:<br />
    <table style="border-collapse: collapse; width: 100%;">
    <tbody><tr>
        <td style="width: 50px; text-align: center;"><img src="{$a->pixrooturl}/btn_google_light_normal_ios.png" alt="Add to Google calendar"/></td>
        <td><a style="text-decoration: none;" href="{$a->googleurl}"><span style="font-size: 13px; color: #01579b;">Google Calendar</span></a></td>
        </tr><tr>
        <td style="width: 50px; text-align: center;"><div style="box-shadow: 0 0 2px 2px #e1e1e1; padding: 5px 0px; margin: 0 6 0 6px">
            <img src="{$a->pixrooturl}/btn_outlook.png" alt="Add to Outlook Live calendar" width="25" height="25" /></div></td>
        <td><a style="text-decoration: none;" href="{$a->liveurl}"><span style="font-size: 13px; color: #01579b;">Outlook Live Calendar</span></a></td>
        </tr><tr>
        <td style="width: 50px; text-align: center;"><div style="box-shadow: 0 0 2px 2px #e1e1e1; padding: 5px 0px; margin: 3 6 0 6px">
            <img src="{$a->pixrooturl}/btn_ical.png" alt="Add to Outlook Live calendar" width="28" height="28" /></div></td>
        <td><a style="text-decoration: none;" href="{$a->icsurl}"><span style="font-size: 13px; color: #01579b;">Download ics/iCal file</span></a></td></tr>
    </tbody>
    </table>
    <hr /></div>';

// email to instructor: confirming session tentative by him/her
$string['emailconfirm'] = 'Session booked';
$string['emailconfirmsubject'] = '{$a->coursename} session booked: \'{$a->exercise}\'';
$string['emailconfirmmsg'] = '\'{$a->exercise}\' session booked with {$a->student} for \'{$a->exercise}\' on {$a->sessiondate}.';
$string['emailconfirmhtml'] = '<div style="font-family:sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p>Session booked with <strong>{$a->student}</strong> for \'<i>{$a->exercise}</i>\' on
    <strong>{$a->sessiondate}</strong>.</p>';
$string['emailconfirmcalendarshtml'] = '<p style="font-size: .9em;">Add a reminder to your calendar:<br />
    <table style="border-collapse: collapse; width: 100%;">
    <tbody><tr>
        <td style="width: 50px; text-align: center;"><img src="{$a->pixrooturl}/btn_google_light_normal_ios.png" alt="Add to Google calendar"/></td>
        <td><a style="text-decoration: none;" href="{$a->googleurl}"><span style="font-size: 13px; color: #01579b;">Google Calendar</span></a></td>
        </tr><tr>
        <td style="width: 50px; text-align: center;"><div style="box-shadow: 0 0 2px 2px #e1e1e1; padding: 5px 0px; margin: 0 6 0 6px">
            <img src="{$a->pixrooturl}/btn_outlook.png" alt="Add to Outlook Live calendar" width="25" height="25" /></div></td>
        <td><a style="text-decoration: none;" href="{$a->liveurl}"><span style="font-size: 13px; color: #01579b;">Outlook Live Calendar</span></a></td>
        </tr><tr>
        <td style="width: 50px; text-align: center;"><div style="box-shadow: 0 0 2px 2px #e1e1e1; padding: 5px 0px; margin: 3 6 0 6px">
            <img src="{$a->pixrooturl}/btn_ical.png" alt="Add to Outlook Live calendar" width="28" height="28" /></div></td>
        <td><a style="text-decoration: none;" href="{$a->icsurl}"><span style="font-size: 13px; color: #01579b;">Download ics/iCal file</span></a></td></tr>
    </tbody>
    </table>
    <hr /></div>';

// email to instructor: session confirmed by student
$string['emailinstconfirm'] = 'Booked session confirmed by Student';
$string['emailinstconfirmsubject'] = '{$a->coursename} - Student confirmed booking: \'{$a->exercise}\'';
$string['emailinstconfirmnmsg'] = '{$a->student} confirmed your booked session for \'{$a->exercise}\' on {$a->sessiondate} zulu.';
$string['emailinstconfirmhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p><strong>{$a->student}</strong> confirmed your booked session for \'<i>{$a->exercise}</i>\' on
    <strong>{$a->sessiondate} zulu</strong>.</p></p><hr />';

// email to student: session cancellation
$string['emailcancel'] = '{$a->coursename} session booking cancellation: \'{$a->exercise}\'';
$string['emailcancelmsg'] = '{$a->instructor} has cancelled your booked session scheduled for {$a->sessiondate} on \'{$a->exercise}\'.
Instructor\'s comment: {$a->comment}.
Please note you will have to post new availability as current availability posts were purged.';
$string['emailcancelhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p>{$a->instructor} has cancelled your booked session scheduled for
    <strong>{$a->sessiondate}</strong> on \'<i>{$a->exercise}</i>\'.</p><p><strong>Instructor\'s comment:</strong><br />{$a->comment}</p><p>
    <span style=\'color: red\'>Please note you will have to post new availability as current availability posts were purged.</span></p></p><hr />';

// email to student: on-hold warning
$string['emailonholdwarning'] = '{$a->coursename}: Inactivity notification';
$string['emailonholdwarningmsg'] = 'Please post an availability slot to avoid being automatically placed on-hold on \'{$a->onholddate}\'. You can post an availability slot for
    the \'{$a->coursename}\' from the following link: {$a->slotsurl}';
$string['emailonholdwarninghtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Please post an availability slot to avoid being automatically placed on-hold on <strong>\'{$a->onholddate}\'</strong>.</p><p>You can post an availability slot for
    the \'{$a->coursename}\' course <a href="{$a->slotsurl}">here</a>.</p><hr />';

// email to student: on-hold notification
$string['emailonholdnotify'] = '{$a->coursename}: Student placed on-hold';
$string['emailonholdnotifymsg'] = 'Due to inactivity since your last posting on \'{$a->lastsessiondate}\', you have been placed on-hold. Please contact your instructor if you are still interested in resuming the \'{$a->coursename}\' course work.';
$string['emailonholdnotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Due to inactivity since your last posting on <strong>\'{$a->lastsessiondate}\'</strong>, you have been placed on-hold. Please contact your instructor if you are
    still interested in resuming \'{$a->coursename}\' course work.</p><p>Please note, if you are no longer interested in continuing course work, you will be automatically
    unenrolled from the course on <strong>\'{$a->suspenddate}\'</strong>.</p><hr />';
$string['emailonholdinstnotifymsg'] = 'Due to inactivity since last posting on \'{$a->lastsessiondate}\', {$a->studentname} has been placed on-hold.';
$string['emailonholdinstnotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Due to inactivity since last posting on <strong>\'{$a->lastsessiondate}\'</strong>, {$a->studentname} has been placed on-hold.</p>
    <p>Please note, {$a->studentname} will be automatically unenrolled from the course on <strong>\'{$a->suspenddate}\'</strong>.</p><hr />';

// email to student: suspension notification
$string['emailsuspendnotify'] = '{$a->coursename}: Suspension notification';
$string['emailsuspendnotifymsg'] = 'Please note that you have been suspended from the \'{$a->coursename}\' course due to session booking inactivity since \'{$a->lastsessiondate}\'.  Please contact your instructor if you are still interested in being enrolled in this course.';
$string['emailsuspendnotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Please note you have been suspended from the \'{$a->coursename}\' course due to session booking inactivity since <strong>\'{$a->lastsessiondate}\'</strong>.</p>
    <p>Please contact your instructor if you are still interested in being enrolled in this course.</p><hr />';
$string['emailsuspendinstnotifymsg'] = 'Please note that {$a->studentname} has been suspended from the \'{$a->coursename}\' course due to session booking inactivity since \'{$a->lastsessiondate}\'.';
$string['emailsuspendinstnotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Please note {$a->studentname} has been suspended from the \'{$a->coursename}\' course due to session booking inactivity since <strong>\'{$a->lastsessiondate}\'</strong>.</p><hr />';

// email to instructor: session overdue notification
$string['emailoverduenobooking'] = 'no booking on record';
$string['emailoverduenotify'] = '{$a->coursename}: Session overdue notification';
$string['emailoverduenotifymsg'] = 'Please note that you have {$a->status}.  Please book a session with your assigned student or any student from the booking view. Otherwise, please ask the course administrator to remove you from the list of active instructors.
You can book a session from the following view link: {$a->bookingurl}';
$string['emailoverduenotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Please note that you have {$a->status}.</p><p>Please book a session with your assigned student or any student from the <a href="{$a->bookingurl}">booking view</a>
    as soon as possible. To stop receiving these messages, please ask the course administrator to remove you from the list of active instructors.</p><hr />';
$string['emailoverduenotifyinstmsg'] = 'Please note that {$a->instructorname} has {$a->status}.  The instructor has been notified to book a session with assigned
    student or any student from the list, otherwise to request removal from the list of active instructors.';
$string['emailoverduenotifyinsthtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Please note that {$a->instructorname} has {$a->status}.</p><p>The instructor has been notified to book a session with assigned student or any student from the list,
    otherwise to request removal from the list of active instructors.</p><hr />';
$string['emailoverduestatus'] = 'not booked a session since \'{$a}\'';

// settings
$string['activitycountweight'] = 'Course activity prioritization';
$string['activitycountweightdesc'] = 'weight multiplier to calculate prioritization for course activity';
$string['completionweight'] = 'Lesson completion prioritization';
$string['completionweightdesc'] = 'weight multiplier to calculate prioritization of lesson completion';
$string['generalsection'] = 'General settings';
$string['recencydaysweight'] = 'Recency prioritization';
$string['recencydaysweightdesc'] = 'weight multiplier to calculate prioritization for session recency';
$string['slotcountweight'] = 'Slot count prioritization';
$string['slotcountweightdesc'] = 'weight multiplier to calculate prioritization for availability slots';

// install
$string['useplugin'] = 'Use Session Booking';
$string['homeicao'] = 'Home airport ICAO';
$string['exercisetitles'] = 'Course exercise titles:';
$string['exercisetitlesdesc'] = 'Use to improve the display of long exercise titles on the instructors dashboard page (one title per line, use &lt;br/&gt; tag to break a title)';
$string['trainingaircraft'] = 'Training Aircraft ICAO';
$string['trainingaircraftdesc'] = '(one per line)';

// APIs
$string['googleaccesstokenerror'] = 'Error: Failed to receieve Google access token';
$string['googletimezoneerror'] = 'Error: Failed to get Google user timezone';
$string['googlecalendarlisterror'] = 'Error: Failed to get Google calendars list';
$string['googlecreateeventerror'] = 'Error: Failed to create Google calendar event';
$string['liveaccesstokenerror'] = 'Error: Failed to receieve Outlook Live access token';
$string['livetimezoneerror'] = 'Error: Failed to get Outlook Live user timezone';
$string['livecalendarlisterror'] = 'Error: Failed to get Outlook Live calendars list';
$string['livecreateeventerror'] = 'Error: Failed to create Outlook Live calendar event';
