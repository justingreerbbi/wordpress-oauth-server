(function($){
  
  /** intiate jQuery tabs */
  $("#wo_tabs").tabs({
    beforeActivate: function (event, ui) {
      var scrollTop = $(window).scrollTop();
      window.location.hash = ui.newPanel.selector;
      $(window).scrollTop(scrollTop);
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
  if(!confirm("Are you sure you want to delete this client?"))
    return;
  
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
      jQuery("#record_"+client_id+"").remove();
    }
  });
}

/**
 * Update a Client
 * @param  {[type]} form [description]
 * @return {[type]}      [description]
 */
function wo_update_client(form){
  alert('Submit the form');
}