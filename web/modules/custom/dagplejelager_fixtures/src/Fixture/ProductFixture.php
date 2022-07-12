<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;

/**
 * Product fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class ProductFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $product = Product::create([
      'type' => 'default',
      'title' => 'Horse',
      'body' => 'This is a nice horse.',
      'field_category' => $this->getReference('product_category:animal'),
      'variations' => [
        ProductVariation::create([
          'type' => 'default',
          'sku' => 'hest',
          'price' => new Price('0.00', 'DKK'),
        ]),
      ],
      'stores' => [$this->getReference('store:default')],
    ]);
    $product->save();
    $this->setReference('product:horse', $product);

    $product = Product::create([
      'type' => 'default',
      'title' => 'Zebra',
      'body' => 'It has stripes!',
      'field_category' => $this->getReference('product_category:animal'),
      'variations' => [
        ProductVariation::create([
          'type' => 'default',
          'sku' => 'zebra',
          'price' => new Price('0.00', 'DKK'),
        ]),
      ],
      'stores' => [$this->getReference('store:default')],
    ]);
    $product->save();
    $this->setReference('product:zebra', $product);

    $product = Product::create([
      'type' => 'default',
      'title' => 'Bicycle',
      'field_category' => $this->getReference('product_category:misc'),
      'variations' => [
        ProductVariation::create([
          'type' => 'default',
          'sku' => 'bicycle',
          'price' => new Price('0.00', 'DKK'),
        ]),
      ],
      'stores' => [$this->getReference('store:default')],
    ]);
    $product->save();
    $this->setReference('product:bicycle', $product);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [StoreFixture::class, ProductCategoryFixture::class];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['dagplejelager'];
  }

}
