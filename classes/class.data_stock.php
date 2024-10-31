<?php
/**
 * DataStock class
 *
 * Handles stock values, and displays total stock in each used currency.
 *
 * @since    1.0.0
 */
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * DataStock stock values, and displays total stock in each used currency.
 */
class DataStock extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     * 
     * @since 1.0.0
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        //usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Get Columns
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @since 1.0.0
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'date'       		=>  __('Date','order-and-inventory-manager-for-woocommerce')
        );
		$currency = $this->get_used_currency();
		if( $currency ){
			foreach( $currency as $curr ){
				$columns['product_in_'. strtolower($curr['currency']) ] = $curr['currency'];
			}
		}
        return $columns;
    }
    
    /**
     * Get used currency
     * gets used supplier currency
     *
     * @since 1.0.0
     * @return Array of currency
     */
	public function get_used_currency(){
		global $wpdb;
        $result = [];
        $args = array(
	            'post_type' => array('supplier'),
                'post_status' => array( 'private', 'publish' ),
                'meta_key'=>'oimwc_supplier_currency',
                'posts_per_page' => -1
            );
        $supplier_list = new WP_Query( $args );
        $supplier_ids = array();
        if( $supplier_list->have_posts() ){
            while( $supplier_list->have_posts() ){
                $supplier_list->the_post();
                $supplier_ids[] = get_the_ID();
            }
            wp_reset_postdata();
        }
        $suppliers = implode(',', $supplier_ids);
        if($suppliers){
            $sql =  "SELECT meta_value AS currency FROM {$wpdb->prefix}postmeta WHERE `meta_key` LIKE 'oimwc_supplier_currency' and post_id in($suppliers) GROUP BY meta_value";
	        $result = $wpdb->get_results( $sql , ARRAY_A);
        }
		return $result;
	}

    /**
     * Define which columns are hidden
     *
     * @since 1.0.0
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('product_name' => array('product_name', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
		global $default_supplier, $wpdb;
        $data = array();

		wp_enqueue_style('woocommerce_admin_styles');
		
		$year = date("Y");
		if( isset( $_GET['year_stock'] ) && sanitize_text_field($_GET['year_stock']) != '' ){
			$year = sanitize_text_field($_GET['year_stock']);
		}
        $currency = $this->get_used_currency();
        $curren_date = date('Y-n-d');
        
        if( $year == date("Y") ){
            //$current_data = oimwc_get_current_day_stock( $curren_date, 'product_in_' );
            $data[strtotime($curren_date)] = array();//$current_data[ $year ][ date('n') ];
            $data[strtotime($curren_date)]['date'] = 'current';    
        }
        
        /*
        $daywise_stock_data = get_option( 'oimwc_daywise_stock_data' );
        
        if( $daywise_stock_data && is_array( $daywise_stock_data ) ){
            foreach( $daywise_stock_data as $key_date => $stock ){
                if( $currency ){
                    $data[ $key_date ]['date'] = $key_date;
                    foreach( $currency as $curr ){
                        if( array_key_exists( $curr['currency'], $stock ) ){
                            $currencies = strtolower($curr['currency']);
                            $data[ $key_date ]['product_in_'. $currencies ] = $stock[$curr['currency']];
                        }
                    }
                }       
            }
        }
        krsort($data);
        return $data;*/

		$stock_data = get_option( 'oimwc_stock_data' );
		if( $stock_data && is_array( $stock_data ) ){
			$stock_year = isset( $_GET['year_stock'] ) && $_GET['year_stock'] != '' ? sanitize_text_field($_GET['year_stock']) : date('Y');
            if( $stock_data[ $stock_year ] ){
    			foreach( $stock_data[ $stock_year ] as $month => $stock ){
    				if( $currency ){
    					$key_date = $month;
    					$data[ $key_date ]['date'] = $key_date;
    					foreach( $currency as $curr ){
    						if( array_key_exists( $curr['currency'], $stock ) ){
    							$currencies = strtolower($curr['currency']);
    							$data[ $key_date ]['product_in_'. $currencies ] = $stock[$curr['currency']];
    						}
    					}
    				}       
    			}
            }
		}
        krsort($data);
        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
		$currency = $this->get_used_currency();
		if( $currency ){			
            foreach( $currency as $curr ){
				if( 'product_in_'. strtolower($curr['currency']) == $column_name ){
					if( isset( $item[ $column_name ] ) ){
                        $price = wc_price($item[ $column_name ],array('currency'=> $curr['currency']));
                        return $price;
					}else{
                        if( $item[ 'date' ] == 'current' ){
                            return '<div class="stock_values_spinner lw_spin" data-curr='.$column_name.'><img src="'.OIMWC_PLUGIN_URL. 'images/loader.gif" /></div>';
                        }
						return '0 '.get_woocommerce_currency_symbol( $curr['currency'] );	
					}
				}
			}
		}
        switch( $column_name ) {
            case 'date':
            //return $item[ $column_name ];
                $current_month = date('n');
				$search_year = $year = date("Y");
				if( isset( $_GET['year_stock'] ) ):
					$search_year = sanitize_text_field($_GET['year_stock']);
				endif;
                $last_date_of_month = $search_year."-".$item[ $column_name ].'-'.'01';
                if($item[ $column_name ] == 'current' && $search_year == $year ){
                    $last_date =  __("Current value",'order-and-inventory-manager-for-woocommerce');
                }else{
                    //$last_date = date("Y-m-t",strtotime($last_date_of_month));
                    $last_date = date_i18n( get_option( 'date_format' ), strtotime( $last_date_of_month ) );
                }

                return $last_date;
            default:
                return $item[ $column_name ];
        }
    }
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @param Array $a stores order by field name
     * @param Array $b stores order by field name
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
		return;
    }
	
    public function pagination( $which ) {
        if ( empty( $this->_pagination_args ) ) {
            return;
        }
     
        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }
     
        if ( 'top' === $which && $total_pages > 1 ) {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }
     
        $output = '<span class="displaying-num">' . sprintf( _n( '%s '.__('record','order-and-inventory-manager-for-woocommerce'), '%s '.__('records','order-and-inventory-manager-for-woocommerce'), $total_items ), number_format_i18n( $total_items ) ) . '</span>';
     
        $current              = $this->get_pagenum();
        $removable_query_args = wp_removable_query_args();
     
        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
     
        $current_url = remove_query_arg( $removable_query_args, $current_url );
     
        $page_links = array();
     
        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';
     
        $disable_first = $disable_last = $disable_prev = $disable_next = false;
     
        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }
     
        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( remove_query_arg( 'paged', $current_url ) ),
                __( 'First page' ),
                '&laquo;'
            );
        }
     
        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }
     
        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;
     
        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
                __( 'Next page' ),
                '&rsaquo;'
            );
        }
     
        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                __( 'Last page' ),
                '&raquo;'
            );
        }
     
        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';
     
        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";
     
        echo $this->_pagination;
    }
}
	
?>