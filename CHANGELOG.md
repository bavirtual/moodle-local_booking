# Change log

## [2024053100] - RELEASED 2024-05-31

### Fixed

- session booking when first session is 00:00

## [2024052900] - RELEASED 2024-05-29 V2.1

### Fixed

- logentry PIREP retrieval
- activity score uasort() bool deprecation
- theoretical exam error when exam is manually graded

### Added

- move config to integration json field in plugin settings
- a link to the QXC Recommendation letter in the instructors notification.
- checking against all dependencies

### Changed

- moved config.json to plugin settings
- removed VATSIM integration for skill test as VATSIM as of this time performs skill test examination
- adding log entries in the Logbook view should not add a linked log entry
- user custom fields in install function

## [2024052000] - RELEASED 2024-05-20

### Added

- site admin graduate student (override)
- logentry Add capability from logbook w/ exercise selection

### Changed

- logentry dynamic rendering of fields based on flight type & training type

## [2024032600] - RELEASED 2024-03-26

### Fixed

- missing footer buttons in logentry popup

### Changed

- graded w/o session session click redirects to Moodle grade page
- removed modal_factory deprecated methods

## [2024032200] - RELEASED 2024-03-22

### Fixed

- course certification process for w/o VATSIM examiner evaluation form generation
- lessons incomplete check for multi-exercise lessons

### Added

- outcomerating and vatsimform custom course fields
- restrict exercise session booking to senior instructors based on moodle grading permissions for the exercise

## [2024012400] - RELEASED 2024-01-24

### Fixed

- fixed issues related to deprecate PHP dynamic properties ($var}
- fixed instructors able to book in past!

## [2024010700] - RELEASED 2024-01-07

### Changed

- icon identifying graded exercises without sessions with a circle check

## [2023122100] - RELEASED 2023-12-21

### Fixed

- student record displaying twice when graded on last exercise (QXC) before skill test

### Added

- evaluation form for each attempt of the skill test
- exercise attempts for reporting and skill test evaluation
- session column in mdl_local_booking_logbooks to associate log entries with sessions
- examiner class

### Changed

- session id evaluation to validate if a logentry has been recorded

## [2023100200] - RELEASED 2023-10-02

### Fixed

- duplicate students showing in dashboard when a students meets multiple sorting criteria

### Added

- get/set gradated_date in the students list

## [2023100200] - RELEASED 2023-10-02

### Fixed

- sending VATSIM p1-examiner-evluation exception for missing flight training manager user
- duplicate notifications when cancelling a booking
- number of sessions conducted and graded sessions totals in the Instructor Profile
- examiner time not being recorded when recording a skill test logbook entry

### Added

- progression grid in the student profile
- VATSIM CID in the skill test recommendation notification by examiners
- chronological order of graduated and suspended students

### Changed

- eliminated action and posts processing for Graduated and Suspended students to improve performance
- minor performance improvement to the mentor reporting loading times
- removed dynamic property for exercise titles in anticipation of future deprecation in PHP9

## [2023090301] - RELEASED 2023-09-03

### Fixed

- ato total hourse formatting

## [2023090300] - RELEASED 2023-09-03

### Added

- enable VATSIM integration for exam evaluation custom field
- instructor total ato hours

## [2023082700] - UNRELEASED 2023-08-27

### Added

- automated filling of the examiner evaluation form and sending to VATSIM Certification

## [2023080601] - RELEASED 2023-08-06

### Fixed

- external_multiple_structure exception related to 4.2.1+ Moodle release

## [2023080600] - RELEASED 2023-08-06

### Fixed

- externalapi AJAX exceptions related to 4.2.1+ Moodle release

## [2023070100] - RELEASED 2023-07-01

### Fixed

- students that became instructors to show in the graduated students list

### Added

- attempts in the practical skill test report

### Changed

- first feedback attachment of the skill test to be the examiner evaluation report
- Action hidden in the Instructor Dashboard for On-hold, Graduated, and Suspended users

## [2023062900] - RELEASED 2023-06-29 - hotfix

### Fixed

- Exams showing as an option for booking a sesison
- Student profile displaying as instructor in the title
- Listing the lessons that are incomplete in student notification

## [2023061702] - RELEASED 2023-06-17 - hotfix

### Fixed

- keep student active when taken off-hold
- instructor profile 'Sessions Conducted' based on assignment grades and grader id

## [2023061700] - RELEASED 2023-06-17

### Fixed

- instructor profile view based user role

### Added

- conflicting session booking validation for instructor and student
- course column in ‘My bookings’ list based on instructor preferences

### Changed

- session cancellation to delete Moodle calendar event
- session canceled confirmation to the instructor same as student

## [2023061100] - RELEASED 2023-06-11

### Changed

- revereted 4.2+ compatibility

## [2023051500] - RELEASED 2023-05-15

### Fixed

- instructor page title

### Changed

- default value of days in processing past data (i.e. past grades) to 3 years

## [2023051301] - UNRELEASED 2023-05-13

### Fixed

- external webservice class loading (pending 4.2 PR upgrade)

### Changed

- instructor profile conducted sessions based on grading vs plugin booking

## [2023051200] - UNRELEASED 2023-05-12

### Added

- no show history in student profile
- notification to booked student attempting to post availability
- instructor profile

### Fixed

- active students sort
- cron task on-hold notifications for booked students
- logbook page title
- error handling for pdf output

### Changed

- moodle 4.2 compatibility (i.e. single_button::BUTTON_PRIMARY type)
- show graduates regardless of how far back

## [2023042802] - RELEASED 2023-04-28 - hotfix

### Fixed

- missing my active bookings section

## [2023042800] - RELEASED 2023-04-28

### Added

- sorting color coding for posts_completed, noposts_completed, and not_completed
- datatable buttons in EASA logbook format (copy/excel/csv/pdf/print)

### Changed

- move lib views to output/views
- clean up styles.css of unused directives from old Moodle 3.x classes

## [2023040600] - RELEASED 2023-04-06

### Added

- logentry edit & delete handling in the logbook view
- examiner and instructor total time calculation and display for dual flights in logbook header

