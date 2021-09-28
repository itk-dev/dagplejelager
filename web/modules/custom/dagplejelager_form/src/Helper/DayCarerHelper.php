<?php

namespace Drupal\dagplejelager_form\Helper;

use Drupal\Core\Database\Connection;

/**
 * Day carer helper.
 */
class DayCarerHelper {
  /**
   * The database table name.
   *
   * @var string
   */
  private $table = 'dagplejelager_form_day_carer';

  /**
   * The database (connection).
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * Constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Update day carers in database.
   *
   * @param array $dayCarers
   *   The day carers.
   *
   * @return array|null
   *   The result.
   */
  public function updateDayCarers(array $dayCarers): ?array {
    $this->database->update($this->table)
      ->fields(['deleted_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')])
      ->execute();
    foreach ($dayCarers as $dayCarer) {
      $dayCarer['deleted_at'] = NULL;
      $result[$dayCarer['id']] = $this->database
        ->upsert($this->table)
        ->key('id')
        ->fields($dayCarer)
        ->execute();
    }

    return $result ?? NULL;
  }

  /**
   * Search day carers.
   *
   * @param string $terms
   *   The search terms.
   *
   * @return array
   *   The day carer matching the query.
   */
  public function searchDayCarers(string $terms): array {
    $terms = preg_split('/\s+/', $terms);
    $query = $this->database
      ->select($this->table, 't')
      ->fields('t')
      ->isNull('deleted_at');
    // Build condition from search terms and searchable fields.
    $searchFields = $this->getSearchFields();
    // All terms must match.
    $searchConditions = $query->andConditionGroup();
    foreach ($terms as $term) {
      // The term must match one column.
      $termCondition = $query->orConditionGroup();
      $value = '%' . $this->database->escapeLike($term) . '%';
      foreach ($searchFields as $field) {
        $termCondition->condition($field, $value, 'LIKE');
      }
      $searchConditions->condition($termCondition);
    }
    $query->condition($searchConditions);

    $result = $query
      ->execute()
      ->fetchAll();

    $dayCarers = [];
    foreach ($result as $item) {
      $item = (array) $item;
      $dayCarers[] = [
        'name' => [
          'given_name' => $item['given_name'],
          'family_name' => $item['family_name'],
        ],
        'address' => [
          'address_line1' => $item['address_line1'],
          'organization' => $item['organization'],
          'postal_code' => $item['postal_code'],
          'locality' => $item['locality'],
        ],
        'institution' => [
          'id' => $item['institution_id'],
          'name' => $item['institution_name'],
        ],
      ];
    }

    return $dayCarers;
  }

  /**
   * Implements hook_schema().
   */
  public function schema() {
    $schema['dagplejelager_form_day_carer'] = [
      'description' => 'Day carers.',
      'fields' => [
        'id' => [
          'description' => 'ID',
          'type' => 'varchar',
          'length' => 16,
          'not null' => TRUE,
        ],
        'institution_id' => [
          'description' => 'Institution ID',
          'type' => 'varchar',
          'length' => 16,
          'not null' => TRUE,
        ],
        'institution_name' => [
          'description' => 'Institution name',
          'type' => 'varchar',
          'length' => 256,
          'not null' => TRUE,
        ],
        'given_name' => [
          'description' => '',
          'type' => 'varchar',
          'length' => 256,
          'not null' => TRUE,
        ],
        'family_name' => [
          'description' => '',
          'type' => 'varchar',
          'length' => 256,
          'not null' => TRUE,
        ],
        'address_line1' => [
          'description' => '',
          'type' => 'varchar',
          'length' => 256,
          'not null' => TRUE,
        ],
        'organization' => [
          'description' => '',
          'type' => 'varchar',
          'length' => 256,
          'not null' => TRUE,
        ],
        'postal_code' => [
          'description' => '',
          'type' => 'varchar',
          'length' => 16,
          'not null' => TRUE,
        ],
        'locality' => [
          'description' => '',
          'type' => 'varchar',
          'length' => 256,
          'not null' => TRUE,
        ],
        'deleted_at' => [
          'description' => '',
          'type' => 'varchar',
          'mysql_type' => 'datetime',
          'not null' => FALSE,
        ],
      ],
      'primary key' => ['id'],
      'indexes' => [
        'institution_id' => ['institution_id'],
      ],
    ];

    return $schema;
  }

  /**
   * Get search fields.
   *
   * @return array
   *   The search fields.
   */
  private function getSearchFields(): array {
    $schema = $this->schema();

    return array_keys(
      array_filter(
        $schema['dagplejelager_form_day_carer']['fields'],
        static function ($definition) {
          // Search only text fields.
          return 'varchar' === $definition['type'] && ('datetime' !== $definition['mysql_type'] ?? NULL);
        }
      )
    );
  }

}
