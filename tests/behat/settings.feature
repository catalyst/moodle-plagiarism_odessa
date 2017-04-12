@plagiarism @plagiarism_odessa @javascript
Feature: Plugin Settings
  In order to configure odessa
  As an administrator
  I need to be able to change its settings

  Background:
    Given plagiarism plugins are enabled
    And I log in as "admin"

  Scenario: Navigate to Odessa settings
    Given I am on homepage
    When I navigate to "Odessa plagiarism plugin" node in "Site administration > Plugins > Plagiarism"
    Then I should see "Odessa Settings"

  Scenario: Change Odessa settings
    Given I am on homepage
    And I navigate to "Odessa plagiarism plugin" node in "Site administration > Plugins > Plagiarism"
    When I set the following fields to these values:
      | odessa_use                | check                |
      | odessa_student_disclosure | We are using Odessa. |
    And I press "Save changes"
    Then I should see "Plagiarism Settings Saved"
