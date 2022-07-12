<?php

namespace Drupal\dagplejelager_actions\EventSubscriber;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Entity event subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Entity pre-save.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function preSaveEntity(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof Order) {
      $originalEntity = $event->getOriginalEntity();
      if (NULL !== $originalEntity) {
        // It's an update.
        $fulfillmentDate = $entity->get('field_fulfillment_date')->first();
        if (NULL !== $fulfillmentDate) {
          // Validate order when fulfillment date is set.
          if ('validation' === $entity->getState()->getId()) {
            $entity->getState()->applyTransitionById('validate');
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'preSaveEntity',
    ];
  }

}
