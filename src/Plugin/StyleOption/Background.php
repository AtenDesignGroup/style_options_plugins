<?php

namespace Drupal\style_options_plugins\Plugin\StyleOption;

use Drupal\file\Entity\File;
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
    $form['bg_color'] = [
      '#type' => 'color_spectrum',
      '#title' => $this->t('Background Color'),
      '#default_value' => $this->getValue('bg_color') ?? $this->getDefaultValue(),
      '#settings' => $this->getConfiguration('background_color')['settings'] ?? [],
      '#wrapper_attributes' => [
        'class' => [$this->getConfiguration('css_class')],
      ],
    ];
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
        '#allowed_bundles' => [$this->getConfiguration('background_image')['background_image_bundle'] ?? 'image'],
        '#default_value' => $this->getValue('bg_image')['media'] ?? $this->getDefaultValue(),
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
      '#prefix' => '<hr />',
      '#suffix' => '<hr />',
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
      '#suffix' => '<hr />',
    ];
    $form['#attached']['library'][] = 'style_options_plugins/background';
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build, $value = '') {
    $fid = $this->getValue('fid');
    if (!empty($fid) && $file_object = File::load($fid[0])) {

      $file_uri = $file_object->getFileUri();
      $file_url = $this->fileUrlGenerator->generate($file_uri)->toString();

      if ($this->getConfiguration('method') == 'css') {
        $this->generateStyle($build, [
          '#file_url' => $file_url,
          '#image' => $file_object,
        ]);
      }
      else {
        $build['#attributes']['style'][] = 'background-image: url(' . $file_url . ');';
      }
    }
    return $build;
  }

}
