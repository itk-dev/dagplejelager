<?php

/**
 * @file
 * Enum for order transitions.
 */

namespace Drupal\dagplejelager_commerce\Helper;

enum OrderTransition: string
{
  case PlaceOrder = 'place';
  case ValidateOrder = 'validate';
  case FulfillOrder = 'fulfill';
  case CancelOrder = 'cancel';
  case AnonymizeOrder = 'anonymize';
}
