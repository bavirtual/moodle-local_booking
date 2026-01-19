# Change log

## [2026011900] - RELEASED 2025-11-20

### Fixed

- cron_task clone last session date when null in process_student_inactivity_notifications

## [2025112000] - RELEASED 2025-11-20

### Fixed

- incorrectly updating last session only when student passes the session
- graduates and suspended dashboard list sorted in reverse-chronological order
- noposts tag not showing for Progressive sessions

### Changed

- student on-hold and suspension logic whereby status changes based on inactivity, which is determined by the last or most recent event (last slot posted, last session booked, last grade received, or enrolment date for new joiners)
- students can change their status from on-hold to active by submitting at least 1 availability post

## [2025093000] - RELEASED 2025-09-30

### Fixed

- certification process badge award exception
- handles aircraft ICAO mismatch between submitted PIREP the training aircraft ICAO from the Session Booming section of the course Settings.
- session conflict incorrect conflict when a tentative session is showing  on the same week for the same student

### Changed

- cert. button to prompt for confirmation
- fallback to config.php db connection values when plugin settings db connection configs are missing

## [2025090200] - RELEASED 2025-09-02

### Fixed

- invalidresponse exception when lastbooking is not there

## [2025081500] - RELEASED 2025-08-15

### Fixed

- lesson completion not updating student's progress

## [2025070301] - RELEASED 2025-07-03

### Fixed

- newly joined student availability exception
- newly joined active status

## [2025060401] - RELEASED 2025-06-04

### Fixed

- user enrolment hook theme already set fix

## [2025051000] - RELEASED 2025-05-31

### Fixed

- course participant profile enrol date
- removed missing enrolment observer events from db/events

### Changed

- iCal calendar icon

## [2025051000] - RELEASED 2025-05-10

### Fixed

- Google calendar icon oversized
- session booking confirmation missing date tag

## [2025010401] - RELEASED 2025-01-04

### Fixed

- Wait Days column showing incorrect data
- instructor participation last session showing no grading records
- no show exception
- incorrect completed exercises in student profile

### Changed

- mdl_local_booking_stats to mdl_local_booking_progress to be more descriptive of its function, and field notifyflags to progressflags
- separate db connection from lookups configurations in JSON config string
- removed score sorting
- get_progress_flag/set_progress_flag key/value pair handling of json string
- moved flags formerly used Moodle user_preference to progress flags including endorsement, min period override, and notifications
- plugin user preferences to use to LOCAL_BOOKING_USERPERFPREFIX.-<courseid>-<pref_name>
- removed prioritization from plugin settings

## [2024122500] - RELEASED 2024-12-25

### Fixed
- calendar week days (day of month)
- calendar event title in Moodle

## [2024122400] - RELEASED 2024-12-24

### Fixed

- students were incorrectly shown as having active slots due to future booked slots
- notification preferences returning null
- handling of disabled/hidden exercises
- peerage missing in student progression view
- subscribed course validation in secondary navigation
- booking cancellation loading overlay while processing

### Changed

- reworked suspension cron to evaluate last graded vs last booking in the sequence

## [2024121900] - RELEASED 2024-12-19

### Fixed

- add log entry from logbook throws an AJAX exception on passed exercises
- user search combo box on booking dashboard view is missing a clear link

### Added

- moodle 4.3 style action bar as tertiary navigation for booking dashboard, logbook, booking confirmation, and availability views
- dirty calendar handling
- sticky footer with filter student drop down, per page results drop down, and a paging bar
- standard warning modal w/ multiple buttons formats and took out all js alerts

### Changed

- availability posting refactor to improve promise handling
- require version support for Moodle 4.0 to support new action bar

## [2024121400] - RELEASED 2024-12-14

### Fixed

- participant fullname
- cron job evaluating suspended students

## [2024121000] - RELEASED 2024-12-10

### Fixed

- logentry cancellation exception
- null current & next exercise handling
- nighttime missing error

### Changed

- refactored participant vault sql queries
- profile administration section verbs

## [2024120700] - RELEASED 2024-12-07

### Changed

- refactored external api services and output exporters
- handling of current and next exercises

## [2024120500] - RELEASED 2024-12-05

### Fixed

- confirmed sessions showing tentative
- empty subscriber enabled value

## [2024120300] - RELEASED 2024-12-03

### Fixed

- booking confirm page duplicate entries

## [2024120100] - RELEASED 2024-12-01

### Fixed

- vatsimcid references replace with vatsimid
- active booking edge cases (first/last bookings)

### Added

- new user search combo box

### Changed

- page headings to always show course title
- removed author email

## [2024112700] - RELEASED 2024-11-27

### Fixed

- wait days reverting to enrolment date

### Added

- Github Actions workflow yml

### Changed

- current and next exercises from booking recent sessions

## [2024111201] - RELEASED 2024-11-12

### Added

- new enrollment and unenrolment hooks
- total active students count on students progression page header

### Changed

- last_session_date to sample last 2 session records comparing future vs past sessions
- progression view student sequence on multiple pages

## [2024110800] - RELEASED 2024-11-08

### Added

- moodle 4.5 compatility and upgrade notes
- updated messages to MESSAGE_DEFAULT_ENABLED
- rounded table style and progression views
- intructor participation session date column

