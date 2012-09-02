<?php

function valet_admin_form($form, &$form_state){

	if(module_exists('menu')){
		$options = array();
		$result = db_query("SELECT * FROM {menu_custom} ORDER BY title", array(), array('fetch' => PDO::FETCH_ASSOC));
	  foreach ($result as $menu) {
	  	$options[$menu['menu_name']] = $menu['title'];
	  }
		$form['valet_index_menus'] = array(
			'#type' => 'checkboxes',
			'#title' => t('Menu Index'),
			'#description' => t('The menu items you would like to make available to Valet.'),
			'#options' => $options,
			'#default_value' => variable_get('valet_index_menus', array()),
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