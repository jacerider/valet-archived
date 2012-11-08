<?php

/**
 * Menu callback; clears all caches, then redirects to the previous page.
 */
function valet_cache_clear($type = 'all') {
  $name = ucwords(str_replace('-', ' ', $type));
	switch($type){
		case 'theme-registry':
			drupal_theme_rebuild();
			break;
		case 'css-js':
		  _drupal_flush_css_js();
		  drupal_clear_css_cache();
		  drupal_clear_js_cache();
		  break;
		case 'menu':
			menu_rebuild();
		case 'block':
  		cache_clear_all(NULL, 'cache_block');
  		break;
		case 'module-list':
			system_rebuild_module_data();
			break;
		case 'theme-list':
			system_rebuild_theme_data();
			break;
		case 'registry':
			registry_update();
			break;
		default:
  		drupal_flush_all_caches();
			break;
	}
  drupal_set_message(t('Cleared cache: %type', array('%type'=>$name)));
  drupal_goto();
}