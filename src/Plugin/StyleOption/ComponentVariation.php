<?php

declare(strict_types=1);

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "component_variation",
 *   label = @Translation("Property")
 * )
 */
class ComponentVariation extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $form['component_variation'] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('component_variation') ?? $this->getDefaultValue(),
      '#wrapper_attributes' => [
        'class' => [$this->getConfiguration()['component_variation'] ?? ''],
      ],
      '#attributes' => [
        'class' => ['me-style-option-tpl-suggestion'],
      ],
      '#description' => $this->getConfiguration('description'),
    ];

    if ($this->hasConfiguration('options')) {

      $form['component_variation']['#type'] = 'select';
      $options = $this->getConfiguration()['options'] ?? [];
      if (
        class_exists('\Drupal\image_radios\Element\ImageRadios') &&
        count(array_filter($options, function ($option) {
          return isset($option['image']);
        }))) {

        $form['component_variation']['#type'] = 'image_radios';
      }
      else {
        array_walk($options, function (&$option) {
          $option = $option['label'];
        });
        if ($this->hasConfiguration('multiple')) {
          $form['component_variation']['#multiple'] = TRUE;
        }
      }
      $form['#attached']['library'][] = 'style_options_plugins/style_options';
      $form['component_variation']['#options'] = $options;

    }
    $submit_name = $this->getConfiguration()['option_id'] . '-' . $this->getConfiguration()['plugin'] . '-submit';
    $form['component_variation']['#ajax'] = [
      'wrapper' => $form_state->getCompleteForm()['#id'],
      'trigger_as' => ['name' => $submit_name],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
      '#attributes' => [
        'class' => ['visually-hidden'],
      ],
      '#name' => $submit_name,
      '#submit' => [
        [$form_state->getFormObject(), 'submitForm'],
        [$this, 'submitForm'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => $form_state->getCompleteForm()['#id'],
      ],
    ];
    return $form;
  }

  /**
   * Ajax callback for the component variation form element.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
   */
  public function ajaxCallback(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Submit callback.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $value = $this->getValue('component_variation') ?? NULL;
    $option_definition = $this->getConfiguration('options');
    if (is_array($value)) {
      $property = implode(' ',
        array_map(function ($index) use ($option_definition) {
          return $option_definition[$index]['value'] ?? NULL;
        }, $value)
      );
    }
    else {
      $property = $value ?? NULL;
    }
    if (!empty($property)) {
      $build['#' . $this->getOptionId()] = $property;
    }
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
    if ($request->get($config['option_id'])) {
      return $request->get($config['option_id']);
    }
    return $this->getConfiguration('default');
  }

}
