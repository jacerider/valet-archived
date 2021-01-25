<?php

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Expose a Node plugin.
 *
 * @Valet(
 *   id = "node",
 *   label = @Translation("Node"),
 *   weight = 0
 * )
 */
class ValetNode extends ValetBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructs a new ValetNode object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The node storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $entity_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['description']['#markup'] = $this->t('Expose nodes to Valet.');
    $form['shortcut']['#markup'] = '<br><code>' . $this->t('<strong>SHORTCUT</strong> :node') . '</code>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareResults() {
    foreach ($this->nodeStorage->loadMultiple() as $node) {
      $this->addResult('node.' . $node->id(), [
        'label' => $node->label(),
        'value' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(),
        'description' => 'View this content.',
        'command' => 'node',
      ]);
    }
    // Clear Valet cache with node operations.
    $this->addCacheTags(['node_list']);
  }

}
