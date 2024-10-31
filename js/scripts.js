var Accordion = function(el, multiple) {
    this.el = el || {};
    this.multiple = multiple || false;

    // Variables privadas
    var links = this.el.find('.link');
    // Evento
    links.unbind('click');
    links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
}

Accordion.prototype.dropdown = function(e) {
    var $el = e.data.el;
        $this = jQuery(this),
        $next = $this.next();

    $next.slideToggle();
    $this.parent().toggleClass('open');

    if (!e.data.multiple) {
        $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
    };
}

jQuery(function ($) {

    var pagination_start = false;
   
    var supplier_variation = {
        init: function () {
            $('#variable_product_options').on('change', 'input.variable_is_supplier', this.variable_is_supplier);
            $('#frm_product_handler').submit(this.add_product_manually);
            $('#btnCreateProductFile').click(this.create_product_file);
            $('body').on('click', '.btnOrderFullyArrived', this.complete_request_product_order);
            $('body').on('click', '.btnOrderSave', this.complete_request_product_order);
            $('body').on('click', '.btnFinalizeProduct', this.finalize_product_order);
            $('body').on('click', '.btnRemoveProduct', this.remove_prod_frm_purchase_ordr);
            $('.add_unit_btn').click(this.add_input_units);
            $('#im_settings_form').on('click', 'a.unit_save', this.unit_save);
            $('#im_settings_form').on('click', 'a.unit_delete', this.unit_delete);
            $(".unit_save").hide();
            if($("#display_units tr").length > 2){
                $(".unit_save").show();
            }
            $("#update_database").click(this.get_total_products_to_update);
            $('#inventory_product_data label[for="oimwc_physical_stock"]').click(function(e) { 
                e.preventDefault();
            });
            $('#frm_product_handler').click(this.disply_warning_for_list);
            $('body').on('click','.btnAddItemToOrder',this.add_item_to_order);
            $('body').on('keyup','.product_listing_panel #the-list:eq(1) .arrived_qty_handler',this.calculate_product_amount );
            $('body').on('keyup','.product_listing_panel #the-list:first .arrived_qty_handler',this.calculate_product_amount_order );
            $('body').on('submit', '#frm_supplier_order', this.update_order );
            $('body').on('click', '#frm_supplier_order .cancel_order',this.cancel_order );
            $('body').on('click', '.discontinued_product_cls', this.disable_low_stock_warning );
            $('body').on('change','#_manage_stock',this.disabled_if_not_manage_stock);
            $('body').on('change','.variable_manage_stock',this.disabled_if_not_manage_stock_variable);
            $('body').on('focus','.arrival_date_cls',this.initialize_arrival_date);
            $('body').on('click','.lock_order_btn',this.lock_awaiting_order);
            $('body').on('click','.order_product_files .cancel_awaiting_order',this.cancel_awaiting_order);
            $('body').on('click','.add_info_po_btn',this.add_info_to_po);
            $('body').on('click','.add_private_note_btn',this.add_private_note);
            $('body').on('click','.support_subjects input[type="radio"]',this.show_hide_contact_form);
            $('body').on('click','.technical_support_content h4.title',this.show_hide_wp_details);
            $('body').on('click','.submit_contact',this.validate_contact_form);
            $('body').on('click','.upgrade_notice_cls',this.upgrade_notice_warning);
            $('body').on('mouseenter','.woocommerce-help-tip',this.hide_custom_tooltip);
            $('body').on('mouseleave','.woocommerce-help-tip',this.show_custom_tooltip);
            $('.pst_search_chk').sortable();
            $('.pst_search_chk').disableSelection();
            supplier_variation.help_page_accordion();
            $('body').on('keydown','.user_wise_access #txt_access_users',this.autocomplete_user_list);
            $('body').on('click','#btn_add_user_access',this.add_user_to_access_list);
            $('body').on('click','.li_selected_access_user',this.li_selected_access_user);
            $('body').on('click','#btn_remove_user_access',this.li_remove_selected_access_user);
            $('body').on('click','.download_po_btn',this.display_popup_on_download_po);
            $('body').on('click','.inside_po_buttons .cancel_awaiting_order',this.view_order_page_cancel_order);
            $('#custom_order_status_dd').select2({
                placeholder: 'Select an order status'
            });
            $('#reduce_physical_stock_OStatus').select2({
                placeholder: 'Select an order status'
            });
            $('#oimwc_discontinued_replacement_product').select2({
                placeholder: oimwc_obj.select_product,
                allowClear: true,
                ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250, 
                        data: function (params) {
                            return {
                                search_val: params.term, 
                                action: 'get_products_list_with_sku'
                            };
                        },
                        processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                                $.each( data, function( index, text ) { 
                                    options.push( { id: index, text: text  } );
                                });
                            }
                            return {
                                results: options
                            };
                        },
                    cache: true
                },
                minimumInputLength: 3,
                language: {
                    inputTooShort: function() {
                        return oimwc_obj.product_placeholder;
                    }
                } 
            }).on("select2:unselecting", function(e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 0);
            });           
           
            $( "#oimwc_settings_tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
            $( "#oimwc_settings_tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
            $('#save_inventory_settings').click(this.save_inventory_settings);
            $('.sticky_header_supplier #posts-filter').submit(this.custom_search_supplier);
            $('.post-type-supplier .wp-list-table thead th a').click(this.custom_sort_supplier);
            if( $('.stock_values_spinner').length ){
                supplier_variation.get_current_stock_values();
            }
            $('#IO_supplier_filter').change(this.low_stock_products_filter);
            $(window).scroll(this.load_low_stock_products);
            $('.search_lw_prod_panel form,.search_btn_order').submit(this.search_low_stock_products);
            if(jQuery('.sticky_header_supplier').length){
                jQuery(jQuery(".page-title-action").detach()).prependTo(".sticky_header_supplier .wrap");
                jQuery(jQuery(".wp-heading-inline").detach()).prependTo(".sticky_header_supplier .wrap");
            }
            $(window).scroll(this.load_completed_purchase_orders);
            $(window).scroll(this.load_view_purchase_orders_products);
            $('#po_supplier_filter').change(this.purchase_orders_supplier_filter);
            $('#add_shipping_address').click(this.add_multiple_shipping_address);
            $('body').on('click','.delete_add_btn',this.delete_shipping_address);
            $('.order_product_files .wp-list-table th a').click(this.custom_sort_PO_columns);
            var accordion = new Accordion($('.company_shipping_address_panel.accordion'),false);
            $('body').on('click','.add_supplier',this.add_additional_supplier);
            var accordion = new Accordion($('.supplier_accordion_panel .accordion'), false);
            $('body').on('click', '.save_supplier', this.save_supplier_info);
            $('body').on('click','.supplier_accordion .remove_supplier',this.remove_supplier);
            jQuery('.save-variation-changes').click(this.variation_default_save_changes);
            $('#oimwc_physical_stock').on('keyup',this.disable_physical_stock_function);
            $('#oimwc_physical_stock').on('change',this.disable_physical_stock_function);
            $('#oimwc_physical_stock').on('keypress',this.allow_only_numbers);
            $('body').on( 'input', '#oimwc_physical_stock',this.restrict_characters_on_paste);
            $('body').click('.oimwc_physical_stock_variation',this.disable_physical_stock_function_var);
            $('body').change('.oimwc_physical_stock_variation',this.disable_physical_stock_function_var);
            $('body').keypress('.oimwc_physical_stock_variation',this.allow_only_numbers);
            $('body').on( 'input', '.oimwc_physical_stock_variation',this.restrict_characters_on_paste);
            $('#oimwc_physical_units_stock').on('keyup',this.disable_unit_stock_function);
            $('#oimwc_physical_units_stock').on('change',this.disable_unit_stock_function);
            $('#oimwc_physical_units_stock').on('keypress',this.allow_only_numbers);
            $('body').on( 'input', '#oimwc_physical_units_stock',this.restrict_characters_on_paste);
            $('body').click('.oimwc_physical_units_stock_variation',this.disable_unit_stock_function_var);
            $('body').change('.oimwc_physical_units_stock_variation',this.disable_unit_stock_function_var);
            $('body').keypress('.oimwc_physical_units_stock_variation',this.allow_only_numbers);
            $('body').on( 'input', '.oimwc_physical_units_stock_variation',this.restrict_characters_on_paste);
            $('#oimwc_our_pack_size').on('keyup',this.disable_pack_size_function);
            $('#oimwc_our_pack_size').on('change',this.disable_pack_size_function);
            $('#oimwc_our_pack_size').on('keypress',this.allow_only_numbers);
            $('body').on( 'input', '#oimwc_our_pack_size',this.restrict_characters_on_paste);
            $('body').click('.oimwc_our_pack_size_variation',this.disable_pack_size_function_var);
            $('body').change('.oimwc_our_pack_size_variation',this.disable_pack_size_function_var);
            $('body').keypress('.oimwc_our_pack_size_variation',this.allow_only_numbers);
            $('body').on( 'input', '.oimwc_our_pack_size_variation',this.restrict_characters_on_paste);
            $('#_stock').on('click',this.disable_stock_function);
            $('#_stock').on('change',this.disable_stock_function);
            $('#_stock').on('keypress',this.allow_only_numbers);
            $('body').on( 'input', '#_stock',this.restrict_characters_on_paste);
            $('body').click('.variable_stock_variation',this.disable_stock_function_var);
            $('body').change('.variable_stock_variation',this.disable_stock_function_var);
            $('body').keypress('.variable_stock_variation',this.allow_only_numbers);
            $('body').on( 'input', '.variable_stock_variation',this.restrict_characters_on_paste);

            $('body').on( 'input', '.oimwc_restrict_characters_on_paste',this.restrict_characters_on_paste);
            $('body').on( 'keypress', '.oimwc_allow_only_numbers',this.allow_only_numbers);

            $('.download_wrong_stock_data_link').on( 'click', this.wrong_stock_data_call );
            $('#view_wrong_product_panel').on( 'click', this.view_wrong_product_panel );
            $('.ignore_rating,.rating_cancel').on( 'click', this.ignore_rating_function );
            $('#view_wrong_order_panel').on( 'click', this.view_wrong_order_panel );
            $('body').on('click','.update_btn', this.update_oimwc_data);
            $('body').on('click','.update_btn_variable', this.update_oimwc_data_variable);
            $('body').on( 'click', '.upload_img', this.upload_pdf_logo);
            $('body').on( 'click', '.remove_img', this.remove_pdf_logo);
            var accordion = new Accordion($('.company_office_address_panel.accordion'),false);
            $('#add_office_address').click(this.add_multiple_office_address);
            $('.show_all_product').change(this.low_stock_products_filter);
            $('body').on('click','#refresh_order .button', this.refresh_purchasde_order_preview);
            supplier_variation.purchase_order_accordion();
            $('body').on('click','.btnRemovePO', this.remove_purchase_order_product);
            $('.search_lw_prod_panel form').click(this.search_product);
            $('#btn_create_product').click(this.add_temp_product);
            $('body').on('click','.btnTempRemoveProduct',this.remove_purchase_order_product);
            $('body').on('click','.btnRemoveProduct',this.remove_tmp_product);
            $('body').on('change','#request_date',this.select_order_number);
            $('#tmp_pack_size,#tmp_product_qty').keyup(this.tmp_allow_numbers);
            $('#tmp_product_price').keyup(this.tmp_allow_product_price);
        },
        autocomplete_user_list: function(event){
            if (event.keyCode === $.ui.keyCode.TAB &&
                $(this).autocomplete("instance").menu.active) {
                event.preventDefault();
            }
            $(this).autocomplete({           
                minLength: 1,
                source: function (name, response) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: ajaxurl,
                        data: 'action=get_access_users&keyword=' + name.term,
                        success: function (data) {
                            response(data);
                            $(".user_wise_access #txt_access_users").removeClass("ui-autocomplete-loading");
                        }
                    });
                },
                focus: function (event, ui) {
                    $(".ui-helper-hidden-accessible").hide();
                    event.preventDefault();
                },
                select: function (event, ui) {
                    $(".user_wise_access #txt_access_users").val(ui.item.label);
                    $('#btn_add_user_access').attr('data-user_id',ui.item.value);
                    return false;
                }
            });
        },
        add_user_to_access_list: function(){

            if($('.no_user_row').length){
                $('.no_user_row').remove();
            }
            var user_id = $(this).attr('data-user_id');
            var user_data = $('#txt_access_users').val();
            if( user_id != '' && user_data != '' ){
                $('.ul_selected_access_user').append('<li class="li_selected_access_user" data-user_id="'+user_id+'"><div>'+ user_data +'</div><input type="hidden" name="access_user_list[]" value="'+user_id+'"></li>');
            }
            else{
               $('.ul_selected_access_user').append('<li class="no_user_row">'+oimwc_obj.no_user_found+'</li>'); 
            }
            $('#txt_access_users').val('');

        },
        li_selected_access_user: function(){
            $(this).toggleClass('selected_user');
        },
        li_remove_selected_access_user: function(){
            var flag = true;
            var msg_text = oimwc_obj.user_remove_text;
            if(!$('.ul_selected_access_user .selected_user').length){
               flag = false;
               msg_text = oimwc_obj.select_any_user_txt;
            }
            
            swal({
                text: msg_text,
                buttons: true,
                buttons: {
                    cancel: oimwc_obj.cancel_text,
                    ok: {
                      text: 'Ok',
                      value: "ok"
                    },
                }
            }).then((value) => {
                switch (value) {
                    case "ok":
                        if(flag){
                            $('.ul_selected_access_user .selected_user').remove();
                            if(!$('.ul_selected_access_user li').length){
                                $('.ul_selected_access_user').append('<li class="no_user_row">'+oimwc_obj.no_user_found+'</li>');
                            }
                        }
                    break;
                  }
            });

            return false;
        },
        variable_is_supplier: function () {
            $(this).closest('.woocommerce_variation').find('.show_if_variation_manage_supplier').hide();

            if ($(this).is(':checked')) {
                $(this).closest('.woocommerce_variation').find('.show_if_variation_manage_supplier').show();
            }
        },
        disply_warning_for_list: function(){
            if( $(this).find('.disabled').length ){
                if($('.show_all_product').is(":checked") == true){
                    swal(oimwc_obj.select_show_all_product);
                    return false;
                }else{
                    swal(oimwc_obj.select_supplier_for_list);
                    return false;
                }
            }
        },
        add_product_manually: function () {
            if( $(this).find('.disabled').length ){
                swal(oimwc_obj.select_supplier_for_list);
                return false;
            }
            $('#frm_product_handler input').removeClass('error');
            if (!$('#frm_product_handler input[name="product_sku"]').val().length) {
                $('#frm_product_handler input[name="product_sku"]').addClass('error');
            }
            if ($('#frm_product_handler input[name="requested_stock"]').length &&
             ($('#frm_product_handler input[name="requested_stock"]').val() == '' || $('#frm_product_handler input[name="requested_stock"]').val() <= 0) ) {
                $('#frm_product_handler input[name="requested_stock"]').addClass('error');
            }

            if ($('#frm_product_handler input').hasClass('error')) {
                return false;
            }
            $('.view_order_add_to_list.product_handler .lw_spin').show();
            $('.view_order_product_listing_panel').addClass('oimwc_overlay');
            $('.inventory_management_panel.blockUI .IO_spin').show();
            $('.inventory_management_panel.blockUI').addClass('oimwc_overlay');
            $.ajax({
                url: ajaxurl,
                data: $('#frm_product_handler').serialize(),
                method: 'POST',
                success: function (data) {
                    if( $('.product_listing_panel').length ){
                        $('.inventory_management_panel.blockUI .IO_spin').hide();
                        $('.inventory_management_panel.blockUI').removeClass('oimwc_overlay');
                        var datavalues = JSON.parse(data);
                        if(datavalues['check_product'] != true){
                            if (datavalues['success']) {
                                if ($('.wp-list-table input[data-id="' + datavalues['id'] + '"]').length) {
                                    alert(datavalues['message']);
                                } else {
                                    $('#frm_product_handler').trigger('reset');
                                    if($(".no-items").length > 0){
                                        $(".no-items").remove();
                                    }
                                    $('.purchase_order_table:eq(1) tbody').append(datavalues['data']);
                                }
                            } else {
                                alert(datavalues['message']);
                            }
                        }
                        if(datavalues['check_product'] == true){
                            var list = datavalues['message'].join("\r\n");
                            var span = document.createElement("span");
                            span.innerHTML = list;
                            swal({
                                    content: span,
                                    buttons: true,
                                    className: "swal_multiple_popup",
                                    buttons: {
                                        cancel: oimwc_obj.cancel_text,
                                        finalize: {
                                          text: 'Ok',
                                          value: "Ok",
                                        },
                                    }
                                }).then((value) => {
                                    switch (value) {
                                        case "Ok":
                                        if($('.column-amount input[name="product['+datavalues['product_id']+'][qty]"]').val() != 0){
                                        $.ajax({
                                            url: ajaxurl,
                                            data: $('.product_listing_panel form').serialize(),
                                            method: 'POST',
                                            success: function (data) {
                                                datavalues = JSON.parse(data);
                                                if (datavalues['success']) {      
                                                    window.location.href = datavalues['redirect_url'];   
                                                } else {
                                                    alert(datavalues['message']);
                                                    $('.inventory_management_panel.blockUI .IO_spin').hide();
                                                }
                                            }
                                        });
                                        }else{
                                            swal.close();
                                            setTimeout(function(){
                                                alert(oimwc_obj.po_order_file);
                                            },1000);
                                        }
                                    }
                                });
                        }
                    }
                    if( $('.view_order_cls').length ){
                        $('.view_order_add_to_list.product_handler .lw_spin').hide();
                        $('.view_order_product_listing_panel').removeClass('oimwc_overlay');
                        if(data.data.result){
                            var product_id = data.data.product_id;
                            var result_message = $('p').html(data.data.message).html();
                            swal({
                                text: result_message,
                                buttons: true,
                                buttons: {
                                    cancel: oimwc_obj.cancel_text,
                                    finalize: {
                                      text: 'Ok',
                                      value: "Ok",
                                    },
                                }
                            }).then((value) => {
                                switch (value) {
                                    case "Ok":
                                    $.ajax({
                                        url: ajaxurl,
                                        data: {
                                            action: 'add_product_to_po',
                                            supplier: $('#frm_product_handler input[name="supplier"]').val(),
                                            date: $('#frm_product_handler input[name="date"]').val(),
                                            order_number: $('#frm_product_handler input[name="order_number"]').val(),
                                            product_id: product_id,
                                            requested_stock: $('#frm_product_handler input[name="requested_stock"]').val()
                                        },
                                        method: 'POST',
                                        success: function (data) {
                                            swal('',data.data.message,"success");
                                            $('.view_order_cls .oimwc-table-shadow').html(data.data.result);
                                            $('#view_po_page').val(1);
                                            jQuery( '.tips' ).tipTip({ 'attribute': 'data-tip', 'fadeIn': 50, 'fadeOut': 50, 'delay': 50 });
                                            jQuery('#frm_product_handler')[0].reset();
                                        },
                                        error: function (data){

                                        }
                                    });
                                }
                            });
                        }
                        else{
                            swal('',data.data.message, "error");
                            jQuery('#frm_product_handler')[0].reset();
                        }
                    }
                },
                error: function (data) {
                    alert(data);
                }
            });
            return false;
        },
        cancel_order: function(){
            $('tr').removeClass('add_product_active');
            jQuery('.ui-dialog-titlebar-close').click();
        },
        add_item_to_order: function(){
            var self = $(this);
            var product_date = $(this).parents('.amount').find('.arrived_qty_handler');
            if( !$(product_date).val() ){
                alert( oimwc_obj.enter_value );
                return;
            }

            if( parseInt( $(product_date).val() ) == NaN || parseInt( $(product_date).val() ) <= 0 ){
                alert( oimwc_obj.enter_valid_qty );
                return;
            }

            var template = wp.template( "add_product_existing_PO" );
            var supplier_id = $('#IO_supplier_filter').val();
            $.ajax({
                url: ajaxurl,
                data: {
                    action : 'add_product_to_order_callback',
                    supplier_id: supplier_id
                },
                method: 'POST',
                success: function (result) {
                    if(result.success){
                        self.parents('tr').addClass('add_product_active');
                        var tmplData = {
                            supplier_order_list : result.data.supplier_order_list,
                            lock_supplier_list: result.data.lock_supplier_list,
                            order_status: result.data.order_status,
                            receiving_status : result.data.check_status,
                            lock_title: result.data.lock_title,
                            product_id: $(product_date).data('id'),
                            qty: $(product_date).val(),
                            stock: $(product_date).data('stock'),
                            oimwc_product_nonce: $('#oimwc_product_nonce').val(),
                            supplier_id : supplier_id
                        }; 
                        if(result.data.lock_product == 1)
                        {
                            swal(oimwc_obj.lock_product_msg);
                            return;
                        }
                        else
                        {
                            var result = template(tmplData);
                            $('#supplier_order_list_panel #frm_supplier_order').html(result);
                            $( "#supplier_order_list_panel" ).dialog({ dialogClass: 'fixed-dialog' });
                        }
                    }
                    else{
                        alert(oimwc_obj.no_previous_order);
                        return;
                    }
                },
                error: function (data) {
                    
                }
            });

            return false;
            
        },
        update_order: function( e ){
            e.preventDefault();
            if( !$('#frm_supplier_order select').val() ){
                alert( oimwc_obj.select_order );
                return false;
            }
            $('#loader').show();
            var order_number = $('#request_date option:selected').data('id');
            $.ajax({
                url: ajaxurl,
                data: $('#frm_supplier_order').serialize()+'&order_number='+order_number,
                method: 'POST',
                success: function (data) {
                    datavalues = JSON.parse(data);
                    //$('.inventory_management_panel.blockUI').removeClass('blockOverlay');
                    if (datavalues['success']) {
                        window.location.reload();
                        jQuery('.ui-dialog-titlebar-close').click();
                        $('#supplier_order_list_panel #loader').hide();
                        
                        var supplier_level = $('select[name="supplier_id"] option:selected').attr('data-sup_level');
                        var dname = $('select[name="supplier_id"] option:selected').data('name');
                        var dno = $('select[name="supplier_id"] option:selected').attr('data-no');
                        dno = parseInt(dno) - 1;
                        $('select[name="supplier_id"] option:selected').attr('data-no', dno);
                        $('select[name="supplier_id"] option:selected').html(dname + ' (' + dno + ')');
                        if( dno < supplier_level ){
                            $('select[name="supplier_id"] option:selected').removeClass('lowstock_cls');
                            if($('.lowstock_cls').length){
                                $('.oimwc_red_bubble').html( $('.lowstock_cls').length );
                            }else{
                                $('.oimwc_red_bubble').remove();
                            }
                        }
                        if($('.wp-list-table tbody tr').length == 0) {
                            $('.wp-list-table tbody').append('<tr><td colspan="5">'+oimwc_obj.no_products+'</td></tr>');
                        }
                    } else {
                        $('#loader').hide();
                        alert(datavalues['message']);
                        $('.inventory_management_panel.blockUI').removeClass('blockOverlay');
                        $('tr').removeClass('add_product_active');
                    }
                },
                error: function (data) {
                    $('#loader').hide();
                    $('.inventory_management_panel.blockUI').removeClass('blockOverlay');
                    $('tr').removeClass('add_product_active');
                    alert(data);
                }
            });
            return false;   
        },      
        create_product_file: function () {
            if( $(this).hasClass('disabled') ){
                swal(oimwc_obj.select_supplier);
                return;
            }

            $('.inventory_management_panel.blockUI .IO_spin').show();

            var add_info = prompt(oimwc_obj.prompt_msg, "");
            jQuery('input[name="additional_info"]').val(add_info);

            $.ajax({
                url: ajaxurl,
                data: $('.product_listing_panel form').serialize(),
                method: 'POST',
                success: function (data) {
                    datavalues = JSON.parse(data);
                    //$('.inventory_management_panel.blockUI').removeClass('blockOverlay');
                    if (datavalues['success']) {
                        
                        if (datavalues['product_id'] && datavalues['product_id'].length) {
                            for ($i = 0; $i < datavalues['product_id'].length; $i++) {
                                //$('.wp-list-table input[data-id="'+ datavalues['product_id'][$i] +'"]').parents('tr').remove();
                            }
                            if ($('.wp-list-table tbody tr').length == 0) {
                                $('.wp-list-table tbody').append(datavalues['default']);
                            }
                        }
                        
                        window.location.href = datavalues['redirect_url'];
                        
                    } else {
                        alert(datavalues['message']);

                        //$('.inventory_management_panel.blockUI').removeClass('blockOverlay');
                        $('.inventory_management_panel.blockUI .IO_spin').hide();
                    }
                },
                error: function (data) {
                    //$('.inventory_management_panel.blockUI').removeClass('blockOverlay');
                    $('.inventory_management_panel.blockUI .IO_spin').hide();
                    alert(data);
                }
            });
            return false;
        },
        complete_request_product_order: function () {
            $obj = $(this);
            $row_id = $(this).data('id');
            $('input[type="text"]').removeClass('error');
            if ($(this).data('product_id')) {
                $data = 'action=complete_product_order&id=' + $row_id + '&product_id=' + $(this).data('product_id');
            } else {
                if ($('input[data-id="' + $row_id + '"]').val() == '') {
                    $('input[type="text"][data-id="' + $row_id + '"]').addClass('error');
                    return false;
                }
                $data = 'action=complete_product_order&id=' + $row_id + '&qty=' + $('input[data-id="' + $row_id + '"]').val();
            }
            $($obj).parent('.action').addClass('blockUI').addClass('blockOverlay');
            $.ajax({
                url: ajaxurl,
                data: $data,
                method: 'POST',
                beforeSend: function(data){
                    jQuery('.btnOrderSave').attr('disabled',true);
                    jQuery('.btnOrderFullyArrived').attr('disabled',true);
                    jQuery('.btnFinalizeProduct').attr('disabled',true);
                    jQuery('.btnRemoveProduct').attr('disabled',true);
                    jQuery('.btnTempRemoveProduct').attr('disabled',true);
                    jQuery('#tiptip_holder').css('display','none');
                },
                success: function (data) {
                    jQuery('.btnOrderSave').attr('disabled',false);
                    jQuery('.btnOrderFullyArrived').attr('disabled',false);
                    jQuery('.btnFinalizeProduct').attr('disabled',false);
                    jQuery('.btnRemoveProduct').attr('disabled',false);
                    jQuery('.btnTempRemoveProduct').attr('disabled',false);
                    datavalues = JSON.parse(data);
                    if (datavalues['success']) {                        
                        $obj = $('input[data-id="'+ datavalues['row_id'] +'"]').first();
                        if(parseInt(datavalues['arrival_stock']) >= parseInt(datavalues['requested_stock'])){
                            $obj.parents('tr').find('span.arrived_stock_count').html(datavalues['arrival_stock']);
                            $obj.parents('tr').find('span.requested_stock_count').append(' - '+datavalues['order_arrival_status']);
                            if($($obj).attr('data-page') == 'delivery_page'){
                                $('.wp-list-table .btnOrderFullyArrived[data-id="' + datavalues['row_id'] + '"]').parents('tr').next('tr').remove();
                                $('.wp-list-table .btnOrderFullyArrived[data-id="' + datavalues['row_id'] + '"]').parents('tr').remove();

                                var dname = $('select[name="supplier_id"] option:selected').data('name');
                                var dno = $('select[name="supplier_id"] option:selected').attr('data-no');
                                dno = dno - 1;
                                $('select[name="supplier_id"] option:selected').attr('data-no', dno);
                                $('select[name="supplier_id"] option:selected').html(dname + ' (' + dno + ')');

                                if ($('.wp-list-table tbody tr').length == 0) {
                                    $('.wp-list-table tbody').append(datavalues['default']);
                                }
                                //$('.order_product_files').replaceWith(datavalues['orders']);
                            }
                            else
                            {
                                $($obj).parents('.action').find('.arrived_qty_handler').addClass('hide');
                                $($obj).parents('.action').find('.btnOrderSave').addClass('hide');
                                $($obj).parents('.action').find('.btnOrderFullyArrived').addClass('hide');
                                $($obj).parents('.action').find('.btnFinalizeProduct').addClass('hide');                                
                            }
                        }
                        else{
                            $obj.parents('tr').find('span.arrived_stock_count').html(datavalues['arrival_stock']);
                        }
                        if(datavalues['update_all_product_txt']){
                            $('.order_status').text(datavalues['update_all_product_txt']);
                        }
                        $obj.parents('tr').find('span.items_in_stock').html(datavalues['update_stock']);
                        $obj.parents('tr').find('span.units_in_stock').html(datavalues['units_in_stock']);
                        $('.arrived_qty_handler').val('');
                        $($obj).parent('.action').removeClass('blockOverlay');

                        $('.btnRemoveProduct').remove();
                        //alert( datavalues['message'] );
                    } else {
                        $($obj).parent('.action').removeClass('blockOverlay');
                        alert(datavalues['message']);
                    }
                },
                error: function (data) {
                    $($obj).parent('.action').removeClass('blockOverlay');
                    alert(data);
                }
            });
            return false;
        },
        finalize_product_order: function(){
            var self = $(this);
            swal({
                text: oimwc_obj.finalize_product_notice,
                buttons: true,
                buttons: {
                    cancel: oimwc_obj.cancel_text,
                    finalize: {
                      text: 'Ok',
                      value: "finalize",
                    },
                }
            }).then((value) => {
                switch (value) {
                    case "finalize":
                        //self.parents('.action').find('.loader').show();
                        self.parent('.action').addClass('blockUI').addClass('blockOverlay');
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                'action' : 'finalize_product_order',
                                'id' : $(this).data('id'),
                                'product_id' : $(this).data('product_id')
                            },
                            method: 'POST',
                            success: function (data) {
                                datavalues = JSON.parse(data);
                                if($(self).attr('data-page') == 'delivery_page'){
                                    $('.wp-list-table .btnOrderFullyArrived[data-id="' + datavalues['row_id'] + '"]').parents('tr').next('tr').remove();
                                    $('.wp-list-table .btnOrderFullyArrived[data-id="' + datavalues['row_id'] + '"]').parents('tr').remove();

                                    var dname = $('select[name="supplier_id"] option:selected').data('name');
                                    var dno = $('select[name="supplier_id"] option:selected').attr('data-no');
                                    dno = dno - 1;
                                    $('select[name="supplier_id"] option:selected').attr('data-no', dno);
                                    $('select[name="supplier_id"] option:selected').html(dname + ' (' + dno + ')');

                                    if ($('.wp-list-table tbody tr').length == 0) {
                                        $('.wp-list-table tbody').append(datavalues['default']);
                                    }
                                }
                                else
                                {
                                    self.parents('.action').find('.arrived_qty_handler').addClass('hide');
                                    self.parents('.action').find('.btnOrderSave').addClass('hide');
                                    self.parents('.action').find('.btnOrderFullyArrived').addClass('hide');
                                    self.parents('.action').find('.btnFinalizeProduct').addClass('hide');
                                    self.parent('td').prev().find('.arrival_stock_txt').append('<strong> - '+datavalues['finalize_text']+'</strong>');
                                    self.parent('.action').removeClass('blockOverlay');

                                    if(datavalues['update_all_product_txt']){
                                        $('.order_status').text(datavalues['update_all_product_txt']);
                                    }
                                }
                                self.parent('.action').removeClass('blockOverlay');
                                $('.btnRemoveProduct').remove();
                            },
                            error: function (data) {
                                self.parent('.action').removeClass('blockOverlay');
                            }      
                       });
                      break;
                  }
            });

            return false;
        },
        remove_prod_frm_purchase_ordr: function(){
            var self = $(this);
            swal({
                text: oimwc_obj.remove_product_txt,
                buttons: true,
                buttons: {
                    cancel: oimwc_obj.cancel_text,
                    remove: {
                      text: 'Ok',
                      value: "remove",
                    },
                }
            }).then((value) => {
                switch (value) {
                    case "remove":
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                action:'remove_prod_frm_purchase_ordr',
                                id: $(this).attr('data-id'),
                                product_id: $(this).attr('data-product_id')
                            },
                            method: 'POST',
                            success: function (data) {
                                var data = JSON.parse(data);
                                if(data.success){
                                    $('.wp-list-table .btnRemoveProduct[data-id="' + data['id'] + '"]').parents('tr').next('tr').remove();
                                    $('.wp-list-table .btnRemoveProduct[data-id="' + data['id'] + '"]').parents('tr').remove();
                                    if(self.attr('data-page') == 'delivery_page'){
                                        var dname = $('select[name="supplier_id"] option:selected').data('name');
                                        var dno = $('select[name="supplier_id"] option:selected').attr('data-no');
                                        dno = dno - 1;
                                        $('select[name="supplier_id"] option:selected').attr('data-no', dno);
                                        $('select[name="supplier_id"] option:selected').html(dname + ' (' + dno + ')');   
                                    }

                                    if( $('.oimwc_red_bubble').length ){
                                        $('.oimwc_red_bubble').html(data.supplier_count);
                                    }
                                    else{
                                        if( data.supplier_count ){
                                            $('li#toplevel_page_order-inventory-management .wp-menu-name').append('<span class="oimwc_red_bubble awaiting-mod">'+data.supplier_count+'</span>');
                                            $('li#toplevel_page_order-inventory-management .wp-first-item a').append('<span class="oimwc_red_bubble awaiting-mod">'+data.supplier_count+'</span>');
                                        }
                                    }

                                    if($('.wp-list-table tbody tr').length == 0) {
                                        $('.wp-list-table tbody').append('<tr><td colspan="5">'+oimwc_obj.no_products+'</td></tr>');
                                    }
                                }
                            }
                        });
                    break;
                }
            });

            return false;
        },
        add_input_units: function () {
            var html = '<tr><td><input type="text" name="singular_unit[]" class="singular_unit" placeholder="'+oimwc_obj.singular_ex+'">\n\
                        </td><td><input type="text" name="plural_unit[]" class="plural_unit" placeholder="'+oimwc_obj.plural_ex+'">\n\
                        </td><td><a href="javascript:void(0);" class="unit_delete button button-primary button-large">'+oimwc_obj.cancel_text+'</a>\n\
                        </td></tr>';
            $("table#display_units").append(html);
            $(".unit_save").show();
        },
        unit_save: function () {
            var selected_option = $('#im_units').val();
            var singular_unit = $("input[name^=singular_unit]").map(function(){
                if($(this).val() != ""){
                    return $(this).val();
                }
            }).get();
            var plural_unit = $("input[name^=plural_unit]").map(function(){
                if($(this).val() != ""){
                    return $(this).val();
                }
            }).get();
            if(singular_unit && plural_unit ) {
                $.ajax({
                    url: ajaxurl,
                    data: {action:'save_unit_to_meta',singular:singular_unit,plural:plural_unit},
                    method: 'POST',
                    dataType: 'json',
                    success: function (data) {
                        if(data){
                            $("#im_units").find('option').remove();
                            $.each(data.units, function(key, value) {   
                                $("#im_units").append($("<option></option>").attr("value",key) .text(key+"/"+value)); 
                            });                             
                            alert(data.msg);
                            $("select option[value='"+selected_option+"']").attr("selected","selected");
                            $(".unit_delete").text(oimwc_obj.delete_text);
                        }
                    }
                });
            }
        },
        unit_delete: function () {
            var current_tr = $(this).closest('tr');
            var unit_key = $(this).closest('tr').find(".singular_unit").val();
            if(unit_key){
                if (confirm(oimwc_obj.confirm_msg))
                {
                    $.ajax({
                        url: ajaxurl,
                        data: {action:'delete_unit_from_meta',unit_key:unit_key},
                        method: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            if(data){
                                $(current_tr).remove();
                                $('#im_units option').each(function(){
                                    if (this.value == unit_key) {
                                        $("#im_units option[value='"+unit_key+"']").remove();
                                    }
                                });
                                if($("#display_units tr").length == 2){
                                    $(".unit_save").hide();
                                }
                            }
                        }
                    });
                }
            }else{
                $(current_tr).remove();
                if($("#display_units tr").length == 2){
                    $(".unit_save").hide();
                }
            }
        },
        get_total_products_to_update:function(){
            $.ajax({
                url: ajaxurl,
                data: {action:'get_product_data_to_update'},
                method: 'POST',
                success: function (response) {
                    if(response){
                        var data = JSON.parse(response);   
                        total_found_post = data.found_post;
                        if(total_found_post > 0){
                            $("#myProgress").show();
                            supplier_variation.add_default_data_to_existing_products();  
                        }else{
                            window.location.reload();
                        }
                    }
                }
            });
        },
        add_default_data_to_existing_products:function(){
            $.ajax({
                url: ajaxurl,
                data: {action:'add_default_data_to_existing_product'},
                method: 'POST',
                success: function (response) {
                    if(response){
                        var data = JSON.parse(response);
                        var elem = $("#myBar");
                        if(data.found_post != 0){
                            var percentage = 100 - Math.floor((data.found_post*100)/total_found_post);
                            elem.css("width",percentage+"%");
                            elem.html(percentage+"%");
                            supplier_variation.add_default_data_to_existing_products(); 
                        }else{
                            elem.css("width","100%");
                            elem.html("100%");
                            setTimeout(function(){ 
                                $("#myProgress").hide();
                                window.location.reload();
                            }, 1000);
                            
                        }
                    }
                }
            });
        },
        calculate_product_amount:function(){
            var qty = 0;
            if( parseInt( jQuery(this).val() ) > 0 ){
                 qty = jQuery(this).val();
                 jQuery(this).val( parseInt( qty ) );
            }
            var price = jQuery(this).parents('.amount').find('.product_calc .amount').data('price');
            if( qty < 0 || qty == '' ){
                jQuery(this).val( 0 );
                qty = 0;
            }
            jQuery(this).parents('.amount').find('.product_calc .amount').html( (qty * price).toFixed(2) );
            var total_amount = 0;
            $( '.product_listing_panel #the-list:eq(1) .product_calc .amount' ).each(function(){
                if($(this).html() != ''){
                    total_amount += parseFloat( $(this).html() );
                }
            });

            $('.total_order_info_panel:eq(1) .amount').html( total_amount.toFixed(2) );
            if(jQuery('.show_all_product').is(":checked") == true){
                $('#refresh_order').css('display','block');
            }else{
                $('#refresh_order').css('display','block');
            }
            $('#refresh_order .button').attr('disabled',false);
        },
        calculate_product_amount_order:function(){
            var qty = 0;
            var price = 0;
            if( parseInt( jQuery(this).val() ) > 0 ){
                 qty = jQuery(this).val();
                 jQuery(this).val( parseInt( qty ) );
            }
            var price = jQuery(this).parents('.amount').find('.product_calc .amount').data('price');
            if( qty < 0 || qty == '' ){
                jQuery(this).val( 0 );
                qty = 0;
            }
            jQuery(this).parents('.amount').find('.product_calc .amount').html( (qty * price).toFixed(2) );
            var total_amount = 0;
            $( '.product_listing_panel #the-list:first .product_calc .amount' ).each(function(){
                total_amount += parseFloat( $(this).html() );
            });
            $('.total_order_info_panel:first .amount').html( total_amount.toFixed(2) );
            if(jQuery('.show_all_product').is(":checked") == true){
                $('#refresh_order').css('display','block');
            }else{
                $('#refresh_order').css('display','block');
            }
            $('#refresh_order .button').attr('disabled',false);
            var qty = $(this).val();
            var id = $(this).parent().parent().parent().find('.productId').val();
            $('.product_listing_panel #the-list:eq(1) td.amount [data-id='+id+']').val(qty);
        },
        disable_low_stock_warning:function(){
            if(jQuery(this).is(':checked'))
            {
                $('#oimwc_show_in_low_stock').attr('checked',false);
                if($('.supplier_variable_checkbox').length > 0){
                    jQuery(this).parents('.woocommerce_variable_attributes').find('.supplier_variable_checkbox').attr('checked', false);

                    jQuery(this).parents('.show_if_variation_manage_supplier').find('.js_disabled_fld').addClass('disable_fields');
                }
                jQuery(this).parents('#suppliers_data_panel').find('.js_disabled_fld').addClass('disable_fields');
            }
            else{
                jQuery('.disable_fields').find('input').removeAttr('disabled');
                jQuery(this).parents('#suppliers_data_panel').find('.js_disabled_fld').removeClass('disable_fields');
                jQuery(this).parents('.show_if_variation_manage_supplier').find('.js_disabled_fld').removeClass('disable_fields');
            }
            
            jQuery(this).parent('p').removeClass('disable_fields');
            jQuery('.disable_fields').find('input').attr('disabled', 'disabled');

        },
        disabled_if_not_manage_stock:function(){
            if(!jQuery(this).is(':checked')){
                jQuery('.oimwc_low_stock_threshold_level_field, .oimwc_physical_stock_field, .oimwc_physical_units_stock_field').addClass('manage_disable_fields');
                jQuery('.manage_disable_fields').attr('data-msg',oimwc_obj.tooltip_msg);
            }
            else{
                jQuery('.oimwc_low_stock_threshold_level_field, .oimwc_physical_stock_field, .oimwc_physical_units_stock_field').removeClass('manage_disable_fields');
                jQuery('.manage_disable_fields').attr('data-msg','');
            }
        },
        disabled_if_not_manage_stock_variable:function(){

            var variable_field = jQuery(this).parents('.woocommerce_variable_attributes').find('.show_if_variation_manage_supplier').find('.form-field input[data-attr="if_no_manage_stock"]').parent('p');
            if(jQuery(this).is(':checked')){

                variable_field.removeClass('manage_disable_fields');
            }
            else
            {
                variable_field.addClass('manage_disable_fields');
                variable_field.attr('data-msg',oimwc_obj.tooltip_msg);
            }
        },
        initialize_arrival_date:function(){
            var supplier_id = jQuery(this).parents('.po_action_btns').attr('data-supplier_id');
            var order_date  = jQuery(this).parents('.po_action_btns').attr('data-order_date');
            jQuery(this).datepicker({
                dateFormat: oimwc_obj.order_date_format,
                changeMonth: true,
                changeYear: true,
                minDate: 0,
                onSelect: function (dateText, inst) {
                    $.ajax({
                        url: ajaxurl,
                        data: {
                            action:'save_arrival_date_callback',
                            arrival_date: jQuery(this).val(),
                            supplier_id: supplier_id,
                            order_date: order_date,
                        },
                        method: 'POST',
                        success: function (response) {
                            
                        }
                    });
                },                
            });
        },
        lock_awaiting_order:function(){
            var supplier_id = jQuery(this).parents('.po_action_btns').attr('data-supplier_id');
            var order_date  = jQuery(this).parents('.po_action_btns').attr('data-order_date');
            var parent_div = jQuery(this).parent('.po_action_btns');
            if(jQuery(this).find('i').hasClass('fa-lock')){

                parent_div.find('.arrival_date_cls').attr('disabled',true);
                parent_div.find('.arrival_date_cls').css('pointer-events','none');

                parent_div.find('.cancel_awaiting_order').attr('disabled',true);
                parent_div.find('.cancel_awaiting_order').css('pointer-events','none');
                
                jQuery(this).html('<i class="fa fa-unlock"></i>');
                jQuery(this).attr('data-lock',1);
                $(this).tipTip({ content: oimwc_obj.change_pending_status });
            }
            else{

                parent_div.find('.arrival_date_cls').attr('disabled',false);
                parent_div.find('.arrival_date_cls').css('pointer-events','');

                parent_div.find('.cancel_awaiting_order').attr('disabled',false);
                parent_div.find('.cancel_awaiting_order').css('pointer-events','');
                
                jQuery(this).html('<i class="fa fa-lock"></i>');  
                jQuery(this).attr('data-lock',0);
                $(this).tipTip({ content: oimwc_obj.change_ordered_status });
            }
            
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'lock_product_callback',
                    lock_product: jQuery(this).attr('data-lock'),
                    supplier_id: supplier_id,
                    order_date: order_date,
                },
                method: 'POST',
                success: function (response) {
                    
                }
            });
        },
        cancel_awaiting_order:function(){

            if(!confirm(oimwc_obj.cancel_order_msg)){
                return;
            }

            jQuery(this).parents('.po_action_btns').parent('tr').remove();
            
            var supplier_id = jQuery(this).parents('.po_action_btns').attr('data-supplier_id');
            var order_date  = jQuery(this).parents('.po_action_btns').attr('data-order_date');
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'cancel_awaiting_order_callback',
                    supplier_id: supplier_id,
                    order_date: order_date,
                },
                method: 'POST',
                success: function (response) {
                    var data = JSON.parse(response);
                    if( $('.oimwc_red_bubble').length ){
                        $('.oimwc_red_bubble').html(data.supplier_lowstock_count);
                    }
                    else{
                        if( data.supplier_lowstock_count ){
                            $('li#toplevel_page_order-inventory-management .wp-menu-name').append('<span class="oimwc_red_bubble awaiting-mod">'+data.supplier_lowstock_count+'</span>');
                            $('li#toplevel_page_order-inventory-management .wp-first-item a').append('<span class="oimwc_red_bubble awaiting-mod">'+data.supplier_lowstock_count+'</span>');
                        }
                    }
                    if(jQuery('.order_product_files table tbody tr').length == 0){
                        jQuery('.order_product_files table tbody').append('<tr><td colspan="8">No rows found.</td></tr>');
                        jQuery('#tiptip_holder').css('display','none');
                    }

                }
            });
        },
        add_info_to_po:function(){
            var dialog;
            var note ='';
            var self = $(this);
            var save_text_lbl = oimwc_obj.save_text;
            var cancel_text_lbl = oimwc_obj.cancel_text;
            var order_type = oimwc_obj.order_type;
            var disabled = false;
            if(order_type == 'finalize_orders'){
                disabled = true;
            }

            dialog = $( "#additional-info-dialog-form" ).dialog({
              autoOpen: false,
              height: 400,
              width: 350,
              modal: true,
              buttons: [{
                disabled: disabled,
                text: save_text_lbl,
                click: function() {
                    note = $('.additional_info_cls').val()
                    $.ajax({
                        url: ajaxurl,
                        data: {
                            action:'add_info_po_callback',
                            additional_info: note,
                            supplier_id: self.attr('data-supplier_id'),
                            order_date: self.attr('data-order_date'),
                        },
                        method: 'POST',
                        success: function (response) {
                            if(response){
                                var data = JSON.parse(response);
                                if(data.content){
                                    self.attr('data-add_info',note);
                                    self.addClass('additional_info_btn'); 
                                }
                                else{
                                    self.attr('data-add_info','');   
                                    self.removeClass('additional_info_btn'); 
                                }
                            }
                        }
                    });
                    dialog.dialog( "close" );
                }},{
                text: cancel_text_lbl,
                click: function() {
                  dialog.dialog( "close" );
                }
              }],
              close: function() {
              }
            });

            dialog.dialog( "open" );
            
            $( "#additional-info-dialog-form .additional_info_cls" ).val(self.attr('data-add_info'));
        },
        add_private_note:function(){
            var dialog;
            var self = jQuery(this);
            var supplier_id = jQuery(this).attr('data-supplier_id');
            var order_date  = jQuery(this).attr('data-order_date');
            var note = '';
            var save_text_lbl = oimwc_obj.save_text;
            var cancel_text_lbl = oimwc_obj.cancel_text;

            dialog = $( "#private-note-dialog-form" ).dialog({
              autoOpen: false,
              height: 400,
              width: 350,
              modal: true,
              buttons: [{
                text: save_text_lbl,
                click: function() {
                    note = jQuery('.private_note_cls').val();
                    $.ajax({
                        url: ajaxurl,
                        data: {
                            action:'add_private_note_callback',
                            private_note: note,
                            supplier_id:  supplier_id,
                            order_date: order_date
                        },
                        method: 'POST',
                        success: function (response) {
                            if(response){
                                var data = JSON.parse(response);
                                if(data.content){
                                    self.addClass('private_note_btn'); 
                                    self.attr('data-private_note',note);
                                }
                            }
                            else
                            {
                                self.removeClass('private_note_btn'); 
                                self.attr('data-private_note','');
                            }
                        }
                    });
                    dialog.dialog( "close" );
                }},{
                text: cancel_text_lbl,
                click: function() {
                  dialog.dialog( "close" );
                }
              }],
              close: function() {
              }
            });

            dialog.dialog( "open" );  
            $( "#private-note-dialog-form .private_note_cls" ).val(self.attr('data-private_note'));
        },
        show_hide_contact_form: function(){
            var radio_val = $('.support_subjects input[type="radio"]:checked').attr('id');
            if(radio_val == 'technical_support_radio' || radio_val == 'bug_radio'){
                $('.technical_support_content').show();
                $('.other_support_content').show();  
            }
            else
            {
                $('.other_support_content').hide();   
                $('.technical_support_content').show();
            }
        },
        show_hide_wp_details: function(){
            if (!$(this).parents().hasClass('closed')){
                $(this).parent('.expandable').addClass('closed');
                $('.wp_login_details').hide();
            }
            else
            {   
                $(this).parent('.expandable').removeClass('closed');
                $('.wp_login_details').show();
            }
        },
        validate_contact_form: function(e){
            e.preventDefault();
           
            $('#oimwc_contact_form').validate({ // initialize the plugin
                rules: {
                    contact_name: {
                        required: true
                    },
                    contact_email: {
                        required: true,
                        email: true
                    },
                    contact_domain: {
                        required: true
                    },
                    contact_summary: {
                        required: true
                    },
                    contact_message: {
                        required: true
                    },

                }
            });
            if(jQuery("#oimwc_contact_form").valid())
            {
                var formdata = $('#oimwc_contact_form').serialize();
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action:'submit_contact_form',
                        formdata: formdata
                    },
                    method: 'POST',
                    success: function (response) {
                        if(response){
                            var data = JSON.parse(response);
                            if(data.sent){
                                $('#contact_form .message-sent').show();
                                $('.oimwc_faq').hide();
                            }
                            else
                            {
                                $('#contact_form .message-not-sent').show();
                                $('.oimwc_faq').hide();
                            }
                            $('#contact_form #oimwc_contact_form').hide();
                        }
                    }
                });
            }
        },
        upgrade_notice_warning: function(){
            var upgrade_text = ''; 
            if(jQuery(this).hasClass('silver_version')){
                upgrade_text = oimwc_obj.silver_upgrade_text;
            }
            if(jQuery(this).hasClass('gold_version')){
                upgrade_text = oimwc_obj.gold_upgrade_text;
            }
            if(jQuery(this).hasClass('platinum_version')){
                upgrade_text = oimwc_obj.platinum_upgrade_text;
            }

            version_popup(upgrade_text);
            return false;
        },
        hide_custom_tooltip: function(){
            if(jQuery(this).parent('p').hasClass('manage_disable_fields')){
                jQuery(this).parent('p').addClass('manage_disable_fields_exist');
                jQuery(this).parent('p').removeClass('manage_disable_fields');
            }
            if(jQuery(this).parent('p').hasClass('free_disabled')){
                jQuery(this).parent('p').addClass('free_disabled_exist');
                jQuery(this).parent('p').removeClass('free_disabled');
            }
            if(jQuery(this).parent('p').hasClass('silver_disabled')){
                jQuery(this).parent('p').addClass('silver_disabled_exist');
                jQuery(this).parent('p').removeClass('silver_disabled');
            }
        },
        show_custom_tooltip: function(){
            if(jQuery(this).parent('p').hasClass('manage_disable_fields_exist')){
                jQuery(this).parent('p').addClass('manage_disable_fields');
                jQuery(this).parent('p').removeClass('manage_disable_fields_exist');
            }
            if(jQuery(this).parent('p').hasClass('free_disabled_exist')){
                jQuery(this).parent('p').addClass('free_disabled');
                jQuery(this).parent('p').removeClass('free_disabled_exist');
            }
            if(jQuery(this).parent('p').hasClass('silver_disabled_exist')){
                jQuery(this).parent('p').addClass('silver_disabled');
                jQuery(this).parent('p').removeClass('silver_disabled_exist');
            }
        },
        help_page_accordion: function(){
            var accordion = new Accordion($('#accordion'), false);
        },
        display_popup_on_download_po: function(){
            var dialog;
            var self = jQuery(this);
            var supplier_id= jQuery(this).parents('.po_action_btns').attr('data-supplier_id');
            var order_date  = jQuery(this).parents('.po_action_btns').attr('data-order_date');
            var download_po_file  = jQuery(this).parents('.po_action_btns').attr('data-download_pdf');
            var supplier_email = jQuery(this).parents('.po_action_btns').attr('data-supplier-email');
            var supplier_email_subject = jQuery(this).parents('.po_action_btns').attr('data-email-subject');
            var supplier_email_message = jQuery(this).parents('.po_action_btns').attr('data-email-message');
            var po_lang  = jQuery(this).parents('.po_action_btns').attr('data-default_po_lang');
            var po_attn  = jQuery(this).parents('.po_action_btns').attr('data-po_attn');
            var note = '';
            var download_text_lbl = oimwc_obj.download_text;
            var cancel_text_lbl = oimwc_obj.cancel_text;
            var back_text_lbl = oimwc_obj.back_text;
            dialog = $( "#manage-po-dialog-form" ).dialog({
              autoOpen: false,
              height: 400,
              width: 350,
              modal: true,
              dialogClass: 'download_po',
              buttons: [{
                text: download_text_lbl,
                click: function() {
                    var formdata = jQuery('#download_po_form').serialize();
                    var bool = false;
                    jQuery( ".manage_po_chks input[type=checkbox]" ).each(function() {
                        var ele = jQuery(this);
                        if( ele.is(":checked") ){
                            bool = true;
                        }
                    });
            
                    if( bool == true ){
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: formdata+"&action=save_po_file_settings&supplier_id="+supplier_id+"&order_date="+order_date,
                            success: function (response) {
                                if(response){
                                    var data = JSON.parse(response);
                                    if(data){
                                        var supplier_id = data.supplier_id;
                                        if(data.default_cols){
                                            var cols = JSON.stringify(data.default_cols);
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols',cols)
                                            .attr('data-default_po_lang',data.download_po_lang).attr('data-default_ship_address',data.default_ship_address);
                                        }else{
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols','')
                                            .attr('data-default_po_lang','').attr('data-default_ship_address','');
                                        }
                                        if(data.download_po_file){
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf',data.download_po_file);
                                        }else{
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf','');
                                        }
                                        if(data.save_flag == 1){
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf',data.download_po_file);
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols',cols)
                                            .attr('data-default_po_lang',data.download_po_lang).attr('data-default_ship_address',data.default_ship_address).attr('data-default_save_flag',1);
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms',data.shipping_terms);
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method',data.shipping_method);
                                        }else{
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf','');
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols','')
                                            .attr('data-default_po_lang','').attr('data-default_ship_address','').attr('data-default_save_flag',0);
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms','');
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method','');
                                        }
                                        if(data.delivery_date != ''){
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                        }else{
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                        }
                                        if(data.shipping_terms != ''){
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms',data.shipping_terms);
                                        }else{
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms','');
                                        }
                                        if(data.shipping_method != ''){
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method',data.shipping_method);
                                        }else{
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method','');
                                        }
                                        if(data.po_attn != ''){
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-po_attn',data.po_attn);
                                        }else{
                                            jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-po_attn','');
                                        }
                                        window.location = data.download_link;
                                    }
                                    else{
                                        self.parents('.po_action_btns')
                                        .attr('data-default_cols','')
                                        .attr('data-default_po_lang','')
                                        .attr('data-default_ship_address','')
                                        .attr('data-download_pdf','')
                                        .attr('data-delivery_date','')
                                        .attr('data-shipping_method','')
                                        .attr('data-shipping_terms','')
                                        .attr('data-po_attn','');
                                    }
                                }
                            }
                        });
                        dialog.dialog( "close" );
                    }
                    else{
                        if(bool == false){
                            jQuery( ".manage_po_chks input[type=checkbox]" ).addClass('error');
                        }
                    }
                    
                }},
                {
                    text: oimwc_obj.send_to_supplier_txt+'...',
                    click: function( e ) {
                        dialog.dialog( "close" );
                        var dialog_email;
                        var self = $(this);
                        var save_text_lbl = oimwc_obj.send_email_text;
                        var cancel_text_lbl = oimwc_obj.cancel_text;
                        jQuery('#send_po_order_email').val(supplier_email);
                        jQuery('#send_po_email_subject').val(supplier_email_subject);
                        jQuery('#send_po_order_email').removeClass('error-field');
                        jQuery('#reply_to_order_email').removeClass('error-field');
                        var regex = /<br\s*[\/]?>/gi;
                        jQuery('.send_po_order_cls').val(supplier_email_message.replace(regex, "\n"));
                        jQuery('.error').remove();
                        jQuery('.success').remove();
                        var formdata = jQuery('#download_po_form').serialize();
                        var bool = false;
                        jQuery( ".manage_po_chks input[type=checkbox]" ).each(function() {
                            var ele = jQuery(this);
                                if( ele.is(":checked") ){
                                    bool = true;
                                }
                        });
                        if(jQuery('#download_po_file').val() != download_po_file){
                            var result = jQuery('.attach_filename').text().split('.')[0] + '.' +jQuery('#download_po_file').val();
                            jQuery('.attach_filename').text(result);
                        }else{
                            var result = jQuery('.attach_filename').text().split('.')[0] + '.' +download_po_file;
                            jQuery('.attach_filename').text(result);
                        }
                        dialog_email = $( "#send-po-order-form" ).dialog({
                          autoOpen: false,
                          height: 575,
                          width: 680,
                          modal: true,
                          dialogClass: 'email-dialog',
                          buttons: [
                          {
                            text: back_text_lbl,
                            id: 'back-btn',
                            click: function() {
                                dialog.dialog( "open" );
                                dialog_email.dialog( "close" );       
                            }
                          },
                          {
                            text: save_text_lbl,
                            id: 'send-btn',
                            click: function() {
                                var po_order_subject = jQuery('#send_po_email_subject').val();
                                var po_order_message = jQuery('.send_po_order_cls').val();
                                var send_po_order_email = jQuery('#send_po_order_email').val();
                                var reply_to_order_email = jQuery('#reply_to_order_email').val();
                                var clone_div = jQuery('.send_po').clone();
                                jQuery(clone_div).insertBefore('.email-dialog .ui-dialog-buttonset button:first');
                                jQuery('.send_po').eq(1).show();
                                jQuery('.error').remove();
                                jQuery('.success').remove();
                                jQuery('.email-dialog .ui-dialog-buttonset button').eq(1).addClass('disable');
                                jQuery('#reply_to_order_email').removeClass('error-field');
                                jQuery('#send_po_order_email').removeClass('error-field');
                                if(supplier_id != '' && order_date !='' ){
                                    $.ajax({
                                        url: ajaxurl,
                                        data: formdata+"&action=download_po_document&supplier_id="+supplier_id+"&order_date="+order_date,
                                        method: 'POST',
                                        success: function(response,textStatus, xhr){
                                            var data = JSON.parse(response);
                                            $.get(data.download_link, function(data) {
                                                window.history.pushState('','',data.download_link);
                                            }).done(function(){
                                                if(send_po_order_email != '' && reply_to_order_email != ''){
                                                    $.ajax({
                                                        url: ajaxurl,
                                                        data: {
                                                            action:'send_po_order_email',
                                                            supplier_id: supplier_id,
                                                            po_order_subject : po_order_subject,
                                                            po_order_message : po_order_message,
                                                            send_po_order_email : send_po_order_email,
                                                            reply_to_order_email : reply_to_order_email
                                                        },
                                                        method: 'POST',
                                                        success: function(response,textStatus, xhr){
                                                            var data = JSON.parse(response);
                                                            if(data.sent == 1){
                                                                jQuery('.email-dialog .ui-dialog-buttonset').before('<p class="success">'+data.message+'</p>');
                                                                jQuery('.send_po').eq(1).hide();
                                                                jQuery('.email-dialog .ui-dialog-buttonset button').eq(1).removeClass('disable');
                                                                jQuery('#reply_to_order_email').removeClass('error-field');
                                                                jQuery('#send_po_order_email').removeClass('error-field');
                                                                jQuery('#back-btn').hide();
                                                                jQuery('#send-btn').hide();
                                                                jQuery('#cancel-btn').text(oimwc_obj.close_text);
                                                            }else if(data.sent == 'no'){
                                                                var interval = setInterval(function() {
                                                                    jQuery('.error').remove();
                                                                    jQuery('.success').remove();
                                                                    if(xhr.status == 200){
                                                                    $.ajax({
                                                                        url: ajaxurl,
                                                                        data: {
                                                                            action:'send_po_order_email',
                                                                            supplier_id: supplier_id,
                                                                            po_order_subject : po_order_subject,
                                                                            po_order_message : po_order_message,
                                                                            send_po_order_email : send_po_order_email,
                                                                            reply_to_order_email : reply_to_order_email
                                                                        },
                                                                    method: 'POST',
                                                                    success: function(response)
                                                                    {
                                                                        var data_response = JSON.parse(response);
                                                                        if(data_response.sent == 1){
                                                                            jQuery('.send_po').eq(1).hide();
                                                                            jQuery('.email-dialog .ui-dialog-buttonset').before('<p class="success">'+data_response.message+'</p>');
                                                                            jQuery('.email-dialog .ui-dialog-buttonset button').eq(1).removeClass('disable');
                                                                            jQuery('#reply_to_order_email').removeClass('error-field');
                                                                            jQuery('#send_po_order_email').removeClass('error-field');
                                                                            jQuery('#back-btn').hide();
                                                                            jQuery('#send-btn').hide();
                                                                            jQuery('#cancel-btn').text(oimwc_obj.close_text);
                                                                            if(data_response.sent == 1){
                                                                                clearInterval(interval);
                                                                            }
                                                                        }
                                                                        else if(data_response.sent == 'no'){ 
                                                                            jQuery('.send_po').eq(1).hide();
                                                                            jQuery('.email-dialog .ui-dialog-buttonset button').eq(1).removeClass('disable');
                                                                            jQuery('#reply_to_order_email').removeClass('error-field');
                                                                            jQuery('#send_po_order_email').removeClass('error-field');
                                                                            jQuery('#back-btn').hide();
                                                                            jQuery('#send-btn').hide();
                                                                            jQuery('#cancel-btn').text(oimwc_obj.close_text);
                                                                        }
                                                                        else{
                                                                            jQuery('.email-dialog .ui-dialog-buttonset').before('<p class="error">'+data_response.message+'</p>');  
                                                                            jQuery('.send_po').eq(1).hide();
                                                                            jQuery('.email-dialog .ui-dialog-buttonset button').eq(1).removeClass('disable');
                                                                            jQuery('#reply_to_order_email').removeClass('error-field');
                                                                            jQuery('#send_po_order_email').removeClass('error-field');
                                                                            jQuery('#back-btn').hide();
                                                                            jQuery('#send-btn').hide();
                                                                            jQuery('#cancel-btn').text(oimwc_obj.close_text);
                                                                        }
                                                                    }
                                                                });
                                                                }
                                                                }, 5000); 
                                                            }
                                                            else{
                                                                jQuery('.email-dialog .ui-dialog-buttonset').before('<p class="error">'+data.message+'</p>');  
                                                                jQuery('.send_po').eq(1).hide();
                                                                jQuery('.email-dialog .ui-dialog-buttonset button').eq(1).removeClass('disable');
                                                                jQuery('#reply_to_order_email').removeClass('error-field');
                                                                jQuery('#send_po_order_email').removeClass('error-field');
                                                                jQuery('#back-btn').hide();
                                                                jQuery('#send-btn').hide();
                                                                jQuery('#cancel-btn').text(oimwc_obj.close_text);
                                                            }
                                                        }
                                                    });
                                                }else{
                                                    if(send_po_order_email == ''){
                                                        jQuery('#send_po_order_email').addClass('error-field');
                                                    }
                                                    if(reply_to_order_email == ''){
                                                        jQuery('#reply_to_order_email').addClass('error-field');
                                                    }
                                                    jQuery('.send_po').eq(1).hide();
                                                    jQuery('.email-dialog .ui-dialog-buttonset button').eq(1).removeClass('disable');
                                                    return false;
                                                }
                                            });
                                            if( bool == true ){
                                            var data = JSON.parse(response);
                                            if(data){
                                                var supplier_id = data.supplier_id;
                                                if(data.default_cols){
                                                    var cols = JSON.stringify(data.default_cols);
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols',cols)
                                                    .attr('data-default_po_lang',data.download_po_lang).attr('data-default_ship_address',data.default_ship_address);
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols','')
                                                        .attr('data-default_po_lang','').attr('data-default_ship_address','');
                                                }
                                                if(data.download_po_file){
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf',data.download_po_file);
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf','');
                                                }
                                                if(data.save_flag == 1){
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf',data.download_po_file);
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols',cols)
                                                    .attr('data-default_po_lang',data.download_po_lang).attr('data-default_ship_address',data.default_ship_address).attr('data-default_save_flag',1);
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms',data.shipping_terms);
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method',data.shipping_method);
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-download_pdf','');
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-default_cols','')
                                                    .attr('data-default_po_lang','').attr('data-default_ship_address','').attr('data-default_save_flag',0);
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms','');
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method','');
                                                    }
                                                if(data.delivery_date != ''){
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-delivery_date','');
                                                }
                                                if(data.shipping_terms != ''){
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms',data.shipping_terms);
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_terms','');
                                                }
                                                if(data.shipping_method != ''){
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method',data.shipping_method);
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-shipping_method','');
                                                }
                                                if(data.po_attn != ''){
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-po_attn',data.po_attn);
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-po_attn','');
                                                }
                                                if(data.po_attn != ''){
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-po_attn',data.po_attn);
                                                }else{
                                                    jQuery('td[data-supplier_id="'+supplier_id+'"],div[data-supplier_id="'+supplier_id+'"]').attr('data-po_attn','');
                                                }
                                            }
                                            else{
                                                    self.parents('.po_action_btns')
                                                    .attr('data-default_cols','')
                                                    .attr('data-default_po_lang','')
                                                    .attr('data-default_ship_address','')
                                                    .attr('data-download_pdf','')
                                                    .attr('data-delivery_date','')
                                                    .attr('data-shipping_terms','')
                                                    .attr('data-shipping_method','')
                                                    .attr('data-po_attn','');
                                                }
                                            }   
                                        }
                                    });
                                }
                            }},{
                            text: cancel_text_lbl,
                            id: 'cancel-btn',
                            click: function() {
                              dialog_email.dialog( "close" );
                            }
                          }],
                          close: function() {
                          }
                        });
                        dialog_email.dialog( "open" );
                    }
                },
                {
                text: cancel_text_lbl,
                click: function() {
                  dialog.dialog( "close" );
                }
              }],
              close: function() {
              }
            });

            dialog.dialog( "open" );  
            var d_cols = self.parents('.po_action_btns').attr('data-default_cols');
            var lang, ship_address;
            var default_view = true;
            if(d_cols != ''){
                d_cols = JSON.parse(d_cols);
                if(d_cols){
                    default_view = false;
                    jQuery( ".manage_po_chks input[type=checkbox]" ).each(function() {
                        var ele = jQuery(this);
                        var chk = ele.val();
                        if( jQuery.inArray(chk,d_cols) !== -1 ){
                            ele.prop('checked',true);
                        }
                        else{
                            ele.prop('checked',false); 
                        }
                    });
                    jQuery('#save_default_sett_chk').attr('checked',true);
                    jQuery( ".manage_po_chks input[type=checkbox]" ).removeClass('error');
                    lang = self.parents('.po_action_btns').attr('data-default_po_lang');
                    $("#manage_po_lang_dd option").each(function(){
                        if ($(this).val() == lang)
                        $(this).prop('selected','selected');
                    });
                    ship_address = self.parents('.po_action_btns').attr('data-default_ship_address');
                    if(ship_address != ''){
                        $("#select_shipping_address option").each(function(){
                            if ($(this).val() == ship_address)
                            $(this).prop('selected','selected');
                        });
                    }
                }
            }else{
                jQuery( ".manage_po_chks input[type=checkbox]" ).each(function() {
                    var ele = jQuery(this);
                    ele.prop('checked',false); 
                });
            }
            var download_cols = self.parents('.po_action_btns').attr('data-download_pdf');
            if(download_cols != ''){
                default_view = false;
                $("#download_po_file option").each(function(){
                    if ($(this).val() == download_cols)
                    $(this).prop('selected','selected');
                });
            }else{
                jQuery("#download_po_file").val(jQuery("#download_po_file option:first").val());
            }
            var save_checkbox = self.parents('.po_action_btns').attr('data-default_save_flag');
            if(save_checkbox != ''){
                default_view = false;
                $("#save_default_sett_chk").each(function(){
                    if (save_checkbox == 1){
                        $(this).prop('checked',true);
                    }else{
                        $(this).prop('checked',false);
                    }
                });
            }else{
                jQuery('#save_default_sett_chk').attr('checked',false);
                jQuery( ".manage_po_chks input[type=checkbox]" ).each(function() {
                    var ele = jQuery(this);
                    ele.prop('checked',false); 
                });
                jQuery("#manage_po_lang_dd").val(jQuery("#manage_po_lang_dd option:first").val());
                jQuery("#select_shipping_address").val(jQuery("#select_shipping_address option:first").val());
                jQuery("#download_po_file").val(jQuery("#download_po_file option:first").val());
                jQuery("#delivery_date").val('');
                jQuery("#shipping_method").val('');
                jQuery('#shipping_terms').val('');
                jQuery('#po_attn').val('');
            }
            $('#delivery_date').datepicker({
                dateFormat: oimwc_obj.order_date_format,
                changeMonth: true,
                changeYear: true,
                minDate: 0             
            });
            var delivery_date = self.parents('.po_action_btns').attr('data-delivery_date');
            if(delivery_date != ''){
                default_view = false;
                $("#delivery_date").val('');
            }else{
                jQuery("#delivery_date").val('');
            }
            var shipping_method = self.parents('.po_action_btns').attr('data-shipping_method');
            if(shipping_method != ''){
                default_view = false;
                $("#shipping_method").val(shipping_method);
            }else{
                jQuery("#shipping_method").val('');
            }
            var shipping_terms = self.parents('.po_action_btns').attr('data-shipping_terms');
            if(shipping_terms != ''){
                default_view = false;
                $("#shipping_terms").val(shipping_terms);
            }else{
                jQuery('#shipping_terms').val('');
            }
            var po_attn = self.parents('.po_action_btns').attr('data-po_attn');
            if(po_attn != ''){
                default_view = false;
                $("#po_attn").val(po_attn);
            }else{
                jQuery('#po_attn').val('');
            }
            var attach_filename = self.parents('.po_action_btns').attr('data-file_name');
            var file_extension = self.parents('.po_action_btns').attr('data-download_pdf');
            if(attach_filename != ''){
                default_view = false;
                jQuery('.attach_filename').text(attach_filename+'.'+file_extension);
            }else{
                jQuery('.attach_filename').text('');
            }
            if( default_view ){
                jQuery( ".manage_po_chks input[type=checkbox]" ).attr('checked',false);
                jQuery( ".manage_po_chks input[type=checkbox]" ).removeClass('error');
                jQuery( ".manage_po_chks_download input[type=checkbox]" ).removeClass('error');
                jQuery('#save_default_sett_chk').attr('checked',false);
                jQuery("#manage_po_lang_dd").val(jQuery("#manage_po_lang_dd option:first").val());
                jQuery("#select_shipping_address").val(jQuery("#select_shipping_address option:first").val());
                jQuery("#download_po_file").val(jQuery("#download_po_file option:first").val());
                jQuery("#delivery_date").val('');
                jQuery("#shipping_method").val('');
                jQuery('#shipping_terms').val('');
                jQuery('#po_attn').val('');
            }
        },
        view_order_page_cancel_order:function(){

            if(!confirm(oimwc_obj.cancel_order_msg)){
                return;
            }

            var supplier_id = jQuery(this).parents('.po_action_btns').attr('data-supplier_id');
            var order_date  = jQuery(this).parents('.po_action_btns').attr('data-order_date');
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'cancel_awaiting_order_callback',
                    supplier_id: supplier_id,
                    order_date: order_date,
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        var data = JSON.parse(response);
                        if(data){
                            window.location = data.redirect_url;
                        }
                    }

                }
            });
        },
        save_inventory_settings:function(){

            $('#im_settings_form .lw_spin').show();

            jQuery('.company_shipping_address_panel .accordion-inner').each(function(){
                var position = jQuery(this).index();
                jQuery(this).find('input, select').each(function(){
                var name = jQuery(this).data('name');
                jQuery(this).attr('name', 'shipping_address[address_'+ position + '][' + name +']' );
                });
            });

            jQuery('.company_office_address_panel .accordion-inner').each(function(){
                var position = jQuery(this).index();
                jQuery(this).find('input, select').each(function(){
                var name = jQuery(this).data('name');
                jQuery(this).attr('name', 'company_address[address_'+ position + '][' + name +']' );
                });
            });

            var formdata = jQuery('#im_settings_form').serialize();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: formdata,
                success: function (response) {
                    var data = JSON.parse(response);
                    if(data){
                        $('#im_settings_form .lw_spin').hide();
                        $('.success_icon').html('');
                        $('.success_icon').show();
                        if(data.success){
                            $('.success_icon').html('<h3 style="color:green;"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i> '+data.msg+'</h3>');
                            setTimeout(function() { $(".success_icon").remove('slow'); }, 2000);
                        }else{
                            $('.success_icon').html('<h3 style="color:red;"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i> '+data.msg+'</h3>');
                            setTimeout(function() { $(".success_icon").remove('slow'); }, 2000);
                        }
                        if(jQuery('input[name="disable_oimwc_gtin"]').is(':checked') == true){
                            jQuery('input[name="show_gtin_number"]').removeAttr('checked');
                        }
                    }
                },
                error: function(response){

                }
            });
        },
        custom_search_supplier: function(e){
            e.preventDefault();
            //var search_val = $('.post-type-supplier #post-search-input').val();
            var search_val = $('.post-type-supplier .sticky_header_supplier #post-search-input').val();
            
            var template = wp.template( "supplier_table" );
            //if(search_val){
            var body = $("html, body");
            body.stop().animate({scrollTop:0}, 500, 'swing', function() {
            });
            $('.post-type-supplier.oimwc_sticky_header').addClass('oimwc_overlay');
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'custom_search_supplier',
                    search_val: search_val
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        $('.post-type-supplier.oimwc_sticky_header').removeClass('oimwc_overlay');
                        var parse_data = JSON.parse(response);
                        $('.post-type-supplier #the-list').html('');
                        _.each(parse_data, function (value, key) {
                            var data = template(value);
                            $('.post-type-supplier #the-list').append(data);
                        });
                        if(jQuery('.post-type-supplier .table-view-list #the-list tr').length == 0){
                            jQuery('.post-type-supplier .table-view-list #the-list').append('<tr class="no-items"><td colspan="4">'+oimwc_obj.no_supplier +'</td></tr>');
                        }
                    }

                }
            });
            
            //}
        },
        custom_sort_supplier: function(e){
            e.preventDefault();
            var orderby = $(this).parent('th').attr('id');
            var self = $(this);
            var order;
            if( self.parent('th').hasClass('desc') ){
                order = 'desc';
            }
            else
            {
                order = 'asc';
            }

            var template = wp.template( "supplier_table" );

            self.parent('th').prepend('<span class="spinner sort_spin"></span>');

            $.ajax({
                url: ajaxurl,
                data: {
                    action:'custom_sort_supplier',
                    orderby: orderby,
                    order: order,
                    post_type: 'supplier'
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        var parse_data = JSON.parse(response);
                        $('.post-type-supplier #the-list').html('');
                        _.each(parse_data, function (value, key) {
                            var data = template(value);
                            $('.post-type-supplier #the-list').append(data);
                        });
                        
                        if( self.parent('th').hasClass('desc') ){
                            self.parent('th').removeClass('desc');
                            self.parent('th').addClass('asc');
                        }
                        else
                        {
                            self.parent('th').removeClass('asc');
                            self.parent('th').addClass('desc');
                        }

                        $('.sort_spin').remove();
                    }

                }
            });
        },
        get_current_stock_values: function(){
            $('.stock_values_spinner.lw_spin').show();
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'oimwc_get_current_stock_values'
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        var data = JSON.parse(response);
                        if(data){
                            $.each(data,function(index,value){
                                
                                jQuery( 'div.stock_values_spinner[data-curr="' + index + '"]' ).html(value);
                            });
                        }
                    }

                }
            });
        },
        low_stock_products_filter: function(e){
            e.preventDefault();
            $('#frm_product_handler input').removeClass('error');
            var subpage = $('.supplier_filter_panel form input[name="subpage"]').val();
            if( subpage == 'delivery_table' ){
                var template = wp.template( "awaiting_delivery_tbl" );
            }else{
                var template = wp.template( "lowstock_supplier_filter" );
            }
            var supplier_id = $(this).val(); 
            $('.search_lw_prod_panel input').val('');
            $('.supplier_filter_panel .lw_spin').show();
            
            var body = $("html, body");
            body.stop().animate({scrollTop:0}, 500, 'swing', function() { 
            });
            
            $('.inventory_management_panel.blockUI .IO_spin').show();
            $('.inventory_management_panel.blockUI').addClass('oimwc_overlay');
            if( $(this).val() != 'all' ){
                $('.total_order_info_panel').show();
                $('.total_order_info_panel .amount').html(0.00);
                $('.total_order_info_panel .currency').html($('#IO_supplier_filter option:selected').attr('data-curr'));
            }
            else{
                $('.total_order_info_panel').hide();   
            }
            if(jQuery('.show_all_product').is(":checked")){
                var show_all_product = 1;
                var supplier_id = $('#IO_supplier_filter').val(); 
                if(jQuery('.purchase_order_table #the-list tr').length){
                    $('.purchase_order_table').show();
                }
            }else{
                show_all_product = 0;
                supplier_id = $('#IO_supplier_filter').val(); 
                if(jQuery('.purchase_order_table #the-list tr').length){
                    $('.purchase_order_table').show();
                }
            }
            jQuery('.load_lowstock_msg').css('display','none');
            $.ajax({
                url: oimwc_obj.prism_ajax_url,
                data: {
                    action:'load_low_stock_products',
                    supplier_id: supplier_id,
                    subpage: subpage,
                    show_all_product: show_all_product
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        $('#refresh_order .button').attr('disabled',true);
                        $('.inventory_management_panel.blockUI').removeClass('oimwc_overlay');
                        $('.inventory_management_panel.blockUI .IO_spin').hide();

                        var parse_data = response.data.records;
                        var total_pages = response.data.total_pages;
                        var order_data = response.data.data_order;

                        $('.product_listing_panel #the-list').html('');
                        $('.product_listing_panel').data('pagination','');  
                        $('.low_stock_page').val(1);
                        $('#lw_total_pages').val(total_pages);  
                        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);  
                        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');                    

                        if($('.show_all_product').is(":checked")){
                            purchase_order_products_response(order_data,template,supplier_id);    
                            low_stock_products_response(parse_data,template,supplier_id);
                            jQuery('.purchase_order_table #the-list tr').each(function(){
                                if($(this).hasClass('no-items')){
                                    $('.purchase_order_table').show();
                                }else{
                                    $('.purchase_order_table').show();
                                }
                            });
                            if(jQuery('.purchase_order_table #the-list tr').length && !jQuery('.purchase_order_table #the-list tr').hasClass('no-items')){
                                $('.purchase_order_table').show();
                            }else{
                                $('.purchase_order_table').show();
                            }        
                            if( supplier_id == 'all' ){
                                $('#refresh_order').hide();
                            }else{
                                $('#refresh_order').show();
                                jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
                                jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
                            }  
                            $('.wp-list-table #the-list:eq(1) tr').each(function(){
                                if($('.wp-list-table #the-list:eq(1) tr:visible').length == 0){
                                    $('.product_listing_panel #the-list:eq(1)').append('<tr class="no-items"><td colspan="5">'+oimwc_obj.no_products +'</td></tr>');
                                } 
                            });                     
                        }else{
                            purchase_order_products_response(order_data,template,supplier_id);    
                            low_stock_products_response(parse_data,template,supplier_id);
                            jQuery('.purchase_order_table #the-list tr').each(function(){
                                if($(this).hasClass('no-items')){
                                    $('.purchase_order_table').show();
                                }else{
                                    $('.purchase_order_table').show();
                                }
                            });
                            if(jQuery('.purchase_order_table #the-list tr').length && !jQuery('.purchase_order_table #the-list tr').hasClass('no-items')){
                                $('.purchase_order_table').show();
                            }else{
                                $('.purchase_order_table').show();
                            }
                            if(jQuery('.purchase_order_table:first #the-list tr').hasClass('no-items')){
                                $('.purchase_order_table:first #the-list tr').html('<td colspan="5">'+oimwc_obj.no_preview_data_msg +'</td>');
                            } 
                            if( supplier_id == 'all' ){
                                $('#refresh_order').hide();
                            }else{
                                $('#refresh_order').show();
                                jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
                                jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
                            } 
                            $('.wp-list-table #the-list:eq(1) tr').each(function(){
                                if($('.wp-list-table #the-list:eq(1) tr:visible').length == 0){
                                    $('.product_listing_panel #the-list:eq(1)').append('<tr class="no-items"><td colspan="5">'+oimwc_obj.no_products +'</td></tr>');
                                } 
                            });                             
                        }

                        if( subpage != 'delivery_table' ){
                            $('#frm_product_handler input[name="product_sku"]').attr('disabled',false);
                            $('#frm_product_handler input[type="submit"]').removeClass('disabled');
                            $('#frm_product_handler input[name="supplier_id"]').val(supplier_id);
                            $('.IO_main_panel .create_PO_cls').removeClass('disabled');
                            $('#btn_create_product').removeClass('disabled');

                            if( supplier_id == 'all' ){
                                jQuery('.show_product_info_handler').css('display','none');
                                $('#frm_product_handler input[name="product_sku"]').attr('disabled',true);
                                $('.IO_main_panel .create_PO_cls').addClass('disabled');
                                $('#frm_product_handler input[type="submit"]').addClass('disabled');
                                $('.purchase_order_table').hide();
                                if($('.wp-list-table #the-list:eq(1) tr:visible').length == 0){
                                    $('.product_listing_panel #the-list:eq(1)').append('<tr class="no-items"><td colspan="5">'+oimwc_obj.no_products +'</td></tr>');
                                } 
                                $('#refresh_order').hide();
                                $('#btn_create_product').addClass('disabled');
                            }else{
                                jQuery('.show_product_info_handler').css('display','block');
                                if( supplier_id == 'all' ){
                                    $('#refresh_order').hide();
                                }else{
                                    $('#refresh_order').show();
                                }
                            }
                            if(jQuery('.show_all_product').is(":checked") == true)
                            { 
                                if(supplier_id == 'all'){
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',true);
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').addClass('disabled');
                                    var total_products = 0;
                                    jQuery('#IO_supplier_filter option').each(function(){
                                        if( jQuery(this).attr( 'value' ) != 'all' ){
                                            total_products += parseInt( jQuery(this).attr('data-all_pro') );
                                        }
                                        var supplier_id = $('#IO_supplier_filter').val();
                                        var name = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('name');
                                        var count = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('all_pro');
                                        jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').html( name + ' ('+ count +')' );
                                    });
                                    jQuery('#IO_supplier_filter option[value="all"]').html( jQuery('#IO_supplier_filter option[value="all"]').data( 'name' ) + ' ('+total_products+')' ); 
                                    $('.purchase_order_table').hide();
                                    jQuery('.show_product_info_handler').css('display','none');
                                    jQuery('#refresh_order').css('display','none');
                                    if($('.wp-list-table #the-list:eq(1) tr').hasClass('no-items')){
                                        $('.product_listing_panel #the-list:eq(1) .no-items').html('<td colspan="5">'+oimwc_obj.load_low_stock_msg +'</td>');
                                    } 
                                    $('.product_listing_panel .purchase_order_table:first').after('<h3 class="load_lowstock_msg"><i class="fas fa-arrow-up"></i>'+oimwc_obj.load_low_stock_msg+'</h3>'); 
                                    $('.load_lowstock_msg:eq(1)').remove();
                                }else{
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
                                    var total_products = 0;
                                    jQuery('#IO_supplier_filter option').each(function(){
                                        if( jQuery(this).attr( 'value' ) != 'all' ){
                                            total_products += parseInt( jQuery(this).attr('data-all_pro') );
                                        }
                                        var supplier_id = $(this).val();
                                        var name = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('name');
                                        var count = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('all_pro');
                                        jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').html( name + ' ('+ count +')' );
                                    });
                                    jQuery('#IO_supplier_filter option[value="all"]').html( jQuery('#IO_supplier_filter option[value="all"]').data( 'name' ) + ' ('+total_products+')' );
                                    jQuery('.show_product_info_handler').css('display','block');
                                    jQuery('#refresh_order').css('display','block');
                                }
                            }else{
                                if( supplier_id == 'all' ){
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',true);
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').addClass('disabled');
                                    $('#refresh_order').hide();
                                    var total_products = 0;
                                    jQuery('#IO_supplier_filter option').each(function(){
                                        if( jQuery(this).attr( 'value' ) != 'all' ){
                                            total_products += parseInt( jQuery(this).attr('data-no') );
                                        }
                                        var supplier_id = $('#IO_supplier_filter').val();
                                        var name = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('name');
                                        var count = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('no');
                                        jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').attr('data-no',count);
                                        jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').html( name + ' ('+ count +')' );
                                    });
                                    jQuery('#IO_supplier_filter option[value="all"]').html( jQuery('#IO_supplier_filter option[value="all"]').data( 'name' ) + ' ('+total_products+')' ); 
                                }else{
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
                                    jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
                                    $('#refresh_order').show();
                                    var total_products = 0;
                                    jQuery('#IO_supplier_filter option').each(function(){
                                        if( jQuery(this).attr( 'value' ) != 'all' ){
                                            total_products += parseInt( jQuery(this).attr('data-no') );
                                        }
                                        var supplier_id = $(this).val();
                                        var name = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('name');
                                        var count = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('no');
                                        jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').attr('data-no',count);
                                        jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').html( name + ' ('+ count +')' );
                                    });
                                    jQuery('#IO_supplier_filter option[value="all"]').html( jQuery('#IO_supplier_filter option[value="all"]').data( 'name' ) + ' ('+total_products+')' ); 
                                }
                            }
                            if(jQuery('.show_all_product').is(":checked") == true){
                                $('#frm_product_handler input[name="product_sku"]').attr('disabled',true);
                                $('#frm_product_handler input[type="submit"]').addClass('disabled');
                            }
                        }else{
                            $('.purchase_order_table').hide();
                            var total_products = 0;
                            jQuery('#IO_supplier_filter option').each(function(){
                                if( jQuery(this).attr( 'value' ) != 'all' ){
                                    total_products += parseInt( jQuery(this).attr('data-no') );
                                }
                            });
                            jQuery('#IO_supplier_filter option[value="all"]').html( jQuery('#IO_supplier_filter option[value="all"]').data( 'name' ) + ' ('+total_products+')' );
                            var name = jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').data('name');
                            var count = response.data.total_supplier_product_low_stock;
                            jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').attr('data-no', count );
                            jQuery('#IO_supplier_filter option[value="'+ supplier_id +'"]').html( name + ' ('+ count +')' );
                        }
                        $('.purchase_orders_table').hide();
                        $('.supplier_filter_panel .lw_spin').hide();

                        $('.total_order_info_panel:eq(1) .amount').html( 0.00 );
                    }

                }
            });
            
        },
        load_low_stock_products: function(){
            var subpage = $('.supplier_filter_panel form input[name="subpage"]').val();
            if(subpage == 'delivery_table'){
                if( !$('.product_listing_panel').length || $('.product_listing_panel').data('pagination') == 'complete' ){
                    return;
                }
                if ($(window).scrollTop() > $(document).height() - $(window).height() - 100 ){
                    load_low_stock_products_func();
                }
            }else{
                if( !$('.purchase_order_table:eq(1)').length || !$('.product_listing_panel').length || $('.product_listing_panel').data('pagination') == 'complete' ){
                    return;
                }
                if($('#IO_supplier_filter').val() != 'all'){
                    load_low_stock_products_func();
                }
            }
        },
        search_low_stock_products: function(e){
            if( $(this).find('.disabled').length ){
                swal(oimwc_obj.select_supplier_for_search);
                return false;
            }
            e.preventDefault();
            var search_val = $('.search_lw_prod_panel input[name="search_lw_txt"]').val();
            var subpage = $('.supplier_filter_panel form input[name="subpage"]').val();
            if( subpage == 'delivery_table' ){
                var template = wp.template( "awaiting_delivery_tbl" );
            }else{
                var template = wp.template( "lowstock_supplier_filter" );
            }
            var supplier_id = jQuery('#IO_supplier_filter').val();

            $('.lw_search_spin').show();
            var body = $("html, body");
            body.stop().animate({scrollTop:0}, 500, 'swing', function() { 
            });
            $('.inventory_management_panel.blockUI .IO_spin').show();
            $('.inventory_management_panel.blockUI').addClass('oimwc_overlay');
            if(jQuery('.show_all_product').is(":checked")){
                var show_all_product = 1;
                var supplier_id = $('#IO_supplier_filter').val(); 
            }else{
                show_all_product = 0;
                supplier_id = $('#IO_supplier_filter').val(); 
                $('.purchase_order_table').show();
            }
            $.ajax({
                url: oimwc_obj.prism_ajax_url,
                data: {
                    action:'load_low_stock_products',
                    search_val: search_val,
                    supplier_id: supplier_id,
                    subpage: subpage,
                    show_all_product: show_all_product
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        //var parse_data = JSON.parse(response);
                        $('.inventory_management_panel.blockUI').removeClass('oimwc_overlay');
                        $('.inventory_management_panel.blockUI .IO_spin').hide();
                        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
                        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
                        
                        var parse_data = response.data.records;
                        var total_pages = response.data.total_pages;
                        var order_data = response.data.data_order;
                        $('.lw_search_spin').hide();
                        
                        jQuery('.product_listing_panel #the-list').html('');
                        jQuery('.low_stock_page').val(1);
                        $('#lw_total_pages').val(total_pages);
                        $('.product_listing_panel').data('pagination',''); 
                        $('.load_lowstock_msg').hide();
                        if($('.show_all_product').is(":checked")){
                            purchase_order_products_response(order_data,template,supplier_id);    
                            low_stock_products_response(parse_data,template,supplier_id);
                            $('.purchase_order_table').show();
                        }else{
                            purchase_order_products_response(order_data,template,supplier_id);    
                            low_stock_products_response(parse_data,template,supplier_id);
                            $('.purchase_order_table').show();
                        }
                        jQuery('.wp-list-table #the-list:eq(1) tr').each(function(){
                            var productId = $(this).find('.productId').val();
                            var keywords = $(this).find('.purchase_order_data').text();
                            var order_data = keywords.split(',');
                            $.each(order_data, function(i, keyword){
                                if(productId == order_data[i]){
                                    $('.product_'+productId).closest('tr').hide();
                                    $('.product_'+productId).closest('tr').next('tr').hide();
                                }
                            });
                        });
                    }
                }
            });
            
        },
        load_completed_purchase_orders: function(){
            if( !$('.order_product_files').length || $('.order_product_files').data('pagination') == 'complete' ){
                return;
            }
            if ($(window).scrollTop() > $(document).height() - $(window).height() - 100 ){
                if( jQuery('.order_product_files .lw_spin').css( 'display' )  == 'block' ){
                    return;
                }
                //var supplier_id = jQuery('#IO_supplier_filter').val();
                var order_type = $('#po_subpage').val();
                var template = wp.template( "completed_po" );
                var nextPage = parseInt(jQuery('#completed_po_page').val()) + 1;
                var total_pages = parseInt(jQuery('#completed_po_total_pages').val());
                if( nextPage <= total_pages){
                    jQuery('.order_product_files .lw_spin').show();
                }
                else{
                    return;
                }
                jQuery.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'load_completed_purchase_orders',
                        page: nextPage,
                        order_type: order_type
                    },
                    method: 'POST',
                    success: function (response) {
                        jQuery('#completed_po_page').val(nextPage);
                        if(response){
                            jQuery('.order_product_files .lw_spin').hide();
                            var parse_data = JSON.parse(response);
                            if(parse_data.length == 0){
                                jQuery('.order_product_files').data('pagination','complete');  
                                return; 
                            }
                            var decodeHTML = function (html) {
                                var txt = document.createElement('textarea');
                                txt.innerHTML = html;
                                return txt.value;
                            };
                             _.each(parse_data, function (value, key) {
                            var data = template(value);
                                $('.order_product_files #the-list').append( decodeHTML(data) );
                            });
                        }
                        else{
                            jQuery('.order_product_files').data('pagination','complete');  
                        }
                    },
                    error: function (response){

                    }
                });                               
            }
        },
        load_view_purchase_orders_products: function(){
            if( !$('.view_order_product_listing_panel').length || $('.view_order_product_listing_panel').data('pagination') == 'complete' || pagination_start ){
                return;
            }
            if ($(window).scrollTop() > $(document).height() - $(window).height() - 100 ){
                if( jQuery('.view_po_spin.lw_spin').css( 'display' )  == 'block' ){
                    return;
                }
                pagination_start = true;
                var template = wp.template( "view_purchase_order" );
                if( $('#frm_product_handler').length ){
                    var supplier = jQuery('#frm_product_handler input[name="supplier"]').val();
                    var date = jQuery('#frm_product_handler input[name="date"]').val();
                }else{
                    var supplier = jQuery('#view_po_supplier').val();
                    var date = jQuery('#view_po_date').val();
                }
                
                var nextPage = parseInt(jQuery('#view_po_page').val()) + 1;
                var total_pages = parseInt(jQuery('#view_po_total_pages').val());
                if( nextPage <= total_pages){
                    jQuery('.view_po_spin.lw_spin').show();
                }
                else{
                    pagination_start = false;
                    return;
                }
                jQuery.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'load_view_purchase_orders_products',
                        supplier: supplier,
                        date: date,
                        page: nextPage,
                        //offset: jQuery('.table_seperator').length
                    },
                    method: 'POST',
                    success: function (response) {
                        jQuery('#view_po_page').val(nextPage);
                        if(response){
                            jQuery('.view_po_spin.lw_spin').hide();
                            var parse_data = JSON.parse(response);
                            if(parse_data.length == 0){
                                jQuery('.view_order_product_listing_panel').data('pagination','complete');  
                                return; 
                            }
                            var decodeHTML = function (html) {
                                var txt = document.createElement('textarea');
                                txt.innerHTML = html;
                                return txt.value;
                            };
                             _.each(parse_data, function (value, key) {
                            var data = template(value);
                                $('.view_order_product_listing_panel #the-list').append( decodeHTML(data) );
                            });
                            jQuery( '.tips' ).tipTip({ 'attribute': 'data-tip', 'fadeIn': 50, 'fadeOut': 50, 'delay': 50 });
                        }
                        else{
                            jQuery('.view_order_product_listing_panel').data('pagination','complete');  
                        }
                        pagination_start = false;
                    },
                    error: function (response){
                        pagination_start = false;
                    }
                });                               
            }
        },
        purchase_orders_supplier_filter: function(){

            var supplier_id = $(this).val();
            var order_type = $('#po_subpage').val();
            var template = wp.template( "completed_po" );
            $('.po_supplier_filter_panel .lw_spin').show();
            $('.purchase_orders_wrap .IO_spin').show();
            $('.purchase_orders_wrap').addClass('oimwc_overlay');

            jQuery.ajax({
                url: ajaxurl,
                data: {
                    action: 'load_completed_purchase_orders',
                    supplier_id: supplier_id,
                    order_type: order_type
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        jQuery('.po_supplier_filter_panel .lw_spin').hide();
                        $('.purchase_orders_wrap').removeClass('oimwc_overlay');
                        $('.purchase_orders_wrap .IO_spin').hide();
                        var parse_data = JSON.parse(response);
                        $('.order_product_files #the-list').html('');
                        var decodeHTML = function (html) {
                            var txt = document.createElement('textarea');
                            txt.innerHTML = html;
                            return txt.value;
                        };
                         _.each(parse_data, function (value, key) {
                        var data = template(value);
                            $('.order_product_files #the-list').append( decodeHTML(data) );
                        });
                        var supplier_id = $('#po_supplier_filter').val();
                        if(supplier_id != ''){
                            var order_data = $('.order_product_files .po_action_btns').attr('data-default_cols');
                            var map_data = order_data.split(',').map(word => `"${word.trim()}"`).join(',');
                            $('.order_product_files .po_action_btns').attr('data-default_cols','['+map_data+']');
                        }else{
                            $('.order_product_files .po_action_btns').each(function(){
                                var order_data = $(this).attr('data-default_cols');
                                var map_data = order_data.split(',').map(word => `"${word.trim()}"`).join(',');
                                $(this).attr('data-default_cols','['+map_data+']');
                            });
                        }
                        
                        jQuery( '.tips' ).tipTip({ 'attribute': 'data-tip', 'fadeIn': 50, 'fadeOut': 50, 'delay': 50 });
                    }
                },
                error: function (response){

                }
            }); 
        },
        add_multiple_shipping_address: function(e){
            e.preventDefault();
            if( $(this).hasClass('gold_version') && $(this).hasClass('upgrade_notice_cls') ){
                return;
            }

            var count = $('.company_shipping_address').length;

            var countries = oimwc_obj.countries_dropdown;
            var countries_str = '';
            $.each(countries, function(index,value){
                countries_str += '<option value="'+index+'">'+value+'</option>'; 
            });
            $('.company_shipping_address_panel').append('<div class="accordion-inner"><div class="link"><i class="fa fa-chevron-right"></i><a href="#" class="button button-primary delete_add_btn" data-table="company_shipping_address_'+ count +'">'+oimwc_obj.delete_address+'</a><input type="text" data-name="title" placeholder="'+oimwc_obj.address_name+'"></div><table id="company_shipping_address_'+ count +'" class="company_shipping_address submenu"><tr><td>'+oimwc_obj.receiver+'</td><td><input type="text" data-name="im_receiver" id="im_receiver"></td></tr><tr><td>'+oimwc_obj.contact_person+'</td><td><input type="text" data-name="im_contact" id="im_contact"></td></tr><tr><td>'+oimwc_obj.address_1+'</td><td><input type="text" data-name="im_address1" id="im_address1"></td></tr><tr><td>'+oimwc_obj.address_2+'</td><td><input type="text" data-name="im_address2" id="im_address2"></td></tr><tr><td>'+oimwc_obj.city+'</td><td><input type="text" id="im_city" data-name="im_city"></td></tr><tr><td>'+oimwc_obj.state+'</td><td><input type="text" data-name="im_state" id="im_state"></td></tr><tr><td>'+oimwc_obj.zip_code+'</td><td><input type="text" data-name="im_zip_code" id="im_zip_code"></td></tr><tr><td>'+oimwc_obj.country+'</td><td><select data-name="im_country" id="im_country"><option value="">'+oimwc_obj.select_country+'</option>'+ countries_str+'</select></td></tr><tr><td>'+oimwc_obj.phone_number+'</td><td><input type="text" data-name="im_phone" id="im_phone"></td></tr></table></div>');
            var accordion = new Accordion($('.company_shipping_address_panel.accordion'), false);            
        },
        delete_shipping_address: function(e){
            e.preventDefault();
            if( $(this).hasClass('gold_version') && $(this).hasClass('upgrade_notice_cls') ){
                return;
            }
            swal({
                text: oimwc_obj.delete_address_msg,
                buttons: true,
                buttons: {
                    cancel: oimwc_obj.cancel_text,
                    upgrade: {
                      text: 'Ok',
                      value: "delete",
                    },
                }
            }).then((value) => {
                switch (value) {
                    case "delete":
                        $(this).parents('.accordion-inner').remove();
                        supplier_variation.save_inventory_settings();
                        return;
                    break;
                }
            });
        },
        custom_sort_PO_columns: function(e){
            e.preventDefault();

            var orderby = $(this).parent('th').attr('id');
            var order_type = $('#po_subpage').val();
            var supplier_id = $('#po_supplier_filter').val();
            var self = $(this);
            var order;
            if( self.parent('th').hasClass('desc') ){
                order = 'desc';
            }
            else
            {
                order = 'asc';
            }

            var template = wp.template( "completed_po" );

            self.append('<span class="spinner sort_spin"></span>');

            $.ajax({
                url: ajaxurl,
                data: {
                    action:'load_completed_purchase_orders',
                    orderby: orderby,
                    order: order,
                    order_type: order_type,
                    supplier_id: supplier_id
                },
                method: 'POST',
                success: function (response) {
                    if(response){
                        var parse_data = JSON.parse(response);
                        $('.order_product_files #the-list').html('');
                        var decodeHTML = function (html) {
                            var txt = document.createElement('textarea');
                            txt.innerHTML = html;
                            return txt.value;
                        };
                         _.each(parse_data, function (value, key) {
                        var data = template(value);
                            $('.order_product_files #the-list').append( decodeHTML(data) );
                        });
                        
                        if( self.parent('th').hasClass('desc') ){
                            self.parent('th').removeClass('desc');
                            self.parent('th').addClass('asc');
                        }
                        else
                        {
                            self.parent('th').removeClass('asc');
                            self.parent('th').addClass('desc');
                        }

                        $('.sort_spin').remove();
                    }

                }
            });
        },
        add_additional_supplier: function(){
            
            var select_ele = $(this).parents('.select_supplier_div').find('select').children("option:selected");
            if( select_ele.val() == 0 ){
                return;
            }
            var supplier_count = $(this).parents('.select_supplier_div').next().find('.supplier_accordion').find('div.accordion-inner').length;
            if( parseInt( oimwc_obj.manage_multi_supplier ) == 0 && parseInt( supplier_count ) > 0 ){
                version_popup( oimwc_obj.gold_upgrade_text );
                return false;
            }
            var supplier_name = select_ele.text();
            select_ele.attr('disabled','disabled');
            var supplier_id = select_ele.val();
            var key = $('.supplier_accordion .accordion-inner').length;

            var template = wp.template( "additional_supplier" );
            var tmplData = {
                supplier_id : supplier_id,
                supplier_name : supplier_name,
                key : key,
                remove_txt : oimwc_obj.remove_txt,
                supplier_prod_id : oimwc_obj.supplier_prod_id,
                supplier_prod_url : oimwc_obj.supplier_prod_url,
                product_notes : oimwc_obj.product_notes,
                purchase_price : oimwc_obj.purchase_price,
                pack_size : oimwc_obj.pack_size,
                currency : $('input[name=oimwc_currency]').val()
            };

            var result = template(tmplData);     
            $(this).parents('.select_supplier_div').next().find('.supplier_accordion').append(result);
            var accordion = new Accordion($('.supplier_accordion_panel .accordion'), false);
            var select_first_opt = $(this).parents('.select_supplier_div').find('select option:first').val();
            $(this).parents('.select_supplier_div').find('select').val(select_first_opt);

            supplier_variation.update_supplier_name();
        },
        save_additional_supplier: function(e){
            e.preventDefault();

            jQuery('.supplier_accordion .accordion-inner').each(function(){
                var position = jQuery(this).index();
                jQuery(this).find('input').each(function(){
                    var name = jQuery(this).data('name');
                    if( name != undefined ){
                        jQuery(this).attr('name', 'additional_suppliers['+ position + '][' + name +']' );
                    }
                });
            });

            var formdata = $('#post').serialize();
            $('#suppliers_data_panel .spinner').addClass('is-active');
            
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'save_additional_supplier',
                    formdata: formdata,
                },
                method: 'POST',
                success: function (response) {
                    $('#suppliers_data_panel .spinner').removeClass('is-active');
                },
                error: function (response){

                }
            });
        },
        save_supplier_info: function(e){
            e.preventDefault();
            $('.supplier_btn .spinner').addClass('is-active');
            var oimwc_supplier_id = jQuery(this).attr('data-id');
            var supplier_index = jQuery(this).attr('data-index');
            if(jQuery('#pid_var').length){
                product_id = jQuery(this).parent().parent().parent().parent().parent().parent().parent().find('#pid_var').val();
            }
            else{
                product_id = jQuery('#pid').val();
            }

            if(jQuery(this).parent().parent().find('input[data-name="product_id"]').length){
                oimwc_supplier_product_id = jQuery(this).parent().parent().find('input[data-name="product_id"]').val();
                oimwc_supplier_product_url = jQuery(this).parent().parent().find('input[data-name="product_url"]').val();
                oimwc_supplier_note = jQuery(this).parent().parent().find('input[data-name="supplier_note"]').val();
                oimwc_supplier_purchase_price = jQuery(this).parent().parent().find('input[data-name="purchase_price"]').val();
                oimwc_supplier_pack_size = jQuery(this).parent().parent().find('input[data-name="pack_size"]').val();
            }
            else{
                oimwc_supplier_product_id = jQuery(this).parent().parent().find('input[name="oimwc_supplier_product_id"]').val();
                oimwc_supplier_product_url = jQuery(this).parent().parent().find('input[name="oimwc_supplier_product_url"]').val();
                oimwc_supplier_note = jQuery(this).parent().parent().find('input[name="oimwc_supplier_note"]').val();
                oimwc_supplier_purchase_price = jQuery(this).parent().parent().find('input[name="oimwc_supplier_purchase_price"]').val();
                oimwc_supplier_pack_size = jQuery(this).parent().parent().find('input[name="oimwc_supplier_pack_size"]').val();
            }

            $.ajax({
                url: ajaxurl,
                data: {
                    action:'save_supplier_info',
                    oimwc_supplier_id: oimwc_supplier_id,
                    supplier_index: supplier_index,
                    product_id: product_id,
                    oimwc_supplier_product_id: oimwc_supplier_product_id,
                    oimwc_supplier_product_url: oimwc_supplier_product_url,
                    oimwc_supplier_note: oimwc_supplier_note,
                    oimwc_supplier_purchase_price: oimwc_supplier_purchase_price,
                    oimwc_supplier_pack_size: oimwc_supplier_pack_size
                },
                method: 'POST',
                success: function (response) {
                    $('.supplier_btn .spinner').removeClass('is-active');
                }
            });
        },
        remove_supplier: function(e){
            e.preventDefault();
            if( !confirm('Are you sure you want to remove it?') ){
                return;
            }
            var supplier_id = $(this).parents('.accordion-inner').attr('data-supplier_id');
            jQuery(this).parents('.supplier_accordion_panel').prev().find('select option[value='+supplier_id+']').attr('disabled',false);
            $(this).parents('.woocommerce_variation').addClass('variation-needs-update');
            $('.save-variation-changes').attr('disabled',false);
            $(this).parents('.accordion-inner').remove();
            supplier_variation.update_supplier_name();
        },
        update_supplier_name: function(e){
            var count = 0;
            jQuery('.supplier_accordion .accordion-inner').each(function(){
                var position = jQuery(this).index();
                var varition_id = -1;
                if( jQuery(this).parents('.supplier_accordion_panel').hasClass('variable') ){
                    var suppliers_variable_data_panel_id = jQuery( this ).parents( '.show_if_variation_manage_supplier' ).attr( 'id' );
                    varition_id = suppliers_variable_data_panel_id.replace( 'suppliers_variable_data_panel_', '' );
                }
                jQuery(this).find('input').each(function(){
                    var name = jQuery(this).data('name');
                    if( name != undefined ){
                        if( varition_id >= 0 ){
                            jQuery(this).attr('name', 'additional_variable_suppliers['+varition_id+']['+ position + '][' + name +']' );
                            jQuery(this).attr('id', 'additional_variable_suppliers_'+varition_id+'_'+ position + '_' + name  );
                        }else{
                            jQuery(this).attr('name', 'additional_suppliers['+ position + '][' + name +']' );    
                        }
                    }
                });
                count++;
            });
        },
        variation_default_save_changes: function(e){
            
            if( jQuery(this).hasClass('do_submit') ){
                return true;
            }
            /*jQuery('.supplier_accordion .accordion-inner').each(function(){
                var position = jQuery(this).index();
                jQuery(this).find('input').each(function(){
                    var name = jQuery(this).data('name');
                    if( name != undefined ){
                        var varition_id = jQuery(this).parents('.woocommerce_variation').find('.remove_variation').attr('rel');
                        jQuery(this).attr('name', 'additional_suppliers['+varition_id+']['+ position + '][' + name +']' );
                    }
                });
                
            });*/
            
            jQuery(this).addClass('do_submit');
            window.setTimeout(function(){
                jQuery( '.save-variation-changes' ).click();
            }, 1000);
            return false;
        },
        disable_physical_stock_function: function () {
            $('#oimwc_physical_units_stock,#oimwc_our_pack_size,#_stock').attr('readonly',true);
            $('.oimwc_physical_units_stock_field,.oimwc_our_pack_size_field,._stock_field').addClass('manage_disable_fields');
            $('.oimwc_physical_units_stock_field,.oimwc_our_pack_size_field,._stock_field').attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
            var oimwc_physical_stock = jQuery('#oimwc_physical_stock').val();
            var oimwc_physical_stock_prev = jQuery('#oimwc_physical_stock').attr('data-name');
            if(oimwc_physical_stock == oimwc_physical_stock_prev )
            {
                $('.oimwc_physical_units_stock_field,.oimwc_our_pack_size_field,._stock_field').removeClass('manage_disable_fields');
                $('#oimwc_physical_units_stock,#oimwc_our_pack_size,#_stock').attr('readonly',false);
            }
        },
        disable_physical_stock_function_var: function()
        {
            if($('.variable_tab_data').hasClass('show_if_variation_manage_supplier'))
            {
                $('.oimwc_physical_stock_variation').each(function() {
                    $(this).on("keyup change",function(){
                        var id = $(this).attr('id').match(/\d+/);
                        $('#oimwc_physical_units_stock_'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_our_pack_size_'+id).parent().addClass('manage_disable_fields');
                        $('#variable_stock'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_physical_units_stock_'+id,'#oimwc_our_pack_size_'+id,'#variable_stock'+id).attr('readonly',true);
                        $('#oimwc_physical_units_stock_'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#oimwc_our_pack_size_'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#variable_stock'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        var oimwc_physical_stock_var = jQuery(this).val();
                        var oimwc_physical_stock_prev_var = jQuery(this).attr('data-name');
                        if(oimwc_physical_stock_var == oimwc_physical_stock_prev_var )
                        {
                            $('#oimwc_physical_units_stock_'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_our_pack_size_'+id).parent().removeClass('manage_disable_fields');
                            $('#variable_stock'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_physical_units_stock_'+id,'#oimwc_our_pack_size_'+id,'#variable_stock'+id).attr('readonly',false);
                        }
                    });
                });
            }
        },
        disable_unit_stock_function: function () {
            $('#oimwc_physical_stock,#oimwc_our_pack_size,#_stock').attr('readonly',true);
            $('.oimwc_physical_stock_field,.oimwc_our_pack_size_field,._stock_field').addClass('manage_disable_fields');
            $('.oimwc_physical_stock_field,.oimwc_our_pack_size_field,._stock_field').attr('data-msg',  oimwc_obj.disabled_field_on_stock_changed );
            var oimwc_physical_units_stock = jQuery('#oimwc_physical_units_stock').val();
            var oimwc_physical_units_stock_prev = jQuery('#oimwc_physical_units_stock').attr('data-name');
            if(oimwc_physical_units_stock == oimwc_physical_units_stock_prev || oimwc_physical_stock_prev_var == '')
            {
                $('.oimwc_physical_stock_field,.oimwc_our_pack_size_field,._stock_field').removeClass('manage_disable_fields');
                $('#oimwc_physical_stock,#_stock,#oimwc_our_pack_size').attr('readonly',false);
            }
        },
        disable_unit_stock_function_var: function()
        {
            if($('.variable_tab_data').hasClass('show_if_variation_manage_supplier'))
            {
                $('.oimwc_physical_units_stock_variation').each(function() {
                    $(this).on("keyup change",function(){
                        var id = $(this).attr('id').split('_').pop();
                        $('#oimwc_physical_stock'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_our_pack_size_'+id).parent().addClass('manage_disable_fields');
                        $('#variable_stock'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_physical_stock'+id,'#oimwc_our_pack_size_'+id,'#variable_stock'+id).attr('readonly',true);
                        $('#oimwc_physical_stock'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#oimwc_our_pack_size_'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#variable_stock'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        var oimwc_physical_stock_var = jQuery(this).val();
                        var oimwc_physical_stock_prev_var = jQuery(this).attr('data-name');
                        if(oimwc_physical_stock_var == oimwc_physical_stock_prev_var || oimwc_physical_stock_prev_var == '' )
                        {
                            $('#oimwc_physical_stock'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_our_pack_size_'+id).parent().removeClass('manage_disable_fields');
                            $('#variable_stock'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_physical_stock'+id,'#oimwc_our_pack_size_'+id,'#variable_stock'+id).attr('readonly',false);
                        }
                    });
                });
            }
        },
        disable_pack_size_function: function () {
            $('#oimwc_physical_stock,#oimwc_physical_units_stock,#_stock').attr('readonly',true);
            $('.oimwc_physical_stock_field,.oimwc_physical_units_stock_field,._stock_field').addClass('manage_disable_fields');
            $('.oimwc_physical_stock_field,.oimwc_physical_units_stock_field,._stock_field').attr('data-msg',  oimwc_obj.disabled_field_on_stock_changed );
            var oimwc_our_pack_size = jQuery('#oimwc_our_pack_size').val();
            var oimwc_our_pack_size_prev = jQuery('#oimwc_our_pack_size').attr('data-name');
            if(oimwc_our_pack_size == oimwc_our_pack_size_prev )
            {
                $('.oimwc_physical_stock_field,.oimwc_physical_units_stock_field,._stock_field').removeClass('manage_disable_fields');
                $('#oimwc_physical_stock,#oimwc_physical_units_stock,#_stock').attr('readonly',false);
                $('#restrict_pack_size_notice').hide();
            }else{
                $('#restrict_pack_size_notice').show();
            }
        },
        disable_pack_size_function_var: function()
        {
            if($('.variable_tab_data').hasClass('show_if_variation_manage_supplier'))
            {
                $('.oimwc_our_pack_size_variation').each(function() {
                    $(this).on("keyup change",function(){
                        var id = $(this).attr('id').split('_').pop();
                        $('#oimwc_physical_stock'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_physical_units_stock_'+id).parent().addClass('manage_disable_fields');
                        $('#variable_stock'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_physical_stock'+id,'#oimwc_physical_units_stock_'+id,'#variable_stock'+id).attr('readonly',true);
                        $('#oimwc_physical_stock'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#oimwc_physical_units_stock_'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#variable_stock'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        var oimwc_physical_stock_var = jQuery(this).val();
                        var oimwc_physical_stock_prev_var = jQuery(this).attr('data-name');
                        if(oimwc_physical_stock_var == oimwc_physical_stock_prev_var || oimwc_physical_stock_prev_var == '' )
                        {
                            $('#oimwc_physical_stock'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_physical_units_stock_'+id).parent().removeClass('manage_disable_fields');
                            $('#variable_stock'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_physical_stock'+id,'#oimwc_physical_units_stock_'+id,'#variable_stock'+id).attr('readonly',false);
                            $('#restrict_pack_size_notice'+id).hide();
                        }else{
                            $('#restrict_pack_size_notice'+id).show();
                        }
                    });
                });
            }
        },
        disable_stock_function: function () {
            $('#oimwc_physical_stock,#oimwc_our_pack_size,#oimwc_physical_units_stock').attr('readonly',true);
            $('.oimwc_physical_stock_field,.oimwc_physical_units_stock_field,.oimwc_our_pack_size_field').addClass('manage_disable_fields');
            $('.oimwc_physical_stock_field,.oimwc_physical_units_stock_field,.oimwc_our_pack_size_field').attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
            var stock = jQuery('#_stock').val();
            var stock_prev = jQuery('input[name=_original_stock]').val();
            if(stock == stock_prev )
            {
                $('.oimwc_physical_stock_field,.oimwc_our_pack_size_field,.oimwc_physical_units_stock_field').removeClass('manage_disable_fields');
                $('#oimwc_physical_stock,#oimwc_our_pack_size,#oimwc_physical_units_stock').attr('readonly',false);
            }
        },
        disable_stock_function_var: function()
        {
            if($('.variable_tab_data').hasClass('show_if_variation_manage_supplier'))
            {
                $('.variable_stock_variation').each(function() {
                    $(this).on("keyup change",function(){
                        var id = $(this).attr('id').match(/\d+/);
                        $('#oimwc_physical_stock'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_physical_units_stock_'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_our_pack_size_'+id).parent().addClass('manage_disable_fields');
                        $('#oimwc_physical_stock'+id,'#oimwc_physical_units_stock_'+id,'#oimwc_our_pack_size_'+id).attr('readonly',true);
                        $('#oimwc_physical_stock'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#oimwc_physical_units_stock_'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        $('#oimwc_our_pack_size_'+id).parent().attr('data-msg', oimwc_obj.disabled_field_on_stock_changed );
                        var oimwc_physical_stock_var = jQuery(this).val();
                        var oimwc_physical_stock_prev_var = jQuery('input[name="variable_original_stock['+id+']"]').val();
                        if(oimwc_physical_stock_var == oimwc_physical_stock_prev_var || oimwc_physical_stock_prev_var == '')
                        {
                            $('#oimwc_physical_stock'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_physical_units_stock_'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_our_pack_size_'+id).parent().removeClass('manage_disable_fields');
                            $('#oimwc_physical_stock'+id,'#oimwc_physical_units_stock_'+id,'#oimwc_our_pack_size_'+id).attr('readonly',false);
                        }
                    });
                });
            }
        },
        allow_only_numbers: function( e ){
            var a = [];
            var k = e.which;
            var id = $( e.target ).attr( 'id' );
            if( ! id.includes( 'oimwc_physical_stock' ) &&  
                ! id.includes( 'oimwc_physical_units_stock' ) &&  
                ! id.includes( 'oimwc_our_pack_size' ) &&  
                ! id.includes( '_stock' ) &&  
                ! id.includes( 'variable_stock' ) &&
                ! $( e.target ).hasClass( 'oimwc_restrict_characters_on_paste' ) &&
                ! $( e.target ).hasClass( 'oimwc_allow_only_numbers' ) ){
                return;
            }
            /*for (i = 48; i < 58; i++)
                a.push(i);
            
            if (!(a.indexOf(k)>=0))
                e.preventDefault();*/
            e = e || window.event;
            var charCode = (typeof e.which == "number") ? e.which : e.keyCode;

            // Allow non-printable keys
            if (!charCode || charCode == 8 /* Backspace */ ) {
                return;
            }
            var pattern = /^(-?)\d+$/;
            var typedChar = String.fromCharCode(charCode);
            // Allow numeric characters
            if (pattern.test(typedChar)) {
                return;
            }
            // Allow the minus sign (-) if the user enters it first
            if ( ( id == '_stock' || id.includes( 'variable_stock' ) || id == 'oimwc_low_stock_threshold_level' ) || id.includes( 'oimwc_low_stock_threshold_level' ) && typedChar == "-" && $(this).val() == "") {
                return;
            }
            if ( ( id == '_stock' || id.includes( 'variable_stock' ) ) && typedChar == "-" && $(this).val() != "") {
                $(this).val( '' );
                return;
            }
            // In all other cases, suppress the event
            return false;
        },
        restrict_characters_on_paste: function( e ){
            var str = $(this).val();
            var dec = str.indexOf(".");
            var first_char = str.charAt(0);
            var isNumber = true;
            var haveDecimal = false;
            var id = $( this ).attr( 'id' );
            if( !str ){
                return;
            }
            if (first_char == '-' || !isNaN(first_char)) {
                    for (i=1; i<str.length; i++) {
                            if(isNaN(str.charAt(i)) && str.charAt(i) != '.') {
                                isNumber = false;
                            break;
                        }
                }
            }
            else {
                    isNumber = false;
            }

            if(dec != -1 && isNumber) {
                str = str.substring(0, str.indexOf("."));
            }
            if (isNumber) {
                $(this).val(str);
            }else{
                $(this).val("");
            }
            console.log(id);
            if ( id != '_stock' && !id.includes( 'variable_stock' ) && first_char == '-' ){
                $(this).val("");   
            } 
        },
        wrong_stock_data_call: function( e ){
            e.preventDefault();
            $( '.download_info span.loader' ).show();
            $( '#download_stock' ).attr( 'disabled', 'disabled' );
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'download_wrong_stock_data',
                    nonce: $( this ).data( 'nonce' )
                },
                method: 'POST',
                success: function ( response ) {
                    console.log( response );
                    $( '.download_info span.loader' ).hide();
                    $( '#download_stock' ).removeAttr( 'disabled' );
                    if(response.success){
                        $('<a href="' + $('#download_stock').attr('href') + '&file_name=' + response.data.file_name + '"></a>')[0].click();
                    }
                },
                error: function (response){
                    $( '.download_info span.loader' ).hide();
                    $( '#download_stock' ).removeAttr( 'disabled' );
                }
            });
        },
        view_wrong_product_panel: function( e ){
            e.preventDefault();
            $('#view_products').dialog({ dialogClass: 'product_popup',title: oimwc_obj.wrong_data_label,height: 400,width: 600,modal: true});
        },
        ignore_rating_function: function( e ){
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'oimwc_plugin_rating_ignore_notice'
                },
                method: 'POST',
                beforeSend: function(){
                    jQuery('.notice-reminder').css('opacity','0.5');
                    jQuery('.notice-reminder').css('pointer-events','none');
                },
                success: function ( response ) {
                    jQuery('.notice-reminder').remove();
                }
            });
        },
        view_wrong_order_panel: function( e ){
            e.preventDefault();
            $('#view_orders').dialog({ dialogClass: 'order_popup',title: oimwc_obj.wrong_order_label,height: 400,width: 600,modal: true});
        },
        update_oimwc_data: function( e ){
            e.preventDefault();
            var formdata = $('#post').serialize();
            $('.update_btn_data .spinner').addClass('is-active');
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'update_oimwc_data',
                    formdata: formdata
                },
                method: 'POST',
                success: function (response) {
                    $('.manage_disable_fields input').removeAttr( 'readonly', false );
                    $('.manage_disable_fields').removeClass( 'manage_disable_fields' );
                    $('#oimwc_physical_stock').attr( 'value', response.data.physical_stock )
                    .attr( 'data-name', response.data.physical_stock )
                    .data( 'name', response.data.physical_stock )
                    .val( response.data.physical_stock );
                    $('#oimwc_physical_units_stock').attr( 'value', response.data.physical_units_stock )
                    .attr( 'data-name', response.data.physical_units_stock )
                    .data( 'name', response.data.physical_units_stock )
                    .val( response.data.physical_units_stock );
                    $('#oimwc_our_pack_size').attr( 'value', response.data.our_pack_size )
                    .attr( 'data-name', response.data.our_pack_size )
                    .data( 'name', response.data.our_pack_size )
                    .val( response.data.our_pack_size );
                    $('#_stock').attr( 'value', response.data.stock )
                    .attr( 'data-name', response.data.stock )
                    .data( 'name', response.data.stock )
                    .val( response.data.stock );
                    $('.order_stock_display_panel').replaceWith( response.data.stock_history );
                    $('.update_btn_data .spinner').removeClass('is-active');
                }
            });
        },
        update_oimwc_data_variable: function( e ){
            e.preventDefault();
            var formdata = $('#post').serialize();
            $('.update_btn_data .spinner').addClass('is-active');
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'update_oimwc_data_variable',
                    formdata: formdata
                },
                method: 'POST',
                success: function (response) {
                    $('.update_btn_data .spinner').removeClass('is-active');
                }
            });
        },
        upload_pdf_logo: function() {
            var button = jQuery(this),
            custom_uploader = wp.media({
                library : {
                    type : 'image'
                },
                multiple: false
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                if(attachment.sizes.thumbnail === undefined){
                    var height = '150';
                }else{
                    var height = attachment.sizes.thumbnail['height'];
                }
                button.parent().prepend('<img src="' + attachment.url + '" width="auto" height="'+height+'" />');
                jQuery('.pdf_logo').val(attachment.id);
            }).open();
            jQuery(this).hide();
            jQuery('.remove_img').show();
        },
        remove_pdf_logo: function() {
            var button = $(this);
            button.next().val('');
            button.hide();
            jQuery('.inner_div img').remove();
            jQuery('.upload_img').show();
        },
        add_multiple_office_address: function(e){
            e.preventDefault();
            var count = $('.office_shipping_address').length;

            var countries = oimwc_obj.countries_dropdown;
            var countries_str = '';
            $.each(countries, function(index,value){
                countries_str += '<option value="'+index+'">'+value+'</option>'; 
            });
            $('.company_office_address_panel').append('<div class="accordion-inner"><div class="link"><i class="fa fa-chevron-right"></i><a href="#" class="button button-primary delete_add_btn" data-table="office_shipping_address_'+ count +'">'+oimwc_obj.delete_address+'</a><input type="text" data-name="title" placeholder="'+oimwc_obj.office_address_name+'"></div><table id="office_shipping_address_'+ count +'" class="office_shipping_address submenu"><tr><td>'+oimwc_obj.company_name+'</td><td><input type="text" data-name="im_company" id="im_company"></td></tr><tr><td>'+oimwc_obj.office_address+'</td><td><input type="text" data-name="im_address" id="im_address"></td></tr><tr><td>'+oimwc_obj.company_name+'</td><td><input type="text" data-name="im_company" id="im_company"></td></tr><tr><td>'+oimwc_obj.city+'</td><td><input type="text" data-name="im_city" id="im_city"></td></tr><tr><td>'+oimwc_obj.street+'</td><td><input type="text" data-name="im_street" id="im_street"></td></tr><tr><td>'+oimwc_obj.zip_code+'</td><td><input type="text" data-name="im_zip_code" id="im_zip_code"></td></tr><tr><td>'+oimwc_obj.country+'</td><td><select data-name="im_country" id="im_country"><option value="">'+oimwc_obj.select_country+'</option>'+ countries_str+'</select></td></tr><tr><td>'+oimwc_obj.email_address+'</td><td><input type="text" data-name="im_email" id="im_email"></td></tr><tr><td>'+oimwc_obj.phone_number+'</td><td><input type="text" data-name="im_phone" id="im_phone"></td></tr><tr><td>'+oimwc_obj.fax_number+'</td><td><input type="text" data-name="im_fax" id="im_fax"></td></tr><tr><td>'+oimwc_obj.website+'</td><td><input type="text" data-name="im_website" id="im_website"></td></tr><tr><td>'+oimwc_obj.tax_vat+'</td><td><input type="text" data-name="im_tax" id="im_tax"></td></tr></table></div>');
            var accordion = new Accordion($('.company_office_address_panel.accordion'), false);            
        },
        refresh_purchasde_order_preview: function(e){
            e.preventDefault();
            jQuery('#tiptip_holder').css('display','none');
            var total_amount = 0;
            $('.purchase_order_table').css('display','block');
            var len = $('.purchase_order_table #the-list:first tr').length;
            var supplier_id = $('#IO_supplier_filter option:selected').val();
            $('.product_listing_panel #the-list:eq(1) tr:visible').each(function(){
                var qty = $(this).find('.amount .arrived_qty_handler').val();
                var product_id = jQuery(this).find('td.column-thumb .productId').val();
                if(qty != '' && $(this).find('td').length == 5 && qty > 0 && product_id != ''){
                    $('.inventory_management_panel.blockUI .IO_spin').show();
                    $('.inventory_management_panel.blockUI').addClass('oimwc_overlay');
                    var clone = $('.product_'+product_id+':first').parent().parent().clone();
                    var clone_tr = $('.product_'+product_id+':first').parent().parent().next('tr').clone();
                    if(len > 1 && product_id != ''){
                        if($('.purchase_order_table #the-list:first tr').hasClass('no-items')){
                            $('.purchase_order_table #the-list:first tr.no-items').remove();
                            $('.purchase_order_table #the-list:first').append(clone).append(clone_tr);   
                        }else{
                            $('.purchase_order_table #the-list:first tr:last').after(clone);
                            var clone_data = $('.purchase_order_table #the-list:first tr:last').after(clone)
                            $(clone_data).after(clone_tr);
                        }
                    }else{
                        if($('.purchase_order_table #the-list:first tr').hasClass('no-items')){
                            $('.purchase_order_table #the-list:first tr.no-items').remove();
                            $('.purchase_order_table #the-list:first').append(clone).append(clone_tr);   
                        }else{
                            $('.purchase_order_table #the-list:first tr:last').after(clone);
                            var clone_data = $('.purchase_order_table #the-list:first tr:last').after(clone)
                            $(clone_data).after(clone_tr);
                        }
                    }
                    total_amount += parseFloat( jQuery('.amount_'+product_id).html() );
                    jQuery('.product_listing_panel .purchase_order_table .total_order_info_panel:first .amount').html( total_amount.toFixed(2) );
                    $(this).hide();
                    $(this).next('tr').hide();
                    $('.product_listing_panel #the-list:first tr').show();
                    $('.purchase_order_table').css('display','block');
                    $('.total_order_info_panel:eq(1) .amount').html( '0.00' );
                    $(this).find('.btn_'+product_id).val(oimwc_obj.remove_txt);
                    $('.purchase_order_table:first #the-list:first .amount input[name="product['+product_id+'][qty]"]').val(qty);
                    var price = $('.purchase_order_table:first #the-list:first .product_calc .amount_'+product_id).data('price');
                    $('.purchase_order_table:first #the-list:first .product_calc .amount_'+product_id).html((qty * price).toFixed(2));
                    if(product_id){
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                action:'purchase_order_table_data',
                                supplier_id: supplier_id,
                                product_id: product_id,
                                qty: qty
                            },
                            method: 'POST',
                            success: function (response) {
                                $('.inventory_management_panel.blockUI .IO_spin').hide();
                                $('.inventory_management_panel.blockUI').removeClass('oimwc_overlay');
                                $('#refresh_order .button').attr('disabled',true);
                                if(!$('.product_listing_panel #the-list:eq(1) tr').is(":visible")){
                                    $('.product_listing_panel #the-list:eq(1)').append('<tr class="no-items"><td colspan="5">'+oimwc_obj.no_products +'</td></tr>');
                                }
                                if(!$('.product_listing_panel #the-list:first tr').is(":visible")){
                                    $('.product_listing_panel #the-list:first').append('<tr class="no-items"><td colspan="5">'+oimwc_obj.no_preview_data_msg +'</td></tr>');
                                }
                                jQuery('#tiptip_holder').css('display','none');
                                $('.product_listing_panel #the-list:first tr').each(function(){
                                    $('.product_listing_panel #the-list:first .btn_'+product_id).addClass('btnTempRemoveProduct').removeClass('btnRemoveProduct');
                                    $('.product_listing_panel #the-list:first .btnRemovePO').css('display','block');
                                });
                            },
                            error: function (response) {
                                console.log(response);
                            }
                        });
                    }
                }
            });
        },
        purchase_order_accordion: function(){
            var accordion = new Accordion($('#accordion'), false);
        },
        remove_purchase_order_product: function(){
            var id = $(this).attr('data-id');
            var supplier_id = $('#IO_supplier_filter option:selected').val();
            $('.inventory_management_panel.blockUI .IO_spin').show();
            $('.inventory_management_panel.blockUI').addClass('oimwc_overlay');
            $.ajax({
                url: ajaxurl,
                data: {
                    action:'remove_purchase_order_product',
                    id: id,
                    supplier_id: supplier_id
                },
                method: 'POST',
                success: function (response) {
                    $('.product_listing_panel #the-list:eq(1) .product_calc .amount').text(0);
                    $('.inventory_management_panel.blockUI .IO_spin').hide();
                    $('.inventory_management_panel.blockUI').removeClass('oimwc_overlay');
                    $('.purchase_order_table #the-list:first tr').each(function(){
                        if($(this).find('.productId').val() == id){
                            $('.purchase_order_table #the-list:first input[value='+id+']').parent().parent().next('tr').remove();
                            $(this).remove();
                        }
                    });
                    if($('.product_listing_panel #the-list:eq(1) tr:visible').length == 1){
                        $('.no-items').remove();
                    } 
                    if( $('input[name="product['+id+'][qty]"]').data('warning') == undefined || $('input[name="product['+id+'][qty]"]').data('stock') < $('input[name="product['+id+'][qty]"]').data('warning') ){
                        $('.product_listing_panel #the-list:eq(1) .btn_'+id+':first').parents('tr.low_stock_field').css('display','table-row');
                        $('.product_listing_panel #the-list:eq(1) .btn_'+id+':first').parents('tr.low_stock_field').next('tr').css('display','table-row');
                    }else{
                        $('.product_listing_panel #the-list:eq(1) .btn_'+id+':first').parents('tr.low_stock_field').next('tr').remove();
                        $('.product_listing_panel #the-list:eq(1) .btn_'+id+':first').parents('tr.low_stock_field').remove();
                    }
                    $('.product_listing_panel #the-list:eq(1) .product_'+id).val(id);
                    $('.product_listing_panel #the-list:eq(1) tr').each(function(){
                        if(id == $(this).find('td.column-amount .btnRemovePO').data('id')){
                            $('.product_listing_panel #the-list:eq(1) input[name="product['+id+'][qty]"]').val('');
                            $('.product_listing_panel #the-list:eq(1) .column-amount .btnRemovePO').css('display','none');
                            $('.product_listing_panel #the-list:eq(1) .btn_'+id).removeClass('btnTempRemoveProduct').addClass('btnRemoveProduct');
                        }
                    });
                    $('.btn_'+id).val(oimwc_obj.remove_txt);
                    if($('.purchase_order_table:first .wp-list-table tbody tr:visible').length == 0) {
                        $('.purchase_order_table:first .wp-list-table tbody').append('<tr class="no-items"><td colspan="5">'+oimwc_obj.no_preview_data_msg+'</td></tr>');
                    }
                    $('.product_listing_panel #the-list:eq(1) .arrived_qty_handler').val('');
                }
            });
        },
        search_product: function () {
            if( $(this).find('.disabled').length ){
                swal(oimwc_obj.select_supplier_for_search);
                return false;
            }
        },
        add_temp_product: function(){
            if( $(this).hasClass('disabled') ){
                swal(oimwc_obj.select_tmp_product);
                return false;
            }
            jQuery('#new_product_form').trigger("reset");
            var dialog;
            dialog = $( "#add-temp-product-form" ).dialog({
              autoOpen: false,
              height: 550,
              width: 450,
              modal: true,
              dialogClass: 'tmp_product_dialog',
              buttons: [{
                text: oimwc_obj.add_tmp_product_text,
                disabled: true,
                id: "tmp_popup_btn",
                click: function() {
                    var formdata = jQuery('#new_product_form').serialize();
                    var supplier_id= jQuery('#IO_supplier_filter').val();
                    if( (tmp_product_id != '' || tmp_product_url != '') ){
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: formdata+"&action=add_temporary_product&supplier_id="+supplier_id,
                            success: function (response) {
                                if(response){
                                    var data = JSON.parse(response);
                                    if(data){
                                        $('.purchase_order_table:eq(1) tbody').prepend(data['data']);
                                        dialog.dialog( "close" );
                                        swal('',data.message,"success");
                                        var total_amount = 0;
                                        $( '.product_listing_panel #the-list:eq(1) .product_calc .amount' ).each(function(){
                                            if($(this).html() != ''){
                                                total_amount += parseFloat( $(this).html() );
                                            }
                                        });

                                        $('.total_order_info_panel .amount').html( total_amount.toFixed(2) );
                                        if(jQuery('.purchase_order_table:eq(1) .no-items').length){
                                            jQuery('.purchase_order_table:eq(1) .no-items').remove();
                                        }
                                        $('#refresh_order .button').attr('disabled',false);
                                    }
                                }
                            }
                        });
                    }
                },
                },{
                text: oimwc_obj.cancel_text,
                click: function() {
                    dialog.dialog( "close" );
                }
              }],
            });
            dialog.dialog( "open" );
            jQuery('#tmp_product_id').keyup(function(){
                if($('#tmp_product_id').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_url').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_id').val() != '' && $('#tmp_product_url').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_id').val() == '' && $('#tmp_product_url').val() != ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_id').val() == '' && $('#tmp_product_url').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                else{
                    jQuery('#tmp_popup_btn').removeClass('ui-state-disabled');
                    jQuery('#tmp_popup_btn').attr('disabled',false);
                }
            });
            jQuery('#tmp_product_url').keyup(function(){
                if($('#tmp_product_id').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_url').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_id').val() != '' && $('#tmp_product_url').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_id').val() == '' && $('#tmp_product_url').val() != ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                if($('#tmp_product_id').val() == '' && $('#tmp_product_url').val() == ''){
                    jQuery('#tmp_popup_btn').addClass('ui-state-disabled');
                }
                else{
                    jQuery('#tmp_popup_btn').removeClass('ui-state-disabled');
                    jQuery('#tmp_popup_btn').attr('disabled',false);
                }
            });
        },
        remove_tmp_product: function() {
            var self = $(this);
            var id = self.attr('data-id');
            var supplier_id = $('#IO_supplier_filter option:selected').val();
            swal({
                text: oimwc_obj.remove_tmp_product_txt,
                buttons: true,
                buttons: {
                    cancel: oimwc_obj.cancel_text,
                    remove: {
                      text: 'Ok',
                      value: "remove",
                    },
                }
            }).then((value) => {
                switch (value) {
                    case "remove":
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                action:'remove_temporary_product',
                                id: id,
                                supplier_id: supplier_id
                            },
                            method: 'POST',
                            success: function (data) {
                                var data = JSON.parse(data);
                                if(data.success){
                                    $('.wp-list-table .btnRemoveProduct[data-id="' + id + '"]').parents('tr').next('tr').remove();
                                    $('.wp-list-table .btnRemoveProduct[data-id="' + id + '"]').parents('tr').remove();
                                    if($('.purchase_order_table:eq(1) tbody tr').length == 0) {
                                        $('.purchase_order_table:eq(1) tbody').append('<tr><td colspan="5">'+oimwc_obj.no_products+'</td></tr>');
                                    }
                                    if($('.table-view-list tbody tr').length == 0) {
                                        $('.table-view-list tbody').append('<tr><td colspan="5">'+oimwc_obj.no_products+'</td></tr>');
                                    }
                                    if($('.purchase_order_table:first tbody tr').length == 0) {
                                        $('.purchase_order_table:first tbody').append('<tr class="no-items"><td colspan="5">'+oimwc_obj.no_preview_data_msg+'</td></tr>');
                                    }
                                }
                            }
                        });
                    break;
                }
            });
            return false;
        },
        select_order_number: function(){
            var id = jQuery("#request_date option:selected").text().split('|')[0].replace('#','').replace(' ','');
            jQuery('#selected_order_id').val(id);
        },
        tmp_allow_numbers: function(){
            if (/\D/g.test(this.value))
            {
                this.value = this.value.replace(/\D/g, '');
            }
        },
        tmp_allow_product_price: function(){
            if (/[A-Za-z!@#$%^&*()]/g.test(this.value))
            {
                this.value = this.value.replace(/\D/g, '');
            }
        }
    };
    supplier_variation.init();

});

