<?php

namespace Drupal\dagplejelager_form\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Form event subscriber.
 */
class FormEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Alter form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function alterForm(FormAlterEvent $event): void {
    $form = &$event->getForm();

    switch ($event->getFormId()) {

      case 'commerce_checkout_flow_multistep_default':
        if (isset($form['billing_information']['profile'])) {
          $this->alterBillingInformationProfileForm($form['billing_information']['profile']);
          break;
        }
    }

    // Handle product forms.
    if (preg_match('/^commerce_product_(.+)_(add|edit)_form$/', $event->getFormId())) {
      // Hide product (variation) price.
      $form['variations']['widget']['entity']['list_price']['#access'] = FALSE;
      $form['variations']['widget']['entity']['price']['#access'] = FALSE;
      // Hide variation status (it's published by default).
      $form['variations']['widget']['entity']['status']['#access'] = FALSE;
    }
  }

  /**
   * Alter billing profile form.
   */
  private function alterBillingInformationProfileForm(array &$form) {
    $form['dagplejelager_form'] = [
      '#weight' => -9999,

      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#placeholder' => $this->t('Search for a name or an address'),
      '#autocomplete_route_name' => 'dagplejelager_form.autocomplete.day_carer',
    ];
    $form['#attached']['library'][] = 'dagplejelager_form/billing-information-profile';

    // Don't copy billing address to address book.
    if (isset($form['copy_to_address_book'])) {
      $form['copy_to_address_book']['#default_value'] = FALSE;
      $form['copy_to_address_book']['#type'] = 'hidden';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'alterForm',
    ];
  }

}
