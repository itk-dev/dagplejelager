<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;

/**
 * Product bundle fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class ProductBundleFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $product = Product::create([
      'type' => 'bundle',
      'title' => 'A band of horses',
      'body' => 'This is a nice gang.',
      'field_category' => $this->getReference('product_category:animal'),
      'variations' => [
        ProductVariation::create([
          'type' => 'default',
          'sku' => 'band-of-horses',
          'price' => new Price('0.00', 'DKK'),
        ]),
      ],
      'field_product_reference' => [
        $this->getReference('product:horse'),
        $this->getReference('product:zebra'),
      ],
      'stores' => [$this->getReference('store:default')],
    ]);
    $product->save();
    $this->setReference('product-bundle:horses', $product);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [StoreFixture::class, ProductFixture::class];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['dagplejelager'];
  }

}
