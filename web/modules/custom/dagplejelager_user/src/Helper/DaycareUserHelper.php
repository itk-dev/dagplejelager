<?php

namespace Drupal\dagplejelager_user\Helper;

/**
 * The helper.
 */
class DaycareUserHelper {

  /**
   * Implements hook_local_tasks_alter().
   */
  public function localTasksAlter(array &$local_tasks) {
    unset($local_tasks['commerce_order.address_book.overview']);
  }

}
