(function($) {

Drupal.behaviors.valet = {
  
  links: false,

  results: false,

  attach: function(context, settings) {

    // Hide valet immediately
    $('#valet').hide();

    // Get locally stored links object
    if(!Drupal.settings.valet.purge){
      Lawnchair({name:'valet'}, function(){
        this.get('links', function(links) {
          Drupal.behaviors.valet.links = links != 'undefined' ? links : false;
        })
      })
    }

    // If we don't have a locall stored links object
    // generate the list via ajax
    if(!Drupal.behaviors.valet.links){
      $.ajax({
        url: Drupal.settings.basePath+'valet/lookup',
        dataType: 'json',
        success: function(data) {
          Lawnchair({name:'valet'}, function() {
            this.save(data);
            Drupal.behaviors.valet.links = data;
          })
        }
      });
    }
    
    // Jquery UI autocomplete field awesomeness
    $('#valet-search').autocomplete({
      minLength: 2,
      delay: 0,
      autoFocus: true,
      appendTo: '#valet-results',
      source: function(request, response) {
        // We only want 4 results max
        var results = $.ui.autocomplete.filter(Drupal.behaviors.valet.links.items, request.term);
        Drupal.behaviors.valet.results = results.slice(0, 4);
        response(Drupal.behaviors.valet.results);
      },
      focus: function( event, ui ) {
        return false;
      },
      select: function( event, ui ) {
        if(ui.item){
          Drupal.behaviors.valet.go(ui.item.label, ui.item.value);
          return false;
        }
      }
    })
    // Add some magical style to our results
    .data( "autocomplete" )._renderItem = function( ul, item ) {
      var icon = Drupal.behaviors.valet.shortcut(item.value);
      return $( "<li></li>" )
        .data( "item.autocomplete", item )
        .append( "<a><strong>" + item.label + "</strong><br><em>" + item.value + "</em>" + icon + "</a>" )
        .appendTo( ul );
    };

    // Bind hotkeys.
    $(document).bind('keydown', Drupal.settings.valet.hotkey, function() {
      Drupal.behaviors.valet.show();
      return false;
    });
    $(document).bind('keydown', 'esc', function() {
      Drupal.behaviors.valet.hide();
    });
    $('#valet-search').bind('keydown', 'esc', function() {
      Drupal.behaviors.valet.hide();
    });

    // Click anywhere else to close.
    $(document).click(function() {
      Drupal.behaviors.valet.hide();
    });

    // Disable form submit
    $('#valet-search-form').submit(function() {
      return false;
    });

    //Drupal.behaviors.valet.lookup('con');
  },

  // Select action
  go: function(label, url){
    // Support for cache clear redirect
    if(url == 'devel/cache/clear') url = 'devel/cache/clear?destination='+window.location.pathname;

    $('#valet').addClass('loading');
    $('#valet-loading-info').text(label);
    $('#valet-loading-value span').text(url);
    window.location = Drupal.settings.basePath+url;
  },

  // Show valet
  show: function(){
    $('#valet').fadeIn('fast', function() {
      $('#valet-search').focus();
    });
  },

  // hide valet
  hide: function(){
    $('#valet').fadeOut('fast', function() {
      $('#valet-search').val('').blur();
    });
    // Unbind shortcuts
    $('#valet-search').unbind('keydown', '1');
    $('#valet-search').unbind('keydown', '2');
    $('#valet-search').unbind('keydown', '3');
  },

  // Bind shortcuts for each result
  shortcut: function(url){
    var key = '';
    switch(url){
      case Drupal.behaviors.valet.results[0]['value']:
        key = 'return';
        break;
      case Drupal.behaviors.valet.results[1]['value']:
        key = '1';
        break;
      case Drupal.behaviors.valet.results[2]['value']:
        key = '2';
        break;
      case Drupal.behaviors.valet.results[3]['value']:
        key = '3';
        break;
    }
    var key_int = parseInt(key);
    if(key_int){
      $('#valet-search').unbind('keydown', key);
      $('#valet-search').bind('keydown', key, function() {
        Drupal.behaviors.valet.go(Drupal.behaviors.valet.results[key_int]['label'], Drupal.behaviors.valet.results[key_int]['value']);
      });
    }
    return key ? '<span class="valet-icon valet-icon-'+key+'"><span>' : '';
  }

}

})(jQuery);