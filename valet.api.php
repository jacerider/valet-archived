<?php

/**
 * @file
 * Hooks provided by Valet module.
 */

/**
 * Alter the Valet results.
 *
 * Here's an example of how to alter Valet results.
 */
function hook_valet_results_alter(&$items) {
  // Change label from Front Page to Home Page.
  $items['front']['label'] = 'Home Page';
}
