<?php

namespace Drupal\migration_demo\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Skips process when destination field is not empty.
 *
 * Usage:
 *
 * @code
 * process:
 *   field_name:
 *     plugin: skip_on_dest_not_empty
 *     source: value
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_dest_not_empty"
 * )
 */
class SkipOnDestNotEmpty extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $id_map = $row->getIdMap();

    if (isset($id_map["destid1"]) && $nid = $id_map["destid1"]) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      if (!$node) {
        throw new MigrateException("Failed to load node with nid $nid.");
      }
      $existing_value = $node->get($destination_property)->value;

      if (!empty($existing_value)) {
        // Return existing value. MigrateSkipProcessException causes field to be empty.
        return $existing_value;
      }
    }

    return $value;
  }

}
