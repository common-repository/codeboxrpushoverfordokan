(function ( $ ) {
	"use strict";

    //console.log('in pushover');

	$(document).ready(function(){
        $('.cbpushover_ajax_icon').hide();

        var self = $( "form#pushover-form"  );



        $( self ).submit(function( event ) {
            //alert( "Handler for .submit() called." );
            event.preventDefault();


            var   form_data = self.serialize() + '&action=pushover_settings&form_id=pushover-form' ;
            //console.log(form_data);


            self.find('.ajax_prev').append('<span class="dokan-loading"> </span>');
            $.post(dokan.ajaxurl, form_data, function(resp) {

                self.find('span.dokan-loading').remove();
                $('html,body').animate({scrollTop:100});

                if ( resp.success ) {
                    // Harcoded Customization for template-settings function
                    $('.dokan-ajax-response').html( $('<div/>', {
                        'class': 'dokan-alert dokan-alert-success',
                        'html': '<p>' + resp.data.msg + '</p>'
                    }) );

                    $('.dokan-ajax-response').append(resp.data.progress);

                }else {
                    $('.dokan-ajax-response').html( $('<div/>', {
                        'class': 'dokan-alert dokan-alert-danger',
                        'html': '<p>' + resp.data + '</p>'
                    }) );
                }
            });
        });




		// Place your public-facing JavaScript here
        $('#setting_cbpdpushup_sendtest').click(function(e){
            e.preventDefault();

            var _cbthis = $(this);
            //var cbdpid     = $(this).attr('data-user-id');

            var cbuserapi  = $('#pushovernotifiction_userapitoken').val();

            //var cbdebug    = $('#setting_cbpdpushup_debug').val();
            var cbdevice   = $('#pushovernotifiction_device').val();
            var cbbusy     = $(this).attr('data-busy');

            if(cbuserapi == ''){
                alert('Please update userapi token');
            }
            if(cbbusy == '0' && cbuserapi != ''){
                $(_cbthis).attr('data-busy' , '1');
                $('.cbpushover_ajax_icon').show();
                jQuery.ajax({
                    type     : "post",
                    dataType : "json",
                    url      : cbpushovertest.ajaxurl,
                    data     : {action: "cbpushovertest" , cbuserapi : cbuserapi , cbdevice : cbdevice, nonce: cbpushovertest.nonce   },
                    success  : function(data, textStatus, XMLHttpRequest){

                        $(_cbthis).attr('data-busy' , '0');
                        $('.cbpushover_ajax_icon').hide();
                        if(data != '1'){
                            alert(data);
                        }
                    }
                });
            }

        });

	});

}(jQuery));