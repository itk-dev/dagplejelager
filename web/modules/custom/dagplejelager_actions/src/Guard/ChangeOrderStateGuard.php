<?php

namespace Drupal\dagplejelager_actions\Guard;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\state_machine\Guard\GuardInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowTransition;

/**
 * Change order state guard.
 */
class ChangeOrderStateGuard implements GuardInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public function allowed(WorkflowTransition $transition, WorkflowInterface $workflow, EntityInterface $entity) {
    if ($entity instanceof OrderInterface) {
      if ($this->currentUser->hasPermission('change order workflow states')) {
        return TRUE;
      }

      // A user can only change own orders.
      if ($entity->getCustomer()->id() !== $this->currentUser->id()) {
        return FALSE;
      }

      // User can cancel or place own orders.
      return in_array($transition->getId(), ['cancel', 'place']);
    }

    // All other transitions.
    return TRUE;
  }

}
