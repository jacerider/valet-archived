<?php

/**
 * @file
 * Contains Drupal\valet\Plugin\ValetBase.
 */

namespace Drupal\valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for Valet plugins.
 */
abstract class ValetBase extends PluginBase implements ValetInterface {

  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  public function buildForm(array $form, FormStateInterface $form_state, $config) {
    return array();
  }

  public function getResults($config) {
    return array();
  }

}
