<?php

/**
 * @file
 * Contains \Drupal\valet\Plugin\Valet\User.
 */

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Url;

/**
 * Expose a User plugin.
 *
 *
 * @Valet(
 *   id = "node_add",
 *   label = @Translation("Node Add"),
 *   weight = 0
 * )
 */
class ValetNodeAdd extends ValetBase implements ContainerFactoryPluginInterface {

  /**
   * The node type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityManager;

  /**
   * Constructs a new ValetUser object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The user storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManager $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description']['#markup'] = $this->t('Expose inidividual content type creation routes to Valet (ie: Add Basic Page).');
    $form['shortcut']['#markup'] = '<br><code>' . $this->t('<strong>SHORTCUT</strong> :add') . '</code>';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareResults() {
    foreach ($this->entityManager->getStorage('node_type')->loadMultiple() as $entity) {
      if ($this->entityManager->getAccessControlHandler('node')->createAccess($entity)) {
        $this->addResult('node.add.' . $entity->id(), [
          'label' => $entity->label() . ': Add',
          'value' => Url::fromRoute('node.add', ['node_type' => $entity->id()])->toString(),
          'description' => 'Add a new ' . $entity->label() . '.',
          'command' => 'add',
        ]);

      }

    }
  }
}