jQuery(document).ajaxComplete(function(event, xhr, settings){
    jQuery('#request_date').change(function(){
        var id = jQuery(this).find('option:selected').text().split(' | ')[0].slice(1);
        jQuery(this).find('option:selected').attr('data-id',id);
    });
    if(jQuery('.purchase_order_table').length > 2){
        jQuery(".purchase_order_table:last").remove();
        jQuery(".purchase_order_table").slice(2,4).remove();
    }
    var total_amount = 0;
    jQuery('.product_listing_panel #the-list:first tr').each(function(){
        var qty = jQuery(this).find('td.column-amount .arrived_qty_handler').val();
        var id = jQuery(this).find('td.column-thumb .productId').val();
        jQuery('.product_listing_panel #the-list:eq(1) tr').each(function(){
            var i = jQuery(this).find('td.column-thumb .productId').val();
            if(id == i && qty > 0){
                jQuery(this).find('[data-id='+id+']').val(qty);
            }
        });
        if(id > 0){
            total_amount += parseFloat( jQuery('.amount_'+id).html() );
        }
        jQuery('.product_listing_panel .purchase_order_table .total_order_info_panel:first .amount').html( total_amount.toFixed(2) );
    });
    var total_amount_product = 0;
    jQuery('.product_listing_panel .purchase_order_table:eq(1) #the-list tr:visible').each(function(){
        var id = jQuery(this).find('td.column-thumb .productId').val();
        if(id > 0){
            total_amount_product += parseFloat( jQuery(this).find('.amount_'+id).html() );
        }
        jQuery('.product_listing_panel .purchase_order_table .total_order_info_panel:eq(1) .amount').html( total_amount_product.toFixed(2) );
        if(jQuery(this).find('.arrived_qty_handler').val() !== undefined){
            if(jQuery(this).find('.arrived_qty_handler').val().length > 0)
            jQuery('#refresh_order .button').removeAttr('disabled');
        }
    });
    if(jQuery('#product-type option:selected').val() == 'variable'){
        if(settings.data != '' && settings.data !== undefined){
            var ajax_action = settings.data.split('=')[1]; 
            if(ajax_action == 'woocommerce_load_variations&security'){
                jQuery('.discontinued_replacement_product_cls').each(function(){
                    jQuery(this).select2({
                    placeholder: oimwc_obj.select_product,
                    allowClear: true,
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250, 
                        data: function (params) {
                            return {
                                search_val: params.term, 
                                action: 'get_products_list_with_sku'
                            };
                        },
                        processResults: function( data ) {
                            var options_variable = [];
                            if ( data ) {
                                jQuery.each( data, function( index, text ) { 
                                    options_variable.push( { id: index, text: text  } );
                                });
                            }
                            return {
                                results: options_variable
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 3,
                    language: {
                        inputTooShort: function() {
                            return oimwc_obj.product_placeholder;
                        }
                    }  
                }).on("select2:unselecting", function(e) {
                    var self = jQuery(this);
                    setTimeout(function() {
                        self.select2('close');
                    }, 0);
                }); 
                });
            }
        }
    }
    jQuery('.temp_product_color').parent().parent().addClass('temp_background_color');
    jQuery('.show_if_variation_manage_stock label:contains(Stock quantity)').next().next().addClass('variable_stock_variation');

    jQuery('.lock_order_btn').each(function(){
        if(jQuery(this).find('i').hasClass('fa-unlock') && jQuery(this).hasClass('disabled')){
            jQuery(this).css('pointer-events','none');
            jQuery(this).tipTip("destroy");
        }
        if(jQuery(this).find('i').hasClass('fa-lock') && jQuery(this).hasClass('disabled')){
            jQuery(this).css('pointer-events','none');
            jQuery(this).tipTip("destroy");
        }
    });
    jQuery('.arrival_date_cls,.cancel_awaiting_order').each(function(){
        if(jQuery(this).hasClass('disabled')){
            jQuery(this).parent().css('pointer-events','none');
            jQuery(this).parent().tipTip("destroy");
        }
    });
});

jQuery( window ).load(function($) {
    jQuery('.temp_product_color').parent().parent().addClass('temp_background_color');
    jQuery('.lock_order_btn').each(function(){
        if(jQuery(this).find('i').hasClass('fa-unlock') && jQuery(this).hasClass('disabled')){
            jQuery(this).css('pointer-events','none');
            jQuery(this).tipTip("destroy");
        }
        if(jQuery(this).find('i').hasClass('fa-lock') && jQuery(this).hasClass('disabled')){
            jQuery(this).css('pointer-events','none');
            jQuery(this).tipTip("destroy");
        }
    });

    jQuery('.arrival_date_cls,.cancel_awaiting_order').each(function(){
        if(jQuery(this).hasClass('disabled')){
            jQuery(this).parent().css('pointer-events','none');
            jQuery(this).parent().tipTip("destroy");
        }
    });

    jQuery(".post-type-supplier #post-search-input").attr('placeholder',oimwc_obj.search_supplier_label);

    jQuery('#suppliers_data_panel .free_disabled').each(function(){
        if(jQuery(this).hasClass('silver_version')){
            jQuery(this).attr('data-msg',oimwc_obj.silver_upgrade_text);
        }
        if(jQuery(this).hasClass('gold_version')){
            jQuery(this).attr('data-msg',oimwc_obj.gold_upgrade_text);
        }
    });

    jQuery('#suppliers_data_panel .silver_disabled').each(function(){
        if(jQuery(this).hasClass('gold_version')){
            jQuery(this).attr('data-msg',oimwc_obj.gold_upgrade_text);
        }
    });

    var action_name = 'woocommerce_load_variations';
    jQuery( document ).ajaxSuccess(function( event, xhr, settings ) {
        if(settings.data){
            var data = (settings.data).split("&");
            if(data){
                var obj={};
                for(var key in data)
                {
                   obj[data[key].split("=")[0]] = data[key].split("=")[1];
                }
                if(obj.action != undefined && obj.action == action_name){
                    jQuery('.oimwc_var_fields_cls .free_disabled').each(function(){
                        if(typeof jQuery(this).data('data-msg') == 'undefined'){
                            if(jQuery(this).hasClass('silver_version')){
                                jQuery(this).attr('data-msg',oimwc_obj.silver_upgrade_text);
                            }
                            if(jQuery(this).hasClass('gold_version')){
                                jQuery(this).attr('data-msg',oimwc_obj.gold_upgrade_text);
                            }
                        }
                    });
                    jQuery('.oimwc_var_fields_cls .silver_disabled').each(function(){
                        if(typeof jQuery(this).data('data-msg') == 'undefined'){
                            if(jQuery(this).hasClass('gold_version')){
                                jQuery(this).attr('data-msg',oimwc_obj.gold_upgrade_text);
                            }
                        }
                    });

                    var accordion = new Accordion(jQuery('.oimwc_var_fields_cls .supplier_accordion_panel .accordion'), false);

                    if( jQuery('.supplier_accordion .accordion-inner').length ){
                        jQuery('.supplier_accordion .accordion-inner').each(function(index,value){
                            var supplier_id = jQuery(this).attr('data-supplier_id');
                            jQuery(this).parents('.supplier_accordion_panel').prev().find('select option[value='+supplier_id+']').attr('disabled','disabled');
                        });
                    }
                    jQuery('.save-variation-changes').removeClass('do_submit');
                }
            }
        }
    });

    jQuery('.tips').tipTip({ 'attribute': 'data-tip', 'fadeIn': 50, 'fadeOut': 50, 'delay': 50 });

    if(jQuery('div.fs-notice.updated').length > 0 || jQuery('div.fs-notice.success').length > 0 || jQuery('div.fs-notice.promotion').length > 0){
        jQuery('div.fs-notice.updated').prependTo( '#oimwc-top-area' );
        jQuery('div.fs-notice.updated').attr('style','display: block !important');
    }

    if( jQuery('.supplier_accordion .accordion-inner').length ){
        jQuery('.supplier_accordion .accordion-inner').each(function(index,value){
            var supplier_id = jQuery(this).attr('data-supplier_id');
            jQuery(this).parents('.supplier_accordion_panel').prev().find('select option[value='+supplier_id+']').attr('disabled','disabled');
        });
    }
    
    if( jQuery('.oimwc_total_missing_product').length ){
        jQuery('.oimwc_total_missing_product').each(function(){
            var appendObj;
            if( jQuery(this).parents('td').find('.wc-order-item-variation').length ){
                appendObj = jQuery(this).parents('td').find('.wc-order-item-variation');
            }else{
                appendObj = jQuery(this).parents('td').find('.wc-order-item-sku');
            }
            jQuery(this).appendTo( appendObj );
        });
    }
});

function version_popup(upgrade_text){
    swal({
        text: upgrade_text,
        buttons: true,
        buttons: {
            cancel: oimwc_obj.cancel_text,
            upgrade: {
              text: oimwc_obj.upgrade_btn_text,
              value: "upgrade",
            },
        }
    }).then((value) => {
        switch (value) {
            case "upgrade":
               window.location = oimwc_obj.upgrade_location;
              break;
          }
    });
}

function load_low_stock_products_func(){
    if( jQuery('.product_listing_panel .lw_spin').css( 'display' )  == 'block' ){
        return ;
    }
    var supplier_id = jQuery('#IO_supplier_filter').val();
    var subpage = jQuery('.supplier_filter_panel form input[name="subpage"]').val();
    if( subpage == 'delivery_table' ){
        var template = wp.template( "awaiting_delivery_tbl" );
    }else{
        var template = wp.template( "lowstock_supplier_filter" );
    }
    var nextPage = parseInt(jQuery('.low_stock_page').val()) + 1;
    var total_pages = parseInt(jQuery('#lw_total_pages').val());
    var search_val = jQuery('.search_lw_prod_panel input').val();
    if( nextPage > total_pages ){
        return;
    }
    jQuery('.product_listing_panel .lw_spin').show();
    if(jQuery('.show_all_product').is(":checked")){
        var show_all_product = 1;
        var supplier_id = jQuery('#IO_supplier_filter').val(); 
    }else{
        show_all_product = 0;
        supplier_id = jQuery('#IO_supplier_filter').val(); 
    }
    jQuery.ajax({
        url: oimwc_obj.prism_ajax_url,
        data: {
            action: 'load_low_stock_products',
            page: nextPage,
            supplier_id: supplier_id,
            search_val: search_val,
            subpage: subpage,
            show_all_product: show_all_product
        },
        method: 'POST',
        success: function (response) {
            jQuery('.low_stock_page').val(nextPage);
            if(response){
                jQuery('.product_listing_panel .lw_spin').hide();
                var parse_data = response.data.records;
                if(parse_data.length == 0){
                    jQuery('.product_listing_panel').data('pagination','complete');  
                    return; 
                }
                low_stock_products_response(parse_data,template,supplier_id);
            }
            else{
                jQuery('.product_listing_panel').data('pagination','complete');  
            }
        },
        error: function (response){

        }
    });
}

function low_stock_products_response(parse_data,template,supplier_id){
    var decodeHTML = function (html) {
        var txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    };
    var subpage = jQuery('.supplier_filter_panel form input[name="subpage"]').val();
    if(parse_data.length != 0){
        _.each(parse_data, function (value, key) {
        var data = template(value);
            if(subpage == 'delivery_table'){
                jQuery('.product_listing_panel #the-list').append( decodeHTML (data) );
            }else{
                jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
                jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
                var clone_data = jQuery('.purchase_order_table').clone();
                jQuery('.load_lowstock_msg').replaceWith( clone_data );
                jQuery('.purchase_order_table:eq(1) #the-list').append( decodeHTML (data) );
                jQuery('.purchase_order_table:eq(1) h3').html(oimwc_obj.products_text);
                jQuery('.purchase_order_table:eq(1)').find('.no-items').remove();
                jQuery('.purchase_order_table:eq(1) #the-list tr').each(function(){
                    jQuery(this).addClass('low_stock_field');
                    var productId = jQuery(this).find('.productId').val();
                    var keywords = jQuery('.purchase_order_data').text();
                    var order_data = keywords.split(',');
                    jQuery.each(order_data, function(i, keyword){
                        if(productId == order_data[i]){
                            jQuery('.btn_'+productId).parents('tr.low_stock_field').hide();
                            jQuery('.btn_'+productId).parents('tr.low_stock_field').next('tr').hide();
                        }
                    });
                });
                jQuery('.product_listing_panel #the-list:first tr').each(function(){
                    var productId = jQuery(this).find('.productId').val();
                    jQuery('.product_listing_panel #the-list:first .btn_'+productId).addClass('btnTempRemoveProduct').removeClass('btnRemoveProduct');
                });
                jQuery('.temp_product').remove();
            }
        });
    }else{
        if(subpage == 'delivery_table'){
            jQuery('.product_listing_panel #the-list').append('<tr class="no-items"><td colspan=6>'+ oimwc_obj.no_products +'</td></tr>');
        }else{
            var clone_data = jQuery('.purchase_order_table').clone();
            jQuery('.load_lowstock_msg').replaceWith( clone_data );
            jQuery('.purchase_order_table:eq(1)').find('.no-items:first').remove();
            jQuery('.purchase_order_table:eq(1) h3').html(oimwc_obj.products_text);
            jQuery('.total_order_info_panel:eq(1)').remove();
            jQuery('.product_listing_panel #the-list:eq(1)').append('<tr class="no-items"><td colspan=5>'+ oimwc_obj.no_products +'</td></tr>');
        }
    }

    if( supplier_id == 'all' ){
        jQuery('.product_listing_panel #the-list .product_calc').remove();
        jQuery('.product_listing_panel #the-list .btnAddItemToOrder').remove();
        jQuery('.product_listing_panel #the-list .arrived_qty_handler').attr('disabled',true);
        jQuery('.product_listing_panel #the-list .arrived_qty_handler').addClass('no_supplier');
        jQuery('.product_listing_panel #the-list .arrived_qty_handler').parent('div').addClass('tips').attr('data-tip',oimwc_obj.select_supplier);
        jQuery('.purchase_order_table').hide();
        jQuery('.product_listing_panel .purchase_order_table:first').after('<h3 class="load_lowstock_msg"><i class="fas fa-arrow-up"></i>'+oimwc_obj.load_low_stock_msg+'</h3>');
        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
    }
    jQuery( '.tips' ).tipTip({ 'attribute': 'data-tip', 'fadeIn': 50, 'fadeOut': 50, 'delay': 50 });
}

function purchase_order_products_response(order_data,template,supplier_id){
    var decodeHTML = function (html) {
        var txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    };

    if(order_data.length != 0){
        _.each(order_data, function (value, key) {
            var data = template(value);
                jQuery('.purchase_order_table #the-list:first').append( decodeHTML (data) );
        });
    }else{
        jQuery('.purchase_order_table #the-list:first').append('<tr class="no-items"><td colspan=5>'+ oimwc_obj.no_preview_data_msg +'</td></tr>');
    }

    if( supplier_id == 'all' ){
        jQuery('.product_listing_panel #the-list .product_calc').remove();
        jQuery('.product_listing_panel #the-list .btnAddItemToOrder').remove();
        jQuery('.product_listing_panel #the-list .arrived_qty_handler').attr('disabled',true);
        jQuery('.product_listing_panel #the-list .arrived_qty_handler').addClass('no_supplier');
        jQuery('.product_listing_panel #the-list .arrived_qty_handler').parent('div').addClass('tips').attr('data-tip',oimwc_obj.select_supplier);
        jQuery('.purchase_order_table').hide();
        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').attr('disabled',false);
        jQuery('.search_lw_prod_panel input[name="search_lw_txt"]').removeClass('disabled');
    }
    jQuery( '.tips' ).tipTip({ 'attribute': 'data-tip', 'fadeIn': 50, 'fadeOut': 50, 'delay': 50 });
}