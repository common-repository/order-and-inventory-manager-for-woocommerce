jQuery(function ($) {

    var front_script = {
        init: function () {
        	$('.single_variation_wrap').on('change',' .variation_id',this.hide_discontinued_prod);
        },
        hide_discontinued_prod: function(){
        	var variation_id = $(this).val();
        	var variation_ids = oimwc_front_obj.variation_ids;
        	if(variation_ids[variation_id]){
        		$('.single_variation_wrap').hide();
        		$('.discontinued_var_prod').show();
        	}
        	else{
        		$('.single_variation_wrap').show();	
        		$('.discontinued_var_prod').hide();
        	}
        	
        }
    };

    front_script.init();
});