<?php
/**
 * Variable inventory settings
 * 
 * Displays inventory and supplier settings for variations
 *
 * @since 1.0.0
 */

global $wpdb;
?>
<div id="suppliers_variable_data_panel_<?php echo $loop; ?>" class="show_if_variation_manage_supplier variable_tab_data">
    <?php
    foreach ($units as $key => $value) {
        $newKeys[$key]=__($key,'order-and-inventory-manager-for-woocommerce');
    }

    $free_disabled = $silver_disabled = '';
	if( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() ) {
	    $free_disabled = 'free_disabled';

		$discontinued_product = 'no';
		$supplier_art_id = $supplier_product_url = $supplier_note = '';
		$supplier_pack = $our_pack = 1;
		$purchase_price = 0;
	}

	if( oimwc_fs()->is_plan_or_trial('silver',true) ){
		$silver_disabled = 'silver_disabled';

		$discontinued_product = 'no';
		$supplier_art_id = $supplier_product_url = $supplier_note = '';
		$supplier_pack = $our_pack = 1;
		$purchase_price = 0;
	}

	if(!$supplier_currency){
        $supplier_currency = $default_currency;
    }

	$symbol = get_woocommerce_currency_symbol( $supplier_currency );
	if( $symbol ){
		$symbol = " ($symbol)";
	}
	$disable_gtin_fields = get_option('disable_oimwc_gtin_fields');
	do_action( 'oimw_product_options_inventory_product_data', $variation->ID );
	$option_select = (!empty($oimwc_discontinued_replacement_title)) ? $oimwc_discontinued_replacement_title : '';
    ?>
	<div class="oimwc_var_fields_cls">
		<input type="hidden" name="pid" id="pid_var" value="<?php echo $variation->ID; ?>" />
		<h2 class="form-row form-row-first"><?php echo __('OIMWC Settings','order-and-inventory-manager-for-woocommerce');?></h2>
		<?php
			$cls = ($discontinued_product == 'yes') ? 'disable_fields' : '';
			if($manage_stock == 'no'){
				$manage_cls	= 'manage_disable_fields';
			}
			else{
			 	$manage_cls = '';
			}
			
			woocommerce_wp_checkbox(array(
                'id' => "oimwc_show_in_low_stock_{$loop}",
                'name' => "oimwc_show_in_low_stock[{$loop}]",
                'value' => $supplier_show_in_low_stock,
                'cbvalue' => "yes",
                'label' => __('Enable warning for low stock', 'order-and-inventory-manager-for-woocommerce'),
                'desc_tip' => 'true',
                'wrapper_class' => "form-row form-row-first $cls js_disabled_fld",
                'description' => __('Add this product to the "Products with low stock"-page if the low stock threshold has been reached.','order-and-inventory-manager-for-woocommerce'),
                'class'=>'supplier_variable_checkbox chk_cls'
            ));
		
			woocommerce_wp_checkbox( array(
	            'id'=>"oimwc_discontinued_product_{$loop}",
	            'name'=>"oimwc_discontinued_product[{$loop}]",
	            'label'=>__('Discontinued Product','order-and-inventory-manager-for-woocommerce'),
	            'value' => $discontinued_product,
	            'desc_tip' => 'true',
	            'class'	=> 'discontinued_product_cls chk_cls',
	            'wrapper_class' => "form-row form-row-last $free_disabled $silver_disabled gold_version"
	        ));
	        
	        woocommerce_wp_select( array(
	            'id'		=> "oimwc_discontinued_replacement_product_{$loop}",
	            'name'		=> "oimwc_discontinued_replacement_product[{$loop}]",
	            'label'		=> __('Discontinued Replacement Product','order-and-inventory-manager-for-woocommerce'),
	            'options'	=> array(__('Select Product','order-and-inventory-manager-for-woocommerce'),$discontinued_replacement_product => $option_select),
	            'value'     => $discontinued_replacement_product,
	            'desc_tip' 	=> 'true',
	            'description' => __('Selected product will be presented as a replacement product on the product page.', 'order-and-inventory-manager-for-woocommerce'),
	            'class'		=> 'discontinued_replacement_product_cls',
	            'wrapper_class' => "form-row form-row-first $free_disabled $silver_disabled gold_version oimwc_discontinued_replacement_products"
	        ));

		    woocommerce_wp_text_input(array(
		        'id' => "oimwc_physical_units_stock_{$loop}",
		        'name' => "oimwc_physical_units_stock[{$loop}]",
		        'value' => esc_html($total_pieces),
		        'label' => __('Physical units in stock', 'order-and-inventory-manager-for-woocommerce'),
		        'desc_tip' => 'false',
		        'data_type' => 'stock',
		        'type' => 'number',
		        'class' => 'oimwc_physical_units_stock_variation',
		        'wrapper_class' => "form-row form-row-first $manage_cls oimwc_physical_units_stock_variation_field",
		        'custom_attributes' => array('data-attr'=>'if_no_manage_stock','data-name'=>esc_html($total_pieces))
		    ));

	        woocommerce_wp_select(
	            array(
	                'id' => "oimwc_supplier_unit_{$loop}",
	                'name' => "oimwc_supplier_unit[{$loop}]",
	                'value' => esc_html($unit),
	                'label' => __('Product unit', 'order-and-inventory-manager-for-woocommerce'),
	                'wrapper_class' => "form-row form-row-last",
	                'options' => $newKeys,
	                'desc_tip' => 'true',
	                'description' => __('Unit type of your product. Ex a rope you might want to sell in meters instead of pieces :-)', 'order-and-inventory-manager-for-woocommerce'),
	            )
	        );
		
	        woocommerce_wp_text_input(array(
                'id' => "oimwc_low_stock_threshold_level_{$loop}",
                'name' => "oimwc_low_stock_threshold_level[{$loop}]",
                'value' => $this->get_data('oimwc_low_stock_threshold_level'),
                'label' => __('Low stock threshold', 'order-and-inventory-manager-for-woocommerce'),
                'desc_tip' => 'true',
                'data_type' => 'stock',
                'type' => 'number',
                'wrapper_class' => "form-row form-row-first $cls js_disabled_fld $manage_cls",
                'description' => __('Enter value for when this product is considered to be low in stock.', 'order-and-inventory-manager-for-woocommerce'),
	        	'custom_attributes' => array('data-attr'=>'if_no_manage_stock')
                )
            );

			//$physical_stock = final_physical_stock($variation->ID,'variable');
			//$physical_stock = calculate_physical_stock($variation->ID,'variable');
			$physical_stock = (!empty($physical_stock)) ? $physical_stock : 0;

	        woocommerce_wp_text_input( array( 
				'id'          => "oimwc_physical_stock{$loop}", 
				'name'		  => "oimwc_physical_stock[{$loop}]", 
				'label'       => __( 'Physical stock Qty', 'order-and-inventory-manager-for-woocommerce' ), 
				'desc_tip'    => 'true',
				'value'       => esc_html($physical_stock),
				//'style' 	  => 'pointer-events: none;',
				'class' => 'oimwc_physical_stock_variation',
				'wrapper_class' => "form-row form-row-last $manage_cls $free_disabled $silver_disabled gold_version oimwc_physical_stock_variation_field",
				'custom_attributes' => array('data-attr'=>'if_no_manage_stock','data-name'=>esc_html($physical_stock))
			));

	        woocommerce_wp_text_input(array(
                'id' => "oimwc_our_pack_size_{$loop}",
                'name' => "oimwc_our_pack_size[{$loop}]",
                'value' => esc_html($our_pack),
                'label' => __('Shop pack size', 'order-and-inventory-manager-for-woocommerce'),
                'desc_tip' => 'true',
                'data_type' => 'stock',
                'type' => 'number',
                'default' => '1',
                'description' => __('This is how many units of a product you sell to your customers when they buy "1" item.<br/>If your default pack size is 2, then enter the value "2".', 'order-and-inventory-manager-for-woocommerce'),
                'class' => 'oimwc_our_pack_size_variation',
                'wrapper_class' => "form-row form-row-first $free_disabled $silver_disabled gold_version",
	        	'custom_attributes' => array('data-name'=>esc_html($our_pack), 'min' => 1)
            ));

            if($disable_gtin_fields != 1){
	        woocommerce_wp_text_input( array(
				'id'                => "oimwc_gtin_num_{$loop}",
				'name' 				=> "oimwc_gtin_num[{$loop}]",
				'label'             => __( 'GTIN Number', 'order-and-inventory-manager-for-woocommerce' ),
				'value'				=> esc_html($gtin_num),
				'desc_tip'    		=> 'true',
				'placeholder'		=> 'EAN / UPC /  UCC / ITF',
				'type'				=>	'text',
				'description'       => __('GTIN-12 (UPC), GTIN-13 (EAN-13), GTIN-14 (EAN/UCC-128 or ITF-14), and GTIN-8 (EAN-8)', 'order-and-inventory-manager-for-woocommerce'),
				'wrapper_class' 	=> "form-row form-row-last"
				)
			);
	    	}

			if( get_ordered_product_qty( $variation->ID, 'variable' ) ){
				$variable_class = 'form-row form-row-last';
				require OIMWC_TEMPLATE . 'notice_for_product_packsize.php';
			}
	        if( oimwc_check_permission() && oimwc_hide_supplier_info() ){
            	$old_supplier_name = get_post_meta($supplier_id,'oimwc_supplier_short_name',true);
				echo '<h2 class="form-row form-row-first">'. __('Supplier Info','order-and-inventory-manager-for-woocommerce').'</h2>';
				echo '<div class="select_supplier_div">';
				woocommerce_wp_select(
					array(
						'id'          => "oimwc_select_supplier",
						'name'        => "oimwc_select_supplier",
						'label'       => __( 'Supplier', 'order-and-inventory-manager-for-woocommerce' ),
						'wrapper_class'	=>	"form-row form-row-full",
						'options'     => OIMWC_MAIN::get_supplier_list( true ),
						'desc_tip'    => 'false',
					)	
				);
				echo '<input type="button" class="button add_supplier" value="'.__('Add','order-and-inventory-manager-for-woocommerce').'">';
				echo '</div>';
				?>
				<div class="supplier_accordion_panel variable">
					<div class="supplier_accordion accordion">
					<?php if($supplier_id){ ?>
					<div class="accordion-inner" data-supplier_id="<?php echo $supplier_id;?>">
						<div class="link">
							<span class="supplier_title"><?php echo $old_supplier_name;?></span>
							<a href="#" class="remove_supplier delete"><?php _e('Remove','order-and-inventory-manager-for-woocommerce');?></a>
						</div>
						<div class="submenu">
							<div>
								<input type="hidden" name="oimwc_supplier_id[<?php echo "{$loop}";?>]" value="<?php echo $supplier_id; ?>" />
								<?php
								woocommerce_wp_text_input( array(
									'id'                => "oimwc_supplier_product_id_{$loop}",
									'name'				=> "oimwc_supplier_product_id[{$loop}]",
									'value'		  		=> esc_html($supplier_art_id),	
									'label'             => __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    		=> 'true',
									'description'       => __('Enter the product ID the supplier used for this product.', 'order-and-inventory-manager-for-woocommerce'),
									'wrapper_class'		=>	"form-row form-row-first $free_disabled silver_version",
									'custom_attributes' => array('data-name'=>'product_id')
									)
								);						        
								
								woocommerce_wp_text_input( array(
									'id'            => "oimwc_supplier_product_url_{$loop}",
									'name'			=> "oimwc_supplier_product_url[{$loop}]",	
									'value'		  	=> esc_html($supplier_product_url),	
									'label'         => __( 'Supplier Prod. URL', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    	=> 'true',
									'data_type'		=>	'url',
									'type'			=>	'text',
									'description'   => __('If the supplier has a direct URL to the product on their homepage, enter it here.', 'order-and-inventory-manager-for-woocommerce'),
									'wrapper_class'	=>	"form-row form-row-last $free_disabled silver_version",
									'custom_attributes' => array('data-name'=>'product_url')
								));	

								woocommerce_wp_text_input( array(
									'id'          => "oimwc_supplier_note_{$loop}",
									'name'		  => "oimwc_supplier_note[{$loop}]",	
									'value'		  => esc_html($supplier_note),	
									'label'       => __( 'Product Notes to Supplier', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    => true,
									'description' => __( 'In this field, you enter special characteristics about the product. It can be something the supplier Product ID does not inform the supplier of, ex. a special color or a unique packing of the product.', 'order-and-inventory-manager-for-woocommerce' ),
									'wrapper_class' => "form-row form-row-first $free_disabled silver_version",
									'custom_attributes' => array('data-name'=>'supplier_note')
								) );

								woocommerce_wp_text_input( array(
									'id'                => "oimwc_supplier_purchase_price_{$loop}",
									'name'             	=> "oimwc_supplier_purchase_price[{$loop}]",
									'value'				=> $purchase_price ? esc_html(wc_format_decimal($purchase_price,2)) : number_format(0,2),
									'label'             => __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ).$symbol,
									'desc_tip'    		=> 'false',
									'wrapper_class'	=>"form-row form-row-last $free_disabled silver_version",
									'data_type'			=>	'price',
									'custom_attributes' => array('data-name'=>'purchase_price')
									)
								);								

								woocommerce_wp_text_input( array(
									'id'            => "oimwc_supplier_pack_size_{$loop}",
									'name'          => "oimwc_supplier_pack_size[{$loop}]",
						            'value' 		=> esc_html($supplier_pack),
									'label'         => __( 'Supplier pack size', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    	=> 'true',
									'data_type'		=>	'stock',
						            'type'          =>'number',
                					'class' 		=> 'oimwc_restrict_characters_on_paste oimwc_allow_only_numbers',
						            'default' 		=> '1',
									'description'   => __('This sets the default value of your suppliers pack size. <br/>If most of your products come in 2-pack, enter the value "2".', 'order-and-inventory-manager-for-woocommerce'),
						            'wrapper_class'		=>	"form-row form-row-first $free_disabled $silver_disabled gold_version",
						            'custom_attributes' => array('data-name'=>'pack_size')
					        	));
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
					$supplier_data = $wpdb->get_results("SELECT * FROM {$tablename} WHERE variable_id = {$variation->ID}", ARRAY_A);
					//oimwc_print_data($supplier_data);
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
								<input type="hidden" id="<?php echo "additional_variable_suppliers_{$loop}_{$key}_supplier_id"; ?>" name="<?php echo "additional_variable_suppliers[{$loop}][{$key}][supplier_id]"; ?>" data-name="supplier_id" value="<?php echo $value['supplier_id'];?>">
								<?php
								woocommerce_wp_text_input( array(
									'name'				=> "additional_variable_suppliers[{$loop}][{$key}][product_id]",
									'id'				=> "additional_variable_suppliers_{$loop}_{$key}_product_id",
									'value'		  		=> esc_html($value['supplier_product_id']),	
									'label'             => __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    		=> 'true',
									'description'       => __('Enter the product ID the supplier used for this product.', 'order-and-inventory-manager-for-woocommerce'),
									'wrapper_class'		=>	"form-row form-row-first $free_disabled silver_version",
									'custom_attributes' => array('data-name'=>'product_id')
									)
								);						        
								
								woocommerce_wp_text_input( array(
									'name'				=> "additional_variable_suppliers[{$loop}][{$key}][product_url]",
									'id'				=> "additional_variable_suppliers_{$loop}_{$key}_product_url",
									'value'		  => esc_html($value['supplier_product_url']),	
									'label'         => __( 'Supplier Prod. URL', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    	=> 'true',
									'data_type'		=>	'url',
									'type'			=>	'text',
									'description'   => __('If the supplier has a direct URL to the product on their homepage, enter it here.', 'order-and-inventory-manager-for-woocommerce'),
									'wrapper_class'	=>	"form-row form-row-last $free_disabled silver_version",
									'custom_attributes' => array('data-name'=>'product_url')
								));	

								woocommerce_wp_text_input( array(
									'name'				=> "additional_variable_suppliers[{$loop}][{$key}][supplier_note]",
									'id'				=> "additional_variable_suppliers_{$loop}_{$key}_supplier_note",
									'value'		  => esc_html($value['product_notes']),	
									'label'       => __( 'Product Notes to Supplier', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    => true,
									'description' => __( 'In this field, you enter special characteristics about the product. It can be something the supplier Product ID does not inform the supplier of, ex. a special color or a unique packing of the product.', 'order-and-inventory-manager-for-woocommerce' ),
									'wrapper_class' => "form-row form-row-first $free_disabled silver_version",
									'custom_attributes' => array('data-name'=>'supplier_note')
								) );

								woocommerce_wp_text_input( array(
									'name'				=> "additional_variable_suppliers[{$loop}][{$key}][purchase_price]",
									'id'				=> "additional_variable_suppliers_{$loop}_{$key}_purchase_price",
									'value'				=> $value['purchase_price'] ? esc_html(wc_format_decimal($value['purchase_price'],2)) : number_format(0,2),
									'label'             => __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ).$new_symbol,
									'desc_tip'    		=> 'false',
									'wrapper_class'	=>"form-row form-row-last $free_disabled silver_version",
									'data_type'			=>	'price',
									'custom_attributes' => array('data-name'=>'purchase_price')
									)
								);								

								woocommerce_wp_text_input( array(
									'name'				=> "additional_variable_suppliers[{$loop}][{$key}][pack_size]",
									'id'				=> "additional_variable_suppliers_{$loop}_{$key}_pack_size",
						            'value' 		=> esc_html($value['pack_size']),
									'label'         => __( 'Supplier pack size', 'order-and-inventory-manager-for-woocommerce' ),
									'desc_tip'    	=> 'true',
									'data_type'		=>	'stock',
						            'type'          =>'number',
						            'default' 		=> '1',
									'description'   => __('This sets the default value of your suppliers pack size. <br/>If most of your products come in 2-pack, enter the value "2".', 'order-and-inventory-manager-for-woocommerce'),
						            'wrapper_class'		=>	"form-row form-row-first $free_disabled $silver_disabled gold_version",
						            'custom_attributes' => array('data-name'=>'pack_size')
					        	));
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
		<?php } ?> 
	</div>
</div>
<div class="clear"></div>