/**
 * @file toolbar.js
 *
 * Defines the behavior of the Drupal administration toolbar.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Registers tabs with the toolbar.
   *
   * The Drupal toolbar allows modules to register top-level tabs. These may
   * point directly to a resource or toggle the visibility of a tray.
   *
   * Modules register tabs with hook_toolbar().
   */
  Drupal.behaviors.toolbar = {

    attach: function (context) {
      // Verify that the user agent understands media queries. Complex admin
      // toolbar layouts require media query support.
      if (!window.matchMedia('only screen').matches) {
        return;
      }
      // Process the administrative toolbar.
      $('body').once('valet').each(function () {
        var model = new Drupal.valet.models.ValetModel();
        new Drupal.valet.views.ValetView({
          el: $(context).find('#valet'),
          model: model
        });
        // console.log(this);
        // var self = this;
        // setTimeout(function(){
        //   $(self).addClass('open');
        // }, 500);
      });
    }
  };

  Drupal.valet = Drupal.valet || {models: {}, views: {}};

  /**
   * Backbone Model for valet.
   */
  Drupal.valet.models.ValetModel = Backbone.Model.extend({
    defaults: {
      // Indicates whether the valet is currently running.
      isOpen: false
    }
  });

  /**
   * Handles valet interactions.
   */
  Drupal.valet.views.ValetView = Backbone.View.extend({

    /**
     * Implements Backbone Views' initialize().
     */
    initialize: function () {
      this.$input = this.$el.find('.valet-input');
      this.down = [];
      this.$el.find('.valet-close').click(this.toggle.bind(this));
      _.bindAll(this, "keyDown");
      _.bindAll(this, "keyUp");
      $(document).bind('keydown', this.keyDown).bind('keyup', this.keyUp);
    },

    toggle: function () {
      var self = this;
      if (this.model.get('isOpen')) {
        $(this.$el.removeClass('open'));
        this.model.set({isOpen: false});
        // trick to hide input text once the search overlay closes
        // todo: hardcoded times, should be done after transition ends
        if( this.$input[0].value !== '' ) {
          setTimeout(function() {
            self.$el.addClass('hideInput');
            setTimeout(function() {
              self.$el.removeClass('hideInput');
              self.$input[0].value = '';
            }, 300 );
          }, 500);
        }
        this.$input[0].blur();
      }
      else{
        this.$input[0].value = '';
        this.getData(this.autoComplete.bind(this));
        $(this.$el.addClass('open'));
        this.model.set({isOpen: true});
        this.$input[0].focus();
      }
    },

    autoComplete: function ( data ) {
      var self = this;
      this.$input.once('valet').autocomplete({
        appendTo: "#valet-results",
        minLength: 1,
        delay: 0,
        autoFocus: true,
        source: data,
        focus: function( event, ui ) {
          return false;
        },
        select: function( event, ui ) {
          if(ui.item){
            self.go(ui.item.label, ui.item.value);
            return false;
          }
        },
      })
      // Add some magical style to our results
      .autocomplete( "instance" )._renderItem = function( ui, item ) {
        var value = item.value.length > 85  ? item.value.substring(0,85)+'...' : (item.value.length > 0 ? item.value : '/')
        return $( "<li></li>" )
          .append( "<a><strong>" + item.label + "</strong> <small>" + value + "</small><br><em>" + item.description + "</em></a>" )
          .appendTo( ui );
      };
    },

    go: function (label, value) {
      if(value == '/devel/cache/clear') value = value + '?destination='+(window.location.pathname.substring(1));

      if (this.down[16]) {
        this.down[16] = false;
        this.toggle();
        window.open(value);
      }
      else{
        this.$input.val( 'Loading...' ).attr("disabled", "true");
        window.location = value;
      }
    },

    getData: function(cb) {
      var data;
      // if(data = localStorage.getItem('valet') && 1 === 2){
      //   return cb(JSON.parse(data));
      // }
      // else{
        $.ajax({
          url: drupalSettings.path.baseUrl+'api/valet',
          dataType: 'json',
          success: function(data) {
            // localStorage.setItem('valet', JSON.stringify(data));
            return cb(data);
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.status);
            alert(thrownError);
            return cb(null);
          }
        });
      // }
    },

    keyDown: function(e) {
      if(this.model.get('isOpen') && e.keyCode === 27){
        this.toggle();
        return;
      }
      this.down[e.keyCode] = true;
    },

    keyUp: function(e) {
      if (this.down[32] && this.down[18]) {
        this.toggle();
      }
      this.down[e.keyCode] = false;
    }

  });

}(jQuery, Drupal, drupalSettings));
