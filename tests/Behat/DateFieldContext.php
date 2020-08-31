<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Mink\Element\NodeElement;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Traits\UtilityTrait;

/**
 * Defines date field step definitions.
 */
class DateFieldContext extends RawDrupalContext {

  use UtilityTrait;

  /**
   * Fills a date or time field at a datetime widget.
   *
   * When I fill in "Start date" with the date "29-08-2016".
   * When I fill in "Start date" with the time "06:59:00AM".
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
  public function fillDateField(string $field_group, string $date_component, string $value): void {
    $field_selectors = $this->findDateFields($field_group);
    $field_selector = reset($field_selectors);
    $field_selector->fillField(ucfirst($date_component), $value);
  }

  /**
   * Fills a date or time field at a daterange widget.
   *
   * When I fill in "Start date" of "Event date" with the date "29-08-2016".
   * When I fill in "End date" of "Event date" with the time "06:59:00AM".
   *
   * @param string $field_item
   *   The date field item inside the field component.
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   *
   * @When I fill in :field_item of :field_group with the :date_component :value
   */
  public function fillDateRangeField(string $field_item, string $field_group, string $date_component, string $value): void {
    $field_selectors = $this->findDateFields($field_group, 'Date range', '.field--widget-daterange-default');
    $field_selector = reset($field_selectors);

    if ($field_item === 'End date') {
      $field_selector = $field_selector->findAll('css', 'div[class*="end-value-' . $date_component . '"]');
      $field_selector = reset($field_selector);
    }

    $field_selector->fillField(ucfirst($date_component), $value);
  }

  /**
   * Set the date and time value of a datelist date range widget.
   *
   * When I set "22-02-2019 02:30" as the "Start date" of "My date field"
   *
   * @param string $field_item
   *   The date field item inside the field component.
   * @param string $field_group
   *   The field component's label.
   * @param string $value
   *   The value of the field.
   *
   * @When I set :value as the :field_item of :field_group
   */
  public function fillDateRangeSelectListField(string $field_item, string $field_group, string $value): void {
    $field_selectors = $this->findDateFields($field_group, 'Date range datelist', '.field--widget-daterange-datelist');

    $field_selector = reset($field_selectors);
    if ($field_item === 'End date') {
      $field_selector = $field_selector->findAll('css', 'div[id*="end-value"]');
      $field_selector = reset($field_selector);
    }

    $date = DrupalDateTime::createFromFormat('d-m-Y H:i', $value, 'UTC');
    $date_components = [
      'Day' => 'd',
      'Month' => 'n',
      'Year' => 'Y',
      'Hour' => 'G',
      'Minute' => 'i',
    ];
    foreach ($date_components as $date_component => $date_component_format) {
      // For avoiding usage of minutes with leading zero sign,
      // we use casting to integer.
      $field_selector->selectFieldOption($date_component, (integer) $date->format($date_component_format));
    }
  }

  /**
   * Set the date and time value of a date list widget.
   *
   * When I set "Field" to the date "22-02-2019"
   * When I set "Field" to the date "22-02-2019 14:30" using format "d-m-Y H:i"
   *
   * @param string $field
   *   The label of the field.
   * @param string $value
   *   The value of the field.
   * @param string $format
   *   The dateformat of the field.
   *
   * @When I set :field to the date :value
   * @When I set :field to the date :value using format :format
   */
  public function fillDateSelectListField(string $field, string $value, string $format = 'd-m-Y'): void {
    $field_selectors = $this->findDateFields($field, 'Datetime list', '.field--widget-datetime-datelist');
    $field_selector = reset($field_selectors);

    $date = DrupalDateTime::createFromFormat($format, $value, 'UTC');
    $date_components = [
      'Year' => 'Y',
      'Month' => 'm',
      'Day' => 'd',
    ];
    if (strpos($format, 'G') !== FALSE) {
      $date_components['Hour'] = 'G';
    }
    if (strpos($format, 'i') !== FALSE) {
      $date_components['Minute'] = 'i';
    }
    foreach ($date_components as $date_component => $date_component_format) {
      // For avoiding usage of minutes with leading zero sign,
      // we use casting to integer.
      $field_selector->selectFieldOption($date_component, (integer) $date->format($date_component_format));
    }
  }

  /**
   * Finds a Date field.
   *
   * @param string $field
   *   The field name.
   * @param string $label
   *   The field label.
   * @param string $selector
   *   The field CSS selector.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   */
  protected function findDateFields(string $field, string $label = 'Date', string $selector = '.field--widget-datetime-default'): array {
    $field_selectors = $this->getSession()->getPage()->findAll('css', $selector);
    $field_selectors = array_filter($field_selectors, function (NodeElement $field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });

    if (empty($field_selectors)) {
      throw new \Exception(sprintf('%s field "%s" was not found.', $label, $field));
    }
    if (count($field_selectors) > 1) {
      throw new \Exception('More than one elements were found.');
    }
    return $field_selectors;
  }

}
