<?php

namespace Drupal\migration_demo\Plugin\migrate\source;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\source\Url;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Source plugin for parks migrations.
 *
 * @MigrateSource(
 *   id = "parks_source"
 * )
 */
class ParksSource extends Url implements ContainerFactoryPluginInterface {

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, ClientInterface $httpClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $park_id = $row->getSourceProperty('src_id');
    $url = 'http://migration-api.lndo.site/geometry?park_no=' . $park_id;

    try {
      $response = $this->httpClient->get($url);
    }
    catch  (\Exception $e) {
      // API call was unsuccessful. Skip this row.
      // Will be reprocessed on next migration.
      $this->logRowError($park_id, $row, $e);
      return FALSE;
    }

    if (empty($response)) {
      // API call was unsuccessful. Skip this row.
      // Will be reprocessed on next migration.
      $this->logRowError($park_id, $row);
      return FALSE;
    }

    $response_contents = $response->getBody()->getContents();
    $response_contents = json_decode($response_contents, TRUE);
    if (isset($response_contents[0]['the_geom'])) {
      $geodata = $response_contents[0]['the_geom'];
      // Encode back into json because geofield_wkt process plugin expects json input.
      $row->setSourceProperty('geodata', json_encode($geodata));
    }

    return parent::prepareRow($row);
  }

  /**
   * Logs row level error message.
   *
   * @param int $park_id
   *   Park ID.
   * @param \Drupal\migrate\Row $row
   *   The row object.
   * @param \Exception $exception
   *   Exception object.
   */
  protected function logRowError($park_id, Row $row, $exception = NULL) {
    $error_message = "SKIPPING ROW: API call for park id $park_id failed.";
    if ($exception) {
      $this->idMap->saveMessage($row->getSourceIdValues(), $exception->getMessage(), MigrationInterface::MESSAGE_ERROR);
    }
    else {
      $this->idMap->saveMessage($row->getSourceIdValues(), $error_message, MigrationInterface::MESSAGE_ERROR);
    }
    $this->idMap->saveIdMapping($row, [], MigrateIdMapInterface::STATUS_FAILED);
  }

}
