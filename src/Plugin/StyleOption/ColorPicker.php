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

    $form = parent::buildConfigurationForm($form, $form_state);
    $options = $this->getConfiguration('options') ?? [];
    $styles = '';
    foreach ($options as $key => $option) {
      $var = $option['css_var'];
      $styles .= "\n  .so--color-picker [value=$key]+label { background-color: $var; }";
    }
    if (!empty($styles)) {
      $style_tag = [
        '#type' => 'inline_template',
        '#template' => "<style>{{ styles }} \n</style>",
        '#context' => [
          'styles' => $styles,
        ],
      ];
      $form['css_class']['#prefix'] = \Drupal::service('renderer')->render($style_tag);
    }

    $form['css_class']['#type'] = 'radios';
    $form['css_class']['#after_build'] = [
      [$this, 'afterBuildRadios'],
    ];
    $libary = $this->getConfiguration('library');
    if (!empty($libary)) {
      $form['css_class']['#attached']['library'][] = $libary;
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
    $classes = $this->getConfiguration('options') ?? [];
    foreach (Element::children($element) as $key) {
      $element[$key]['#attributes']['class'][] = 'visually-hidden';
    }
    $class_name = 'so--' . str_replace('_', '-', $this->getConfiguration('option_id'));
    $element['#attributes']['class'] = [
      'container-inline',
      'so--color-picker',
      $class_name,
    ];
    $element['#attributes']['class'][] = 'hide-radios';
    $element['#attributes']['class'][] = 'hide-labels';
    $element['#attached']['library'][] = 'style_options_plugins/css_class_radios';
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

}
