<?php

namespace Drupal\valet\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Defines a link plugin.
 *
 * @ExoToolbarItem(
 *   id = "valet",
 *   admin_label = @Translation("Valet"),
 *   category = @Translation("Common"),
 * )
 */
class Valet extends ExoToolbarItemBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'title' => 'Go to',
      'icon' => 'regular-search',
      'mark_only_horizontal' => TRUE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    $element = parent::elementBuild();
    $element->addClass('valet-trigger');
    $element->setTag('a');
    $element->setAsLink('');
    return $element;
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
  protected function itemAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access valet');
  }

}
