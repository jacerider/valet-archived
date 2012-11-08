<?php

/**
 * A generic Entity handler.
 *
 * The generic base implementation has a variety of overrides to workaround
 * core's largely deficient entity handling.
 */
class Valet_LinksHandler_Entity extends Valet_LinksHandler_Abstract {

	protected function options(){	
	  $options = array();
	  $entities = entity_get_info();

    ctools_include('plugins');
    $plugins = ctools_get_plugins('valet', 'entity');
	  foreach($entities as $entity_type => $entity){
      if(isset($plugins[$entity_type])){
		    foreach($entity['bundles'] as $bundle_type => $bundle){
		      $options[] = array(
		      	'id' => $entity_type.':'.$bundle_type,
		      	'label' => $bundle['label'],
		      	'type' => $entity['label'],
		      );
		    }
	  	}
	  }
	  return $options;
	}

  protected function links($settings){
  	$links = array();
  	$entities = entity_get_info();

    ctools_include('plugins');
    $plugins = ctools_get_plugins('valet', 'entity');
    foreach($settings as $entity_type => $bundles){
      if(isset($plugins[$entity_type])){
        $handler = _valet_get_handler('entity', $entity_type, $entities[$entity_type], $bundles);
        $links += $handler->links_load();
    	}
    }
  	return $links;
  }

}
