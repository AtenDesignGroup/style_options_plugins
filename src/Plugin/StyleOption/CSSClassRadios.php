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
 *   id = "css_class_radios",
 *   label = @Translation("CSS Class Radios")
 * )
 */
class CSSClassRadios extends CssClass implements TrustedCallbackInterface {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $form = parent::buildConfigurationForm($form, $form_state);
    $form['css_class']['#type'] = 'radios';
    $form['css_class']['#after_build'] = [
      [$this, 'afterBuildRadios'],
    ];
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
    array_walk($classes, function (&$option) {
      $option = explode(' ', $option['class'] ?? '');
    });
    foreach (Element::children($element) as $key) {
      if ($this->getConfiguration('hide_radios')) {
        $element[$key]['#attributes']['class'][] = 'visually-hidden';
      }
      $element[$key]['#wrapper_attributes']['class'] = $classes[$key] ?? [];
    }
    // @todo Replace this with the ability to reference a library
    if ($this->getConfiguration('style')) {
      $style = [
        '#type' => 'inline_template',
        '#template' => '<style>{{ style }}</style>',
        '#context' => [
          'style' => $this->getConfiguration('style'),
        ],
      ];
      $element['css_class']['#prefix'] = \Drupal::service('renderer')->render($style);
    }

    $class_name = 'so--' . str_replace('_', '-', $this->getConfiguration('option_id'));
    $element['#attributes']['class'] = [
      'container-inline',
      'so--css-class-radios',
      $class_name,
    ];
    if ($this->getConfiguration('hide_radios')) {
      $element['#attributes']['class'][] = 'hide-radios';
    }
    if ($this->getConfiguration('hide_labels')) {
      $element['#attributes']['class'][] = 'hide-labels';
    }
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
