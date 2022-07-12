<?php

namespace Drupal\dagplejelager_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Product category fixture.
 *
 * @package Drupal\dagplejelager_fixtures\Fixture
 */
class ProductCategoryFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $categories = [
      'misc' => [
        'description' => 'All the other stuff.',
      ],
      'animal' => [
        'description' => 'Livestock and other beasts.',
      ],
    ];
    foreach ($categories as $name => $data) {
      $term = Term::create([
        'vid' => 'product_category',
        'name' => $name,
      ] + $data);
      $term->save();
      $this->setReference('product_category:' . $name, $term);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['dagplejelager'];
  }

}
