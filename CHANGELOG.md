# Change log

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