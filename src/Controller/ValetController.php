<?php

/**
 * @file
 * Contains \Drupal\valet\Controller\ValetController.
 */

namespace Drupal\valet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;
use Drupal\Core\Access\CsrfTokenGenerator;

class ValetController extends ControllerBase {

  /**
   * The CSRF token generator to validate the form token.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(CsrfTokenGenerator $csrf_token, ModuleHandlerInterface $module_handler) {
    $this->csrfToken = $csrf_token;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('csrf_token'),
      $container->get('module_handler')
    );
  }

  /**
   * Return the data for Valet consumption.
   *
   * @return json
   */
  public function data() {
    $cid = 'valet:' . $this->csrfToken->get('/api/valet');
    $data = array();
    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      $data = array();
      $tags = array(
        'valet',
        'config:valet.admin',
        'config:core.extension',
      );
      $config = \Drupal::config('valet.admin');
      $manager = \Drupal::service('plugin.manager.valet');
      foreach($config->get('plugins') as $id => $plugin){
        if(!empty($plugin['enabled'])){
          $instance = $manager->createInstance($id);
          $plugin_results = $instance->getResults();
          if(is_array($plugin_results)){
            $data += $plugin_results;
          }
          $tags = Cache::mergeTags($tags, $instance->getCacheTags());
        }
      }

      // Iconify integration
      if (\Drupal::moduleHandler()->moduleExists('iconify')) {
        foreach($data as &$item) {
          if (empty($item['icon'])) {
            if ($iconify = iconify('valet:' . $item['command'])->getIcon()) {
              $item['icon'] = $iconify;
            }
          }
        }
      }

      // Append prefix and titles to commands as needed.
      foreach($data as &$item) {
        if (!empty($item['command'])) {
          $item['command'] = ':' . $item['command'] . ' ' . $item['label'];
        }
      }

      // Invoke alter hook.
      $this->moduleHandler->alter('valet_results', $data);

      // Clean up array keys
      $data = array_values($data);

      // Cache for 1 day.
      $cache_time = time() + (60*60*24);
      \Drupal::cache()->set($cid, $data, $cache_time, $tags);
      // Cache time of rebuild.
      \Drupal::cache()->set($cid . ':timestamp', time(), $cache_time, $tags);
    }
    return new JsonResponse($data);
  }
}
