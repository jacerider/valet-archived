<?php

function valet_admin_form($form, &$form_state){
	// Load all plugins type "operation".
  ctools_include('plugins');
  $operations = ctools_get_plugins('valet', 'links');
  $options = array();
  foreach($operations as $name => $plugin){
  	$handler = _valet_get_handler('links', $name);
  	$options += $handler->options_load();
  }
  
	if(!empty($options)){
		$form['valet_index_types'] = array(
			'#type' => 'checkboxes',
			'#title' => t('Menu Index'),
			'#description' => t('The items you would like to make available to Valet.'),
			'#options' => $options,
			'#default_value' => variable_get('valet_index_types', array()),
		);
	}

	$form['valet_compression'] = array(
		'#type' => 'radios',
		'#title' => t('Compression Level'),
		'#options' => array('Production (minified)', 'Development (uncompressed)'),
		'#default_value' => variable_get('valet_compression', 0),
		'#required' => TRUE,
	);

	$form['valet_key_modifier'] = array(
		'#type' => 'select',
		'#title' => t('Hotkey Modifier'),
		'#options' => drupal_map_assoc(array('Alt', 'Ctrl', 'Shift')),
		'#default_value' => variable_get('valet_key_modifier', 'Alt'),
		'#required' => TRUE,
	);

	$form['valet_key'] = array(
		'#type' => 'textfield',
		'#title' => t('Hotkey'),
		'#default_value' => variable_get('valet_key', 'space'),
		'#required' => TRUE,
		'#description' => t('Take a look at !url for more information.', array('!url' => l('jquery.hotkeys', 'https://github.com/jeresig/jquery.hotkeys'))),
	);

	$form['#submit'][] = 'valet_admin_form_submit';

  return system_settings_form($form);
}

function valet_admin_form_submit($form, &$form_state){
	// Flush valet cache so index is rebuilt
	cache_clear_all('valet_lookup', 'cache');
}