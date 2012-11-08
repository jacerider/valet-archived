(function($) {

Drupal.behaviors.valet = {

  once: false,

  open: false,
  
  links: false,

  results: false,

  weight: false,

  selected: false,

  requestTerm: '',

  valetSelector: '',

  valetSearchSelector: '',

  attach: function(context, settings) {

    if(Drupal.behaviors.valet.once) return;
    Drupal.behaviors.valet.once = true;

    Drupal.behaviors.valet.valetSelector = $('#valet');
    Drupal.behaviors.valet.valetSearchSelector = $('#valet-search');

    // Hide valet immediately
    Drupal.behaviors.valet.valetSelector.hide();

    // Get locally stored links object
    if(!Drupal.settings.valet.purge){
      Lawnchair({name:'valet'}, function(){
        this.get('links', function(links) {
          Drupal.behaviors.valet.links = links != 'undefined' ? links : false;
        });
        this.get('weight', function(weight) {
          Drupal.behaviors.valet.weights = weight != 'undefined' ? weight : false;
        });
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
            this.remove('links');
            this.save(data);
            Drupal.behaviors.valet.links = data;
          })
        }
      });
    }
    
    // Jquery UI autocomplete field awesomeness
    Drupal.behaviors.valet.valetSearchSelector.autocomplete({
      minLength: 2,
      delay: 0,
      autoFocus: true,
      appendTo: '#valet-results',
      source: function(request, response) {
        Drupal.behaviors.valet.resultsFilter(request.term);
        response(Drupal.behaviors.valet.results);
      },
      focus: function( event, ui ) {
        Drupal.behaviors.valet.selected = ui.item;
        Drupal.behaviors.valet.childrenCheck();
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
    .data( "autocomplete" )._renderItem = function( ui, item ) {
      var icon = Drupal.behaviors.valet.shortcut(item.value);
      var children = Drupal.behaviors.valet.children(item);
      var value = item.value.length > 85  ? item.value.substring(0,85)+'...' : (item.value.length > 0 ? item.value : '/')
      return $( "<li></li>" )
        .data( "item.autocomplete", item )
        .append( "<a><strong>" + item.label + "</strong><br><em>" + value + "</em>" + children + icon + "</a>" )
        .appendTo( ui );
    };

    // Bind hotkeys.
    $(document).bind('keydown', Drupal.settings.valet.modifier+'+'+Drupal.settings.valet.key, function() {
      Drupal.behaviors.valet.show();
      return false;
    });
    $(document).bind('keydown', 'esc', function() {
      Drupal.behaviors.valet.hide();
    });
    Drupal.behaviors.valet.valetSearchSelector.bind('keydown', 'esc', function() {
      Drupal.behaviors.valet.hide();
    });
    // Hotkey for children
    Drupal.behaviors.valet.valetSearchSelector.bind('keydown', Drupal.settings.valet.modifier, function() {
      Drupal.behaviors.valet.childrenCreate();
      return false;
    });

    // Click anywhere else to close.
    $(document).click(function() {
      Drupal.behaviors.valet.hide();
    });

    // Disable form submit
    $('#valet-search-form').submit(function() {
      return false;
    });
  },

  // Sort by weighted fields
  resultsFilter: function(requestTerm){
    Drupal.behaviors.valet.requestTerm = requestTerm;
    var results = $.ui.autocomplete.filter(Drupal.behaviors.valet.links.items, Drupal.behaviors.valet.requestTerm);
    Drupal.behaviors.valet.results = results.slice(0, 4);
    if(Drupal.behaviors.valet.weights && Drupal.behaviors.valet.weights[Drupal.behaviors.valet.requestTerm]){
      var weight = Drupal.behaviors.valet.weights[Drupal.behaviors.valet.requestTerm];
      for (var i=0;i<Drupal.behaviors.valet.results.length;i++){
        if(weight[Drupal.behaviors.valet.results[i].value]){
          Drupal.behaviors.valet.results[i].weight = weight[Drupal.behaviors.valet.results[i].value];
        }else{
          Drupal.behaviors.valet.results[i].weight = 0;
        }
      }
    }
    Drupal.behaviors.valet.results.sort(Drupal.behaviors.valet.weightSort);
  },

  // Select action
  go: function(label, url){
    // Save for weight
    Drupal.behaviors.valet.goSave(url);

    // Support for cache clear redirect
    if(url == 'devel/cache/clear') url = 'devel/cache/clear?destination='+(window.location.pathname.substring(1));
    if(url == 'devel/ambit/clear') url = 'devel/ambit/clear?destination='+(window.location.pathname.substring(1));
    if(url.indexOf("valet/cache/clear") != -1) url += '?destination='+(window.location.pathname.substring(1));
    var label_short = label.replace(/<\/?[a-z][a-z0-9]*[^<>]*>/ig, "");
    label_short = label_short.length > 28 ? label_short.substring(0,28)+'...' : label_short;
    var url_short = url.length > 40  ? url.substring(0,40)+'...' : (url.length > 0 ? url : '/');
    // Hide results
    $('#valet-results').hide();
    // Update display
    Drupal.behaviors.valet.valetSelector.addClass('loading');
    $('#valet-loading-info').text(label_short);
    $('#valet-loading-value span').text(url_short);
    window.location = Drupal.settings.basePath+url;
  },

  // Save weight per search term and path
  goSave: function(url){
    Lawnchair({name:'valet'}, function(){
      var me = this;
      //this.remove('weight');
      this.get('weight', function(weight) {
        if(!weight){
          weight = {key:'weight'};
        }
        if(!weight[Drupal.behaviors.valet.requestTerm]){
          weight[Drupal.behaviors.valet.requestTerm] = {};
        };
        weight[Drupal.behaviors.valet.requestTerm][url] = weight[Drupal.behaviors.valet.requestTerm][url] ? weight[Drupal.behaviors.valet.requestTerm][url]+1 : 1;
        this.save(weight);
      });
    })
  },

  // Show valet
  show: function(){
    Drupal.behaviors.valet.open = true;
    Drupal.behaviors.valet.valetSelector.fadeIn('normal');
    Drupal.behaviors.valet.valetSearchSelector.focus();
  },

  // hide valet
  hide: function(){
    Drupal.behaviors.valet.open = false;
    Drupal.behaviors.valet.valetSelector.fadeOut('fast');
    // Unbind shortcuts
    Drupal.behaviors.valet.valetSearchSelector.unbind('keydown', '1');
    Drupal.behaviors.valet.valetSearchSelector.unbind('keydown', '2');
    Drupal.behaviors.valet.valetSearchSelector.unbind('keydown', '3');
  },

  children: function(item){
    if(item.children){
      return '<span class="valet-icon valet-icon-'+Drupal.settings.valet.modifier.toLowerCase()+'"></span>';
    }
    return '';
  },

  childrenCheck: function(item){
    var item = Drupal.behaviors.valet.selected;
    Drupal.behaviors.valet.childrenRemove();
  },

  childrenCreate: function(){
    var item = Drupal.behaviors.valet.selected;
    if(item.children && !item.child){
      if($('.valet-child').length) return;
      var placeAfter = $('#ui-active-menuitem').parent();
      for (var i=0;i<item.children.length;i++){
        // Copy the child item
        var childItem = item.children[i];
        childItem.child = true;
        childItem.weight = i;
        var newItemClass = '';
        if(i == 0) newItemClass += ' first';
        if(i == item.children.length - 1) newItemClass += ' last';
        var newItem = $( '<li class="ui-menu-item valet-child'+newItemClass+'" role="menuitem"></li>' )
          .data( "item.autocomplete", childItem )
          .append( "<a>"+childItem.label+"</a>" );
        placeAfter.after(newItem);
        placeAfter = newItem;
        var height = newItem.height();
        newItem.css({height:0}).animate({height:height}, 300);
      }
    }
  },

  childrenRemove: function(){
    var item = Drupal.behaviors.valet.selected;
    var children = $('.valet-child');
    if(!item.child && children.length){
      children.animate({height:0}, 300, function(){
        $(this).remove();
      });
    }
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
      Drupal.behaviors.valet.valetSearchSelector.unbind('keydown', key);
      Drupal.behaviors.valet.valetSearchSelector.bind('keydown', key, function() {
        Drupal.behaviors.valet.go(Drupal.behaviors.valet.results[key_int]['label'], Drupal.behaviors.valet.results[key_int]['value']);
      });
    }
    return key ? '<span class="valet-icon valet-icon-'+key+'"><span>' : '';
  },

  weightSort: function compare(a,b) {
    if (a.weight > b.weight)
       return -1;
    if (a.weight < b.weight)
      return 1;
    return 0;
  }

}

})(jQuery);