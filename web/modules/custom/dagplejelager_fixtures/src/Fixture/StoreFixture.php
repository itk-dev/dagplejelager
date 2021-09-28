<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\commerce_price\CurrencyImporterInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_store\Entity\Store;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Store fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class StoreFixture extends AbstractFixture implements FixtureGroupInterface {
  /**
   * @var CurrencyImporterInterface
   */
  private $currencyImporter;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $storeStorage;

  public function __construct(CurrencyImporterInterface $currencyImporter, EntityTypeManagerInterface $entityTypeManager)
  {
    $this->currencyImporter = $currencyImporter;
    $this->storeStorage = $entityTypeManager->getStorage('commerce_store');
  }

  /**
   * {@inheritdoc}
   */
  public function load()
  {
    $address = [
      'country_code' => 'US',
      'address_line1' => '123 Street Drive',
      'locality' => 'Beverly Hills',
      'administrative_area' => 'CA',
      'postal_code' => '90210',
    ];

    // The currency code.
    $currency = 'DKK';

    // If needed, this will import the currency.
    $this->currencyImporter->import($currency);

    $store = Store::create([
      'type' => 'custom_store_type',
      'uid' => 1,
      'name' => 'My Store',
      'mail' => 'admin@example.com',
      'address' => $address,
      'default_currency' => $currency,
      'billing_countries' => ['DK'],
    ]);
    $store->save();

    // If needed, this sets the store as the default store.
    $this->storeStorage->markAsDefault($store);
    $this->setReference('store:default', $store);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['dagplejelager'];
  }

}