### Fixed

- lang text in students_progression mustache

## [2023040301] - RELEASED 2023-04-03 - hotfix

### Fixed

- examiner tooltip for final examination / skill test book button

## [2023040300] - RELEASED 2023-04-03 - hotfix

### Fixed

- last session booking disabled if instructor is not an examiner for progressing and objective not met sessions

## [2023033100] - RELEASED 2023-03-31 - hotfix

### Fixed

- flex no-wrap fleet column in instructor dashboard and students progression views

## [2023032400] - RELEASED 2023-03-24 - hotfix

### Fixed

- rendering of students progression page in student view

## [2023031800] - RELEASED 2023-03-18

### Fixed

- logbook entry quick form search PIREP null fix

### Added

- fleet field MVC

## [2023031300] - RELEASED 2023-03-13

### Fixed

- availability cell selection border

### Added

- secondary navigation callbacks

### Changed

- availability page formatting
- Session booking page formatting
- tertiary menus options and buttons moved to top of views
- Secondary menu link selection (check mark) based on the right context
- strtotime change to DateTimeImmutable::createFromFormat

## [2023022800] - RELEASED 2023-02-28

### Fixed

- solo flight editing

### Added

- student notification of logbook entry recording

### Changed

- logbook entries to show solo flight types in the heading of an entry
- participants list to be limited to past cutoff date for data retrieval
- remove instructors from graduates list

## [2023022700] - RELEASED 2023-02-27 - hotfix

### Fixed

- solo flight persistence exception

## [2023022200] - RELEASED 2023-02-22 - hotfix

### Fixed

- old grades for returning students are showing even when they're before past processing cutoff date 'LOCAL_BOOKING_PASTDATACUTOFF'

## [2023020700] - RELEASED 2023-02-07

### Fixed

- student read-only progression view doesn’t show booked sessions

### Added

- verification of student assignment submission for submission-enabled exercises as a pre-requisite to grading

### Changed

- replaced deprecated php8.1 functions: array_key_exists(), strftime(), current(), end() https://www.php.net/manual/en/appendices.php
- dynamic to static properties in preparation for php9: https://wiki.php.net/rfc/deprecate_dynamic_properties
- slot notification to use a ‘notified’ field in local_booking_slots instead of user preferences
- moved Moodle lib.php deprecation calls to other classes: set_user_prefs, process_submission_graded_event, get_week_days, get_week_start, get_booking_config, set_booking_config

## [2022122600] - RELEASED 2022-12-26 - hotfix

### Fixed

- no-post tag not showing in progression view

## [2022122003] - RELEASED 2022-12-20 - hotfix

### Fixed

- booking sessions interface to include quizes, noshows, and passed sessions
- style sheet progressing (color code)

## [2022122001] - RELEASED 2022-12-20

### Added

- ‘No Show’ feature under ‘My bookings’
    - 1st no-show: warning notification copying senior instructors
    - 2nd no-show: suspension notification & automatic suspension for 30 days
    - 3rd no-show: unenrolment notification copying senior instructors to manually unenrol the repeated offender

### Fixed

- progressing graded sessions status to show the new booked status vs progressing (color coding)
- instructor last booked date to show booking date/time vs booking creation date/time

### Changed

- cron task to automatically unsuspend students after the 30 day period is done and sends reinstatement notification
- updated some functions to be PHP 8 compatible
- moved save_booking and confirm_booking from lib

## [2022112700] - RELEASED 2022-11-27 - hotfix

### Fixed

- posted slots display should be course specific

## [2022111100] - RELEASED 2022-11-11 - hotfix

### Fixed

- cron task background processing of session booking scheduled notifications (availability postings)

## [2022102600] - UNRELEASED 2022-10-26

### Added

- Students progression page, a read-only view for students progression
- ability to save comments regarding a student in his/her profile (i.e. temporary leave)

### Changed

- replaced ATO name with the plugin name as the section (category) label in the course settings page
- removed ATO plugin settings information from config.json

## [2022102100] - RELEASED 2022-10-21

### Fixed

- night time field showing for VFR flights in the logbook entry form

### Changed

- Moodle OAuth2 API instead of custom API to get Google & Microsoft tokens for calendar integration
- took out dual time, ground time, and session time from practical exam and mentor reports
- only show Google and or Microsoft calendar links if the integration services are implemented in notifications
- find pirep button to show only when pirep integration is configure
- PICUS Time to be written out in pdf of mentor reports only for check flights
- inactive instructors activated when he/she books a session
- ‘Keep active’ status revoked after a session is graded

# Change log

## [2022100900] - RELEASED 2022-10-09

### Changed

- took out dual time, ground time, and session time from practical exam and mentor reports
- change pirep column to int(10) - upgrade.php

## [2022100702] - RELEASED 2022-10-07

### Fixed

- get_exercise_name() exception handling in calendar integration

### Added

- ability to add a logbook entry for progressing sessions
- flighttime column instead of using PIC time
- flighttime setter & getter methods
- flighttime reflected in reports, logbook, and notifications message instead of pictime

### Changed

- autofill behavior for all flight types and PIREP lookups in new log entry form
- cosmetic changes to the new logbook entry form
- revised flight time calculation rules

## [2022100400] - RELEASED 2022-10-04 - hotfix

### Fixed

- get_exercise_name() exception handling

### Changed

- moved graduation email notifications to schedulde cron job [notifications_task]
- logentry flight type to default to [Check] for graduation exercise
- congratulatory message changes for Multicrew type courses

## [2022100301] - RELEASED 2022-10-03

### Fixed

- qualified flag for QXC or other final examination qualification

## [2022100300] - UNRELEASED 2022-10-03

### Fixed

- reports flight time totals to factor in exercise sequence in sections containing multiple exercises
- failed or poor grade on final skill test shows the [Grad.] button instead of [Book]

### Added

- Added 'Last session booked' column to the 'Instructor Progression' section in the 'Instructor dashboard'

### Changed

- course modules retrieval to use Moodle API (get_course_content_items) instead of direct database access
- grade handling to use Moodle APIs instead of direct database access

## [2022093000] - RELEASED 2022-09-30

