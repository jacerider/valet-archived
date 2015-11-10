<?php

/**
 * @file
 * Contains \Drupal\valet\Plugin\Valet\User.
 */

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;

/**
 * Expose a User plugin.
 *
 *
 * @Valet(
 *   id = "user",
 *   label = @Translation("User")
 * )
 */
class User extends ValetBase {

  public function buildForm(array $form, FormStateInterface $form_state, $config) {

    $form['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Available roles'),
      '#options' => user_role_names(TRUE),
      '#default_value' => $config->get('plugins.user.settings.roles'),
    );

    return $form;
  }

  public function getResults($config) {
    $results = array();
    $results['user.1'] = array(
      'label' => 'title',
      'value' => 'value',
      'description' => 'description',
    );
    return $results;
  }
}
