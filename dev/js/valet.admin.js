/**
 * @file toolbar.js
 *
 * Defines the behavior of the Drupal administration toolbar.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Valet admin page bindings.
   */
  Drupal.behaviors.valetAdmin = {

    attach: function (context) {
      $('#edit-hotkey').bind('keyup', function (e) {
        $(this).val(e.keyCode);
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
