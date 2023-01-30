<?php

namespace Drupal\dagplejelager_actions\TwigExtension;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\Core\Session\AccountInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension.
 */
class TwigExtension extends AbstractExtension {
  /**
   * The account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * Constructor.
   */
  public function __construct(AccountInterface $account) {
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('dagplejelager_is_granted', [$this, 'isGranted']),
      new TwigFunction('dagplejelager_order_handling', [$this, 'orderHandling']),
    ];
  }

  /**
   * Is granted.
   *
   * Heavily inspired by
   * https://symfony.com/doc/current/reference/twig_reference.html#is-granted.
   *
   * Examples:
   *
   *   Check for action on order:
   *     dagplejelager_is_granted('convert to cart', order_entity)
   *
   * @param string|null $attribute
   *   The attribute, e.g. 'convert to cart'.
   * @param mixed|null $object
   *   The optional object used to check the attribute against.
   *
   * @return bool
   *   True if the attribute is granted.
   */
  public function isGranted(string $attribute = NULL, $object = NULL) {
    if (NULL !== $attribute) {
      if ('convert to cart' === $attribute && $object instanceof Order) {
        return 'validation' === $object->getState()->getId()
          && $object->getCustomer()->id() === $this->currentUser->id();
      }
    }

    return FALSE;
  }

  /**
   * Return list of handlings on order items.
   *
   * @param mixed $orderId
   *   The order id.
   *
   * @return array|string[]
   *   The handlings.
   */
  public function orderHandling($orderId): array {
    $orderId = (string) $orderId;
    /** @var ?\Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::load($orderId);

    if (NULL === $order) {
      return [];
    }

    return array_filter(
      array_unique(
        array_map(
          static function (OrderItemInterface $item) {
            return $item->get('field_handling')->value;
          },
          $order->getItems()
        ),
        SORT_STRING
      )
    );
  }

}