### Added

- Message noting student already booked for an exercise by another instructor

## [2022092603] - RELEASED 2022-09-26 - hotfix

### Fixed

- PICUS & IFR flight time display formatting

## [2022092602] - RELEASED 2022-09-26 - hotfix

### Fixed

- exception handling of null grades

## [2022092601] - UNRELEASED 2022-09-26 - hotfix

### Fixed

- rendering in PDFs exception handling for images in feedback comment

## [2022092600] - UNRELEASED 2022-09-26

### Fixed

- incorrect totals in EASA logbook. The total is course specific where it should be a grand total
- exception handling when grading past session 'Objective Not Met' rubric exercises
- evaluation button [Eval.] showing in courses with no skill evaluation
- tcpdf exception handling for embedded images and media content

### Added

- instructor notifications when a student posts availability
- instructor notifications when a student is recommended for the skills test
- rubric and scale grades in mentor report

### Changed

- mentor report logbook format depending on course (dual vs multi-crew)
- handling of images in pdf reports
- grade retrieval from standard Moodle grading manager/controller

## [2022091700] - RELEASED 2022-09-17 - hotfix

### Fixed

- VATSIM rating course setting to 50 chars
- Congratulatory message text

## [2022091500] - RELEASED 2022-09-15

### Changed

- verification of pdftk executable location

## [2022091400] - UNRELEASED 2022-09-14

### Fixed

- ”FPDF-Merge Error: Object streams are not supported” error in reading PDF skill reports

### Added

- update ATO info after Administrator Settings save through set_updatedcallback()
- install of PDF Toolkit package to fixe FPDF-Merge error of non FPDM standard PDFs
- set_booking_config to recursively update plugin config.json file

### Changed

- congratulatory message signing by the examiner
- moved ATO configurations to Administration Settings

## [2022090900] - RELEASED 2022-09-09 hotfix

### Fixed

- cron task failing in participant get_profile_field for custom fields, added necessary require

## [2022090602] - RELEASED 2022-09-06

### Added

- Reference to the BAV New Pilots Forum in the messaging

## [2022090601] - RELEASED 2022-09-06

### Added

- PICUS time
- Handling of profile evaluation form generation without showing intructional message

## [2022090600] - RELEASED 2022-09-06

### Changed

- Graduate no longer recieves a copy of the congratuary message
- Use of Evaluate and Graduate actions vs. Certify
- Formatting text part of the graduation process

## [2022090504] - RELEASED 2022-09-05 - hotfix

### Fixed

- Handling of course modules where deletion is in progress
- Congratulations message formatting
- Grading of final skill test exam is to be conducted by the examiner only
- Excluded course modules with deletion in progress on from subscriber exercises list
- Book button disabled for students with 'Objective Not Met' sessions although exercise prerequiste lessons are completed
- Quizes (i.e. PoF) showing in interim booking page
- Back button in student profile doesn't reload Session Booking

### Added

- Added certify capability to graduate a student sending badges, certificate, and message students broadcast

### Changed

- course subscriber section retrieval
- student current & next exercise methods
- removed assessment exercises and handling. Skill test assessments are submitted through the VATSIM from in exercise feedback file submission
- updated action class behavior for Book, Grade, and Certify actions
- course get_graduation exercise, last exercise in the course

## [2022073001] - RELEASED 2022-07-30 hotfix

### Fixed

- action tooltip returning null

## [2022073000] - RELEASED 2022-07-30

### Fixed

- subscriber course arrays to exclude empty values
- final exercise not showing in Session Booking in some courses
- Book button should be inactive when the last exercise is graded or not examiner

### Added

- When a student passes graduation exercise he/she is automatically added to the Graduates group

### Changed

- has_completed_lessons in student class to reduce sql roundtrips
- enabled Restriction Override once a session is canceled automatically

## [2022072400] - RELEASED 2022-07-24

### Fixed

- Lessons with multiple assignments showing disabled Book button even when the student completed the first exercise's module

## [2022062700] - RELEASED 2022-06-27

### Changed

- student profile get_exercise call

### Fixed

- Examiner name in Mentored Sessions and Practical exam reports

## [2022061600] - UNRELEASED 2022-06-16

### Added

- EASA and course tooltip

### Changed

- Logbook course button to show course shortname

## [2022061400] - UNRELEASED 2022-06-14

### Added

- Skill test PDF including exercise sections and 'Further training' section in PDF form

### Changed

- Subscriber exercises to execlude skill test assignments other than the main one

## [2022060900] - UNRELEASED 2002-06-09

### Added

- recommendation letter PDF
- link to show after endorsement
- only endorser can change toggle
- lock endorsement option once graded

## [2022060600] - UNRELEASED 2002-06-06

### Added

- filter to show active, on-hold, and suspended students in the progression table
- modal_actions amd to register listenersa and handle additional logentry modal form actions

### Changed

- reorganized listeners in booking and booking_actions
- took out _active_ from student, instructor, and participant methods made into a filter parameter

## [2022060600] - UNRELEASED 2002-06-06

### Added

- Skill test exercise course custom field for graduation qualification (install.php)
- Examiners group to subscriber object to implement skill exam and assessment restrictions
- is_examiner() method to check if an instructor has the examiner role for the skill test
- shading to completed sessions in the interim page
- Skill test report restriction requiring the skill test to be graded

### Changed

- removed Override button from the booking interim page
- Interim page to show exercise Skill test for examiners only
- Gray out graded sessions in interim page
- Disabled grading in progression list for exercises requiring file submissions but no submission exists

### Fixed

- Participant is_active to lookup active enrolled courses from enrollib
- install.php shortname for trainingtype

## [2022052700] - UNRELEASED 2022-05-27

### Added

- require_capability to all base php accessible through base URLs
- allow manager role access to availabilityview, logbookview, and studentnotification
- view student logbook
- theory exam report
- practical exam report if the student is qualified (finished last lesson)
- mentored sessions report

### Changed

- get_student_quizes_grades to use quiz_attempts instead of quiz_grades

## [2022052000] - UNRELEASED 2022-05-08

### Added

