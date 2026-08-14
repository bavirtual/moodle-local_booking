@local @local_booking
Feature: Instructors administer a student from the course profile
  In order to manage on-hold status, logbooks, and reports
  As an instructor
  I need a course-specific student profile

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category | customfield_subscribed | customfield_homeicao | customfield_onholdperiod |
      | PPL Dual Training  | C1        | 0        | 1                      | EGLL                 | 21                       |
    And the following "users" exist:
      | username | firstname | lastname   | email                |
      | teacher1 | Inga      | Instructor | teacher1@example.com |
      | student1 | Sam       | Student    | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name        | intro               | course | idnumber | section |
      | assign   | P1 Flight 1 | First dual exercise | C1     | p1ex1    | 1       |
    And the following "groups" exist:
      | name        | course | idnumber |
      | OnHold      | C1     | onhold   |
      | Keep Active | C1     | keep     |
      | Graduates   | C1     | grads    |

  Scenario: An instructor can open a student profile from the dashboard
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    And I navigate to "Session booking" in current page administration
    When I follow "Sam Student"
    Then I should see "Student profile"
    And I should see "Fleet"
    And I should see "Primary simulator"
    And I should see "Moodle profile"
    And I should see "Enrol date"

  @javascript
  Scenario: The student profile exposes logbook, on-hold, and reports
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    And I navigate to "Session booking" in current page administration
    When I follow "Sam Student"
    Then I should see "Logbook"
    And I should see "Put on-hold"
    And I should see "Keep active"
    And I should see "Mentored Sessions Report"
