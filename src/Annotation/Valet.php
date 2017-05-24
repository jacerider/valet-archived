<?php

/**
 * @file
 * Contains Drupal\valet\Annotation\Valet.
 */

namespace Drupal\valet\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Valet item annotation object.
 *
 * @see \Drupal\valet\Plugin\ValetManager
 * @see plugin_api
 *
 * @Annotation
 */
class Valet extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * An integer to determine the weight of this plugin relative to other
   * plugins in the Valet UI.
   *
   * @var int optional
   */
  public $weight = NULL;

}
