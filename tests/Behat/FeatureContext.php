<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines step definitions that are generally useful in this project.
 */
class FeatureContext extends RawDrupalContext {

  /**
   * Fills a date or time field at a datetime widget.
   *
   * Example: When I fill in "Start date" with the date "29-08-2016".
   * Example: When I fill in "Start date" with the time "26:59:00".
   *
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   *
   * @throws \Exception
   *    Thrown when more than one elements match the given field in the given
   *    field group.
   *
   * @When I fill in :field_group with the :date_component :value
   */
  public function fillDateField($field_group, $date_component, $value) {
    $field_selectors = $this->findDateFields($field_group);
    if (count($field_selectors) > 1) {
      throw new \Exception("More than one elements were found.");
    }
    $field_selector = reset($field_selectors);
    $field_selector->fillField(ucfirst($date_component), $value);
  }

  /**
   * Finds a datetime field.
   *
   * @param string $field
   *   The field name.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   *
   * @throws \Exception
   *   Thrown when the field was not found.
   */
  public function findDateFields($field) {
    $field_selectors = $this->getSession()->getPage()->findAll('css', '.field--widget-datetime-timestamp');
    $field_selectors = array_filter($field_selectors, function ($field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });
    if (empty($field_selectors)) {
      throw new \Exception("Date field {$field} was not found.");
    }
    return $field_selectors;
  }

  /**
   * Check that a link is pointing to a specific target.
   *
   * @Then I should see the link :link pointing to :url
   *
   * @throws \Exception
   *   If link cannot be found or target is incorrect.
   */
  public function assertLinkWithHref($link, $url) {
    $page = $this->getSession()->getPage();

    $result = $page->findLink($link);
    if (empty($result)) {
      throw new \Exception("No link '{$link}' on the page");
    }

    $href = $result->getAttribute('href');
    if ($url != $href) {
      throw new \Exception("The link '{$link}' points to '{$href}'");
    }

  }

  /**
   * Checks that the AV Portal photo is rendered.
   *
   * @param string $title
   *   The photo title.
   * @param string $src
   *   The final photo source.
   *
   * @Then I should see the AV Portal photo :title with source :src
   */
  public function assertAvPortalPhoto(string $title, string $src): void {
    $media = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $title]);
    if (!$media) {
      throw new \Exception(sprintf('The media named "%s" does not exist', $title));
    }

    $this->assertSession()->elementAttributeContains('css', 'img.avportal-photo', 'src', $src);
  }

}
