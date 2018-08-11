<?php

namespace Drupal\twig_datetime_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeFormatterBase;

/**
 * Plugin implementation of the 'Custom Twig' formatter for 'datetime' fields.
 *
 * @FieldFormatter(
 *   id = "datetime_custom_twig",
 *   label = @Translation("Custom Twig"),
 *   field_types = {
 *     "datetime"
 *   }
 *)
 */
class DateTimeCustomTwigFormatter extends DateTimeFormatterBase {
  
  protected $default_twig = '{{ "now"|date("F j - g:i a")|replace({"am" : "a.m.", "pm" : "p.m.", ":00" : ""})|replace({"12 a.m." : "midnight", "12 p.m." : "noon"}) }}';
  
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'date_twig_format' => ''
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // @todo Evaluate removing this method in
    // https://www.drupal.org/node/2793143 to determine if the behavior and
    // markup in the base class implementation can be used instead.
    $elements = [];
    
    foreach ($items as $delta => $item) {
      if (!empty($item->date)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item->date;
        $elements[$delta] = $this->formatDate($date);
      }
    }
    
    return $elements;
  }
  
  /**
   * {@inheritdoc}
   */
  protected function formatDate($date) {
    return $this->applyCustomTwigFormatting($date);
  }
  
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['date_twig_format'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Twig date/time format'),
      '#description' => $this->t('The default custom twig format will replace a blank field.
               See the <a href="http://php.net/manual/function.date.php" target="_blank">PHP manual</a> for available datetime options and checkout the <a href="https://twig.symfony.com/doc/2.x/" target="_blank">Twig Docs</a>
               for info on the "date" and "replace" filters. You can test your twig at <a href="https://twigfiddle.com/" target="_blank">Twig Fiddle</a>.<br>
               <strong>The word "now" in the twig format will be replaced with the date field timestamp. <br>
               Default twig: </strong> ') . $this->default_twig,
      '#default_value' => !empty(trim($this->getSetting('date_twig_format'))) ? trim($this->getSetting('date_twig_format')) : $this->default_twig,
    ];
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->applyCustomTwigFormatting();
  }
  
  /**
   * @param null $date
   * @return mixed
   */
  public function applyCustomTwigFormatting($date = NULL) {
    // Get custom twig format for this field.
    $format = !empty(trim($this->getSetting('date_twig_format'))) ? trim($this->getSetting('date_twig_format')) : $this->default_twig;
    
    if ($date) {
      // Swap iso timestamp if date is passed in.
      $iso_time = $this->dateFormatter->format($date->getTimestamp(), 'datetime_custom_twig', 'c');
      $format = str_replace('now', $iso_time, $format);
    }
  
    // Build inline twig template using the custom format.
    $build['rendered_datetime_custom_twig_format'] = [
      '#type' => 'inline_template',
      '#template' => "$format",
    ];
  
    return $build;
  }
  
}
