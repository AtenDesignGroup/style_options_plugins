<?php

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\Core\Render\Element;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'ThemeColors' style option.
 *
 * @StyleOption(
 *   id = "theme_colors",
 *   label = @Translation("Theme Colors"),
 *   description = @Translation("Select a theme color."),
 *   category = @Translation("Style Options"),
 * )
 */
class ThemeColors extends CSSClassRadios {

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
    $element = parent::afterBuildRadios($element, $form_state);
    $config = $this->getConfiguration();
    $style = '';
    $css = '';
    foreach (Element::children($element) as $key) {
      $option = $config['options'][$key] ?? [];
      if ($option['theme_setting'] && $config['theme'] && $option['css_property']) {
        $theme_setting = theme_get_setting($option['theme_setting'], $config['theme']);
        $style .= $option['css_property'] . ': ' . $theme_setting . '; ';
        $css .= '.' . $option['class'] . ' label { background-color: var(' . $option['css_property'] . "); }\n";
      }
      if ($key == 'none') {
        $css .= '.' . $option['class'] . <<<EOT
 label {
  --line-width: 3px;
  border: 1px solid #666;
  background-color: white;
  background-image: linear-gradient(
    -45deg,
    transparent calc(50% - var(--line-width) / 2),
    red 0,
    red calc(50% + var(--line-width) / 2),
    transparent 0
  );
}
EOT;
      }
    }
    if ($style) {
      $element['#attributes']['style'] = $style;
    }
    if ($css) {
      $style_tag = [
        '#type' => 'inline_template',
        '#template' => '<style>{{ style }}</style>',
        '#context' => [
          'style' => $css,
        ],
      ];
      $element['#prefix'] = \Drupal::service('renderer')->render($style_tag);
    }
    return $element;
  }

}
