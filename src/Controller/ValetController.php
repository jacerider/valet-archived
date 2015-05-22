<?php

/**
 * @file
 * Contains \Drupal\valet\Controller\ValetController.
 */

namespace Drupal\valet\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Routing\RouteProvider;

class ValetController {

  public function data() {
    $tree = array();
    $menu_tree = \Drupal::menuTree();

    // Devel Routes
    $module_exists = \Drupal::moduleHandler()->moduleExists('devel');
    if ($module_exists) {
      $parameters = new \Drupal\Core\Menu\MenuTreeParameters();
      $parameters->onlyEnabledLinks();
      $tree += $menu_tree->load('devel', $parameters);
    }

    // Admin Routes
    $parameters = new \Drupal\Core\Menu\MenuTreeParameters();
    $parameters->setRoot('system.admin')->excludeRoot()->setMaxDepth(3);
    $tree += $menu_tree->load(NULL, $parameters);

    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);

    $routes = $this->build($tree);

    return new JsonResponse(array_values($routes));
  }

  protected function build($tree){
    $routes = array();
    foreach ($tree as $data) {
      $link = $data->link;
      // Generally we only deal with visible links, but just in case.
      if (!$link->isEnabled()) {
        continue;
      }
      $routes[$link->getPluginId()] = array(
        'label' => $link->getTitle(),
        'value' => $link->getUrlObject()->toString(),
        'description' => $link->getDescription(),
      );
      if($data->subtree){
        $routes += $this->build($data->subtree);
      }
    }
    return $routes;
  }
}
