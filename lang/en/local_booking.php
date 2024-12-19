<?php
/**
 * Session Booking plugin en language file
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Booking
$string['actionbooksession'] = 'Book session';
$string['actioncancelsession'] = 'Cancel session';
$string['actiondisabledincompletelessonstooltip'] = 'The student has not completed prerequisite lesson module';
$string['actiondisabledexaminersonlytooltip'] = 'Examiners only';
$string['actiondisabledseniorsonlytooltip'] = 'Senior instructors only';
$string['actiondisabledsubmissionmissingtooltip'] = 'Exercise submission missing.<br/>Request the student to submit the PLog to enable grading.';
$string['actiondisabledwrongexaminerstooltip'] = 'Action reserved for examiner \'{$a}\'';
$string['actiondisablednologentrytooltip'] = 'Missing logbook entry.<br/>Please add an entry for the exam from the student\'s profile (Logbook link > {$a} logbook)';
$string['actiongradesession'] = 'Grade session';
$string['actiongraduatetooltip'] = 'Graduate \'{$a->studentname}\' from the course';
$string['activestudents'] = 'Active students progression';
$string['alreadybooked'] = 'You are currently booked. Availability slots can only be posted when the student has no active booking.';
$string['averagewaittime'] = 'Avg wait days';
$string['book'] = 'Book';
$string['bookingactive'] = 'My active bookings';
$string['bookingavailabilityposts'] = 'Slots posted';
$string['bookingsetpreferencesunable'] = 'Unable to save user preference setting {$a->preference} set to {$a->value}.';
$string['bookingcancel'] = 'Cancel';
$string['bookingcancelhdr'] = 'Cancel Booking';
$string['bookingcancelnoshow'] = 'No Show';
$string['bookingcanceledsuccess'] = 'Booking with \'{$a->studentname}\' cancelled!';
$string['bookingcanceledsuccesswnoshow'] = 'And the no-show incident was reported.';
$string['bookingcanceledunable'] = 'Unable to cancel booking!';
$string['bookingcancelednotfound'] = 'Booking not found!';
$string['bookingconfirmpg'] = 'Session selection';
$string['bookingconfirmmsg'] = '{$a->status} session on \'{$a->exercise}\' with instructor {$a->instructor}';
$string['bookingconfirmsuccess'] = 'Booking confirmed for \'{$a->exercise}\' with {$a->instructor} on {$a->sessiondate} zulu';
$string['bookingconfirmunable'] = 'Unable to confirm booking!';
$string['bookingdashboard'] = 'Instructor dashboard';
$string['bookingfooter'] = '<p style="font-family:sans-serif"><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href="{$a->bookingurl}">Session Booking</a></p>';
$string['bookinginfo'] = '{$a->status} session on \'{$a->exercise}\' with instructor {$a->instructor}';
$string['bookingnoposts'] = 'No posts';
$string['bookingprogression'] = 'Progression';
$string['bookingrecencyfrombooktooltip'] = 'Last booking<br/>{$a}';
$string['bookingrecencyfromenroltooltip'] = 'No bookings.<br/>Days since enrollment<br/>{$a}';
$string['bookings'] = 'Session booking';
$string['bookingsavesuccess'] = 'Booking saved for \'{$a->exercise}\' with {$a->studentname} on {$a->sessiondate} zulu';
$string['bookingsaveunable'] = 'Unable to save booking!';
$string['bookingsessionselection'] = 'Session selection';
$string['bookingsortby'] = 'Sort by';
$string['bookingsortbyscore'] = 'activity';
$string['bookingsortbyscoretooltip'] = 'Sorts by student activity score';
$string['bookingsortbyavailability'] = 'availability';
$string['bookingsortbyavailabilitytooltip'] = 'Sorts by students with availability posts, then no posts, then incomplete lessons';
$string['bookingtotalstudents'] = 'Total active students';
$string['booksave'] = 'Save booking';
$string['col3header'] = 'Wait Days';
$string['col3headergraduates'] = 'Grad. Date';
$string['col3headersuspended'] = 'Suspend Date';
$string['commentcancel'] = 'Comment';
$string['commentcancelhelp'] = 'Please provide the student with a short comment on this cancellation:';
$string['commentcanceltitle'] = 'Booking cancellation';
$string['commentnoshow'] = 'Confirm student failed to show without a prior notice.';
$string['commentnoshow0'] = 'This is the student\'s first no-show, therefore they will be sent a warning.';
$string['commentnoshow1'] = 'This is the student\'s second no-show within 3 months, therefore they will be suspended.';
$string['commentnoshow2'] = 'This is the student\'s third no-show within 3 months, therefore they will be scheduled to be unenrolled from the course.';
$string['configmissing'] = 'Failed to open {$a}';
$string['coursecompletion'] = 'course completion';
$string['customfielddual'] = 'Dual';
$string['customfieldmulticrew'] = 'Multicrew';
$string['elapseddays'] = 'Elapsed Days';
$string['enroldate'] = 'Enrol date';
$string['exercise'] = 'Exercise';
$string['fleet'] = 'Fleet';
$string['grade'] = 'Grade';
$string['graduate'] = 'Cert.';
$string['instructors'] = 'Instructors';
$string['lastbookeddate'] = 'Last booked';
$string['lastgradeddate'] = 'Last graded';
$string['lastsessiondate'] = 'Last session';
$string['nextlesson'] = 'Next Lesson';
$string['nobookingtoconfirm'] = 'You have no booking to confirm.';
$string['nograduatedate'] = 'No graduate date';
$string['participation'] = 'Instructor participation';
$string['pluginname'] = 'Session booking';
$string['role'] = 'Role';
$string['sequencetooltip_a'] = '{$a->tag}';
$string['sequencetooltip_s'] = 'Activity score: {$a->score}<br/>Last session: {$a->recency} days<br/>Course activity: {$a->activity} views
<br/>Availability: {$a->slots} posts<br/>Completion: {$a->completion} lessons';
$string['sessionaction'] = 'Action';
$string['sessioncompleted'] = 'Session completed';
$string['sessionconfirmed'] = 'Session booked and confirmed by student';
$string['sessiondate'] = 'Session Date';
$string['sessionend'] = 'End time';
$string['sessionexam'] = 'Exam';
$string['sessionbookedby'] = '{$a->sessiondate}<br/>{$a->bookingstatus}<br/>{$a->instructor}';
$string['sessionincomplete'] = 'Session incomplete';
$string['sessiongradednosession'] = 'Graded without a booking. Graded by:<br/>{$a->instructor}<br/>On: {$a->gradedate}';
$string['sessiongradedby'] = 'Last session:<br/>{$a->sessiondate}Graded by:<br/>{$a->instructor}<br/>On: {$a->gradedate}';
$string['sessiongradeexampass'] = 'Exam Results<br/>Grade: {$a->grade}<br/>Date: {$a->gradedate}';
$string['sessionprogressing'] = 'Objective Not Met<br/>(click for feedback)<br/>{$a->sessiondate}{$a->instructor}';
$string['sessionnotconfirmed'] = 'Session booked but not confirm by student';
$string['sessionstart'] = 'Start time';
$string['sessionvenue'] = 'Discord';
$string['showactive'] = 'Active';
$string['showonhold'] = 'On-hold';
$string['showgraduates'] = 'Graduated';
$string['showsuspended'] = 'Suspended';
$string['simulator'] = 'Sim';
$string['slots'] = 'slots';
$string['statusbooked'] = 'confirmed';
$string['statustentative'] = 'tentative';
$string['student'] = 'Student';
$string['studentavailability'] = 'Student availability';
$string['suspend'] = 'Suspend';
$string['tag_status_graduating'] = 'Ready to graduate the course';
$string['tag_status_book'] = 'Availability slots posted';
$string['tag_status_grade'] = 'Session booked<br/>Ready to grade';
$string['tag_status_book_noposts'] = 'No availability<br/>slots posted';
$string['tag_status_lessons_incomplete'] = 'Can not book<br/>Did not complete lesson';
$string['taskcron'] = 'Background processing for session booking activity';
$string['tasknotifications'] = 'Background processing of session booking scheduled notifications';
$string['unknown'] = 'No grading record!';
$string['wanringtitle'] = 'Warning';
$string['zulutime'] = 'Zulu';

// Availability posting
$string['activebookingmsg'] = 'This session has already been booked by instructor {$a}';
$string['availability'] = 'My availability';
$string['availabilityinst'] = 'Student availability';
$string['availabilityallview'] = 'View everyone\'s availability';
$string['availabilityposting'] = '{$a} Slots posted';
$string['buttonsave'] = 'Save';
$string['buttoncopy'] = 'Copy';
$string['buttonpaste'] = 'Paste';
$string['buttonclear'] = 'Clear';
$string['checkpassed'] = 'Passed';
$string['checkfailed'] = 'Failed / Progressing';
$string['flightsim'] = 'Flight Simulation';
$string['lessonsincomplete'] = 'Please complete lesson(s): {$a}. Otherwise instructors will not see your availability.';
$string['local'] = 'Local';
$string['loading'] = 'Loading slots...';
$string['nextsessionwaitdays'] = 'Wait period:';
$string['nextsessionwaitdaysdesc'] = 'waiting period used to restrict availability posting before x days had passed since student\'s last session';
$string['processingresult'] = 'The processing result';
$string['save'] = 'Save Availability';
$string['slotsdeletesuccess'] = 'Slots deleted';
$string['slotsdeleteunable'] = 'Unable to delete slots!';
$string['slotsbookednotsaved'] = 'Save this booking?';
$string['slotsbookednotsavedtitle'] = 'Booking not saved';
$string['slotsnotsaved'] = 'Save pending slots?';
$string['slotsnotsavedtitle'] = 'Slots not saved';
$string['slotssavesuccess'] = 'Slots saved';
$string['slotssaveunable'] = 'Unable to save slots!';
$string['slotsstatusbooked'] = 'Booked';
$string['slotsstatustentative'] = 'Tentative';
$string['studentonhold'] = 'You are currently on-hold and instructors will NOT see your postings. Please contact your instructor to be re-activated.';
$string['warnminslotperiod'] = 'Minimum slot period not met. Please make sure all slots are at a minimum of {$a} hours.';
$string['warnminslotperiodtitle'] = 'Slot time warning';
$string['warnsomeslotsnotsaved'] = 'Some slots were not saved due to date/time restrictions.';
$string['warntitle'] = 'Warning';
$string['week'] = 'Week';
$string['weekinyear'] = 'Week {$a}';
$string['weeklytitle'] = 'Weekly Availability';
$string['weekprev'] = 'Previous week';
$string['weeknext'] = 'Next week';

// Logbook
$string['addlognentry'] = 'Add log entry';
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
$string['landingsday'] = 'Day landings';
$string['deletelogentry'] = 'Delete entry';
$string['depgroup'] = 'Departure (ICAO/time)';
$string['deptime'] = 'Departure time (zulu)';
$string['dualtime'] = 'Dual time';
$string['dualtime_help'] = 'Flight time logged for the student in training flights';
$string['depicao'] = 'Departure ICAO';
$string['enginetype'] = 'Engine type';
$string['examinerpirep'] = 'Examiner PIREP';
$string['feedback'] = 'Feedback';
$string['flightcopilottimetooltip'] = 'Flight time logged for the co-pilot in a multi-crewed flights';
$string['flightdate'] = 'Flight date';
$string['flightrule'] = 'Flight rule';
$string['flightrule_help'] = 'flight operation in visual meteorological conditions (VMC) or operate in instrument meteorological conditions (IMC)';
$string['flightrulevfr'] = 'VFR';
$string['flightruleifr'] = 'IFR';
$string['flightsolo'] = 'solo flight';
$string['flighttraining'] = 'training flight';
$string['flightttestdual'] = 'skill test flight';
$string['flightttestmc'] = 'command check flight';
$string['flighttime'] = 'flight time';
$string['flighttime_help'] = 'Duration of the flight';
$string['flighttype'] = 'Flight type';
$string['flighttype_help'] = 'A regular training flight, a solo flight, or a check flight (line check / check ride)';
$string['flighttypetraining'] = 'Training';
$string['flighttypesolo'] = 'Solo';
$string['flighttypecheck'] = 'Check';
$string['fstd'] = 'FSTD';
$string['fstd_help'] = 'Flight Simulation Training Device qualification';
$string['groundtime'] = 'Ground time';
$string['groundtime_help'] = 'The ground training (brief & debrief) overall time';
$string['ifrtime'] = 'IFR time';
$string['ifrtime_help'] = 'Operational IFR time is the portion of the flight flown under Instrument Flight Rules, logged for all pilots flying the aircraft';
$string['instpirep'] = 'Instructor PIREP';
$string['instpirep_help'] = 'PIREP to be searched';
$string['instructortime'] = 'Instructor time';
$string['instructortime_help'] = 'Flight time logged for the instructor in training flights';
$string['landingsp1'] = 'Landings P1 (day/night)';
$string['landingsp1_help'] = 'The number day and night landings for P1 for each flight';
$string['landingsp2'] = 'Landings P2 (day/night)';
$string['landingsp2_help'] = 'The number day and night landings for P2 for each flight';
$string['logbook'] = 'logbook';
$string['logbookarr'] = 'Arrival';
$string['logbookcopilot'] = 'Copilot';
$string['logbookdate'] = 'Date';
$string['logbookday'] = 'Day';
$string['logbookdep'] = 'Departure';
$string['logbookdual'] = 'Dual';
$string['logbookformat'] = 'format';
$string['logbookformateasa'] = 'EASA';
$string['logbookformateasatip'] = 'European Aviation Safety Agency format';
$string['logbookformatcourse'] = 'Course format';
$string['logbookfunc'] = 'Function';
$string['logbookfstd'] = 'FSTD';
$string['logbookifr'] = 'IFR';
$string['logbookistr'] = 'Instructor';
$string['logbooklabel'] = 'Logbook';
$string['logbooklandings'] = 'Landings';
$string['logbookmakemodel'] = 'Make, Model';
$string['logbookme'] = 'ME';
$string['logbookmedesc'] = 'Multiple engine';
$string['logbookmultipilot'] = 'Multi-Pilot';
$string['logbookmy'] = 'My logbook';
$string['logbooknight'] = 'Night';
$string['logbookops'] = 'Operational';
$string['logbookp1name'] = 'Name(s) PIC';
$string['logbookpic'] = 'PIC';
$string['logbookpicus'] = 'PICUS';
$string['logbookplace'] = 'Place';
$string['logbookreg'] = 'Reg\'n';
$string['logbookremarks'] = 'Remarks';
$string['logbookroute'] = 'Route';
$string['logbookse'] = 'SE';
$string['logbooksedesc'] = 'Single engine';
$string['logbooksinglepilot'] = 'Single-Pilot';
$string['logbooksolopirep'] = 'Solo PIREP';
$string['logbookstudent'] = 'Logbook';
$string['logbooksummary'] = 'LOGBOOK SUMMARY';
$string['logbooktime'] = 'Time';
$string['logbooktotaltime'] = 'Total Flight Time';
$string['logbooktotaltimestd'] = 'Total flight time';
$string['logentry'] = 'Logbook Entry';
$string['logentrymissing'] = 'No logbook entry!';
$string['logentrydeletesuccess'] = 'Logbook entry deleted';
$string['logentrydeletefailed'] = 'Unable to delete logbook entry';
$string['logentrysavesuccess'] = 'Logbook entry saved';
$string['logentrysaveunable'] = 'Unable to save logbook entry';
$string['multipilottime'] = 'Multi-pilot time';
$string['multipilottime_help'] = 'Flight time logged for each pilot in multi-crew flights';
$string['nighttime'] = 'Night flight time';
$string['nighttime_help'] = 'Operational night time is the portion of the flight flown in night time. Logged for the PIC and copilots';
$string['p1dual'] = 'Instructor';
$string['p1dual_help'] = 'The instructor (P1)';
$string['p1multicrew'] = 'P1';
$string['p1multicrew_help'] = 'P1 commonly the trainer';
$string['p1pirep'] = 'P1 PIREP';
$string['p1solo'] = 'P1 (solo)';
$string['p2dual'] = 'Student';
$string['p2dual_help'] = 'Student';
$string['p2multicrew'] = 'P2';
$string['p2multicrew_help'] = 'P2 commonly the trainee';
$string['p2pirep'] = 'P2 PIREP';
$string['pictime'] = 'PIC time';
$string['pictime_help'] = 'Flight time logged for the pilot in command (PIC). As per the EASA <a href="https://www.easa.europa.eu/sites/default/files/dfu/Part-FCL.pdf">Part-FLC</a>
    [pg30]. "the applicant for or the holder of a pilot license may log as PIC time all solo flight time, flight time as SPIC and flight time under supervision..."';
$string['picustime'] = 'PICUS time';
$string['picustime_help'] = 'Flight time logged by the pilot in command under supervision (PICUS), which is the FO or the student that passes a check ride';
$string['pilot'] = 'Pilot';
$string['pilotlogbook'] = 'Pilot\'s Logbook';
$string['pirep'] = 'PIREP';
$string['pireplabel'] = 'PIREP search';
$string['pirepfound'] = ' - PIREP found.';
$string['pirepfoundnotlinked'] = ' - PIREP found. (NOT LINKED!)';
$string['pirepsgroup'] = 'PIREPs (P1/P2)';
$string['remarks'] = 'Remarks';
$string['sessionlength'] = 'Session length';
$string['soloflight'] = 'Solo Flight';
$string['solotime'] = 'Solo time';
$string['titleeditlogentry'] = 'Editing logbook entry';
$string['titlenewlogentry'] = 'New logbook entry';
$string['totalcheckpilottime'] = 'Total examiner flight time';
$string['totalcopilottime'] = 'Total Co-pilot flight time';
$string['totaldualtime'] = 'Total dual flight time';
$string['totalexaminertime'] = 'Total examiner flight time';
$string['totalgroundtime'] = 'Total ground time';
$string['totalinstructorime'] = 'Total Instructor flight time';
$string['totalifrtime'] = 'Total IFR flight time';
$string['totalinstructortime'] = 'Total instructor flight time';
$string['totallandingsday'] = 'Total day landings';
$string['totallandingsnight'] = 'Total night landings';
$string['totalmultipilottime'] = 'Total Multi-pilot flight time';
$string['totalnighttime'] = 'Total night flight time';
$string['totalpictime'] = 'Total PIC flight time';
$string['totalpicustime'] = 'Total PICUS flight time';
$string['totalsessionlength'] = 'Total session length';
$string['totalsolotime'] = 'Total solo time';
$string['verifypirep'] = 'Verify PIREP';

// profile
$string['assigntoinstructor'] = 'Assign to Instructor';
$string['auditreports'] = 'Audit';
$string['completereport'] = 'Complete report';
$string['comment'] = 'Comments';
$string['commentsaved'] = '- Comment saved';
$string['commentnotsaved'] = '- Unable to save comment';
$string['conductedsessions'] = 'Sessions Conducted';
$string['courseactivity'] = 'Course activity';
$string['dangerarea'] = 'Danger Area';
$string['daysback'] = 'days back';
$string['graduated'] = 'Graduated';
$string['endorsement'] = 'Skill Test Recommendation';
$string['endorsementmgs'] = 'Recommended by {$a->endorser} on {$a->endorsedate}';
$string['examinersince'] = 'Examiner since';
$string['graduation'] = 'Certification';
$string['graduationconfirmation'] = '<p><strong>Process completed successfully...</strong></p><p>&nbsp;\'{$a->fullname}\' certification is complete and the following actions were performed:</p><ul>
    <li style="padding-bottom: 20px;">\'{$a->fullname}\' <strong>course completion</strong> has been processed.</li>';
$string['graduationconfirmationbadges'] = '<li style="padding-bottom: 20px;"><strong>Course badge</strong> for {$a->courseshortname} was generated and sent via email to \'{$a->fullname}\'.&nbsp; A copy of the generated badge notification was sent to you as well.</li>';
$string['graduationconfirmationnotify0'] = '<li style="padding-bottom: 20px;">A <strong>congratulatory message</strong> was sent informing course managers.</li>';
$string['graduationconfirmationnotify1'] = '<li style="padding-bottom: 20px;">A <strong>congratulatory message</strong> was sent to active course participants.</li>';
$string['graduationconfirmationnotify2'] = '<li style="padding-bottom: 20px;">A <strong>congratulatory message</strong> was sent to members of the same group.</li>';
$string['feedbackreport'] = 'Feedback report';
$string['forcecompletion'] = 'Force course completion';
$string['forcecompletion_help'] = 'Intended for forcing course completion for legacy graduates';
$string['forcecompletionconfirm'] = 'Forcing completion will flag all course requirements as complete. Are you sure you want to force course completion?';
$string['instructorsince'] = 'In {$a} role since';
$string['keepactive'] = 'Keep active';
$string['lastlesson'] = 'Last lesson completed';
$string['lastgraded'] = 'Last session graded';
$string['mentorreport']='Mentored sessions report';
$string['modulescompleted'] = 'Modules completed';
$string['moodleprofile'] = 'Moodle profile';
$string['modscompletemsg'] = '{$a->usermods} out of {$a->coursemods} ({$a->percent}% complete)';
$string['noshows'] = 'No Shows';
$string['notfound'] = 'not found';
$string['notqualified'] = 'Not qualified';
$string['onhold'] = 'On-hold';
$string['outlinereport'] = 'Outline report';
$string['pageviews'] = 'page views';
$string['practicalexamreport']='Practical examination report';
$string['profilestudent'] = 'Student profile';
$string['profileinstructor'] = 'Instructor profile';
$string['progression'] = 'Session progression';
$string['putonhold'] = 'Put on-hold';
$string['qualified'] = 'Qualified for';
$string['recency'] = 'Recency';
$string['recommendationletterlink'] = 'Recommendation letter';
$string['reportdate'] = 'Report date';
$string['restrictionoverride'] = 'Override {$a} day restriction';
$string['score'] = 'Score';
$string['sessionsflighttime'] = 'Total flight hours';
$string['sessionsgroundtime'] = 'Total ground hours';
$string['sessionssummary'] = 'Sessions Summary';
$string['sim1'] = 'Primary simulator';
$string['sim2'] = 'Secondary simulator';
$string['skilltest']='Skill Test';
$string['skilltestendorse']='Recommend for skill test';
$string['slots'] = 'Slots';
$string['slotsactive'] = 'Posted slots';
$string['theoryexamreport']='Theory examination report';
$string['totalatohours']='Total {$a} hours';
$string['totalexamhours']='Total exam hours';
$string['totalexamsessions']='Total exam sessions';
$string['totalsessionhours']='Total training hours';
$string['totalsessionsbooked']='Total booked sessions';
$string['totalsessionsgraded']='Total graded sessions';
$string['xcoursebookings']='Show bookings from all courses';
$string['xcoursebookings_help']='Show bookings from all course';

// reports
$string['aircrafttypelabel'] = 'Aircraft type/class course conducted in';
$string['candidatedetails'] = 'Candidate Details';
$string['candidateflyinghours'] = 'Candidate\'s flying hours';
$string['candidatename'] = 'Candidate name';
$string['checklistuse'] = 'Use of checklists, airmanship, control of aeroplane by external visual references, anti-icing procedures etc. apply in all sections.';
$string['comments'] = 'Comments';
$string['copyright'] = 'Copyright © {$a->ato} {$a->year}. All rights reserved.';
$string['cumulative'] = 'Cumulative';
$string['datecommenced'] = 'Date training commenced';
$string['datecompleted'] = 'Date training completed';
$string['declarationtlabel'] = 'Declaration';
$string['declarationtext'] = 'I certify that the candidate has successfully completed all the required exercises to a satisfactory standard, meets the pre-requisite
    requirements in accordance with {$a->ato} Flight Crew Standards and VATSIM PTD requirements, and I consider the candidate fully ready to undertake the Skill Test.';
$string['deparr'] = 'Dep/Arr';
$string['dualflight'] = 'Dual flight instruction';
$string['duration'] = 'Duration';
$string['examiner'] = 'Examiner';
$string['examinerdetails'] = 'Examiner\'s Details';
$string['examreportcopies'] = 'Copies of the report shall be submitted to (1) The candidate (2) BAVirtual Flight Training (3) The Examiner.';
$string['examdate'] = 'Exam date';
$string['examend'] = 'Exam end time';
$string['examstart'] = 'Exam start time';
$string['gradescore'] = 'Grade';
$string['recommendationreport'] = 'Recommendation For Skill Test';
$string['recommendationreportsubject'] = 'This is {$a->ato} ATO Recommendation for Skill Test letter compiled by the instructor recommending a student for a Skill Test.';
$string['footnote'] = '*These items may be combined at the discretion of the Examiner.';
$string['instructor'] = 'Instructor';
$string['mentorreport'] = 'Mentored Sessions Report';
$string['mentorreportdesc'] = 'The theory examination is a multi-choice, randomly selected question paper that is time-limited. It is self-contained within the Moodle program for the {$a->ato}
    {$a->coursename} course. {$a->studentname} has completed the test successfully with {$a->attempts} attempt(s) achieving the pass mark of {$a->score} out of {$a->total} ({$a->percent}%) exactly.';
$string['mentorreportsubject'] = 'This is {$a->ato} ATO Mentored Sessions report detailing student feedback for mentored sessions of the {$a->coursename} course.';
$string['otherflyingtime'] = 'Other flying time';
$string['practicalexamreport'] = 'Practical Examination Report';
$string['practicalexamreportsubject'] = 'This is {$a->ato} ATO Practical Examination report detailing the examiner\'s feedback of the {$a->coursename} course.';
$string['ratinglabel'] = 'Rating applied for';
$string['recommendationletterver'] = 'Rev 2 09 Jun 22';
$string['recommendedby'] = 'Recommended for skill test by';
$string['results'] = 'Results';
$string['skill'] = 'Skill';
$string['theoryexamreport'] = 'Theory Examination Report';
$string['theoryexamreportsubject'] = 'This is {$a->ato} ATO Theory Examination report summarizing the student\'s score in the {$a->coursename} course.';
$string['tobecompletedbyexaminer'] = 'to be completed by the examiner';
$string['trainingaudit'] = 'Flight Training Audit';
$string['trainingcontent'] = 'Training Content';
$string['vatsimid'] = 'VATSIM CID';
$string['vatsimidmissing'] = '-- missing! --';
$string['aircrafttype'] = "Aircraft Type";
$string['ato'] = "ATO";
$string['attempts'] = "Attempts";
$string['attempt'] = "Attempt";
$string['fail'] = "Fail";
$string['pass'] = "Pass";
$string['route'] = "Route";

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
$string['messageprovider:logbook_notification'] = 'Student notification of a logbook entry';
$string['messageprovider:session_cancellation'] = 'Student session cancellation / no-show notifications';
$string['messageprovider:noshow_notification'] = 'Student notification of no-show and resulting actions';
$string['messageprovider:noshow_instructor_notification'] = 'Instructor notification of student no-show actions taken';
$string['messageprovider:reinstatement_notification'] = 'Student notification of being reinstated after suspension';
$string['messageprovider:reinstatement_instructor_notification'] = 'Instructor notification of student no-show suspension reinstatement';
$string['messageprovider:inactive_warning'] = 'Student inactive warning notification';
$string['messageprovider:onhold_warning'] = 'Student on-hold warning notification';
$string['messageprovider:onhold_notification'] = 'Student placed on-hold notification';
$string['messageprovider:suspension_notification'] = 'Student suspended notification';
$string['messageprovider:sessionoverdue_notification'] = 'Instructor session overdue notification';
$string['messageprovider:availabilityposting_notification'] = 'Student availability posting notification';
$string['messageprovider:recommendation_notification'] = 'Student recommendation notification';
$string['messageprovider:graduation_notification'] = 'Graduating students notification';

// email to student: notifying of booked session
$string['emailnotifysubject'] = '{$a->coursename} session booking notification: \'{$a->exercise}\'';
$string['emailnotifymsg'] = '{$a->instructor} has booked a session for your availability on {$a->sessiondate} for \'{$a->exercise}\'.
    Please confirm this booking by clicking on this link: {$a->confirmurl}';
$string['emailnotifyhtml'] = '<div style="font-family:sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p>{$a->instructor} has booked a session for your availability on <strong>{$a->sessiondate}
    </strong> for \'<i>{$a->exercise}</i>\'.</p><p>Please <a href=\'{$a->confirmurl}\'>confirm</a> this booking.</p></div>';

// email to instructor: confirming booked session
$string['emailconfirm'] = 'Session booked';
$string['emailconfirmsubject'] = '{$a->coursename} session booked: \'{$a->exercise}\'';
$string['emailconfirmmsg'] = '\'{$a->exercise}\' session booked with {$a->student} for \'{$a->exercise}\' on {$a->sessiondate}.';
$string['emailconfirmhtml'] = '<div style="font-family:sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p>Session booked with <strong>{$a->student}</strong> for \'<i>{$a->exercise}</i>\' on
    <strong>{$a->sessiondate}</strong>.</p></div>';

// calendar links
$string['calendarshtmlstart'] = '<div style="font-family:sans-serif"><p style="font-size: .9em;">Add a reminder to your calendar:<br />
    <table style="border-collapse: collapse; width: 100%;"><tbody>';
$string['calendarshtmlgoogle'] = '<tr><td style="width: 50px; text-align: center;"><img src="{$a->pixrooturl}/btn_google_light_normal_ios.png" alt="Add to Google calendar"/></td>
        <td><a style="text-decoration: none;" href="{$a->googleurl}"><span style="font-size: 13px; color: #01579b;">Google Calendar</span></a></td></tr>';
$string['calendarshtmlmicrosoft'] = '<tr><td style="width: 50px; text-align: center;"><div style="box-shadow: 0 0 2px 2px #e1e1e1; padding: 5px 0px; margin: 0 6 0 6px">
        <img src="{$a->pixrooturl}/btn_outlook.png" alt="Add to Outlook Live calendar" width="25" height="25" /></div></td>
        <td><a style="text-decoration: none;" href="{$a->outlookurl}"><span style="font-size: 13px; color: #01579b;">Outlook Live Calendar</span></a></td></tr><tr>';
$string['calendarshtmlics'] = '<td style="width: 50px; text-align: center;"><div style="box-shadow: 0 0 2px 2px #e1e1e1; padding: 5px 0px; margin: 3 6 0 6px">
            <img src="{$a->pixrooturl}/btn_ical.png" alt="Add to desktop calendar" width="28" height="28" /></div></td>
        <td><a style="text-decoration: none;" href="{$a->icsurl}"><span style="font-size: 13px; color: #01579b;">Download ics/iCal file</span></a></td></tr>';
$string['calendarshtmlend'] = '</tbody></table><hr /></div>';

// email to instructor: session confirmed by student
$string['emailinstconfirm'] = 'Booked session confirmed by Student';
$string['emailinstconfirmsubject'] = '{$a->coursename} - Student confirmed booking: \'{$a->exercise}\'';
$string['emailinstconfirmnmsg'] = '{$a->student} confirmed your booked session for \'{$a->exercise}\' on {$a->sessiondate} zulu.';
$string['emailinstconfirmhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p><strong>{$a->student}</strong> confirmed your booked session for \'<i>{$a->exercise}</i>\' on
    <strong>{$a->sessiondate} zulu</strong>.</p></p><hr />';

// email to student: logbook entry recorded
$string['emaillogentry'] = 'Logbook entry recorded';
$string['emaillogentrysubject'] = '{$a->coursename} logbook entry recorded: \'{$a->title}\'';
$string['emaillogentrymsg'] = '\'{$a->title}\' logbook entry recorded for {$a->student} by \'{$a->instructor}\' on {$a->recordeddate}.
    Ground time: {$a->groundtime}
    Flight time: {$a->flighttime}
    Aircraft: {$a->aircraft}
    PIREP: {$a->pirep}
    Departure ICAO: {$a->dept}
    Arrival ICAO: {$a->arr}
    Route: {$a->route}';
$string['emaillogentryhtml'] = '<div style="font-family:sans-serif"><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> -> {$a->title}
    <p><hr /><p>\'{$a->title}\' <a href=\'{$a->logbookurl}\'>logbook</a> entry recorded for <strong>{$a->student}</strong> by \'<i>{$a->instructor}</i>\' on {$a->recordeddate}.</p>
    <p><strong>Flight details:</strong></p><pre>    Ground time&#09;&#09;{$a->groundtime}
    Flight time&#09;&#09;{$a->flighttime}
    Aircraft&#09;&#09;{$a->aircraft}
    PIREP&#09;&#09;{$a->pirep}
    Departure ICAO&#09;{$a->dept}
    Arrival ICAO&#09;{$a->arr}
    Route&#09;&#09;{$a->route}
    Remarks</pre><span style="font-size:14px; padding-left: 50px; position: relative; display: flex; justify-content: flex-start; flex-wrap: wrap;">{$a->remarks}</span><br />
    <hr /><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> -> {$a->title}</div>';

// email to student: session cancellation
$string['emailcancel'] = '{$a->coursename} session booking cancellation: \'{$a->exercise}\'';
$string['emailcancelmsg'] = '{$a->instructorname} has cancelled your booked session scheduled for {$a->sessiondate} on \'{$a->exercise}\'.
    Instructor\'s comments: {$a->comment}.
    Please note that you will have to post new availability slots as previous slots were purged.';
$string['emailcancelhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>{$a->instructorname} has cancelled your booked session scheduled for
    <strong>{$a->sessiondate}</strong> on \'<i>{$a->exercise}</i>\'.</p><p><strong>Instructor\'s comments:</strong><br />{$a->comment}</p><p>
    <span style=\'color: red\'>Please note that you will have to post new availability slots as previous slots were purged.</span></p></p><hr />';
$string['emailcancelinstmsg'] = '{$a->studentname} session scheduled for {$a->sessiondate} on \'{$a->exercise}\' has been cancelled.
    Your comments: {$a->comment}.';
$string['emailcancelinsthtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>{$a->studentname} session scheduled for <strong>{$a->sessiondate}</strong> on \'<i>{$a->exercise}</i>\' has been cancelled.
    </p><p><strong>Your comments:</strong><br />{$a->comment}</p><p><hr />';

// email to student: noshow warning (strike 1)
$string['emailnoshow1'] = '{$a->coursename} missed session warning!';
$string['emailnoshow1msg'] = 'You have missed an instructor-led session on {$a->sessiondate} with your instructor {$a->instructorname} for exercise \'{$a->exercise}\'.
    According to the {$a->coursename} course policy around \'No show\' incidents, students that miss a session without a prior notice to their instructor are given a warning.
    Please consider this as your warning and do note that repeated incidents will result in suspension and unenrollment from the course.
    Training staff';
$string['emailnoshow1html'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>You have missed an instructor-led session on {$a->sessiondate} with your instructor {$a->instructorname} for exercise \'{$a->exercise}\'.</p>
    <p>According to the {$a->coursename} course policy around \'No show\' incidents, students that miss a session without a prior notice to their instructor are given a warning.</p>
    <p>Please consider this as your warning and do note that repeated incidents will result in suspension and unenrollment from the course.</p>
    <p>&nbsp;</p><p>Training staff</p><hr />';

// email to student: noshow suspension (strike 2)
$string['emailnoshow2'] = '{$a->coursename} suspension due to repeated \'no-show\'';
$string['emailnoshow2msg'] = 'You have missed an instructor-led session on {$a->sessiondate} with your instructor {$a->instructorname} for exercise \'{$a->exercise}\'.
    This marks the second time you miss an instructor-led session in a {$a->noshowperiod}-day period.
    Therefore, you are being suspended from the course for {$a->suspensiondays} days in accordance with the {$a->coursename} course policy until {$a->reinstatementdate}.
    Please note that this will be the final warning and missing another instructor-led session will result in your unenrolment from the {$a->coursename} course.
    Training staff';
$string['emailnoshow2html'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>You have missed an instructor-led session on {$a->sessiondate} with your instructor {$a->instructorname} for exercise \'{$a->exercise}\'.</p>
    <p>This marks the second time you miss an instructor-led session in a {$a->noshowperiod}-day period.   Therefore, you are being suspended from the course for {$a->suspensiondays} days in accordance with the {$a->coursename} course policy until {$a->reinstatementdate}.</p>
    <p>Please note that this will be the final warning and missing another instructor-led session will result in your unenrolment from the {$a->coursename} course.</p>
    <p>&nbsp;</p><p>Training staff</p><hr />';

// email to student: noshow unenrolment (strike 3)
$string['emailnoshow3'] = '{$a->coursename} unenrolment from the {$a->coursename} course!';
$string['emailnoshow3msg'] = 'You have missed an instructor-led session on {$a->sessiondate} with your instructor {$a->instructorname} for exercise \'{$a->exercise}\'.
    This marks the third time you miss an instructor-led session in a {$a->noshowperiod}-day period.
    Therefore, you will be unenrolled from the {$a->coursename} in accordance with the {$a->coursename} course policy.
    Training staff';
$string['emailnoshow3html'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>You have missed an instructor-led session on {$a->sessiondate} with your instructor {$a->instructorname} for exercise \'{$a->exercise}\'.</p>
    <p>This marks the third time you miss an instructor-led session in a {$a->noshowperiod}-day period. Therefore, you will be unenrolled from the {$a->coursename} in accordance with the {$a->coursename} course policy.</p>
    <p>&nbsp;</p><p>Training staff</p><hr />';

// email to instructor: student noshow warning (strike 1)
$string['emailnoshowinst1'] = '{$a->coursename}: {$a->studentname} received a \'Missed session warning\'';
$string['emailnoshowinst1msg'] = '{$a->studentname} missed an instructor-led session on {$a->sessiondate} with {$a->instructorname} for exercise \'{$a->exercise}\'.
    The student was issued an automatic warning and was notified that repeated incidents will result in suspension and unenrollment from the course.
    Training staff';
$string['emailnoshowinst1html'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>{$a->studentname} missed an instructor-led session on {$a->sessiondate} with {$a->instructorname} for exercise \'{$a->exercise}\'.</p>
    <p>The student was issued an automatic warning and was notified that repeated incidents will result in suspension and unenrollment from the course.</p>
    <p>&nbsp;</p><p>Training staff</p><hr />';

// email to instructor: student noshow suspension (strike 2)
$string['emailnoshowinst2'] = '{$a->coursename}: {$a->studentname} Suspension';
$string['emailnoshowinst2msg'] = '{$a->studentname} missed an instructor-led session on {$a->sessiondate} with {$a->instructorname} for exercise \'{$a->exercise}\'.
    This marks the second time {$a->studentname} misses an instructor-led session in a {$a->noshowperiod}-day period.
    Therefore, {$a->studentname} is being suspended from the course for {$a->suspensiondays} days in accordance with the {$a->coursename} course policy until {$a->reinstatementdate}.
    {$a->studentname} was notified that this will be a final warning and missing another instructor-led session will result in unenrolment from the {$a->coursename} course.
    Training staff';
$string['emailnoshowinst2html'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>{$a->studentname} missed an instructor-led session on {$a->sessiondate} with {$a->instructorname} for exercise \'{$a->exercise}\'.</p>
    <p>This marks the second time {$a->studentname} misses an instructor-led session in a {$a->noshowperiod}-day period.   Therefore, {$a->studentname} is being suspended from the course for {$a->suspensiondays} days in accordance with the {$a->coursename} course policy until {$a->reinstatementdate}.</p>
    <p>{$a->studentname} was notified that this will be a final warning and missing another instructor-led session will result in unenrolment from the {$a->coursename} course.</p>
    <p>Training staff</p><hr />';

// email to instructor: student noshow unenrolment (strike 3)
$string['emailnoshowinst3'] = '{$a->coursename}: {$a->studentname} unenrolment recommendation!';
$string['emailnoshowinst3msg'] = '{$a->studentname} missed an instructor-led session on {$a->sessiondate} with {$a->instructorname} for exercise \'{$a->exercise}\'.
    This marks the third time {$a->studentname} misses an instructor-led session in a {$a->noshowperiod}-day period.  Therefore, {$a->studentname} is recommended to be unenrolled from the {$a->coursename} in accordance with the {$a->coursename} course policy.
    {$a->studentname} was notified of being scheduled to be unenroled from the {$a->coursename} course.
    Training staff';
$string['emailnoshowinst3html'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>{$a->studentname} missed an instructor-led session on {$a->sessiondate} with {$a->instructorname} for exercise \'{$a->exercise}\'.</p>
    <p>This marks the third time {$a->studentname} misses an instructor-led session in a {$a->noshowperiod}-day period.   Therefore, {$a->studentname} is recommended to be unenrolled from the {$a->coursename} in accordance with the {$a->coursename} course policy.</p>
    <p>{$a->studentname} was notified of being scheduled to be unenroled from  the {$a->coursename} course.</p>
    <p>&nbsp;</p><p>Training staff</p><hr />';

// email to student: noshow suspension reinstatement
$string['emailnoshowreinstatement'] = '{$a->coursename}: suspension reinstatement';
$string['emailnoshowreinstatementmsg'] = 'Your suspension period has ended and you have been reinstated to active status.  Make sure to complete pending lesson modules, post availability slots, and please do your utmost best to avoid missing future instructor-led sessions.
    Training staff';
$string['emailnoshowreinstatementhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>Your suspension period has ended and you have been reinstated to active status.  Make sure to complete pending lesson modules, post availability slots, and please do your utmost best to avoid missing future instructor-led sessions.</p>
    <p>&nbsp;</p><p>Training staff</p><hr />';

// email to instructor: student noshow suspension reinstatement
$string['emailnoshowreinstatementinstmsg'] = '{$a->studentname} no-show suspension period completed and the student has been reinstated. {$a->studentname} has been notified of being back in active status';
$string['emailnoshowreinstatementinsthtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    {$a->exercise}</p><hr /><p>{$a->studentname} no-show suspension period has ended and the student has been reinstated. {$a->studentname} has been notified of being back in active status.</p><hr />';

// email to student: inactive warning
$string['emailinactivewarning'] = '{$a->coursename}: Inactivity notification';
$string['emailinactivewarningmsg'] = 'Since your last booking on \'{$a->lastbookeddate}\' you have not posted availability and/or completed next lesson.  Please post availability slots
    and/or complete next lesson to avoid being automatically placed on-hold on \'{$a->onholddate}\'. You can post an availability slot for
    the \'{$a->coursename}\' from the following link: {$a->slotsurl}';
$string['emailinactivewarninghtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Since your last booking on <strong>\'{$a->lastbookeddate}\'</strong> you have not posted availability and/or completed next lesson.  Please post an availability slots
    and/or complete next lesson to avoid being automatically placed on-hold on <strong>\'{$a->onholddate}\'</strong>.</p><p>You can post availability slot for
    the \'{$a->coursename}\' course <a href="{$a->slotsurl}">here</a>.</p><hr />';

// email to student: on-hold warning
$string['emailonholdwarning'] = '{$a->coursename}: On-hold warning';
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
$string['emailoverduenotify'] = '{$a->coursename}: Session overdue notification';
$string['emailoverduenotifymsg'] = 'Please note that you have {$a->status}.  Please book a session with a student from the booking view. Otherwise, please ask the course administrator to remove you from the list of active instructors.
You can book a session from the following view link: {$a->bookingurl}';
$string['emailoverduenotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Please note that you have {$a->status}.</p><p>Please book a session with a student from the <a href="{$a->bookingurl}">booking view</a>
    as soon as possible. To stop receiving these messages, please ask the course administrator to remove you from the list of active instructors.</p><hr />';
$string['emailoverduenotifyinstmsg'] = 'Please note that {$a->instructorname} has {$a->status}.  The instructor has been notified to book a session a student from the list, otherwise to request removal from the list of active instructors.';
$string['emailoverduenotifyinsthtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a></p><hr />
    <p>Please note that {$a->instructorname} has {$a->status}.</p><p>The instructor has been notified to book a session with a student from the list,
    otherwise to request removal from the list of active instructors.</p><hr />';
$string['emailoverduestatus'] = 'not booked a session since \'{$a}\'';

// email to instructors: student availability posting notification
$string['emailavailpostingnotify'] = '{$a->coursename}: {$a->studentname} posted availability';
$string['emailavailpostingnotifymsg'] = '{$a->studentname} had posted availability for the following days:

        {$a->postingstext}

        You can book a session with {$a->firstname} from the following view link: {$a->bookingurl}';
$string['emailavailpostingnotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr />
    <p>{$a->studentname} had posted availability for the following days:</p><p>&nbsp;</p><p><div style="padding-left: 50px;">{$a->postingshtml}</div></p><p>&nbsp;</p>
    <div style="padding-left: 0px;"><p>Click <a href="{$a->bookingurl}">here</a> to book a session with {$a->firstname}.</p><hr /></div>';

// email to instructors: student recommendation notification
$string['emailrecommendationnotify'] = '{$a->coursename}: {$a->studentname} QXC recommendation';
$string['emailrecommendationnotifymsg'] = '{$a->coursename} student {$a->studentname} has completed the qualifying cross-country session to standard.
    {$a->firstname} has been recommended for the {$a->skilltest} examination by {$a->instructorname}.

    Instructor recommendation letter: {$a->recommendltrurl}

    Note to examiners: please coordinate with the {$a->coursename} training manager to arrange for {$a->firstname}\'s {$a->skilltest} examination.
    {$a->bookingurl}';
$string['emailrecommendationnotifyhtml'] = '<font face="sans-serif"><p><a href=\'{$a->courseurl}\'>{$a->coursename}</a> -> <a href=\'{$a->assignurl}\'>Assignment</a> ->
    <a href=\'{$a->exerciseurl}\'>{$a->exercise}</a></p><hr /><p>{$a->coursename} student <strong>{$a->studentname}</strong> has completed the qualifying cross-country assignment to standard.
    <p>{$a->firstname} has been recommended for the {$a->skilltest} examination by {$a->instructorname}.</p><p><a href="{$a->recommendltrurl}">Instructor recommendation letter</a></p>
    <p>&nbsp;</p><p><i>Note to examiners: please coordinate with the {$a->coursename} training manager to arrange for {$a->firstname}\'s {$a->skilltest} examination.</i></p><hr />';

// settings
$string['activitycountweight'] = 'Course activity prioritization';
$string['activitycountweightdesc'] = 'weight multiplier to calculate prioritization for course activity';
$string['atoname'] = 'Name';
$string['atosection'] = 'Authorized Training Organization (ATO)';
$string['completionweight'] = 'Lesson completion prioritization';
$string['completionweightdesc'] = 'weight multiplier to calculate prioritization of lesson completion';
$string['gradmsgbody'] = 'Message body';
$string['gradmsgbodydesc'] = 'message body for the congratulatory notification of graduating students sent to course participants. Leave blank to prevent notifications from being sent.
    For more details refer to <a href="https://docs.moodle.org/400/en/Session_Booking#Skillset_evaluation_%26_graduation" target="_blank" rel="noopener noreferrer">Skillset evaluation & graduation</a>';
$string['gradmsgsubject'] = 'Message subject';
$string['gradmsgsubjectdesc'] = 'message subject for the congratulatory notification of graduating students sent to course participants. Leave blank to prevent notifications from being sent.';
$string['firstsession'] = 'First session time';
$string['firstsessiondesc'] = 'first allowable session time';
$string['lastsession'] = 'Last session time';
$string['lastsessiondesc'] = 'last allowable session time';
$string['pluginconfigssection'] = 'Integrations';
$string['configsjson'] = 'Advanced configurations';
$string['configsjsondesc'] = 'custom configuration to access external data and other hidden features in JSON format (<a href="https://docs.moodle.org/400/en/Session_Booking#Advanced_configurations" target="_blank" rel="noopener noreferrer">Advanced configurations</a>)';
$string['postingsection'] = 'Availability posting';
$string['prioritizationsection'] = 'Booking prioritization';
$string['recencydaysweight'] = 'Recency prioritization';
$string['recencydaysweightdesc'] = 'weight multiplier to calculate prioritization for session recency';
$string['slotcountweight'] = 'Slot count prioritization';
$string['slotcountweightdesc'] = 'weight multiplier to calculate prioritization for availability slots';
$string['weeksahead'] = 'Availability posting weeks lookahead';
$string['weeksaheaddesc'] = 'allowable weeks lookahead of availability posting. 0=Unlimited';

// install
$string['exercisetitles'] = 'Course exercise titles';
$string['exercisetitlesdesc'] = 'Use to improve the display of long exercise titles on the instructor dashboard page (one title per line, use &lt;br/&gt; tag to insert a line-break in a title)';
$string['homeicao'] = 'Home airport ICAO';
$string['postingwait'] = 'Posting wait restriction';
$string['postingwaitdesc'] = 'A restriction period in days between the last conducted session and the next time a student can post availability (0 = disable restriction)';
$string['minslotperiod'] = 'Minimum slot period';
$string['minslotperioddesc'] = 'Minimum amount of hours required to book an availability slot (0 = disable restriction)';
$string['onholdperiod'] = 'On-hold restriction';
$string['onholdperioddesc'] = 'A restriction period in days after which inactive students will be automatically placed on-hold (0 = disable restriction)';
$string['outcomerating'] = 'Course outcome';
$string['outcomeratingdesc'] = 'Course completion outcome if applicable (i.e. VATSIM P1 rating)';
$string['overdueperiod'] = 'Instructor session overdue notification';
$string['overdueperioddesc'] = 'A period in days after which inactive instructors will be automatically sent a notification (0 = disable restriction)';
$string['requirelessoncompletion']= 'Requires lesson completion';
$string['requirelessoncompletiondesc'] ='Whether lesson completion is required prior to an air exercise.';
$string['requiresskillseval']= 'Requires skills evaluation';
$string['requiresskillsevaldesc'] ='Whether skills evaluation is required at the end of the course.';
$string['suspensionperiod'] = 'Suspension restriction';
$string['suspensionperioddesc'] = 'A restriction period in days after which inactive students will be automatically suspended from the course (0 = disable restriction)';
$string['trainingaircraft'] = 'Training aircraft ICAO';
$string['trainingaircraftdesc'] = '(one per line)';
$string['trainingtype'] = 'Training type';
$string['useplugin'] = 'Enable session booking';

// Groups
$string['grouponholddesc'] = 'Group to track students put on hold.';
$string['groupinactivedesc'] = 'Group to track inactive instructors.';
$string['groupgraduatesdesc'] = 'Group to track graduated students.';
$string['groupkeepactivedesc'] = 'Group to track students from being placed on hold.';
$string['groupexaminersdesc'] = 'Group to identify examiner instructors.';

// Plugin errors
$string['errorcertifiernotexaminer'] = 'Permission denied. Only the examiner can certify a graduating student.';
$string['errorconflictingbooking'] = 'Conflicts with existing booking with {$a->studentname} for {$a->coursename} \'{$a->exercisename}\' on {$a->date}.';
$string['errorcoresubscriber'] = 'The site can not be a Session Booking plugin subscriber';
$string['errordelete'] = 'Failed to delete logentry';
$string['errorexercisemissing'] = 'No exercise specified!';
$string['errorgradeitemnotfound'] = 'Assignment grade item not found. Unable to update student statistics.';
$string['errorgradenotfound'] = 'Assignment grade not found. Unable to update student statistics.';
$string['errorinvaliddate'] = 'Flight date cannot be before booked session date';
$string['errorinvalidarrtime'] = 'Arrival date/time must be greater than departure date/time';
$string['errorlandings'] = 'Number from 1-9';
$string['errorlinking'] = 'Failed to link instructor/student logentries';
$string['errorlogentrycancel'] = 'Errors encountered: Unable to cancel booking!';
$string['errorlogentryfetch'] = 'Error encountered while trying to fetch logbook entry with ID: ';
$string['errormissingexercise'] = 'Invalid exercise (id=0)';
$string['errornewattemptunable'] = 'Unable to add a new attempt.';
$string['errorp1pirepnotfound'] = ' - PIREP not found.';
$string['errorp1pirepnopilotintegration'] = ' - No pilot lookup integration.';
$string['errorp1pirepwrongpilot'] = ' - PIREP belongs to a different user.';
$string['errordbconnection'] = "Failed to connect to the database: ";
$string['errorgooglecreateevent'] = 'Error: Failed to authenticate client when adding a Google calendar event';
$string['erroroutlookcreateevent'] = 'Error: Failed to authenticate client when adding an Outlook Live calendar event';
$string['errorredirecttofile'] = 'Redirecting to file download...';
