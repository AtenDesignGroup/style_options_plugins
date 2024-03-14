<?php

declare(strict_types=1);

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "template_suggestion",
 *   label = @Translation("Property")
 * )
 */
class TemplateSuggestion extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $form['template_suggestion'] = [
      '#type' => 'textfield',
      '#title' => $this->getLabel(),
      '#default_value' => $this->getValue('template_suggestion') ?? $this->getDefaultValue(),
      '#wrapper_attributes' => [
        'class' => [$this->getConfiguration()['template_suggestion'] ?? ''],
      ],
      '#attributes' => [
        'class' => ['me-style-option-tpl-suggestion'],
      ],
      '#description' => $this->getConfiguration('description'),
    ];

    if ($this->hasConfiguration('options')) {
      $form['template_suggestion']['#type'] = 'select';
      $options = $this->getConfiguration()['options'];
      $hide_fields = array_map(function ($option) {
        return $option['hide_fields'] ?? [];
      }, $options);
      if (
        class_exists('\Drupal\image_radios\Element\ImageRadios') &&
        count(array_filter($options, function ($option) {
          return isset($option['image']);
        }))) {

        $form['template_suggestion']['#type'] = 'image_radios';
      }
      else {
        array_walk($options, function (&$option) {
          $option = $option['label'];
        });
        if ($this->hasConfiguration('multiple')) {
          $form['template_suggestion']['#multiple'] = TRUE;
        }
      }
      $form['#attached']['drupalSettings']['mercuryEditorStyleOptions']['hideFields'] = $hide_fields;
      $form['#attached']['library'][] = 'style_options_plugins/style_options';
      $form['template_suggestion']['#options'] = $options;
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $value = $this->getValue('template_suggestion') ?? NULL;
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
