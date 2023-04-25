<?php

namespace Drupal\migration_demo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Configure Migration Demo settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migration_demo_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['migration_demo.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['migrate_date_start'] = [
      '#type' => 'textfield',
      '#title' => t('Start Date'),
      '#default_value' => $this->config('migration_demo.settings')->get('migrate_date_start') ?? $this->config('migration_demo.settings')->get('migrate_date_start') || '',
      '#description' => t('Imports content modified after this date. Enter a specific date or a relative date string. Examples: "2019-01-01" or "-2 weeks". Leave blank to ignore.'),
    ];

    $form['promoted_parks'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t('Promoted Parks.'),
      '#description' => t('Can enter multiple parks separated by commas'),
      '#tags' => TRUE,
      '#target_type' => 'node',
      '#bundles' => ['park'],
    ];

    // Need to set this way to avoid error with entity_autocomplete field.
    // InvalidArgumentException: The #default_value property has to be an entity object or an array of entity objects.
    if (!empty($this->config('migration_demo.settings')->get('promoted_parks'))) {
      $form['promoted_parks']['#default_value'] = Node::loadMultiple($this->config('migration_demo.settings')->get('promoted_parks'));
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('migration_demo.settings')
      ->set('migrate_date_start', $form_state->getValue('migrate_date_start'))
      ->save();

    $promoted_parks = [];
    foreach ($form_state->getValue('promoted_parks') as $nid) {
      $promoted_parks[] = $nid['target_id'];
    }
    $this->config('migration_demo.settings')
      ->set('promoted_parks', $promoted_parks)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