## [2024110301] - RELEASED 2024-11-03

### Fixed

- changed last session booked date to revert to slot starttime when applicable
- error handling for absence of sessions
- instructor participation sort order

### Changed

- reintroduced graduates group

## [2024110200] - RELEASED 2024-11-02

### Fixed

- progression grid to include visible assignment modules (air exercises)

## [2024102900] - RELEASED 2024-10-29

### Fixed

- student profile w/o stats exception fix

## [2024102801] - RELEASED 2024-10-28

### Fixed

- course specific student stats (left outer join)
- graduation notification config string vs int

## [2024102600] - RELEASED 2024-10-26

### Fixed

- student view menu not showing 'My availability' option for posting
- newly joined students not showing in the instructor dashboard

### Changed

- removed user enrolment triggered event to create student stats record

## [2024101900] - RELEASED 2024-10-19

### Fixed

- external api filter (passed==null)
- sort order: completion, posts, booked, then wait days
- recency days and recency info logic
- current & next exercise ids logic for new enrollment (cid=0, nid=1st)

### Added

- notify field to store student scheduled notification flags in JSON format
- graduation notification options to send to all active participants or same group participants
- cron job to send graduation notifications basde on 'graduation_notification' flag (0=course admins and examiner, 1=active users, 2=same group)
- new attempt added for failed attempts when grading

### Changed

- droped activeposts field from student statistics, when slot date passes the count is no longer valid
- course completion & graduation retrieved from completionlib is_course_complete()
- removed GRADUATES group references, group and groupmember records
- force completion part of graduation processing
- removed sort_student function from bookings_exporter
- instructor participation 'Elapsed days' to use last booked session vs last graded
- progression grid flag is indicated by a blue strip for students to be graded and a black stripe to be graduated
- moved notifications flag from user preferences to stats
- extended logbook entry remarks to VARCHAR(1000) from VARCHAR(500)

## [202409xxxx] - RELEASED 2024-09-xx

### Fixed

- search dashboard students
- overlay on [Save booking] whlie loading the dashboard
- force course completion in profile to exclude legacy enrolments

### Fixed

- graduates filter exception
- my bookings not showing after saving a logentry or canceling a booking

### Added

- center pagination to the middle of the grid not the page
- active/graduated students evaluation
- calendar week days (day of month)
- calendar event title in Moodle

## [2024122400] - RELEASED 2024-12-24

### Fixed

- students were incorrectly shown as having active slots due to future booked slots
- notification preferences returning null
- handling of disabled/hidden exercises
- peerage missing in student progression view
- subscribed course validation in secondary navigation
- booking cancellation loading overlay while processing

### Changed

- reworked suspension cron to evaluate last graded vs last booking in the sequence

## [2024121900] - RELEASED 2024-12-19

### Fixed

- add log entry from logbook throws an AJAX exception on passed exercises
- user search combo box on booking dashboard view is missing a clear link

### Added

- moodle 4.3 style action bar as tertiary navigation for booking dashboard, logbook, booking confirmation, and availability views
- dirty calendar handling
- sticky footer with filter student drop down, per page results drop down, and a paging bar
- standard warning modal w/ multiple buttons formats and took out all js alerts

### Changed

- availability posting refactor to improve promise handling
- require version support for Moodle 4.0 to support new action bar

## [2024121400] - RELEASED 2024-12-14

### Fixed

- participant fullname
- cron job evaluating suspended students

## [2024121000] - RELEASED 2024-12-10

### Fixed

- logentry cancellation exception
- null current & next exercise handling
- nighttime missing error

### Changed

- refactored participant vault sql queries
- profile administration section verbs

## [2024120700] - RELEASED 2024-12-07

### Changed

- refactored external api services and output exporters
- handling of current and next exercises

## [2024120500] - RELEASED 2024-12-05

### Fixed

- confirmed sessions showing tentative
- empty subscriber enabled value

## [2024120300] - RELEASED 2024-12-03

### Fixed

- booking confirm page duplicate entries

## [2024120100] - RELEASED 2024-12-01

### Fixed

- vatsimcid references replace with vatsimid
- active booking edge cases (first/last bookings)

### Added

- new user search combo box

### Changed

- page headings to always show course title
- removed author email

## [2024112700] - RELEASED 2024-11-27

### Fixed

- wait days reverting to enrolment date

### Added

- Github Actions workflow yml

### Changed

- current and next exercises from booking recent sessions

## [2024111201] - RELEASED 2024-11-12

### Added

- new enrollment and unenrolment hooks
- total active students count on students progression page header

### Changed

- last_session_date to sample last 2 session records comparing future vs past sessions
- progression view student sequence on multiple pages

## [2024110800] - RELEASED 2024-11-08

### Added

- moodle 4.5 compatility and upgrade notes
- updated messages to MESSAGE_DEFAULT_ENABLED
- rounded table style and progression views
- intructor participation session date column

## [2024110301] - RELEASED 2024-11-03

### Fixed

- changed last session booked date to revert to slot starttime when applicable
- error handling for absence of sessions
- instructor participation sort order

### Changed

- reintroduced graduates group

## [2024110200] - RELEASED 2024-11-02

### Fixed

- progression grid to include visible assignment modules (air exercises)
