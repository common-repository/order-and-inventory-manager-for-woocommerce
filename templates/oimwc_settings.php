<?php
/**
 * Inventory main global settings page
 * 
 * This settings page handles license verification, pack size settings, default units, display of pack size, supplier default currency, shipping address
 *
 * @since 1.0.0
 */

$disabled_cls = $gold_disabled = '';
$upgrade_text = $upgrade_platinum_text = $disabled = $disabled_title = $platinum_disabled_title = $platinum_disabled = '';
if( (oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial()) || oimwc_fs()->is_plan_or_trial('silver',true) ){
    $disabled_cls = 'upgrade_notice_cls';
    $disabled = 'disabled';
    $disabled_title = 'disabled_title';
    $upgrade_text = sprintf('<div><span class="upgrade_text_cls">%s</span></div>',OIMWC_GOLD_UPGRDAE_NOTICE);
    $upgrade_platinum_text = sprintf('<div><span class="upgrade_text_cls">%s</span></div>',OIMWC_PLATINUM_UPGRDAE_NOTICE);
    $show_settings = $show_one_pack_settings = $enable_arrival_status = '';
    $oimwc_order_status_feature = $enable_packsize_invoice = 'no';
    $selected_posttypes = $access_roles = [];
}
if( oimwc_fs()->is_plan_or_trial('gold',true) ){
    $gold_disabled = 'upgrade_notice_cls';
    $platinum_disabled_title = 'platinum_disabled_title';
    $upgrade_platinum_text = sprintf('<div><span class="upgrade_text_cls">%s</span></div>',OIMWC_PLATINUM_UPGRDAE_NOTICE);
    $access_roles = [];
    $platinum_disabled = 'disabled';
}
?>
<?php include( OIMWC_TEMPLATE . 'top_area.php' ); ?>
<div class="wrap settings_main">
	<form name="im_settings_form" id="im_settings_form" action="" method="post">
		<div id="oimwc_settings_tabs">
		  	<ul>
		  		<label><?php esc_html_e('Settings','order-and-inventory-manager-for-woocommerce');?></label>
		    	<li><a href="#purchase_orders_tab"><?php esc_html_e('Purchase Orders','order-and-inventory-manager-for-woocommerce');?></a></li>
		    	<li><a href="#business_info_tab"><?php esc_html_e('Business Information','order-and-inventory-manager-for-woocommerce');?></a></li>
		    	<li><a href="#pack_size_tab"><?php esc_html_e('Pack Size','order-and-inventory-manager-for-woocommerce');?></a></li>
		    	<li><a href="#supplier_tab"><?php esc_html_e('Supplier','order-and-inventory-manager-for-woocommerce');?></a></li>
		    	<li><a href="#products_tab"><?php esc_html_e('Products','order-and-inventory-manager-for-woocommerce');?></a></li>
		    	<li><a href="#order_status_tab"><?php esc_html_e('Order Status','order-and-inventory-manager-for-woocommerce');?></a></li>
		    	<li><a href="#admin_search_bar_tab"><?php esc_html_e('Admin Search Bar','order-and-inventory-manager-for-woocommerce');?></a></li>
		    	<li><a href="#security_tab"><?php esc_html_e('Security','order-and-inventory-manager-for-woocommerce');?></a></li>
		  	</ul>
		  	<div class="tabs_content">
		  		<!-- Place empty <h2> tag, for display admin notice after <h2>-->
		  		<h2></h2>
			  	<div id="purchase_orders_tab">
				    <div class="inner_div shipping_address_panel">
		            <div class="po_odf_logo">
		            	<label><?php _e('Purchase Order File Format','order-and-inventory-manager-for-woocommerce'); ?></label><br />
		            	<div class="po_odf_logo_inner_div">
			            	<label><?php _e('Header Logo','order-and-inventory-manager-for-woocommerce'); ?></label>
			            	<div class="inner_div">
				            	<?php $image_id = get_option('oimwc_pdf_logo');
				            		if( $image = wp_get_attachment_image_src( $image_id, 'thumbnail') ) {
		 								echo '<img src="' . wp_get_attachment_url($image_id) . '" width="auto" height="'.$image[2].'" id="pdf_img" />
		 								  <a href="javascript:void(0);" class="upload_img button button-primary button-large" style="display:none">'.sprintf('Upload / Select Image','order-and-inventory-manager-for-woocommerce').'</a>
									      <a href="javascript:void(0);" class="remove_img button button-primary button-large">X</a>
									      <input type="hidden" name="pdf_logo" value="' . $image_id . '" class="pdf_logo">';
								 	} else {
									 	echo '<a href="javascript:void(0);" class="upload_img button button-primary button-large">'.sprintf('Upload / Select Image','order-and-inventory-manager-for-woocommerce').'</a>
										    <a href="javascript:void(0);" class="remove_img button button-primary button-large" style="display:none">X</a>
										    <input type="hidden" name="pdf_logo" value="" class="pdf_logo">';
									} ?>
							</div>
						</div>
		            </div>
		            <div class="po_pdf_color">
		            	<label><?php _e('Header Background Color','order-and-inventory-manager-for-woocommerce'); ?></label><br />
		            	<div class="inner_div">
		            		<?php $pdf_color = get_option('oimwc_pdf_color'); ?>
							<input id="pdf_color" name="pdf_color" value="<?php echo $pdf_color; ?>" data-jscolor="{format:'hex'}">
						</div>
		            </div>
		            <div class="po_pdf_title_color">
		            	<label><?php _e('Header Text Color','order-and-inventory-manager-for-woocommerce'); ?></label><br />
		            	<div class="inner_div">
		            		<?php $pdf_title_color = get_option('oimwc_pdf_title_color'); ?>
							<input id="pdf_title_color" name="pdf_title_color" value="<?php echo $pdf_title_color; ?>" data-jscolor="{format:'hex'}"> 
						</div>
		            </div>
		            <div class="po_pdf_email">
		            	<label><?php _e('Default reply-to Email Address','order-and-inventory-manager-for-woocommerce'); ?></label><br />
		            	<div class="inner_div">
		            		<?php $pdf_email = get_option('oimwc_pdf_email'); ?>
							<input type="text" id="pdf_email" name="pdf_email" value="<?php echo $pdf_email; ?>">
						</div>
		            </div>
		            <div class="po_pdf_email_title">
		            	<label><?php _e('Email Default Subject','order-and-inventory-manager-for-woocommerce'); ?></label><br />
		            	<div class="inner_div">
		            		<?php $pdf_email_title = get_option('oimwc_pdf_email_title'); ?>
							<input type="text" id="pdf_email_title" name="pdf_email_title" value="<?php echo $pdf_email_title; ?>">
						</div>
		            </div>
		            <div class="po_pdf_email_message">
		            	<label><?php _e('Email Default Message','order-and-inventory-manager-for-woocommerce'); ?></label><br />
		            	<div class="inner_div">
		            		<?php $pdf_email_message = get_option('oimwc_pdf_email_message'); ?>
							<textarea id="pdf_email_message" name="pdf_email_message" rows="4" cols="50"><?php echo $pdf_email_message; ?></textarea>
						</div>
		            </div>
		            </div>
				</div>
				<div id="business_info_tab">
					<div class="inner_div business_info_panel">
						<label><?php _e('Business Information','order-and-inventory-manager-for-woocommerce'); ?></label><br />
			  			<div class="office_address_info">
				  			<label><?php _e('Registered Office Address', 'order-and-inventory-manager-for-woocommerce') ?></label>
				  			<div class="add_office_address">
			                	<input type="button" name="add_office_address" id="add_office_address" class="button button-primary button-large" value="<?php echo __('Add Office Address','order-and-inventory-manager-for-woocommerce');?>" />
			                </div>
			            </div>
			            <div class="company_office_address_panel accordion">
		                	<?php
			                if(is_array($company_address) && count($company_address) > 0){
			                	foreach ($company_address as $key => $value)
			                	{		
							?>	
							<div class="accordion-inner">
								<div class="link">
			                		<i class="fa fa-chevron-right"></i>
			                		<input type="text" data-name="title" placeholder="<?php echo __('Add Office Address Name','order-and-inventory-manager-for-woocommerce');?>" value="<?php echo $value['title']?>">
			                		<a href="" class="button button-primary delete_add_btn" data-table="office_shipping_<?php echo $key;?>"><?php echo __('Delete Address', 'order-and-inventory-manager-for-woocommerce'); ?></a>
			                	</div>
							<table class="office_shipping_address submenu" id="office_shipping_<?php echo $key;?>">
								<tr>
			                        <td><?php _e('Company Name', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_company" id="im_company" value="<?php echo $value['im_company'] ? esc_html($value['im_company']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 1', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_address_line1" id="im_address_line1" value="<?php echo $value['im_address_line1'] ? esc_html($value['im_address_line1']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 2', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_address_line2" id="im_address_line2" value="<?php echo $value['im_address_line2'] ? esc_html($value['im_address_line2']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('City', 'order-and-inventory-manager-for-woocommerce'); ?></td>
			                        <td><input type="text" data-name="im_city" id="im_city" value="<?php echo $value['im_city'] ? esc_html($value['im_city']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('State / Province / Region', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_state" id="im_state" value="<?php echo $value['im_state'] ? esc_html($value['im_state']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Zip / Postal code', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_zip_code" id="im_zip_code" value="<?php echo $value['im_zip_code'] ? esc_html($value['im_zip_code']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Country', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td>
			                            <select data-name="im_country" id="im_country">
			                                <option value=""><?php _e('Select country', 'order-and-inventory-manager-for-woocommerce')?></option>
			                                <?php
			                                foreach ($countries as $key => $country) {
			                                	$selected = $value['im_country'] == $key ? 'selected' : '';
			                                    echo sprintf('<option %s value="%s">%s</option>', $selected, $key, __($country));
			                                }
			                                ?>
			                            </select>
			                        </td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Phone Number', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_phone" id="im_phone" value="<?php echo $value['im_phone'] ? esc_html($value['im_phone']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Fax Number', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_fax" id="im_fax" value="<?php echo $value['im_fax'] ? esc_html($value['im_fax']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Tax registration nr. / VAT', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_tax" id="im_tax" value="<?php echo $value['im_tax'] ? esc_html($value['im_tax']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Email Address', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_email" id="im_email" value="<?php echo $value['im_email'] ? esc_html($value['im_email']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Website', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_website" id="im_website" value="<?php echo $value['im_website'] ? esc_html($value['im_website']) : ""; ?>"></td>
			                    </tr>
							</table>
						</div>
						<?php }
							}else{
		                	?>
		                	<div class="accordion-inner">
		                		<div class="link">
			                		<i class="fa fa-chevron-right"></i>
			                		<input type="text" name="company_address[address_0][title]" data-name="title" placeholder="<?php echo __('Add Office Address Name','order-and-inventory-manager-for-woocommerce'); ?>">
			                		<a href="" class="button button-primary delete_add_btn" data-table="office_shipping_address_0"><?php echo __('Delete Address', 'order-and-inventory-manager-for-woocommerce'); ?></a>
			                	</div>
			                	<table class="office_shipping_address submenu" id="office_shipping_address_0">
			                		<tr>
			                        <td><?php _e('Company Name', 'order-and-inventory-manager-for-woocommerce'); ?></td>
			                        <td><input type="text" name="company_address[address_0][im_company]" data-name="im_company" id="im_company" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 1', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_address_line1" id="im_address_line1" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 2', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_address_line2" id="im_address_line2" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('City', 'order-and-inventory-manager-for-woocommerce'); ?></td>
			                        <td><input type="text" name="company_address[address_0][im_city]" data-name="im_city" id="im_city" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('State / Province / Region', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_state" id="im_state" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Zip / Postal code', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="company_address[address_0][im_zip_code]" data-name="im_zip_code" id="im_zip_code" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Country', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td>
			                            <select name="company_address[address_0][im_country]" data-name="im_country" id="im_country">
			                                <option value=""><?php _e('Select country', 'order-and-inventory-manager-for-woocommerce')?></option>
			                                <?php
			                                foreach ($countries as $key => $country) {
			                                    echo sprintf('<option value="%s">%s</option>', $key, __($country));
			                                }
			                                ?>
			                            </select>
			                        </td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Phone Number', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="company_address[address_0][im_phone]" data-name="im_phone" id="im_phone" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Fax Number', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="company_address[address_0][im_fax]" data-name="im_fax" id="im_fax" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Tax registration nr. / VAT', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="company_address[address_0][im_tax]" data-name="im_tax" id="im_tax" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Email Address', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="company_address[address_0][im_email]" data-name="im_email" id="im_email" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Website', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="company_address[address_0][im_website]" data-name="im_website" id="im_website" value=""></td>
			                    </tr>
			                	</table>
		                	</div>
		                <?php } ?>
		                </div>
			  		</div>
			  		<div class="inner_div shipping_address_panel">
				    	<div class="office_address_info">
			                <label><?php _e('Shipping address', 'order-and-inventory-manager-for-woocommerce') ?></label>
			                <div class="add_shipping_address">
			                	<input type="button" name="add_shipping_address" id="add_shipping_address" class="button button-primary button-large <?php echo "$disabled_cls gold_version";?>" value="<?php echo __('Add Shipping Address','order-and-inventory-manager-for-woocommerce');?>" />
			                </div>
			            </div>
		                <div class="company_shipping_address_panel accordion">
		                <?php
		                if(is_array($shipping_address) && count($shipping_address) > 0){
		                	foreach ($shipping_address as $key => $value)
		                	{		
						?>	
						<div class="accordion-inner">
		                	<div class="link">
		                		<i class="fa fa-chevron-right"></i>
		                		<input type="text" data-name="title" placeholder="<?php echo __('Add Address Name','order-and-inventory-manager-for-woocommerce');?>" value="<?php echo $value['title']?>">
		                		<a href="" class="button button-primary delete_add_btn <?php echo "$disabled_cls gold_version";?>" data-table="company_shipping_<?php echo $key;?>"><?php echo __('Delete Address', 'order-and-inventory-manager-for-woocommerce'); ?></a>
		                	</div>
			                <table class="company_shipping_address submenu" id="company_shipping_<?php echo $key;?>">
			                    <tr>
			                        <td><?php _e('Receiver', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_receiver" id="im_receiver" value="<?php echo $value['im_receiver'] ? esc_html($value['im_receiver']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Contact person', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_contact" id="im_contact" value="<?php echo $value['im_contact'] ? esc_html($value['im_contact']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 1', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_address1" id="im_address1" value="<?php echo $value['im_address1'] ? esc_html($value['im_address1']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 2', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_address2" id="im_address2" value="<?php echo $value['im_address2'] ? esc_html($value['im_address2']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('City', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" id="im_city" data-name="im_city" value="<?php echo $value['im_city'] ? esc_html($value['im_city']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('State / Province / Region', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_state" id="im_state" value="<?php echo $value['im_state'] ? esc_html($value['im_state']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Zip / Postal code', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_zip_code" id="im_zip_code" value="<?php echo $value['im_zip_code'] ? esc_html($value['im_zip_code']) : ""; ?>"></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Country', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td>
			                            <select data-name="im_country" id="im_country">
			                                <option value=""><?php _e('Select country', 'order-and-inventory-manager-for-woocommerce')?></option>
			                                <?php
			                                foreach ($countries as $key => $country) {
			                                    $selected = $value['im_country'] == $key ? 'selected' : '';
			                                    echo sprintf('<option %s value="%s">%s</option>', $selected, $key, __($country));
			                                }
			                                ?>
			                            </select>
			                        </td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Phone Number', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" data-name="im_phone" id="im_phone" value="<?php echo $value['im_phone'] ? esc_html($value['im_phone']) : ""; ?>"></td>
			                    </tr>
			                </table>
			            </div>
		                <?php
		                	}
		                }else{
		                	?>
		                <div class="accordion-inner">
		                	<div class="link">
		                		<i class="fa fa-chevron-right"></i>
		                		<input type="text" name="shipping_address[address_0][title]" data-name="title" placeholder="<?php echo __('Add Address Name','order-and-inventory-manager-for-woocommerce');?>">
		                		<a href="" class="button button-primary delete_add_btn <?php echo "$disabled_cls gold_version";?>" data-table="company_shipping_address_0"><?php echo __('Delete Address', 'order-and-inventory-manager-for-woocommerce'); ?></a>
		                	</div>
			                <table class="company_shipping_address submenu" id="company_shipping_address_0">
			                    <tr>
			                        <td><?php _e('Receiver', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="shipping_address[address_0][im_receiver]" data-name="im_receiver" id="im_receiver" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Contact person', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="shipping_address[address_0][im_contact]" data-name="im_contact" id="im_contact" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 1', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="shipping_address[address_0][im_address1]" data-name="im_address1" id="im_address1" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Address line 2', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="shipping_address[address_0][im_address2]" data-name="im_address2" id="im_address2" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('City', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" id="im_city" name="shipping_address[address_0][im_city]" data-name="im_city" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('State / Province / Region', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="shipping_address[address_0][im_state]" data-name="im_state" id="im_state" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Zip / Postal code', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="shipping_address[address_0][im_zip_code]" data-name="im_zip_code" id="im_zip_code" value=""></td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Country', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td>
			                            <select name="shipping_address[address_0][im_country]" data-name="im_country" id="im_country">
			                                <option value=""><?php _e('Select country', 'order-and-inventory-manager-for-woocommerce')?></option>
			                                <?php
			                                foreach ($countries as $key => $country) {
			                                    echo sprintf('<option value="%s">%s</option>', $key, __($country));
			                                }
			                                ?>
			                            </select>
			                        </td>
			                    </tr>
			                    <tr>
			                        <td><?php _e('Phone Number', 'order-and-inventory-manager-for-woocommerce') ?></td>
			                        <td><input type="text" name="shipping_address[address_0][im_phone]" data-name="im_phone" id="im_phone" value=""></td>
			                    </tr>
			                </table>
		                </div>
		                	<?php
		                }
		                ?>
		            </div>
					</div>
				</div>
			  	<div id="pack_size_tab">
			    	<div class="inner_div">
		                <label class="<?php echo $disabled_title;?>"><?php _e('Shop default pack size', 'order-and-inventory-manager-for-woocommerce');
		                echo $upgrade_text; ?>
		                </label>
		                <input type="text" name="default_our_pack_size" class="oimwc_restrict_characters_on_paste oimwc_allow_only_numbers" value="<?php echo $default_our_pack_size ? esc_html($default_our_pack_size) : 1 ?>" <?php echo $disabled; ?>>
		                <p class="description" id="tagline-description"><?php _e('This is how many units of a product you sell to your customers when they buy "1" item.<br/>If your default pack size is 2, then enter the value "2".', 'order-and-inventory-manager-for-woocommerce') ?>
		                </p>
		            </div>
		            <div class="inner_div">
		                <label class="<?php echo $disabled_title;?>"><?php _e('Supplier default pack size', 'order-and-inventory-manager-for-woocommerce');
		                	echo $upgrade_text;
		                    ?>
		                </label>
		                <input type="text" name="default_supplier_pack_size" class="oimwc_restrict_characters_on_paste oimwc_allow_only_numbers" value="<?php echo $default_supplier_pack_size ? esc_html($default_supplier_pack_size) : 1 ?>" <?php echo $disabled; ?>>
		                <p class="description" id="tagline-description"><?php _e('This sets the default value of your suppliers pack size. <br/>If most of your products come in 2-pack, enter the value "2".', 'order-and-inventory-manager-for-woocommerce') ?>
		                </p>
		            </div>
		            <div class="inner_div">
		                <label class="<?php echo $disabled_title;?> ">
		                    <?php _e('Show pack size info on product page', 'order-and-inventory-manager-for-woocommerce');
		                    echo $upgrade_text;
		                    ?>
		                </label>
		                <label class="switch">
		                <input name="show_pack_settings" type="checkbox" id="show_pack_settings" <?php echo $show_settings ? "checked" : "" ?> class="<?php echo "$disabled_cls gold_version"; ?>">
		                <span class="slider round"></span>
		                </label>
		                <p class="description"><?php _e('If you use different pack sizes on your products then we highly recommend you turn this option on!<br/>Are you only using one type of pack size on all your products then this option can be turned off.', 'order-and-inventory-manager-for-woocommerce') ?>
		                </p>
		            </div>
		            <div class="inner_div">
		                <label class="<?php echo $disabled_title;?> ">
		                    <?php _e('Hide 1-pack size information', 'order-and-inventory-manager-for-woocommerce');
		                    echo $upgrade_text; ?>
		                </label>
		                <label class="switch">
		                	<input name="show_one_pack_settings" type="checkbox" id="show_one_pack_settings" class="<?php echo "$disabled_cls gold_version"; ?>" <?php echo $show_one_pack_settings ? "checked" : "" ?>>
		                	<span class="slider round"></span>
		                </label>
		                <p class="description"><?php _e('Turning on this option will hide the pack size information on all products that have pack size set to "1".', 'order-and-inventory-manager-for-woocommerce') ?>
		                </p>
		            </div>
		            <div class="inner_div">
		                <label class="<?php echo $disabled_title;?> ">
		                    <?php esc_html_e("Show pack size in printouts", "order-and-inventory-manager-for-woocommerce");
		                    echo $upgrade_text; ?>
		                </label>
		                <label class="switch">
		                <input type="checkbox" name="enable_packsize_in_invoice" class="<?php echo "$disabled_cls gold_version"; ?>" value="1" <?php echo $enable_packsize_invoice == 'yes' ? 'checked' : ''; ?>>
		                <span class="slider round"></span>
		                </label>
		                <p class="description"><?php echo __('This options add the product pack size to common printouts such as invoices and recites.','order-and-inventory-manager-for-woocommerce'); ?>
		                </p>
		            </div>
			  	</div>
			  	<div id="supplier_tab">
			    	<div class="inner_div">
		                <label><?php _e('Default supplier currency', 'order-and-inventory-manager-for-woocommerce') ?></label>
		                <div class="forminp">
		                    <select id="default_supplier_currency" name="default_supplier_currency" data-placeholder="<?php esc_attr_e('Choose a currency&hellip;', 'order-and-inventory-manager-for-woocommerce'); ?>" class="location-input wc-enhanced-select dropdown">
		                        <option value=""><?php esc_html_e('Choose a currency&hellip;', 'order-and-inventory-manager-for-woocommerce'); ?></option>
		                        <?php
		                        asort($currencies);
		                        foreach ($currencies as $code => $name) :
		                            ?>
		                            <option value="<?php echo esc_attr($code); ?>" <?php selected($default_supplier_currency, $code); ?>>
		                                <?php printf(esc_html__('%1$s (%2$s)', 'woocommerce'), $name, get_woocommerce_currency_symbol($code)); ?>
		                            </option>
		                        <?php endforeach; ?>
		                    </select>
		                </div>
		            </div>
				</div>
				<div id="products_tab">
				    <div class="inner_div">
		                <label class="">
		                    <?php esc_html_e("Show GTIN number on the product page.", "order-and-inventory-manager-for-woocommerce"); ?>
		                </label>
		                <label class="switch">
		                <input type="checkbox" name="show_gtin_number" class="show_gtin_number" value="1" <?php echo $show_gtin_number ? 'checked' : '';?>>
		                <span class="slider round"></span>
		                </label>
		                <!-- <p class="description"><?php //echo __('Show GTIN Number on front product page','order-and-inventory-manager-for-woocommerce'); ?></p> -->
		            </div>	
		            <?php if(is_plugin_active( 'product-gtin-ean-upc-isbn-for-woocommerce/product-gtin-ean-upc-isbn-for-woocommerce.php' ) || is_plugin_active( 'seo-by-rank-math-pro/rank-math-pro.php' ) || is_plugin_active( 'woo-add-gtin/woocommerce-gtin.php' ) ){ ?>
		            	<div class="inner_div">
			                <label>
			                    <?php esc_html_e("Disable OIMWC GTIN fields if another enabled plugin manages it (recommended)", "order-and-inventory-manager-for-woocommerce"); ?>
			                </label>
			                <label class="switch">
			                <input type="checkbox" name="disable_oimwc_gtin" class="disable_oimwc_gtin <?php echo "gold_version"; ?>" value="1" <?php echo $disable_gtin_fields ? 'checked' : ''; ?>>
			                <span class="slider round"></span>
			                </label>
			                <p class="description"><?php echo __('Disabling OIMWC GTIN function will hide all its GTIN related information/input fields on both in admin and on product pages.','order-and-inventory-manager-for-woocommerce'); ?>
			                </p>
		            	</div>
		            <?php }
		            else {
		            	update_option('disable_oimwc_gtin_fields', '');
		            } ?>	            
		            <div class="inner_div">
	                    <label class="">
	                    	<?php _e('Default product unit type', 'order-and-inventory-manager-for-woocommerce') ?>
	                    </label>
                        <select id="im_units" name="im_units">

                            <?php
                            //$units = get_option('oimwc_units');
                            if ($units) {
                                foreach ($units as $key => $value) {
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php
                                    if ($key == $default_unit) {
                                        echo "selected";
                                    }
                                    ?>><?php echo __($key, 'order-and-inventory-manager-for-woocommerce') . "/" . __($value, 'order-and-inventory-manager-for-woocommerce'); ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                        </select>
                        <p class="description"><?php _e('This will be the defualt unit for all products.', 'order-and-inventory-manager-for-woocommerce') ?></p>
                	</div>
		            <div class="inner_div">
		            	<div>
		                    <label><?php _e('Units', 'order-and-inventory-manager-for-woocommerce') ?></label>
		                    <td><a href="javascript:void(0);" class="button button-primary button-large add_unit_btn"><?php _e('Add new unit', 'order-and-inventory-manager-for-woocommerce') ?></a></td>
		                </div>
		                <div>
		                    <table id="display_units">
		                        <tr>
		                            <th><?php _e('Singular', 'order-and-inventory-manager-for-woocommerce') ?></th>
		                            <th><?php _e('Plural', 'order-and-inventory-manager-for-woocommerce') ?></th>
		                        </tr>
		                        <tr>
		                            <td class="default_unit">
		                                <input type="text" disabled class="singular_unit" value="<?php _e('piece', 'order-and-inventory-manager-for-woocommerce') ?>">
		                            </td>
		                            <td>
		                                <input type="text" disabled class="singular_unit" value="<?php _e('pieces', 'order-and-inventory-manager-for-woocommerce') ?>">
		                            </td>

		                        </tr>
		                        <?php
		                        if ($units) {
		                            array_shift($units);
		                            foreach ($units as $key => $value) {
		                                ?>
		                                <tr>
		                                    <td>
		                                        <input type="text" name="singular_unit[]" class="singular_unit" placeholder="<?php _e('Ex. meter', 'order-and-inventory-manager-for-woocommerce') ?>" value="<?php echo esc_html($key); ?>">
		                                    </td>
		                                    <td>
		                                        <input type="text" name="plural_unit[]" class="plural_unit" placeholder="<?php _e('Ex. meters', 'order-and-inventory-manager-for-woocommerce') ?>" value="<?php echo esc_html($value); ?>">
		                                    </td>
		                                    <td>
		                                        <a href="javascript:void(0);" class="unit_delete button button-primary button-large"><?php _e('Delete', 'order-and-inventory-manager-for-woocommerce') ?></a>
		                                    </td>
		                                </tr>
		                                <?php
		                            }
		                        }
		                        ?>
		                    </table>
		                </div>
		                <div id="unit_save_tr">
		                    <a href="javascript:void(0);" class="unit_save button button-primary button-large"><?php _e('Save units', 'order-and-inventory-manager-for-woocommerce') ?></a>
		                </div>
		            </div>

		            <div class="inner_div">
		                <label>
		                    <?php esc_html_e("Stock log limitation", "order-and-inventory-manager-for-woocommerce"); ?>
		                </label>
		                <input type="number" name="stock_log_limitation" class="oimwc_restrict_characters_on_paste oimwc_allow_only_numbers" value="<?php echo $stock_log_limitation; ?>" /> <?php _e( 'Month/s', 'order-and-inventory-manager-for-woocommerce' ) ?> 
		                <p class="description"><?php echo __('This will be the defualt stock history limit for all products.','order-and-inventory-manager-for-woocommerce'); ?>
		                </p>
		            </div>

		            <div class="inner_div">
		                <label class="<?php echo $disabled_title;?> ">
		                    <?php esc_html_e("Show estimated arrival status", "order-and-inventory-manager-for-woocommerce");
		                    echo $upgrade_text; ?>
		                </label>
		                <label class="switch">
		                <input type="checkbox" name="enable_arrival_status" class="<?php echo "$disabled_cls gold_version"; ?>" value="1" <?php echo $enable_arrival_status ? 'checked' : ''; ?>>
		                <span class="slider round"></span>
		                </label>
		                <p class="description"><?php echo __('This option shows the estimated arrival date status on product page.','order-and-inventory-manager-for-woocommerce'); ?>
		                </p>
		            </div>
				</div>
				<div id="order_status_tab">
					<div class="inner_div">
		                <label class="<?php echo "$disabled_title";?>">
		                    <?php _e('Enable order status feature','order-and-inventory-manager-for-woocommerce');
		                    echo $upgrade_text; ?>
		                </label>
		                <label class="switch">
		                <input type="checkbox" id="oimwc_order_status_feature" name="oimwc_order_status_feature" class="<?php echo "$disabled_cls gold_version"; ?>" value="yes" <?php echo $oimwc_order_status_feature == "yes" ? "checked" : ""?>>
		                <span class="slider round"></span>
		                </label>
		            </div>

					<div class="inner_div">
		                <label class="<?php echo "$disabled_title";?> ">
		                    <?php esc_html_e("Custom Order Statuses that reserve products", "order-and-inventory-manager-for-woocommerce");
		                    echo $upgrade_text; ?>
		                </label>
		                <?php $order_status = wc_get_order_statuses(); ?>
		                <select name="custom_order_status_dd[]" id="custom_order_status_dd" style="width: 50%" multiple="multiple" <?php echo "$disabled"; ?>>
		                    <?php 
		                        if(is_array($order_status) && count($order_status) > 0){
		                            unset($order_status['wc-completed']);
		                            unset($order_status['wc-cancelled']);
		                            unset($order_status['wc-refunded']);
		                            unset($order_status['wc-failed']);
		                            unset($order_status['wc-pending']);

		                            foreach ($order_status as $key => $status) {
		                                if(in_array($key, $selected_order_status)){
		                                    $selected = "selected";
		                                }else{
		                                   $selected =  "";
		                                }
		                                echo sprintf('<option value="%s" %s>%s</option>', $key,$selected, $status);
		                            }
		                        }
		                    ?>
		                </select>
		                <p class="description">
		                    <?php echo __("If you use custom order statuses that reserve products, enter them here.<br/> This is used to calculate the correct physical order stock qty. Typical order statuses that don't reserve products are completed, refunded and failed.",'order-and-inventory-manager-for-woocommerce'); ?>
		                </p>
		            </div>

		            <div class="inner_div">
		                <label class="<?php echo "$disabled_title";?> ">
		                    <?php esc_html_e("Custom Order Statuses that reduce physical stock", "order-and-inventory-manager-for-woocommerce");
		                    echo $upgrade_text; ?>
		                </label>
		                <?php $order_status = wc_get_order_statuses(); ?>
		                <select name="reduce_physical_stock_OStatus[]" id="reduce_physical_stock_OStatus" style="width: 50%" multiple="multiple" <?php echo "$disabled"; ?>>
		                    <?php 
		                        if(is_array($order_status) && count($order_status) > 0){
		                            $remove_status = array('wc-pending','wc-cancelled','wc-refunded','wc-failed','wc-processing','wc-on-hold','wc-completed');
		                            $order_status = array_diff_key($order_status, array_flip($remove_status));

		                            foreach ($order_status as $key => $status) {
		                                if(in_array($key, $reduce_physical_stock_OStatus)){
		                                    $selected = "selected";
		                                }else{
		                                   $selected =  "";
		                                }
		                                echo sprintf('<option value="%s" %s>%s</option>', $key,$selected, $status);
		                            }
		                        }
		                    ?>
		                </select>
		                <p class="description">
		                    <?php echo __("If you use custom order statuses that reduce physical stock, enter them here.<br/> Typical order statuses that don't reduce physical stock are processing, pending and canceled, etc.",'order-and-inventory-manager-for-woocommerce'); ?>
		                </p>
		            </div>
				</div>
				<div id="admin_search_bar_tab">
				    <div class="inner_div">
		                <label class="">
		                    <?php esc_html_e("Display search bar", "order-and-inventory-manager-for-woocommerce"); ?>
		                </label>
		                <label class="switch">
						<input type="checkbox" name="enable_admin_seachbar" class="" value="1" <?php echo $enable_wpsearch ? 'checked' : '';?>>
		                <span class="slider round"></span>
		                </label>
		                <p class="description"><?php echo __('Adds a search bar in the top admin bar','order-and-inventory-manager-for-woocommerce'); ?>
		                </p>
		            </div>
		            <div class="inner_div">
		                <label class="<?php echo $disabled_title;?> ">
		                    <?php esc_html_e("Choose enabled post types in the search bar", "order-and-inventory-manager-for-woocommerce");
		                    echo $upgrade_text; ?>
		                </label>
		                <div class="pst_search_chk">
		                    <?php
		                    $types = get_post_types();
		                    unset($types['attachment']);
		                    if(!is_array($selected_posttypes)){
		                        $selected_posttypes = array();
		                    }
		                    $all_posttypes = get_option('oimwc_all_search_posttypes');
		                    if(is_array($all_posttypes) && count($all_posttypes) > 0){
		                        $types = $all_posttypes;
		                    }
		                    foreach ($types as $key => $value) {
		                        $selected = in_array($key, $selected_posttypes) ? "checked" : "";
		                        $obj = get_post_type_object($key);
		                        $label = $obj->labels->singular_name;
		                        $searchable = $obj->show_ui;
		                        //$disabled = ($key == "post" || $key == "page") ? "disabled" : "";
		                        if ($searchable == 1 ) {
		                            ?>
		                            <div class="">
		                                <span class="fas fa-sort"></span>
		                                <label class="switch">
		                                <input name="search_types[]" type="checkbox" class="<?php echo "$disabled_cls gold_version"; ?>" value="<?php echo $key ?>" <?php echo $selected ?>>
		                                <span class="slider round"></span>
		                                <input type="hidden" name="all_posttypes[<?php echo $key ?>]" value="<?php echo $key ?>">
		                                </label>
		                                <span class="pst_search_title"><?php esc_html_e($label, "wpsearch"); ?></span>
		                            </div>
		                            <?php
		                        }
		                    }
		                    ?>
		                    <p class="description"><?php _e('You can reorder post types in a list using mouse. You can make default post type by moving it to first.','order-and-inventory-manager-for-woocommerce');?>
		                    </p>
		                </div>
		            </div>
				</div>
				<div id="security_tab">
				    <div class="inner_div">
		                <label class="<?php echo "$disabled_title $platinum_disabled_title";?> ">
		                    <?php esc_html_e("Access Role", "order-and-inventory-manager-for-woocommerce");
		                    echo $upgrade_platinum_text; ?>
		                </label>
		                <div class="access_role_cls">
		                    
		                    <?php foreach (get_editable_roles() as $role_name => $role_info): ?>
		                        <?php 
		                            if(is_array($access_roles) && in_array($role_name,$access_roles)){
		                                $selected = 'checked';
		                            }
		                            else
		                            {
		                                $selected = '';
		                            }
		                            
		                            if( (oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial()) || oimwc_fs()->is_plan_or_trial('silver',true) || oimwc_fs()->is_plan_or_trial('gold',true) ){
		                                if( $role_name != 'customer' ){
		                                    $selected = 'checked';
		                                }
		                            }

		                            if( $role_name != 'administrator' ){
		                        ?>
		                        <label class="switch">
		                            <input name="access_roles[]" type="checkbox" value="<?php echo $role_name; ?>" <?php echo $selected;?> class="<?php echo "$disabled_cls $gold_disabled platinum_version"; ?>">
		                            <span class="slider round"></span>
		                        </label>
		                        <span class="">
		                        <?php echo $role_info['name']; ?>
		                        </span>
		                        <br/>
		                    <?php } ?>
		                      <?php endforeach; ?>
		                        <p class="description">
		                            <?php echo __('Choose what user roles should have access to this plugin. Administrator always have the access by default.','order-and-inventory-manager-for-woocommerce'); ?>
		                        </p>
		                </div>
		            </div>

		            <div class="inner_div">
		                <label class="<?php echo "$disabled_title $platinum_disabled_title";?> ">
		                    <?php esc_html_e("Grant Access User Wise", "order-and-inventory-manager-for-woocommerce");
		                    echo $upgrade_platinum_text; ?>
		                </label>
		                <div class="user_wise_access">
		                        <input type="text" name="txt_access_users" id="txt_access_users" placeholder="<?php echo __('Search...','order-and-inventory-manager-for-woocommerce');?>" class="ui-autocomplete-input" autocomplete="off" <?php echo "$disabled $platinum_disabled"; ?>>
		                        <input type="button" name="btn_add_user_access" id="btn_add_user_access" class="button button-primary" data-user_id="" value="<?php _e('Add User','order-and-inventory-manager-for-woocommerce');?>" <?php echo "$disabled $platinum_disabled"; ?>>
		                        <h4><?php echo __('Granted Users','order-and-inventory-manager-for-woocommerce');?></h4>
		                        <div class="user_list_panel">
		                            <ul class="ul_selected_access_user">
		                                <?php $users = get_option('oimwc_user_access_list'); 
		                                if(is_array($users) && count($users) > 0){
		                                    foreach ($users as $key => $user_id) {
		                                        $user_obj = get_user_by('ID',$user_id);
		                                        $user_data = $user_obj->display_name.' ('.$user_obj->user_email.')';
		                                        printf( '<li class="li_selected_access_user" data-user_id="%1$d"><div>
		                                            %2$s</div><input type="hidden" name="access_user_list[]" value="%1$d"></li>', $user_id, $user_data );
		                                    }
		                                }
		                                else
		                                {
		                                    echo '<li class="no_user_row">'.__('No users Found.','order-and-inventory-manager-for-woocommerce').'</li>';
		                                }
		                                ?>
		                            </ul>
		                        </div>
		                        <input type="button" name="btn_remove_user_access" id="btn_remove_user_access" class="button button-primary" data-user_id="" value="<?php _e('Remove Selected User','order-and-inventory-manager-for-woocommerce');?>" <?php echo "$disabled $platinum_disabled"; ?>>
		                </div>
		            </div>
				</div>
		
				<div class="setting_btns">
					<input type="hidden" name="action" value="save_inventory_settings_callback">
					<?php wp_nonce_field( 'oimwc_settings_nonce', 'oimwc_settings_nonce_field' ); ?>
					<input type="button" name="save_inventory_settings" id="save_inventory_settings" value="<?php _e('Save', 'order-and-inventory-manager-for-woocommerce') ?>" class="button button-primary button-large">
					<div class="lw_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
					<div class="success_icon"></div>
				</div>
			</div>
		</div>
	</form>
</div>