<?php

namespace Drupal\dagplejelager_commerce\Helper;

/**
 * A useful helper.
 */
class Helper {

  /**
   * Implements hook_menu_local_actions_alter().
   *
   * Removes “Create new order” link button from commerce orders view.
   *
   * @phpstan-param array<string, mixed> $local_actions
   */
  public function menuLocalActionsAlter(array &$local_actions): void {
    unset($local_actions['entity.commerce_order.add_page']);
  }

}
