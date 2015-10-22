<?php

/**
 * @file
 * Contains \Drupal\vaet\Element\Valet.
 */

namespace Drupal\valet\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Element;

/**
 * Provides a render element for Valet.
 *
 * @RenderElement("valet")
 */
class Valet extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    $info = array(
      '#theme' => 'valet',
      '#attached' => array(
        'library' => array(
          'valet/valet',
        ),
      ),
      '#pre_render' => array(
        array($class, 'preRenderValet'),
      ),
      // Metadata for the valet wrapping element.
      '#attributes' => array(
        'id' => 'valet',
        'role' => 'group',
        'aria-label' => $this->t('Valet quick navigation'),
      ),
    );
    return $info;
  }

  /**
   * Prepares a #type 'valet' render element for input.html.twig.
   */
  public static function preRenderValet($element) {
    $renderer = \Drupal::service('renderer');
    $config = \Drupal::config('valet.admin');
    $element['#attached']['drupalSettings']['valet'] = array(
      'modifier' => $config->get('modifier'),
      'hotkey' => $config->get('hotkey'),
    );
    $renderer->addCacheableDependency($element, $config);
    return $element;
  }

}
