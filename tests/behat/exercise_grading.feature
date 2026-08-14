@local @local_booking
Feature: Instructors work from assignment exercises toward grading
  In order to complete the booking-to-grade workflow
  As an instructor
  I need booked exercises to appear as assignments in the subscribed course

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category | customfield_subscribed |
      | PPL Dual Training  | C1        | 0        | 1                      |
    And the following "users" exist:
      | username | firstname | lastname   | email                |
      | teacher1 | Inga      | Instructor | teacher1@example.com |
      | student1 | Sam       | Student    | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name        | intro               | course | idnumber | section | assignsubmission_onlinetext_enabled |
      | assign   | P1 Flight 1 | First dual exercise | C1     | p1ex1    | 1       | 1                                   |

  Scenario: An assignment exercise is visible on the instructor dashboard
    Given I am on the "PPL Dual Training" "Course" page logged in as "teacher1"
    When I navigate to "Session booking" in current page administration
    Then I should see "P1 Flight 1"

  Scenario: An instructor can open the exercise assignment for grading
    Given I am on the "P1 Flight 1" "assign activity" page logged in as "teacher1"
    Then I should see "P1 Flight 1"
    And I should see "Grade"

  Scenario: A student can submit the exercise assignment
    Given I am on the "P1 Flight 1" "assign activity" page logged in as "student1"
    Then I should see "P1 Flight 1"
    And I should see "Add submission"
