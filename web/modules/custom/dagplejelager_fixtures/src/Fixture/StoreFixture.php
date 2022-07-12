<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\commerce_price\CurrencyImporterInterface;
use Drupal\commerce_store\Entity\Store;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;

/**
 * Store fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class StoreFixture extends AbstractFixture implements FixtureGroupInterface {
  /**
   * The currency importer.
   *
   * @var \Drupal\commerce_price\CurrencyImporterInterface
   */
  private $currencyImporter;

  /**
   * {@inheritdoc}
   */
  public function __construct(CurrencyImporterInterface $currencyImporter) {
    $this->currencyImporter = $currencyImporter;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
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
      'type' => 'online',
      'name' => 'My Store',
      'mail' => 'admin@example.com',
      'address' => $address,
      'default_currency' => $currency,
      'billing_countries' => ['DK'],
    ]);
    $store->setDefault(TRUE);
    $store->save();
    $this->setReference('store:default', $store);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['dagplejelager'];
  }

}
