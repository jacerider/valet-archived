<?php

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
   *   The label.
   */
  public function getLabel();

  /**
   * Return a configuration form for the plugin.
   *
   * @return array
   *   The form.
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

  /**
   * Returns if the plugin can be used.
   *
   * @return bool
   *   TRUE if the formatter can be used, FALSE otherwise.
   */
  public static function isApplicable();

}
