<?php

/**
 * @file
 * Abstraction of the selection logic of a valet link group.
 *
 * Implementations that wish to provide an implementation of this should
 * register it using CTools' plugin system.
 */
interface Valet_EntityHandler {

  /**
   * Constructor for the links.
   *
   * @param $type
   *   The name of the links plugin.
   */
  public function __construct($type, $args = NULL);

  public function links_load();

}

/**
 * An abstract implementation of EntityReference_LinksHandler.
 */
abstract class Valet_EntityHandler_Abstract implements Valet_EntityHandler {

  /**
   * The name of the links plugin.
   */
  protected $type;

  /**
   * The plugin definition.
   */
  protected $plugin;

  /**
   * Entity info.
   */
  protected $entity;

  /**
   * Enabled entity types.
   */
  protected $bundles;

  /**
   * Constructor for the links.
   *
   * @param $type
   *   The name of the links plugin.
   */
  public function __construct($type, $args = NULL){
    $this->type = $type;
    ctools_include('plugins');
    $plugin = ctools_get_plugins('valet', 'links', $type);
    $this->plugin = $plugin;
    $this->entity = $args[0];
    $this->bundles = $args[1];
  }

  public function links_load(){
    return $this->db_lookup();
  }

  protected function links_alter(&$links){
  }

  protected function db_lookup(){
    $links = array();
    $fields = $this->db_keys();
    if(empty($fields)) return $links;
    $query = db_select($this->entity['base table'], 'q');
    $query->fields('q', $fields);
    $this->db_filter($query);
    $results = $query->execute()->fetchAll();
    foreach($results as $result){
      $link = $this->db_result($result);
      if(!empty($link)){
        $links += $link;
      }
    }
    $this->links_alter($links);
    return $links;
  }

  protected function db_keys(){
    return array();
  }

  protected function db_result($result){
    return array();
  }

  protected function db_filter(&$query){
  }

}

/**
 * A null implementation of Valet_LinksHandler.
 */
class Valet_EntityHandler_Broken extends Valet_EntityHandler_Abstract {

}