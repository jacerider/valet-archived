(function($) {

Drupal.behaviors.valet = {

  dataLabel: parseFloat($.ui.version) == 1.8 ? 'autocomplete' : 'ui-Autocomplete',

  dataItemLabel: parseFloat($.ui.version) == 1.8 ? 'item.autocomplete' : 'ui-autocomplete-item',

  once: false,

  open: false,

  links: false,

  results: false,

  weight: false,

  selected: false,

  requestTerm: '',

  valetSelector: '',

  valetSearchSelector: '',

  shifted: false,

  attach: function(context, settings) {

    var me = this;

    if(me.once) return;
    me.once = true;

    me.valetSelector = $('#valet');
    if(!me.valetSelector.length) return;
    me.valetSearchSelector = $('#valet-search');

    // Hide valet immediately
    me.valetSelector.hide();

    // Get locally stored links object
    if(!Drupal.settings.valet.purge){
      Lawnchair({name:'valet'}, function(){
        this.get('links', function(links) {
          me.links = links != 'undefined' ? links : false;
        });
        this.get('weight', function(weight) {
          me.weights = weight != 'undefined' ? weight : false;
        });
      })
    }

    // If we don't have a locall stored links object
    // generate the list via ajax
    if(!me.links){
      $.ajax({
        url: Drupal.settings.basePath+'admin/valet/lookup',
        dataType: 'json',
        success: function(data) {
          Lawnchair({name:'valet'}, function() {
            this.remove('links');
            this.save(data);
            me.links = data;
          })
        },
        error: function (xhr, ajaxOptions, thrownError) {
          alert(xhr.status);
          alert(thrownError);
        }
      });
    }

    // Jquery UI autocomplete field awesomeness
    me.valetSearchSelector.autocomplete({
      minLength: 2,
      delay: 0,
      autoFocus: true,
      appendTo: '#valet-results',
      source: function(request, response) {
        me.resultsFilter(request.term);
        response(me.results);
      },
      focus: function( event, ui ) {
        me.selected = ui.item;
        me.childrenCheck();
        return false;
      },
      select: function( event, ui ) {
        if(ui.item){
          me.go(ui.item.label, ui.item.value);
          return false;
        }
      },
      open: function(event, ui){
        var autocomplete = $( this ).data( Drupal.behaviors.valet.dataLabel );
        menu = autocomplete.menu;
        if(menu.activate){
          menu.activate( $.Event({
              type: "mouseenter"
          }), menu.element.children().first());
        }
        // Use shift to open new tab
        if(!this.shiftSet){
          this.shiftSet = true;
          $(this).bind('keydown', 'shift', function() {
            me.shifted = true;
          }).bind('keyup', 'shift', function(){
            me.shifted = false;
          });
        }
      }
    })
    // Add some magical style to our results
    .data( Drupal.behaviors.valet.dataLabel )._renderItem = function( ui, item ) {
      var icon = me.shortcut(item.value);
      var children = me.children(item);
      var value = item.value.length > 85  ? item.value.substring(0,85)+'...' : (item.value.length > 0 ? item.value : '/')
      return $( "<li></li>" )
        .data( Drupal.behaviors.valet.dataItemLabel, item )
        .append( "<a><strong>" + item.label + "</strong><br><em>" + value + "</em>" + children + icon + "</a>" )
        .appendTo( ui );
    };

    // Bind hotkeys.
    $(document).bind('keydown', Drupal.settings.valet.modifier+'+'+Drupal.settings.valet.key, function() {
      me.show();
      return false;
    });
    $(document).bind('keydown', 'esc', function() {
      me.hide();
    });
    me.valetSearchSelector.bind('keydown', 'esc', function() {
      me.hide();
    });
    // Hotkey for children
    me.valetSearchSelector.bind('keydown', Drupal.settings.valet.modifier, function() {
      me.childrenCreate();
      return false;
    });

    // Click anywhere else to close.
    $(document).click(function() {
      me.hide();
    });

    // Disable form submit
    $('#valet-search-form').submit(function() {
      return false;
    });
  },

  // Sort by weighted fields
  resultsFilter: function(requestTerm){
    var me = this;

    me.requestTerm = requestTerm;
    if(!me.links){
      console.log('Valet Links Not Found!');
      return;
    }
    var results = $.ui.autocomplete.filter(me.links.items, me.requestTerm);
    me.results = results.slice(0, 4);
    if(me.weights && me.weights[me.requestTerm]){
      var weight = me.weights[me.requestTerm];
      for (var i=0;i<me.results.length;i++){
        if(weight[me.results[i].value]){
          me.results[i].weight = weight[me.results[i].value];
        }else{
          me.results[i].weight = 0;
        }
      }
    }
    me.results.sort(me.weightSort);
  },

  // Select action
  go: function(label, url){
    var me = this;

    // Save for weight
    me.goSave(url);

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
    me.valetSelector.addClass('loading');
    $('#valet-loading-info').text(label_short);
    $('#valet-loading-value span').text(url_short);

    if(me.shifted){
      me.hide();
      window.open(Drupal.settings.basePath+url);
    }else{
      window.location = Drupal.settings.basePath+url;
    }
  },

  // Save weight per search term and path
  goSave: function(url){
    var me = this;

    Lawnchair({name:'valet'}, function(){
      var me = this;
      //this.remove('weight');
      this.get('weight', function(weight) {
        if(!weight){
          weight = {key:'weight'};
        }
        if(!weight[me.requestTerm]){
          weight[me.requestTerm] = {};
        };
        weight[me.requestTerm][url] = weight[me.requestTerm][url] ? weight[me.requestTerm][url]+1 : 1;
        this.save(weight);
      });
    })
  },

  // Show valet
  show: function(){
    var me = this;
    me.open = true;
    me.valetSelector.fadeIn('normal');
    me.valetSearchSelector.focus().data( Drupal.behaviors.valet.dataLabel )._trigger("change");
  },

  // hide valet
  hide: function(){
    var me = this;
    me.open = false;
    me.valetSelector.fadeOut('fast');
    // Unbind shortcuts
    me.valetSearchSelector.unbind('keydown', '1');
    me.valetSearchSelector.unbind('keydown', '2');
    me.valetSearchSelector.unbind('keydown', '3');

    // Reset results
    $('#valet-results').show();
    // Reset display
    me.valetSelector.removeClass('loading');
    $('#valet-loading-info').text('');
    $('#valet-loading-value span').text('');
  },

  children: function(item){
    var me = this;
    if(item.children){
      return '<span class="valet-icon valet-icon-'+Drupal.settings.valet.modifier.toLowerCase()+'"></span>';
    }
    return '';
  },

  childrenCheck: function(item){
    var me = this;
    var item = me.selected;
    me.childrenRemove();
  },

  childrenCreate: function(){
    var me = this;
    var item = me.selected;
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
    var me = this;
    var item = me.selected;
    var children = $('.valet-child');
    if(!item.child && children.length){
      children.animate({height:0}, 300, function(){
        $(this).remove();
      });
    }
  },

  // Bind shortcuts for each result
  shortcut: function(url){
    var me = this;
    var key = '';
    switch(url){
      case me.results[0]['value']:
        key = 'return';
        break;
      case me.results[1]['value']:
        key = '1';
        break;
      case me.results[2]['value']:
        key = '2';
        break;
      case me.results[3]['value']:
        key = '3';
        break;
    }
    var key_int = parseInt(key);
    if(key_int){
      me.valetSearchSelector.unbind('keydown', key);
      me.valetSearchSelector.bind('keydown', key, function() {
        me.go(me.results[key_int]['label'], me.results[key_int]['value']);
      });
    }
    return key ? '<span class="valet-icon valet-icon-'+key+'"><span>' : '';
  },

  weightSort: function compare(a,b) {
    var me = this;
    if (a.weight > b.weight)
       return -1;
    if (a.weight < b.weight)
      return 1;
    return 0;
  }

}

})(jQuery);
