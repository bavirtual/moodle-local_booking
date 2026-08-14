@local @local_booking
Feature: Course managers enable Session booking on a course
  In order to manage instructor-led training for a course
  As a course manager
  I need to subscribe the course and set training restrictions

  Background:
    Given the following "courses" exist:
      | fullname           | shortname | category |
      | PPL Dual Training  | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                 |
      | manager1 | Course    | Manager  | manager1@example.com  |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | manager1 | C1     | manager |

  Scenario: Session booking course fields are available in course settings
    Given I log in as "manager1"
    And I am on "PPL Dual Training" course homepage
    When I navigate to "Settings" in current page administration
    Then I should see "Enable session booking"
    And I should see "Training type"
    And I should see "Home airport ICAO"
    And I should see "Training aircraft ICAO"
    And I should see "Posting wait restriction"
    And I should see "On-hold restriction"
    And I should see "Requires lesson completion"
    And I should see "Minimum slot period"

  Scenario: A manager enables Session booking for dual training
    Given I log in as "manager1"
    And I am on "PPL Dual Training" course homepage
    And I navigate to "Settings" in current page administration
    When I set the following fields to these values:
      | Enable session booking     | 1    |
      | Training type              | Dual |
      | Home airport ICAO          | EGLL |
      | Posting wait restriction   | 0    |
      | On-hold restriction        | 0    |
      | Minimum slot period        | 2    |
    And I press "Save and display"
    Then I should see "PPL Dual Training"

  Scenario: A manager can switch a subscribed course to multicrew training
    Given I log in as "manager1"
    And I am on "PPL Dual Training" course homepage
    And I navigate to "Settings" in current page administration
    And I set the following fields to these values:
      | Enable session booking | 1        |
      | Training type          | Multicrew |
    When I press "Save and display"
    And I navigate to "Settings" in current page administration
    Then the field "Enable session booking" matches value "1"
    And the field "Training type" matches value "Multicrew"
