<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\UserInterface;

/**
 * Order fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class OrderFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $user = $this->getReference('user:manager');
    assert($user instanceof UserInterface);

    // @see https://metadrop.net/en/articles/drupal-8-commerce-2-create-order-programmatically
    $product = $this->getReference('product:horse');
    assert($product instanceof ProductInterface);
    $item = OrderItem::create([
      'type' => 'default',
      'purchased_entity' => $product->id(),
      'title' => $product->getTitle(),
      'unit_price' => new Price(0, 'DKK'),
      'quantity' => 1,
    ]);
    $item->save();

    $order = Order::create([
      'type' => 'default',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'store_id' => $this->getReference('store:default')->id(),
      'order_items' => [$item],
      'placed' => (new \DateTimeImmutable('2021-09-11'))->getTimestamp(),
      'state' => 'completed',
    ]);
    $order->recalculateTotalPrice();
    $order->save();
    $order->set('order_number', $order->id());
    $order->save();

    $product = $this->getReference('product:horse');
    assert($product instanceof ProductInterface);
    $item0 = OrderItem::create([
      'type' => 'default',
      'purchased_entity' => $product->id(),
      'title' => $product->getTitle(),
      'unit_price' => new Price(0, 'DKK'),
      'quantity' => 1,
    ]);
    $item0->save();

    $product = $this->getReference('product:zebra');
    assert($product instanceof ProductInterface);
    $item1 = OrderItem::create([
      'type' => 'default',
      'purchased_entity' => $product->id(),
      'title' => $product->getTitle(),
      'unit_price' => new Price(0, 'DKK'),
      'quantity' => 2,
    ]);
    $item1->save();

    $user = $this->getReference('user:manager');
    assert($user instanceof UserInterface);
    $order = Order::create([
      'type' => 'default',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'store_id' => $this->getReference('store:default')->id(),
      'order_items' => [$item0, $item1],
      'placed' => (new \DateTimeImmutable('2021-05-23'))->getTimestamp(),
      'state' => 'validation',
    ]);
    $order->recalculateTotalPrice();
    $order->save();
    $order->set('order_number', $order->id());
    $order->save();

    $user = $this->getReference('user:manager1');
    assert($user instanceof UserInterface);
    $order = Order::create([
      'type' => 'default',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'store_id' => $this->getReference('store:default')->id(),
      'order_items' => [$item0, $item1],
      'placed' => (new \DateTimeImmutable('2021-12-24'))->getTimestamp(),
      'state' => 'validation',
    ]);
    $order->recalculateTotalPrice();
    $order->save();
    $order->set('order_number', $order->id());
    $order->save();

    $item = OrderItem::create([
      'type' => 'default',
      'purchased_entity' => $product->id(),
      'title' => $product->getTitle(),
      'unit_price' => new Price(0, 'DKK'),
      'quantity' => 3,
    ]);
    $item->save();

    $user = $this->getReference('user:manager');
    assert($user instanceof UserInterface);
    $order = Order::create([
      'type' => 'default',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'store_id' => $this->getReference('store:default')->id(),
      'order_items' => [$item],
      'placed' => (new \DateTimeImmutable('2022-01-01'))->getTimestamp(),
      'state' => 'canceled',
    ]);
    $order->recalculateTotalPrice();
    $order->save();
    $order->set('order_number', $order->id());
    $order->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      ProductBundleFixture::class,
      ProductFixture::class,
      StoreFixture::class,
      UserFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['dagplejelager'];
  }

}
