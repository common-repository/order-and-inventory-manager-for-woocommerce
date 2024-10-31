<div class="order_stock_display_panel">
	<h2>
		<?php _e( 'Product Stock History', 'order-and-inventory-manager-for-woocommerce' ); ?>
		<?php if( count( $stock_data ) > 1 ): ?>
			<a class="button" target="_blank" href="<?php echo $log_history_link; ?>"><?php _e( 'Download log history', 'order-and-inventory-manager-for-woocommerce' ); ?></a>
		<?php endif; ?>				
	</h2>
	<?php   if(count( $stock_data ) == 1 || count( $stock_data ) == 2){
				$style = 'height:45px; overflow:auto;';
			}
			else if(count( $stock_data ) == 3){
				$style = 'height:85px; overflow:auto;';	
			}else{
				$style = 'height:100px; overflow:auto;';
			} ?>
	<div class="order_stock_row_display_panel" style="<?php echo $style; ?>">
		<?php if( count( $stock_data ) > 0 ): ?>
			<?php foreach ( $stock_data as $key => $row ) {
				if( $row['note'] == 'new_product' ){
					continue;	
				}
				$user_name 	= '';
				if( $row['user_id'] ){
					$user_name = get_user_meta( $row['user_id'], 'first_name', true );
					$user_name .= ' '. get_user_meta( $row['user_id'], 'last_name', true );
					if( empty( trim( $user_name ) ) ){
						$user_name = get_user_meta( $row['user_id'], 'nickname', true );
					}
				}
				if( empty( trim( $user_name ) ) ){
					$user_name = __( 'Customer', 'order-and-inventory-manager-for-woocommerce' );
				}
				$date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row['date'] ) );
					if( $row['order_id'] ): 
						if( in_array( $row['note'], array( 'completed', 'processing', 'on-hold' ) ) ): 
							if( isset( $stock_data[ $key + 1 ] ) ){
								$html = sprintf( __( '%s - WooCommerce stock: %d → %d. The product was added to the order: #%d by %s.', 'order-and-inventory-manager-for-woocommerce' ), $date, $stock_data[ $key + 1 ]['stock'], $row['stock'], $row['order_id'], $user_name ); 
								printf( '<p>%s</p>', $html );
							}else{
								$html = sprintf( __( '%s - WooCommerce stock: %d → %d. The product was added to the order: #%d by %s.', 'order-and-inventory-manager-for-woocommerce' ), $date, 'N/A', $row['stock'], $row['order_id'], $user_name ); 
								printf( '<p>%s</p>', $html );
							}
						endif; 
						if( in_array( $row['note'], array( 'cancelled', 'pending', 'failed' ) ) ): 
							if( isset( $stock_data[ $key + 1 ] ) ){
								$html = sprintf( __( '%s - WooCommerce stock: %d → %d. Order #%d was cancelled by %s.', 'order-and-inventory-manager-for-woocommerce' ), $date, $stock_data[ $key + 1 ]['stock'], $row['stock'], $row['order_id'], $user_name ); 
								printf( '<p>%s</p>', $html );
							}
						endif;
						if( $row['note'] == 'order_complete' ):
							$notes = sprintf( __( 'Physical stock: %d → %d. Order #%d was completed.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['physical_stock'], $row['physical_stock'], $row['order_id'] ); 
							printf( '<p>%s - %s</p>', $date, $notes );
						endif;
						if( $row['note'] == 'refund' ):
							$html = sprintf( __( '%s - WooCommerce stock: %d → %d. Order #%d was refunded by %s.', 'order-and-inventory-manager-for-woocommerce' ), $date, $stock_data[ $key + 1 ]['stock'], $row['stock'], $row['order_id'], $user_name ); 
								printf( '<p>%s</p>', $html );
						endif;
					else:
						$notes = $row['note'];
						switch ( $row[ 'note' ] ) {
						 	case 'stock_changed':
						 		$notes = sprintf( __( 'WooCommerce stock: %d → %d. Changed by %s.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['stock'], $row['stock'], $user_name );
						 		break;
						 	case 'stock_n_shop_pack_size_changed':
						 		$notes = sprintf( __( 'WooCommerce stock: %d → %d. Pack size: %d → %d. Changed by %s', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['stock'], $row['stock'], $stock_data[ $key + 1 ]['shop_pack_size'], $row[ 'shop_pack_size' ], $user_name );	
						 		break;
						 	case 'physical_stock_changed':
						 		$notes = sprintf( __( 'Physical stock: %d → %d. Changed by %s.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['physical_stock'], $row['physical_stock'], $user_name );
						 		break;
						 	case 'physical_stock_n_shop_pack_size_changed':
						 		$notes = sprintf( __( 'Physical stock: %d → %d. Pack size: %d → %d. Changed by %s.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['physical_stock'], $row['physical_stock'], $stock_data[ $key + 1 ]['shop_pack_size'], $row[ 'shop_pack_size' ], $user_name );	
						 		break;
						 	case 'unit_changed':
						 		$notes = sprintf( __( 'Units in stock: %d → %d. Changed by %s.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['stock_in_units'], $row['stock_in_units'], $user_name );
						 		break;
						 	case 'unit_n_shop_pack_size_changed':
						 		$notes = sprintf( __( 'Units in stock: %d → %d. Pack size: %d → %d. Changed by %s.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['stock_in_units'], $row['stock_in_units'], $stock_data[ $key + 1 ]['shop_pack_size'], $row[ 'shop_pack_size' ], $user_name );	
						 		break;
						 	case 'shop_pack_size_changed':
						 		$notes = sprintf( __( 'Shop pack size: %d → %d. Changed by %s.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ]['shop_pack_size'], $row[ 'shop_pack_size' ], $user_name );
						 		break;
						 	case 'new_stock_arrived':
						 		$notes = sprintf( __( 'WooCommerce stock: %d → %d. Products (%dx) from purchase order #%d were marked as arrived by %s.', 'order-and-inventory-manager-for-woocommerce' ), $stock_data[ $key + 1 ][ 'stock' ], $row[ 'stock' ], $row[ 'arrive_qty' ], $row[ 'purchase_order_id' ], $user_name );
						 		break;
						 	case '':
						 		$notes = sprintf( __( 'Stock is %d.', 'order-and-inventory-manager-for-woocommerce' ), $row[ 'stock' ] );
						 		break;
						 }
						 $row['imported'] = isset( $row['imported'] ) ? $row['imported'] : '';
						 $import_text = $row['imported'] ? '( '. __( 'Import', 'order-and-inventory-manager-for-woocommerce' ). ' )' : '';
						 printf( '<p>%s - %s %s</p>', $date, $notes, $import_text );
					endif; 
				} 
				else: ?>	
			<p>
			<?php _e( 'No history found', 'order-and-inventory-manager-for-woocommerce' ); ?>
			</p>
		<?php endif; ?>	
	</div>
</div>