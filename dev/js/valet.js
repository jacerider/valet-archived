/**
 * @file toolbar.js
 *
 * Defines the behavior of the Drupal administration toolbar.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  // Remap the filter functions for autocomplete to recognise the
  // extra value "command". Borrowed from the wonderful coffee project.
  var proto = $.ui.autocomplete.prototype;
  var initSource = proto._initSource;

  function filter(array, term) {
    var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), 'i');
    return $.grep(array, function (value) {
      return matcher.test(value.command) || matcher.test(value.label) || matcher.test(value.value);
    });
  }

  $.extend(proto, {
    _initSource: function () {
      if ($.isArray(this.options.source)) {
        this.source = function (request, response) {
          response(filter(this.options.source, request.term));
        };
      }
      else {
        initSource.call(this);
      }
    }
  });

  /**
   * Set up and bind Valet.
   */
  Drupal.behaviors.valet = {

    attach: function (context) {
      // Process the administrative toolbar.
      $('body').once('valet').each(function () {
        var model = new Drupal.valet.models.ValetModel();
        new Drupal.valet.views.ValetView({
          el: $(context).find('#valet'),
          model: model
        });
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
      var self = this;
      this.$input = this.$el.find('.valet-input');
      this.$window = $(window);
      this.$body = $('body');
      this.$results = $('#valet-results');
      this.down = [];
      this.$el.find('.valet-close').click(this.toggle.bind(this));
      this.$el.find('.valet-open').click(function (e) {
        e.preventDefault();
        self.toggle();
      });
      this.timeout = null;
      _.bindAll(this, 'keyDown');
      _.bindAll(this, 'keyUp');
      $(document).bind('keydown', this.keyDown).bind('keyup', this.keyUp);
      $('.toolbar-icon-valet').click(function (e) {
        e.preventDefault();
        self.toggle();
      });
      // Autocomplete setup
      this.$input.autocomplete({
        appendTo: this.$results,
        minLength: 1,
        delay: 0,
        autoFocus: true,
        search: function (event, ui) {
          self.$results.addClass('searching');
        },
        response: function (event, ui) {
          clearTimeout(self.timeout);
          self.timeout = setTimeout(function () {
            self.$results.removeClass('searching');
          }, 10);
        },
        // source: data,
        focus: function (event, ui) {
          return false;
        },
        select: function (event, ui) {
          if (ui.item) {
            self.go(ui.item.label, ui.item.value);
            return false;
          }
        }
      });
      // Add some magical style to our results
      this.$input.autocomplete('instance')._renderItem = function (ul, item) {
        var value = item.value.length > 0 ? item.value : '/';
        value = item.value.length > 85 ? item.value.substring(0, 85) + '...' : value;
        var icon = item.icon ? '<i class="' + item.icon + '"></i>' : '';
        return $('<li></li>')
          .append('<a>' + icon + '<strong>' + item.label + '</strong><em>' + item.description + '</em><small>' + value + '</small></a>')
          .appendTo(ul);
      };
      // Limit the max results
      this.$input.autocomplete('instance')._renderMenu = function (ul, items) {
        var self = this;
        items = items.slice(0, 6);
        $.each(items, function (index, item) {
          self._renderItemData(ul, item);
        });
      };
    },

    toggle: function () {
      var self = this;
      if (this.model.get('isOpen')) {
        this.$el.removeClass('open');
        this.$body.removeClass('valet-open');
        this.model.set('isOpen', false);
        // trick to hide input text once the search overlay closes
        // todo: hardcoded times, should be done after transition ends
        if (this.$input[0].value !== '') {
          setTimeout(function () {
            self.$el.addClass('hideInput');
            setTimeout(function () {
              self.$el.removeClass('hideInput');
              self.$input[0].value = '';
            }, 300);
          }, 500);
        }
        this.$input[0].blur();
        this.$window.off('click.valet-link');
      }
      else {
        this.$input.val('').attr('disabled', false);
        this.getData(this.autoComplete.bind(this));
        this.$el.addClass('open');
        this.$body.addClass('valet-open');
        this.model.set('isOpen', true);
        this.$input.focus();
        // delay binding of window click.
        setTimeout(function () {
          self.$window.on('click.valet-link', function (e) {
            if (!$(e.target).closest('.valet-inner').length) {
              self.toggle();
            }
          });
        }, 300);
      }
    },

    autoComplete: function (data) {
      this.$input.autocomplete('option', {source: data});
    },

    go: function (label, value) {
      value = value.replace('RETURN_URL', window.location.pathname.substring(1));

      if (this.down[16]) {
        this.down[16] = false;
        this.toggle();
        window.open(value);
      }
      else {
        this.$input.val('Loading...').attr('disabled', true);
        window.location = value;
      }
    },

    getData: function (cb) {
      var self = this;
      var data = localStorage ? JSON.parse(localStorage.getItem('valet')) : null;
      if (data && drupalSettings.valet.cache && data.timestamp >= drupalSettings.valet.cache) {
        return cb(data.data);
      }
      else {
        self.$input.val('Loading data...').attr('disabled', true);
        $.ajax({
          url: drupalSettings.path.baseUrl + 'api/valet',
          dataType: 'json',
          success: function (data) {
            self.$input.val('').attr('disabled', false).focus();
            if (localStorage) {
              var time = Math.floor(new Date().getTime() / 1000);
              localStorage.setItem('valet', JSON.stringify({timestamp: time, data: data}));
            }
            return cb(data);
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.status);
            alert(thrownError);
            return cb(null);
          }
        });
      }
    },

    keyDown: function (e) {
      if (this.model.get('isOpen') && e.keyCode === 27) {
        this.toggle();
        return;
      }
      this.down[e.keyCode] = true;

      if (this.down[drupalSettings.valet.hotkey] && this.down[drupalSettings.valet.modifier] && !$(e.target).is(':focus')) {
        e.preventDefault();
        this.toggle();
      }
    },

    keyUp: function (e) {
      this.down[e.keyCode] = false;
    }

  });

}(jQuery, Drupal, drupalSettings));
