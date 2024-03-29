<?php

declare(strict_types=1);

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options_plugins\Plugin\StyleOption\Property;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "boxsizing",
 *   label = @Translation("Box Sizing"),
 * )
 */
class MercuryBoxSize extends Property {
  /**
   * @var array
   *   The directions that the property can be applied to.
   */
  protected $directions = [
    'top' => [
      'label' => 'Top',
      'prefix' => 't-',
    ],
    'right' => [
      'label' => 'Right',
      'prefix' => 'r-',
    ],
    'bottom' => [
      'label' => 'Bottom',
      'prefix' => 'b-',
    ],
    'left' => [
      'label' => 'Left',
      'prefix' => 'l-',
    ],
  ];

  /**
   * @var array
   *   The properties available to set.
   */
  protected $properties = [
    'margin' => [
      'label' => 'Margin',
      'prefix' => 'u-m',
    ],
    'padding' => [
      'label' => 'Padding',
      'prefix' => 'u-p',
    ],
  ];

  protected $defaultValue = 'default';

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    // Add a fieldset to group the column form elements together.
    $form['boxsize'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Box Size'),
      '#tree' => TRUE,
      '#description' => $this->getConfiguration('description'),
    ];

    foreach ($this->properties as $property => $property_info) {
      $form['boxsize'][$property] = [
        '#type' => 'fieldset',
        '#title' => $this->t('@label', ['@label' => $property_info['label']]),
        '#attributes' => array('class' => array('container-inline')),
      ];

      foreach ($this->directions as $direction => $direction_info) {

        $element = [
          '#type' => 'textfield',
          '#title' => $this->t('@label', ['@label' => $direction_info['label']]),
          '#default_value' => $this->getValue('boxsize')[$property][$direction] ?? $this->getDefaultValue(),
        ];

        if ($this->hasConfiguration('options')) {
          $element['#type'] = 'select';
          $options = $this->getConfiguration()['options'];

          array_walk($options, function (&$option) {
            $option = $option['label'];
          });

          $element['#options'] = $options;
        }
        $form['boxsize'][$property][$direction] = $element;
      }
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $value = $this->getValue('boxsize') ?? NULL;

    if (empty($value)) {
      return $build;
    }

    $classes = [];

    foreach ($this->properties as $property => $property_info) {
      foreach ($this->directions as $direction => $direction_info) {
        if (!empty($value[$property][$direction]) && $value[$property][$direction] !== 'default') {
          $classes[] = $property_info['prefix'] . $direction_info['prefix'] . $value[$property][$direction];
        }
      }
    }

    if (!empty($classes)) {
      // Ensure $classes is an array so it can be easily manipulated later.
      $classes = is_array($classes) ? $classes : explode(' ', $classes);
      foreach ($classes as $class) {
        $build['#attributes']['class'][] = $class;
      }
    }

    return $build;
  }

}
