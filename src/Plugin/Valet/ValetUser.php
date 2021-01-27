<?php

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Expose a User plugin.
 *
 * @Valet(
 *   id = "user",
 *   label = @Translation("User"),
 *   weight = 0
 * )
 */
class ValetUser extends ValetBase implements ContainerFactoryPluginInterface {

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs a new ValetUser object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The user storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $entity_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager')->getStorage('user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['description']['#markup'] = $this->t('Expose users to Valet.');
    $form['shortcut']['#markup'] = '<br><code>' . $this->t('<strong>SHORTCUT</strong> :user') . '</code>';
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => t('Available roles'),
      '#options' => user_role_names(TRUE),
      '#default_value' => $this->settings['roles'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareResults() {
    $allowed = array_filter($this->settings['roles']);

    $query = $this->userStorage->getQuery()
      ->condition('uid', 0, '>');
    if (!empty($allowed) && empty($allowed['authenticated'])) {
      $query->condition('roles.target_id', $allowed, 'IN');
    }
    $uids = $query->execute();
    $users = $this->userStorage->loadMultiple($uids);

    if (!empty($users)) {
      foreach ($users as $user) {
        /** @var \Drupal\user\UserInterface $user */
        $this->addResult('user.' . $user->id(), [
          'label' => $user->getDisplayName(),
          'value' => '/user/' . $user->id(),
          'description' => 'View this user.',
          'command' => 'user',
        ]);
      }
      // Clear Valet cache with user operations.
      $this->addCacheTags(['user']);
    }
  }

}
