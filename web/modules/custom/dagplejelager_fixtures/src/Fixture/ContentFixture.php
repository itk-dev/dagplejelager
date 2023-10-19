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
      'title' => 'Statisk side 223232323',
      'status' => NodeInterface::PUBLISHED,
//      "field_media_image_single" => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()],
////      "field_section" => [], Hvad er det
//      "field_teaser" => 'field teaser',
//      "field_sidebar" => ['value' => 'Sidebar ?'],
//      "field_teaser_color" => '#fff',
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
