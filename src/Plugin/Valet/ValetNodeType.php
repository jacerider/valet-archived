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
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;

/**
 * Expose a User plugin.
 *
 *
 * @Valet(
 *   id = "node_type",
 *   label = @Translation("Node Type"),
 *   weight = 0
 * )
 */
class ValetNodeType extends ValetBase implements ContainerFactoryPluginInterface {

  /**
   * The node type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityListBuilder
   */
  protected $listBuilder;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManager $entity_manager, ConfigEntityListBuilder $list_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
    $this->listBuilder = $list_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_manager = $container->get('entity.manager');
    $definition = $entity_manager->getDefinition('node');
    $list_builder = ConfigEntityListBuilder::createInstance($container, $definition);
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $entity_manager,
      $list_builder
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description']['#markup'] = $this->t('Expose inidividual content type management routes to Valet (ie: Manage fields, Manage form display, Manage display).');
    $form['shortcut']['#markup'] = '<br><code>' . $this->t('<strong>SHORTCUT</strong> :type') . '</code>';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareResults() {
    foreach ($this->entityManager->getStorage('node_type')->loadMultiple() as $entity) {
      foreach($this->listBuilder->getOperations($entity) as $id => $operation) {
        $id = 'node_type.' . $entity->id() . '.' . $id;
        $this->addResult($id, [
          'label' => $entity->label() . ': ' . $operation['title'],
          'value' => $operation['url']->toString(),
          'description' => $operation['title'],
          'command' => 'type',
        ]);
      }
    }
    // Clear Valet cache with user operations.
    $this->addCacheTags(array('node_type'));
  }
}
