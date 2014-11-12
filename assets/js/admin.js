(function($){
  
  /** intiate jQuery tabs */
  $("#wo_tabs").tabs({
    activate: function(event, ui) {
      //window.location.hash = ui.newPanel.attr('id'); // Does not seem to work 100%
    }
  });

  $('#create-new-client').submit(function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    //console.log(formData);
    var data = {
      'action': 'wo_create_new_client',
      'data': formData
    };
    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.post(ajaxurl, data, function(response) {
      if(response != '1')
      {
        alert(response);
      }
      else
      {
        location.reload(); // Reload the page - Temp way until I have time to do it properly
      }
    });
  });
  

})(jQuery);

/**
 * Remove a Client
 */
function wo_remove_client (client_id)
{
  var data = {
      'action': 'wo_remove_client',
      'data': client_id
    };
    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
    jQuery.post(ajaxurl, data, function(response) {
      if(response != '1')
      {
        alert(response);
      }
      else
      {
        alert(client_id);
        jQuery("#record_"+client_id+"").remove();
      }
    });
}