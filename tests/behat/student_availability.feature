@local @local_booking
Feature: Students post weekly availability for session booking
  In order for instructors to book me
  As a student
  I need to record and manage my weekly availability slots

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category | customfield_subscribed | customfield_postingwait | customfield_minslotperiod |
      | PPL Dual Training  | C1        | 0        | 1                      | 0                       | 2                         |
    And the following "users" exist:
      | username | firstname | lastname   | email                |
      | teacher1 | Inga      | Instructor | teacher1@example.com |
      | student1 | Sam       | Student    | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "groups" exist:
      | name        | course | idnumber |
      | OnHold      | C1     | onhold   |
      | Keep Active | C1     | keep     |
      | Graduates   | C1     | grads    |

  Scenario: A student can open My availability
    Given I am on the "PPL Dual Training" "Course" page logged in as "student1"
    When I navigate to "My availability" in current page administration
    Then I should see "My availability"
    And I should see "Save Availability"

  @javascript
  Scenario: A student can see the weekly availability calendar
    Given I am on the "PPL Dual Training" "Course" page logged in as "student1"
    When I navigate to "My availability" in current page administration
    Then I should see "Save Availability"
    And I should see "Slots posted"

  Scenario: An instructor can view student availability
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    When I navigate to "Student availability" in current page administration
    Then I should see "Student availability"
    And I should see "View everyone's availability"

  Scenario: An on-hold student is warned that instructors will not see posts
    Given the following "group members" exist:
      | user     | group  |
      | student1 | onhold |
    And I am on the "PPL Dual Training" "Course" page logged in as "student1"
    When I navigate to "My availability" in current page administration
    Then I should see "You are currently on-hold and instructors will NOT see your postings"
