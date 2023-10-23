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
class ContentFixture extends AbstractFixture implements  FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $node = Node::create([
      'type' => 'basic_page',
      'title' => 'Statisk side',
      'status' => NodeInterface::PUBLISHED,
      'body' => ['value' => 'dette er en tekst'],
    ]);
    $this->addReference('basic_page:fixture-1', $node);
    $node->save();
  }
  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
