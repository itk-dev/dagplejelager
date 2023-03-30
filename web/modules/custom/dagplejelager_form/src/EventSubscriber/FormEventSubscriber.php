<?php

namespace Drupal\dagplejelager_form\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\views\ViewExecutable;
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
        }
        break;

      case 'views_exposed_form':
        $view = $event->getFormState()->get('view');
        assert($view instanceof ViewExecutable);
        if ('commerce_orders' === $view->id()) {
          $form['product_id'] = [
            '#type' => 'entity_autocomplete',
            '#tags' => TRUE,
            '#target_type' => 'commerce_product',
            '#selection_settings' => [
              'target_bundles' => ['default'],
            ],
            '#placeholder' => $this->t('Search for a product'),
            '#description' => $this->t('Separate multiple products by comma. When searching for multiple products, only orders containing <em>all</em> products are shown.'),
          ];

          $form['product_category_id'] = [
            '#type' => 'entity_autocomplete',
            '#tags' => TRUE,
            '#target_type' => 'taxonomy_term',
            '#selection_settings' => [
              'target_bundles' => ['product_category'],
            ],
            '#placeholder' => $this->t('Search for a product category'),
            '#description' => $this->t('Separate multiple categories by comma. When searching for multiple categories, only orders containing <em>all</em> categories are shown.'),
          ];
        }
        break;
    }

    if (preg_match('/^state_machine_transition_form_commerce_order_state_(.+)$/', $event->getFormId())) {
      // Hide "Validate order" button (validation is performed by setting
      // “Fulfillment date“ on the order, cf.
      // Drupal\dagplejelager_actions\EventSubscriber\EntityEventSubscriber::preSaveEntity().).
      if (isset($form['actions']['validate'])) {
        $form['actions']['validate']['#access'] = FALSE;
      }
    }

    // Handle product forms.
    if (preg_match('/^commerce_product_(.+)_(add|edit)_form$/', $event->getFormId())) {
      // Hide product (variation) price.
      $form['variations']['widget']['entity']['list_price']['#access'] = FALSE;
      $form['variations']['widget']['entity']['price']['#access'] = FALSE;
      $form['variations']['widget']['entity']['price']['widget'][0]['#default_value'] = [
        'number' => 0.00,
        'currency_code' => 'DKK',
      ];
      // Hide variation status (it's published by default).
      $form['variations']['widget']['entity']['status']['#access'] = FALSE;
    }
  }

  /**
   * Alter billing profile form.
   *
   * @phpstan-param array<string, mixed> $form
   */
  private function alterBillingInformationProfileForm(array &$form): void {
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
      FormHookEvents::FORM_ALTER => 'alterForm',
    ];
  }

}
