<?php

namespace Drupal\dfl_store_fixtures\Fixture;

use Drupal\commerce_store\Entity\Store;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Store fixture.
 *
 * @package Drupal\dfl_store_fixtures\Fixture
 */
class StoreFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MediaFixture constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $store = Store::create([
      'type' => 'online',
      'name' => 'Dagplejelager',
      'mail' => 'itkdev@mkb.aarhus.dk',
      'default_currency' => 'DKK',
      'address' => [
        'country_code' => 'DK',
        'address_line1' => 'Dokk1',
        'address_line2' => 'Hack Kampmanns Pl. 2',
        'locality' => 'Aarhus',
        'postal_code' => '8000',
      ],
      'billing_countries' => ['DK'],
    ]);
    $store->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['os2loop_store'];
  }

}
