<?php

namespace Drupal\valet\Plugin\Escort;

use Drupal\escort\Plugin\Escort\EscortPluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

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
  public function escortPreview() {
    return [
      '#tag' => 'a',
      '#markup' => $this->t('Go to'),
      '#icon' => 'fa-search',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function escortBuild() {
    $build = $this->escortPreview();
    $build['#attributes']['class'][] = 'valet-trigger';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function escortAccess(AccountInterface $account) {
    return $account->hasPermission('access valet') ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
