<?php

/**
 * @file
 * Contains Drupal\valet\Plugin\ValetBase.
 */

namespace Drupal\valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

/**
 * Base class for Valet plugins.
 */
abstract class ValetBase extends PluginBase implements ValetInterface {
  use RefinableCacheableDependencyTrait;

  /**
   * Valet plugin config.
   *
   * @var array
   */
  protected $config = array();

  /**
   * The plugin settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * Constructs a new SelectionBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = \Drupal::config('valet.admin');
    $this->settings = $this->config->get('plugins.' . $this->getBaseId() . '.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return array();
  }

  /**
   * Build out all results. Utilize addResult() to add an individual item to
   * the results output.
   *
   * @return none
   */
  protected function prepareResults() {
    // Utilize addResult() to build up a list of results.
  }

  /**
   * {@inheritdoc}
   */
  public function getResults() {
    $this->prepareResults();
    $results = &drupal_static('addResult');
    return is_array($results) ? array_filter($results) : [];
  }

  /**
   * Expose an item to the results.
   *
   * @param string $id
   *   A unique id.
   * @param array $data
   *   An array of data that must contain at minium
   *   ('label' => 'Administration', 'value' => '/admin').
   */
  protected function addResult($id, $data) {
    $results = &drupal_static(__FUNCTION__);
    if (!is_array($data)) {
      return;
    }
    $data += ['label' => '', 'value' => '', 'description' => '', 'command' => '', 'icon' => ''];
    if (isset($results[$id]) || empty($data['label']) || empty($data['value'])) {
      return;
    }
    if (!empty($data['label']) && is_string($data['label'])) {
      $data['label'] = $this->t($data['label']);
    }
    if (!empty($data['description']) && is_string($data['description'])) {
      $data['description'] = $this->t($data['description']);
    }
    $results[$id] = $data;
  }

}
