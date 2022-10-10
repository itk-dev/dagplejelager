<?php

namespace Drupal\dagplejelager_commerce\Commands;

use Drupal\dagplejelager_commerce\Helper\Helper;
use Drush\Commands\DrushCommands;

/**
 * Orders commands.
 */
class OrdersCommands extends DrushCommands {
  /**
   * The commerce helper.
   *
   * @var \Drupal\dagplejelager_commerce\Helper\Helper
   */
  private $helper;

  /**
   * Constructor.
   */
  public function __construct(Helper $helper) {
    $this->helper = $helper;
  }

  /**
   * The anonymize-orders command.
   *
   * @param array $options
   *   The options.
   *
   * @command dagplejelager_commerce:orders:anonymize
   *
   * @option dry-run
   *   Don't do anything, but show what will be done.
   * @usage dagplejelager_commerce:orders:anonymize
   *
   * @phpstan-param array<string, mixed> $options
   */
  public function anonymize(array $options = ['dry-run' => FALSE]): void {
    $this->helper->anonymizeOrders($options);
  }

}
