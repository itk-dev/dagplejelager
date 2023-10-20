<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Page fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class ProductAttributeFixture extends AbstractFixture implements  FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $node = Node::create([
      'type' => 'commerce_product_attribute',
      'title' => 'commerce_product_attribute',
      'status' => NodeInterface::PUBLISHED,
      'attribute' => ['cirkusdyr' => ['name' => 'Elefant', 'name' => 'Heste']],
      'created' => date("Y-m-d", 2222222322222),
      ]);
    $this->addReference('commerce_product_attribute:fixture-1', $node);
    $node->save();
  }
  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
