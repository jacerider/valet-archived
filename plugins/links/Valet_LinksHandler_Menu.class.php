<?php

/**
 * A generic Entity handler.
 *
 * The generic base implementation has a variety of overrides to workaround
 * core's largely deficient entity handling.
 */
class Valet_LinksHandler_Menu extends Valet_LinksHandler_Abstract {

	/**
	 * Define available options
	 * @return array
	 *   An array of options
	 */
	protected function options(){	
	  $options = array();
	  if(module_exists('menu')){
	    $result = db_query("SELECT * FROM {menu_custom} ORDER BY title", array(), array('fetch' => PDO::FETCH_ASSOC));
	    foreach ($result as $menu) {
	      $options[] = array(
	      	'id' => $menu['menu_name'],
	      	'label' => $menu['title'],
	      );
	    }
	  }
	  return $options;
	}

	/**
	 * The links to make available
	 * @param  array $settings 
	 *   An array containing a list of active options
	 * @return array           
	 *   An array of links
	 */
  protected function links($settings){
  	$links = array();
    $menus = array_keys($settings);
    $result = db_select('menu_links', 'm')
      ->fields('m', array('link_title', 'link_path'))
      ->condition('menu_name', $menus,'IN')
      ->condition('link_title', '','<>')
      ->condition('link_path', '%\%%','NOT LIKE')
      ->execute()
      ->fetchAll();

    foreach($result as $link){
      $links[$link->link_path] = array(
        'label' => $link->link_title,
        'value' => $link->link_path,
      );
    }
  	return $links;
  }

  protected function links_alter(&$links){
  	foreach($links as $url => &$link){
	  	$method = 'links_alter_'.str_replace('/', '_', $url);
	  	if((int)method_exists('Valet_LinksHandler_Menu', $method)){
	  		$this->{$method}($link);
	  	}
  	}
  }

  /**
   * Grab content type links
   * 
   * NOTE: Permissions have already been checked
   */
  protected function links_alter_admin_structure_types(&$link){
  	$types = node_type_get_types();
  	foreach($types as $type){  		
      $link['children'][] = array(
        'label' => $type->name.' <em>'.$link['label'].'</em>',
        'value' => $link['value'].'/manage/'.$type->type,
      );
      if(module_exists('field_ui')){
	      $link['children'][] = array(
	        'label' => $type->name.' Fields <em>'.$link['label'].'</em>',
	        'value' => $link['value'].'/manage/'.$type->type.'/fields',
	      );
    	}
  	}
  }
}
