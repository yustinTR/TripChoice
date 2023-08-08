<?php

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\MinkExtension\Context\RawMinkContext;
use GuzzleHttp\Client;

/**
 * Features context.
 */
class FeatureContext extends RawMinkContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  /**
   *
   * @BeforeScenario
   */
  public function beforeJavascriptScenario(BeforeScenarioScope $scope) {
    // Work around https://github.com/jhedstrom/drupalextension/issues/486#issuecomment-700146113
    $environment = $scope->getEnvironment();
    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
    $this->setMinkParameter('ajax_timeout', 10);

    // Handle basic authorisation depending on session type
    $driver = $this->getSession()->getDriver();

    if ($driver instanceof Selenium2Driver) {

      // Get base_url from behat.yml
      $currentBaseURL = $this->getMinkParameter('base_url');
      // Determine if base_url contains basic auth using "@"
      if (strpos($currentBaseURL, "@") == FALSE) {
        // Construct url with basic auth
        $baseUrlWithAuth = str_replace("://", "://klant:KlantLogin@", $currentBaseURL);
        // Open url with basic auth in session
        print("Starting session with: $baseUrlWithAuth \n");
        $this->getSession()->visit($baseUrlWithAuth);
        $this->getSession()->visit($currentBaseURL);
        print("Tests will use base_url: $currentBaseURL");

      }
      else {
        print("Tests will use base_url: $currentBaseURL");

      }
    }
    else {
      // Setup basic auth.
      $this->getSession()->setBasicAuth('klant', 'KlantLogin');
    }
  }

  /**
   * MPOL: Adapted code for determining drush version
   * Determine if drush is a legacy version.
   *
   * @return bool
   *   Returns TRUE if drush is older than drush 9.
   */
  protected function isLegacyDrush() {
    try {
      // Try for a drush 9 version.
      $version = trim($this->drush('version', [], ['format' => 'string']));
      // MPOL: Adapted code for determining drush version.
      $version = str_replace('Drush version : ', '', $version);
      return version_compare($version, '11', '<=');
    }
    catch (\RuntimeException $e) {
      // The version of drush is old enough that only `--version` was available,
      // so this is a legacy version.
      return TRUE;
    }
  }

  const TIMEOUT = 5;

  /** @var MinkContext */
  private $minkContext;

  /**
   * @When /^I wait (\d+) seconds$/
   */
  public function iWaitForSeconds($seconds) {
    sleep($seconds);
  }

  /**
   * Wait for a element.
   *
   * @When (I )wait :count second(s) until I see :element element
   */
  public function iWaitSecondsForElement($seconds, $element) {
    for ($i = 0; $i < $seconds; $i++) {
      if (!is_null($this->getSession()->getPage()->find('css', $element))) {
        break;
      }
      sleep(1);
    }
    $this->assertSession()->elementExists('css', $element);
  }

  /**
   * @When (I )wait until I see :element element
   */
  public function iWaitForElement($element) {
    $this->iWaitSecondsForElement(self::TIMEOUT, $element);
  }

  /**
   * Checks, that the element contains specified text after timeout.
   *
   * @When (I )wait :count second(s) until I see :text in the :element element
   * @throws ResponseTextException
   */
  public function iWaitSecondsUntilISeeInTheElement($seconds, $text, $element) {

    $this->iWaitSecondsForElement(self::TIMEOUT, $element);

    for ($i = 0; $i < $seconds; $i++) {
      $foundText = $this->getSession()
        ->getPage()
        ->find('css', $element)
        ->getText();
      if ($foundText !== '' && str_contains($text, $foundText)) {
        break;
      }
      sleep(1);
    }

    $this->minkContext->assertElementContainsText($element, $text);
  }

  /**
   * Checks, that the page should contains specified text after given timeout.
   *
   * @When (I )wait :count second(s) until I see :text
   */
  public function iWaitSecondsUntilISee($seconds, $text) {
    $this->iWaitSecondsUntilISeeInTheElement($seconds, $text, 'html');
  }

  /**
   * Checks, that the element contains specified text after timeout.
   *
   * @When (I )wait until I see :text in the :element element
   */
  public function iWaitUntilISeeInTheElement($text, $element) {
    $this->iWaitSecondsUntilISeeInTheElement(self::TIMEOUT, $text, $element);
  }

  /**
   * @When (I )wait until I see :text
   */
  public function iWaitUntilISee($text) {
    $this->iWaitSecondsUntilISeeInTheElement(self::TIMEOUT, $text, 'html');
  }

  /**
   * Checks that a 403 Access Denied error occurred.
   *
   * @Then I should get an access denied error
   */
  public function assertAccessDenied() {
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * @When I fill in the autocomplete :autocomplete with :text and click :popup
   */
  public function fillInDrupalAutocomplete($autocomplete, $text, $popup) {
    $el = $this->getSession()->getPage()->findField($autocomplete);
    $el->focus();

    // Set the autocomplete text then put a space at the end which triggers
    // the JS to go do the autocomplete stuff.
    $el->setValue($text);
    $el->keyUp(' ');

    // Sadly this grace of 1 second is needed here.
    sleep(1);
    $this->minkContext->iWaitForAjaxToFinish();

    // Drupal autocompletes have an id of autocomplete which is bad news
    // if there are two on the page.
    $autocomplete = $this->getSession()->getPage()->findById('autocomplete');

    if (empty($autocomplete)) {
      throw new ExpectationException('Could not find the autocomplete popup box', $this->getSession());
    }

    $popup_element = $autocomplete->find('xpath', "//div[text() = '{$popup}']");

    if (empty($popup_element)) {
      throw new ExpectationException('Could not find autocomplete popup text @popup'
        . ['@popup' => $popup], $this->getSession());
    }

    $popup_element->click();
  }

  /**
   * Checks that the given list of files return a 200 OK status code.
   *
   * @param \Behat\Gherkin\Node\TableNode $files
   *   The list of files that should be downloadable, relative to the base URL.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   Thrown when a file could not be downloaded.
   *
   * @Then the following files can be downloaded:
   */
  public function assertFilesDownloadable(TableNode $files) {
    $client = new Client();
    foreach ($files->getColumn(0) as $file) {
      if ($client->head($this->locatePath($file))->getStatusCode() != 200) {
        throw new ExpectationException("File $file could not be downloaded.", $this->getSession());
      }
    }
  }

  /**
   * @Given /^the page title should be "([^"]*)"$/
   */
  public function thePageTitleShouldBe($expectedTitle) {
    $titleElement = $this->getSession()->getPage()->find('css', 'head title');
    if ($titleElement === NULL) {
      throw new Exception('Page title element was not found!');
    }
    else {
      $title = $titleElement->getText();
      if ($expectedTitle !== $title) {
        throw new Exception("Incorrect title! Expected:$expectedTitle | Actual:$title ");
      }
    }
  }

  /**
   * @Then there should be a(n) :tag element titled :title with attribute :attribute and value :value
   */
  public function thereShouldBeAnElementTitledWithAttributeAndValue($tag, $title, $attribute, $value) {
    return $this->assertSession()
      ->elementExists('css', sprintf('%s:contains("%s")[%s="%s"]', $tag, $title, $attribute, $value));
  }

  /**
   * @Then there should be a(n) :tag element with attribute :attribute and value :value
   */
  public function thereShouldBeAnElementWithAttributeAndValue($tag, $attribute, $value) {
    return $this->assertSession()
      ->elementExists('css', sprintf('%s[%s="%s"]', $tag, $attribute, $value));
  }

  /**
   * @Then there should be a(n) :tag element with attribute :attribute containing :value and with attribute :attribute2 containing :value2
   */
  public function thereShouldBeAnElementWithAttributeAndValueAndAttributeAndValue($tag, $attribute, $value, $attribute2, $value2) {
    $page = $this->getSession()->getPage();
    $meta = $page->find('xpath', sprintf('//%s[@%s = "%s"]', $tag, $attribute, $value));
    $actualValue = $meta->getAttribute($attribute2);
    $expectedValue = $value2;
    if ($expectedValue !== $actualValue) {
      throw new Exception("Value not found for attribute $attribute2 Expected:$expectedValue | Actual:$actualValue ");
    }
  }

  private $storedFieldValues = [];

  /**
   * @When I remember the text shown in element :element as :name
   */
  public function iRememberTheTextShownInElementAs($element, $storeAs) {

    if (substr($element, 0, 2) === "//") {
      $foundElement = $this->assertSession()->elementExists('xpath', $element);
    }
    else {
      $foundElement = $this->assertSession()->elementExists('css', $element);
    }

    $text = $foundElement->getText();

    if (empty($text)) {
      throw new \Exception(sprintf("No text was found in element '%s' on the page %s", $element, $this->getSession()
        ->getCurrentUrl()));
    }

    $this->storedFieldValues[$storeAs] = $text;

    foreach ($this->storedFieldValues as $key => $value) {
      printf("Remembering '$value' as '$key'\n");
    }
  }

  /**
   * @When I remember the value of attribute :attribute of element :element as :name
   */
  public function iRememberTheValueOfAttributeOfElementAs($attribute, $element, $storeAs) {

    if (substr($element, 0, 2) === "//") {
      $foundElement = $this->assertSession()->elementExists('xpath', $element);
    }
    else {
      $foundElement = $this->assertSession()->elementExists('css', $element);
    }


    // $text = $this->getSession()->getPage()->find('css', $element)->getAttribute($attribute);
    $text = $foundElement->getAttribute($attribute);

    if (!($text)) {
      throw new \Exception(sprintf("No value was found in the attribute '%s' of element '%s' on the page %s", $attribute, $element, $this->getSession()
        ->getCurrentUrl()));
    }

    $this->storedFieldValues[$storeAs] = $text;

    foreach ($this->storedFieldValues as $key => $value) {
      printf("Remembering '$value' as '$key'\n");
    }
  }

  /**
   * @When I remember the current url as :name
   */
  public function iRememberTheCurrentUrl($storeAs) {

    $url = $this->getSession()->getCurrentUrl();

    $this->storedFieldValues[$storeAs] = $url;

    foreach ($this->storedFieldValues as $key => $value) {
      printf("Remembering '$value' as '$key'\n");
    }
  }

  /**
   * @Then I fill in :field with the text remembered as :name
   */
  public function iFillInWithTheTextRememberedAs($field, $storedAs) {

    $value = strval($this->storedFieldValues[$storedAs]);
    printf("Recalling '$storedAs' as '$value'");

    $this->getSession()->getPage()->fillField($field, $value);
  }

  /**
   * Check text of remembered token in element.
   *
   * @Then I should see the text remembered as :name in the :element element
   */
  public function iShouldSeeTheTextRememberedAsInTheRegion($storedAs, $selector) {

    $text = strval($this->storedFieldValues[$storedAs]);

    printf("Recalling '$storedAs' as '$text'");

    $this->assertSession()->elementTextContains('css', $selector, $text);
  }

  /**
   * Check text of remembered token not in element.
   *
   * @Then I should not see the text remembered as :name in the :element element
   */
  public function iShouldNotSeeTheTextRememberedAsInTheRegion($storedAs, $selector) {

    $text = strval($this->storedFieldValues[$storedAs]);

    printf("Recalling '$storedAs' as '$text'");

    $this->assertSession()->elementTextNotContains('css', $selector, $text);

  }


  /**
   * @Then the :field field should contain the text remembered as :name
   */
  public function theFieldShouldContainTheTextRememberedAs($field, $storedAs) {

    $value = strval($this->storedFieldValues[$storedAs]);
    printf("Recalling '$storedAs' as '$value'");

    $this->assertSession()->fieldValueEquals($field, $value);
  }

  /**
   * @When I visit the page remembered as :name
   */
  public function iVisitThePageRememberedAs($storedAs) {
    $page = strval($this->storedFieldValues[$storedAs]);
    printf("Recalling '$storedAs' as '$page'");

    $this->visitPath($page);
  }

  /**
   * @When I click link with text remembered as :name
   */
  public function iClickLinkWithTextRememberedAs($storedAs) {
    $value = strval($this->storedFieldValues[$storedAs]);
    printf("Recalling '$storedAs' as '$value'");
    $this->getSession()->getPage()->clickLink($value);
  }

  /**
   * @Then I should see the texts remembered as :name1 and :name2 match
   */
  public function iShouldSeeTheTextsRememberedAsAndMatch($storedAs1, $storedAs2) {

    $text1 = strval($this->storedFieldValues[$storedAs1]);
    $text2 = strval($this->storedFieldValues[$storedAs2]);

    printf("Making sure the texts stored as '$storedAs1' and '$storedAs2' are matching...\n");

    // Error when not equal.
    if ($text1 != $text2) {
      throw new \InvalidArgumentException(sprintf("The texts\n'$text1'\n'$text2'\n do not match!", $text1, $text2));
    }

    printf("Yes, the texts\n'$text1'\n'$text2'\nmatch!");
  }

  /**
   * @Then I should see the urls remembered as :url1 and :url2 match
   */
  public function iShouldSeeTheUrlsRememberedAsAndMatch($url1, $url2) {
    $text1 = str_replace(['https://', 'http://'], '', strval($this->storedFieldValues[$url1]));
    $text2 = str_replace(['https://', 'http://'], '', strval($this->storedFieldValues[$url2]));

    printf("Making sure the urls stored as '$url1' and '$url2' are matching...\n");

    // Error when not equal.
    if ($text1 != $text2) {
      throw new \InvalidArgumentException(sprintf("The urls\n'$text1'\n'$text2'\n do not match!", $text1, $text2));
    }

    printf("Yes, the urls\n'$text1'\n'$text2'\nmatch!");
  }


  /**
   * @Then I should see the texts remembered as :name1 and :name2 do not match
   */
  public function iShouldSeeTheTextsRememberedAsAndDoNotMatch($storedAs1, $storedAs2) {

    $text1 = strval($this->storedFieldValues[$storedAs1]);
    $text2 = strval($this->storedFieldValues[$storedAs2]);

    printf("Making sure the texts stored as '$storedAs1' and '$storedAs2' are not matching...\n");

    // Error when not equal.
    if ($text1 == $text2) {
      throw new \InvalidArgumentException(sprintf("The texts\n'$text1'\n'$text2'\nshould not match!", $text1, $text2));
    }

    printf("Yes, the texts\n'$text1'\n'$text2'\ndo not match!");
  }

  /**
   * @When /^I click on element "([^"]*)"$/
   */
  public function iClickOn($arg1) {
    $page = $this->getSession()->getPage();

    $findName = $page->find("css", $arg1);
    if (!$findName) {
      throw new Exception($arg1 . " could not be found");
    }
    else {
      // $findName->focus();
      // $findName->mouseOver();
      $findName->click();
    }
  }

  /**
   * @When /^I hover over element "([^"]*)"$/
   */
  public function iHoverOverTheElement($locator) {
    // Get the mink session.
    $session = $this->getSession();
    // Runs the actual query and returns the element.
    $element = $session->getPage()->find('css', $locator);

    // Errors must not pass silently.
    if (NULL === $element) {
      throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $locator));
    }

    // ok, let's hover it.
    $element->mouseOver();
  }

  /**
   * @Then I fill in wysiwyg on field :locator with :value
   */
  public function iFillInWysiwygOnFieldWith($locator, $value) {
    $el = $this->getSession()->getPage()->findField($locator);

    if (empty($el)) {
      throw new ExpectationException('Could not find WYSIWYG with locator: ' . $locator, $this->getSession());
    }

    $fieldId = $el->getAttribute('id');

    if (empty($fieldId)) {
      throw new Exception('Could not find an id for field with locator: ' . $locator);
    }

    $this->getSession()
      ->executeScript("CKEDITOR.instances[\"$fieldId\"].setData(\"$value\");");
  }

  /**
   * @Then the mail sent to remembered :arg1 with subject :arg2 contains texts:
   */
  public function aMailWasSentToRememberedWithSubjectThatContains($storedAsTo, $subject, TableNode $texts) {

    $toAddress = strval($this->storedFieldValues[$storedAsTo]);

    printf("Recalling '$storedAsTo' as '$toAddress'");

    $textSelector = 'div.preview.ng-scope';

    $this->checkMail($subject, $toAddress, 'keep');

    foreach ($texts->getRowsHash() as $text => $value) {
      $this->assertSession()->elementTextContains('css', $textSelector, $text);
    }
  }

  /**
   * @Then the mail sent to remembered :arg1 with subject :arg2 contains remembered texts:
   */
  public function aMailWasSentToRememberedWithSubjectThatContainsRemembered($storedAsTo, $subject, TableNode $storedAs) {

    $toAddress = strval($this->storedFieldValues[$storedAsTo]);

    printf("Recalling '$storedAsTo' as '$toAddress'");

    foreach ($storedAs->getRowsHash() as $text => $value) {
      $rememberedText = strval($this->storedFieldValues[$text]);
      printf("\nRecalling '$text' as '$rememberedText'");
    }

    $this->checkMail($subject, $toAddress, 'keep');

    $textSelector = 'div.preview.ng-scope';

    foreach ($storedAs->getRowsHash() as $text => $value) {
      $rememberedText = strval($this->storedFieldValues[$text]);
      $this->assertSession()
        ->elementTextContains('css', $textSelector, $rememberedText);
    }
  }

  /**
   * @Then I delete the mail sent to remembered :arg1 with subject :arg2
   */
  public function iDeleteTheMailSentToRememberedWithSubject($storedAsTo, $subject) {
    $toAddress = strval($this->storedFieldValues[$storedAsTo]);
    printf("Recalling '$storedAsTo' as '$toAddress'");

    $this->checkMail($subject, $toAddress, 'trash');
  }

  /**
   *
   */
  public function checkMail($subject, $toAddressName, $action) {

    $toAddress = $toAddressName . "@example.com";
    print("\nEmail is $toAddress");

    $startUrl = $this->getMinkParameter('base_url');

    if (str_contains($startUrl, '.test')) {
      $mailhog = 'http://127.0.0.1:8025/';
    }
    elseif ($startUrl === 'https://web') {
      $mailhog = 'http://web:8025/';
    }
    else {
      $mailhog = 'https://p-mailhog-drupal.finalist.nl/';
    }

    $firstMessageSelector = 'div.messages.container-fluid.ng-scope > div:nth-child(1)';
    $subjectSelectorList = 'div.messages.container-fluid.ng-scope > div:nth-child(1) > div > span';
    $toAddressSelectorList = 'div.messages.container-fluid.ng-scope > div:nth-child(1) > div > div > div';
    $subjectSelectorDetail = 'div.preview.ng-scope > div.row.headers > div.col-md-10 > table > tbody > tr:nth-child(2) > td > strong';
    $toAddressSelectorDetail = 'div.preview.ng-scope > div.row.headers > div.col-md-10 > table > tbody > tr:nth-child(3) > td';

    $this->visitPath($mailhog);
    $secondsToWait = 5;
    $startTime = time();
    print("\nWaiting max $secondsToWait sec for mailhog ($mailhog) to be connected... ");
    foreach (range(1, $secondsToWait) as $index) {
      $page = $this->getSession()->getPage();
      $inboxCount = $page->find('css', 'body > div > div > div.col-md-2.col-sm-3 > ul > li:nth-child(1) > a')
        ->getText();
      if (strpos($inboxCount, 'Disconnected') === FALSE) {
        break;
      }
      sleep(1);
    }

    $endTime = time();
    $duration = $endTime - $startTime;
    print("Done: Connected (took $duration sec)");

    $secondsToWait = 10;
    $startTime = time();
    print("\nWaiting max $secondsToWait sec for mailhog ($mailhog) to show all messages... ");
    foreach (range(1, $secondsToWait) as $index) {
      $page = $this->getSession()->getPage();
      $inboxCount = $page->find('css', 'body > div > div > div.col-md-2.col-sm-3 > ul > li:nth-child(2) > a')
        ->getText();
      if (strpos($inboxCount, '(0)') === FALSE) {
        break;
      }
      sleep(1);
    }

    $endTime = time();
    $duration = $endTime - $startTime;
    print("Done: $inboxCount (took $duration sec)");

    $page->findField('search')->setValue($toAddress);
    $page->clickLink('Find messages to ' . $toAddress);
    sleep(2);
    $this->assertSession()
      ->elementTextContains('css', $subjectSelectorList, $subject);
    $this->assertSession()
      ->elementTextContains('css', $toAddressSelectorList, $toAddress);

    $this->iClickOn($firstMessageSelector);
    sleep(2);
    $this->assertSession()
      ->elementTextContains('css', $subjectSelectorDetail, $subject);
    $this->assertSession()
      ->elementTextContains('css', $toAddressSelectorDetail, $toAddress);

    if ($action == 'trash') {
      $trashIcon = "button > i.glyphicon-trash";
      $this->iClickOn($trashIcon);
    }
  }

  /**
   * Fill field in paragraph.
   *
   * @When I fill field :arg1 of paragraph :arg2 with :arg3
   */
  public function iFillFieldOfParagraphWith($field, $paragraph, $value) {

    $actualParagraph = intval($paragraph) - 1;
    $fieldName = 'field_paragraphs[' . $actualParagraph . '][subform][field_' . $field . '][0][value]';
    $this->getSession()->getPage()->fillField($fieldName, $value);
    $this->assertSession()->fieldValueEquals($fieldName, $value);
  }

  /**
   * @Then I should see the text :arg1 in paragraph :arg2
   */
  public function iShouldSeeTheTextInParagraphOfType($text, $paragraph) {
    $selector = 'div.paragraphs > div:nth-child(' . $paragraph . ')';
    $this->assertSession()->elementTextContains('css', $selector, $text);
  }

  /**
   * Find field by locator.
   *
   * @Then I should see the field :locator
   */
  public function iSeeField($locator) {
    $el = $this->getSession()->getPage()->findField($locator);

    if (empty($el)) {
      throw new \Exception(sprintf("The field '%s' was not found on the page %s", $locator, $this->getSession()
        ->getCurrentUrl()));
    }

  }

  /**
   * Find field by locator in region.
   *
   * @Then I should see the field :locator in the :region( region)
   */
  public function iSeeFieldInRegion($locator, $region) {

    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);

    if (!$regionObj) {
      throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));

    }

    $el = $regionObj->findField($locator);

    if (empty($el)) {
      throw new \Exception(sprintf("No field with locator '%s' was found in region '%s' on the page %s", $locator, $region, $this->getSession()
        ->getCurrentUrl()));
    }

  }

  /**
   * Add an image to the field with the media popup.
   *
   * @Given I add an image for :field
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function iAddImageFor($field) {

    $buttonId = "edit-field-" . strtolower($field) . "-open-button";
    $checkboxFirstItem = "media_library_select_form[0]";
    $insertButton = "button.media-library-select";

    $this->getSession()->getPage()->pressButton($buttonId);
    $this->minkContext->iWaitForAjaxToFinish();
    $this->getSession()->getPage()->checkField($checkboxFirstItem);
    $this->minkContext->iWaitForAjaxToFinish();
    $this->getSession()->getPage()->find("css", $insertButton)->press();
    $this->minkContext->iWaitForAjaxToFinish();

  }

  /**
   * Add an image to the field with the media popup.
   *
   * @Given I add image :imagenumber to paragraph :paragraphnumber
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function iAddImageToParagraph($imageNumber, $paragraphNumber) {

    $paragraphNumber = strval($paragraphNumber) - 1;

    $buttonSelector = "//*[@name='field_images-media-library-open-button-field_paragraphs-" . $paragraphNumber . "-subform']";

    $checkboxFirstItem = "media_library_select_form[" . $imageNumber . "]";
    $insertButton = "button.media-library-select";

    $this->getSession()->getPage()->find("xpath", $buttonSelector)->press();
    $this->minkContext->iWaitForAjaxToFinish();
    $this->getSession()->getPage()->checkField($checkboxFirstItem);
    $this->minkContext->iWaitForAjaxToFinish();
    $this->getSession()->getPage()->find("css", $insertButton)->press();
    $this->minkContext->iWaitForAjaxToFinish();

  }

  /**
   * Helper function to read file.
   *
   * @param string $file
   *   Name of the file.
   *
   * @return false|string[]
   *   Gives back array with fields/elements.
   */
  public function getFieldsFromFile($file) {
    $filePath = str_replace("/web", "", getcwd()) . "/tests/behat/files/" . $file;
    //    echo("Using file: $filePath\n");

    $fields = array_map('str_getcsv', file($filePath));

    //    print_r($fields);
    return ($fields);
  }

  /**
   * Check elements are shown within element based on input from file.
   *
   * @Given I should see the elements from file :file in the :element element
   */
  public function iCheckElementsFromFileInElement($file, $element) {
    $this->checkElementsFromFile("should", $file, $element);
  }

  /**
   * Check elements are not shown within element based on input from file.
   *
   * @Given I should not see the elements from file :file in the :element element
   */
  public function iCheckElementsFromFileNotInElement($file, $element) {
    $this->checkElementsFromFile("should not", $file, $element);
  }

  /**
   * Check elements are/are not within element based on input from file.
   *
   */
  public function checkElementsFromFile($type, $file, $element) {
    $fields = $this->getFieldsFromFile($file);
    if ($type == 'should') {
      $errorStringPart = 'Could not find';
      $check = FALSE;
    }
    else {
      $errorStringPart = 'Found';
      $check = TRUE;
    }

    $checkElement = $this->getSession()->getPage()->find("css", $element);

    if (!empty($fields)) {
      foreach ($fields as $field) {
        switch ($field[0]) {
          case "field":
            $fieldElement = $checkElement->hasField($field[1]);
            if ($fieldElement === $check) {
              throw new \InvalidArgumentException(sprintf('%s type: %s (locator: %s) in the element (%s)', $errorStringPart, $field[0], $field[1], $element));
            }
            break;
          case "button":
            $button = $checkElement->hasButton($field[1]);
            if ($button === $check) {
              throw new \InvalidArgumentException(sprintf('%s type: %s (locator: %s) in the element (%s)', $errorStringPart, $field[0], $field[1], $element));
            }
            break;
          case "checkbox_on":
            $checkbox_checked = $checkElement->hasCheckedField($field[1]);
            if ($checkbox_checked === $check) {
              throw new \InvalidArgumentException(sprintf('%s type: %s (locator: %s) in the element (%s)', $errorStringPart, $field[0], $field[1], $element));
            }
            break;
          case "checkbox_off":
            $checkbox_unchecked = $checkElement->hasUncheckedField($field[1]);
            if ($checkbox_unchecked === $check) {
              throw new \InvalidArgumentException(sprintf('%s type: %s (locator: %s) in the element (%s)', $errorStringPart, $field[0], $field[1], $element));
            }
            break;
          case "element":
            $element = $checkElement->find("css", $field[1]);
            if ($element == $check) {
              throw new \InvalidArgumentException(sprintf('%s type: %s (locator: %s) in the element (%s)', $errorStringPart, $field[0], $field[1], $element));
            }
            break;
          default:
            throw new \InvalidArgumentException(sprintf('Unknown type: %s (locator: %s) in the element (%s)', $field[0], $field[1], $element));
        }
      }
    }
  }

  /**
   * @When I wait until DWO dashboard is ready
   */
  public function iWaitUntilDwoDashboardIsReady() {
    $this->minkContext->iWaitForAjaxToFinish();
    sleep(2);
  }

  /**
   * @When I visit group member page for :name
   */
  public function iVisitGroupMemberPageFor($storedAs) {
    $page = strval($this->storedFieldValues[$storedAs]);
    printf("Recalling '$storedAs' as '$page'");
    $page = str_replace('members', 'leden', $page);
    $this->visitPath($page);
    sleep(10);
  }

}
