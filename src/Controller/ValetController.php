<?php

/**
 * @file
 * Contains \Drupal\valet\Controller\ValetController.
 */

namespace Drupal\valet\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;

class ValetController {

  public function data() {
    $tree = array();
    $menu_tree = \Drupal::menuTree();

    // Devel Routes
    $module_exists = \Drupal::moduleHandler()->moduleExists('devel');
    if ($module_exists) {
      $parameters = new MenuTreeParameters();
      $parameters->onlyEnabledLinks();
      $tree += $menu_tree->load('devel', $parameters);
    }

    // Admin Routes
    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(3)->onlyEnabledLinks();
    $tree += $menu_tree->load('admin', $parameters);

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

      // @todo This is just an ugly workaround for Drupal 8's inability to
      // process URL CSRFs without a render array.
      $urlBubbleable = $link->getUrlObject()->toString(TRUE);
      $urlRender = array(
        '#markup' => $urlBubbleable->getGeneratedUrl(),
      );
      BubbleableMetadata::createFromRenderArray($urlRender)
        ->merge($urlBubbleable)->applyTo($urlRender);

      $routes[$link->getPluginId()] = array(
        'label' => $link->getTitle(),
        'value' => \Drupal::service('renderer')->renderPlain($urlRender),
        'description' => $link->getDescription(),
      );
      if($data->subtree){
        $routes += $this->build($data->subtree);
      }
    }
    return $routes;
  }
}
