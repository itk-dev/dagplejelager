<?php

namespace Drupal\dagplejelager_form\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A useful helper.
 */
class Helper {
  use StringTranslationTrait;

  /**
   * Implements hook_inline_entity_form_table_fields_alter().
   *
   * @phpstan-param array<string, mixed> $fields
   * @phpstan-param array<string, mixed> $context
   */
  public function inlineEntityFormTableFieldsAlter(array &$fields, array $context): void {
    if ('commerce_order_item' === $context['entity_type']) {
      unset($fields['unit_price']);
      $fields['field_handling'] = [
        'type' => 'field',
        'label' => $this->t('Handling'),
        'weight' => 101,
      ];
    }
  }

}
