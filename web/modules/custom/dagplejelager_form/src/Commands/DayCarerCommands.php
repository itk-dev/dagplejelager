<?php

namespace Drupal\dagplejelager_form\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
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
   * The config.
   *
   * @var array|null
   *
   * @phpstan-var array<string, mixed>|null
   */
  private $importConfig;

  /**
   * Constructor.
   */
  public function __construct(DayCarerHelper $helper, ConfigFactoryInterface $configFactory) {
    $this->helper = $helper;
    $this->importConfig = $configFactory->get('dagplejelager_form')->get('import');
  }

  /**
   * The import command.
   *
   * @param array $options
   *   The options.
   *
   * @command dagplejelager_form:day-carers:import
   *
   * @option dry-run
   *   Don't do anything, but show what will be done.
   * @usage dagplejelager_form:day-carers:import
   * @usage dagplejelager_form:day-carers:import --dry-run
   *
   * @phpstan-param array<string, mixed> $options
   */
  public function import(array $options = ['dry-run' => FALSE]): void {
    try {
      $connectionString = $this->getConnectionString();
      $connection = new \PDO(
        $connectionString,
        $this->importConfig['database_username'],
        $this->importConfig['database_password']
      );
      $statement = $connection->query('{CALL [dbo].[GetFederation-DPLager-Data]}');
    }
    catch (\PDOException $exception) {
      Drush::output()->writeln($exception->getMessage());
      return;
    }

    $dayCarers = [];
    try {
      while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
        $dayCarer = [];
        // Most values are padded with spaces.
        $item = array_map('trim', $item);

        if ($options['verbose'] ?? FALSE) {
          Drush::output()->writeln(json_encode($item, JSON_PRETTY_PRINT));
        }

        $id = $item['ident'];
        if (empty($id)) {
          continue;
        }

        $dayCarer['id'] = $id;
        $dayCarer['institution_id'] = $item['instId'];
        $dayCarer['institution_name'] = $item['instNavn'];
        $dayCarer['telephone_number'] = $item['telefonnr'] ?? '';

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
        // Split postDist by space and use first part as postal code and the
        // rest as locality.
        $parts = preg_split('/\s+/', $item['postDist'], 2);
        $dayCarer['postal_code'] = $parts[0];
        $dayCarer['locality'] = $parts[1] ?? '';

        $dayCarers[] = $dayCarer;
      }
    }
    catch (\PDOException $exception) {
      Drush::output()->writeln($exception->getMessage());
      return;
    }

    if (!empty($dayCarers)) {
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

  /**
   * Get connection string.
   */
  private function getConnectionString(): string {
    $config = [
      'sqlsrv:server' => $this->importConfig['database_host'],
      'database' => $this->importConfig['database_name'],
      'encrypt' => 'true',
      'trustServerCertificate' => 'false',
      'loginTimeout' => 30,
      'authentication' => 'ActiveDirectoryPassword',
    ];

    $connectionString = '';
    array_walk($config, function ($val, $key) use (&$connectionString) {
      if (!empty($connectionString)) {
        $connectionString .= '; ';
      }
      $connectionString .= "$key=$val";
    });

    return $connectionString;
  }

}
