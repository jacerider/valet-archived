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
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;

class ValetController {

  /**
   * Return the data for Valet consumption.
   *
   * @return json
   */
  public function data() {
    $cid = 'valet';
    $data = array();
    if ($cache = \Drupal::cache()->get($cid) && false) {
      $data = $cache->data;
    }
    else {
      $routes = array();
      $tags = array(
        'valet',
        'config:valet.admin',
      );
      $config = \Drupal::config('valet.admin');
      $manager = \Drupal::service('plugin.manager.valet');
      foreach($config->get('plugins') as $id => $plugin){
        if(!empty($plugin['enabled'])){
          $instance = $manager->createInstance($id);
          $plugin_results = $instance->getResults();
          if(is_array($plugin_results)){
            $routes += $plugin_results;
          }
          $tags = Cache::mergeTags($tags, $instance->getCacheTags());
        }
      }
      $data = array_values($routes);
      \Drupal::cache()->set($cid, $data, CacheBackendInterface::CACHE_PERMANENT, $tags);
    }
    return new JsonResponse($data);
  }
}
