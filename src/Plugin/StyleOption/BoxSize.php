<?php

declare(strict_types=1);

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOption\CssClass;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "boxsize",
 *   label = @Translation("Box Sizing"),
 * )
 */
class BoxSize extends CssClass {
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

  /**
   * Default value key for margin and padding.
   *
   * @var string
   */
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
      '#title' => $this->getConfiguration('label') ?? $this->t('Box Sizing'),
      '#tree' => TRUE,
      '#description' => $this->getConfiguration('description'),
      '#attributes' => [
        'class' => [
          'so-boxsize',
        ],
      ],
      '#attached' => [
        'library' => [
          'style_options_plugins/boxsize',
        ],
      ],
    ];

    foreach ($this->properties as $property => $property_info) {
      $form['boxsize'][$property] = [
        '#type' => 'fieldset',
        '#title' => $this->t('@label', ['@label' => $property_info['label']]),
        '#attributes' => [
          'class' => [
            'js-so-boxsize',
            'so-boxsize__property',
            'so-boxsize__property--' . $property,
          ],
        ],
      ];

      $form['boxsize'][$property]['lock'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Lock'),
        '#options' => [
          'x' => $this->t('X'),
          'y' => $this->t('Y'),
          'all' => $this->t('All'),
        ],
        '#default_value' => [
          'x' => $this->getValue('boxsize')[$property]['lock']['x'] ?? FALSE,
          'y' => $this->getValue('boxsize')[$property]['lock']['y'] ?? FALSE,
          'all' => $this->getValue('boxsize')[$property]['lock']['all'] ?? FALSE,
        ],
        '#attributes' => [
          'class' => [
            'container-inline',
            'so-boxsize__lock-wrapper',
            'so-boxsize__lock-wrapper--' . $property,
          ],
        ],
        'x' => [
          '#attributes' => [
            'title' => $this->t('Lock @property along the x axis', [
              '@property' => $property,
            ]),
          ],
        ],
        'y' => [
          '#attributes' => [
            'title' => $this->t('Lock @property along the y axis', [
              '@property' => $property,
            ]),
          ],
        ],
        'all' => [
          '#attributes' => [
            'title' => $this->t('Lock @property along all axes', [
              '@property' => $property,
            ]),
          ],
        ],
      ];

      foreach ($this->directions as $direction => $direction_info) {

        $element = [
          '#type' => 'textfield',
          '#title' => $this->t('@label', ['@label' => $direction_info['label']]),
          '#default_value' => $this->getValue('boxsize')[$property][$direction] ?? $this->getDefaultValue(),
          '#title_display' => 'invisible',
          '#attributes' => [
            'class' => [
              'so-boxsize__input',
              'so-boxsize__input--' . $direction,
              'so-boxsize__input--' . $property,
            ],
          ],
        ];

        if ($this->hasConfiguration('options')) {
          $element['#type'] = 'select';
          $element['#attributes']['title'] = $this->t('@label @property', [
            '@label' => $direction_info['label'],
            '@property' => $property,
          ]);
          $options = $this->getConfiguration()['options'][$property];

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
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $values = $form_state->cleanValues()->getValues();

    // Handle locked values.
    foreach (['margin', 'padding'] as $property) {
      $property_values = &$values['boxsize'][$property];
      if ($property_values['lock']['all']) {
        $property_values['left']
          = $property_values['right']
            = $property_values['bottom']
              = $property_values['top'];
      }
      if ($property_values['lock']['x']) {
        $property_values['right'] = $property_values['left'];
      }
      if ($property_values['lock']['y']) {
        $property_values['bottom'] = $property_values['top'];
      }
    }
    $this->setValues($values);
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
        if (!empty($value[$property][$direction]) && $value[$property][$direction] !== 'default' && !is_array($value[$property][$direction])) {
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
