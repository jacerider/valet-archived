<?php

/**
 * @file
 * Contains \Drupal\valet\Plugin\Valet\Menu.
 */

namespace Drupal\valet\Plugin\Valet;

use Drupal\valet\ValetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Expose a Menu plugin.
 *
 *
 * @Valet(
 *   id = "menu",
 *   label = @Translation("Menu"),
 *   weight = -1
 * )
 */
class ValetMenu extends ValetBase implements ContainerFactoryPluginInterface {

  /**
   * The local task manager.
   *
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  protected $localTaskManager;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LocalTaskManagerInterface $local_task_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->localTaskManager = $local_task_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('plugin.manager.menu.local_task')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if(\Drupal::moduleHandler()->moduleExists('menu_ui')){
      $form['menus'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Available menus'),
        '#options' => $this->getMenuLabels(),
        '#default_value' => $this->settings['menus'],
      );
    }

    return $form;
  }

  /**
   * Return an associative array of menus names.
   *
   * @return array
   *   An array with the machine-readable names as the keys, and human-readable
   *   titles as the values.
   */
  protected function getMenuLabels() {
    $menus = [];
    foreach (Menu::loadMultiple() as $menu_name => $menu) {
      $menus[$menu_name] = $menu->label();
    }
    asort($menus);
    return $menus;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareResults() {
    $enabled = array_filter($this->settings['menus']);

    $this->addResult('front', [
      'label' => 'Front Page',
      'value' => Url::fromRoute('<front>')->toString(),
      'description' => 'Go to front page',
      'command' => 'front',
    ]);

    foreach($enabled as $mid){
      if ($mid === '0') {
        continue;
      }

      $menu_tree = \Drupal::menuTree();

      // Build the menu tree.
      $menu_tree_parameters = new MenuTreeParameters();
      $tree = $menu_tree->load($mid, $menu_tree_parameters);

      $manipulators = array(
        array('callable' => 'menu.default_tree_manipulators:checkAccess'),
        array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
      );
      $tree = $menu_tree->transform($tree, $manipulators);

      foreach ($tree as $key => $link) {
        $this->getChildren($link);
      }
    }

    // Clear Valet cache with route operations.
    // @see \Drupal\Core\EventSubscriber\MenuRouterRebuildSubscriber
    $this->addCacheTags(array('local_task'));
  }

  /**
   * Helper function to traverse down through a menu structure.
   */
  protected function getChildren($link) {
    $l = isset($link->link) ? $link->link : NULL;
    if (!$l) {
      return;
    }
    $url = $l->getUrlObject();
    if ($url->access()) {

      $urlString = $url->toString();
      if($url->getRouteName() == 'devel.cache_clear'){
        // @todo This is just an ugly workaround for Drupal 8's inability to
        // process URL CSRFs without a render array.
        $urlBubbleable = $l->getUrlObject()->toString(TRUE);
        $urlRender = array(
          '#markup' => $urlBubbleable->getGeneratedUrl(),
        );
        BubbleableMetadata::createFromRenderArray($urlRender)
          ->merge($urlBubbleable)->applyTo($urlRender);
        $urlString = \Drupal::service('renderer')->renderPlain($urlRender);
        $urlString = str_replace('/api/valet', 'RETURN_URL', htmlspecialchars_decode($urlString));
      }

      $this->addResult($url->getRouteName(), [
        'label' => $l->getTitle(),
        'value' => $urlString,
        'description' => $l->getDescription(),
      ]);
    }

    if ($link->subtree) {
      foreach ($link->subtree as $below_link) {
        $this->getChildren($below_link);
      }
    }

    $manager = \Drupal::service('plugin.manager.menu.local_task');
    $tasks = $manager->getLocalTasksForRoute($l->getRouteName());
    if ($tasks) {
      foreach ($tasks as $key => $task) {
        $this->getTasks($l, $task);
      }
    }
  }

  /**
   * Helper function to traverse the local tasks.
   */
  protected function getTasks($link, $task) {
    if (is_array($task)) {
      foreach ($task as $key => $local_task) {
        $this->getTasks($link, $local_task);
      }
    }
    else {
      $local_task = $task;
    }

    if (is_object($local_task)) {
      $route_name = $local_task->getPluginDefinition()['route_name'];
      $route_parameters = $local_task->getPluginDefinition()['route_parameters'];
      $url = Url::fromRoute($route_name, $route_parameters);

      if ($url->access()) {
        $this->addResult($route_name, [
          'label' => $link->getTitle() . ': ' . $local_task->getTitle(),
          'value' => $url->toString(),
          'description' => $local_task->getTitle(),
        ]);
      }
    }
  }
}
