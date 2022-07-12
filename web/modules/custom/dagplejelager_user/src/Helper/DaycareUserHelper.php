<?php

namespace Drupal\dagplejelager_user\Helper;

/**
 * The helper.
 */
class DaycareUserHelper {

  /**
   * Implements hook_local_tasks_alter().
   *
   * @phpstan-param array<string, mixed> $local_tasks
   */
  public function localTasksAlter(array &$local_tasks): void {
    unset($local_tasks['commerce_order.address_book.overview']);
  }

}
