<?php

declare(strict_types=1);

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options_plugins\Plugin\StyleOption\Property;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "boxsize",
 *   label = @Translation("Box Sizing"),
 * )
 */
class BoxSize extends Property {
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
          '#states' => [
            'disabled' => [
              ':input[name*="[boxsize][' . $property . '][lock][all]"]' => ['checked' => TRUE],
            ],
            'checked' => [
              ':input[name*="[boxsize][' . $property . '][lock][all]"]' => ['checked' => TRUE],
            ],
          ],
          '#attributes' => [
            'title' => $this->t('Lock @property along the x axis', [
              '@property' => $property,
            ])
          ],
        ],
        'y' => [
          '#states' => [
            'checked' => [
              ':input[name*="[boxsize][' . $property . '][lock][all]"]' => ['checked' => TRUE],
            ],
            'disabled' => [
              ':input[name*="[boxsize][' . $property . '][lock][all]"]' => ['checked' => TRUE],
            ],
          ],
          '#attributes' => [
            'title' => $this->t('Lock @property along the y axis', [
              '@property' => $property,
            ])
          ],
        ],
        'all' => [
          '#attributes' => [
            'title' => $this->t('Lock @property along all axes', [
              '@property' => $property,
            ])
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

        switch ($direction) {
          case 'right':
            $element['#states'] = [
              'disabled' => [
                ':input[name*="[boxsize][' . $property . '][lock][all]"]' => ['checked' => TRUE],
              ],
            ];
            break;
          case 'bottom':
            $element['#states'] = [
              'disabled' => [
                ':input[name*="[boxsize][' . $property . '][lock][y]"]' => ['checked' => TRUE],
              ],
            ];
            break;
          case 'left':
            $element['#states'] = [
              'disabled' => [
                ':input[name*="[boxsize][' . $property . '][lock][x]"]' => ['checked' => TRUE],
              ],
            ];
            break;
        }

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
