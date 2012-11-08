<?php

class Valet_EntityHandler_User extends Valet_EntityHandler_Abstract {

	protected function db_keys(){
		return array('uid', 'name');
	}

  protected function db_result($result){
  	$uri = $this->entity['uri callback'];
    $path = $uri($result);
  	$link = array(
      'label' => $result->name,
      'value' => $path['path'],
  	);
    return array($path['path'] => $link);
  }

  protected function db_filter(&$query){
  }

}