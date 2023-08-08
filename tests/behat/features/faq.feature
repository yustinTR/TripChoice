@api @faq @authenticated @instance_goutte
Feature: FAQ

  @authenticated @instance_goutte
  Scenario: Authenticated user without author/editor cannot create faq node
    Given I am logged in as a user with the "authenticated" role
    When I go to "node/add/faq"
    Then the response status code should be 403
    And I should see the heading "Geen toegang"


  @javascript
  Scenario Outline: Editor can create and view node
    Given I am logged in as a user with the "<role>" role
    And I visit "/node/add/faq"
    And I fill in "Behat FAQ question" for "Vraag"
#    And I should see the text "Afbeelding toevoegen"
#    And I should see the text "Tekst met afbeelding toevoegen"
#    When I press "Tekst toevoegen"
#    And I fill field "title" of paragraph "1" with "Behat Answer title"
#    And I fill field "text" of paragraph "1" with "Behat Answer text"
    When I press "Opslaan"
    Then I should see the heading "Behat FAQ question"
#    And I should see the text "Behat Answer title"
#    And I should see the text "Behat Answer text"

    Examples:
      | role         |
      | editor       |
      | chief_editor |
