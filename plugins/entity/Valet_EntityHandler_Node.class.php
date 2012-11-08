<?php

class Valet_EntityHandler_Node extends Valet_EntityHandler_Abstract {
  
	protected function db_keys(){
		return array('nid', 'title');
	}

  protected function db_result($result){
  	$uri = $this->entity['uri callback'];
    $path = $uri($result);
  	$link = array(
      'label' => $result->title,
      'value' => $path['path'],
  	);
    return array($path['path'] => $link);
  }

  protected function db_filter(&$query){
    $bundles = $this->bundles;
    $query->condition('q.type', $bundles, 'IN');
  }

}