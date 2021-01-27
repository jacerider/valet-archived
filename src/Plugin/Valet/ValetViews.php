<?php

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Expose a User plugin.
 *
 * @Valet(
 *   id = "views",
 *   label = @Translation("Views"),
 *   weight = 0
 * )
 */
class ValetViews extends ValetBase implements ContainerFactoryPluginInterface {

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
   *   The list builder.
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
    $definition = $entity_type_manager->getDefinition('view');
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
  public function access(AccountInterface $account) {
    return $account->hasPermission('administer views') ? AccessResult::allowed() : AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description']['#markup'] = $this->t('Expose inidividual views management routes to Valet (ie: Manage fields, Manage form display, Manage display).');
    $form['shortcut']['#markup'] = '<br><code>' . $this->t('<strong>SHORTCUT</strong> :view') . '</code>';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareResults() {
    foreach ($this->entityTypeManager->getStorage('view')->loadMultiple() as $entity) {
      foreach ($this->listBuilder->getOperations($entity) as $id => $operation) {
        $id = 'view.' . $entity->id() . '.' . $id;
        $this->addResult($id, [
          'label' => $entity->label() . ': ' . $operation['title'],
          'value' => $operation['url']->toString(),
          'description' => $operation['title'],
          'command' => 'view',
        ]);
      }
      $this->addCacheTags($entity->getCacheTags());
    }
    // Clear Valet cache with user operations.
    $this->addCacheTags(['config:view_list']);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable() {
    return \Drupal::moduleHandler()->moduleExists('views');
  }

}
