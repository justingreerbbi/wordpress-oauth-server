(function($){
  
  /** intiate jQuery tabs */
  $("#wo_tabs").tabs({
    activate: function(event, ui) {
      //window.location.hash = ui.newPanel.attr('id'); // Does not seem to work 100%
    }
  });

})(jQuery);