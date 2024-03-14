<?php

declare(strict_types=1);

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the view mode option plugin.
 *
 * @StyleOption(
 *   id = "display_mode",
 *   label = @Translation("Display mode")
 * )
 */
class DisplayMode extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $plugin_id = $this->getPluginId();
    $element[$plugin_id] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue($plugin_id) ?? $this->getDefaultValue(),
      '#attributes' => [
        'class' => ['me-style-option-view-mode'],
      ],
      '#description' => $this->getConfiguration('description'),
    ];
    if ($this->hasConfiguration('options')) {
      $element[$plugin_id]['#type'] = 'select';
      $options = $this->getConfiguration()['options'];
      if (
        class_exists('\Drupal\image_radios\Element\ImageRadios') &&
        count(array_filter($options, function ($option) {
          return isset($option['image']);
        }))) {
        $element[$plugin_id]['#type'] = 'image_radios';
      }
      else {
        array_walk($options, function (&$option) {
          $option = $option['label'];
        });
        if ($this->hasConfiguration('multiple')) {
          $element[$plugin_id]['#multiple'] = TRUE;
        }
      }
      $element[$plugin_id]['#attached']['library'][] = 'style_options_plugins/style_options';
      $element[$plugin_id]['#options'] = $options;
    }
    return $element;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    return $build;
  }

  /**
   * Get the attribute option default value.
   *
   * @return mixed
   *   The attribute option default value.
   */
  protected function getDefaultValue() {
    $config = $this->getConfiguration();
    $request = \Drupal::request();
    return $this->getConfiguration('default');
  }

}
