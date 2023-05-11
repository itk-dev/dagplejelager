<?php

/**
 * @file
 * Enum for order states.
 */

namespace Drupal\dagplejelager_commerce\Helper;

enum OrderState: string
{
  case Draft = 'draft';
  case Validation = 'validation';
  case Fulfillment = 'fulfillment';
  case Completed = 'completed';
  case Canceled = 'canceled';
  case Anonymized = 'anonymized';
}
