<?php

namespace Drupal\style_options_plugins\Element;

use Drupal\Core\Render\Element\Radios;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Provides a layout selection element.
 *
 * Extends the radios form element and adds thumbnail previews for layouts.
 *
 * Usage example:
 * @code
 * $form['choose'] = [
 *   '#type' => 'classy_radios',
 *   '#title' => t('Color mode'),
 *   '#options' => [
 *     'option_1' => [
 *       'classes' => 'color-mode--light bg-color--white',
 *       'label' => 'Light Mode',
 *     ],
 *     'option_2' => [
 *       'classes' => 'color-mode--dark bg-color--dark-gray',
 *       'label' => 'Dark Mode',
 *     ],
 *   ],
 *   '#default_value' => 'option_1',
 * ];
 * @endcode
 *
 * @RenderElement("classy_radios")
 */
class ClassyRadios extends Radios {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info += [
      '#width' => 50,
      '#height' => 50,
      '#stroke_width' => 1,
      '#padding' => 0,
    ];
    $info['#process'][] = [__CLASS__, 'processClassyRadios'];
    return $info;
  }

  /**
   * Add layout thumbnail previews.
   */
  public static function processClassyRadios(
    &$element,
    FormStateInterface $form_state,
    &$complete_form) {
    foreach (Element::children($element) as $key) {
      $extra_wrapper_classes = explode(' ', $element[$key]['#title']['classes'] ?? '');
      $extra_wrapper_attributes = $element[$key]['#title']['attributes'] ?? [];
      $title = new FormattableMarkup('<span class="classy-radios__item-label">@label</span>', [
        '@label' => $element[$key]['#title']['label'],
      ]);
      $element[$key]['#title'] = $title;
      $element[$key]['#wrapper_attributes']['class'][] = 'classy-radios__item';
      $element[$key]['#wrapper_attributes']['class'] = array_merge(
        $element[$key]['#wrapper_attributes']['class'],
        $extra_wrapper_classes
      );
      $element[$key]['#wrapper_attributes'] = array_merge(
        $element[$key]['#wrapper_attributes'],
        $extra_wrapper_attributes
      );
      $element[$key]['#attributes']['class'][] = 'visually-hidden';
    }
    $element['#attributes']['class'][] = 'classy-radios-wrapper';
    $element['#attached']['library'][] = 'style_options_plugins/classy_radios';
    $element['#wrapper_attributes'] = ['class' => ['classy-radios']];
    return $element;
  }

}
