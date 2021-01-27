<?php

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;

/**
 * Expose a User plugin.
 *
 * @Valet(
 *   id = "node_type",
 *   label = @Translation("Node Type"),
 *   weight = 0
 * )
 */
class ValetNodeType extends ValetBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The user storage.
   * @param \Drupal\Core\Config\Entity\ConfigEntityListBuilder $list_builder
   *   Config entity list builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager, ConfigEntityListBuilder $list_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->listBuilder = $list_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_type_manager = $container->get('entity_type.manager');
    $definition = $entity_type_manager->getDefinition('node');
    $list_builder = ConfigEntityListBuilder::createInstance($container, $definition);
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $entity_type_manager,
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
    foreach ($this->entityTypeManager->getStorage('node_type')->loadMultiple() as $entity) {
      foreach ($this->listBuilder->getOperations($entity) as $id => $operation) {
        $id = 'node_type.' . $entity->id() . '.' . $id;
        $this->addResult($id, [
          'label' => $entity->label() . ': ' . $operation['title'],
          'value' => $operation['url']->toString(),
          'description' => $operation['title'],
          'command' => 'type',
        ]);
      }
      $this->addCacheTags($entity->getCacheTags());
    }
    // Clear Valet cache with user operations.
    $this->addCacheTags(['config:node_type_list']);
  }

}
