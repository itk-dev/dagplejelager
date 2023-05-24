<?php

namespace Drupal\dagplejelager_commerce\Helper;

use Drupal\address\Plugin\Field\FieldType\AddressItem;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderStorage;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\profile\Entity\ProfileInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Another useful helper.
 */
class AnonymizeOrderHelper implements LoggerAwareInterface {
  use LoggerTrait;
  use LoggerAwareTrait;

  private const EMPTY_VALUE = '';

  /**
   * The order storage.
   *
   * @var \Drupal\commerce_order\OrderStorage
   */
  private OrderStorage $orderStorage;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $orderStorage = $entityTypeManager->getStorage('commerce_order');
    assert($orderStorage instanceof OrderStorage);
    $this->orderStorage = $orderStorage;
    $this->setLogger(new NullLogger());
  }

  /**
   * Anonymize orders.
   *
   * @return array|OrderInterface[]
   *   The orders
   *
   * @phpstan-param array<string, mixed> $options
   */
  public function loadAnonymizableOrders(array $options): array {
    // Resolve the anonymize settings with the passed options taking precedence.
    $options = $this->resolveLoadAnonymizableOrdersOptions($options + $this->getSettings());

    // Substract the time interval from now.
    $now = new \DateTimeImmutable();
    $then = new \DateTimeImmutable($options['anonymize_after']);
    $threshold = $now->add($then->diff($now));

    $this->logger->info(sprintf('Loading orders completed after %s', $threshold->format(\DateTimeImmutable::ATOM)));

    // Load all orders ….
    $query = $this->orderStorage->getQuery()
      ->accessCheck(FALSE);

    if (!empty($options['order_ids'])) {
      $query->condition('order_id', $options['order_ids'], 'IN');
    }
    else {
      // … that are ….
      $query->condition(
        $query->orConditionGroup()
          // … completed before the threshold …
          ->condition(
            $query->andConditionGroup()
              ->condition('state', OrderState::Completed->value)
              ->condition('completed', $threshold->getTimestamp(), '<=')
          )
          // … or canceled.
          ->condition('state', OrderState::Canceled->value)
      );
    }

    // @phpstan-ignore-next-line
    return $this->orderStorage->loadMultiple($query->execute());
  }

  /**
   * Resolve options for loadAnonymizableOrders().
   *
   * @param array $options
   *   The input options.
   *
   * @return array
   *   The resolved options.
   *
   * @phpstan-param array<string, mixed> $options
   * @phpstan-return array<string, mixed>
   */
  private function resolveLoadAnonymizableOrdersOptions(array $options): array {
    return (new OptionsResolver())
      ->setRequired('anonymize_after')
      ->setAllowedTypes('anonymize_after', 'string')
      // Check that anonymize_after is a valid datetime.
      ->setAllowedValues('anonymize_after', static function (string $value) {
        try {
          new \DateTimeImmutable($value);
          return TRUE;
        }
        catch (\Throwable) {
          return FALSE;
        }
      })

      ->setDefault('order_ids', [])
      ->setAllowedTypes('order_ids', 'int[]')
      ->setAllowedValues('order_ids', static fn(array $values) => array_is_list($values))

      ->resolve($options + $this->getSettings());
  }

  /**
   * Anonymize order.
   */
  public function anonymizeOrder(OrderInterface $order, bool $save = FALSE): void {
    $this->applyTransition($order, OrderTransition::AnonymizeOrder);

    $order
      // Set customer to the anonymous user.
      ->setCustomerId(0)
      ->setIpAddress(self::EMPTY_VALUE)
      ->setEmail(self::EMPTY_VALUE)
      ->set(
        'field_order_notes',
        (string) new TranslatableMarkup('Anonymized on @now', ['@now' => (new DrupalDateTime())->format(DrupalDateTime::FORMAT)])
      );
    if ($billingProfile = $order->getBillingProfile()) {
      $this->anonymizeProfile($billingProfile, $save);
    }

    if ($save) {
      $order->save();
    }
  }

  /**
   * Anonymize a profile.
   */
  private function anonymizeProfile(ProfileInterface $profile, bool $save): void {
    try {
      $fields = [
        'field_institution_id',
        'field_telephone_number',
      ];
      foreach ($fields as $field) {
        if ($profile->hasField($field)) {
          $profile->set($field, self::EMPTY_VALUE);
        }
      }
      $address = $profile->get('address')->first();
      if ($address instanceof AddressItem) {
        $this->anonymizeAddress($address);
      }
    }
    catch (\Exception) {
    }

    if ($save) {
      $profile->save();
    }
  }

  /**
   * Anonymize address.
   */
  private function anonymizeAddress(AddressItem $address, bool $save = FALSE): void {
    $keys = [
      'address_line1',
      'address_line2',
      'given_name',
      'family_name',
      'organization',
    ];

    foreach ($keys as $key) {
      $address->set($key, self::EMPTY_VALUE);
    }
  }

  /**
   * Apply a transition to an order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   * @param OrderTransition $transition
   *   The transition.
   */
  private function applyTransition(OrderInterface $order, OrderTransition $transition): void {
    $order->getState()->applyTransitionById($transition->value);
  }

  /**
   * Get settings.
   *
   * @phpstan-return array<string, mixed>
   */
  private function getSettings(): array {
    $settings = (array) Settings::get('dagplejelager_commerce');

    return $settings['anonymize'] ?? [];
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param mixed $level
   * @phpstan-param string $message
   * @phpstan-param array<string, mixed> $context
   */
  public function log($level, $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
