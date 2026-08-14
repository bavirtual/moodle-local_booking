@local @local_booking
Feature: Pilots view course and EASA logbooks
  In order to track dual and flight time
  As a student or instructor
  I need to open my logbook in course or EASA format

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category | customfield_subscribed | customfield_homeicao |
      | PPL Dual Training  | C1        | 0        | 1                      | EGLL                 |
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

  Scenario: A student can open My logbook in course format
    Given I am on the "PPL Dual Training" "Course" page logged in as "student1"
    When I navigate to "My logbook" in current page administration
    Then I should see "My logbook"
    And I should see "LOGBOOK SUMMARY"
    And I should see "Total flight time"
    And I should see "Course format"
    And I should see "EASA"

  Scenario: A student can switch the logbook to EASA format
    Given I am on the "PPL Dual Training" "Course" page logged in as "student1"
    And I navigate to "My logbook" in current page administration
    When I follow "EASA"
    Then I should see "EASA"
    And I should see "LOGBOOK SUMMARY"

  Scenario: A student cannot open another pilot's logbook
    Given I log in as "student1"
    And I am on "PPL Dual Training" course homepage
    When I navigate to "My logbook" in current page administration
    Then I should see "Sam Student"
    And I should not see "Inga Instructor"
