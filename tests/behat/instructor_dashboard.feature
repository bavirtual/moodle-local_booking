@local @local_booking
Feature: Instructors manage student progression from the dashboard
  In order to book the next training session
  As an instructor
  I need a dashboard of active students, my bookings, and participation

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category | customfield_subscribed | customfield_homeicao |
      | PPL Dual Training  | C1        | 0        | 1                      | EGLL                 |
    And the following "users" exist:
      | username | firstname | lastname   | email                  |
      | teacher1 | Inga      | Instructor | teacher1@example.com   |
      | manager1 | Pat       | Manager    | manager1@example.com   |
      | student1 | Sam       | Student    | student1@example.com   |
      | student2 | Alex      | Student    | student2@example.com   |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | manager1 | C1     | manager        |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | name         | intro               | course | idnumber | section |
      | assign   | P1 Flight 1  | First dual exercise | C1     | p1ex1    | 1       |
      | assign   | P1 Flight 2  | Second dual exercise | C1    | p1ex2    | 1       |
      | lesson   | Ground brief | Prerequisite lesson | C1     | lesson1  | 1       |
    And the following "groups" exist:
      | name                 | course | idnumber |
      | OnHold               | C1     | onhold   |
      | Keep Active          | C1     | keep     |
      | Inactive Instructors | C1     | inactive |
      | Graduates            | C1     | grads    |

  Scenario: An instructor can open the Session booking dashboard
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    When I navigate to "Session booking" in current page administration
    Then I should see "Instructor dashboard"
    And I should see "Student"
    And I should see "Fleet"
    And I should see "Wait Days"

  Scenario: The dashboard lists enrolled students for booking
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    When I navigate to "Session booking" in current page administration
    Then I should see "Sam Student"
    And I should see "Alex Student"
    And I should see "P1 Flight 1"
    And I should see "P1 Flight 2"

  @javascript
  Scenario: An instructor sees the My active bookings section
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    When I navigate to "Session booking" in current page administration
    Then I should see "My active bookings"

  Scenario: A course manager can view instructor participation
    Given I am on the "PPL Dual Training" "Course" page logged in as "manager1"
    When I navigate to "Session booking" in current page administration
    Then I should see "Instructor participation"
    And I should see "Inga Instructor"
