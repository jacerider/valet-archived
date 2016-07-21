<?php

/**
 * @file
 * Contains \Drupal\valet\Plugin\Valet\User.
 */

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Expose a User plugin.
 *
 *
 * @Valet(
 *   id = "user",
 *   label = @Translation("User"),
 *   weight = 0
 * )
 */
class User extends ValetBase implements ContainerFactoryPluginInterface {

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs a new UserDevelGenerate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The user storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
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
      $container->get('entity.manager')->getStorage('user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Available roles'),
      '#options' => user_role_names(TRUE),
      '#default_value' => $this->config->get('plugins.user.settings.roles'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getResults() {
    $results = array();
    $allowed = array_filter($this->config->get('plugins.user.settings.roles'));

    $query = $this->userStorage->getQuery()
      ->condition('uid', 0, '>');
    if(empty($allowed['authenticated'])){
      $query->condition('roles.target_id', $allowed, 'IN');
    }
    $uids = $query->execute();
    $users = $this->userStorage->loadMultiple($uids);

    if(!empty($users)){
      foreach($users as $user){
        $results['user.' . $user->id()] = array(
          'label' => $user->getDisplayName(),
          'value' => '/user/' . $user->id(),
          'description' => $this->t('View this user.'),
        );
      }
      // Clear Valet cache with user operations.
      $this->addCacheTags(array('user'));
    }
    return $results;
  }
}
