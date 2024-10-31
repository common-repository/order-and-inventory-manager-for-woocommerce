<?php
/**
 * OIMProductStock class
 *
 * Handles stock values, and displays total stock in each used currency.
 *
 * @since    1.0.0
 */
global $OIMProductStock;
/**
 * Product stock values, and displays total stock, total unit in stock and total physical stocks.
 */
class OIMProductStock
{
	private static $_instance;
	private static $table_name;

	public static function init(){
		if( ! self::$_instance instanceof OIMProductStock ){
			self::$_instance = new OIMProductStock();
		}
		return self::$_instance;
	}

	private function __construct(){
		global $wpdb;
		self::$table_name = $wpdb->prefix . 'oim_product_stock';
		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'init', array( $this, 'init_call' ) );
		/*
		* Add stock entry to product stock table after create the order
		*/
		add_action( 'woocommerce_payment_complete', array( $this, 'deduct_product_stock' ), 99 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'deduct_product_stock' ), 99 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'deduct_product_stock' ), 99 );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'deduct_product_stock' ), 99 );
		/*
		* Reduct stock entry to product stock table after create the order
		*/
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'increase_product_stock' ), 99 );
		add_action( 'woocommerce_order_status_pending', array( $this, 'increase_product_stock' ), 99 );
		add_action( 'woocommerce_order_status_failed', array( $this, 'increase_product_stock' ), 99 );
		add_action( 'woocommerce_restock_refunded_item', array( $this, 'increase_refunded_product_stock' ), 10, 5 );
		/**
		* Reduce physical stock when order status is changed to complete or another custom status.
		*/
		add_action( 'woocommerce_order_status_changed', array( $this, 'deduct_product_physical_stock' ), 10, 4 );
		/*
		* Display product stock history data
		*/
		add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'display_simple_product_stock_history' ) );
		add_action( 'oimw_product_options_inventory_product_data', array( $this, 'display_variable_product_stock_history' ) );
		/*
		* Download history data
		*/
		add_action( 'wp_ajax_download_stock_history_log', array( $this, 'download_stock_history_log_callback' ) );
		/*
		* Schedule delete history data
		*/
		add_action('init', array($this, 'delete_stock_history'));
		add_action('delete_product_stock_history', array($this, 'delete_history'));
		/**
		 * 
		 * */
		add_action('woocommerce_product_duplicate', array($this, 'reset_duplicate_product_stock'), 10, 2);
	}

	/**
	 * 
	 * */
	public function reset_duplicate_product_stock( $duplicate, $product ){
		$product_id = $duplicate->get_id();
		$old_product_id = $product->get_id();

		$physical_stock = get_post_meta( $product_id, 'oimwc_physical_stock', true );
		$shop_pack_size = get_post_meta( $old_product_id, 'oimwc_our_pack_size', true );
		$level 			= get_post_meta( $old_product_id, 'oimwc_low_stock_threshold_level', true );

		update_post_meta( $product_id, '_stock', $physical_stock );
		update_post_meta( $product_id, 'oimwc_our_pack_size', $shop_pack_size );
		update_post_meta( $product_id, 'oimwc_low_stock_threshold_level', $level );
	}

    /**
    * Schedules deletation of stock history on every month's last date at 23:59:59
    *
    * @since 1.0.0
    */
    function delete_stock_history() {
        if (!wp_next_scheduled('delete_product_stock_history')) {
            $schedule_time = oimwc_getCurrentDateByTimeZone('Y-m-d') . ' 23:59:59';
            $schedule_time = get_gmt_from_date($schedule_time, 'U');
            wp_schedule_event($schedule_time, 'daily', 'delete_product_stock_history');
        }
    }

    /**
    * Schedules deletation of stock history on every month's last date at 23:59:59
    *
    * @since 1.0.0
    */
    public function delete_history(){
    	global $wpdb;
        $stock_log_limitation = get_option('stock_log_limitation', 6);
        if( ! $stock_log_limitation ){
        	$stock_log_limitation = 6;
        }
        $date = date( 'Y-m-d H:i:s', strtotime( "-{$stock_log_limitation} months" ) );
        $sql = "DELETE FROM ".self::$table_name." WHERE `date` <= '{$date}'" ;
        $wpdb->query( $sql );
    }

	public function download_stock_history_log_callback(){
		if ( isset( $_GET['_nonce'] ) && wp_verify_nonce( $_GET['_nonce'], 'download_stock_history_log_nonce' ) ) {
			$product_id 	= isset( $_GET['product_id'] ) ? $_GET['product_id'] : 0;
			$variation_id 	= isset( $_GET['variation_id'] ) ? $_GET['variation_id'] : 0;
			if( $product_id ) :
				$stock_data = $this->get_product_stock_detail( array( 'product_id' => $product_id, 'variation_id' => 0 ), false );
			endif;
			if( $variation_id ) :
				$stock_data	= $this->get_product_stock_detail( array( 'variation_id' => $variation_id ), false );
			endif;
			$output = '';
			foreach ( $stock_data as $key => $row ) {
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
							$output .= "$html \r\n"; 
						}
					endif; 
					if( in_array( $row['note'], array( 'cancelled', 'pending', 'failed' ) ) ): 
						if( isset( $stock_data[ $key + 1 ] ) ){
							$html = sprintf( __( '%s - WooCommerce stock: %d → %d. Order #%d was cancelled by %s.', 'order-and-inventory-manager-for-woocommerce' ), $date, $stock_data[ $key + 1 ]['stock'], $row['stock'], $row['order_id'], $user_name );
							$output .= "$html \r\n"; 
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
					 $output .= "$date - $notes \r\n"; 
				endif; 
			}
			$filename = __( 'Stock history log', 'order-and-inventory-manager-for-woocommerce' );
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename='.$filename .'-'. time().'.txt');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header("Content-Type: text/plain");
			echo $output;
		}
		die;
	}

	public function display_simple_product_stock_history(){
		global $post;
		$_product = wc_get_product( $post->ID );
		if( $_product->is_type( 'variation' ) ){
			return;
		}
		$stock_data = $this->get_product_stock_detail( array( 'product_id' => $post->ID, 'variation_id' => 0 ), false );
		$nonce = wp_create_nonce( 'download_stock_history_log_nonce' );
		$log_history_link = add_query_arg( array( 'action' => 'download_stock_history_log', 'product_id' => $post->ID, '_nonce' => $nonce ), admin_url( 'admin-ajax.php' ) );
		require_once OIMWC_TEMPLATE . 'display_product_stock.php';
	}

	public function display_variable_product_stock_history( $variation_id ){
		global $post;
		$stock_data = $this->get_product_stock_detail( array( 'variation_id' => $variation_id ), false );
		$nonce = wp_create_nonce( 'download_stock_history_log_nonce' );
		$log_history_link = add_query_arg( array( 'action' => 'download_stock_history_log', 'variation_id' => $variation_id, '_nonce' => $nonce ), admin_url( 'admin-ajax.php' ) );
		require OIMWC_TEMPLATE . 'display_product_stock.php';
	}

	public function deduct_product_physical_stock( $order_id, $from_status, $to_status, $obj_order ){
		$order_status 	= get_option('oimwc_reduce_physical_stock_OStatus');
		$order_status[]	= 'completed';
		if( in_array( $to_status, $order_status ) ){
			$this->update_ordered_product_stock( $order_id, true, true );
		}
	}

	public function deduct_product_stock( $order_id ){
		$this->update_ordered_product_stock( $order_id );
	}

	public function increase_product_stock( $order_id ){
		$this->update_ordered_product_stock( $order_id, false );
	}

	public function increase_refunded_product_stock( $product_id, $old_stock, $new_stock, $order, $product ){

        $variation_id 	= 0;
        $product_obj_id = $product_id;
		$product_type 	= 'simple';
		$_product 		= wc_get_product( $product_id );

        if( $_product->is_type( 'variation' ) ){
			$product_type 	= 'variable';
			$variation_id 	= $product_id;
			$product_id 	= $product->get_parent_id();
			$product_obj_id = $variation_id;
		}
		
		$product_stock  = $this->get_product_stock_detail( array( 'product_id' => $product_id, 'variation_id' => $variation_id ) );

		$shop_pack_size 			 	 = (!empty($product_stock['shop_pack_size'])) ? $product_stock['shop_pack_size'] : 1;
		$new_physical_stock				 = calculate_physical_stock ( $product_obj_id, $product_type );	
		$stock_in_units 				 = floor($new_physical_stock * $shop_pack_size);

		$shop_pack_size 				 = $shop_pack_size > 0 ? intval( $shop_pack_size ) : 1;
		$total_pieces 					 = $total_pieces > 0 ? intval( $total_pieces ) : 0;
		$new_physical_stock 			 = $new_physical_stock > 0 ? intval( $new_physical_stock ) : 0;


		$user_id 						 = get_current_user_id();

        $args = array(
        	'user_id'    	=> $user_id,	
        	'order_id' 		=> $order->get_id(),
        	'product_id' 	=> $product_id,
        	'variation_id'	=> $variation_id,
        	'stock'			=> $new_stock,
        	'physical_stock'=> $new_physical_stock,
        	'stock_in_units'=> $stock_in_units,
        	'shop_pack_size'=> $product_stock[ 'shop_pack_size' ],
        	'note'			=> 'refund'
        );

        self::add( $args );

        update_post_meta( $product_obj_id, 'oimwc_physical_units_stock', $stock_in_units );
        update_post_meta( $product_obj_id, 'oimwc_physical_stock', $new_physical_stock );

	}

	public function update_ordered_product_stock( $order_id, $reduce_stock = true, $order_complete = false ){
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$items 			= $order->get_items(); 
			$order_status 	= $order->get_status(); 
            foreach ( $items as $item ) {
                $product_id 	= $item->get_product_id();
                $variation_id 	= $item->get_variation_id();
                $sell_qty 		= $item->get_quantity();
                $stock 			= 0;
                $product_obj_id = $product_id;
                $product_stock  = $this->get_product_stock_detail( array( 'product_id' => $product_id, 'variation_id' => $variation_id ) );
                $product_order  = $this->get_product_stock_detail( array( 'product_id' => $product_id, 'variation_id' => $variation_id, 'order_id' => $order_id ) );

                if( is_array( $product_order ) && count( $product_order ) && ! $order_complete ){
                	$order_note = $product_order['note'];
                	if( $reduce_stock && in_array( $order_note , array( 'completed', 'processing', 'on-hold' ) ) ){
	                	return;
	                }
                	if( !$reduce_stock && in_array( $order_note , array( 'cancelled', 'pending', 'failed' ) ) ){
	                	return;
	                }
                }
                $product_type 	= 'simple';
                if( $variation_id ){
                	$product_obj_id = $variation_id;
                	$product_type 	= 'variable';
                }
                $_manage_stock 		= get_post_meta( $product_obj_id, '_manage_stock', true );
                if( $_manage_stock == 'no' ){
                	continue;
                }
                $product_shop_pack_size = get_post_meta( $product_obj_id, 'oimwc_our_pack_size', true );
                $product_shop_pack_size = $product_shop_pack_size ? $product_shop_pack_size : 1;

                $product_obj 		= wc_get_product( $product_obj_id );
                $stock 				= (int)$product_obj->get_stock_quantity();	
                $shop_pack_size 	= (!empty($product_stock['shop_pack_size'])) ? $product_stock['shop_pack_size'] : $product_shop_pack_size;
                $_stock_status 		= get_post_meta( $product_obj_id, '_backorders', true );	
                $order_qty_units 	= $sell_qty * $shop_pack_size;
                $new_physical_stock	= calculate_physical_stock ( $product_obj_id, $product_type );	
                $stock_in_units 	= floor($new_physical_stock *  $shop_pack_size);
				$old_physial_stock	= $product_stock['physical_stock'];
				$old_stock_in_units	= $product_stock['stock_in_units'];
				$cal_stock_in_units	= floor( $old_physial_stock * $shop_pack_size );
				$extra_units 		= $old_stock_in_units - $cal_stock_in_units;
				$stock_in_units 	= $stock_in_units + $extra_units;
				$order_status 		= $order_complete ? 'order_complete' : $order_status;
                
                if( $new_physical_stock < 0 || $stock_in_units < 0 ){
                	$new_stock = get_ordered_product_qty( $product_obj_id, $product_type );
                	if( $new_stock != $stock ){
                		if( $stock < 0 && $new_stock > 0 && $_stock_status != 'no' ){
                			$new_stock = $new_stock * -1;	
                		}
                		$stock = $new_stock;
                		wc_update_product_stock($product_obj_id,$new_stock);
                	}
                }

                $stock_in_units 	= $stock_in_units > 0 ? $stock_in_units : 0;
                $new_physical_stock = $new_physical_stock > 0 ? $new_physical_stock : 0;
                $shop_pack_size 	= $shop_pack_size > 0 ? $shop_pack_size : 1;

                $args = array(
                	'order_id' 		=> $order_id,
                	'product_id' 	=> $product_id,
                	'variation_id'	=> $variation_id,
                	'order_id'		=> $order_id,
                	'stock'			=> $stock,
                	'physical_stock'=> $new_physical_stock,
                	'sell_qty'		=> $sell_qty,
                	'stock_in_units'=> $stock_in_units,
                	'shop_pack_size'=> $shop_pack_size,
                	'note'			=> $order_status
                );

                self::add( $args );
                update_post_meta( $product_obj_id, 'oimwc_physical_units_stock', $stock_in_units );
                update_post_meta( $product_obj_id, 'oimwc_physical_stock', $new_physical_stock );
                update_post_meta( $product_obj_id, 'oimwc_our_pack_size', $shop_pack_size );
            }
		}
	}

	public function update_product( $product_id, $variation_id, $stock, $physical_stock, $unit_in_stock, $shop_pack_size, $import = 0, $existing_field = '' ){
		$product_stock  = $this->get_product_stock_detail( array( 'product_id' => $product_id, 'variation_id' => $variation_id ) );
		$product_obj_id = $product_id;
		$product_type 	= 'simple';

		if( $variation_id ){
			$product_obj_id = $variation_id;
			$product_type 	= 'variable';
		}

		$new_product = true;
		if( is_array( $product_stock ) && count( $product_stock ) ){
			$new_product = false;
		}

		$oimwc_physical_stock            = (!empty($product_stock['physical_stock']) ) ? $product_stock['physical_stock'] : 0;
		$physical_stock 				 = (!empty($physical_stock)) ? $physical_stock : 0;
		$unit_in_stock 				     = (!empty($unit_in_stock)) ? $unit_in_stock : 0;
		$product_stock['stock']          = (!empty($product_stock['stock']) ) ? $product_stock['stock'] : 0;
		$product_stock['physical_stock'] = (!empty($product_stock['physical_stock']) ) ? $product_stock['physical_stock'] : 0;
		$product_stock['stock_in_units'] = (!empty($product_stock['stock_in_units']) ) ? $product_stock['stock_in_units'] : 0;
		$product_stock['shop_pack_size'] = (!empty($product_stock['shop_pack_size']) ) ? $product_stock['shop_pack_size'] : 1;

		$data = array( 'product_id' => $product_id, 'variation_id' => $variation_id, 'physical_stock' => $oimwc_physical_stock );
		if( $new_product ){
			goto add_new_product;
		}

		/* All fields are the same */
		if( $stock == $product_stock['stock'] &&  
			$physical_stock == $product_stock['physical_stock'] &&  
			$unit_in_stock == $product_stock['stock_in_units'] &&  
			$shop_pack_size == $product_stock['shop_pack_size'] && 
			$import == 0 ){
			wc_update_product_stock( $product_obj_id, $stock );
			update_post_meta( $product_obj_id, 'oimwc_physical_stock', sanitize_text_field($physical_stock) );
			update_post_meta( $product_obj_id, 'oimwc_physical_units_stock', sanitize_text_field($unit_in_stock) );
			update_post_meta( $product_obj_id, 'oimwc_our_pack_size', sanitize_text_field($shop_pack_size) );
			return false;
		}

		$new_physical_stock = 0;
		$total_pieces = 0;
		
		$value_updated = false;
		/* The $stock is changed, $shop_pack_size is changed/not changed, and other fields are the same. */
		if( ( $stock != $product_stock['stock'] && 
			$physical_stock == $oimwc_physical_stock && 
			$unit_in_stock == $product_stock['stock_in_units'] && 
			( $shop_pack_size == $product_stock['shop_pack_size'] ||
			$shop_pack_size != $product_stock['shop_pack_size'] ) ) || 
			$existing_field == 'stock' ){

			$value_updated 				= true;
			$new_physical_stock			= calculate_physical_stock ( $product_obj_id, $product_type );	
			$total_pieces 				= floor($new_physical_stock * $shop_pack_size);

			$shop_pack_size 		= $shop_pack_size > 0 ? intval( $shop_pack_size ) : 1;
			$total_pieces 			= $total_pieces > 0 ? intval( $total_pieces ) : 0;
			$new_physical_stock 	= $new_physical_stock > 0 ? intval( $new_physical_stock ) : 0;

			$data[ 'stock' ] 			= $stock;
			$data[ 'physical_stock' ]	= $new_physical_stock;
			$data[ 'shop_pack_size' ] 	= $shop_pack_size;
			$data[ 'stock_in_units' ] 	= $total_pieces;
			$data[ 'note' ]	 			= 'stock_changed';

			if( (int)$product_stock['shop_pack_size'] > 0 && $shop_pack_size != $product_stock['shop_pack_size'] ){
				$data[ 'note' ]	 		= 'stock_n_shop_pack_size_changed';
			}
		}

		/* The $physical_stock is changed, $shop_pack_size is changed/not changed, and other fields are the same */
		if( ( $stock == $product_stock['stock'] && 
			$physical_stock != $oimwc_physical_stock && 
			$unit_in_stock == $product_stock['stock_in_units'] && 
			( $shop_pack_size == $product_stock['shop_pack_size'] ||
			$shop_pack_size != $product_stock['shop_pack_size'] ) ) || 
			$existing_field == 'physical_stock' ){

			$value_updated 				= true;
			$order_product_qty			= get_ordered_product_qty( $product_obj_id, $product_type );
			$total_pieces 				= floor($physical_stock * $shop_pack_size);

			$new_stock = $physical_stock - $order_product_qty;
			wc_update_product_stock( $product_obj_id, $new_stock );

			$shop_pack_size 		= $shop_pack_size > 0 ? intval( $shop_pack_size ) : 1;
			$total_pieces 			= $total_pieces > 0 ? intval( $total_pieces ) : 0;

			$data[ 'stock' ] 			= $new_stock;
			$data[ 'shop_pack_size' ] 	= $shop_pack_size;
			$data[ 'stock_in_units' ] 	= $total_pieces;
			$data[ 'physical_stock' ] 	= $physical_stock;
			$data[ 'note' ]	 			= 'physical_stock_changed'; 
			$new_physical_stock			= $physical_stock;

			if( (int)$product_stock['shop_pack_size'] > 0 && $shop_pack_size != $product_stock['shop_pack_size'] ){
				$data[ 'note' ]	 		= 'physical_stock_n_shop_pack_size_changed';
			}
		}

		/* The $unit_in_stock is changed, $shop_pack_size is changed/not changed, and other fields are the same */
		if( ( $stock == $product_stock['stock'] && 
			$physical_stock == $oimwc_physical_stock && 
			$unit_in_stock != $product_stock['stock_in_units'] && 
			( $shop_pack_size == $product_stock['shop_pack_size'] ||
			$shop_pack_size != $product_stock['shop_pack_size'] ) ) || 
			$existing_field == 'units_in_stock' ){

			$value_updated 				= true;
			$total_pieces 				= $unit_in_stock;
			$order_product_qty			= get_ordered_product_qty( $product_obj_id, $product_type );
			$new_physical_stock			= floor($total_pieces / $shop_pack_size);

			$new_stock = $new_physical_stock - $order_product_qty;
			wc_update_product_stock( $product_obj_id, $new_stock );

			$shop_pack_size 		= $shop_pack_size > 0 ? intval( $shop_pack_size ) : 1;
			$total_pieces 			= $total_pieces > 0 ? intval( $total_pieces ) : 0;

			$data[ 'stock' ] 			= $new_stock;
			$data[ 'shop_pack_size' ] 	= $shop_pack_size;
			$data[ 'stock_in_units' ] 	= $total_pieces;
			$data[ 'physical_stock' ] 	= $new_physical_stock;
			$data[ 'note' ]	 			= 'unit_changed';

			if( (int)$product_stock['shop_pack_size'] > 0 && $shop_pack_size != $product_stock['shop_pack_size'] ){
				$data[ 'note' ]	 		= 'unit_n_shop_pack_size_changed'; 
			}
		}

		/* Only $shop_pack_size is changed, and other fields are the same */
		if( ( $stock == $product_stock['stock'] && 
			$physical_stock == $oimwc_physical_stock && 
			$unit_in_stock == $product_stock['stock_in_units'] && 
			$shop_pack_size != $product_stock['shop_pack_size'] &&
			! $value_updated ) || 
			$existing_field == 'shop_pack_size'  ){

			$total_pieces 				= $unit_in_stock;
			$order_product_qty			= get_ordered_product_qty( $product_obj_id, $product_type );
			$new_physical_stock			= floor($total_pieces / $shop_pack_size);

			//$previous_ordered_pieces = floor( $product_stock['shop_pack_size'] * $order_product_qty );
			$previous_ordered_pieces = floor( $shop_pack_size * $order_product_qty );
            $new_stock = floor(($total_pieces - $previous_ordered_pieces) / $shop_pack_size);
			wc_update_product_stock( $product_obj_id, $new_stock );

			$shop_pack_size 		= $shop_pack_size > 0 ? intval( $shop_pack_size ) : 1;
			$total_pieces 			= $total_pieces > 0 ? intval( $total_pieces ) : 0;
			$new_physical_stock 	= $new_physical_stock > 0 ? intval( $new_physical_stock ) : 0;

			$data[ 'stock' ] 			= $new_stock;
			$data[ 'shop_pack_size' ] 	= $shop_pack_size;
			$data[ 'stock_in_units' ] 	= $total_pieces;
			$data[ 'physical_stock' ] 	= $new_physical_stock;
			$data[ 'note' ]	 			= 'shop_pack_size_changed';

		}
		if( count( $data ) < 4 && ! $new_product ){
			$this->reset_product_stock( $product_id, $variation_id, $stock, $physical_stock, $unit_in_stock, $shop_pack_size, $import, $existing_field);
			return false;
		}
		add_new_product:
		if( $new_product ){
			if( $stock == 0 && $physical_stock == 0 && $unit_in_stock == 0 ){
				return false;
			}
			$shop_pack_size = intval( $shop_pack_size ) ? $shop_pack_size : 1;
			if( intval( $stock ) ){
				$physical_stock			= $stock;	
				$total_pieces 			= floor($physical_stock * $shop_pack_size);
			}
			if( intval( $physical_stock ) ){
				$stock					= $physical_stock;	
				$total_pieces 			= floor($physical_stock * $shop_pack_size);
				wc_update_product_stock( $product_obj_id, $stock );
			}
			if( intval( $unit_in_stock ) ){
				$stock					= $unit_in_stock;
				$physical_stock 		= $unit_in_stock;
				$total_pieces 			= $unit_in_stock;
				wc_update_product_stock( $product_obj_id, $stock );
			}

			$data[ 'stock' ] 			= $stock;
			$data[ 'shop_pack_size' ] 	= $shop_pack_size;
			$data[ 'stock_in_units' ] 	= $total_pieces;
			$data[ 'physical_stock' ] 	= $physical_stock;
			$data[ 'note' ]	 			= 'new_product'; 
			$new_physical_stock			= $physical_stock;
		}
		$new_physical_stock 		= $new_physical_stock > 0 ? $new_physical_stock : 0;
		$total_pieces 				= $total_pieces > 0 ? $total_pieces : 0;
		$data['physical_stock'] 	= $new_physical_stock;
		$data['stock_in_units'] 	= $total_pieces;
		$data['imported']		 	= $import;
		update_post_meta( $product_obj_id, 'oimwc_physical_stock', sanitize_text_field($new_physical_stock) );
		update_post_meta( $product_obj_id, 'oimwc_physical_units_stock', sanitize_text_field($total_pieces) );
		update_post_meta( $product_obj_id, 'oimwc_our_pack_size', sanitize_text_field($shop_pack_size) );
		self::add( $data );
		return true;
	}

	public function reset_product_stock( $product_id, $variation_id, $stock, $physical_stock, $unit_in_stock, $shop_pack_size, $import = 0, $existing_field = '' ){

		$product_obj_id = $product_id;
		$product_type 	= 'simple';

		if( $variation_id ){
			$product_obj_id = $variation_id;
			$product_type 	= 'variable';
		}

		$new_stock = get_post_meta( $product_obj_id, '_stock', true );
		$new_physical_stock = get_post_meta( $product_obj_id, 'oimwc_physical_stock', true );
		$new_total_pieces = get_post_meta( $product_obj_id, 'oimwc_physical_units_stock', true );
		$new_shop_pack_size = get_post_meta( $product_obj_id, 'oimwc_our_pack_size', true );

		if( $stock != $new_stock 
			&& $physical_stock == $new_physical_stock 
			&& $unit_in_stock == $new_total_pieces ){
			$this->update_product($product_id, $variation_id, $stock, $physical_stock, $unit_in_stock, $shop_pack_size, $import = 0, $existing_field = 'stock');
		}

		if( $stock == $new_stock 
			&& $physical_stock != $new_physical_stock 
			&& $unit_in_stock == $new_total_pieces ){
			$this->update_product($product_id, $variation_id, $stock, $physical_stock, $unit_in_stock, $shop_pack_size, $import = 0, $existing_field = 'physical_stock');
		}

		if( $stock == $new_stock 
			&& $physical_stock == $new_physical_stock 
			&& $unit_in_stock != $new_total_pieces ){
			$this->update_product($product_id, $variation_id, $stock, $physical_stock, $unit_in_stock, $shop_pack_size, $import = 0, $existing_field = 'units_in_stock');
		}
	}

	public function update_purchased_product_stock( $product_id, $purchased_qty, $supplier_pack_size, $shop_pack_size, $purchase_order_id ){
		$_product = wc_get_product( $product_id );
		$variation_id = 0;
		$product_obj_id = $product_id;
		$product_type 	= 'simple';
		if(!empty($_product)){
			if( $_product->is_type( 'variation' ) ){
				$product_type 	= 'variable';
				$variation_id 	= $product_id;
				$product_id 	= $_product->get_parent_id();
				$product_obj_id = $variation_id;
			}
		}
		
		$product_stock  = $this->get_product_stock_detail( array( 'product_id' => $product_id, 'variation_id' => $variation_id ) );

		$stock 				= $product_stock[ 'stock' ];
		$total_pieces		= $product_stock[ 'stock_in_units' ];
		$old_physical_stock = $product_stock[ 'physical_stock' ];
		$physical_stock		= get_post_meta( $product_obj_id, 'oimwc_physical_stock', true );
		$order_product_qty	= get_ordered_product_qty( $product_obj_id, $product_type );

		$purchased_units	= ( int ) $purchased_qty * (int) $supplier_pack_size;
		$new_purchased_stock= floor( $purchased_units / $shop_pack_size );

		$new_total_pieces 	= ( int ) $total_pieces + (int) $purchased_units;
		$new_physical_stock = floor( $new_total_pieces / $shop_pack_size );
		$new_stock 			= $new_physical_stock - $order_product_qty;

		wc_update_product_stock( $product_obj_id, $new_stock );
		update_post_meta( $product_obj_id, 'oimwc_physical_units_stock', $new_total_pieces );
		update_post_meta( $product_obj_id, 'oimwc_physical_stock', $new_physical_stock );

		$data = array();
		$data[ 'product_id' ]		= $product_id;
		$data[ 'variation_id' ]		= $variation_id;
		$data[ 'arrive_qty' ]		= $purchased_qty;
		$data[ 'stock' ] 			= $new_stock;
		$data[ 'shop_pack_size' ] 	= $shop_pack_size;
		$data[ 'stock_in_units' ] 	= $new_total_pieces;
		$data[ 'physical_stock' ] 	= $new_physical_stock;
		$data[ 'purchase_order_id' ] 	= $purchase_order_id;
		$data[ 'note' ]	 			= 'new_stock_arrived';
		
		self::add( $data );
		return $data;
	}

	public function init_call(){
		if( isset( $_GET['update_product_stock_query'] ) ){
			oimwc_product_stock_tbl();
			wp_redirect( admin_url( 'admin.php?page=order-inventory-management' ) );
			exit();
		}
	}

	public function initialize(){
		global $wpdb;
		$is_stock_save = get_option( 'oim_product_stock', false );
		if( !$is_stock_save ){
			oimwc_create_order_inventory_tbl();
			add_action( 'admin_notices', array( $this, 'notice_for_stock' ) );
		}
	}

	public function notice_for_stock(){
		$link = admin_url( 'index.php?update_product_stock_query=1' );
		require_once OIMWC_TEMPLATE . 'notice_for_product_stock.php';
	}

	public function get_product_stock_detail( $where, $limit = 1 ){
		global $wpdb;
		$where_condition = '';
		$sql = 'SELECT * FROM '. self::$table_name . '  ';
		if( is_array( $where ) ){
			$where_condition = ' WHERE ';
			$condition = '';
			foreach ($where as $key => $value) {
				$where_condition .= sprintf( ' %s %s = "%s"', $condition, $key, $value );
				$condition = 'AND';
			}
		}
		$sql .= $where_condition . ' ORDER BY id DESC ';
		if( $limit ){
			$sql .= " LIMIT 0,1";
			return $wpdb->get_row( $sql, ARRAY_A );
		}
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public static function add( $data, $type = '' ){
		global $wpdb;

		delete_transient( 'oimwc_check_product_stock' );

		switch ( $type ) {
			case 'value':
				$data['note']	= sprintf( __( '', 'order-and-inventory-manager-for-woocommerce' )  );
				break;
		}
		$data['user_id'] 	= get_current_user_id();
		$insert_id = $wpdb->insert( self::$table_name, $data );
		return $insert_id;
	}
}

$OIMProductStock = OIMProductStock::init();
?>