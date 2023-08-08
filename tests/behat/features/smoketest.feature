@smoketest @api

Feature: Validation of usable system

  Scenario: Check status page values general
    Given I am logged in as a user with the "administrator" role
    When I visit "/admin/reports/status"
    Then I should see the text "Beeldverwerkingstoolkit imagemagick ImageMagick image toolkit" in the system_status_report region
    And I should see the text "Genereren willekeurig nummer Succesvol" in the system_status_report region
    And I should see the text "PHP-extensies Ingeschakeld" in the system_status_report region
    And I should see the text "Unicode-bibliotheek PHP-Mbstring-uitbreiding" in the system_status_report region

  Scenario: Check status page values database
    Given I am logged in as a user with the "administrator" role
    When I visit "/admin/reports/status"
    And I should see the text "Database-updates Actueel" in the system_status_report region
    And I should see the text "Entiteit-/velddefinities Actueel" in the system_status_report region

  Scenario: Check status page values for security
    Given I am logged in as a user with the "administrator" role
    When I visit "/admin/reports/status"
    And I should see the text "Node-toegangsrechten Uitgeschakeld" in the system_status_report region
    And I should see the text "Toegang tot update.php Beveiligd" in the system_status_report region
    And I should see the text "Vertrouwde host-instellingen Ingeschakeld" in the system_status_report region

  Scenario: Check status page values for webforms
    Given I am logged in as a user with the "administrator" role
    When I visit "/admin/reports/status"
    And I should see the text "Webform: Private files Private file system is set." in the system_status_report region
    And I should see the text "Webform: HTML email support Provided by the Webform module." in the system_status_report region

  Scenario: Administrator can login and view content overviews
    Given I am logged in as a user with the "administrator" role
    When I visit "/admin/content"
    Then I should see the link "Inhoud toevoegen"
    When I visit "/admin/content/files"
    Then I see the heading "Bestanden"
    When I visit "/admin/content/media"
    Then I see the heading "Media"
    When I visit "/admin/content/media-grid"
    Then I see the heading "Media"

  Scenario: Administrator can login and access add node screens
    Given I am logged in as a user with the "administrator" role
    When I visit "node/add"
    Then I should see the link "FAQ"
    When I visit "/node/add/faq"
    Then I see the heading "FAQ aanmaken"

  @instance_goutte
  Scenario: Administrator can login and access add media screens
    Given I am logged in as a user with the "administrator" role
    When I visit "/media/add"
    Then I should see the link "Audio"
    And I should see the link "Document"
    And I should see the link "Image"
    And I should see the link "Link"
    And I should see the link "Remote video"
    And I should see the link "Video"
    When I visit "/media/add/document"
    Then I see the heading "Document toevoegen"
    When I visit "/media/add/image"
    Then I see the heading "Image toevoegen"
    When I visit "/media/add/remote_video"
    Then I see the heading "Remote video toevoegen"
    When I visit "/media/add/video"
    Then I see the heading "Video toevoegen"

  @javascript @instance_selenium
  Scenario: Administrator can login and upload and remove an image file
    Given I am logged in as a user with the "administrator" role
    When I visit "/media/add/image"
    Then I see the heading "Image toevoegen"
    And I attach the file "file_example_JPG_100kB.jpg" to "Bestand toevoegen"
    And I wait for AJAX to finish
    And I wait until I see "div.form-managed-file__main > span > a" element
    And I remember the text shown in element "div.form-managed-file__main > span > a" as "image-name"
    And I fill in "Tekstalternatief" with "image-name"
    And I press "Opslaan"
    Then I see the heading "Media"
    When I fill in "Media name" with the text remembered as "image-name"
    And I press "Filter"
    Then I should see the text remembered as "image-name" in the "tr:nth-child(1) > td.views-field.views-field-name > a" element
    And I remember the value of attribute "href" of element "tr:nth-child(1) > td.views-field.views-field-name > a" as "image-edit-url"
    When I visit the page remembered as "image-edit-url"
    And I wait for AJAX to finish
    Then I should see the text remembered as "image-name" in the "div.form-managed-file__main > span > a" element
    And I remember the value of attribute "href" of element "#edit-delete" as "image-delete-url"
    When I visit the page remembered as "image-delete-url"
    Then I should see the text remembered as "image-name" in the "#block-gin-page-title > h1" element
    When I press "Delete"
    Then I should see the heading "Media"
    And I should see "media-item" in the "div.messages__content" element
    And I should see the text remembered as "image-name" in the "div.messages__content" element
    And I should see "is verwijderd" in the "div.messages__content" element
    When I fill in "Media name" with the text remembered as "image-name"
    And I press "Filter"
    Then I should see the text "No media available."
    When I visit the page remembered as "image-edit-url"
    Then I see the heading "Pagina niet gevonden"

  @drush @instance_goutte
  Scenario: Drush returns expected roles
    Given I run drush 'role:list | grep :'
    And print last drush output
    Then drush output should contain "administrator:"
    And drush output should contain "chief_editor:"
    And drush output should contain "editor:"
    And drush output should contain "authenticated:"
    And drush output should contain "anonymous:"

