var physical_stock = [];
var product_status = [];
var physical_arr = [];
var total_rows = 0;

jQuery(window).load(function(){
	setTimeout(function(){
		total_rows = jQuery('.order_lists tbody tr').length; 

		var orderCalcultate = setInterval(function(){
			startIndex = jQuery('.order_lists tbody tr.counted').length; 
			if( total_rows <= startIndex ){
				clearInterval(orderCalcultate);
				load_order_overview_data();
				jQuery(".order_overview_loader").hide();
				jQuery(".order_overview_main .lw_spin").hide();
				return;
			}
			show_order_overview_icons( startIndex, total_rows );
		}, 1000 );
		
	}, 2000);
	jQuery(window).bind("resize", function () {
	    if(jQuery('.notice-reminder').outerHeight()){
			var notice = jQuery('.notice-reminder').outerHeight();
		}else if(jQuery('.notice-success').outerHeight(true)){
			notice = jQuery('.notice-success').outerHeight(true);
		}else{
			notice = 0;
		}
		var tbl_head = Math.ceil(notice + jQuery('.order_field_wrap').outerHeight(true) + jQuery('#order_status_form').outerHeight(true) + jQuery('#wp_order_table2 thead').height() + (jQuery('.wp_order_table_panel').outerHeight(true) - jQuery('.wp_order_table_panel').height()));
		if(jQuery('.is-dismissible').length == 2 && jQuery('.order_lists tbody tr.counted').length > 4 && jQuery('.notice-reminder').length == 0){
	        jQuery('.oimwc-table-shadow').css('height','calc(89vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.is-dismissible').length ==1 && jQuery('.order_lists tbody tr.counted').length > 4 && jQuery('.notice-reminder').length == 0){
	        jQuery('.oimwc-table-shadow').css('height','calc(94vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.is-dismissible').length == 1 || jQuery('.is-dismissible').length == 2 && jQuery('.notice-reminder').length == 1 && jQuery('.order_lists tbody tr.counted').length > 4){
	        jQuery('.oimwc-table-shadow').css('height','calc(78vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.notice-reminder').length == 0 && jQuery('.is-dismissible').length ==0 && jQuery('.order_lists tbody tr.counted').length > 4){
	    	jQuery('.oimwc-table-shadow').css('height','calc(96vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.notice-reminder').length == 0 && jQuery('.is-dismissible').length ==0 && jQuery('.order_lists tbody tr.counted').length <= 4){
	        jQuery('.oimwc-table-shadow').css('height','calc(68vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.order_lists tbody tr.counted').length <= 4){
	        jQuery('.oimwc-table-shadow').css('height','calc(81vh - '+tbl_head+'px)');
	    }
	    else{
	        jQuery('.oimwc-table-shadow').css('height','calc(93vh - '+tbl_head+'px)');
	    }
	    jQuery('.wp_order_table_panel').attr('data-height',jQuery(window).height());
	}).trigger('resize');
});

