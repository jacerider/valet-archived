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
   * {@inheritdoc}
   */
  public function getResults() {
    return array();
  }

}
