<?php

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\style_options\StyleOptionStyleTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a background style option plugin.
 *
 * @StyleOption(
 *   id = "background",
 *   label = @Translation("Background")
 * )
 */
class Background extends StyleOptionPluginBase {

  use StyleOptionStyleTrait;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    Renderer $renderer,
    EntityTypeManagerInterface $entityTypeManager,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
    protected ModuleHandlerInterface $moduleHandler) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $renderer, $entityTypeManager);
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('renderer'),
        $container->get('entity_type.manager'),
        $container->get('file_url_generator'),
        $container->get('module_handler')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['bg_image'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Background Image'),
      '#collapsible' => TRUE,
      '#attributes' => [
        'class' => ['so-bg-image__wrapper'],
      ]
    ];
    if ($this->moduleHandler->moduleExists('media_library_form_element')) {
      $form['bg_image']['media'] = [
        '#type' => 'media_library',
        '#title' => $this->t('Media'),
        '#description' => $this->t('Media'),
        '#allowed_bundles' => [$this->getConfiguration('background_image')['bundle'] ?? 'image'],
        '#default_value' => $this->getValue('bg_image')['media'] ?? NULL,
      ];
    }
    else {
      $form['fid'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Background Image'),
        '#default_value' => $this->getValue('bg_image')['fid'] ?? $this->getDefaultValue(),
        '#upload_location' => 'public://',
        '#upload_validators' => [
          'file_validate_extensions' => ['gif png jpg jpeg'],
        ],
        '#wrapper_attributes' => [
          'class' => [$this->getConfiguration('css_class')],
        ],
      ];
    }
    $form['bg_image']['background_position'] = [
      '#type' => 'radios',
      '#title' => $this->t('Position'),
      '#options' => [
        'left top' => $this->t('Left Top'),
        'center top' => $this->t('Center Top'),
        'right top' => $this->t('Right Top'),
        'left center' => $this->t('Left Center'),
        'center' => $this->t('Center'),
        'right center' => $this->t('Right Center'),
        'left bottom' => $this->t('Left Bottom'),
        'center bottom' => $this->t('Center Bottom'),
        'right bottom' => $this->t('Right Bottom'),
      ],
      '#attributes' => [
        'class' => ['so-bg-image__position'],
      ],
      '#default_value' => $this->getValue('bg_image')['background_position'] ?? 'center',
    ];
    $form['bg_image']['background_repeat'] = [
      '#type' => 'radios',
      '#title' => $this->t('Repeat'),
      '#options' => [
        'no-repeat' => $this->t('No repeat'),
        'repeat' => $this->t('Repeat'),
        'repeat-x' => $this->t('Repeat X'),
        'repeat-y' => $this->t('Repeat Y'),
      ],
      '#default_value' => $this->getValue('bg_image')['background_repeat'] ?? 'no-repeat',
    ];
    $form['bg_image']['background_attachment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Attachment'),
      '#options' => [
        'not_fixed' => $this->t('Not Fixed'),
        'fixed' => $this->t('Fixed'),
      ],
      '#default_value' => $this->getValue('bg_image')['background_attachment'] ?? 'not_fixed',
    ];
    $form['bg_image']['background_size'] = [
      '#type' => 'radios',
      '#title' => $this->t('Size'),
      '#options' => [
        'cover' => $this->t('Cover'),
        'contain' => $this->t('Contain'),
        'auto' => $this->t('Auto'),
      ],
      '#default_value' => $this->getValue('bg_image')['background_size'] ?? 'cover',
    ];
    $form['#attached']['library'][] = 'style_options_plugins/background';
    return $form;
  }

  /**
   * Build a list of background color options for classy radios.
   */
  protected function backgroundColorOptions() {
    $options = $this->getConfiguration('background_color')['options'] ?? [];
    return array_map(function ($varname, $color) {
      return [
        'classes' => 'bg-color' . $varname,
        'label' => '',
        'attributes' => ['style' => 'background-color: var(' . $varname . ', ' . $color . ')'],
      ];
    }, array_keys($options), $options);
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build, $value = '') {

    $style_variables = [];
    $media_id = $this->getValue('bg_image')['media'] ?? NULL;
    if (!empty($media_id) && is_numeric($media_id)) {
      $media_entity = Media::load($media_id);
      if (!empty($media_entity)) {
        $field_name = $this->getConfiguration('background_image')['field'] ?? 'field_media_image';
        $fid = $media_entity->$field_name->target_id;
      }
    }
    else {
      $fid = $this->getValue('bg_image')['fid'] ?? NULL;
    }
    if (!empty($fid) && $file_object = File::load($fid[0])) {

      $file_uri = $file_object->getFileUri();
      $file_url = $this->fileUrlGenerator->generate($file_uri)->toString();
      if ($this->getConfiguration('method') == 'css') {
        $style_variables += [
          '#file_url' => $file_url,
          '#image' => $file_object,
          '#repeat' => $this->getValue('bg_image')['background_repeat'] ?? 'no-repeat',
          '#position' => $this->getValue('bg_image')['background_position'] ?? 'center',
          '#attachment' => $this->getValue('bg_image')['background_attachment'] ?? 'not-fixed',
          '#size' => $this->getValue('bg_image')['background_size'] ?? 'cover',
        ];
      }
      else {
        $build['#attributes']['style'][] = 'background-image: url(' . $file_url . ');';
      }
    }

    $bg_color = $this->getValue('bg_color') ?? NULL;
    if (!empty($bg_color)) {
      if ($this->getConfiguration('method') == 'css') {
        $style_variables += ['#color' => $bg_color];
      }
      else {
        $build['#attributes']['style'][] = "background-color: $bg_color;";
      }
    }
    if ($this->getConfiguration('method') == 'css') {
      $this->generateStyle($build, $style_variables);
    }
    return $build;
  }

  /**
   * {@inheritDoc}
   */
  public function setValues($values) {
    $this->values = $values;
    // Addresses an issue with the way media form elements load default values
    // when the form is not rendered.
    // if (isset($values['bg_image']['media']['media_library_selection'])) {
    //   $this->values['bg_image']['media'] = $values['media']['media_selection_id'];
    // }
    return $this;
  }

}
