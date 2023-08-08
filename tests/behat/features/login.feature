@login
Feature: Log in to DWO

  Scenario: Login screen shown for anonymous user
    Given I visit "/user/login"
    Then I should see the heading "Inloggen"
    And I should see the button "Inloggen"
    And I should see the text "Gebruikersnaam"
    And I should see the text "Wachtwoord"