function show_order_overview_icons( $start, $total_rows ){
	if( !jQuery('.order_lists').length ){
		return;
	}
	if( product_status.length == 0 ){	
		product_status = JSON.parse( jQuery('#product_status').val() );
	}
	if( physical_stock.length == 0 ){	
		jQuery('.order_lists tfoot tr:last-child td').each(function(index){
			if( index > 1 ){
				physical_stock[ index ] = parseInt( jQuery(this).find('.product_qty').html() );
			}
		});
		physical_arr = physical_stock.slice();	
	}
	var countRows = 0;
	for( $i = $start; $i <= $start + 10; $i++ ){
		if( $i > $total_rows ){
			break;
		}
		$rowObj = jQuery('.order_lists tbody tr:nth-child( '+( $i + 1 ) +' )');
		jQuery($rowObj).addClass('counted');
		if( jQuery($rowObj).find('.order_status_mark').length ){
			var hasStock = 1;
			var hasStock2 = 0;
			var hasStock1 = 0;
			var exclamation = true;
			var objRow = jQuery($rowObj);
			var counter = 0;
			var flag = false;
			jQuery($rowObj).find('td').each(function(index){
				if( index > 1 ){
					
					var current_product_qty = parseInt( jQuery(this).find('.product_qty').html() );
					if( parseInt(current_product_qty) > 0 ){
						
						if( physical_stock[ index ] >= current_product_qty ){	
							physical_stock[ index ] = parseInt( physical_stock[ index ] ) - current_product_qty;
							jQuery(this).append('<i class="fas fa-check-circle tooltip"><span class="tooltiptext">' + os_obj.manage_stock_yes + '<br>' + os_obj.in_stock_status +  '</span></i>');	
						}else{
							hasStock = 2;	
							physical_stock[ index ] = parseInt( physical_stock[ index ] ) - current_product_qty;
							jQuery(this).append('<i class="fas fa-times-circle tooltip"><span class="tooltiptext">' + os_obj.manage_stock_yes + '<br>' + os_obj.out_of_stock_status +  '</span></i>');	
						}
						
						if( current_product_qty && product_status[ index - 2 ] == "0" ){
							hasStock1 = 1;	
							if( jQuery(this).find('.fas').length ){
								physical_stock[ index ] = parseInt( physical_stock[ index ] ) + current_product_qty;
								jQuery(this).find('.fas').remove();
							}
							jQuery(this).append('<i class="fas fa-star color-red tooltip"><span class="tooltiptext">' + os_obj.manage_stock_no + '<br>' + os_obj.out_of_stock_status +  '</span></i>');
							flag = true;
						}
						if( current_product_qty && product_status[ index - 2 ] == "1" ){
							hasStock2 = 1;	
							if( jQuery(this).find('.fas').length ){
								physical_stock[ index ] = parseInt( physical_stock[ index ] ) + current_product_qty;
								jQuery(this).find('.fas').remove();
							}	
							jQuery(this).append('<i class="fas fa-star tooltip"><span class="tooltiptext">' + os_obj.manage_stock_no + '<br>' + os_obj.in_stock_status +  '</span></i>');	
							flag = true;
						}
						
						if(jQuery(this).find('.fa-times-circle').length && physical_arr[index] < current_product_qty)
						{
							exclamation = false;
						}
												
						if(product_status[ index - 2 ] != "1" && product_status[ index - 2 ] != "0" && physical_arr[index] <= 0)
						{
							counter += 1;
						}
							
					}
				}	
			});
						
			if( hasStock2 ){
				hasStock = 3;			
			}
			if( hasStock1 ){
				hasStock = 4;			
			}
			if(jQuery($rowObj).find('.fa-times-circle').length && (jQuery($rowObj).find('.fa-star').length || jQuery($rowObj).find('.fa-star color-red').length))
			{
				hasStock = 4;
			}
			
			switch( hasStock ){
				case 1:
					jQuery($rowObj).find('.order_status_mark').append('<i class="fa fa-check-circle fa-lg tooltip"><span class="tooltiptext">' + os_obj.ready_to_process + '</span></i>');	
					break;
				case 2:
					jQuery($rowObj).find('.order_status_mark').append('<i class="fa fa-times-circle fa-lg color-red tooltip"><span class="tooltiptext">' + os_obj.out_of_stock + '</span></i>');	
					break;
				case 3:
					jQuery($rowObj).find('.order_status_mark').append('<i class="far fa-check-circle fa-lg color-green tooltip"><span class="tooltiptext">' + os_obj.in_stock_special + '</span></i>');
					break;
				case 4:
					jQuery($rowObj).find('.order_status_mark').append('<i class="far fa-times-circle fa-lg color-red tooltip"><span class="tooltiptext">' + os_obj.out_of_stock_special + '</span></i>');
					break;
			}
			
			if(counter > 0)
			{
				exclamation = false;
			}
			
			if(exclamation && objRow.find('.fa.fa-check-circle').length == 0 && objRow.find('.far.fa-check-circle.color-green').length == 0 )
			{
				if(flag)
				{
					objRow.find('.order_status_mark').append('<i class="fa fa-exclamation-circle color-yellow tooltip"><span class="tooltiptext">' + os_obj.exclamation_yellow + '</span></i>');
				}
				else
				{
					objRow.find('.order_status_mark').append('<i class="fa fa-exclamation-circle tooltip"><span class="tooltiptext">' + os_obj.exclamation_blue + '</span></i>');
				}
			}
		}
	}
}

