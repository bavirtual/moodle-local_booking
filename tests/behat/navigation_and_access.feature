@local @local_booking
Feature: Session booking navigation follows role capabilities
  In order to use only the tools I am allowed to use
  As an instructor or student
  I need to see the correct Session booking links for my role

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category | customfield_subscribed |
      | PPL Dual Training  | C1        | 0        | 1                      |
      | Ground School      | C2        | 0        | 0                      |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Inga      | Instructor | teacher1@example.com |
      | student1 | Sam       | Student    | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | teacher1 | C2     | editingteacher |
      | student1 | C2     | student        |
    And the following "activities" exist:
      | activity | name        | intro              | course | idnumber | section |
      | assign   | P1 Flight 1 | First dual exercise | C1     | p1ex1    | 1       |

  Scenario: An instructor sees booking and availability links on a subscribed course
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    Then I should see "Session booking"
    And I should see "Student availability"
    And I should not see "Progression"

  Scenario: A student sees availability, progression, and logbook links
    Given I am on the "PPL Dual Training" "Course" page logged in as "student1"
    Then I should see "My availability"
    And I should see "Progression"
    And I should see "My logbook"
    And I should not see "Session booking"
    And I should not see "Student availability"

  Scenario: Session booking links are hidden on an unsubscribed course
    Given I am on the "Ground School" "Course" page logged in as "teacher1"
    Then I should not see "Session booking"
    And I should not see "Student availability"

  Scenario: A student cannot open the instructor dashboard
    Given I log in as "student1"
    When I am on "PPL Dual Training" course homepage
    Then I should not see "Instructor dashboard"
    And I should not see "My active bookings"
