<?php

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\style_options\Plugin\StyleOption\CssClass;

/**
 * Extends the CssClass plugin to provide a radios form element.
 *
 * @StyleOption(
 *   id = "color_picker",
 *   label = @Translation("Color Picker")
 * )
 */
class ColorPicker extends CssClass implements TrustedCallbackInterface {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $plugin_id = $this->getPluginId();
    $form = parent::buildConfigurationForm($form, $form_state);
    $options = $this->getConfiguration('options') ?? [];
    $styles = '';
    foreach ($options as $key => $option) {
      $var = $option['value'] ?? NULL;
      if ($var) {
        $styles .= "\n  .so-color-picker [value=$key]+label { background-color: $var; }";
      }
    }
    if (!empty($styles)) {
      $style_tag = [
        '#type' => 'inline_template',
        '#template' => "<style>{{ styles }} \n</style>",
        '#context' => [
          'styles' => $styles,
        ],
      ];
      $form[$plugin_id]['#prefix'] = \Drupal::service('renderer')->render($style_tag);
    }

    $form[$plugin_id]['#type'] = 'radios';
    $form[$plugin_id]['#after_build'] = [
      [$this, 'afterBuildRadios'],
    ];
    $libary = $this->getConfiguration('library');
    if (!empty($libary)) {
      $form[$plugin_id]['#attached']['library'][] = $libary;
    }
    return $form;
  }

  /**
   * After build callback for radios element.
   *
   * @param array $element
   *   The radios element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   Returns the radios element with classes added.
   */
  public function afterBuildRadios(array $element, FormStateInterface $form_state) {
    $class_name = 'so-' . str_replace('_', '-', $this->getConfiguration('option_id'));
    $element['#attributes']['class'] = [
      'container-inline',
      'so-color-picker',
      $class_name,
    ];
    $options = $this->getConfiguration('options') ?? [];
    foreach (Element::children($element) as $key) {
      $element[$key]['#label_attributes']['title'] = $options[$key]['label'] ?? '';
    }
    $element['#attributes']['class'][] = 'hide-radios';
    $element['#attributes']['class'][] = 'hide-labels';
    $element['#attached']['library'][] = 'style_options_plugins/color_picker';
    return $element;
  }

  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return [
      'afterBuildRadios',
    ];
  }

  /**
   * {@inheritDoc}
   *
   * In addition to adding the specified class to the build array, add the
   * id, value, and label in case it is needed for theming.
   */
  public function build(array $build) {
    $build = parent::build($build);
    $plugin_id = $this->getPluginId();
    $value = $this->getValue($plugin_id) ?? NULL;
    $option_definition = $this->getConfiguration('options');
    $options = $option_definition[$value] ?? [];
    if (!empty($value)) {
      $build['#' . $this->getOptionId()] = [
        'id' => $value,
      ] + $options;
    }
    return $build;
  }

}
