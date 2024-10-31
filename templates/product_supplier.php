<?php
/**
 * Product inventory settings
 * 
 * Displays inventory and supplier settings for parent product.
 *
 * @since 1.0.0
 */

global $wpdb;
?>
<div id="suppliers_data_panel" class="panel woocommerce_options_panel">
	<p class="supplier_description description"><?php _e('This information is used for single type product or default values for the variations in variable products.', 'order-and-inventory-manager-for-woocommerce'); ?></p>
	<?php

	$free_disabled = $silver_disabled = '';
	if( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() ) {
	    $free_disabled = 'free_disabled';		
	}

	if( oimwc_fs()->is_plan_or_trial('silver',true) ){
		$silver_disabled = 'silver_disabled';
	}
    
    if( (oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial()) || oimwc_fs()->is_plan_or_trial('silver',true) ) {
    	$discontinued_product = $all_discontinued = 'no';
		$supplier_art_id = $supplier_product_url = $supplier_note = '';
		$supplier_pack = $our_pack = 1;
		$display_pack_size = 2;  //2 means hide
		$purchase_price = 0;
    }
    
    if(!$supplier_currency){
        $supplier_currency = $default_currency;
    }
	$symbol = get_woocommerce_currency_symbol( $supplier_currency );
	if( $symbol ){
		$symbol = " ($symbol)";
	}

	$newKeys = [];
	if($units){
        foreach ($units as $key => $value) {
            $newKeys[$key]=__($key,'order-and-inventory-manager-for-woocommerce');
        }
    }

    $cls = ($discontinued_product == 'yes') ? 'disable_fields' : '';
    $manage_cls = ($manage_stock == 'no') ? 'manage_disable_fields' : '';

    $post_id = $post->ID;
    $product = wc_get_product( $post_id );
	$simple = false;
	$disable_gtin_fields = get_option('disable_oimwc_gtin_fields');
	if( $product->is_type( 'simple' ) )
	{
		$simple = true;
	}
	$option_select = (!empty($oimwc_discontinued_replacement_title)) ? $oimwc_discontinued_replacement_title : '';
	if($simple)
	{	
        woocommerce_wp_checkbox( array(
            'id'=>'oimwc_discontinued_product' ,
            'label'=>__('Discontinued Product','order-and-inventory-manager-for-woocommerce'),
            'value' => $discontinued_product,
            'desc_tip' => 'true',
            'class'	=> 'discontinued_product_cls',
            'wrapper_class' => "$free_disabled $silver_disabled gold_version"
        ));

        woocommerce_wp_select( array(
            'id'			=>'oimwc_discontinued_replacement_product' ,
            'label'			=>__('Discontinued Replacement Product','order-and-inventory-manager-for-woocommerce'),
            'options'		=> array('' =>__('Select Product','order-and-inventory-manager-for-woocommerce'),$discontinued_replacement_product => $option_select),
            'value'         => $discontinued_replacement_product,
            'desc_tip' 		=> 'true',
            'description' 	=> __('Selected product will be presented as a replacement product on the product page.', 'order-and-inventory-manager-for-woocommerce'),
            'class'			=> 'discontinued_product_cls1',
            'wrapper_class' => "$free_disabled $silver_disabled gold_version"
        ));
	}

	if($discontinued_product == 'yes'){
    	$supplier_show_in_low_stock = 'no';
    }
    if($simple && $disable_gtin_fields != 1){
    woocommerce_wp_text_input( array(
			'id'                => 'oimwc_gtin_num',
			'label'             => __( 'GTIN Number', 'order-and-inventory-manager-for-woocommerce' ),
			'value'				=> esc_html($gtin_num),
			'desc_tip'    		=> 'true',
			'placeholder'		=> 'EAN / UPC /  UCC / ITF',
			'type'				=>	'text',
			'description'       => __('GTIN-12 (UPC), GTIN-13 (EAN-13), GTIN-14 (EAN/UCC-128 or ITF-14), and GTIN-8 (EAN-8)', 'order-and-inventory-manager-for-woocommerce'),
			'wrapper_class' 	=> ""
			)
		);
	}
    woocommerce_wp_checkbox(array(
        'id'=>'oimwc_show_in_low_stock' ,
        'label'=>__('Enable warning for low stock','order-and-inventory-manager-for-woocommerce'),
        'value' => $supplier_show_in_low_stock,
        'cbvalue' => "yes",
        'desc_tip' => 'true',
        'description' => __('Add this product to the "Products with low stock"-page if the low stock threshold has been reached.', 'order-and-inventory-manager-for-woocommerce'),
        'wrapper_class' => "$cls js_disabled_fld" 
    ));

	woocommerce_wp_text_input( array(
		'id'                => 'oimwc_low_stock_threshold_level',
		'label'             => __( 'Low stock threshold', 'order-and-inventory-manager-for-woocommerce' ),
		'desc_tip'    		=> 'true',
		'data_type'			=>	'stock',
		'type'				=>	'number',
		'description'       => __('Enter value for when this product is considered to be low in stock.',
		 'order-and-inventory-manager-for-woocommerce'),
		'wrapper_class' 	=> "$cls js_disabled_fld $manage_cls"
		)
	);
			
	if($simple)
	{
		//$physical_stock = final_physical_stock($post_id,'simple');
		//$physical_stock = calculate_physical_stock($post_id,'simple');
		$physical_stock = (!empty($physical_stock)) ? $physical_stock : 0;

		woocommerce_wp_text_input( 
			array( 
			  'id' => 'oimwc_physical_stock', 
			  'label' => __( 'Physical stock Qty', 'order-and-inventory-manager-for-woocommerce' ), 
			  'desc_tip' => 'true',
			  'value' => esc_html($physical_stock),
			  'disabled' => 'disabled',
			  //'style' 	  => 'pointer-events: none;',
			  'wrapper_class' => "$manage_cls $free_disabled $silver_disabled gold_version",
			  'custom_attributes' => array('data-name'=> esc_html($physical_stock))
		));
	}	
	if($simple){
    woocommerce_wp_text_input(array(
        'id' => 'oimwc_physical_units_stock',
        'label' => __('Physical units in stock', 'order-and-inventory-manager-for-woocommerce'),
        'desc_tip' => 'false',
        'data_type' => 'stock',
        'type' => 'number',
        'value'=> esc_html($total_pieces),
        'wrapper_class' => "$manage_cls",
        'custom_attributes' => array('data-name'=> esc_html($total_pieces)) 
        )
    );
    woocommerce_wp_text_input(array(
            'id' => 'oimwc_our_pack_size',
            'label' => __('Shop pack size', 'order-and-inventory-manager-for-woocommerce'),
            'desc_tip' => 'true',
            'data_type' => 'stock',
            'type' => 'number',
            'value'=> esc_html($our_pack),
            'description' => __('This is how many units of a product you sell to your customers when they buy "1" item.<br/>If your default pack size is 2, then enter the value "2".', 'order-and-inventory-manager-for-woocommerce'),
            'wrapper_class' => "$free_disabled $silver_disabled gold_version",
            'custom_attributes' => array('data-name'=> esc_html($our_pack), 'min' => 1)
            )
    );
	}
	if( $simple && get_ordered_product_qty( $post_id, 'simple' ) ){
		require_once OIMWC_TEMPLATE . 'notice_for_product_packsize.php';
	}  	
    woocommerce_wp_select(
            array(
                'id' => 'oimwc_supplier_unit',
                'label' => __('Product unit', 'order-and-inventory-manager-for-woocommerce'),
                'options' => $newKeys,
                'value'=> esc_html($unit),
                'desc_tip' => 'true',
                'description' => __('Unit type of your product. Ex a rope you might want to sell in meters instead of pieces :-)', 'order-and-inventory-manager-for-woocommerce'),
                'wrapper_class' => ''
            )
    );
       
    woocommerce_wp_select(
        array(
            'id' => 'oimwc_manual_pack_size_setting',
            'label' => __('Display pack size on product page', 'order-and-inventory-manager-for-woocommerce'),
            'options' => array(__('Preset','order-and-inventory-manager-for-woocommerce'),__('Show','order-and-inventory-manager-for-woocommerce'),__('Hide','order-and-inventory-manager-for-woocommerce')),
            'value' => esc_html($display_pack_size),
            'desc_tip' => 'false',
            'wrapper_class' => "$free_disabled $silver_disabled gold_version"
        )
	);

	if(!$simple)
	{
        woocommerce_wp_checkbox( array(
            'id'=>'oimwc_all_discontinued_products' ,
            'label'=>__('Flag all variants as discontinued products','order-and-inventory-manager-for-woocommerce'),
            'value' => $all_discontinued,
            'desc_tip' => 'false',
            'class'	=> 'all_discontinued_products_cls',
            'wrapper_class' => "$free_disabled $silver_disabled gold_version"
        ));
	}
	if($simple){
	if( oimwc_check_permission() && oimwc_hide_supplier_info() ){
		$old_supplier_name = get_post_meta($supplier_id,'oimwc_supplier_short_name',true);
		echo '<h2 class="">'. __('Supplier Info','order-and-inventory-manager-for-woocommerce').'</h2>';
		echo '<div class="select_supplier_div">';
		woocommerce_wp_select(
			array(
				'id'          => 'oimwc_select_supplier',
				'name'        => 'oimwc_select_supplier',
				'label'       => __( 'Supplier', 'order-and-inventory-manager-for-woocommerce' ),
				'options'     => OIMWC_MAIN::get_supplier_list( true ),
				'desc_tip'    => 'false',
				'wrapper_class' => "",
			)	
		);
		echo '<input type="hidden" name="pid" id="pid" value="'.$post_id.'" />';
		echo '<input type="button" class="button add_supplier" value="'.__('Add','order-and-inventory-manager-for-woocommerce').'">';
		echo '</div>';
		?>
		<div class="supplier_accordion_panel">
			<div class="supplier_accordion accordion">
				<?php if($supplier_id){ ?>
				<div class="accordion-inner" data-supplier_id="<?php echo $supplier_id;?>">
					<div class="link">
						<span class="supplier_title"><?php echo $old_supplier_name;?></span>
						<a href="#" class="remove_supplier delete"><?php _e('Remove','order-and-inventory-manager-for-woocommerce');?></a>
					</div>
					<div class="submenu">
						<div>
							<input type="hidden" name="oimwc_supplier_id" value="<?php echo $supplier_id; ?>" />
						<?php
						woocommerce_wp_text_input( array(
							'id'                => 'oimwc_supplier_product_id',
							'label'             => __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' ),
							'value'				=> esc_html($supplier_art_id),
							'desc_tip'    		=> 'true',
							'description'       => __('Enter the product ID the supplier used for this product.', 'order-and-inventory-manager-for-woocommerce'),
							'wrapper_class' 	=> "form-row form-row-first $free_disabled silver_version"
							)
						);
						
						woocommerce_wp_text_input( array(
							'id'                => 'oimwc_supplier_product_url',
							'label'             => __( 'Supplier Prod. URL', 'order-and-inventory-manager-for-woocommerce' ),
							'value'				=> esc_html($supplier_product_url),
							'desc_tip'    		=> 'true',
							'data_type'			=>	'url',
							'type'				=>	'text',
							'description'       => __('If the supplier has a direct URL to the product on their homepage, enter it here.', 'order-and-inventory-manager-for-woocommerce'),
							'wrapper_class' 	=> "form-row form-row-last $free_disabled silver_version"
							)
						);

						woocommerce_wp_text_input( array(
							'id'          => 'oimwc_supplier_note',
							'label'       => __( 'Product Notes to Supplier', 'order-and-inventory-manager-for-woocommerce' ),
							'value'		  => esc_html($supplier_note),
							'desc_tip'    => true,
							'description' => __( 'In this field, you enter special characteristics about the product. It can be something the supplier Product ID does not inform the supplier of, ex. a special color or a unique packing of the product.', 'order-and-inventory-manager-for-woocommerce' ),
							'wrapper_class' => "form-row form-row-first $free_disabled silver_version"
						));

						woocommerce_wp_text_input( array(
							'id'            => 'oimwc_supplier_purchase_price',
							'label'         => __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ).$symbol,
					        'value' 		=> $purchase_price ? esc_html(wc_format_decimal($purchase_price,2)) : number_format(0,2),
							'desc_tip'    	=> 'false',
							'data_type'		=>	'price',
							'wrapper_class' => "form-row form-row-last $free_disabled silver_version"
							)
						);

					    woocommerce_wp_text_input( array(
							'id'                => 'oimwc_supplier_pack_size',
							'label'             => __( 'Supplier pack size', 'order-and-inventory-manager-for-woocommerce' ),
							'desc_tip'    		=> 'true',
							'data_type'			=>	'stock',
					        'type'              =>'number',
					        'value' 			=> esc_html($supplier_pack),
					        'class' 			=> 'oimwc_restrict_characters_on_paste oimwc_allow_only_numbers',
							'description'       => __('This sets the default value of your suppliers pack size. <br/>If most of your products come in 2-pack, enter the value "2".', 'order-and-inventory-manager-for-woocommerce'),
							'wrapper_class' 	=> "form-row form-row-first $free_disabled $silver_disabled gold_version"
							)
					   	);
					    ?>
					    <div class="supplier_btn">
							<button type="button" class="button-primary save_supplier" data-id="<?php echo $supplier_id; ?>" data-index="0"><?php _e('Save supplier info','order-and-inventory-manager-for-woocommerce');?></button>
							<span class="spinner"></span>
						</div>
						</div>
					</div>
				</div>
				<?php } 

				$tablename = $wpdb->prefix . 'additional_supplier_info';
				$supplier_data = $wpdb->get_results("SELECT * FROM {$tablename} WHERE product_id = {$post_id} && variable_id = 0", ARRAY_A);
				if( is_array($supplier_data) && count($supplier_data) > 0 ){
					foreach ($supplier_data as $key => $value) {
						$key = $key + 1;
						$supplier_name = get_post_meta($value['supplier_id'],'oimwc_supplier_short_name',true);
						$supplier_currency = get_post_meta($value['supplier_id'], 'oimwc_supplier_currency', true);
						if(!$supplier_currency){
					        $supplier_currency = $default_currency;
					    }
						$new_symbol = get_woocommerce_currency_symbol( $supplier_currency );
						if( $new_symbol ){
							$new_symbol = " ($new_symbol)";
						}
						?>
						<div class="accordion-inner" data-supplier_id="<?php echo $value['supplier_id'];?>">
							<div class="link">
								<span class="supplier_title"><?php echo $supplier_name;?></span>
								<a href="#" class="remove_supplier delete"><?php _e('Remove','order-and-inventory-manager-for-woocommerce');?></a>
							</div>
							<div class="submenu">
								<div>
									<input type="hidden" name="additional_suppliers[<?php echo $key; ?>][supplier_id]" data-name="supplier_id" value="<?php echo $value['supplier_id'];?>">
									<?php
									woocommerce_wp_text_input( array(
										'id'        => 'additional_suppliers['.$key.']',
										'name' 		=> 'additional_suppliers['.$key.'][product_id]',
										'label' 	=> __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' ),
										'value'		=> esc_html($value['supplier_product_id']),
										'desc_tip' 	=> 'true',
										'description' => __('Enter the product ID the supplier used for this product.','order-and-inventory-manager-for-woocommerce'),
										'wrapper_class' => "form-row form-row-first $free_disabled silver_version",
										'custom_attributes' => array('data-name'=>'product_id')
										)
									);
						
									woocommerce_wp_text_input( array(
										'id'        => 'additional_suppliers['.$key.']',
										'name'    	=> 'additional_suppliers['.$key.'][product_url]',
										'label' 	=> __( 'Supplier Prod. URL', 'order-and-inventory-manager-for-woocommerce' ),
										'value'		=> esc_html($value['supplier_product_url']),
										'desc_tip' 	=> 'true',
										'data_type'	=>	'url',
										'type'		=>	'text',
										'description' => __('If the supplier has a direct URL to the product on their homepage, enter it here.', 'order-and-inventory-manager-for-woocommerce'),
										'wrapper_class' => "form-row form-row-last $free_disabled silver_version",
										'custom_attributes' => array('data-name'=>'product_url')
										)
									);

									woocommerce_wp_text_input( array(
										'id'          => 'additional_suppliers['.$key.']',
										'name'        => 'additional_suppliers['.$key.'][supplier_note]',
										'label'       => __( 'Product Notes to Supplier', 'order-and-inventory-manager-for-woocommerce' ),
										'value'		  => esc_html($value['product_notes']),
										'desc_tip'    => true,
										'description' => __( 'In this field, you enter special characteristics about the product. It can be something the supplier Product ID does not inform the supplier of, ex. a special color or a unique packing of the product.', 'order-and-inventory-manager-for-woocommerce' ),
										'wrapper_class' => "form-row form-row-first $free_disabled silver_version",
										'custom_attributes' => array('data-name'=>'supplier_note')
									));

									woocommerce_wp_text_input( array(
										'id'            => 'additioal_suppliers['.$key.']',
										'name'          => 'additional_suppliers['.$key.'][purchase_price]',
										'label'         => __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ).$new_symbol,
								        'value' 		=> $value['purchase_price'] ? esc_html(wc_format_decimal($value['purchase_price'],2)) : number_format(0,2),
										'desc_tip'    	=> 'false',
										'data_type'		=>	'price',
										'wrapper_class' => "form-row form-row-last $free_disabled silver_version",
										'custom_attributes' => array('data-name'=>'purchase_price')
										)
									);

								    woocommerce_wp_text_input( array(
								    	'id'                => 'additional_suppliers['.$key.']',
										'name'              => 'additional_suppliers['.$key.'][pack_size]',
										'label'             => __( 'Supplier pack size', 'order-and-inventory-manager-for-woocommerce' ),
										'desc_tip'    		=> 'true',
										'data_type'			=>	'stock',
								        'type'              =>'number',
								        'value' 			=> esc_html($value['pack_size']),
										'description'  		=> __('This sets the default value of your suppliers pack size. <br/>If most of your products come in 2-pack, enter the value "2".', 'order-and-inventory-manager-for-woocommerce'),
										'wrapper_class' 	=> "form-row form-row-first $free_disabled $silver_disabled gold_version",
										'class' 			=> 'oimwc_restrict_characters_on_paste oimwc_allow_only_numbers',
										'custom_attributes' => array('data-name'=>'pack_size')
										)
								   	);
									?>
									<div class="supplier_btn">
										<button type="button" class="button-primary save_supplier" data-id="<?php echo $value['supplier_id']; ?>" data-index="<?php echo $key; ?>"><?php _e('Save supplier info','order-and-inventory-manager-for-woocommerce');?></button>
										<span class="spinner"></span>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
		<input type="hidden" name="oimwc_currency" value="<?php echo $symbol;?>">
	    <?php
	}
	}
	if($simple) { ?>
		<div class="update_btn_data">
			<button type="button" class="button-primary update_btn"><?php _e('Update','order-and-inventory-manager-for-woocommerce');?></button>
			<span class="spinner"></span><br />
			<small><?php _e( 'Only saves OIMWC data and stock related data.', 'order-and-inventory-manager-for-woocommerce' ); ?></small>
		</div>
	<?php } else{ ?>
		<div class="update_btn_data">
			<button type="button" class="button-primary update_btn_variable"><?php _e('Update','order-and-inventory-manager-for-woocommerce');?></button>
			<span class="spinner"></span><br />
			<small><?php _e( 'Only saves OIMWC data and stock related data.', 'order-and-inventory-manager-for-woocommerce' ); ?></small>
		</div>
	<?php } ?>
</div>