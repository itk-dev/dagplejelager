<?php

namespace Drupal\dagplejelager_form\EventSubscriber;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views_event_dispatcher\Event\Views\ViewsQueryAlterEvent;
use Drupal\views_event_dispatcher\ViewsHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Views event subscriber.
 */
class ViewsEventSubscriber implements EventSubscriberInterface {
  /**
   * The order item storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $orderItemStorage;

  /**
   * The product storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $productStorage;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->orderItemStorage = $entityTypeManager->getStorage('commerce_order_item');
    $this->productStorage = $entityTypeManager->getStorage('commerce_product');
  }

  /**
   * Alter form.
   *
   * @param \Drupal\views_event_dispatcher\Event\Views\ViewsQueryAlterEvent $event
   *   The event.
   */
  public function alterViewsQuery(ViewsQueryAlterEvent $event): void {
    $view = $event->getView();

    switch ($view->id()) {
      case 'commerce_orders':
        $applyOrderIdFilter = FALSE;
        $orderIds = [];

        $productIds = $view->exposed_raw_input['product_id'] ?? NULL;
        if (NULL !== $productIds) {
          $applyOrderIdFilter = TRUE;
          // Get the actual product ids.
          $productIds = array_column($productIds, 'target_id');
          // Find orders containing all products.
          foreach ($productIds as $productId) {
            /** @var \Drupal\commerce_order\Entity\OrderItemInterface[] $items */
            $items = $this->orderItemStorage->loadByProperties(['purchased_entity' => $productId]);
            $orderIds[] = array_map(static function (OrderItemInterface $item) {
              return $item->getOrderId();
            }, $items);
          }
        }

        $productCategoryIds = $view->exposed_raw_input['product_category_id'] ?? NULL;
        if (NULL !== $productCategoryIds) {
          $applyOrderIdFilter = TRUE;
          // Get the actual product ids.
          $productCategoryIds = array_column($productCategoryIds, 'target_id');
          // Find orders containing all products.
          foreach ($productCategoryIds as $productCategoryId) {
            // Find order items mathing the product category.
            $products = $this->productStorage->loadByProperties(['field_category' => $productCategoryId]);
            /** @var \Drupal\commerce_order\Entity\OrderItemInterface[] $items */
            $items = $this->orderItemStorage->loadByProperties(['purchased_entity' => array_keys($products)]);
            $orderIds[] = array_map(static function (OrderItemInterface $item) {
              return $item->getOrderId();
            }, $items);
          }
        }

        if ($applyOrderIdFilter) {
          if (!empty($orderIds)) {
            // Intersect all order id lists to include only orders matching all
            // criteria.
            $orderIds = count($orderIds) > 1 ? array_intersect(...$orderIds) : reset($orderIds);
          }

          // Add non-existing order id to make sure that the list is not empty.
          $orderIds[] = '-1';
          $group = $view->query->setWhereGroup();
          assert($view->query instanceof Sql);
          $view->query->addWhere($group, 'order_id', $orderIds, 'IN');
        }
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ViewsHookEvents::VIEWS_QUERY_ALTER => 'alterViewsQuery',
    ];
  }

}
