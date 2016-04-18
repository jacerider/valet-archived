<?php

/**
 * @file
 * Contains \Drupal\valet\Plugin\Valet\Menu.
 */

namespace Drupal\valet\Plugin\Valet;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\valet\ValetBase;
use Drupal\Core\Extension\ModuleHandler;

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
class Menu extends ValetBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if(\Drupal::moduleHandler()->moduleExists('menu_ui')){
      $form['menus'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Available menus'),
        '#options' => menu_ui_get_menus(),
        '#default_value' => $this->config->get('plugins.menu.settings.menus'),
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getResults() {
    $menu_tree = \Drupal::menuTree();
    if(\Drupal::moduleHandler()->moduleExists('menu_ui')){
      $tree = array();
      $enabled = array_filter($this->config->get('plugins.menu.settings.menus'));
      $menus = menu_ui_get_menus();
      foreach($enabled as $mid){
        if(isset($menus[$mid])){
          $parameters = new MenuTreeParameters();
          $parameters->excludeRoot()->setMaxDepth(4)->onlyEnabledLinks();
          $tree += $menu_tree->load($mid, $parameters);
        }
      }
    }
    else{
      $tree = array();
      $roots = ['system.admin'];
      foreach($roots as $root){
        $parameters = new MenuTreeParameters();
        $parameters->setRoot($root)->setMaxDepth(4)->onlyEnabledLinks();
        $tree += $menu_tree->load(NULL, $parameters);
      }
      if(\Drupal::moduleHandler()->moduleExists('devel')){
        $parameters = new MenuTreeParameters();
        $parameters->setMaxDepth(4)->onlyEnabledLinks();
        $tree += $menu_tree->load('devel', $parameters);
      }
    }

    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);
    if(!empty($tree)){
      // Clear Valet cache with route operations.
      // @see \Drupal\Core\EventSubscriber\MenuRouterRebuildSubscriber
      $this->addCacheTags(array('local_task'));
    }
    return $this->resultsBuild($tree);
  }

  /**
   * Given a tree of menu items, prepare for delivery to Valet.
   *
   * @param array $tree
   *   An array of menu items.
   *
   * @return array
   */
  protected function resultsBuild($tree){
    $routes = array();
    foreach ($tree as $data) {
      $link = $data->link;
      // Generally we only deal with visible links, but just in case.
      if (!$link->isEnabled()) {
        continue;
      }

      // @todo This is just an ugly workaround for Drupal 8's inability to
      // process URL CSRFs without a render array.
      $urlBubbleable = $link->getUrlObject()->toString(TRUE);
      $urlRender = array(
        '#markup' => $urlBubbleable->getGeneratedUrl(),
      );
      BubbleableMetadata::createFromRenderArray($urlRender)
        ->merge($urlBubbleable)->applyTo($urlRender);
      $url = \Drupal::service('renderer')->renderPlain($urlRender);
      $url = str_replace('/api/valet', 'RETURN_URL', $url);

      $routes[$link->getPluginId()] = array(
        'label' => $link->getTitle(),
        'value' => htmlspecialchars_decode($url),
        'description' => $link->getDescription(),
      );
      if($data->subtree){
        $routes += $this->resultsBuild($data->subtree);
      }
    }
    return $routes;
  }
}
