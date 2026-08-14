@local @local_booking
Feature: Site administrators configure Session booking
  In order to run an Authorized Training Organization on Moodle
  As an administrator
  I need to configure ATO identity, posting hours, and advanced integrations

  Scenario: An administrator can open Session booking plugin settings
    Given I log in as "admin"
    When I navigate to "Plugins > Local plugins > Session booking" in site administration
    Then I should see "Authorized Training Organization (ATO)"
    And I should see "Availability posting"
    And I should see "Integrations"
    And I should see "First session time"
    And I should see "Last session time"
    And I should see "Availability posting weeks lookahead"
    And I should see "Advanced configurations"

  Scenario: An administrator can save ATO name and posting window
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Session booking" in site administration
    When I set the following fields to these values:
      | Name                                       | BAVirtual ATO |
      | First session time                         | 08:00         |
      | Last session time                          | 23:00         |
      | Availability posting weeks lookahead       | 5             |
    And I press "Save changes"
    Then I should see "Changes saved"
    And the field "Name" matches value "BAVirtual ATO"
    And the field "Availability posting weeks lookahead" matches value "5"
