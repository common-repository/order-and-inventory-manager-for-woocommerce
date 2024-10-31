<?php
/**
 * order_status_list class
 *
 * Handles stock values, and displays total stock in each used currency.
 *
 * @since    1.0.0
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * order_status_list handles stock values, and displays total stock in each used currency. 
 *
 * @since    1.0.0
 */
class order_status_list extends WP_List_Table {
        /**
        * Empty array
        *
        * @var Array $data empty array  
        * 
        * @since 1.0.0
        */
	public $data = array();
        /**
         * Stores order ids
         * 
         * @var Array $order_ids_arr 
         */
	public $order_ids_arr = array();
        /**
         * Stores product names
         * 
         * @var Array $product_id_name_arr 
         */
	public $product_id_name_arr = array();
        /**
         * Stores ordered product's quantity
         * 
         * @var Array $order_prod_qty 
         */
	public $order_prod_qty = array();
        /**
         * Stores product in stock
         * 
         * @var Array $stock_prod 
         */
	public $stock_prod = array();
        /**
         * Stores physical stock
         * 
         * @var Array $physical_stock 
         */
	public $physical_stock = array();
        /**
         * Stores stock status
         * 
         * @var Array $stock_status 
         */
	public $stock_status = array();
	/**
	 * Setup class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		   parent::__construct( [
			  'singular' => 'order_list', 
			  'plural'   => 'order_lists', 
			  'ajax'     => false
		   ] );
		   
		  $this->get_orders_list();
	}
	/**
        * Gets list of orders
        *
        * @since 1.0.0
        */
	public function get_orders_list() {
			$filter_status = $_POST['order_filter'];
		  	global $wpdb,$woocommerce,$product;
		  	$where = '';
		  	$join = '';

		  	$order_status = get_option('oimwc_selected_order_status');
        	if( is_array($order_status) && count($order_status) > 0 ){
            	$order_status = "('".implode("','", $order_status)."')";
        	}else{
            	$order_status = "('wc-processing','wc-on-hold')";
        	}

		  	if( isset($filter_status) && $filter_status != '' && isset($_POST["submit_btn"])){
        		if( $filter_status == 1 ){
        			$join .= 'LEFT JOIN '.$wpdb->prefix.'postmeta E ON WO.order_id = E.post_id';
        			$where .= 'AND E.meta_key = "order_status" AND E.meta_value IN (0,1) '; 
	            }  
	            if( $filter_status == 2 ){
        			$join .= 'LEFT JOIN '.$wpdb->prefix.'postmeta E ON WO.order_id = E.post_id';
        			$where .= 'AND E.meta_key = "order_status" AND E.meta_value = '.$filter_status; 
	            }  
	            if( $filter_status == 'yes' ){
	            	$join .= 'LEFT JOIN '.$wpdb->prefix.'postmeta E ON a.meta_value = E.post_id';
	                $where .= 'AND E.meta_key = "oimwc_discontinued_product" AND E.meta_value = "'.$filter_status.'"';
	            }
	            $sql = 'SELECT DISTINCT WO.order_id,DATE_FORMAT(p.post_date,"%M %d, %Y") as date,a.meta_value AS product_id,b.meta_value AS qty,a.order_item_id
	                FROM '.$wpdb->prefix.'woocommerce_order_itemmeta AS a
	                LEFT JOIN '.$wpdb->prefix.'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
	                LEFT JOIN '.$wpdb->prefix.'woocommerce_order_items AS WO ON ( WO.order_item_id = b.order_item_id )
	                LEFT JOIN '.$wpdb->prefix.'posts AS p ON ( p.ID = WO.order_id ) '.$join.'
	                WHERE a.meta_key = "_product_id" AND b.meta_key = "_qty"'.$where.'
	                AND p.post_status IN '.$order_status.' order by WO.order_id ASC';
        	}
        	else{
			  	$sql = 'SELECT DISTINCT WO.order_id,DATE_FORMAT(p.post_date,"%Y-%m-%d") as date,a.meta_value AS product_id,b.meta_value AS qty,a.order_item_id
				FROM '.$wpdb->prefix.'woocommerce_order_itemmeta AS a
				LEFT JOIN '.$wpdb->prefix.'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
				LEFT JOIN '.$wpdb->prefix.'woocommerce_order_items AS WO ON ( WO.order_item_id = b.order_item_id )
				LEFT JOIN '.$wpdb->prefix.'posts AS p ON ( p.ID = WO.order_id )
				WHERE a.meta_key = "_product_id" AND b.meta_key = "_qty"
				AND p.post_status IN '.$order_status.' order by WO.order_id ASC';	
			}	
	
		  	$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		  
		  	if($result)
		  	{
		    
			  $arr = array();
			  foreach($result as $oid)
			  {
				  $arr[$oid['order_id']][] = array('product_id'=>$oid['product_id'],
												 'order_item_id'=>$oid['order_item_id'],
												 'date'=>$oid['date'],
												 'qty'=>$oid['qty']
												 );
			  }
			  foreach($arr as $key=>$a_rr)
			  {	
				  $product_arr = array();
				  $order_date =	 '';
				 
				  foreach($a_rr as $a)
				  {
					 $order_date = $a['date'];
					 $product_id = $a['product_id'];
					 
					 $variation_id = wc_get_order_item_meta($a['order_item_id'],'_variation_id',true);
					 $parent_prod_id = 0;
					 if($variation_id) 
					 {
						$product_id = $variation_id;
						$parent_prod_id = $a['product_id'];
					 }
					 					 
					 $product_name = get_the_title($product_id);
					 $sku = get_post_meta( $product_id, '_sku', true );
					 if($sku != ''){
					 	$sku = ' | '.$sku;
					 }
					 else{
					 	$sku = '';
					 }
					 $this->product_id_name_arr[$product_id] = array('name'=>$product_name,'parent_prod_id'=>$parent_prod_id,'sku'=>$sku);
					 $product_arr[$product_id] = $a['qty'];
					 
					 if(isset($this->order_prod_qty[$product_id]))
					 {
						$this->order_prod_qty[$product_id] = $this->order_prod_qty[$product_id] + $a['qty'];	
					 }
					 else
					 {					
						$this->stock_prod[$product_id] = (int)get_post_meta($product_id,'_stock',true);	
						$manage_stock = get_post_meta( $product_id, '_manage_stock', true );
						$status = 2;
						if( $manage_stock == 'no' ){
							$_stock_status = get_post_meta( $product_id, '_stock_status', true );
							if( $_stock_status == 'instock' || $_stock_status == 'onbackorder' ){
								$status = 1;
							}else{
								$status = 0;
							}
						}
						$this->stock_status[] = $status;	
						
						$this->order_prod_qty[$product_id] = $a['qty'];	
					 }
				  }
				  
				  $this->order_ids_arr[] = array('order_id'=>$key,
												  'date'=>$order_date,
												  'order_qty'=>$product_arr
												 );
			  }
			  foreach($this->order_prod_qty as $key=>$value)
			  {
					$manage_stock = get_post_meta( $key, '_manage_stock', true );
					if( $manage_stock == 'no' ){
						$_stock_status = get_post_meta( $key, '_stock_status', true );
						if( $_stock_status == 'instock' || $_stock_status == 'outofstock' ){
							$this->physical_stock[$key] = 0;	
						}
					}
					else
					{
						$this->physical_stock[$key]  = $value + $this->stock_prod[$key];
					}
			  }
			  foreach($this->stock_prod as $p_id=>$stock_pro)
			  {
					$manage_stock = get_post_meta( $p_id, '_manage_stock', true );
					if( $manage_stock == 'no' ){
						$_stock_status = get_post_meta( $p_id, '_stock_status', true );
						if( $_stock_status == 'instock' || $_stock_status == 'outofstock' ){
							$this->stock_prod[$p_id] = 0;	
						}
					}
			  }
			  
			  $this->order_ids_arr[] = array('order_id'=>__('Total Product Ordered','order-and-inventory-manager-for-woocommerce'),'date'=>'','order_qty'=>$this->order_prod_qty, 'footer_row' => 'footer_row_first');
			  $this->order_ids_arr[] = array('order_id'=>__('WooCommerce stock Qty','order-and-inventory-manager-for-woocommerce'),'date'=>'','order_qty'=>$this->stock_prod, 'footer_row' => 0);
			  $this->order_ids_arr[] = array('order_id'=>__('Physical stock Qty','order-and-inventory-manager-for-woocommerce'),'date'=>'','order_qty'=>$this->physical_stock, 'footer_row' =>0);
			  $this->data = $this->order_ids_arr;
		}
	}
	/**
        * Returns message when no orders available
        *
        * @since 1.0.0
        */
	public function no_items() {
		  _e( 'No orders avaliable.', 'order-and-inventory-manager-for-woocommerce' );
	}  
	/**
        * Define what data to show on each column of the table
        *
        * @param  Array $item        Data
        * @param  String $column_name - Current column name
        *
        * @return Mixed
        * @since 1.0.0
        */
	public function column_default( $item, $column_name ) {
	
		switch ( $column_name ) {
			case 'date':
			  return esc_html(date_i18n( get_option( 'date_format' ), strtotime( $item[ $column_name ] )));
			case 'order_id':
			 if(is_numeric($item[ $column_name ]))
			 {
				 $link = sprintf('<a href="post.php?post=%s&action=edit">#%s</a>', $item[ $column_name ], $item[ $column_name ]) ;
				  return $link.'<span class="order_status_mark"></span>';
			 }
			 return $item[ $column_name ];
			default:
				if(isset($item['order_qty'][$column_name]))
				{
					return $item['order_qty'][$column_name];
				}
				return 0;
		  }
	}
	/**
        * Displays data
        *
        * @since 1.0.0
        */
	public function display() {
			global $footer_row;
			$singular = $this->_args['singular'];

			//$this->display_tablenav( 'top' );

			$this->screen->render_screen_reader_content( 'heading_list' );
			include_once( OIMWC_TEMPLATE . 'order_overview_filter.php' );
	?>
	<div class="wp_order_table_panel oimwc-table-shadow">
		<div class="order_overview_loader"></div>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?> stripe row-border order-column" id="wp_order_table2">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list order-overview-tbody"<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				} ?>>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
			<tfoot>
				<?php echo $footer_row;?>
			</tfoot>

