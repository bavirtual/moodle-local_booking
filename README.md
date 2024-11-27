# Session booking

Copyright (c) 2021 BAVirtual.co.uk © 2021 Licensed under the GNU GPL v3 or later license (<http://www.gnu.org/copyleft/gpl.html>)

Session Booking is a [Moodle](https://moodle.org/) plugin that allows Authorized Training Organizations (ATO) manage instructor-led training sessions. It allows students to post their availability and instructors to book sessions according to student availability.

Session Booking provides a workflow for posting availability slots, booking against these slots, then grading the exercise. The plugin also allows course managers in assigning students to instructors.

## Highlights

- Allows instructors to book students based on their recorded availability
- Allows students to record and manage their weekly availability for sessions
- Allows course managers in assigning students to instructors
- Tracks instructor participation in conducting student sessions
- Tracks flight data and tracks student pilot log book entries
- Manages email communication between students and instructors
- Provides a dashboard for instructors and course administrators to visualize student progression throughout the course
- Provides a custom student prioritization mechanism for booking sessions based on student's session recency, course activity, availability posting, and lesson completion
- Provides session calendar integration with Moodle, Google, and Outlook Live calendars, including (ics) standard iCal calendar file download
- Provides simple course specific logbook and EASA format.
- Allows for Administration and flight time analysis reporting.
- Allows for configuration-specific integration with external data sources for PIREP, aircraft, and fleet lookup.
- Provides automatic notification of student inactivity warnings, placement on-hold and warning, suspension and instructor inactivity warning communication.
- Provides course-specific student profile with relevant information along with administration functions to manage logbook entries, on-hold management, overdue restriction override, keep alive for inactive students placed on-hold.

## Setup

The plugin can be installed either directly from Moodle or through CLI.

### Moodle plugins install

1. Download [SessionBooking](https://github.com/bavirtual/session-booking/archive/refs/heads/main.zip) zip
2. Site administration > Plugins > Install plugins
3. Drop or upload the zip file

### CLI install

1. `$ cd [path-to-moodle]\local`
2. `$ mkdir booking`
3. `$ cd booking`
4. `$ git clone https://github.com/bavirtual/session-booking.git`

### Dependencies

- [Moodle 4.3](https://moodle.org/)
- [Robin Herbots Inputmask](https://github.com/RobinHerbots/Inputmask) (package already included in js folder)
   - npm install inputmask --save

## Configuration

There are three areas of configurations:

1. Plugin configuration (moodle administrators)

   - User custom fields (primary & secondary simulators, and callsign)
   - Course custom fields (restrictions, home airport and training aircraft ICAOs, course titles)
   - ## Session Booking Configurations:
   - Capability and role assignment: the plugin should have the same configuration as what is shown below. Note that the `Beta User` role is meant for beta rollout, afterwhich the `student` role should have the same capability as the `Beta User`:
     <img src="pix/capability.png" alt="capabilities">

2. Course configuration (course administrators)

3. User configuration (users)
   - Primary Simulator
   - Secondary Simulator
   - Callsign
