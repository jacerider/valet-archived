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

/**
 * Expose a Menu plugin.
 *
 *
 * @Valet(
 *   id = "menu",
 *   label = @Translation("Menu")
 * )
 */
class Menu extends ValetBase {

  public function buildForm(array $form, FormStateInterface $form_state, $config) {

    $form['menus'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Available menus'),
      '#options' => menu_ui_get_menus(),
      '#default_value' => $config->get('plugins.menu.settings.menus'),
    );

    return $form;
  }

  public function getResults($config) {
    $menus = menu_ui_get_menus();
    $enabled = array_filter($config->get('plugins.menu.settings.menus'));

    $menu_tree = \Drupal::menuTree();
    $tree = array();
    foreach($enabled as $mid){
      if(isset($menus[$mid])){
        $parameters = new MenuTreeParameters();
        $parameters->excludeRoot()->setMaxDepth(4)->onlyEnabledLinks();
        $tree += $menu_tree->load($mid, $parameters);
      }
    }

    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);
    return $this->resultsBuild($tree);
  }

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
      // if(strpos($url, 'token=')){
      //   $url .= '&destination=' . \Drupal::request()->getRequestUri();
      // }

      $routes[$link->getPluginId()] = array(
        'label' => $link->getTitle(),
        'value' => $url,
        'description' => $link->getDescription(),
      );
      if($data->subtree){
        $routes += $this->resultsBuild($data->subtree);
      }
    }
    return $routes;
  }
}
