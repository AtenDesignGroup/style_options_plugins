<?php

declare(strict_types=1);

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;

/**
 * Define the class attribute option plugin.
 *
 * @StyleOption(
 *   id = "image_style",
 *   label = @Translation("Image style")
 * )
 */
class ImageStyle extends StyleOptionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state): array {

    $element = [];
    $options = [];
    $plugin_id = $this->getPluginId();
    $styles = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple();
    foreach ($styles as $style) {
      $options[$style->id()] = $style->label();
    }
    $value = $this->getValue($plugin_id) ?? $this->getDefaultValue() ?? key($options);
    $element['preview'] = [
      '#process' => [[$this, 'processPreview']],
      '#theme' => 'image_style_preview',
      '#style' => $styles[$value],
      '#all_styles' => $styles,
      '#prefix' => '<div id="image-style-preview">',
      '#suffix' => '</div>',
    ];
    $element[$plugin_id] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->getLabel(),
      '#default_value' => $value,
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => 'image-style-preview',
      ],
    ];
    $element['#attached']['library'][] = 'image/admin';

    return $element;
  }

  /**
   * Process callback for the preview element.
   *
   * @param array $element
   *   The element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The processed element.
   */
  public function processPreview(array $element, FormStateInterface $form_state) {
    $path = $element['#parents'];
    array_pop($path);
    $path[] = 'image_style';
    if ($value = $form_state->getValue($path)) {
      $element['#style'] = $element['#all_styles'][$value];
    }
    return $element;
  }

  /**
   * Ajax callback for the preview element.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The preview element.
   */
  public function ajaxCallback(array $form, FormStateInterface $form_state) {
    return $form['behavior_plugins']['style_options'][$this->getPluginId()]['preview'];
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $plugin_id = $this->getPluginId();
    $value = $this->getValue($plugin_id) ?? NULL;
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

}
