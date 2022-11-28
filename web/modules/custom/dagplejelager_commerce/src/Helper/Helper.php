<?php

namespace Drupal\dagplejelager_commerce\Helper;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderStorage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;

/**
 * A useful helper.
 */
class Helper {
  /**
   * The order storage.
   *
   * @var \Drupal\commerce_order\OrderStorage
   */
  private OrderStorage $orderStorage;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    // @phpstan-ignore-next-line
    $this->orderStorage = $entityTypeManager->getStorage('commerce_order');
  }

  /**
   * Implements hook_menu_local_actions_alter().
   *
   * Removes “Create new order” link button from commerce orders view.
   *
   * @phpstan-param array<string, mixed> $local_actions
   */
  public function menuLocalActionsAlter(array &$local_actions): void {
    unset($local_actions['entity.commerce_order.add_page']);
  }

  /**
   * Anonymize orders.
   *
   * @phpstan-param  array<string, mixed> $options
   */
  public function anonymizeOrders(array $options): void {
    $options += $this->getSettings();
    $anonymizeAfter = $options['anonymize_after'] ?? '30 days';

    // Substract the time interval from now.
    $now = new \DateTimeImmutable();
    $then = new \DateTimeImmutable($anonymizeAfter);
    $threshold = $now->add($then->diff($now));

    // Load all completed orders.
    $query = $this->orderStorage->getQuery()
      ->accessCheck(FALSE);

    // Find order that are ….
    $query->condition(
      $query->orConditionGroup()
        // … completed before the threshold …
        ->condition(
          $query->andConditionGroup()
            ->condition('state', 'completed')
            ->condition('completed', $threshold->getTimestamp(), '<=')
        )
        // … or canceled.
        ->condition('state', 'canceled')
    );

    /** @var \Drupal\commerce_order\Entity\OrderInterface[] $orders */
    $orders = $this->orderStorage->loadMultiple($query->execute());
    foreach ($orders as $order) {
      $this->anonymizeOrder($order);
    }
  }

  /**
   * Anonymize order.
   */
  public function anonymizeOrder(OrderInterface $order): void {
    $order->getState()->applyTransitionById('anonymize');
    $order->save();
  }

  /**
   * Get settings.
   *
   * @phpstan-return array<string, mixed>
   */
  private function getSettings(): array {
    $settings = (array) Settings::get('dagplejelager_commerce');

    return $settings['anonymize'] ?? [];
  }

}
