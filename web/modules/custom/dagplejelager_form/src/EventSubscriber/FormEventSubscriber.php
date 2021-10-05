<?php

namespace Drupal\dagplejelager_form\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
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
