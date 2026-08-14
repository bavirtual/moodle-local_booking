@local @local_booking
Feature: Students review their training progression
  In order to know which exercise is next
  As a student
  I need a read-only view of course progression

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category | customfield_subscribed |
      | PPL Dual Training  | C1        | 0        | 1                      |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam       | Student  | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | name        | intro               | course | idnumber | section |
      | assign   | P1 Flight 1 | First dual exercise | C1     | p1ex1    | 1       |
      | lesson   | Ground brief | Prerequisite lesson | C1    | lesson1  | 1       |

  Scenario: A student can open the progression view
    Given I am on the "PPL Dual Training" "Course" page logged in as "student1"
    When I navigate to "Progression" in current page administration
    Then I should see "Progression"
    And I should see "P1 Flight 1"
    And I should see "Sam Student"
    And I should not see "Save booking"
    And I should not see "My active bookings"
