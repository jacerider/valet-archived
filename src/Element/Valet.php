<?php

namespace Drupal\valet\Element;

use Drupal\Core\Render\Element\RenderElement;

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

    $info = [
      '#theme' => 'valet',
      '#attached' => [
        'library' => [
          'valet/valet',
        ],
      ],
      '#pre_render' => [
        [$class, 'preRenderValet'],
      ],
      // Metadata for the valet wrapping element.
      '#attributes' => [
        'id' => 'valet',
        'class' => ['valet'],
        'role' => 'group',
        'aria-label' => $this->t('Valet quick navigation'),
      ],
    ];
    return $info;
  }

  /**
   * Prepares a #type 'valet' render element for input.html.twig.
   */
  public static function preRenderValet($element) {
    $renderer = \Drupal::service('renderer');
    $config = \Drupal::config('valet.admin');
    $csrfToken = \Drupal::service('csrf_token');
    $cid = 'valet:' . $csrfToken->get('/api/valet') . ':timestamp';
    $cache_timestamp = \Drupal::cache()->get($cid);
    $element['#attributes']['class'][] = $config->get('position');
    $element['#attached']['drupalSettings']['valet'] = [
      'modifier' => $config->get('modifier'),
      'hotkey' => $config->get('hotkey'),
      'cache' => $cache_timestamp ? $cache_timestamp->data : NULL,
    ];
    $renderer->addCacheableDependency($element, $config);
    return $element;
  }

}
