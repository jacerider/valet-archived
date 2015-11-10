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
    $routes = array();
    $config = \Drupal::config('valet.admin');
    $manager = \Drupal::service('plugin.manager.valet');
    foreach($config->get('plugins') as $id => $plugin){
      if(!empty($plugin['enabled'])){
        $instance = $manager->createInstance($id);
        $plugin_results = $instance->getResults($config);
        if(is_array($plugin_results)){
          $routes += $plugin_results;
        }
      }
    }
    return new JsonResponse(array_values($routes));
  }
}
