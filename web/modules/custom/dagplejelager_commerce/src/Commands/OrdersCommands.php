<?php

namespace Drupal\dagplejelager_commerce\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dagplejelager_actions\Guard\ChangeOrderStateGuard;
use Drupal\dagplejelager_commerce\Helper\AnonymizeOrderHelper;
use Drupal\user\UserStorageInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Orders commands.
 */
class OrdersCommands extends DrushCommands {
  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  private UserStorageInterface $userStorage;

  /**
   * Constructor.
   */
  public function __construct(
    readonly private AnonymizeOrderHelper $helper,
    readonly EntityTypeManagerInterface $entityTypeManager,
    readonly private AccountSwitcherInterface $accountSwitcher,
    readonly private LoggerChannelInterface $loggerChannel
  ) {
    // @todo Inject the user storage directly (cf. https://www.drupal.org/project/drupal/issues/2376347)
    $this->userStorage = $this->entityTypeManager->getStorage('user');
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
  public function anonymize(array $options = [
    'user-id' => 1,
    'dry-run' => FALSE,
    'anonymize-after' => NULL,
    'order-id' => NULL,
  ]): void {
    $dryRun = $options['dry-run'];
    $user = $this->getAnonymizeUser($options['user-id']);
    $this->accountSwitcher->switchTo($user);

    $loadOptions = [];
    if (isset($options['anonymize-after'])) {
      $loadOptions['anonymize_after'] = $options['anonymize-after'];
    }
    if (isset($options['order-id'])) {
      $loadOptions['order_ids'] = $this->getIntList($options['order-id']);
    }

    $this->helper->setLogger($this->logger());
    $orders = $this->helper->loadAnonymizableOrders($loadOptions);

    if (empty($orders)) {
      $this->logger->info('No orders to be anonymized');
    }
    else {
      foreach ($orders as $order) {
        $context = [
          '@order' => $order->label(),
          '@order_id' => $order->id(),
          '@url' => $order->toUrl()->setAbsolute()->toString(TRUE)->getGeneratedUrl(),
          '@state' => $order->getState()->getLabel(),
        ];
        if (!$dryRun) {
          try {
            $this->helper->anonymizeOrder($order, TRUE);
            $this->loggerChannel->info('@order (id: @order_id; state: @state) (@url) has been anonymized', $context);
          }
          catch (\Throwable $throwable) {
            $this->loggerChannel->error('Error anonymizing @order (id: @order_id; state: @state) (@url): @message', $context + [
              '@message' => $throwable->getMessage(),
              '@throwable' => $throwable,
            ]);
          }
        }
        else {
          $this->logger()->info((string) new TranslatableMarkup('@order (id: @order_id; state: @state) (@url) will be anonymized', $context));
        }
      }
    }

    $this->accountSwitcher->switchBack();
  }

  /**
   * Get user that can anonymize orders.
   *
   * @param int $userId
   *   The user id.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user.
   */
  private function getAnonymizeUser(int $userId): AccountInterface {
    $user = $this->userStorage->load($userId);
    if (NULL === $user) {
      throw new RuntimeException(sprintf('Cannot load user %d', $userId));
    }
    assert($user instanceof AccountInterface);
    $permission = ChangeOrderStateGuard::PERMISSION_CHANGE_ORDER_WORKFLOW_STATES;
    if (!$user->hasPermission($permission)) {
      throw new RuntimeException(sprintf('User %d (%s) does not have the %s permission', $user->id(), $user->getAccountName(), $permission));
    }

    return $user;
  }

  /**
   * Get integer list from comma separated string value (CSV).
   *
   * @param string $csv
   *   The CSV.
   *
   * @return array
   *   The list if integers.
   *
   * @phpstan-return array<int>
   */
  private function getIntList(string $csv): array {
    return array_values(
      // Get only non-zero values.
      array_filter(
        // Get integers.
        array_map('intval', preg_split('/\s*,\s*/', $csv, -1, PREG_SPLIT_NO_EMPTY))
      )
    );
  }

}