- student profile page
- endorse, suspended, on hold, keep active, and restriction override handling in student profile
- set user preference handling
- webservices to handle user preferences, group membership, and enrolment status handling (suspend true/false)

### Changed

- get_active_students to get_students to include suspended students in participant_vault
- get_active_participant to get_participant to include suspended students in participant_vault

## [2022050800] - RELEASED 2022-05-08

### Added

- Next exercise if not passed defaults to the correct exercise during booking confirmation

### Changed

- get_next_exercise to just get_exercise and passing next vs current parameter

## [2022042800] - UNRELEASED 2022-04-28

### Added

- get/set preferences for user in Student object
- 'Objective not met' handling for progressing sessions

## [2022021100] - RELEASED 2022-02-11

### Changed

- Keep Active for suspension as well

## [2022020200] - RELEASED 2022-02-02

### Changed

- handling of student next allowed session date to fallback to last graded then enrolled date

## [2022020101] - RELEASED 2022-02-01

### Added

- Student posting overdue notification sent 10 days after not being active in posting or completing lessons

### Changed

- mdl_local_booking_logbooks2 to mdl_local_booking_logbooks and dropped old table, reflected in logbook_vault

## [2022020100] - RELEASED 2022-02-01 hotfix

### Fixed

- included course id in active participants subquery

## [2022013100] - RELEASED 2022-01-31

### Fixed

- Wait days to ignore exam attempts

### Changed

- view and action parameters handling excluded from URL

## [2022012505] - RELEASED 2022-01-25

### Added

- forced display of hidden course fields
- engine type look up of default aircraft in integration mode

## [2022012504] - RELEASED 2022-01-25 - hotfix4

### Fixed

- cron suspension using onhold period instead

## [2022012503] - RELEASED 2022-01-25 - hotfix3

### Fixed

- is_active always returning false

## [2022012502] - RELEASED 2022-01-25 - hotfix2

### Fixed

- Hiding navigation from course suspended participants

## [2022012501] - RELEASED 2022-01-25 - hotfix

### Fixed

- Handling of null student in get_next_allowed_session_date() navigation

## [2022012500] - RELEASED 2022-01-25 - hotfix

### Fixed

- Handling of no students in get_active_students()

## [2022012400] - RELEASED 2022-01-24

### Fixed

- Save button status showing inactive in Availability posting by student after slot selection
- Inability to enter a logbook entry without a PIREP

### Added

- Course short name prefix in logbook entry
- Subscriber to global $COURSE

### Changed

- Restrictions and instructor notifications from plugin settings to course custom fields
- References to subscriber to map to $COURSE->subscriber
- Prevent placing On-hold for students that completed lessons and have availability

## [2022012201] - RELEASED 2022-01-22

### Fixed

- Ignore null values retrieved get_pireps

## [2022012200] - UNRELEASED 2022-01-22

### Fixed

- Dual time not showing in logbook course format for students
- Hide ground time in logbook course format for students in solo flights
- PICUS time not being recorded in multicrew link check

### Added

- Exercise info in the Remarks in dual training flights in EASA format

### Changed

- EASA format to include logbook entries from all courses

## [2022012101] - UNRELEASED 2022-01-21

### Fixed

- logentry dual time not being persisted

### Added

- examiner time to course format logbook

## [2022012100] - UNRELEASED 2022-01-21

### Fixed

- get_active_students to include courseid in the groups lookup
- logentry missing elements in exporter

## [2022012000] - UNRELEASED 2022-01-20

### Changed

- session time to ground time everywhere
- logic to show/hide elements on modal_logboo_form
- flight type (training/solo/check) handing everywhere
- separated landings for each of the instructor and student in logentries

## [2022011700] - UNRELEASED 2022-01-17

### Fixed

- getpirep with invalid string exception
- Non-numeric pirep value verification prior to lookup

## [2022011601] - UNRELEASED 2022-01-16

### Fixed

- Average time division by zero

### Added

- Integrated PIREP label for instructors
- solo flight toggle logic

## [2022011600] - UNRELEASED 2022-01-16

### Changed

- logentry from logentry summary form
- PICUS column in table structure and mvc
- multicrew operations
- PIREP integration with BAV_MANAGEMENT
- new logentry modal to handle PIREP lookup and defaults

## [2022010201] - UNRELEASED 2022-01-02

### Fixed

- handling of last exercise in a course

### Added

- subscriber to auto create 'OnHold' and 'Inactive Instructors' groups
- subscriber training type custom field ('Dual'/'Multicrew')

### Changed

- Calendar logo formatting in notification emails
- EASA format logbook table structure
- Model, controller, and view of logbook and logentry
- Logbook entry quickform to reflect EASA log format for both Dual and Multicrew training
- Refactored student and session exporters

## [2021121701] - RELEASED 2021-12-17 - hotfix

### Fixed

- cron task placing students on-hold and suspended.

## [2021121700] - RELEASED 2021-12-17

### Fixed

- cron task on-hold and suspension date evaluation of timestamp <= today.

### Changed

- made logbook display course specific logbook entries.

## [2021121600] - RELEASED 2021-12-16 - hotfix

### Fixed

- get_active_students to exclude groups 'OnHold' OR 'Graduate' not AND.

## [2021121500] - RELEASED 2021-12-15

### Changed

- Google branding reformatting in email notifications body.
- Integrated calendar links excluded from calendar event body.
- Removed 'Graduates' from get_active_instructors with $includeonhold setting.

## [2021121102] - RELEASED 2021-12-11 - hotfix

### Fixed

- Distinct instructors in get_active_instructors

## [2021121101] - UNRELEASED 2021-12-11 - hotfix

### Fixed

- Error in 'Session booking' page when instructor has a student in the On-Hold or Graduates groups

## [2021121100] - RELEASED 2021-12-11

### Fixed

- Logbook summary popup Feedback page to redirect to the correct student regardless of set submission filter

## [2021121001] - UNRELEASED 2021-12-10

### Changed

- Further took out additional unncessary Google scope to create a Google calendar event.

## [2021121000] - RELEASED 2021-12-10

### Changed

