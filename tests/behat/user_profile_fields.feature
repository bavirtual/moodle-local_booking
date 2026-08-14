@local @local_booking
Feature: Pilots maintain Session booking profile fields
  In order for instructors to match simulators and callsigns
  As a student
  I need to set my primary simulator, secondary simulator, and callsign

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam       | Student  | student1@example.com |

  Scenario: A student can edit Session booking profile fields
    Given I log in as "student1"
    When I open my profile in edit mode
    Then I should see "Primary Flight Simulator"
    And I should see "Secondary Flight Simulator"
    And I should see "Callsign"
    And I should see "VATSIM CID"

  Scenario: A student can save a callsign and primary simulator
    Given I log in as "student1"
    And I open my profile in edit mode
    When I set the following fields to these values:
      | Callsign                 | BAW123 |
      | Primary Flight Simulator | MSFS   |
    And I press "Update profile"
    Then I should see "Changes saved"
