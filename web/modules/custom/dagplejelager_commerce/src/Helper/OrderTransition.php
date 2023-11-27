<?php

namespace Drupal\dagplejelager_commerce\Helper;

/**
 * Available order transistion.
 */
enum OrderTransition: string {
  case PlaceOrder = 'place';
  case ValidateOrder = 'validate';
  case FulfillOrder = 'fulfill';
  case CancelOrder = 'cancel';
  case AnonymizeOrder = 'anonymize';
}
