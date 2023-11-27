<?php

namespace Drupal\dagplejelager_commerce\Helper;

/**
 * Available order states.
 */
enum OrderState: string {
  case Draft = 'draft';
  case Validation = 'validation';
  case Fulfillment = 'fulfillment';
  case Completed = 'completed';
  case Canceled = 'canceled';
  case Anonymized = 'anonymized';
}
