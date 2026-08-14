# Change log

## [2026081403] - RELEASED 2026-08-14

### Added

- PHPUnit copies of development courses 10 (PPL), 33 (A320 Line Training), and 52 (SEIR), with an instructor and student enrolled on each

### Fixed

- `get_next_exercise()` no longer assumes a subscribed course already has assignment/quiz modules during enrolment
- cancelling a booking no longer reports failure when the cancellation email cannot be sent
- Dual logentry form no longer crashes when PIREP lookup is disabled
- Dual logentry populate reads optional P1/P2 ids that Moodle omits from frozen selects
- checklist `get_items()` handles a checklist that has not preloaded its items

## [2026081402] - RELEASED 2026-08-14

### Added

- PHPUnit coverage for `save_slots`, `save_booking`, `cancel_booking` (including no-show), `save_logentry`, and `update_checklist_item`

### Fixed

- Moodle calendar events for booked sessions used a SCORM visibility check against the exercise name; they now use the assignment course module
- Dual logentry populate reading optional form fields that are absent on PHP 8

## [2026081401] - RELEASED 2026-08-14

### Security

- enforce `validate_context()` and `require_capability()` in all AJAX external functions (capabilities in `db/services.php` are not auto-enforced)
- require login and capability checks on `calendar.php` and `index.php`; stop building calendar events from raw `$_GET`
- require sesskey for booking confirmation, certification, and force course completion
- restrict booking confirmation to the booked student
- whitelist group names, user preferences, and progress-flag keys on write web services
- prevent users from writing another user's preferences
- parameterize logbook link updates and external PIREP lookups to block SQL injection
- whitelist progress-table field names before interpolating them into SQL
- escape the post-grading JavaScript redirect URL

### Fixed

- linked logentry save calling `get_linkedpirep()` instead of `set_linkedpirep()`, which fatals on PHP 8
- undefined `$capability` when a student tries to open another pilot's logbook
- `json_validate()` usage that requires PHP 8.3 while Moodle 4.5 supports PHP 8.1
- empty/invalid progress-flag JSON throwing when a new student has no flags yet
- `get_booking_config()` concatenating strings with `+` and echoing exceptions
- `update_checklist_item` service missing `loginrequired` / `capabilities`
- missing `exerciseid` in PIREP parameter validation

### Changed

- bulk-load checklist check records instead of querying per item
- cache course subscription status in Moodle application cache (Redis/MUC)
- confirm booking from email via an intermediate confirmation page (CSRF)

### Added

- `db/caches.php` definition for subscribed-course lookups

## [2026081400] - RELEASED 2026-08-14

### Added

- teacher comment column on the checklist grading page, matching the Checklist plugin comment field

### Changed

- Grade action and post-booking redirect go to checklist grading / student stats only when the course is Multicrew and has at least one Checklist activity

### Fixed

- checklist grading comment column not rendering because empty() on magic checklist properties treated enabled teacher comments as off
- has_checklists detecting Checklist course modules that the previous join could miss

## [2026070900] - RELEASED 2026-07-09

### Fixed

- exception handling for newly enrolled students with a quiz vs assignment grade as the first grade, negating their new enrollee designation

## [2026052700] - RELEASED 2026-05-27

### Fixed

- exception handling for unenrolled student in the instructor dashboard ‘My bookings’ section
- when booking a session out of sequence, the grade action links to next assignment grading versus whichever assignment was booked
- sessionid in logentry edit/new popup form not being passed from calling page
- grade action pointing to the next air-exercise/assignment and not the exercise that was booked

### Added

- student summary statistics page for multi-crew courses
- itemid field in the sessions table and updated with grade or grade attempt itemid when a grade is submitted through the event observer
- pilotrole field to logbooks table to record multi-crew pilot role (pilot flying PF vs pilot monitoring PM)
- pilot role option in edit/new logentry popup for multi-crew log entries only

### Changed

- student profile to include student's Summary Report for multi-crew courses

## [2026032701] - RELEASED 2026-03-27

### Fixed

- increased mdl_local_booking_progress field size to 1000 to accommodate longer JSON strings

## [2026032700] - RELEASED 2026-03-27

### Fixed

- checklist progress % to update when check items are toggled. click response to include total clicked to display updated progress (update js & server-side service)
- on-hold period cron task to consider all slot posted not just active ones

### Added

- rename Score to Activity score in the student profile
- Add last posted, on-hold, and suspend expected dates to student profile
- Checklist inline uo progress update

## [2026031600] - RELEASED 2026-03-16

### Fixed

- exception on null when student has no completed lessons

## [2026032500] - RELEASED 2026-03-25

### Added

- checklist items sign-off page after grading for courses with checklists
- student profile page checklist progress for courses with checklists

## [2026030701] - RELEASED 2026-03-07

### Fixed

- 'no posts' flag in session booking dashboard is offset by one table cell for newly joined pilots
- dashboard exercise titles to allow `<br />` tags
- incomplete lesson modules although the student did complete last lesson module required. temporary forced update completion implemented
- subscriber $mods[$exerciseidx + $offset] edge cases exception handling

## [2026011900] - RELEASED 2026-01-19

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
