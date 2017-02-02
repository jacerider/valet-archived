<?php

namespace Drupal\valet\Plugin\Escort;

use Drupal\escort\Plugin\Escort\EscortPluginBase;

/**
 * Defines a link plugin.
 *
 * @Escort(
 *   id = "valet",
 *   admin_label = @Translation("Valet"),
 *   category = @Translation("Basic"),
 * )
 */
class Valet extends EscortPluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesIcon = FALSE;

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#tag' => 'a',
      '#markup' => $this->t('Go to'),
      '#attributes' => ['class' => ['valet-trigger']],
      '#icon' => 'fa-search',
    ];
  }

}
