<?php

namespace Drupal\style_options_plugins\EventSubscriber;

use Drupal\mercury_editor\Event\BeforeCreateComponentEvent;
use Drupal\style_options\StyleOptionConfigurationDiscovery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Changes the component type based on the context.
 */
class BeforeCreateComponentSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      BeforeCreateComponentEvent::EVENT_NAME => 'alterComponentType',
    ];
  }

  /**
   * Constructor for BeforeCreateComponentSubscriber.
   *
   * @param \Drupal\style_options\StyleOptionConfigurationDiscovery $styleOptionsDiscovery
   *   The style options discovery service.
   */
  public function __construct(
    protected StyleOptionConfigurationDiscovery $styleOptionsDiscovery
  ) {}

  /**
   * Alter the component type and defaults if needed.
   *
   * @param \Drupal\mercury_editor\Event\BeforeCreateComponentEvent $event
   *   The event object.
   */
  public function alterComponentType(BeforeCreateComponentEvent $event) {

    $type = $event->getParagraphType();
    if (str_contains($type, '__')) {
      $parts = explode('__', $type);
      $bundle = $parts[0];
      $variation = $parts[1];
    }
    if (empty($bundle)) {
      return;
    }

    $options = $this->styleOptionsDiscovery->getContextOptions('paragraphs', $bundle);
    foreach (array_keys($options) as $option_id) {
      $definition = $this->styleOptionsDiscovery->getOptionDefinition($option_id);
      if ($definition['plugin'] == 'component_variation') {
        $event->setParagraphType($bundle);
        $event->setDefaultValues([
          'behavior_settings' => serialize([
            'style_options' => [
              // This needs to be the key of the option.
              $option_id => [
                'component_variation' => $variation,
              ],
            ],
          ]),
        ]);
      }
    }
  }

}
