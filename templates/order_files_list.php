<?php
/**
 * Displays purchased orders list 
 * 
 * Purchased order list which displays supplier name, order date, total ordered products and purchase price.
 *
 * @since 1.0.0
 */
$main_obj = OIMWC_MAIN::init();
if(isset($_GET['view_order']) && sanitize_text_field($_GET['view_order']) == 1 && isset($_GET['supplier']) && isset($_GET['date'])) {
    $main_obj->view_order_product_listing();
    return;
}
?>
<?php include( OIMWC_TEMPLATE . 'top_area.php' ); ?>
<div class="PO_tab_panel">
    <?php include( OIMWC_TEMPLATE . 'navigation.php' ); ?>
</div>
<div class="wrap purchase_orders_wrap">
	<div class="IO_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
	<?php if( is_array($supplier_list) && count($supplier_list) > 0 ){
		$supplier_list = array_unique($supplier_list);
	?>
	<div class="po_supplier_filter_panel">
		<span class="IO_supplier_filter_span">
		<select name="po_supplier_filter" id="po_supplier_filter">
			<?php
				$supplier_name_arr = [];
				echo '<option value="">'.__('All Suppliers','order-and-inventory-manager-for-woocommerce').'</option>';
				foreach ($supplier_list as $key => $supplier_id) {
					$supplier_name_arr[$supplier_id] = get_post_meta($supplier_id,'oimwc_supplier_short_name',true);
				}
				asort($supplier_name_arr);
				foreach ($supplier_name_arr as $key => $value) {
					echo '<option value="'.$key.'">'.$value.'</option>';
				}
			?>		
		</select>
		<div class="lw_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
		</span>
	</div>
	<?php } ?>
	<h2></h2>
	<div class="order_product_files oimwc-table-shadow" data-pagination="">
        <div>
        	<?php
			$finalize_order = false;
			if( isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) == 'purchase-orders' && isset($_GET['tab']) && sanitize_text_field($_GET['tab']) == 'finalize_orders' ){
				$finalize_order = true;
			}
        	?>
			<table class="wp-list-table widefat fixed striped toplevel_page_order-inventory-management">
				<thead>
					<tr>
						<th scope="col" class="sortable" id="order_number">
							<a href="">
								<?php _e('No.' ,'order-and-inventory-manager-for-woocommerce');?>
								<span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" class="sortable" id="supplier">
							<a href="">
								<?php _e('Supplier','order-and-inventory-manager-for-woocommerce')?>
								<span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" class="sortable" id="order_date">
							<a href="">
								<?php _e('Created','order-and-inventory-manager-for-woocommerce');?>
								<span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" class="sortable" id="total_products">
							<a href="">
								<?php _e('Products','order-and-inventory-manager-for-woocommerce')?><span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" class="sortable" id="subtotal">
							<a href="">
								<?php _e('Subtotal','order-and-inventory-manager-for-woocommerce')?>
								<span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" class="sortable" id="arrived_products">
							<a href="">
								<?php _e('Arrived','order-and-inventory-manager-for-woocommerce')?>
								<span class="sorting-indicator"></span>
							</a>
						</th>
						<?php
						if( !$finalize_order ){ 
							echo '<th scope="col" class="sortable" id="status"><a href="">'.__('Status','order-and-inventory-manager-for-woocommerce').'<span class="sorting-indicator"></span></a></th>';
						} ?>
						<?php
						/*if($finalize_order){ 
							echo '<th>'.__('Estimated Arrival','order-and-inventory-manager-for-woocommerce').'</th>';
						}*/ ?>
						<th></th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php
					if( is_array( $remaning_orders ) && count( $remaning_orders ) ){
						$order_status = array();
						foreach( $remaning_orders as $remaning_order ){
							
							$supplier_name = get_post_meta($remaning_order->supplier_id,'oimwc_supplier_short_name',true);
							
							$total_products = OIMWC_Order::get_total_products( array( 'supplier_id' => $remaning_order->supplier_id, 'order_date' => $remaning_order->order_date ) );
							$total_purchase = OIMWC_Order::get_total_purchase_amount( array( 'supplier_id' => $remaning_order->supplier_id, 'order_date' => $remaning_order->order_date ) );	

							if($remaning_order->order_date){
			                	$order_date = date_i18n( get_option( 'date_format' ).' H:i:s', strtotime($remaning_order->order_date ) );
			                }	
			                $supplier_order_url = get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_order_url', true );
			                $supplier_name = $supplier_order_url ? '<a href="'.$supplier_order_url.'" target="_blank">'.$supplier_name.'</a>' : $supplier_name;

							echo sprintf( '<tr><td class="col-order_number column-primary" scope="row" data-label="No.">%d</td><td class="col-supplier column-primary" data-label="Supplier">%s</td><td class="col-order_date column-primary" data-label="Created">%s</td><td class="col-total_products" data-label="Products">%s</td><td class="col-total_purchase" data-label="Subtotal">%s</td><td class="expanded-td column-primary"><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>', 
								esc_html($remaning_order->order_number),
								sprintf($supplier_name),
								esc_html($order_date),
								esc_html($total_products),
								$total_purchase);

							$total_awaiting_products = OIMWC_Order::get_awaiting_delivery_products( array( 'supplier_id' => $remaning_order->supplier_id, 'order_date' => $remaning_order->order_date ) );
							echo '<td class="col-arrived" data-label="Arrived">'.esc_html($total_awaiting_products.' / '.$total_products).'</td>';

							if( !$finalize_order ){ 
								if( !isset($order_status[$remaning_order->supplier_id] ) || !isset($order_status[$remaning_order->supplier_id][$remaning_order->order_date] ) ){
									$order_status[$remaning_order->supplier_id][$remaning_order->order_date] = $main_obj->get_purchase_order_status($remaning_order->supplier_id, $remaning_order->order_date);
								}
								echo '<td data-label="Status">'.$order_status[$remaning_order->supplier_id][$remaning_order->order_date].'</td>';
							}
							
			                $arrival_date = $remaning_order->arrival_date ? date_i18n( get_option( 'date_format' ), strtotime($remaning_order->arrival_date) ) : '';

			                /*if($finalize_order){
			                	$arrival_date = $arrival_date ? $arrival_date : '-';
								echo '<td class="col-arrival_date">'.esc_html($arrival_date).'</td>';
							}*/

							$po_default_cols = get_post_meta($remaning_order->supplier_id,'oimwc_default_po_settings',true);
							$po_default_setting_flag = get_post_meta($remaning_order->supplier_id,'oimwc_default_po_settings_flag',true);
							$po_default_lang = get_post_meta($remaning_order->supplier_id,'oimwc_download_po_lang',true);

							$default_cols = $po_default_setting_flag ? json_encode($po_default_cols) : '';
							$po_default_lang = $po_default_setting_flag ? $po_default_lang : '';
							$default_ship_add = get_post_meta($remaning_order->supplier_id,'oimwc_supplier_shipping_address',true);
							$default_ship_add = $default_ship_add ? $default_ship_add : '';
							$download_pdf = get_post_meta($remaning_order->supplier_id, 'oimwc_download_po_file', true);
							$download_pdf = $po_default_setting_flag ? $download_pdf : '';
							$supplier_email = (!empty(get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_order_email', true ))) ? get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_order_email', true ) : get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_email', true );
					    	$supplier_name = get_the_title($remaning_order->supplier_id);

					    	$po_email_subject = get_option('oimwc_pdf_email_title');
					    	$po_email_subject = (!empty($po_email_subject)) ? $po_email_subject : __('Purchase Order PO from Order & inventory Manager','order-and-inventory-manager-for-woocommerce');
					    	$po_email_message_default = stripslashes(get_option('oimwc_pdf_email_message'));
					    	$order_id = ($remaning_order->order_number != '') ? $remaning_order->order_number : 0;

					        include(OIMWC_TEMPLATE.'supplier_email.php');
					        $po_email_message = (!empty($po_email_message_default)) ? $po_email_message_default : $po_email_message;
					        $save_default_sett = get_post_meta($remaning_order->supplier_id,'oimwc_default_po_settings_flag',true);
					        $save_default_sett = (!empty($save_default_sett)) ? 1 : 0;
					        $delivery_date = get_post_meta($remaning_order->supplier_id,'oimwc_delivery_date',true);
					        $delivery_date = $po_default_setting_flag ? $delivery_date : '';
					        $shipping_method = get_post_meta($remaning_order->supplier_id,'oimwc_shipping_method',true);
					        $shipping_method = $po_default_setting_flag ? $shipping_method : '';
					        $shipping_terms = get_post_meta($remaning_order->supplier_id,'oimwc_shipping_terms',true);
					        $shipping_terms = $po_default_setting_flag ? $shipping_terms : '';
					        $po_attn = get_post_meta($remaning_order->supplier_id,'oimwc_po_attn',true);
					        $po_attn = $po_default_setting_flag ? $po_attn : '';
					        $orderDate = str_replace(array(' ', ':'), '-', $remaning_order->order_date);
					        $attach_filename = $remaning_order->supplier_id.'_'.$orderDate;

							/** PO action buttons **/
							printf( '<td class="po_action_btns" data-supplier_id="%s" data-order_date="%s" data-default_cols=\'%s\' data-default_po_lang="%s" data-label="" data-default_ship_address="%s" data-download_pdf="%s" data-supplier-email="%s" data-email-subject="%s" data-email-message="%s" data-default_save_flag="%s" data-delivery_date="%s" data-shipping_method="%s" data-shipping_terms="%s" data-po_attn="%s" data-file_name="%s">', 
								$remaning_order->supplier_id, 
								strtotime( $remaning_order->order_date ),
								$default_cols,
								$po_default_lang,
								$default_ship_add,
								$download_pdf,
								$supplier_email,
								$po_email_subject,
								$po_email_message,
								$save_default_sett,
								$delivery_date,
								$shipping_method,
								$shipping_terms,
								$po_attn,
								$attach_filename );

							display_po_buttons($remaning_order,$finalize_order,$arrival_date);
	                    	echo '</td></tr>';
	                    	/**** **** **** ***/
						}
					}else{
						echo sprintf( '<tr><td colspan="7">%s</td></tr>', __('No current purchase order files.', 'order-and-inventory-manager-for-woocommerce') );
					}
					?>

				</tbody>
			</table>
			<form>
				<input type="hidden" name="completed_po_page" id="completed_po_page" value="1">
				<input type="hidden" name="po_subpage" id="po_subpage" value="<?php echo $finalize_order ? 'finalize_orders' : 'active_orders'; ?>">
				<input type="hidden" name="completed_po_total_pages" id="completed_po_total_pages" value="<?php echo $total_pages;?>">
			</form>
			<div class="lw_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
		</div>
	</div>
	<?php include( OIMWC_TEMPLATE . 'info_modal.php' ); ?>
</div>

<?php
	function display_po_buttons($remaning_order,$finalize_order,$arrival_date){

		if( !isset($_GET['view_order']) ){
			$view_order_url = admin_url().'admin.php?page=order-inventory-management';
	        $view_order_url = add_query_arg(
				array(
					'subpage' => 'purchase-orders',
					'view_order' => 1,
					'supplier' => $remaning_order->supplier_id,
					'date' => strtotime( $remaning_order->order_date )

				),$view_order_url);
			
			echo '<a class="button wc-action-button tips go_to_order" href="'.esc_url($view_order_url).'" data-tip="'.__('Go to order','order-and-inventory-manager-for-woocommerce').'"><i class="fas fa-arrow-right"></i></a>';
		}

		$additional_info = $remaning_order->additional_information;
		$add_info_cls = $remaning_order->additional_information ? 'additional_info_btn' : '';
		
		echo '<a class="wc-action-button button add_info_po_btn tips '.$add_info_cls.'" data-add_info="'.esc_html($additional_info).'" data-supplier_id="'.$remaning_order->supplier_id.'" data-order_date="'.strtotime( $remaning_order->order_date ).'" data-tip="'.__('Additional Information in PO','order-and-inventory-manager-for-woocommerce').'"><i class="fas fa-clipboard"></i></a>';

		$private_cls = $remaning_order->private_note ? 'private_note_btn' : '';
		echo '<a class="wc-action-button button add_private_note_btn tips '.$private_cls.'" data-private_note="'.esc_html($remaning_order->private_note).'" data-supplier_id="'.$remaning_order->supplier_id.'" data-order_date="'.strtotime( $remaning_order->order_date ).'" data-tip="'.__('Private Notes','order-and-inventory-manager-for-woocommerce').'"><i class="far fa-sticky-note"></i></a>';

		echo '<a target="_blank" class="wc-action-button button tips download_po_btn" data-tip="'.__('Process Purchase Order','order-and-inventory-manager-for-woocommerce').'"><i class="fas fa-file-alt"></i></a>';

		$lock_product = $remaning_order->lock_product;
    	$disabled = $lock_product ? 'disabled' : '';
    	$style = $lock_product ? "pointer-events:none" : "";
		$main_obj = OIMWC_MAIN::init();
		$lock_status = $main_obj->get_purchase_order_status($remaning_order->supplier_id, $remaning_order->order_date);
		if($lock_status == __('Receiving','order-and-inventory-manager-for-woocommerce'))
		{
			$disable_lock = 'disabled';
			$cls = 'fa fa-lock';
		}
		else
		{
			$disable_lock = '';
			$cls = 'fa fa-unlock';
		}

        $lock_title = $lock_product ? __('Unlock Order And Change Status to Pending','order-and-inventory-manager-for-woocommerce') : __('Lock Order And Change Status to Ordered','order-and-inventory-manager-for-woocommerce');
        if( !$finalize_order ){
            echo '<a class="button wc-action-button lock_order_btn tips '.$disable_lock.'" data-lock="1" data-tip="'.$lock_title.'"><i class="'.$cls.'"></i></a>';
        }
       
       	if( !$finalize_order ){
			echo '<div class="tips" data-tip="'.__('Estimated Arrival Date','order-and-inventory-manager-for-woocommerce').'"><input type="text" placeholder="'.__('ETA','order-and-inventory-manager-for-woocommerce').'" class="arrival_date_cls button '.$disable_lock.'" value="'.esc_html($arrival_date).'" style="'.$style.'" '.$disabled.'></div>';
			echo '<div class="tips" data-tip="'.__('Cancel Order','order-and-inventory-manager-for-woocommerce').'"><input type="button" class="button cancel_awaiting_order '.$disable_lock.'" value="'.__('Cancel','order-and-inventory-manager-for-woocommerce').'" '.$disabled.' style="'.$style.'"></div>';
    	}
	}
?>