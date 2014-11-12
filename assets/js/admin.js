(function($){
  
  /** intiate jQuery tabs */
  $("#wo_tabs").tabs({
    activate: function(event, ui) {
      //window.location.hash = ui.newPanel.attr('id'); // Does not seem to work 100%
    }
  });

  /**
   * Create New Client Form Submission Hook
   * @param  {[type]} e [description]
   * @return {[type]}   [description]
   */
  $('#create-new-client').submit(function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    var data = {
      'action': 'wo_create_new_client',
      'data': formData
    };
    jQuery.post(ajaxurl, data, function(response) {
      if(response != '1')
      {
        alert(response);
      }
      else
      {
        /** reload for the time being */
        location.reload();
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