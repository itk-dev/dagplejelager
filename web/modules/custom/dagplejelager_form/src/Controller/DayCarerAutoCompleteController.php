<?php

namespace Drupal\dagplejelager_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dagplejelager_form\Helper\DayCarerHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autocomplete controller.
 */
class DayCarerAutoCompleteController extends ControllerBase {
  /**
   * The day carer helper.
   *
   * @var \Drupal\dagplejelager_form\Helper\DayCarerHelper
   */
  private $helper;

  /**
   * Constructor.
   */
  public function __construct(DayCarerHelper $helper) {
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static($container->get(DayCarerHelper::class));
  }

  /**
   * Handler for autocomplete request.
   */
  public function autocomplete(Request $request): JsonResponse {
    $results = [];
    $query = $request->query->get('q') ?? '';

    $dayCarers = $this->helper->searchDayCarers($query);
    foreach ($dayCarers as $dayCarer) {
      $label = sprintf('%s %s (%s [%s])',
        $dayCarer['name']['given_name'], $dayCarer['name']['family_name'],
        $dayCarer['institution']['name'], $dayCarer['institution']['id']
      );
      $results[] = [
        'value' => $label,
        'label' => $label,
      ] + $dayCarer;
    }

    return new JsonResponse($results);
  }

}
