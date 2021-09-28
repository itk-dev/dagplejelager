<?php

namespace Drupal\dagplejelager_form\Commands;

use Drupal\dagplejelager_form\Helper\DayCarerHelper;
use Drush\Commands\DrushCommands;
use Drush\Drush;

/**
 * Drush commands.
 */
class DayCarerCommands extends DrushCommands {
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
   * The run command.
   *
   * @param string $filename
   *   CVS filename. Relative paths are resolved from the Drupal root.
   * @param array $options
   *   The options.
   *
   * @command dagplejelager_form:load-day-carers:file
   *
   * @option dry-run
   *   Don't do anything, but show what will be done.
   * @usage dagplejelager_form:load-day-carers:file file.csv
   * @usage dagplejelager_form:load-day-carers:file file.csv --dry-run
   */
  public function run(string $filename, array $options = ['dry-run' => FALSE]) {
    if (!file_exists($filename)) {
      throw new \RuntimeException(sprintf('Cannot read file %s', $filename));
    }

    $file = fopen($filename, 'r');
    $columns = NULL;
    $items = [];
    while (($line = fgetcsv($file)) !== FALSE) {
      if (NULL === $columns) {
        $columns = $line;
      }
      else {
        $items[] = array_combine($columns, $line);
      }
    }
    fclose($file);

    foreach ($items as $item) {
      $dayCarer = [];

      $id = $item['ident'];
      if (empty($id)) {
        continue;
      }

      $dayCarer['id'] = $id;
      $dayCarer['institution_id'] = $item['instId'];
      $dayCarer['institution_name'] = $item['instNavn'];

      // Split navn by space and use first part as given name and the rest as
      // family name.
      $parts = preg_split('/\s+/', $item['navn'], 2);
      $dayCarer['given_name'] = $parts[0];
      $dayCarer['family_name'] = $parts[1] ?? '';
      $dayCarer['address_line1'] = $item['adresse'];
      // 'country_code' => $countryCode,
      // 'langcode' => $item[''],
      // 'locality' => $item[''],
      $dayCarer['organization'] = $item['instNavn'];
      // Split postDist by space and use first part as postal code and the rest
      // as locality.
      $parts = preg_split('/\s+/', $item['postDist'], 2);
      $dayCarer['postal_code'] = $parts[0];
      $dayCarer['locality'] = $parts[1] ?? '';

      $dayCarers[] = $dayCarer;
    }

    if (isset($dayCarers)) {
      $result = $this->helper->updateDayCarers($dayCarers);
      Drush::output()->writeln(1 === count($result)
        ? 'One day carer updated.'
        : sprintf('%d day carers updated.', count($result))
      );
    }
    else {
      Drush::output()->writeln('No day carers loaded.');
    }
  }

}