- Took out unncessary Google scopes to create a Google calendar event.

## [1.01.107] - RELEASED 2021-12-07

### Fixed

- 'My availaiblity' navigation link highlight for students.

### Changed

- Next allowed session date to start at midnight to eliminate descripancies each time the function is called.
- Started using Moodle versioning scheme yyyymmddxx.

## [1.00.106] - RELEASED 2021-12-07 - hotfix

### Fixed

- booking_actions incorrect course parameter name passed.

## [1.00.105] - UNRELEASED 2021-12-07 - hotfix

### Fixed

- 'View everyone's availability' link permissions due to incorrect course parameter name passed.

## [1.00.104] - RELEASED 2021-12-06 - hotfix

### Fixed

- Course context not passed by interim and confirm pages to Availability calendar page resulting in permissions denied.

## [1.00.103] - RELEASED 2021-12-06

### Added

- Instructors Logbook. Data for instructors' logbook migrated from students' logbook entries.

### Changed

- Logbook cards ordering by latest date vs course sections.
- Logbook entry New Entry session date to date-time and default to booked session vs graded date.

## [1.00.102] - RELEASED 2021-12-05

### Fixed

- Navigation bar highlight selection for My logbook, My availability, and Session booking links

### Changed

- Wait Days tooltip exlaining source of wait days in the Session Booking page.

## [1.00.101] - RELEASED 2021-12-04

### Changed

- recency days looking at last and before last slots, then reverting to graded then enrolled dates in unavailable.

## [1.00.100] - RELEASED 2021-12-04

### Changed

- slot count to take into consideration current slots only.

## [1.00.99] - RELEASED 2021-12-04

### Added

- past data cutoff to excluded returning student's past grades from before 365 days.

### Changed

- removed session date from the session tooltip if doesn't exist.

## [1.00.98] - RELEASED 2021-12-04

### Fixed

- students without simulator record but other user custom field data are not showing.

### Added

- booked slot endtime date to clarify confusion with graded date indicating recency days since last session.

## [1.00.97] - RELEASED 2021-12-03 - hotfix

### Fixed

- sorttype is not passed from webservices, now defaulting to empty.

## [1.00.96] - RELEASED 2021-12-03 - hotfix

### Fixed

- next exercise id in next action returning null in the last exercise in the course (boungry condition).
- sorting active students.

## [1.00.95] - RELEASED 2021-12-02

### Fixed

- weekofyear, nextweek, and previousweek evaluating to string where response expected value is int.

### Added

- sorting student progression list by score or availability. Availability sorting splits into 3 segments each sorted by recency then posts.

## [1.00.94] - RELEASED 2021-11-30 - hotfix

### Fixed

