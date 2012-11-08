<?php

/**
 * @file
 * Abstraction of the selection logic of a valet link group.
 *
 * Implementations that wish to provide an implementation of this should
 * register it using CTools' plugin system.
 */
interface Valet_LinksHandler {

  /**
   * Constructor for the links.
   *
   * @param $type
   *   The name of the links plugin.
   */
  public function __construct($type, $args = NULL);

  /**
   * Load options for given type
   */
  public function options_load();

  /**
   * Load options for given type
   */
  public function links_load($settings);

}

/**
 * An abstract implementation of EntityReference_LinksHandler.
 */
abstract class Valet_LinksHandler_Abstract implements Valet_LinksHandler {

  /**
   * The name of the links plugin.
   */
  protected $type;

  /**
   * The plugin definition.
   */
  protected $plugin;

  public function __construct($type, $args = NULL) {
    $this->type = $type;
    ctools_include('plugins');
    $plugin = ctools_get_plugins('valet', 'links', $type);
    $this->plugin = $plugin;
  }

  public function options_load(){
  	$loaded = $this->options();
  	$options = array();
  	foreach($loaded as $option){
  		$type = isset($option['type']) ? $option['type'] : $this->type;
  		$options[$this->type.':'.$option['id']] = strtoupper($type).': '.$option['label'];
  	}
  	return $options;
  }

  protected function options(){
  	return array();
  }

  public function links_load($settings){
    $links = $this->links($settings);
    $this->links_local_tasks($links);
    $this->links_alter($links);
  	return $links;
  }

  protected function links($settings){
  	return array();
  }

  protected function links_alter(&$links){
  }

  /**
   * Fetch local tasks for each link and set them up as children
   */
  protected function links_local_tasks(&$links){
    $query = $_GET['q'];
    foreach($links as &$link){
      $path = $link['value'];
      $_GET['q'] = $path;
      // Flush local task cache so we get the tasks for this path
      drupal_static('menu_local_tasks', NULL, TRUE);
      $tasks = menu_local_tasks();
      if(!empty($tasks['tabs']['count'])){
        foreach($tasks['tabs']['output'] as $l){
          $tab = $l['#link'];
          if($tab['href'] == $path) continue;
          $link['children'][] = array(
            'label' => $tab['title'].' <em>'.$link['label'].'</em>',
            'value' => ($alias = drupal_lookup_path('alias', $tab['href'])) ? $alias : $tab['href'],
          );
        }
      }
      // Convert path to alias
      if($alias = drupal_lookup_path('alias', $link['value'])) $link['value'] = $alias;
    }
    $_GET['q'] = $query;
  }

}

/**
 * A null implementation of Valet_LinksHandler.
 */
class Valet_LinksHandler_Broken extends Valet_LinksHandler_Abstract {

}