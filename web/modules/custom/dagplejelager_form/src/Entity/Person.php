<?php

namespace Drupal\dagplejelager_form\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the person entity.
 *
 * @ContentEntityType(
 *   id = "dagplejelager_form_person",
 *   label = @Translation("Person"),
 *   base_table = "dagplejelager_form_person",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class Person extends ContentEntityBase implements ContentEntityInterface {
}
