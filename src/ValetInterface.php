<?php

/**
 * @file
 * Contains Drupal\valet\Plugin\ValetInterface.
 */

namespace Drupal\valet;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for Valet plugins.
 */
interface ValetInterface extends PluginInspectionInterface {

  /**
   * Return the name of the plugin.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Return a configuration form for the plugin.
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state);

  /**
   * Return the results of the plugin.
   *
   * Example return:
   * @code
   *   return array(
   *     'uniqueId' => array(
   *       'label' => '',
   *       'value' => '',
   *       'description' => '',
   *     ),
   *   );
   *
   * @return array
   *   The array of results, keyed by unique id.
   */
  public function getResults();

}
