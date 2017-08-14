jQuery(function(){

    jQuery('#submit_post_sendbox_shipment').click(
             
	      function(){
           
              var fee= jQuery('#sendbox_courier_id').find(':selected').data('fee');
		 jQuery('#sendbox_quotes_div').hide();
         
    
         var data = {
      'action': 'post_sendbox_shipment',
      'order_id':  jQuery('#sendbox_order_id').val(),
            'origin_country': jQuery('#origin_country').val(),
            'origin_state': jQuery('#origin_state').val(),
            'origin_address': jQuery('#origin_address').val(),
            'origin_street': jQuery('#origin_street').val(),
            'origin_city': jQuery('#origin_city').val(),
            'origin_phone': jQuery('#origin_phone').val(),
             'origin_email': jQuery('#origin_email').val(),
             'origin_name': jQuery('#origin_name').val(),
             'auth_header': jQuery('#auth_header').val(),
			 'test_mode': jQuery('#test_mode').val(),
             'sendbox_courier_id': jQuery('#sendbox_courier_id').val(),
             'sendbox_rate_fee' : fee
               };

                jQuery('#sendbox_shipment_response_div').show();

          jQuery.ajax({
               method: "POST",
           url: ajaxurl,
           data: data
           }).done(function(response){
           jQuery('#sendbox_ajax_wait2').hide();
             jQuery('#sendbox_shipment_header').text("Shipment posted,details below:");
                jQuery('#sendbox_shipment_response').text(response);

           });
		  
		  }
	
	);
    
    jQuery('select[name="wc_order_action"]').change(function(){
           if(jQuery('select[name="wc_order_action"]').val()=="get_sendbox_carrier_quotes")
           {      var id=GetURLParameter('post');
             var origin_country=jQuery('#origin_country').val();
             var origin_state=jQuery('#origin_state').val();
             var origin_address=jQuery('#origin_address').val();
             var origin_street=jQuery('#origin_street').val();
             var origin_city=jQuery('#origin_city').val();
             var origin_phone=jQuery('#origin_phone').val();
              var origin_email=jQuery('#origin_email').val();
             var auth_header=jQuery('#auth_header').val();
              var origin_name=jQuery('#origin_name').val();
			   var test_mode=jQuery('#test_mode').val();
               jQuery('#sendbox_order_id').val(id);

                 jQuery('#carriersModal').show();
                 	var data = {
			'action': 'load_sendbox_carriers',
			'order_id':  id,
            'origin_country': origin_country,
            'origin_state': origin_state,
            'origin_address': origin_address,
            'origin_street': origin_street,
            'origin_city': origin_city,
            'origin_phone': origin_phone,
             'origin_name': origin_name,
             'auth_header': auth_header,
			 'test_mode': test_mode
		           };
       jQuery('#sendbox_ajax_wait').css("display", "block");

         jQuery.ajax({
               method: "POST",
           url: ajaxurl,
           data: data
           }).done(function(response){

			response=jQuery.parseJSON(response);
           
               if(response!==null && response.indexOf("message:")==-1)
                {
             jQuery.each(response, function(i, d) {

                if( d.id!==null)
                 {
                    jQuery('#sendbox_courier_id').append('<option data-fee="'+d.fee+'" value="' + d.id + '">' +d.courier.name+' -₦'+d.fee+ '</option>');
               }else
                  {
                       alert("Sorry no quotes at this time.")
                   }
                });
               }else
                 {
                        jQuery('#carriersModal').text(response) ;
                  }
              jQuery('#sendbox_ajax_wait').css("display", "none");
              jQuery('#carriersModal').dialog();
		});
                
           }
    });
    
  
});
function GetURLParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return sParameterName[1];
        }
    }
}

