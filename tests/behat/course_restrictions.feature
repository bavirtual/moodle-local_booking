@local @local_booking
Feature: Course restrictions control posting, on-hold, and lesson gates
  In order to keep students progressing fairly
  As a course manager
  I need posting wait, on-hold, suspension, and lesson-completion settings

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category |
      | PPL Dual Training  | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | manager1 | Pat       | Manager  | manager1@example.com |
      | student1 | Sam       | Student  | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | manager1 | C1     | manager |
      | student1 | C1     | student |
    And the following "groups" exist:
      | name        | course | idnumber |
      | OnHold      | C1     | onhold   |
      | Keep Active | C1     | keep     |
      | Graduates   | C1     | grads    |

  Scenario: A manager can require lesson completion before air exercises
    Given I log in as "manager1"
    And I am on "PPL Dual Training" course homepage
    And I navigate to "Settings" in current page administration
    When I set the following fields to these values:
      | Enable session booking       | 1 |
      | Requires lesson completion   | 1 |
    And I press "Save and display"
    And I navigate to "Settings" in current page administration
    Then the field "Requires lesson completion" matches value "1"

  Scenario: A manager can set posting wait and on-hold periods
    Given I log in as "manager1"
    And I am on "PPL Dual Training" course homepage
    And I navigate to "Settings" in current page administration
    When I set the following fields to these values:
      | Enable session booking       | 1  |
      | Posting wait restriction     | 7  |
      | On-hold restriction          | 21 |
      | Minimum slot period          | 2  |
    And I press "Save and display"
    And I navigate to "Settings" in current page administration
    Then the field "Posting wait restriction" matches value "7"
    And the field "On-hold restriction" matches value "21"
    And the field "Minimum slot period" matches value "2"

  Scenario: A manager can see Session booking student groups
    Given I log in as "manager1"
    And I am on "PPL Dual Training" course homepage
    When I navigate to "Users > Groups" in current page administration
    Then I should see "Keep Active"
    And I should see "Graduates"
    And I should see "OnHold"