		</table>
	</div>
	<input type="hidden" id="product_status" value='<?php echo json_encode($this->stock_status); ?>' />
	<?php
		//$this->display_tablenav( 'bottom' );
	}
	/**
        * Override the parent columns method. Defines the columns to use in your listing table
        *
        * @return Array
        */
	function get_columns() {
		  		  
		  $columns = array('date' => __( 'Date', 'order-and-inventory-manager-for-woocommerce' ),
			'order_id' => __( 'Order ID', 'order-and-inventory-manager-for-woocommerce' )
  		  );
			
		  $pro_id_arr = $this->product_id_name_arr;
		  foreach($pro_id_arr as $key=>$prod_arr)
		  {
			  
				if(isset($prod_arr['parent_prod_id']) && !empty($prod_arr['parent_prod_id']))
				{
					$columns[$key] = sprintf('<a href="post.php?post=%s&action=edit" target="_blank">%s</a> %s',$prod_arr['parent_prod_id'],$prod_arr['name'], $prod_arr['sku']);	
				}
				else
				{
					$columns[$key] = sprintf('<a href="post.php?post=%s&action=edit" target="_blank">%s</a>%s',$key,$prod_arr['name'] ,$prod_arr['sku']);	
				}
		  }
		  
		  return $columns;
	}
	/**
        * Prepare the items for the table to process
        *
        * @return Void
        */
	public function prepare_items() {

		  $this->_column_headers = array($this->get_columns());

		  $per_page     = $this->get_items_per_page( 'orders_per_page', 100 );
		  $current_page = $this->get_pagenum();

		  $this->items = $this->data;
		  	  
	}
	/**
        * Gets single row
        *
         * @param Array $item
        * @return Void
        */
	public function single_row( $item ){
		global $footer_row;
		$class = '';
		if( isset( $item['footer_row'] ) ){
			$class = 'footer_row '. ( $item['footer_row'] ? $item['footer_row'] : '' );
		}
		ob_start();
		echo '<tr class="'.$class.'">';
		$this->single_row_columns( $item );
		echo '</tr>';
		$data = ob_get_contents();
		ob_get_clean();
		if($class){
			$footer_row .= $data;
		}
		else{
			echo $data;
		}
	}
	/**
        * Gets single row columns
        *
         * @param Array $item
        * @return Void
        */
	protected function single_row_columns( $item ) {
                list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
                foreach ( $columns as $column_name => $column_display_name ) {
                        $classes = "$column_name column-$column_name";
                        if ( $primary === $column_name ) {
                                $classes .= ' has-row-actions column-primary';
                        }
                        if ( in_array( $column_name, $hidden ) ) {
                                $classes .= ' hidden';
                        }
                        if( isset( $item['footer_row'] ) ){
							if ( 'date' === $column_name ) {
								//continue;
								$classes .= ' hidden';
							}
						}
                        // Comments column uses HTML in the display name with screen reader text.
                        // Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
                        $data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';
                        $attributes = "class='$classes' $data";
                        if( isset( $item['footer_row'] ) ){
							if ( 'order_id' === $column_name ) {
								$attributes .=  ' colspan=2';
							}
						}
                        if ( 'cb' === $column_name ) {
                                echo '<th scope="row" class="check-column">';
                                echo $this->column_cb( $item );
                                echo '</th>';
                        } elseif ( method_exists( $this, '_column_' . $column_name ) ) {
                                echo call_user_func(
                                        array( $this, '_column_' . $column_name ),
                                        $item,
                                        $classes,
                                        $data,
                                        $primary
                                );
                        } elseif ( method_exists( $this, 'column_' . $column_name ) ) {
                                echo "<td $attributes><div class='product_qty'>";
                                echo call_user_func( array( $this, 'column_' . $column_name ), $item );
                                echo $this->handle_row_actions( $item, $column_name, $primary );
                                echo "</div></td>";
                        } else {
                                echo "<td $attributes><div class='product_qty'>";
                                echo $this->column_default( $item, $column_name );
                                echo $this->handle_row_actions( $item, $column_name, $primary );
                                echo "</div></td>";
                        }
                }
        }
	
}

?>