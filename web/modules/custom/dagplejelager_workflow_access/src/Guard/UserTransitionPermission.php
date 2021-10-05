<?php

namespace Drupal\dagplejelager_workflow_access\Guard;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\state_machine\Guard\GuardInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowTransition;

class UserTransitionPermission implements GuardInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new PublicationGuard object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user..
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  public function allowed(WorkflowTransition $transition, WorkflowInterface $workflow, EntityInterface $entity) {
    if ($this->currentUser->hasPermission('change order workflow states')) {
      /*if ($transition->getId() == 'validate') {
        foreach ($entity->getItems() as $order_item) {
          return TRUE;
        }
      }
      */

      return TRUE;
    }

    // All other transitions.
    return FALSE;
  }

}
