<?php

namespace Drupal\style_options_plugins\EventSubscriber;

use Drupal\style_options\StyleOptionConfigurationDiscovery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\layout_paragraphs\Event\LayoutParagraphsAllowedTypesEvent;

/**
 * Class definition for LayoutParagraphsAllowedTypesSubcriber.
 */
class ComponentMenuVariationsSubscriber implements EventSubscriberInterface {

  /**
   * Constructor for ComponentMenuVariationsSubscriber.
   *
   * @param \Drupal\style_options\StyleOptionConfigurationDiscovery $styleOptionsDiscovery
   *   The style options discovery service.
   */
  public function __construct(
    protected StyleOptionConfigurationDiscovery $styleOptionsDiscovery
  ) {}

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      LayoutParagraphsAllowedTypesEvent::EVENT_NAME => 'typeRestrictions',
    ];
  }

  /**
   * Restricts available types based on settings in layout.
   *
   * @param \Drupal\layout_paragraphs\Event\LayoutParagraphsAllowedTypesEvent $event
   *   The allowed types event.
   */
  public function typeRestrictions(LayoutParagraphsAllowedTypesEvent $event) {

    $types = $event->getTypes();
    foreach ($types as $bundle => $info) {
      $options = $this->styleOptionsDiscovery->getContextOptions('paragraphs', $bundle);
      foreach (array_keys($options) as $option_id) {
        $definition = $this->styleOptionsDiscovery->getOptionDefinition($option_id);
        if ($definition['plugin'] == 'template_suggestion' || $definition['plugin'] == 'component_variation') {
          foreach ($definition['options'] as $key => $option) {
            if ($key !== $definition['default']) {
              $types[$bundle . '__' . $key] = $info;
              $url_object = clone $types[$bundle . '__' . $key]['url_object'];
              $options = $url_object->getOptions();
              $options['query'][$option_id] = $key;
              $url_object->setOptions($options);
              $types[$bundle . '__' . $key]['url_object'] = $url_object;
              $types[$bundle . '__' . $key]['label'] = $option['label'];
              $types[$bundle . '__' . $key]['id'] = $bundle . '__' . $key;
            }
            else {
              if (!empty($option['label'])) {
                $types[$bundle]['label'] = $option['label'];
              }
            }
          }
        }
      }
    }
    $event->setTypes($types);
  }

}