- Issue[#23]: Error during add/delete a logentry.

## [1.00.93] - RELEASED 2021-11-29

### Added

- ‘No post’ indication for students with no availability posted for sessions ready to book.

## [1.00.92] - RELEASED 2021-11-27

### Fixed

- Back button on Booking confirmation page
- Logbook entry addition in Booking confirmation page

## [1.00.91] - UNRELEASED 2021-11-20

### Fixed

- Newly enrolled students not showing in 'Students progression'

### Added

- interim booking page to force booked exercise and override restriction
- inactive group to exlude from active instructors list
- add an additional new logbook entry for the same exercise

### Changed

- instructors without custom field data (i.e. simulator) to show in active instructors list

## [0.20.90] BETA - RELEASED 2021-11-16

### Changed

- analytics to select slots that are not a booking slot
- analytics completion lessons to select unique completed lessons

## [0.20.89] BETA - RELEASED 2021-11-15

### Changed

- days since last session in Student Progression table to check for last graded first vs enrolled date where possible

## [0.20.88] BETA - RELEASED 2021-11-12

### Fixed

- instructor permission to view availability posting for all students
- student booking session next exercise sql

## [0.20.87] BETA - RELEASED 2021-11-11

### Fixed

- legacy grades falsely marking sessions completed (grade>0)

## [0.20.86] BETA - UNRELEASED 2021-11-11

### Fixed

- cron task CC senior instructors thowing permissions exception

## [0.20.85] BETA - RELEASED 2021-11-09 - hotfix

### Fixed

- 'permission denied' in moodle calendar for session booking by instructors

### Added

- availability posting mouse drag for multiple slots vs mouse click only

## [0.20.84] BETA - RELEASED 2021-11-07

### Fixed

- cron task date conversions and formatting

### Changed

- changed version for release

## [0.20.83] BETA - UNRELEASED 2021-11-05

### Fixed

- iCal description formatting to exclude html (iCal only supports text descriptions)
- cancellation error: access to protected property 'slot'

### Added

- is_member_of method to participant class
- notification message to Availability postsing view when a student is on-hold
- error handling of confirmation without a booking
- more descriptive messages for cron job log output
- senior instructors copy on instructor inactivity

### Changed

- instructor inactive notification to exclude newly enrolled instructors

## [0.20.82] BETA - UNRELEASED 2021-11-04

### Fixed

- Moodle calendar event duration
- integration with Google and Outlook Live calendars

### Added

- session booked confirmation email html formatting and icons for Google, Outlook Live, and iCal links

### Changed

- Moodle calendar event from a course event to a user event
- Tooltip formatting in Student progression view for Booked, Tentative, and Graded sessions

## [0.20.81] BETA - UNRELEASED 2021-11-01

### Added

- integration with Google and Outlook Live calendars
- links in booking confirmation emails to student and instructor to download calendar event ics file
- links in booking confirmation emails add booked session to Google and Outlook Live session calendars

### Changed

- session date tooltip in student progression to include time in zulu

## [0.14.80] BETA - UNRELEASED 2021-10-28

### Changed

- moved delegated transaction management (start, commit, and rollback) to vaults
- Fontawesome icon formatting
- css slot and session buttons formatting

## [0.13.79] BETA - UNRELEASED 2021-10-25

### Changed

- css classes and template style cleanup
- Fontawesome moved to lib and cleaned up all button icons
- Availability calendar to display current date (week) even if posted sessions are in the past
- Moved Priority object to Student accessed through get_priority()
- Moved get_next_allowed_session_date from lib to Student class
- completed_lessons to has_completed_lesson method
- Email notification edits matching Assignment Moodle notifications formatting
- vault methods made static

## [0.12.78] BETA - RELEASED 2021-10-19 - hotfix

### Fixed

- [BR00272]: redirect to assignment for grading does not bring the correct student if a filter is applied in ‘View Submissions’.

### Added

- assign.php to clear any preset filters for the assignment feedback submission and redirects to the feedback Moodle page

### Changed

- Grade button link to assign.php vs direct feedback Moodle page

## [0.12.77] BETA - RELEASED 2021-10-18 - hotfix

### Changed

- get_student_assignment_grades to retrieve unique rows
- version 2021101801

## [0.12.76] BETA - UNRELEASED 2021-10-18

### Fixed

- Lesson completion section passed correction

## [0.12.75] BETA - UNRELEASED 2021-10-17

### Fixed

- Lesson completion based on section order not id

### Added

- Fontawesome checkmark on completed quizes/exams
- Average wait days for student sessions

### Changed

- version 2021101700
- changed ATO exercise_titles to be visible to Teachers only to avoid from titles displaying in Course listing from Site home
- Analytics to retrieve session recency for priority wait days from booked slots endtime vs session booked timemodified
- lessons completed logic

### Fixed

- Clear in availability posting doesn't enable Save button and save doesn't delete slots for that week

## [0.12.74] BETA - RELEASED 2021-10-11 - hotfix

### Changed

- version 2021101100
- changed Book button background to green/off-green

### Fixed

- context id not being passed in Log entry edit view modal form resulting in WS error
- Unable to post availability

## [0.12.73] BETA - RELEASED 2021-10-10 - hotfix

### Added

- subscriber_vault_interface
- subscriber_vault

### Changed

- version 2021101000
- subscriber data access SQL to vaults

### Fixed

- context id not being passed correctly to js after last hotfix

## [0.12.72] BETA - RELEASED 2021-10-07 - hotfix

### Changed

- reverted repository.js timeout

## [0.10.71] BETA - RELEASED 2021-10-03 - hotfix

### Fixed

- Logbook entry summary modal edit

## [0.10.63-70] BETA - RELEASED 2021-10-03 - hotfix

### Fixed

- Unhandled promise exception fix testing & debugging
- ajax timeout increase post 502 fix
- tooltips is not working. Fixed after upgrade
- Inputmask is not working. Fixed after upgrade
- List role names from defined at Course level rather than the system level
- Instructors should not be restricted to enter booking apart from passed date
- Student slots are not being purged after a booking is made
- 502 Bad Gateway: Unhandled Promise rejection when saving a logbook entry: server restart fixed it

## [0.10.62] BETA - RELEASED 2021-10-02

### Changed

- JS template content refresh for booking

## [0.10.61] BETA - RELEASED 2021-10-02

### Changed

- day restriction to 5
- excluded instructors from restricted view

## [0.10.60] BETA - RELEASED 2021-10-01

### Changed

- Availability calendar view text lang edits

## [0.10.59] BETA - RELEASED 2021-10-01

### Changed

- EN text lang edits

## [0.10.58] BETA - RELEASED 2021-10-01

### Added

- BAV api phps

## [0.10.57] BETA - RELEASED 2021-09-29

### Changed

- instructor participation role from system role to course named role

## [0.10.56] BETA - RELEASED 2021-09-29

### Fixed

- course subscriber section sorting bug fix

## [0.10.55] BETA - RELEASED 2021-09-29

### Fixed

- section sequence bug fix

### Changed

- participant methods for instructors bookings and student slots to remove reliance on vault actions outside of entity classes.
- Move all DB to vaults only
- All ’new booking(…)’ and ’new slot(…)’ returned objects should have no direct access to protected properties only getters and setters
- All entities returning data from vaults should return objects
- use parameters instead of concatenated values (see get_senior_instructors in participant_vault)

## [0.10.54] BETA - RELEASED 2021-09-29

### Fixed

- section sorting vs exercise id sorting

### Added

- participant context to instructor participation

## [0.10.53] BETA - RELEASED 2021-09-28

### Changed

- moodle version
- Migrate common functions from lib.php to auto loaded classes:
  - all booking function, only callbacks and view functions should remain
  - get_fullusername

## [0.10.52] BETA - RELEASED 2021-09-28

### Fixed

- array explode in subscriber to preg_split
- SQL semicolon bug fixes

## [0.10.51] BETA - RELEASED 2021-09-25

### Fixed

- Logbook participant identification from user id vs student id
- availability calendar day doesn’t match the correct date
- student next restriction date is different from what the instructors sees when booking
- Availability week view lanes shows booked/tentative session over other students session, it should only do that for same user

### Changed

- Changed to BETA release after demo
- incremented BETA release minor version to 10 from ALPHA 02

## [0.02.50] ALPHA - UNRELEASED

### Added

- Logbook summary summing flight hours
- Static methods:
  - participant.php
  - \_vaults.php

### Changed

- Add Capability check in view.php, logbook.php, availability.php, and confirm.php:
  - require_capability(‘local/availability:view', $context) per subscribed course
  - require_capability(‘local/booking:view', $context) per subscribed course
  - require_capability(‘local/logbook:view', $context) per subscribed course

## [0.02.49] ALPHA - UNRELEASED

### Fixed

- db/access.php params bug fix
- Book button next action exercise skips past exam even if the exercise is prior to the exam (get_next_exercise)
- session confirmation is not going out to the instructor

### Added

- access cleanup and require_capablty

### Changed

- Book/Grade text & icon align vertically center
- Optimize booking_session_exporter calls to DB

## [0.02.48] ALPHA - UNRELEASED

### Added

- Quizes sections to progression view, (i.e. Principles of flight)
- exercise name in titles

### Changed

- 4-lane minimum in availability all

## [0.02.47] ALPHA - UNRELEASED

### Added

- Logbook entry functionality

### Fixed

- Availability timeslot posting not marking properly

## [0.02.46] ALPHA - UNRELEASED

### Fixed

- Uninstall function call signture

## [0.02.45] ALPHA - UNRELEASED

### Fixed

- Uninstall of ATO course category and custom fields

## [0.02.44] ALPHA - UNRELEASED

### Fixed

- LOG_ENTRY: Log entry session date is off by one day. mform GMT and not user timezone.

### Added

- config.json for ATO name
- config.json for student posting background colors
- Added booking_view_manager and calendar_view_manager
- Uninstall of ATO course category and custom fields
  - export slots, bookings, log books, and user profile (custom_info_category, custom_info_field, custom_info_data)
  - import after testing

### Changed

- config.xml to hold ATO name
- Convert all table fields to non null values

## [0.02.43] ALPHA - UNRELEASED

### Fixed

- Logbook entry selectors fix
- slot posting selectors fix

## [0.02.42] ALPHA - UNRELEASED

### Fixed

- CRON_TASK: instructor activity booking overdue notification is being sent incorrectly (john petit) although booked a day before
- AVAIL_SLOTS: If today==end of week render next week else all week will be grayed out (change OS time to Sunday)
- AVAIL_SLOTS: Prevent from saving empty week by mistake (js Slots.length=0)
- AVAIL_SLOTS: last slot of the day endgame = 0!
- AVAIL_SLOTS: three mini calendars show table border. Separate formatting
- AVAIL_SLOTS: fix sessions and availability tooltips like the analytics ones (data-html=“true” in mustache) or {{{tooltip}}}
- LOG_ENTRY: amd.init not exported for modal_logentry_form
- LOG_ENTRY: New Log entry defaults to today not session graded date
- BOOK_GRADE: Grade button redirects to a different students. Moodle bug fixed in (3.11.2+ RELEASE - MDL-70176 mod_forum: Grading respects

### Added

- Course custom fields for course to subscribe to the plugin under ATO category
- instructor participation

### Changed

- Move plugin settings to course fields https://docs.moodle.org/dev/Custom_fields_API
  - Enable to the Session Booking plugin through a checkbox
  - Custom Exercise Titles: exercise titles for the Session Booking view as exercise column headings, comma delimited (use <br/> for line breaks)
  - If title missing use left(15) of the exercise names {{#shortentext}}
  - Default course aircraft [‘C172’, ‘P28A’] => line break in description
  - Home Airport => Add to create.php::fromicao/toicao
- Change the way cron_task is written $courseshortnames array

## [0.02.41] ALPHA - UNRELEASED

### Added

- Logbook entry form function complete

### Changed

- Pilot Logbook navigation: logbook_exporter, logbook_pilot_exporter, logbook_inst_exporter
- Fontawesome Logbook icon
- Delete logentry
- Modal form Create/Update
- Modal form display formatting
- Info icon for missing log entries
- convert formatting for some logentry data coming out (flight duration, session duration, and solo flight duration) int<=>string
- get user custom field (callsign) for the instructor

## [0.02.40] ALPHA - UNRELEASED

### Added

- Logbook entry get by id webservices
- Logbook entry save (create/update) webservices
- Logbook entry delete webservices

## [0.02.39] ALPHA - UNRELEASED

### Added

- Logbook entry form
  - mdl_local_booking_logbook table {courseid, userid, picid, sitid, PIREPs, callsign, flightime, session time, fromicao, toicao, timemodified }
  - Modal form to retrieve/save logbook entry for each session (data validation):
    - - Date
    - - Flight Time
    - - Session Time
    - PIC (pre-filled from instructor)
    - SIC (pre-filled from student)
    - PIREP
    - Flight Callsign (pre-filled from instructor)
    - From (pre-filled from default - future from course)
    - To (pre-filled from default - future from course)
    - [courseid]
    - [exerciseid]
    - [student]
- Add color coding to ‘Wait’ column cells: 2x waitdays amber, 3x waitdays red

## [0.02.32-38] ALPHA - UNRELEASED

### Fixed

- ATO install script testing & debuging

## [0.02.31] ALPHA - UNRELEASED

### Fixed

- ATO install script testing & debuging

## [0.02.30] ALPHA - UNRELEASED

### Fixed

- ATO install script

## [0.02.29] ALPHA - UNRELEASED

### Fixed

- capability access

## [0.02.28] ALPHA - UNRELEASED

### Added

- task for cron job
- install script for ATO specific information
- custom fields in install.php and check if fields already exist (Davo’s post)
  - Simulator:
  - Callsign: Instructor role only

### Changed

- Instructor book should take him to the week of the date of the first slot (if slots exist)
- Add a link to Booking in all Instructor communication
- Show next session in Assigned Trainees list in Booking

## [0.02.27] ALPHA - UNRELEASED

### Fixed

- joined Booking & Availability plugins errors

### Added

- cron tasks
- [task]: OnHold:
  - Notify student of {$a} days with no availability posted with options to post or be on hold after 2x restriction days period have passed from the date of last booking. The notification should show automatically placed on hold.
  - Notify student of being placed on hold and what to do to be active again.
  - Place student on hold 3x after restriction days had passed and no booking or availability posted
- [task]: Suspension:
  - Notify student of being suspended (unenrolled) if x days pass based on criteria:
    - x days passed without availability posting or booking
    - x days since course content access
  - Suspend after x days being on hold
- [TASK]: Instructors Inactivity:
  - Notify the instructor after X days of having not booked any session (copy course manager) noting the number of days since last booking
  - Notify every X day since last conducted session if not graded

### Changed

- capability access

## [0.02.26] ALPHA - UNRELEASED

### Added

- Add ‘Wait’ column

### Changed

- joined Booking & Availability plugins
- incremented Alpha minor version

## [0.01.25] ALPHA - UNRELEASED

### Added

- booking student priority
- Priority (sequence array that updates the activestudents list prior to exporting through a prioritize method
  - Session Recency
  - Availability marking
  - Course activity
  - Lesson completion

### Changed

- Assign students colors from 1-20 (max lanes)
- Calander table cosmetics
- Confirmation message verify showing after redirect
- Criteria for active student:
  - Not in Graduates group
  - Active enrollments
  - Grey out the Book button with a popup if the student’s ground lesson is incompleted (modules before current assignment 'Air Exercise’)

## [0.01.24] ALPHA - UNRELEASED

### Added

- active column to local_booking

## [0.01.23] ALPHA - UNRELEASED

### Added

- Bookings list for instructors
- Assigned students list for instructors
- Bookings cancellation
  - Collect instructor’s comment
  - Refresh content after
  - Delete all student slots
  - Notify student of deleted slots and the reason behind the cancellation
  - Prompt for confirmation
  - Email student
- view all students in calendar table
- excluded Students whereby they don’t show if they did not complete lessons prior to the next exercise

### Changed

- Stack slots in All Students Availability table with minimum slots
- Navigation links when booking should show the user associated with the view (user, specific user, all)
- Booked sessions not showing on student’s view
- Better slot packing

## [0.01.22] ALPHA - UNRELEASED

### Added

- booking functionality complete
- mybookings and mystudents listing
- booking notification and confirmation emails

### Changed

- calendar table formatting
- fake blocks experimentation

## [0.00.21] ALPHA - UNRELEASED

### Changed

- bookinginfo column size from 100 to 500

## [0.00.20] ALPHA - UNRELEASED

### Added

- booking vault
- messaging for email communication

### Changed

- Save booking including ref slot

## [0.00.19] ALPHA - UNRELEASED

### Added

- local_booking table structure
- booking vault

## [0.00.18] ALPHA - UNRELEASED

### Changed

- booking and availability plugins

## [0.00.17] ALPHA - UNRELEASED

### Fixed

- [UI]: Exercise short names, figure out a short name for exercises

### Added

- exerciseid column
- Open booking where an instructor can pick any time for a booking

### Changed

- bookingstatus to slotstatus column
- Action selection between Grade & Book with proper links
- Table formatting as Users list in Moodle

## [0.00.16] ALPHA - UNRELEASED

### Fixed

- Delete availability after session being graded

### Added

- save booking webservice
- listen to grade event: grading event trigger code is in event set_module_viewed)
- Show loading and icon on page load until ajax completes (review mod/assign/view and grading_panel.js)
- Booking tooltip in (availability week_detail.mustache)
- Availability of all students in a single view

## [0.00.15] ALPHA - UNRELEASED

### Added

- Booking student availability weekly view to book slots
  - Availability handling of userid (including next/prev week navigation links ws)
  - Availability footer action (Save vs Save Booking w/ redirect) webservice
  - Booking if slot not available
  - Availability check to (overwrite existing booking?)
  - After booking is made refreshcontent
  - tooltips for booking grid
  - event handling after grading event
  - email notifications formatting
  - confirm booking (notify instructor)
- Criteria
  - Student role for the course/context
  - Not suspended
  - Not on-hold

### Changed

- show only students in the progression page based on role enroll as students for that course/context
- Progress table w/ cell color coding

## [0.00.14] ALPHA - UNRELEASED

### Fixed

- .git fix

### Added

- add data for student and instructor users
- add data for assignment grades (suspend Mike)

### Changed

- upgraded moodle
- access changes to Availabilty
- Prevent a student from marking sessions until ’n’ days have passed (settings, istoday) - set view to that period so user doesn’t have to go next

## [0.00.13] ALPHA - UNRELEASED

### Fixed

- Copy/Paste/Clear not to reflect booked slots

### Added

- capability access
- add/configure plugin capabilities (\local\readme.txt)

## [0.00.12] ALPHA - UNRELEASED

### Added

- initial booking functionality
- initial student progression functionality

## [0.00.11] ALPHA - UNRELEASED

### Added

- booked slots implementation
- slot actions js
- bookedslots column char(50)
- show notification of the completed action

### Changed

- week and year column length
- review plugin required interdependencies
- booked sessions styling

## [0.00.10] ALPHA - UNRELEASED

### Fixed

- Paste losing blue on next weeks
- Week slot availability incorrect in next week and after

### Added

- fully functional availability posting

## [0.00.9] ALPHA - UNRELEASED

### Added

- save form
- webservice for Clear availability posting action
- show notification of the completed action

## Changed

- Copy function JS loaded to memory object
- moved Save button to footer
- Paste button manipulation and post pasted slots of memory object

## [0.00.8] ALPHA - UNRELEASED

### Changed

- availability posting web services
- move buttons to footer
- loading overlay & icon and manipulate footer buttons

## [0.00.7] ALPHA - UNRELEASED

### Fixed

- local time and zulu time calculation

### Added

- AMD\JS modules includes slots array for load & save
- web services module for availability posting
- save slot service to repository.js
- Save/Copy Last Week/Reset services to the db
- Font awesome for buttons
- RefreshContent JS listeners to call web services for navigation

## [0.00.6] ALPHA - UNRELEASED

### Fixed

- local time and zulu time calculation

### Added

- added course id to local_availability_slots table

### Changed

- week_detail_availability mustache formatting change

## [0.00.5] ALPHA - UNRELEASED

### Added

- user, week, year indexes

## [0.00.4] ALPHA - UNRELEASED

### Added

- persistent and database interaction to slot_vault
- slot marked variable to week_day_exporter
- weekday & weekslot to week_exporter

### Changed

- slotunavailable to slotavailable variable

## [0.00.3] ALPHA - UNRELEASED

### Added

- slot vault implementation for data access
- day exporter marked tag output

### Changed

- local_availability_slots slottimestamp to starttime & endtime
- added instructorid column
- timestamp and marked mustache tags
- day to slot timestamp label in src calendar.js
- slots table

## [0.00.2] ALPHA - UNRELEASED

### Added

- local_availability_slots table structure

### Changed

- timeslots field change
- slot available and slot selected background color change

## [0.00.1] ALPHA - UNRELEASED

### Added

- core_calendar-event_summary
- external lib services
- lib navigation
- settings.DayStartHour & .DayEndHour (get/set_config)
- implemented work_exporter including get_hours(), {{hours}}, table formatting, and get_local_hours() (GMT-to-Profile timezone)
- base style.css
- base view

Initial start of a changelog

See commits for previous history.