function load_order_overview_data(){
	if( !jQuery('.order_lists').length ){
		return;
	}
    jQuery('.wp_order_table_panel').scroll(function(){
		jQuery('#wp_order_table_clone').css( 'left', jQuery(this).scrollLeft() );
	});

	if(('.missing_prod_qty').length > 0){
		jQuery('.missing_prod_qty').each(function(){
			var close_div = jQuery(this).parent('td').find( '.wc-order-item-sku' );
			jQuery( this ).appendTo( close_div );
		});
	}
	if( jQuery('#wp_order_table2').length ){
		if(jQuery('.notice-reminder').outerHeight()){
			var notice = jQuery('.notice-reminder').outerHeight();
		}else if(jQuery('.notice-success').outerHeight(true)){
			notice = jQuery('.notice-success').outerHeight(true);
		}else{
			notice = 0;
		}
		var tbl_head = Math.ceil(notice + jQuery('.order_field_wrap').outerHeight(true) + jQuery('#order_status_form').outerHeight(true) + jQuery('#wp_order_table2 thead').height() + (jQuery('.wp_order_table_panel').outerHeight(true) - jQuery('.wp_order_table_panel').height()));
		if(jQuery('.is-dismissible').length == 2 && jQuery('.order_lists tbody tr.counted').length > 4 && jQuery('.notice-reminder').length == 0){
	        jQuery('.oimwc-table-shadow').css('height','calc(89vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.is-dismissible').length ==1 && jQuery('.order_lists tbody tr.counted').length > 4 && jQuery('.notice-reminder').length == 0){
	        jQuery('.oimwc-table-shadow').css('height','calc(94vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.is-dismissible').length == 1 || jQuery('.is-dismissible').length == 2 && jQuery('.notice-reminder').length == 1 && jQuery('.order_lists tbody tr.counted').length > 4){
	        jQuery('.oimwc-table-shadow').css('height','calc(78vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.notice-reminder').length == 0 && jQuery('.is-dismissible').length ==0 && jQuery('.order_lists tbody tr.counted').length > 4){
	    	jQuery('.oimwc-table-shadow').css('height','calc(96vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.notice-reminder').length == 0 && jQuery('.is-dismissible').length ==0 && jQuery('.order_lists tbody tr.counted').length <= 4){
	        jQuery('.oimwc-table-shadow').css('height','calc(68vh - '+tbl_head+'px)');
	    }
	    else if(jQuery('.order_lists tbody tr.counted').length <= 4){
	        jQuery('.oimwc-table-shadow').css('height','calc(81vh - '+tbl_head+'px)');
	    }
	    else{
	        jQuery('.oimwc-table-shadow').css('height','calc(93vh - '+tbl_head+'px)');
	    }
		jQuery('.wp_order_table_panel').attr('data-height',jQuery(window).height());
		jQuery('#wp_order_table2').clone().attr('id','wp_order_table_clone').prependTo( ".wp_order_table_panel" );
		jQuery('#wp_order_table2').attr('data-relation', 'wp_order_table_clone');
		jQuery('#wp_order_table_clone').attr('data-relation', 'wp_order_table2');
		jQuery('#wp_order_table_clone th:not(:first-child()):not(:nth-child(2))').remove();
		jQuery('#wp_order_table_clone td:not(:first-child()):not(:nth-child(2))').remove();
		jQuery('#wp_order_table_clone').width( jQuery('#wp_order_table2 tfoot td.order_id').outerWidth() ); 
		jQuery('#wp_order_table_clone th').height( jQuery('#wp_order_table2 th').outerHeight() ); 
		jQuery('.wp_order_table_panel tbody tr').mouseenter(function(){
			var index = parseInt( jQuery(this).index() ) + 1;
			jQuery('.wp_order_table_panel tbody tr:nth-child(' + index  + ')').addClass('active');
		});
		jQuery('.wp_order_table_panel tbody tr').mouseleave(function(){
			jQuery('.wp_order_table_panel tbody tr').removeClass('active');
		});
	}
}