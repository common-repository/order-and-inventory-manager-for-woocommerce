<?php
global $wpdb;
$table_name = $wpdb->prefix.'order_inventory';
$supplier_id = sanitize_text_field($_GET['supplier']);
$supplier_name = get_the_title( $supplier_id );
$order_date = date('Y-m-d H:i:s', sanitize_text_field($_GET['date']));
?>
<div class="view_order_cls">
	<?php include( OIMWC_TEMPLATE . 'top_area.php' ); ?>
	<div class="wrap">
		<div class="tab_panel">
			<?php
			$order_number = $wpdb->get_var('select order_number from '.$table_name.'  where supplier_id = "'.$supplier_id.'" and order_date = "'.$order_date.'"');
			    
			include( OIMWC_TEMPLATE . 'navigation.php' );
			?>
		</div>
		<?php
		$status = $this->get_purchase_order_status($supplier_id,$order_date);
		$total_purchase = OIMWC_Order::get_total_purchase_amount( array( 'supplier_id' => $supplier_id, 'order_date' => $order_date ) );      
		$status_cls = '';
		if( $status == __('Pending','order-and-inventory-manager-for-woocommerce') ){
			$status_cls = 'pending_po_panel';
		}
		if( $status == __('Receiving','order-and-inventory-manager-for-woocommerce') ){
			$status_cls = 'receiving_po_panel';	
		}
		?>

		<h2></h2>
		<div class="order_info_sticky <?php echo $status_cls;?>">
			<div class="order_info_panel">
				<span>
					<strong><?php esc_html_e('Supplier Name','order-and-inventory-manager-for-woocommerce');?>:</strong>
					<?php echo $supplier_name?>
				</span>
				<span>
					<strong><?php esc_html_e('PO date','order-and-inventory-manager-for-woocommerce');?>: </strong>
					<?php echo $order_date;?>
				</span>
				<span>
					<strong><?php esc_html_e('Purchase price','order-and-inventory-manager-for-woocommerce')?>:</strong>
					<?php echo $total_purchase;?>
				</span>
				<span><strong><?php esc_html_e('Status','order-and-inventory-manager-for-woocommerce');?>: </strong>
				<span class="order_status"> <?php echo $status;?></span></span>
			</div>
			<?php if( $status == __('Pending','order-and-inventory-manager-for-woocommerce') ){ ?>
			<div class="product_handler view_order_add_to_list">
                <form id="frm_product_handler">
                    <input type="hidden" name="action" value="add_product_manually_order_page">
                    <input type="hidden" name="supplier" value="<?php echo $supplier_id; ?>">
                    <input type="hidden" name="date" value="<?php echo strtotime($order_date); ?>">
                    <input type="hidden" name="order_number" value="<?php echo $order_number; ?>">
                    <?php wp_nonce_field('oimwc_add_product', 'oimwc_nonce'); ?>
                    <div class="<?php echo $cls;?>" data-tip="<?php echo $msg ? $msg : '';?>">
                        <input type="text" name="product_sku" placeholder="<?php _e('Product SKU','order-and-inventory-manager-for-woocommerce'); ?>" class="order_prod_sku" />
                        <input type="text" name="requested_stock" placeholder="<?php _e('Qty','order-and-inventory-manager-for-woocommerce'); ?>" class="order_req_stock" />
                        <input type="submit" value="<?php _e('Add to order', 'order-and-inventory-manager-for-woocommerce'); ?>" class="button" />
                    </div>
                </form>
                <div class="lw_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
            </div>
        	<?php } ?>
        	<input type="hidden" name="supplier" id="view_po_supplier" value="<?php echo $supplier_id; ?>">
            <input type="hidden" name="date" id="view_po_date" value="<?php echo strtotime($order_date); ?>">
        	<input type="hidden" name="view_po_page" id="view_po_page" value="1">
			<input type="hidden" name="view_po_total_pages" id="view_po_total_pages" value="<?php echo $obj->total_pages;?>">
			<?php
			/*** Display PO Buttons ***/

			$finalize_order = $wpdb->get_var("SELECT completed_order from $table_name where supplier_id = {$supplier_id} and order_date = '{$order_date}'");
			if( $finalize_order ){
			    $order_type = 'finalize_orders';
			}
			else{
			    $order_type = 'active_orders';   
			}
			$page = 0;
			$remaning_orders = OIMWC_Order::get_remaining_orders($order_type,$page,'','','');
			if( $remaning_orders ){
			    foreach ($remaning_orders as $remaning_order) {
			        if( $remaning_order->supplier_id == $supplier_id && $remaning_order->order_date == $order_date ){

			            $arrival_date = $remaning_order->arrival_date ? date_i18n( get_option( 'date_format' ), strtotime($remaning_order->arrival_date) ) : '';
			            
			            if($finalize_order){
			                $arrival_date = $arrival_date ? $arrival_date : '-';
			            }

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
					    $total_purchase = OIMWC_Order::get_total_purchase_amount( array( 'supplier_id' => $remaning_order->supplier_id, 'order_date' => $remaning_order->order_date ) );

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


			            echo '<div class="inside_po_buttons po_action_btns" data-supplier_id = "'.$supplier_id.'" data-order_date = "'.sanitize_text_field($_GET['date']).'" data-default_cols=\''.$default_cols.'\' data-default_po_lang="'.$po_default_lang.'" data-label="" data-default_ship_address="'.$default_ship_add.'" data-download_pdf="'.$download_pdf.'" data-supplier-email="'.$supplier_email.'" data-email-subject="'.$po_email_subject.'" data-email-message="'.$po_email_message.'" data-default_save_flag="'.$save_default_sett.'" data-delivery_date="'.$delivery_date.'" data-shipping_method="'.$shipping_method.'" data-shipping_terms="'.$shipping_terms.'" data-po_attn="'.$po_attn.'" data-file_name="'.$attach_filename.'">';
			            display_po_buttons($remaning_order,$finalize_order,$arrival_date);
			            echo '</div>';
			        }
			    }
			}
			/**** ***** **** ***/
			?>
		</div>