<?php

namespace Drupal\migration_demo\Plugin\migrate_plus\data_fetcher;

use Drupal\migrate\MigrateException;
use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\Http;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Retrieve data over an HTTP connection for migration with date filtering.
 *
 * Example:
 *
 * @code
 * source:
 *   plugin: url
 *   data_fetcher_plugin: date_http
 *   headers:
 *     Accept: application/json
 *     User-Agent: Internet Explorer 6
 *     Authorization-Key: secret
 *     Arbitrary-Header: foobarbaz
 * @endcode
 *
 * @DataFetcher(
 *   id = "date_http",
 *   title = @Translation("Date HTTP")
 * )
 */
class DateHttp extends Http {

  /**
   * The url parameters.
   */
  protected array $urlParameters = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $date_start = \Drupal::config('migration_demo.settings')->get('migrate_date_start');

    $date_parameters = [];

    if (!empty($date_start)) {
      if ($date_start != '0') {
        $date_parameters['updated_date_gte'] = date('Y-m-d', strtotime($date_start));
      }
    }

    // Set url parameters for all API calls.
    $this->urlParameters = $date_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseContent(string $url): string {
    // Build url with query parameters.
    $options['query'] = $this->urlParameters;

    return (string) $this->getResponse($url, $options)->getBody();
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse($url, $options = []): ResponseInterface {
    try {
      $options['headers'] = $this->getRequestHeaders();
      if (!empty($this->configuration['authentication'])) {
        $options = array_merge($options, $this->getAuthenticationPlugin()->getAuthenticationOptions());
      }
      if (!empty($this->configuration['request_options'])) {
        $options = array_merge($options, $this->configuration['request_options']);
      }
      $response = $this->httpClient->get($url, $options);
      if (empty($response)) {
        throw new MigrateException('No response at ' . $url . '.');
      }
    }
    catch (RequestException $e) {
      throw new MigrateException('Error message: ' . $e->getMessage() . ' at ' . $url . '.');
    }
    return $response;
  }

}
