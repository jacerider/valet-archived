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
    return array(
      '#theme' => 'valet',
      '#attached' => array(
        'library' => array(
          'valet/valet',
        ),
      ),
      // Metadata for the valet wrapping element.
      '#attributes' => array(
        'id' => 'valet',
        'role' => 'group',
        'aria-label' => $this->t('Valet quick navigation'),
      ),
    );
  }

}
