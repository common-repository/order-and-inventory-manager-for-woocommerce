<?php

/**
 * OIMWC_Order class
 *
 * Handles orders and reports
 *
 * @since    1.0.0
 */

if ( !class_exists( 'OIMWC_Order' ) ) {
    /**
     * OIMWC_Order Handles orders and reports
     *
     * @since    1.0.0
     */
    class OIMWC_Order
    {
        /**
         * Setup class.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            add_action( 'init', array( $this, 'download_order_file' ) );
            add_action( 'wp_ajax_save_po_file_settings', array( $this, 'save_po_file_settings' ) );
            add_action( 'wp_ajax_add_product_manually', array( $this, 'add_product_manually' ) );
            add_action( 'wp_ajax_add_product_manually_order_page', array( $this, 'add_product_manually_order_page' ) );
            add_action( 'wp_ajax_add_product_to_po', array( $this, 'add_product_to_po' ) );
            add_action( 'wp_ajax_update_product_file', array( $this, 'update_product_file' ) );
            add_action( 'wp_ajax_create_product_file', array( $this, 'create_product_file' ) );
            add_action( 'wp_ajax_complete_product_order', array( $this, 'complete_product_order' ) );
            add_action( 'wp_ajax_finalize_product_order', array( $this, 'finalize_product_order' ) );
            add_action( 'init', array( $this, 'generate_stock_report' ) );
            add_action( 'generate_stock_report', array( $this, 'generate_report' ) );
            $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
            if ( !$default_our_pack_size ) {
                update_option( 'oimwc_default_our_pack_size', 1 );
            }
            $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
            if ( !$default_supplier_pack_size ) {
                update_option( 'oimwc_default_supplier_pack_size', 1 );
            }
            $wpsearch_posttypes = get_option( 'oimwc_wpsearch_posttypes' );
            
            if ( !is_array( $wpsearch_posttypes ) ) {
                $arr = array( 'product', 'shop_order' );
                update_option( 'oimwc_wpsearch_posttypes', $arr );
            }
            
            $selected_order_status = get_option( 'oimwc_selected_order_status' );
            
            if ( !is_array( $selected_order_status ) ) {
                $arr = array( 'wc-processing', 'wc-on-hold' );
                update_option( 'oimwc_selected_order_status', $arr );
            }
            
            $oimwc_order_status_feature = get_option( 'oimwc_order_status_feature' );
            if ( !$oimwc_order_status_feature ) {
                update_option( 'oimwc_order_status_feature', "yes" );
            }
        }
        
        /**
         * Generate stock report
         * Schedules generation of stock report on every month's first date at 23:59:59
         *
         * @since 1.0.0
         */
        function generate_stock_report()
        {
            
            if ( !wp_next_scheduled( 'generate_stock_report' ) ) {
                $schedule_time = oimwc_getCurrentDateByTimeZone( 'Y-m-d' ) . ' 23:59:59';
                $schedule_time = get_gmt_from_date( $schedule_time, 'U' );
                wp_schedule_event( $schedule_time, 'daily', 'generate_stock_report' );
            }
        
        }
        
        /**
         * Get used currency
         * Gets used currency
         *
         * @since 1.0.0
         * @return array stores used currencies
         */
        public function get_used_currency()
        {
            global  $wpdb ;
            $sql = "SELECT meta_value AS currency FROM {$wpdb->prefix}postmeta WHERE meta_key = 'oimwc_supplier_currency' GROUP BY meta_value";
            $result = $wpdb->get_results( $sql, ARRAY_A );
            return $result;
        }
        
        /**
         * Generate report
         * Generates stock report, and updates stock value of oimwc_stock_data meta
         *
         * @since 1.0.0
         */
        function generate_report()
        {
            global  $wpdb ;
            $future_date = get_option( 'oimwc_stock_cron_time' );
            
            if ( strtotime( 'now' ) > strtotime( $future_date ) ) {
                $date = date( 'n' );
                $stock_data = get_option( 'oimwc_stock_data' );
                
                if ( !is_array( $stock_data ) ) {
                    $stock_data = [];
                } else {
                    $stock_data = oimwc_get_correct_array_values( $stock_data );
                }
                
                $current_stock = oimwc_get_current_day_stock( $date );
                foreach ( $current_stock as $key => $stock ) {
                    $date = array_keys( $stock );
                    $stock_data[$key][$date[0]] = $stock[$date[0]];
                }
                $date = new DateTime( 'now' );
                $date->modify( 'first day of next month' );
                $future_date = $date->format( 'Y-m-d 00:00:00' );
                update_option( 'oimwc_stock_cron_time', $future_date );
                update_option( 'oimwc_stock_data', $stock_data );
            }
        
        }
        
        /**
         * Get remaining order
         * Gets placed inventory orders which are not arrived.
         *
         * @since 1.0.0
         */
        public static function get_remaining_orders(
            $order_type,
            $page = 0,
            $supplier_id = '',
            $orderby = '',
            $order = ''
        )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $posts_table = $wpdb->prefix . 'posts';
            $post_meta = $wpdb->prefix . 'postmeta';
            $completed_order = 0;
            $limit = '';
            if ( isset( $order_type ) && $order_type == 'finalize_orders' ) {
                $completed_order = 1;
            }
            $perPage = 20;
            $start = 0;
            
            if ( $page ) {
                $start = $perPage * ($page - 1);
                $limit = " LIMIT {$start}, {$perPage} ";
            }
            
            $supplier_id_cond = '';
            if ( !empty($supplier_id) ) {
                $supplier_id_cond = " AND supplier_id = {$supplier_id}";
            }
            $orderby = ( !empty($orderby) ? $orderby : 'order_date' );
            $order = ( !empty($order) ? $order : 'DESC' );
            $left_join = $supplier_and_cond = '';
            
            if ( $orderby == 'supplier' ) {
                $left_join = " LEFT JOIN {$posts_table} as WPO ON ( WOI.supplier_id = WPO.ID )  ";
                $supplier_and_cond = " AND WPO.post_type = 'supplier' ";
                $orderby = 'post_title';
            }
            
            
            if ( $orderby == 'subtotal' ) {
                $sql = "SELECT WOI.*,COUNT(WOI.supplier_id) AS total_products, SUM( WPM.meta_value * WOI.requested_stock ) AS subtotal\r\n                        FROM {$table_name} AS WOI\r\n                        LEFT JOIN {$post_meta} AS WPM ON WOI.product_id = WPM.post_id\r\n                        WHERE WOI.completed_order = {$completed_order} {$supplier_id_cond} AND WPM.meta_key = 'oimwc_supplier_purchase_price'\r\n                        GROUP BY WOI.order_date, WOI.supplier_id ORDER BY subtotal {$order} {$limit}";
            } else {
                
                if ( $orderby == 'arrived_products' ) {
                    $sql = "SELECT *,COUNT(supplier_id) AS total_products, CAST( SUM( finalize_product )/2 AS SIGNED ) AS arrived_products FROM `{$table_name}` WHERE completed_order = {$completed_order} {$supplier_id_cond} GROUP BY order_date, supplier_id ORDER BY arrived_products {$order} {$limit}";
                } else {
                    
                    if ( $orderby == 'status' ) {
                        $sql = "SELECT *,COUNT(supplier_id) AS total_products, IF( ( MAX( finalize_product) > 0 OR MAX( arrvived_stock) > 0 ) AND MAX(completed_order) = 0, 'Receiving', IF( MAX(completed_order) = 0, 'Pending', 'Completed' ) ) AS status FROM {$table_name} WHERE completed_order = {$completed_order} {$supplier_id_cond} GROUP BY order_date, supplier_id ORDER BY status {$order} {$limit}";
                    } else {
                        $sql = "SELECT WOI.*,COUNT(WOI.supplier_id) AS total_products\r\n                        FROM `{$table_name}` AS WOI {$left_join} \r\n                        WHERE WOI.completed_order = {$completed_order} {$supplier_id_cond} {$supplier_and_cond}\r\n                        GROUP BY WOI.order_date, WOI.supplier_id ORDER BY {$orderby} {$order} {$limit}";
                    }
                
                }
            
            }
            
            $data_result = $wpdb->get_results( $sql );
            return $data_result;
        }
        
        public static function get_total_remaining_orders( $order_type )
        {
            global  $wpdb ;
            $completed_order = 0;
            if ( isset( $order_type ) && $order_type == 'finalize_orders' ) {
                $completed_order = 1;
            }
            $table_name = $wpdb->prefix . 'order_inventory';
            $sql = "SELECT COUNT(*) AS total_products FROM `{$table_name}` WHERE completed_order = {$completed_order} GROUP BY order_date, supplier_id";
            $data_result = $wpdb->get_col( $sql );
            $perPage = 20;
            $total_pages = ceil( count( $data_result ) / $perPage );
            return $total_pages;
        }
        
        public static function get_po_supplier_list( $order_type )
        {
            global  $wpdb ;
            $completed_order = 0;
            if ( isset( $order_type ) && $order_type == 'finalize_orders' ) {
                $completed_order = 1;
            }
            $table_name = $wpdb->prefix . 'order_inventory';
            $sql = "SELECT supplier_id FROM `{$table_name}` WHERE completed_order = {$completed_order} AND supplier_id != 0 GROUP BY order_date, supplier_id";
            $result = $wpdb->get_col( $sql );
            return $result;
        }
        
        /**
         * Get total products
         * Gets count of total purchased product (supplier id wise)
         *
         * @param array $where stores supplier id and order date
         * @since 1.0.0
         * @return count of total product 
         */
        public static function get_total_products( $where )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $posts_table = $wpdb->prefix . 'posts';
            $temp_product_table = $wpdb->prefix . 'oimwc_temp_product';
            $sql = "SELECT COUNT(WOI.supplier_id) AS total_products\r\n             FROM `{$table_name}` as WOI\r\n                     LEFT JOIN {$posts_table} as WPO ON ( WOI.product_id = WPO.ID )\r\n                     WHERE WOI.supplier_id = {$where['supplier_id']} AND WOI.order_date = '{$where['order_date']}' AND WPO.post_status IN ('publish','private') AND WOI.temp_product = 0";
            $post_data_result = $wpdb->get_var( $sql );
            $temporary_product = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'oimwc_temp_product' );
            
            if ( count( $temporary_product ) > 0 ) {
                $sql = "SELECT COUNT(WOI.supplier_id) AS total_products\r\n                     FROM `{$table_name}` as WOI\r\n                     LEFT JOIN `{$temp_product_table}` as WPO ON ( WOI.product_id = WPO.id )\r\n                     WHERE WOI.supplier_id = {$where['supplier_id']} AND WPO.order_id = WOI.id AND WOI.temp_product = 1 AND WPO.order_date = '{$where['order_date']}'";
                $temp_data_result = $wpdb->get_var( $sql );
            }
            
            
            if ( count( $temp_data_result ) > 0 && count( $post_data_result ) > 0 ) {
                $data_result = $temp_data_result + $post_data_result;
            } else {
                $data_result = $post_data_result;
            }
            
            return $data_result;
        }
        
        /**
         * Get awaiting delivery total products
         * Gets count of total awaiting delivery product (supplier id wise)
         *
         * @param array $where stores supplier id and order date
         * @since 1.0.0
         * @return count of total product 
         */
        public static function get_awaiting_delivery_products( $where )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            //$sql = "SELECT COUNT(supplier_id) AS total_products FROM `{$table_name}` WHERE supplier_id = {$where['supplier_id']} AND order_date = '{$where['order_date']}' AND arrvived_stock != 0";
            $sql = "SELECT COUNT(supplier_id) AS total_products FROM `{$table_name}` WHERE supplier_id = {$where['supplier_id']} AND order_date = '{$where['order_date']}' AND finalize_product >= 1";
            $data_result = $wpdb->get_var( $sql );
            return $data_result;
        }
        
        /**
         * Get total purchase amount
         * Gets total amount of purchased products (Supplier id wise)
         *
         * @param array $where stores supplier id and order date
         * @since 1.0.0
         * @return array of total amount with currency symbol
         */
        public static function get_total_purchase_amount( $where )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $sql = "SELECT * FROM `{$table_name}` WHERE supplier_id = {$where['supplier_id']} AND order_date = '{$where['order_date']}'";
            $data_result = $wpdb->get_results( $sql );
            $total_price_pro = 0;
            $temporary_product = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "oimwc_temp_product" );
            
            if ( count( $temporary_product ) > 0 ) {
                $total_price_temp = 0;
                $temp_sql = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "order_inventory as WOI LEFT JOIN " . $wpdb->prefix . "oimwc_temp_product as WPO ON ( WOI.product_id = WPO.id ) WHERE WOI.supplier_id = {$where['supplier_id']} AND WPO.order_id = WOI.id AND WOI.temp_product = 1 AND WOI.order_date = '{$where['order_date']}'" );
                foreach ( $temp_sql as $key => $value ) {
                    $requested_stock = (int) $value->requested_stock;
                    $product_price = wc_format_decimal( $value->product_price, 2 );
                    $supplier_currency = get_post_meta( $value->supplier_id, 'oimwc_supplier_currency', true );
                    if ( $product_price ) {
                        $total_price_temp += $product_price * $requested_stock;
                    }
                }
            }
            
            if ( count( $data_result ) > 0 ) {
                foreach ( $data_result as $row ) {
                    $product_id = $row->product_id;
                    $requested_stock = (int) $row->requested_stock;
                    $product_price = wc_format_decimal( get_post_meta( $product_id, 'oimwc_supplier_purchase_price', true ), 2 );
                    $additional_supplier = oimwc_additional_supplier_details_from_product( $row->product_id, $where['supplier_id'] );
                    if ( is_array( $additional_supplier ) && count( $additional_supplier ) ) {
                        $product_price = wc_format_decimal( $additional_supplier['purchase_price'], 2 );
                    }
                    if ( $product_price ) {
                        $total_price_pro += $product_price * $requested_stock;
                    }
                    $supplier_currency = get_post_meta( $where['supplier_id'], 'oimwc_supplier_currency', true );
                }
            }
            $symbol = get_woocommerce_currency_symbol( $supplier_currency );
            if ( $symbol ) {
                $symbol = " {$symbol}";
            }
            if ( count( $temporary_product ) > 0 && count( $data_result ) > 0 ) {
                $total_price = $total_price_temp + $total_price_pro;
            }
            if ( count( $temporary_product ) == 0 && count( $data_result ) > 0 ) {
                $total_price = $total_price_pro;
            }
            if ( count( $temporary_product ) > 0 && count( $data_result ) == 0 ) {
                $total_price = $total_price_temp;
            }
            
            if ( isset( $where['currency'] ) && $where['currency'] == 1 ) {
                return wc_price( $total_price, array(
                    'currency'          => $supplier_currency,
                    'decimal_separator' => '.',
                ) );
            } else {
                return wc_price( $total_price, array(
                    'currency' => $supplier_currency,
                ) );
            }
        
        }
        
        /**
         * Get ordered product
         * Gets total purchased products (Supplier id wise)
         *
         * @param integer $supplier_id stores supplier id if passed
         * @since 1.0.0
         * @return array of total purchased product
         */
        public static function get_ordered_product( $supplier_id = 0, $cache = true, $compare = '=' )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $posts_table = $wpdb->prefix . 'posts';
            $post_meta_table = $wpdb->prefix . 'postmeta';
            $additional_supplier_table = $wpdb->prefix . 'additional_supplier_info';
            //$sql = "SELECT product_id FROM `{$table_name}` WHERE arrvived_stock = '' ";
            //$sql = "SELECT product_id FROM `{$table_name}` WHERE completed_order = 0 ";
            $product_list = '';
            if ( $cache ) {
                $product_list = wp_cache_get( $supplier_id, 'oimwc_ordered_product_list_cache' );
            }
            
            if ( !$product_list ) {
                $sql = "SELECT WOI.product_id FROM `{$table_name}` as WOI\r\n                LEFT JOIN {$posts_table} as WPO ON WOI.product_id = WPO.ID\r\n                WHERE WOI.finalize_product = 0 and WPO.post_status IN ('publish','private')";
                if ( $supplier_id ) {
                    $sql .= ' AND WOI.supplier_id ' . $compare . $supplier_id;
                }
                $data_result = $wpdb->get_results( $sql );
                $product_list = array();
                if ( $data_result ) {
                    foreach ( $data_result as $data ) {
                        $product_list[] = $data->product_id;
                    }
                }
                
                if ( $supplier_id == 0 ) {
                    $sql_data = "SELECT DISTINCT(WPM.product_id) FROM " . $additional_supplier_table . " as WPM WHERE WPM.variable_id = 0";
                    if ( $supplier_id ) {
                        $sql_data .= ' AND WPM.supplier_id' . $compare . $supplier_id;
                    }
                    $result_data = $wpdb->get_results( $sql_data );
                    $product_info = array();
                    if ( $result_data ) {
                        foreach ( $result_data as $result ) {
                            $product_info[] = $result->product_id;
                        }
                    }
                    $query_data = "SELECT DISTINCT(WPM.product_id) FROM " . $additional_supplier_table . " as WPM WHERE WPM.variable_id != 0";
                    if ( $supplier_id ) {
                        $query_data .= ' AND WPM.supplier_id' . $compare . $supplier_id;
                    }
                    $variable_data = $wpdb->get_results( $query_data );
                    $variable_info = array();
                    if ( $variable_data ) {
                        foreach ( $variable_data as $variable ) {
                            $variable_info[] = $variable->product_id;
                        }
                    }
                    $query = 'SELECT DISTINCT(A.post_id) FROM ' . $post_meta_table . ' AS A 
                            LEFT JOIN ' . $post_meta_table . ' B ON A.post_id = B.post_id 
                            LEFT JOIN ' . $post_meta_table . ' C ON A.post_id = C.post_id 
                            LEFT JOIN ' . $post_meta_table . ' D ON A.post_id = D.post_id 
                            LEFT JOIN ' . $post_meta_table . ' E ON A.post_id = E.post_id 
                            LEFT JOIN ' . $posts_table . ' F ON A.post_id = F.ID 
                            WHERE A.meta_key = "oimwc_low_stock_threshold_level" AND B.meta_key = "_stock" 
                            AND C.meta_key = "oimwc_show_in_low_stock" AND C.meta_value = "yes" 
                            AND D.meta_key = "_manage_stock" AND D.meta_value = "yes" 
                            AND CAST(A.meta_value AS SIGNED) >= CAST(B.meta_value AS SIGNED) 
                            AND F.post_status IN ("private","publish")';
                    if ( $supplier_id ) {
                        $query .= ' AND E.meta_key = "oimwc_supplier_id" AND E.meta_value ' . $compare . $supplier_id;
                    }
                    $lowstock_result = $wpdb->get_results( $query );
                    $lowstock_list = array();
                    if ( $lowstock_result ) {
                        foreach ( $lowstock_result as $stock_data ) {
                            $lowstock_list[] = $stock_data->post_id;
                        }
                    }
                    $product_list = array_merge(
                        $product_list,
                        $product_info,
                        $lowstock_list,
                        $variable_info
                    );
                }
                
                wp_cache_add( $supplier_id, $product_list, 'oimwc_ordered_product_list_cache' );
            }
            
            return $product_list;
        }
        
        /**
         * Get ordered supplier
         * Gets supplier ids of ordered products
         *
         * @since 1.0.0
         * @param $supplier_id int 
         * @return array of supplier id
         */
        public static function get_ordered_supplier( $supplier_id = 0 )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $lock_product = '';
            //$sql = "SELECT supplier_id FROM `{$table_name}` WHERE arrvived_stock = '' ";
            $sql = "SELECT supplier_id FROM `{$table_name}` WHERE completed_order = 0 ";
            if ( $supplier_id ) {
                $sql = "SELECT * FROM `{$table_name}` WHERE completed_order = 0 AND supplier_id = {$supplier_id} GROUP BY order_date";
            }
            $data_result = $wpdb->get_results( $sql );
            $supplier_list = array();
            $lock_supplier_list = array();
            $order_status = array();
            if ( $data_result ) {
                foreach ( $data_result as $data ) {
                    
                    if ( $supplier_id ) {
                        $short_name = get_post_meta( $supplier_id, 'oimwc_supplier_short_name', true );
                        //$supplier_list[] = $data->order_date;
                        $order_date = date( 'Y-m-d', strtotime( $data->order_date ) );
                        $supplier_list[$data->order_date] = '#' . $data->order_number . ' | ' . $short_name . ' | ' . $order_date;
                        $lock_supplier_list[$data->order_date] = $data->lock_product;
                        $main_obj = OIMWC_MAIN::init();
                        $order_status[$data->order_date] = $main_obj->get_purchase_order_status( $supplier_id, $data->order_date, true );
                        $status = __( 'Pending', 'order-and-inventory-manager-for-woocommerce' );
                        
                        if ( !in_array( $status, $order_status ) ) {
                            $lock_product = '1';
                        } else {
                            $lock_product = '0';
                        }
                    
                    } else {
                        $short_name = get_post_meta( $data->supplier_id, 'oimwc_supplier_short_name', true );
                        $supplier_list[$data->supplier_id] = ( $short_name ? $short_name : get_the_title( $data->supplier_id ) );
                    }
                
                }
            }
            return array(
                'supplier_list'      => $supplier_list,
                'lock_supplier_list' => $lock_supplier_list,
                'lock_product'       => $lock_product,
                'order_status'       => $order_status,
            );
        }
        
        /**
         * Check product
         * When manually adding products to low stock, checks that product is already being added or not
         *
         * @param integer $product_id stores product id
         * @since 1.0.0
         * @return boolean has request or not
         */
        function check_product( $product_id, $supplier_id )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            //$sql = "SELECT count(id) FROM `{$table_name}` WHERE arrvived_stock = '' AND product_id ={$product_id}";
            $sql = "SELECT count(id) FROM `{$table_name}` WHERE completed_order = 0 AND product_id ={$product_id} AND supplier_id = {$supplier_id}";
            $has_request = $wpdb->get_var( $sql );
            return $has_request;
        }
        
        /**
         * Check product
         * When manually adding products to purchase order, checks that product is already being added or not in the current purchase order.
         *
         * @param integer $product_id stores product id
         * @since 1.0.0
         * @return boolean has request or not
         */
        function check_existing_product_PO( $product_id, $supplier_id, $order_date )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $sql = "SELECT count(id) FROM `{$table_name}` WHERE completed_order = 0 AND product_id ={$product_id} AND supplier_id = {$supplier_id} AND order_date = '{$order_date}'";
            $has_request = $wpdb->get_var( $sql );
            return $has_request;
        }
        
        /**
         * Insert
         * Inserts inventory order to order_inventory table
         *
         * @param array $data stores purchased order data
         * @since 1.0.0
         * @return integer of insert id of order
         */
        function insert( $data )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $wpdb->insert( $table_name, $data );
            return $wpdb->insert_id;
        }
        
        /**
         * Update
         * When ordered quantity is arrived, it updated quantity in order_inventory table. And gives message accordingly.
         *
         * @param array $data data to update
         * @param array $where order id to update
         * @since 1.0.0
         * @return integer total arrived products
         */
        function update( $data, $where )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $total_rows = $wpdb->update( $table_name, $data, $where );
            return $total_rows;
        }
        
        /**
         * Complete product order
         * When ordered quantity is arrived, it updates stock of arrived products.
         *
         * @since 1.0.0
         */
        function complete_product_order()
        {
            global  $wpdb, $OIMProductStock ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $message = $error = array();
            $message['success'] = false;
            $product_id = ( isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : 0 );
            $requested_qty = ( isset( $_POST['qty'] ) ? sanitize_text_field( $_POST['qty'] ) : 0 );
            $id = sanitize_text_field( $_POST['id'] );
            $message['row_id'] = $id;
            $finalize_product_status = $total_qty = 0;
            $arrived_qty = $requested_qty;
            
            if ( $product_id ) {
                //$requested_qty = $wpdb->get_var("SELECT requested_stock FROM {$wpdb->prefix}order_inventory WHERE id={$id} AND product_id={$product_id} AND arrvived_stock = '' ");
                $requested_qty = $wpdb->get_var( "SELECT requested_stock FROM {$wpdb->prefix}order_inventory WHERE id={$id} AND product_id={$product_id} AND finalize_product = 0" );
                $requested_stock = $requested_qty;
                $finalize_product_status = 2;
                $arrived_qty = $wpdb->get_var( "SELECT ( requested_stock - arrvived_stock ) AS stock FROM {$wpdb->prefix}order_inventory WHERE id={$id} AND product_id={$product_id} AND finalize_product = 0" );
            } else {
                $sql = $wpdb->get_row( "SELECT product_id,arrvived_stock,requested_stock,temp_product FROM {$wpdb->prefix}order_inventory WHERE id={$id}" );
                $product_id = $sql->product_id;
                $last_arrvived_stock = ( !empty($sql->arrvived_stock) ? $sql->arrvived_stock : 0 );
                // $last_arrvived_stock = $sql->arrvived_stock;
                $requested_stock = $sql->requested_stock;
            }
            
            $purchase_order_id = $wpdb->get_var( "SELECT order_number FROM {$wpdb->prefix}order_inventory WHERE id={$id}" );
            $supplier_pack_size = get_post_meta( $product_id, 'oimwc_supplier_pack_size', true );
            $default_supplier_pack = get_option( 'oimwc_default_supplier_pack_size' );
            $supplier_pack = ( $supplier_pack_size ? $supplier_pack_size : (( $default_supplier_pack ? $default_supplier_pack : 1 )) );
            $our_pack_size = get_post_meta( $product_id, 'oimwc_our_pack_size', true );
            $default_our_pack = get_option( 'oimwc_default_our_pack_size' );
            $our_pack = ( $our_pack_size ? $our_pack_size : (( $default_our_pack ? $default_our_pack : 1 )) );
            $supplier_previous_total_pieces = get_post_meta( $product_id, 'oimwc_previous_total_pieces', true );
            
            if ( $requested_qty || $product_id ) {
                $prev_stock = get_post_meta( $product_id, '_stock', true );
                
                if ( !$finalize_product_status ) {
                    $total_qty = $last_arrvived_stock + $requested_qty;
                    $result = $this->update( array(
                        'arrvived_stock' => sanitize_text_field( $total_qty ),
                    ), array(
                        'id' => $id,
                    ) );
                } else {
                    
                    if ( $sql->temp_product == 1 ) {
                        $result = $this->update( array(
                            'arrvived_stock'   => $requested_qty,
                            'finalize_product' => $finalize_product_status,
                        ), array(
                            'id' => $id,
                        ) );
                    } else {
                        $result = $this->update( array(
                            'arrvived_stock'   => $requested_qty,
                            'finalize_product' => $finalize_product_status,
                        ), array(
                            'id' => $id,
                        ) );
                    }
                
                }
                
                $supplier_previous_total_pieces = $prev_stock * $our_pack;
                $new_total_pieces = floor( $requested_qty * $supplier_pack + $supplier_previous_total_pieces );
                
                if ( $result === false ) {
                    $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
                } else {
                    
                    if ( $result ) {
                        $message['success'] = true;
                        
                        if ( $requested_qty ) {
                            /*$total_stock = floor($new_total_pieces/$our_pack);
                              $remaining_pieces = fmod($new_total_pieces, $our_pack);
                              /*update_post_meta($product_id, '_stock', sanitize_text_field($total_stock));
                              update_post_meta($product_id,'oimwc_physical_units_stock', sanitize_text_field($new_total_pieces));
                              update_post_meta($product_id,'oimwc_previous_total_pieces', sanitize_text_field($new_total_pieces));
                              update_post_meta($product_id,'oimwc_supplier_remaining_pieces', sanitize_text_field($remaining_pieces));*/
                            if ( $prev_stock <= 0 ) {
                                update_post_meta( $product_id, '_stock_status', "instock" );
                            }
                            
                            if ( $requested_stock == $requested_qty || $total_qty >= $requested_stock ) {
                                $message['order_arrival_status'] = __( 'Fully Arrived', 'order-and-inventory-manager-for-woocommerce' );
                                $message['arrival_stock'] = ( $finalize_product_status ? $requested_stock : $total_qty );
                                $this->update( array(
                                    'finalize_product' => 2,
                                ), array(
                                    'id' => $id,
                                ) );
                            } else {
                                $message['arrival_stock'] = $total_qty;
                            }
                            
                            $message['requested_stock'] = $requested_stock;
                            $update_finalize_qry = $wpdb->get_row( "select supplier_id,order_date,lock_product from {$table_name} where id = {$id}" );
                            $supplier_id = $update_finalize_qry->supplier_id;
                            $order_date = $update_finalize_qry->order_date;
                            $update_all_product = $this->update_finalize_to_completed_orders( $supplier_id, $order_date );
                            $message['update_all_product'] = $update_all_product;
                            $message['update_all_product_txt'] = '';
                            
                            if ( $update_all_product ) {
                                $message['update_all_product_txt'] = __( 'Completed', 'order-and-inventory-manager-for-woocommerce' );
                            } else {
                                $message['update_all_product_txt'] = __( 'Receiving', 'order-and-inventory-manager-for-woocommerce' );
                            }
                            
                            $lock_order = $update_finalize_qry->lock_product;
                            if ( !$lock_order ) {
                                $this->update( array(
                                    'lock_product' => 1,
                                ), array(
                                    'supplier_id' => $supplier_id,
                                    'order_date'  => $order_date,
                                ) );
                            }
                            $data = $OIMProductStock->update_purchased_product_stock(
                                $product_id,
                                $arrived_qty,
                                $supplier_pack,
                                $our_pack,
                                $purchase_order_id
                            );
                            $message['update_stock'] = $data['stock'];
                            $message['units_in_stock'] = $data['stock_in_units'];
                            oimwc_supplier_low_stock_count( 0, $supplier_id );
                            oimwc_show_all_product_stock_count(
                                $supplier_id,
                                false,
                                '=',
                                true
                            );
                        }
                        
                        $message['message'] = __( 'Stock updated successfully!', 'order-and-inventory-manager-for-woocommerce' );
                    } else {
                        $message['message'] = __( 'No row is updated!', 'order-and-inventory-manager-for-woocommerce' );
                    }
                
                }
            
            } else {
                $message['message'] = __( 'No product is found!', 'order-and-inventory-manager-for-woocommerce' );
            }
            
            $message['default'] = sprintf( '<tr><td colspan="6">%s</td></tr>', __( 'No products are found!', 'order-and-inventory-manager-for-woocommerce' ) );
            echo  json_encode( $message ) ;
            wp_die();
        }
        
        /**
         * Update product file
         * Update old orders in table and gives message accordingly.
         *
         * @since 1.0.0
         */
        function update_product_file()
        {
            $message = $error = array();
            $message['success'] = false;
            
            if ( !isset( $_POST['oimwc_product_nonce'] ) || !wp_verify_nonce( $_POST['oimwc_product_nonce'], 'oimwc_create_product' ) ) {
                $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
            } else {
                global  $wpdb ;
                $table_name = $wpdb->prefix . 'order_inventory';
                $product_id = sanitize_text_field( $_POST['product_id'] );
                $product_supplier_id = get_post_meta( $product_id, 'oimwc_supplier_id', true );
                
                if ( $product_supplier_id == $_POST['supplier_id'] ) {
                    $product_supplier_id = $product_supplier_id;
                } else {
                    $product_supplier_id = $_POST['supplier_id'];
                }
                
                $order_number = $_POST['order_number'];
                $temp_product_supplier = $wpdb->get_var( 'SELECT supplier_id FROM ' . $wpdb->prefix . 'oimwc_temp_product WHERE id=' . $product_id );
                
                if ( count( $temp_product_supplier ) > 0 ) {
                    $order_data = array(
                        'product_id'      => $product_id,
                        'stock'           => sanitize_text_field( $_POST['stock'] ),
                        'supplier_id'     => $temp_product_supplier,
                        'requested_stock' => sanitize_text_field( $_POST['qty'] ),
                        'arrvived_stock'  => '',
                        'order_date'      => sanitize_text_field( $_POST['request_date'] ),
                        'order_number'    => sanitize_text_field( $_POST['selected_order_id'] ),
                        'temp_product'    => 1,
                    );
                } else {
                    $order_data = array(
                        'product_id'      => $product_id,
                        'stock'           => sanitize_text_field( $_POST['stock'] ),
                        'supplier_id'     => $product_supplier_id,
                        'requested_stock' => sanitize_text_field( $_POST['qty'] ),
                        'arrvived_stock'  => '',
                        'order_date'      => sanitize_text_field( $_POST['request_date'] ),
                        'order_number'    => sanitize_text_field( $_POST['selected_order_id'] ),
                        'temp_product'    => 0,
                    );
                }
                
                $result = $this->insert( $order_data );
                
                if ( $result ) {
                    $message['success'] = true;
                    $message['message'] = __( 'The product is added to order file successfully.', 'order-and-inventory-manager-for-woocommerce' );
                    oimwc_supplier_low_stock_count( 0, $product_supplier_id );
                    oimwc_show_all_product_stock_count(
                        $product_supplier_id,
                        false,
                        '=',
                        true
                    );
                    $sql = $wpdb->query( 'UPDATE ' . $wpdb->prefix . 'oimwc_temp_product SET order_id =  ' . $result . ', order_date = "' . $order_date . '" where id = ' . $product_id );
                } else {
                    $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
                }
            
            }
            
            echo  json_encode( $message ) ;
            wp_die();
        }
        
        /**
         * Create product file
         * Inserts new orders in table and gives message accordingly.
         *
         * @since 1.0.0
         */
        function create_product_file()
        {
            global  $wpdb ;
            $message = $error = array();
            $message['success'] = false;
            
            if ( !isset( $_POST['oimwc_product_nonce'] ) || !wp_verify_nonce( $_POST['oimwc_product_nonce'], 'oimwc_create_product' ) ) {
                $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
            } else {
                $order_date = date( 'Y-m-d H:i:s' );
                $total_product_count = 0;
                
                if ( isset( $_POST['product'] ) && is_array( $_POST['product'] ) && count( $_POST['product'] ) ) {
                    $order_data = array();
                    $ordered_product_id = array();
                    $order_number = $wpdb->get_var( 'SELECT order_number from ' . $wpdb->prefix . 'order_inventory ORDER BY order_number DESC LIMIT 1' );
                    $order_number = $order_number + 1;
                    $supplier_id = 0;
                    $all_product_supplier_list = [];
                    foreach ( $_POST['product'] as $key => $data ) {
                        
                        if ( $data['qty'] ) {
                            $temp_product_stock = $wpdb->get_var( 'SELECT product_qty FROM ' . $wpdb->prefix . 'oimwc_temp_product WHERE supplier_id=' . $data['supplier'] . ' AND id=' . $key );
                            
                            if ( count( $temp_product_stock ) > 0 ) {
                                $order_data = array(
                                    'product_id'             => $key,
                                    'stock'                  => $data['qty'],
                                    'supplier_id'            => $data['supplier'],
                                    'requested_stock'        => $data['qty'],
                                    'arrvived_stock'         => '',
                                    'order_date'             => $order_date,
                                    'additional_information' => sanitize_text_field( $_POST['additional_info'] ),
                                    'order_number'           => $order_number,
                                    'finalize_product'       => 0,
                                    'temp_product'           => 1,
                                );
                            } else {
                                $order_data = array(
                                    'product_id'             => $key,
                                    'stock'                  => $data['stock'],
                                    'supplier_id'            => $data['supplier'],
                                    'requested_stock'        => $data['qty'],
                                    'arrvived_stock'         => '',
                                    'order_date'             => $order_date,
                                    'additional_information' => sanitize_text_field( $_POST['additional_info'] ),
                                    'order_number'           => $order_number,
                                    'temp_product'           => 0,
                                );
                            }
                            
                            $supplier_id = $data['supplier'];
                            $all_product_supplier_list[] = get_post_meta( $key, 'oimwc_supplier_id', true );
                            $result = $this->insert( $order_data );
                            
                            if ( $result ) {
                                $total_product_count++;
                                $ordered_product_id[] = $key;
                                $product_id = $key;
                                $product_supplier_list = oimwc_additional_supplier_from_product( $product_id );
                                $variation_supplier_list = oimwc_additional_supplier_from_product( $product_id, false );
                                $all_product_supplier_list = array_merge( $all_product_supplier_list, $product_supplier_list, $variation_supplier_list );
                                $sql = $wpdb->query( 'UPDATE ' . $wpdb->prefix . 'oimwc_temp_product SET order_id =  ' . $result . ', product_qty = ' . $data['qty'] . ' , order_date = "' . $order_date . '" where id = ' . $key );
                            }
                        
                        }
                    
                    }
                    
                    if ( is_array( $all_product_supplier_list ) && count( $all_product_supplier_list ) ) {
                        $all_product_supplier_list = array_unique( $all_product_supplier_list );
                        foreach ( $all_product_supplier_list as $product_supplier_id ) {
                            oimwc_supplier_low_stock_count( 0, $product_supplier_id );
                            oimwc_show_all_product_stock_count(
                                $product_supplier_id,
                                false,
                                '=',
                                true
                            );
                        }
                    }
                    
                    $request_data = get_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', true );
                    foreach ( $ordered_product_id as $key => $value ) {
                        if ( $request_data ) {
                            unset( $request_data[$value] );
                        }
                    }
                    update_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', $request_data );
                    $message['product_id'] = $ordered_product_id;
                    
                    if ( $total_product_count == count( $_POST['product'] ) ) {
                        //$remaning_orders = OIMWC_MAIN::pending_order_list();
                        //$message['orders'] = $remaning_orders;
                        $message['success'] = true;
                        $message['message'] = __( 'Purchase order file generated successfully!', 'order-and-inventory-manager-for-woocommerce' );
                        $message['default'] = sprintf( '<tr><td colspan="6">%s</td></tr>', __( 'No products are found!', 'order-and-inventory-manager-for-woocommerce' ) );
                        $message['redirect_url'] = admin_url() . 'admin.php?page=order-inventory-management&subpage=purchase-orders&view_order=1&supplier=' . $order_data['supplier_id'] . '&date=' . strtotime( $order_data['order_date'] );
                    } else {
                        
                        if ( $total_product_count > 0 ) {
                            $product_message = sprintf( __( 'Purchase order file generated for %d product!', 'order-and-inventory-manager-for-woocommerce' ), $total_product_count );
                            if ( $total_product_count == 1 ) {
                                $product_message = sprintf( __( 'Purchase order file generated for %d product!', 'order-and-inventory-manager-for-woocommerce' ), $total_product_count );
                            }
                            //$remaning_orders = OIMWC_MAIN::pending_order_list();
                            //$message['orders'] = $remaning_orders;
                            $message['message'] = sprintf( $product_message, $total_product_count );
                            $message['default'] = sprintf( '<tr><td colspan="6">%s</td></tr>', __( 'No products are found!', 'order-and-inventory-manager-for-woocommerce' ) );
                            $message['success'] = true;
                            $message['redirect_url'] = admin_url() . 'admin.php?page=order-inventory-management&subpage=purchase-orders&view_order=1&supplier=' . $order_data['supplier_id'] . '&date=' . strtotime( $order_data['order_date'] );
                        } else {
                            
                            if ( $total_product_count == 0 ) {
                                $message['message'] = __( 'No purchase order file was created!', 'order-and-inventory-manager-for-woocommerce' );
                            } else {
                                $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
                            }
                        
                        }
                    
                    }
                
                } else {
                    $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
                }
            
            }
            
            echo  json_encode( $message ) ;
            wp_die();
        }
        
        /**
         * Add product manually
         * Adds product in low stock with using sku and quantity.
         * If it is already being added, will give you proper message.
         *
         * @since 1.0.0
         */
        public function add_product_manually()
        {
            $message = $error = array();
            $message['success'] = false;
            
            if ( !isset( $_POST['oimwc_nonce'] ) || !wp_verify_nonce( $_POST['oimwc_nonce'], 'oimwc_add_product' ) ) {
                $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
            } else {
                global  $wpdb ;
                $product_sku = sanitize_text_field( $_POST['product_sku'] );
                $filtered_supplier = sanitize_text_field( $_POST['supplier_id'] );
                if ( empty($product_sku) ) {
                    $error[] = __( 'Please enter product ID.', 'order-and-inventory-manager-for-woocommerce' );
                }
                
                if ( count( $error ) ) {
                    $message['message'] = implode( "\n", $error );
                } else {
                    $sql = "SELECT post_id FROM `{$wpdb->postmeta}` WHERE meta_key LIKE '_sku' AND meta_value = '{$product_sku}'";
                    $product_id = $wpdb->get_var( $sql );
                    
                    if ( $product_id ) {
                        $supplier_id = $_POST['supplier_id'];
                        $check_product = $this->check_product( $product_id, $supplier_id );
                        
                        if ( $check_product ) {
                            $sku = get_post_meta( $product_id, '_sku', true );
                            $purchase_order_id = $wpdb->get_results( "SELECT order_number,order_date FROM {$wpdb->prefix}order_inventory WHERE product_id={$product_id} AND supplier_id={$filtered_supplier}", ARRAY_A );
                            foreach ( $purchase_order_id as $key => $value ) {
                                $adminUrl = admin_url() . "admin.php?page=order-inventory-management&subpage=purchase-orders&view_order=1&supplier=" . $supplier_id . "&date=" . strtotime( $value['order_date'] );
                                $msg[] = '<strong>' . $sku . '</strong>' . ': ' . __( 'This item is already in an active PO', 'order-and-inventory-manager-for-woocommerce' ) . ' ' . '<a href="' . $adminUrl . '" target="_blank">' . $value['order_number'] . '</a><br /><br />';
                            }
                            $msg[] .= '<br />' . __( 'Continue to add items to a new order?', 'order-and-inventory-manager-for-woocommerce' );
                            $message['check_product'] = true;
                            $message['success'] = true;
                            $message['message'] = $msg;
                            $message['product_id'] = $product_id;
                        } else {
                            $id = $product_id;
                            $post = get_post( $id );
                            $product_supplier = get_post_meta( $id, 'oimwc_supplier_product_url', true );
                            $product_stock = get_post_meta( $id, '_stock', true );
                            $product_sku = get_post_meta( $id, '_sku', true );
                            $warning_level = get_post_meta( $id, 'oimwc_low_stock_threshold_level', true );
                            $purchase_price = wc_format_decimal( get_post_meta( $id, 'oimwc_supplier_purchase_price', true ) );
                            $product_supplier_id = get_post_meta( $id, 'oimwc_supplier_id', true );
                            $purchase_currency = get_post_meta( $product_supplier_id, 'oimwc_supplier_currency', true );
                            $purchase_price = ( $purchase_price ? $purchase_price : 0 );
                            $product_type = $post->post_type;
                            $product_title = ( $product_type == 'product' ? get_the_title( $id ) : get_the_title( $post->post_parent ) );
                            $actual_product_id = ( $product_type == 'product' ? $id : $post->post_parent );
                            $product_variant = '-';
                            $image = '<img width="40" height="40" src="' . plugins_url( '/woocommerce/assets/images/placeholder.png' ) . '" />';
                            $product = wc_get_product( $id );
                            $price = $product->get_price();
                            
                            if ( $product_type == 'product_variation' ) {
                                $product = new WC_Product_Variation( $id );
                                $product_variant = $product->get_variation_attributes();
                                
                                if ( is_array( $product_variant ) && count( $product_variant ) ) {
                                    $variation_names = array();
                                    foreach ( $product_variant as $key => $value ) {
                                        $term = get_term_by( 'slug', $value, str_replace( "attribute_", "", $key ) );
                                        
                                        if ( !$term ) {
                                            $variation_names[] = $value;
                                        } else {
                                            $variation_names[] = $term->name;
                                        }
                                    
                                    }
                                    $product_variant = implode( ' | ', $variation_names );
                                } else {
                                    $product_variant = '-';
                                }
                                
                                
                                if ( has_post_thumbnail( $id ) ) {
                                    $image = get_the_post_thumbnail( $id, array( 40, 40 ) );
                                } else {
                                    if ( has_post_thumbnail( $post->post_parent ) ) {
                                        $image = get_the_post_thumbnail( $post->post_parent, array( 40, 40 ) );
                                    }
                                }
                                
                                $price = $product->get_price();
                            } else {
                                if ( has_post_thumbnail( $id ) ) {
                                    $image = get_the_post_thumbnail( $id, array( 40, 40 ) );
                                }
                            }
                            
                            
                            if ( $product_type == 'product_variation' ) {
                                $additional_supplier_id = oimwc_additional_supplier_from_product( $id, false );
                            } else {
                                $additional_supplier_id = oimwc_additional_supplier_from_product( $id );
                            }
                            
                            
                            if ( in_array( $filtered_supplier, $additional_supplier_id ) ) {
                                $product_supplier_id = $filtered_supplier;
                            } else {
                                $product_supplier_id = $product_supplier_id;
                            }
                            
                            
                            if ( $filtered_supplier == $product_supplier_id ) {
                                $warning_level = ( $warning_level ? $warning_level : 0 );
                                $product_stock = ( $product_stock ? $product_stock : 0 );
                                $non_format_purchase_price = $purchase_price;
                                $purchase_price = wc_price( $purchase_price, array(
                                    'currency' => $purchase_currency,
                                ) );
                                $product_title_a = sprintf( '<a href="%s" >%s</a>', get_edit_post_link( $actual_product_id ), $product_title );
                                $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
                                $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
                                $supplier_pack_size = get_post_meta( $id, 'oimwc_supplier_pack_size', true );
                                $supplier_pack = ( $supplier_pack_size ? $supplier_pack_size : (( $default_supplier_pack_size ? $default_supplier_pack_size : 1 )) );
                                $our_pack_size = get_post_meta( $id, 'oimwc_our_pack_size', true );
                                $our_pack = ( $our_pack_size ? $our_pack_size : (( $default_our_pack_size ? $default_our_pack_size : 1 )) );
                                $supplier_remaining_pieces = (int) get_post_meta( $id, 'oimwc_supplier_remaining_pieces', true );
                                $total_pieces = floor( $product_stock * $our_pack + $supplier_remaining_pieces );
                                $variant = ( isset( $product_variant ) && $product_variant == '' && $product_variant === FALSE ? '' : $product_variant );
                                $variant = ( $variant && $variant != '-' ? sprintf( '<div>%s: %s</div>', __( 'Variant', 'order-and-inventory-manager-for-woocommerce' ), $variant ) : '' );
                                $product_info = sprintf(
                                    '<div><div>%1$s</div>%2$s<div class="product_sku">%3$s: %4$s</div><div>%5$s: %6$s</div></div>',
                                    $product_title_a,
                                    $variant,
                                    __( 'Product ID', 'order-and-inventory-manager-for-woocommerce' ),
                                    $product_sku,
                                    __( 'Low stock threshold', 'order-and-inventory-manager-for-woocommerce' ),
                                    $warning_level
                                );
                                $supplier_name = ( $product_supplier_id ? get_the_title( $product_supplier_id ) : '-' );
                                $supplier_name = ( isset( $supplier_name ) && $supplier_name == '' && $supplier_name === FALSE ? '-' : $supplier_name );
                                $purchase_price = ( isset( $purchase_price ) && $purchase_price == '' && $purchase_price === FALSE ? '-' : $purchase_price );
                                $url_text = ( $product_supplier ? '<a href=' . $product_supplier . ' target="_blank">' . __( 'Link to product', 'order-and-inventory-manager-for-woocommerce' ) . '</a>' : '-' );
                                $div_text = '';
                                
                                if ( $url_text ) {
                                    $url_txt = __( 'URL', 'order-and-inventory-manager-for-woocommerce' );
                                    $supplier_url_text = sprintf( '<div>%s: %s</div>', $url_txt, $url_text );
                                }
                                
                                $supplier_sku = get_post_meta( $id, 'oimwc_supplier_product_id', true );
                                
                                if ( $supplier_sku ) {
                                    $supplier_sku_text = __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' );
                                    $supplier_sku = sprintf( '<div>%s: %s</div>', $supplier_sku_text, $supplier_sku );
                                }
                                
                                $supplier_note = get_post_meta( $id, 'oimwc_supplier_note', true );
                                
                                if ( $supplier_note ) {
                                    $supplier_note_text = __( 'Product Notes', 'order-and-inventory-manager-for-woocommerce' );
                                    $supplier_note = sprintf( '<div>%s: %s</div>', $supplier_note_text, $supplier_note );
                                }
                                
                                $supplier_info = sprintf(
                                    '<div>%1$s<div>%2$s: %3$s</div> %4$s %5$s %6$s</div>',
                                    $supplier_url_text,
                                    __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ),
                                    $purchase_price,
                                    $div_text,
                                    $supplier_sku,
                                    $supplier_note
                                );
                                $product_price = ( isset( $price ) && $price == '' && $price === FALSE ? '-' : wc_price( $price, array(
                                    'currency' => $purchase_currency,
                                ) ) );
                                $our_pack_size = ( isset( $our_pack ) && $our_pack == '' && $our_pack === FALSE ? '-' : $our_pack );
                                $total_pieces = ( isset( $total_pieces ) && $total_pieces == '' && $total_pieces === FALSE ? '-' : $total_pieces );
                                $product_stock = ( isset( $product_stock ) && $product_stock == '' && $product_stock === FALSE ? '-' : (int) $product_stock );
                                $div_text = '';
                                $product_detail = sprintf(
                                    '<div><div>%1$s: %2$s</div>%3$s<div>%4$s: %5$s</div><div>%6$s: %7$s</div></div>',
                                    __( 'Shop price', 'order-and-inventory-manager-for-woocommerce' ),
                                    $product_price,
                                    $div_text,
                                    __( 'Items in stock', 'order-and-inventory-manager-for-woocommerce' ),
                                    $product_stock,
                                    __( 'Physical units in stock', 'order-and-inventory-manager-for-woocommerce' ),
                                    $total_pieces
                                );
                                $cls = $msg = $qty_column = '';
                                
                                if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() ) {
                                    $cls = 'disabled_panel tips';
                                    $msg = OIMWC_SILVER_UPGRDAE_NOTICE;
                                }
                                
                                $qty_column = '<div class="' . $cls . '" data-tip="' . $msg . '"><input type="text" class="arrived_qty_handler" data-stock="' . $product_stock . '" data-warning="' . $warning_level . '" data-id="' . $id . '" name="product[' . $id . '][qty]" /></div>';
                                $qty_column .= '<input type="hidden" name="product[' . $id . '][stock]" value="' . $product_stock . '" /><input type="hidden" name="product[' . $id . '][supplier]" value="' . $product_supplier_id . '" />';
                                $qty_column .= '<div class="product_calc"><span data-price="' . $non_format_purchase_price . '" class="amount">0</span> <span class="currency">' . $purchase_currency . '</span></div>';
                                $qty_column .= '<div class="' . $cls . '" data-tip="' . $msg . '"><input type="button" class="button btnAddItemToOrder" value="' . __( 'Add to order', 'order-and-inventory-manager-for-woocommerce' ) . '" />';
                                $qty_column .= '<input type="button" data-id="' . $id . '" class="btnRemovePO manual_prod button btn_"' . $id . '" value="' . __( 'Remove', 'order-and-inventory-manager-for-woocommerce' ) . '" style="display: none;" />';
                                $product_class = ( $variant ? '' : 'simple' );
                                $purchase_order_preview_data = get_post_meta( $product_supplier_id, 'oimwc_supplier_purchase_order_data', true );
                                
                                if ( $purchase_order_preview_data ) {
                                    $purchase_data = implode( ',', array_keys( $purchase_order_preview_data ) );
                                } else {
                                    $purchase_data = '';
                                }
                                
                                $content = '<tr>
                                            <td class="thumb column-thumb has-row-actions column-primary" data-colname="Image">%s <span type="hidden" name="purchase_order_data" class="purchase_order_data" style="display: none;">%s</span><input type="hidden" class="productId product_%s" name="productId" value="%s" /><div class="mobile_prod_info"><div>%s</div><div class="%s">%s</div></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>
                                            <td class="product_info column-product_info" data-colname="Product Info">%s</td>
                                            <td class="supplier_info column-supplier_info" data-colname="Supplier Info">%s</td>
                                            <td class="product_detail column-product_detail" data-colname="Product Price &amp; Stock">%s</td>
                                            <td class="amount column-amount" data-colname="Qty">%s</td>
                                            </tr>
                                            <tr>
                                            <td colspan="5"><div class="table_seperator">
                                            </div></td>
                                            </tr>';
                                $message['success'] = true;
                                $message['id'] = $id;
                                $message['message'] = __( 'This product is already added!', 'order-and-inventory-manager-for-woocommerce' );
                                $message['data'] = sprintf(
                                    $content,
                                    $image,
                                    $purchase_data,
                                    $id,
                                    $id,
                                    $product_title,
                                    $product_class . '_product',
                                    $variant,
                                    $product_info,
                                    $supplier_info,
                                    $product_detail,
                                    $qty_column
                                );
                            } else {
                                $message['message'] = __( 'This product is not from selected supplier. Please select supplier of this product and then add this product!', 'order-and-inventory-manager-for-woocommerce' );
                            }
                        
                        }
                    
                    } else {
                        $message['message'] = __( 'No product has this ID, please check and try again.', 'order-and-inventory-manager-for-woocommerce' );
                    }
                
                }
            
            }
            
            echo  json_encode( $message ) ;
            wp_die();
        }
        
        /**
         * Add product manually to view order page
         * Adds product in view order page with using sku and quantity.
         * If it is already being added, will give you proper message.
         *
         * @since 1.0.0
         */
        public function add_product_manually_order_page()
        {
            $message = $error = array();
            $message['success'] = false;
            
            if ( !isset( $_POST['oimwc_nonce'] ) || !wp_verify_nonce( $_POST['oimwc_nonce'], 'oimwc_add_product' ) ) {
                $message['message'] = __( 'Ops! Something is wrong. Please try again.', 'order-and-inventory-manager-for-woocommerce' );
            } else {
                global  $wpdb ;
                $product_sku = sanitize_text_field( $_POST['product_sku'] );
                $requested_stock = sanitize_text_field( $_POST['requested_stock'] );
                
                if ( empty($product_sku) && empty($requested_stock) ) {
                    $error[] = __( 'Please enter product ID.', 'order-and-inventory-manager-for-woocommerce' );
                    $error[] = __( 'Please enter product qty', 'order-and-inventory-manager-for-woocommerce' );
                }
                
                
                if ( count( $error ) ) {
                    $message['message'] = implode( "\n", $error );
                } else {
                    $sql = "SELECT post_id FROM `{$wpdb->postmeta}` WHERE meta_key LIKE '_sku' AND meta_value = '{$product_sku}'";
                    $product_id = $wpdb->get_var( $sql );
                    $supplier_id = sanitize_text_field( $_POST['supplier'] );
                    $order_date = date( 'Y-m-d H:i:s', sanitize_text_field( $_REQUEST['date'] ) );
                    
                    if ( $product_id ) {
                        $check_product = $this->check_existing_product_PO( $product_id, $supplier_id, $order_date );
                        
                        if ( $check_product ) {
                            $message['message'] = __( 'This product is already in the order!', 'order-and-inventory-manager-for-woocommerce' );
                        } else {
                            $db_supplier_id = get_post_meta( $product_id, 'oimwc_supplier_id', true );
                            
                            if ( $supplier_id == $db_supplier_id ) {
                                $product = get_post( $product_id );
                                $product_type = $product->post_type;
                                $product_title = ( $product_type == 'product' ? get_the_title( $product->ID ) : get_the_title( $product->post_parent ) );
                                
                                if ( $product_type == 'product_variation' ) {
                                    $product = new WC_Product_Variation( $product_id );
                                    $product_variant = $product->get_variation_attributes();
                                    
                                    if ( is_array( $product_variant ) && count( $product_variant ) ) {
                                        $variation_names = array();
                                        foreach ( $product_variant as $key => $value ) {
                                            $term = get_term_by( 'slug', $value, str_replace( "attribute_", "", $key ) );
                                            
                                            if ( !$term ) {
                                                $variation_names[] = $value;
                                            } else {
                                                $variation_names[] = $term->name;
                                            }
                                        
                                        }
                                        $product_variant = implode( ' | ', $variation_names );
                                    } else {
                                        $product_variant = '';
                                    }
                                
                                }
                                
                                $product_title .= ( !empty($product_variant) ? ' | ' . $product_variant : '' );
                                $message['message'] = sprintf( __( 'Do you want to add %d qty of %s ?', 'order-and-inventory-manager-for-woocommerce' ), $requested_stock, $product_title );
                                $message['product_id'] = $product_id;
                                $message['result'] = true;
                            } else {
                                $message['message'] = __( 'This product is not from the current supplier. Please enter product ID of this supplier!', 'order-and-inventory-manager-for-woocommerce' );
                            }
                        
                        }
                    
                    } else {
                        $message['message'] = __( 'No product has this ID, please check and try again.', 'order-and-inventory-manager-for-woocommerce' );
                    }
                
                }
            
            }
            
            wp_send_json_success( $message );
            die;
        }
        
        function add_product_to_po()
        {
            global  $wpdb ;
            $message = array();
            $supplier_id = sanitize_text_field( $_POST['supplier'] );
            $order_date = date( 'Y-m-d H:i:s', sanitize_text_field( $_REQUEST['date'] ) );
            $order_number = sanitize_text_field( $_POST['order_number'] );
            $product_id = sanitize_text_field( $_POST['product_id'] );
            $product_stock = get_post_meta( $product_id, '_stock', true );
            $arrival_date = $wpdb->get_var( 'select arrival_date from ' . $wpdb->prefix . 'order_inventory where supplier_id = ' . $supplier_id . ' AND order_date = "' . $order_date . '"' );
            $requested_stock = sanitize_text_field( $_POST['requested_stock'] );
            $order_data = array(
                'order_number'     => $order_number,
                'product_id'       => $product_id,
                'stock'            => $product_stock,
                'supplier_id'      => $supplier_id,
                'requested_stock'  => $requested_stock,
                'arrvived_stock'   => '',
                'order_date'       => $order_date,
                'arrival_date'     => $arrival_date,
                'finalize_product' => 0,
                'completed_order'  => 0,
            );
            $result = $this->insert( $order_data );
            oimwc_supplier_low_stock_count( 0, $supplier_id );
            oimwc_show_all_product_stock_count(
                $supplier_id,
                false,
                '=',
                true
            );
            require_once OIMWC_CLASSES . 'class.view_order_product_list.php';
            ob_start();
            $obj = new ViewOrderTable();
            $obj->prepare_items();
            $obj->display();
            $result = ob_get_contents();
            $message['result'] = $result;
            ob_clean();
            $message['message'] = __( 'The product successfully added to order!', 'order-and-inventory-manager-for-woocommerce' );
            wp_send_json_success( $message );
            die;
        }
        
        function save_po_file_settings()
        {
            $supplier_id = sanitize_text_field( $_POST['supplier_id'] );
            $order_date = date( 'Y-m-d H:i:s', sanitize_text_field( $_POST['order_date'] ) );
            $column_names = ( isset( $_POST['manage_po_chks'] ) ? $_POST['manage_po_chks'] : '' );
            $save_default_sett = ( isset( $_POST['save_default_sett_chk'] ) ? $_POST['manage_po_chks'] : '' );
            $download_po_lang = sanitize_text_field( $_POST['manage_po_lang_dd'] );
            $shipping_address = sanitize_text_field( $_POST['select_shipping_address'] );
            $download_file_type = ( isset( $_POST['download_po_file'] ) ? $_POST['download_po_file'] : '' );
            $save_flag = ( isset( $_POST['save_default_sett_chk'] ) ? 1 : 0 );
            $delivery_date = ( isset( $_POST['delivery_date'] ) ? $_POST['delivery_date'] : '' );
            $shipping_method = ( isset( $_POST['save_default_sett_chk'] ) ? $_POST['shipping_method'] : '' );
            $shipping_terms = ( isset( $_POST['save_default_sett_chk'] ) ? $_POST['shipping_terms'] : '' );
            $po_attn = ( isset( $_POST['save_default_sett_chk'] ) ? $_POST['po_attn'] : '' );
            $office_address = sanitize_text_field( $_POST['select_office_address'] );
            update_post_meta( $supplier_id, 'oimwc_default_po_settings', $column_names );
            update_post_meta( $supplier_id, 'oimwc_default_po_settings_flag', $save_default_sett );
            update_post_meta( $supplier_id, 'oimwc_download_po_lang', $download_po_lang );
            update_post_meta( $supplier_id, 'oimwc_supplier_shipping_address', $shipping_address );
            update_post_meta( $supplier_id, 'oimwc_download_po_file', $download_file_type );
            update_post_meta( $supplier_id, 'oimwc_delivery_date', $delivery_date );
            update_post_meta( $supplier_id, 'oimwc_shipping_method', $shipping_method );
            update_post_meta( $supplier_id, 'oimwc_shipping_terms', $shipping_terms );
            update_post_meta( $supplier_id, 'oimwc_po_attn', $po_attn );
            update_post_meta( $supplier_id, 'oimwc_supplier_office_address', $office_address );
            $site_url = site_url( 'wp-admin' );
            $download_po_link = add_query_arg( array(
                'action'           => 'download_order_file',
                'supplier'         => $supplier_id,
                'date'             => strtotime( $order_date ),
                'po_lang'          => $download_po_lang,
                'download_po_file' => $download_file_type,
                'save_po_file'     => 'no',
            ), $site_url );
            
            if ( !$save_default_sett ) {
                $column_names = '';
                $download_po_lang = '';
                $shipping_address = '';
                $download_file_type = '';
                $delivery_date = '';
                $shipping_method = '';
                $shipping_terms = '';
                $po_attn = '';
            }
            
            echo  json_encode( array(
                'download_link'        => $download_po_link,
                'default_cols'         => $column_names,
                'download_po_lang'     => $download_po_lang,
                'supplier_id'          => $supplier_id,
                'default_ship_address' => $shipping_address,
                'download_po_file'     => $download_file_type,
                'save_flag'            => $save_flag,
                'delivery_date'        => $delivery_date,
                'shipping_method'      => $shipping_method,
                'shipping_terms'       => $shipping_terms,
                'po_attn'              => $po_attn,
            ) ) ;
            die;
        }
        
        /**
         * Download order file
         * On click of download, it generates excel file of ordered product information.
         *
         * @since 1.0.0
         */
        public function download_order_file()
        {
            
            if ( isset( $_GET['action'] ) && sanitize_text_field( $_GET['action'] ) == 'download_order_file' ) {
                global  $wpdb ;
                $supplier_id = sanitize_text_field( $_GET['supplier'] );
                $file_type = $_GET['download_po_file'];
                $language_type = $_GET['po_lang'];
                $order_date = date( 'Y-m-d H:i:s', sanitize_text_field( $_GET['date'] ) );
                $sql = "SELECT * FROM `{$wpdb->prefix}order_inventory` WHERE supplier_id = {$supplier_id} AND order_date = '{$order_date}' order by order_number DESC";
                $result = $wpdb->get_results( $sql );
                $column_names = get_post_meta( $supplier_id, 'oimwc_default_po_settings', true );
                
                if ( $language_type == 'hi_IN' ) {
                    setlocale( LC_ALL, "hi" );
                    $new_date = strftime( '%d %B, %Y', strtotime( $order_date ) );
                } else {
                    setlocale( LC_ALL, $language_type . '.utf8' );
                    $new_date = strftime( '%d %B, %Y', strtotime( $order_date ) );
                }
                
                $order_date_pdf = $new_date;
                $total_purchase = $this->get_total_purchase_amount( array(
                    'supplier_id' => $supplier_id,
                    'order_date'  => $order_date,
                    'currency'    => 1,
                ) );
                $pdf_color = get_option( 'oimwc_pdf_color' );
                $pdf_font_color = get_option( 'oimwc_pdf_title_color' );
                $pdf_font_color = ( empty($pdf_font_color) ? '#FFFFFF' : $pdf_font_color );
                $image_id = get_option( 'oimwc_pdf_logo' );
                $image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                $selected_shipping_address = get_post_meta( $supplier_id, 'oimwc_supplier_shipping_address', true );
                $selected_shipping_address = ( !empty($selected_shipping_address) ? $selected_shipping_address : 'address_0' );
                $shipping_address_list = get_option( 'oimwc_shipping_address' );
                $im_receiver = $shipping_address_list[$selected_shipping_address]['im_receiver'];
                $im_address1 = $shipping_address_list[$selected_shipping_address]['im_address1'];
                $im_state = $shipping_address_list[$selected_shipping_address]['im_state'];
                $im_zip_code = $shipping_address_list[$selected_shipping_address]['im_zip_code'];
                $im_country = WC()->countries->countries[$shipping_address_list[$selected_shipping_address]['im_country']];
                $im_contact = $shipping_address_list[$selected_shipping_address]['im_contact'];
                $im_phone = $shipping_address_list[$selected_shipping_address]['im_phone'];
                $office_address_list = get_option( 'oimwc_company_address' );
                $selected_office_address = get_post_meta( $supplier_id, 'oimwc_company_address', true );
                $selected_office_address = ( !empty($selected_office_address) ? $selected_office_address : 'address_0' );
                $im_company = $office_address_list[$selected_office_address]['im_company'];
                $address1 = $office_address_list[$selected_office_address]['im_address_line1'];
                $address2 = $office_address_list[$selected_office_address]['im_address_line2'];
                $state_office = $office_address_list[$selected_office_address]['im_state'];
                $zip_code = $office_address_list[$selected_office_address]['im_zip_code'];
                $country = WC()->countries->countries[$office_address_list[$selected_office_address]['im_country']];
                $phone = $office_address_list[$selected_office_address]['im_phone'];
                $email = $office_address_list[$selected_office_address]['im_email'];
                $fax_number = $office_address_list[$selected_office_address]['im_fax'];
                $url = $office_address_list[$selected_office_address]['im_website'];
                $tax = $office_address_list[$selected_office_address]['im_tax'];
                $city = $office_address_list[$selected_office_address]['im_city'];
                $supplier_name = get_the_title( $supplier_id );
                $supplier_address = get_post_meta( $supplier_id, 'oimwc_supplier_address', true );
                $supplier_phone = get_post_meta( $supplier_id, 'oimwc_supplier_phone_no', true );
                $supplier_email = get_post_meta( $supplier_id, 'oimwc_supplier_email', true );
                $supplier_currency = get_post_meta( $supplier_id, 'oimwc_supplier_currency', true );
                $delivery_date = get_post_meta( $supplier_id, 'oimwc_delivery_date', true );
                $shipping_terms = get_post_meta( $supplier_id, 'oimwc_shipping_terms', true );
                $shipping_method = get_post_meta( $supplier_id, 'oimwc_shipping_method', true );
                $po_attn = get_post_meta( $supplier_id, 'oimwc_po_attn', true );
                $delivery_date = ( !empty($_POST['delivery_date']) ? $_POST['delivery_date'] : $delivery_date );
                
                if ( $delivery_date != '' ) {
                    setlocale( LC_ALL, $language_type . '.utf8' );
                    $delivery_date = strftime( '%d %B, %Y', strtotime( $delivery_date ) );
                } else {
                    $delivery_date = '-';
                }
                
                
                if ( is_array( $result ) && count( $result ) && $file_type != '' ) {
                    
                    if ( $file_type == 'xlsx' ) {
                        require OIMWC_INCLUDES . 'phpspreadsheet/vendor/autoload.php';
                        $objPHPSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
                        $objPHPSpreadsheet->getDefaultStyle()->getFont()->setName( 'Calibri' )->setSize( 12 );
                        $objPHPSpreadsheet->getActiveSheet()->getPageSetup()->setPaperSize( \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4 );
                        $styleArray = [
                            'font' => [
                            'bold'  => true,
                            'color' => [
                            'argb' => str_replace( '#', 'FF', $pdf_font_color ),
                        ],
                        ],
                            'fill' => [
                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                            'argb' => str_replace( '#', 'FF', $pdf_color ),
                        ],
                        ],
                        ];
                        $styleRowArray = [
                            'fill' => [
                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                            'argb' => 'FFF6F7F9',
                        ],
                        ],
                        ];
                        foreach ( $result as $row ) {
                            $order_id = ( $row->order_number != '' ? $row->order_number : 0 );
                            $private_notes = $row->additional_information;
                            $arrival_date = $row->arrival_date;
                        }
                        $range = range( 'A', 'Z' );
                        
                        if ( $image_id != '' ) {
                            $rowcount = 1;
                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[0] . $rowcount . ':' . $range[7] . ++$rowcount );
                            $product_image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                            $height = $product_image[2];
                            $objPHPSpreadsheet->getActiveSheet()->getRowDimension( $rowcount )->setRowHeight( $height );
                            $exploded = explode( '/', wp_get_attachment_url( $image_id ) );
                            $product_imagename = wp_upload_dir()['basedir'] . '/' . $exploded[count( $exploded ) - 3] . '/' . $exploded[count( $exploded ) - 2] . '/' . $exploded[count( $exploded ) - 1];
                            $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $drawing->setPath( $product_imagename );
                            $drawing->setCoordinates( $range[0] . $rowcount );
                            $drawing->setHeight( $product_image[2] );
                            $drawing->setOffsetX( 20 );
                            $drawing->setOffsetY( 0 );
                            $drawing->setWorksheet( $objPHPSpreadsheet->getActiveSheet() );
                        }
                        
                        $l = $range[0];
                        
                        if ( $image_id != '' ) {
                            $rowcount = 3;
                        } else {
                            $rowcount = 1;
                        }
                        
                        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                        $payable = $richText->createTextRun( $im_company . "\n" );
                        $payable->getFont()->setBold( true )->setSize( 14 );
                        if ( !empty($address1) ) {
                            $richText->createText( $address1 . "\n" );
                        }
                        if ( !empty($address2) ) {
                            $richText->createText( $address2 . "\n" );
                        }
                        
                        if ( $city != '' || $zip_code != '' || $state_office != '' ) {
                            $state = '';
                            if ( !empty($city) ) {
                                $state .= $city . ' ';
                            }
                            if ( !empty($state_office) ) {
                                $state .= $state_office . ' ';
                            }
                            if ( !empty($zip_code) ) {
                                $state .= $zip_code . ' ';
                            }
                            $richText->createText( $state . "\n" );
                        }
                        
                        if ( !empty($country) ) {
                            $richText->createText( $country . "\n" . "\n" );
                        }
                        
                        if ( $phone != '' ) {
                            $payableTxt = $richText->createTextRun( __( 'Phone No', 'order-and-inventory-manager-for-woocommerce' ) . '.: ' );
                            $richText->createText( $phone . "\n" );
                        }
                        
                        
                        if ( $email != '' ) {
                            $payableTxt = $richText->createTextRun( __( 'Email', 'order-and-inventory-manager-for-woocommerce' ) . ': ' );
                            $richText->createText( $email . "\n" );
                        }
                        
                        
                        if ( $fax_number != '' ) {
                            $payableTxt = $richText->createTextRun( __( 'Fax Number', 'order-and-inventory-manager-for-woocommerce' ) . ': ' );
                            $richText->createText( $fax_number . "\n" );
                        }
                        
                        
                        if ( $url != '' ) {
                            $payableTxt = $richText->createTextRun( __( 'Website', 'order-and-inventory-manager-for-woocommerce' ) . ': ' );
                            $richText->createText( $url . "\n" );
                        }
                        
                        
                        if ( $tax != '' ) {
                            $payableTxt = $richText->createTextRun( __( 'Tax registration nr. / VAT', 'order-and-inventory-manager-for-woocommerce' ) . ': ' );
                            $richText->createText( $tax . "\n" );
                        }
                        
                        
                        if ( $po_attn != '' ) {
                            $payableAttn = $richText->createTextRun( __( 'Attn', 'order-and-inventory-manager-for-woocommerce' ) . '.: ' );
                            $richText->createText( $po_attn . "\n" );
                        }
                        
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l . $rowcount, $richText );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l . $rowcount )->getAlignment()->setWrapText( true );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP );
                        
                        if ( $image_id != '' ) {
                            $merge = $l . $rowcount . ':' . $range[3] . ($rowcount + 12);
                        } else {
                            $merge = $l . $rowcount . ':' . $range[3] . ($rowcount + 13);
                        }
                        
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $merge );
                        $l_B = $range[4];
                        
                        if ( $image_id != '' ) {
                            $rowcount_B = 3;
                        } else {
                            $rowcount_B = 1;
                        }
                        
                        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                        $payable = $richText->createTextRun( __( 'Purchase Order', 'order-and-inventory-manager-for-woocommerce' ) . "\n" );
                        $payable->getFont()->setBold( true )->setSize( 14 );
                        $richText->createText( __( 'PO No', 'order-and-inventory-manager-for-woocommerce' ) . '.: ' . $order_id . "\n" );
                        $richText->createText( $order_date_pdf . "\n" );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_B . $rowcount_B, $richText );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_B . $rowcount_B )->getAlignment()->setWrapText( true );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_B . $rowcount_B )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP );
                        
                        if ( $image_id != '' ) {
                            $merge = $l_B . $rowcount_B . ':' . $range[7] . ($rowcount_B + 12);
                        } else {
                            $merge = $l_B . $rowcount_B . ':' . $range[7] . ($rowcount_B + 13);
                        }
                        
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $merge );
                        
                        if ( $image_id == '' ) {
                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( 'A16:H16' );
                        } else {
                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( 'A16:H16' );
                        }
                        
                        $rowcount = 17;
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l . $rowcount, __( 'SUPPLIER', 'order-and-inventory-manager-for-woocommerce' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l . $rowcount . ':' . $range[3] . $rowcount )->applyFromArray( $styleArray );
                        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                        $payable = $richText->createTextRun( $supplier_name . "\n" );
                        $payable->getFont()->setBold( true )->setSize( 12 );
                        if ( !empty($supplier_address) ) {
                            $richText->createText( $supplier_address . "\n" . "\n" );
                        }
                        
                        if ( $supplier_phone != '' ) {
                            $payableTxt = $richText->createTextRun( __( 'Phone No', 'order-and-inventory-manager-for-woocommerce' ) . '.: ' );
                            $payableTxt->getFont()->setBold( true )->setSize( 12 );
                            $richText->createText( $supplier_phone . "\n" );
                        }
                        
                        
                        if ( $supplier_email != '' ) {
                            $payableEmail = $richText->createTextRun( __( 'Email', 'order-and-inventory-manager-for-woocommerce' ) . '.: ' );
                            $payableEmail->getFont()->setBold( true )->setSize( 12 );
                            $richText->createText( $supplier_email . "\n" );
                        }
                        
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l . ++$rowcount, $richText );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l . $rowcount )->getAlignment()->setWrapText( true );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l . $rowcount )->applyFromArray( $styleRowArray );
                        $merge = $l . $rowcount . ':' . $range[3] . ($rowcount + 7);
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $merge );
                        $rowcount_B = 17;
                        if ( !empty($shipping_address_list) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_B . $rowcount_B, __( 'DELIVERY ADDRESS', 'order-and-inventory-manager-for-woocommerce' ) );
                        }
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_B . $rowcount_B . ':' . $range[7] . $rowcount_B )->applyFromArray( $styleArray );
                        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                        if ( !empty($im_receiver) ) {
                            $richText->createText( $im_receiver . "\n" );
                        }
                        if ( !empty($im_address1) ) {
                            $richText->createText( $im_address1 . "\n" );
                        }
                        
                        if ( $im_state != '' || $im_zip_code != '' ) {
                            $state = '';
                            if ( !empty($im_state) ) {
                                $state .= $im_state;
                            }
                            if ( !empty($im_state) && !empty($im_zip_code) ) {
                                $state .= ', ';
                            }
                            if ( !empty($im_zip_code) ) {
                                $state .= $im_zip_code;
                            }
                            $richText->createText( $state . "\n" );
                        }
                        
                        if ( !empty($im_country) ) {
                            $richText->createText( $im_country . "\n" . "\n" );
                        }
                        
                        if ( $im_phone != '' ) {
                            $payablePhone = $richText->createTextRun( __( 'Phone No', 'order-and-inventory-manager-for-woocommerce' ) . ': ' );
                            $payablePhone->getFont()->setBold( true )->setSize( 12 );
                            $richText->createText( $im_phone . "\n" );
                        }
                        
                        
                        if ( $po_attn != '' ) {
                            $payableAttn = $richText->createTextRun( __( 'Attn', 'order-and-inventory-manager-for-woocommerce' ) . ': ' );
                            $payableAttn->getFont()->setBold( true )->setSize( 12 );
                            $richText->createText( $po_attn . "\n" );
                        }
                        
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_B . ++$rowcount_B, $richText );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_B . $rowcount_B )->getAlignment()->setWrapText( true );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_B . $rowcount_B )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_B . $rowcount_B )->applyFromArray( $styleRowArray );
                        $merge = $l_B . $rowcount_B . ':' . $range[7] . ($rowcount_B + 7);
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $merge );
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( 'A26:H26' );
                        $l_A = $range[0];
                        $rowcount = $rowcount + 9;
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'DELIVERY DATE', 'order-and-inventory-manager-for-woocommerce' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . ++$rowcount, ( !empty($delivery_date) ? __( $delivery_date, 'order-and-inventory-manager-for-woocommerce' ) : '-' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( ++$l_A . --$rowcount, __( 'REQUESTED BY', 'order-and-inventory-manager-for-woocommerce' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount . ':' . $range[2] . $rowcount )->applyFromArray( $styleArray );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . ++$rowcount, ( !empty($im_contact) ? $im_contact : '-' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount . ':' . $range[2] . $rowcount )->applyFromArray( $styleRowArray );
                        $l_A = 'C';
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( ++$l_A . --$rowcount, __( 'SHIPPING TERMS', 'order-and-inventory-manager-for-woocommerce' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount . ':' . $range[4] . $rowcount )->applyFromArray( $styleArray );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . ++$rowcount, ( !empty($shipping_terms) ? $shipping_terms : '-' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount . ':' . $range[4] . $rowcount )->applyFromArray( $styleRowArray );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $range[5] . --$rowcount, __( 'SHIPPING METHOD', 'order-and-inventory-manager-for-woocommerce' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[5] . $rowcount . ':' . $range[7] . $rowcount )->applyFromArray( $styleArray );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $range[5] . ++$rowcount, ( !empty($shipping_method) ? $shipping_method : '-' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[5] . $rowcount . ':' . $range[7] . $rowcount )->applyFromArray( $styleRowArray );
                        $rowcount++;
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[0] . $rowcount . ':' . $range[7] . $rowcount );
                        $l_A = $range[0];
                        $rowcount = $rowcount + 1;
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'NOTES', 'order-and-inventory-manager-for-woocommerce' ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount . ':' . $range[7] . $rowcount )->applyFromArray( $styleArray );
                        $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . ++$rowcount, ( empty($private_notes) ? '-' : $private_notes ) );
                        $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $l_A . $rowcount . ':' . $range[7] . $rowcount );
                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $l_A . ++$rowcount . ':' . $range[7] . $rowcount );
                        $l_A = $range[0];
                        $rowcount = $rowcount + 1;
                        
                        if ( count( $column_names ) == 8 ) {
                            $l_A = $range[0];
                        } else {
                            
                            if ( count( $column_names ) == 1 && !in_array( 'product_price', $column_names ) ) {
                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[0] . $rowcount . ':' . $range[7] . $rowcount );
                            } else {
                                
                                if ( count( $column_names ) <= 7 && !in_array( 'shop_product_name', $column_names ) && in_array( 'shop_variant_name', $column_names ) ) {
                                    if ( count( $column_names ) == 2 ) {
                                        
                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                        } else {
                                            
                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                $l_A = $range[0];
                                            }
                                        
                                        }
                                    
                                    }
                                    if ( count( $column_names ) == 3 ) {
                                        
                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                        } else {
                                            
                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                $l_A = $range[0];
                                            }
                                        
                                        }
                                    
                                    }
                                    if ( count( $column_names ) == 4 ) {
                                        
                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                        } else {
                                            
                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                $l_A = $range[0];
                                            }
                                        
                                        }
                                    
                                    }
                                    if ( count( $column_names ) == 5 ) {
                                        
                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                        } else {
                                            
                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                $l_A = $range[0];
                                            }
                                        
                                        }
                                    
                                    }
                                    if ( count( $column_names ) == 6 ) {
                                        
                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                        } else {
                                            
                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                $l_A = $range[0];
                                            }
                                        
                                        }
                                    
                                    }
                                    if ( count( $column_names ) == 7 ) {
                                        $l_A = $range[0];
                                    }
                                } else {
                                    
                                    if ( count( $column_names ) <= 7 && in_array( 'shop_product_name', $column_names ) && !in_array( 'shop_variant_name', $column_names ) ) {
                                        if ( count( $column_names ) == 2 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 3 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 4 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 5 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 6 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 7 ) {
                                            $l_A = $range[0];
                                        }
                                    } else {
                                        
                                        if ( count( $column_names ) <= 7 && (in_array( 'shop_product_name', $column_names ) && in_array( 'shop_variant_name', $column_names )) ) {
                                            if ( count( $column_names ) == 2 ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[0] . $rowcount . ':' . $range[7] . $rowcount );
                                            }
                                            if ( count( $column_names ) == 3 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 4 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 5 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 6 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 7 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                        } else {
                                            
                                            if ( count( $column_names ) <= 7 && (!in_array( 'shop_product_name', $column_names ) || !in_array( 'shop_variant_name', $column_names )) ) {
                                                if ( count( $column_names ) == 2 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 3 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 4 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 5 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 6 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                            }
                                        
                                        }
                                    
                                    }
                                
                                }
                            
                            }
                        
                        }
                        
                        
                        if ( in_array( 'product_image', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'ITEM IMAGE', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 15 );
                            $l_A++;
                        }
                        
                        
                        if ( in_array( 'shop_product_name', $column_names ) || in_array( 'shop_variant_name', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'ITEM NAME', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 20 );
                            $l_A++;
                        }
                        
                        
                        if ( in_array( 'product_url', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'ITEM URL', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 12 );
                            $l_A++;
                        }
                        
                        
                        if ( in_array( 'product_id', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'ITEM CODE', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 15 );
                            $l_A++;
                        }
                        
                        
                        if ( in_array( 'notes', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'NOTES', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 20 );
                            $l_A++;
                        }
                        
                        
                        if ( in_array( 'qty', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'QTY', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 8 );
                            $l_A++;
                        }
                        
                        
                        if ( in_array( 'product_price', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'ITEM PRICE', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 15 );
                            $l_A++;
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'TOTAL', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->getColumnDimension( $l_A )->setWidth( 12 );
                        }
                        
                        foreach ( $result as $row ) {
                            $total_price = 0;
                            
                            if ( $row->temp_product == 1 ) {
                                $temporary_product = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'oimwc_temp_product WHERE id=' . $row->product_id, ARRAY_A );
                                foreach ( $temporary_product as $value ) {
                                    $product_title = $value['product_name'];
                                    $product_variant = $value['variation_name'];
                                    $product_image = wp_upload_dir()['basedir'] . '/woocommerce-placeholder-100x100.png';
                                    $product_supplier = $value['product_url'];
                                    $product_artid = $value['supplier_product_id'];
                                    $product_notes = $value['supplier_notes'];
                                    $product_qty = $value['product_qty'];
                                    $product_price = $value['product_price'];
                                    
                                    if ( $product_price ) {
                                        $total_price += $value['product_price'] * $product_qty;
                                    } else {
                                        $total_price = 0;
                                    }
                                    
                                    $product_supplier = ( !empty($value['product_url']) ? $value['product_url'] : '' );
                                    $product_artid = ( !empty($value['supplier_product_id']) ? $value['supplier_product_id'] : '' );
                                    $product_notes = ( !empty($value['supplier_notes']) ? $value['supplier_notes'] : '' );
                                }
                            } else {
                                $product = get_post( $row->product_id );
                                $product_title = html_entity_decode( ( $product->post_parent ? get_the_title( $product->post_parent ) : get_the_title( $product->ID ) ) );
                                $product_type = $product->post_type;
                                $product_variant = '';
                                
                                if ( $product_type == 'product_variation' ) {
                                    $variable_product = new WC_Product_Variation( $product->ID );
                                    $product_variant = $variable_product->get_variation_attributes();
                                    
                                    if ( is_array( $product_variant ) && count( $product_variant ) ) {
                                        $variation_names = array();
                                        foreach ( $product_variant as $key => $value ) {
                                            
                                            if ( strpos( $key, 'pa_' ) !== false ) {
                                                $product_attribute = wc_attribute_label( str_replace( "attribute_", "", $key ) );
                                            } else {
                                                $product_attribute = wc_attribute_label( 'pa_' . str_replace( "attribute_", "", $key ) );
                                                
                                                if ( strpos( $product_attribute, 'pa_' ) !== false ) {
                                                    $product_attribute = ucfirst( wc_attribute_label( str_replace( array( "attribute_", "-" ), array( "", " " ), $key ) ) );
                                                } else {
                                                    $product_attribute = wc_attribute_label( 'pa_' . str_replace( "attribute_", "", $key ) );
                                                }
                                            
                                            }
                                            
                                            $term = get_term_by( 'slug', $value, str_replace( "attribute_", "", $key ) );
                                            
                                            if ( !$term ) {
                                                $variation_names[] = [
                                                    'name'      => $value,
                                                    'attribute' => $product_attribute,
                                                ];
                                            } else {
                                                $variation_names[] = [
                                                    'name'      => $term->name,
                                                    'attribute' => $product_attribute,
                                                ];
                                            }
                                        
                                        }
                                        $product_variant = $variation_names;
                                    } else {
                                        $product_variant = '';
                                    }
                                    
                                    $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->post_parent ), array( '100', '100' ) );
                                    $variation_image = wp_get_attachment_image_src( get_post_thumbnail_id( $row->product_id ), array( '100', '100' ) );
                                    $product_image = ( !empty($variation_image) ? $variation_image[0] : $product_image[0] );
                                    $exploded = explode( '/', $product_image );
                                    $product_image = wp_upload_dir()['basedir'] . '/' . $exploded[count( $exploded ) - 3] . '/' . $exploded[count( $exploded ) - 2] . '/' . $exploded[count( $exploded ) - 1];
                                    
                                    if ( empty($product_image) ) {
                                        $product_image = wp_upload_dir()['basedir'] . '/woocommerce-placeholder-100x100.png';
                                    } else {
                                        
                                        if ( $exploded[count( $exploded ) - 1] ) {
                                            $product_image = wp_upload_dir()['basedir'] . '/' . $exploded[count( $exploded ) - 3] . '/' . $exploded[count( $exploded ) - 2] . '/' . $exploded[count( $exploded ) - 1];
                                        } else {
                                            $product_image = wp_upload_dir()['basedir'] . '/woocommerce-placeholder-100x100.png';
                                        }
                                    
                                    }
                                
                                } else {
                                    $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $row->product_id ), array( '100', '100' ) );
                                    
                                    if ( empty($product_image) ) {
                                        $product_image = wp_upload_dir()['basedir'] . '/woocommerce-placeholder-100x100.png';
                                    } else {
                                        $product_image = $product_image[0];
                                        $exploded = explode( '/', $product_image );
                                        
                                        if ( $exploded[count( $exploded ) - 1] ) {
                                            $product_image = wp_upload_dir()['basedir'] . '/' . $exploded[count( $exploded ) - 3] . '/' . $exploded[count( $exploded ) - 2] . '/' . $exploded[count( $exploded ) - 1];
                                        } else {
                                            $product_image = wp_upload_dir()['basedir'] . '/woocommerce-placeholder-100x100.png';
                                        }
                                    
                                    }
                                
                                }
                                
                                $product_supplier = get_post_meta( $row->product_id, 'oimwc_supplier_product_url', true );
                                $product_supplier = ( $product_supplier != '' ? $product_supplier : '' );
                                $product_artid = get_post_meta( $row->product_id, 'oimwc_supplier_product_id', true );
                                $product_artid = ( $product_artid != '' ? $product_artid : '' );
                                $product_notes = get_post_meta( $row->product_id, 'oimwc_supplier_note', true );
                                $product_notes = ( $product_notes != '' ? $product_notes : '' );
                                $product_qty = $row->requested_stock;
                                $product_price = get_post_meta( $row->product_id, 'oimwc_supplier_purchase_price', true );
                                
                                if ( strpos( $product_price, ',' ) !== false ) {
                                    $product_price = str_replace( ',', '.', $product_price );
                                } else {
                                    $product_price = $product_price;
                                }
                                
                                $additional_supplier_info = oimwc_additional_supplier_details_from_product( $product->ID, $supplier_id );
                                
                                if ( is_array( $additional_supplier_info ) && count( $additional_supplier_info ) ) {
                                    $product_url = $additional_supplier_info['supplier_product_url'];
                                    $product_code = $additional_supplier_info['supplier_product_id'];
                                    $product_notes = $additional_supplier_info['product_notes'];
                                    $product_price = $additional_supplier_info['purchase_price'];
                                    if ( !$product_code ) {
                                        $product_code = get_post_meta( $row->product_id, '_sku', true );
                                    }
                                }
                                
                                if ( $product_price ) {
                                    $total_price += $product_price * $product_qty;
                                }
                            }
                            
                            $productTitle = '';
                            
                            if ( $rowcount % 2 != 0 ) {
                                $styleRowArray = [
                                    'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => [
                                    'argb' => 'FFF6F7F9',
                                ],
                                ],
                                ];
                            } else {
                                $styleRowArray = [
                                    'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => [
                                    'argb' => 'FFFFFFFF',
                                ],
                                ],
                                ];
                            }
                            
                            $l_A = $range[0];
                            $rowcount = $rowcount + 1;
                            
                            if ( count( $column_names ) == 8 ) {
                                $l_A = $range[0];
                            } else {
                                
                                if ( count( $column_names ) == 1 && !in_array( 'product_price', $column_names ) ) {
                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[0] . $rowcount . ':' . $range[7] . $rowcount );
                                    $objPHPSpreadsheet->getActiveSheet()->getRowDimension( $rowcount )->setRowHeight( 90 );
                                } else {
                                    
                                    if ( count( $column_names ) <= 7 && !in_array( 'shop_product_name', $column_names ) && in_array( 'shop_variant_name', $column_names ) ) {
                                        if ( count( $column_names ) == 2 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 3 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 4 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 5 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 6 ) {
                                            
                                            if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                            } else {
                                                
                                                if ( !in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    $l_A = $range[0];
                                                }
                                            
                                            }
                                        
                                        }
                                        if ( count( $column_names ) == 7 ) {
                                            $l_A = $range[0];
                                        }
                                    } else {
                                        
                                        if ( count( $column_names ) <= 7 && in_array( 'shop_product_name', $column_names ) && !in_array( 'shop_variant_name', $column_names ) ) {
                                            if ( count( $column_names ) == 2 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 3 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 4 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 5 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 6 ) {
                                                
                                                if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                                } else {
                                                    
                                                    if ( !in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        $l_A = $range[0];
                                                    }
                                                
                                                }
                                            
                                            }
                                            if ( count( $column_names ) == 7 ) {
                                                $l_A = $range[0];
                                            }
                                        } else {
                                            
                                            if ( count( $column_names ) <= 7 && (in_array( 'shop_product_name', $column_names ) && in_array( 'shop_variant_name', $column_names )) ) {
                                                
                                                if ( count( $column_names ) == 2 ) {
                                                    $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[0] . $rowcount . ':' . $range[7] . $rowcount );
                                                    $objPHPSpreadsheet->getActiveSheet()->getRowDimension( $rowcount )->setRowHeight( 90 );
                                                }
                                                
                                                if ( count( $column_names ) == 3 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 4 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 5 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 6 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                                if ( count( $column_names ) == 7 ) {
                                                    
                                                    if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                        $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                                    } else {
                                                        
                                                        if ( !in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            $l_A = $range[0];
                                                        }
                                                    
                                                    }
                                                
                                                }
                                            } else {
                                                
                                                if ( count( $column_names ) <= 7 && (!in_array( 'shop_product_name', $column_names ) || !in_array( 'shop_variant_name', $column_names )) ) {
                                                    if ( count( $column_names ) == 2 ) {
                                                        
                                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            
                                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[1] . $rowcount . ':' . $range[7] . $rowcount );
                                                            } else {
                                                                $l_A = $range[0];
                                                            }
                                                        
                                                        }
                                                    
                                                    }
                                                    if ( count( $column_names ) == 3 ) {
                                                        
                                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            
                                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[2] . $rowcount . ':' . $range[7] . $rowcount );
                                                            } else {
                                                                $l_A = $range[0];
                                                            }
                                                        
                                                        }
                                                    
                                                    }
                                                    if ( count( $column_names ) == 4 ) {
                                                        
                                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            
                                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[3] . $rowcount . ':' . $range[7] . $rowcount );
                                                            } else {
                                                                $l_A = $range[0];
                                                            }
                                                        
                                                        }
                                                    
                                                    }
                                                    if ( count( $column_names ) == 5 ) {
                                                        
                                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            
                                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[4] . $rowcount . ':' . $range[7] . $rowcount );
                                                            } else {
                                                                $l_A = $range[0];
                                                            }
                                                        
                                                        }
                                                    
                                                    }
                                                    if ( count( $column_names ) == 6 ) {
                                                        
                                                        if ( $objPHPSpreadsheet->getActiveSheet()->getCell( 'H33' )->getValue() == '' && in_array( 'product_price', $column_names ) ) {
                                                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[6] . $rowcount . ':' . $range[7] . $rowcount );
                                                        } else {
                                                            
                                                            if ( !in_array( 'product_price', $column_names ) ) {
                                                                $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[7] . $rowcount );
                                                            } else {
                                                                $l_A = $range[0];
                                                            }
                                                        
                                                        }
                                                    
                                                    }
                                                }
                                            
                                            }
                                        
                                        }
                                    
                                    }
                                
                                }
                            
                            }
                            
                            
                            if ( preg_match( '/[ëäöåÄÖÅ]+/', end( explode( '/', $product_image ) ), $matches ) ) {
                                $product_image = wp_upload_dir()['basedir'] . '/woocommerce-placeholder-100x100.png';
                            } else {
                                $product_image = $product_image;
                            }
                            
                            
                            if ( in_array( 'product_image', $column_names ) ) {
                                $objPHPSpreadsheet->getActiveSheet()->getRowDimension( $rowcount )->setRowHeight( 90 );
                                $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                $drawing->setPath( $product_image );
                                $drawing->setCoordinates( $l_A . $rowcount );
                                $drawing->setOffsetX( 10 );
                                $drawing->setOffsetY( 10 );
                                $drawing->setWorksheet( $objPHPSpreadsheet->getActiveSheet() );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                $l_A++;
                            }
                            
                            
                            if ( in_array( 'shop_product_name', $column_names ) || in_array( 'shop_variant_name', $column_names ) ) {
                                
                                if ( in_array( 'shop_product_name', $column_names ) ) {
                                    $productTitle .= $product_title;
                                    if ( in_array( 'shop_variant_name', $column_names ) ) {
                                        
                                        if ( $row->temp_product == 1 ) {
                                            $p_variant = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                                            $productVariant = $p_variant->createTextRun( "\n" );
                                            $p_variant->createText( $product_variant );
                                            $productTitle .= $p_variant;
                                        } else {
                                            foreach ( $product_variant as $key => $value ) {
                                                $p_variant = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                                                $productVariant = $p_variant->createTextRun( "\n" . $value['attribute'] . ' : ' );
                                                $p_variant->createText( $value['name'] );
                                                $productTitle .= $p_variant;
                                            }
                                        }
                                    
                                    }
                                } else {
                                    if ( in_array( 'shop_variant_name', $column_names ) ) {
                                        
                                        if ( !empty($product_variant) ) {
                                            
                                            if ( $row->temp_product == 1 ) {
                                                $p_variant = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                                                $productVariant = $p_variant->createTextRun( "\n" );
                                                $p_variant->createText( $product_variant );
                                                $productTitle .= $p_variant;
                                            } else {
                                                foreach ( $product_variant as $key => $value ) {
                                                    $p_variant = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
                                                    $productVariant = $p_variant->createTextRun( "\n" . $value['attribute'] . ' : ' );
                                                    $p_variant->createText( $value['name'] );
                                                    $productTitle .= $p_variant;
                                                }
                                            }
                                        
                                        } else {
                                            $productTitle .= '';
                                        }
                                    
                                    }
                                }
                                
                                $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, $productTitle );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setWrapText( true );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                $l_A++;
                            }
                            
                            
                            if ( in_array( 'product_url', $column_names ) ) {
                                
                                if ( empty($product_supplier) ) {
                                    $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, '-' );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                } else {
                                    $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, __( 'Item Url', 'order-and-inventory-manager-for-woocommerce' ) );
                                    $objPHPSpreadsheet->getActiveSheet()->getCell( $l_A . $rowcount )->getHyperlink()->setUrl( $product_supplier );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getFont()->getColor()->setARGB( \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                }
                                
                                $l_A++;
                            }
                            
                            
                            if ( in_array( 'product_id', $column_names ) ) {
                                
                                if ( empty($product_artid) ) {
                                    $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, '-' );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                } else {
                                    $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, $product_artid );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setWrapText( true );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                }
                                
                                $l_A++;
                            }
                            
                            
                            if ( in_array( 'notes', $column_names ) ) {
                                
                                if ( empty($product_notes) ) {
                                    $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, '-' );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                } else {
                                    $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, $product_notes );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setWrapText( true );
                                    $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                }
                                
                                $l_A++;
                            }
                            
                            
                            if ( in_array( 'qty', $column_names ) ) {
                                $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, ( empty($product_qty) ? '-' : $product_qty ) );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                $l_A++;
                            }
                            
                            
                            if ( in_array( 'product_price', $column_names ) ) {
                                $html = new PhpOffice\PhpSpreadsheet\Helper\Html();
                                $HTMLCODE = $html->toRichTextObject( ( !empty($product_price) ? strip_tags( wc_price( $product_price, array(
                                    'currency'          => $supplier_currency,
                                    'decimal_separator' => '.',
                                ) ) ) : strip_tags( wc_price( 0, array(
                                    'currency'          => $supplier_currency,
                                    'decimal_separator' => '.',
                                ) ) ) ) );
                                $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, $HTMLCODE );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                                $l_A++;
                                $html = new PhpOffice\PhpSpreadsheet\Helper\Html();
                                $price = $html->toRichTextObject( ( !empty($total_price) ? strip_tags( wc_price( $total_price, array(
                                    'currency'          => $supplier_currency,
                                    'decimal_separator' => '.',
                                ) ) ) : strip_tags( wc_price( 0, array(
                                    'currency'          => $supplier_currency,
                                    'decimal_separator' => '.',
                                ) ) ) ) );
                                $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $l_A . $rowcount, $price );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                                $objPHPSpreadsheet->getActiveSheet()->getStyle( $l_A . $rowcount )->applyFromArray( $styleRowArray );
                            }
                        
                        }
                        
                        if ( in_array( 'product_price', $column_names ) ) {
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $range[5] . ++$rowcount, __( 'ORDER TOTAL', 'order-and-inventory-manager-for-woocommerce' ) );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[5] . $rowcount . ':' . $range[6] . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[5] . $rowcount . ':' . $range[6] . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[5] . $rowcount . ':' . $range[6] . $rowcount )->applyFromArray( $styleArray );
                            $objPHPSpreadsheet->getActiveSheet()->mergeCells( $range[5] . $rowcount . ':' . $range[6] . $rowcount );
                            $html = new PhpOffice\PhpSpreadsheet\Helper\Html();
                            $total_purchase = '<strong>' . strip_tags( $total_purchase ) . '</strong>';
                            $Totalprice = $html->toRichTextObject( $total_purchase );
                            $objPHPSpreadsheet->setActiveSheetIndex( 0 )->setCellValue( $range[7] . $rowcount, $Totalprice );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[7] . $rowcount )->getAlignment()->setHorizontal( \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[7] . $rowcount )->getAlignment()->setVertical( \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER );
                            $objPHPSpreadsheet->getActiveSheet()->getStyle( $range[7] . $rowcount )->getFill()->setFillType( \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID )->getStartColor()->setARGB( 'F6F7F9' );
                        }
                        
                        $order_date = str_replace( array( ' ', ':' ), '-', $order_date );
                        $filename = $supplier_id . '_' . $order_date . '.xlsx';
                        header( "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" );
                        header( "Content-Disposition: attachment; filename=\"{$filename}\"" );
                        header( "Cache-Control: max-age=0" );
                        ob_end_clean();
                        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx( $objPHPSpreadsheet );
                        
                        if ( $_GET['save_po_file'] == 'yes' ) {
                            $file_path = wp_upload_dir()['basedir'] . "/download_document/";
                            if ( !is_dir( $file_path ) ) {
                                mkdir( $file_path );
                            }
                            $writer->save( $file_path . $filename );
                        } else {
                            $writer->save( "php://output" );
                        }
                    
                    } else {
                        
                        if ( $file_type == 'pdf' ) {
                        } else {
                            if ( $file_type == 'doc' ) {
                            }
                        }
                    
                    }
                    
                    die;
                }
            
            }
        
        }
        
        /**
        * Update finalize product
        * Update product as finalize if product is fully arrived, finalized and add qty to 
          stock
        * @since 1.0.0
        */
        public function finalize_product_order()
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $id = sanitize_text_field( $_POST['id'] );
            $product_id = sanitize_text_field( $_POST['product_id'] );
            $wpdb->update( $wpdb->prefix . 'order_inventory', array(
                'finalize_product' => 1,
            ), array(
                'id' => $id,
            ) );
            $sql = $wpdb->get_row( "select supplier_id,order_date,lock_product from {$table_name} where id = {$id}" );
            $supplier_id = $sql->supplier_id;
            $order_date = $sql->order_date;
            $update_all_product = $this->update_finalize_to_completed_orders( $supplier_id, $order_date );
            $lock_order = $sql->lock_product;
            if ( !$lock_order ) {
                $this->update( array(
                    'lock_product' => 1,
                ), array(
                    'supplier_id' => $supplier_id,
                    'order_date'  => $order_date,
                ) );
            }
            oimwc_supplier_low_stock_count( 0, $supplier_id );
            oimwc_show_all_product_stock_count(
                $supplier_id,
                false,
                '=',
                true
            );
            echo  json_encode( array(
                'row_id'                 => $id,
                'default'                => sprintf( '<tr><td colspan="6">%s</td></tr>', __( 'No products are found!', 'order-and-inventory-manager-for-woocommerce' ) ),
                'update_all_product'     => $update_all_product,
                'update_all_product_txt' => ( $update_all_product ? __( 'Finalized', 'order-and-inventory-manager-for-woocommerce' ) : __( 'Receiving', 'order-and-inventory-manager-for-woocommerce' ) ),
                'finalize_text'          => __( 'Finalized', 'order-and-inventory-manager-for-woocommerce' ),
            ) ) ;
            die;
        }
        
        /**
         * Update orders as completed
         * Check if all products of particular purchase order are finalized then that order updated as completed order
         * @since 1.0.0
         */
        function update_finalize_to_completed_orders( $supplier_id, $order_date )
        {
            global  $wpdb ;
            $flag = 0;
            $table_name = $wpdb->prefix . 'order_inventory';
            $sql1 = "SELECT id FROM {$table_name} WHERE supplier_id = {$supplier_id} and order_date = '{$order_date}'";
            $total_products = $wpdb->get_results( $sql1, ARRAY_A );
            $count_total_products = count( $total_products );
            $ids = [];
            foreach ( $total_products as $key => $value ) {
                $ids[] = $value['id'];
            }
            $ids = implode( ',', $ids );
            $sql = "SELECT count(id) FROM {$table_name} WHERE supplier_id = {$supplier_id} and finalize_product >= 1 and order_date = '{$order_date}'";
            $total_finalize_products = $wpdb->get_var( $sql );
            
            if ( $total_finalize_products == $count_total_products ) {
                $update_qry = $wpdb->query( "UPDATE {$table_name} SET completed_order = 1 WHERE id IN ({$ids})" );
                $flag = 1;
            }
            
            return $flag;
        }
        
        public static function get_requested_product_stock( $supplier_id = 0, $cache = true, $compare = '=' )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $posts_table = $wpdb->prefix . 'posts';
            $post_meta_table = $wpdb->prefix . 'postmeta';
            $requested_product_list = '';
            if ( $cache ) {
                $requested_product_list = wp_cache_get( $supplier_id, 'oimwc_ordered_product_list_cache' );
            }
            
            if ( !$requested_product_list ) {
                $sql = "SELECT DISTINCT(WOI.product_id),WOI.requested_stock,WOI.order_number FROM `{$table_name}` as WOI\r\n                LEFT JOIN {$posts_table} as WPO ON WOI.product_id = WPO.ID\r\n                LEFT JOIN {$post_meta_table} as WPM ON WOI.product_id = WPM.post_id\r\n                WHERE WOI.finalize_product = 0 and WPO.post_status IN ('publish','private')\r\n                AND WPM.meta_key = 'oimwc_low_stock_threshold_level' AND WPM.meta_value >= (WOI.stock + WOI.requested_stock)";
                if ( $supplier_id ) {
                    $sql .= ' AND WOI.supplier_id ' . $compare . $supplier_id;
                }
                $data_result = $wpdb->get_results( $sql );
                $requested_product_list = array();
                
                if ( $data_result ) {
                    $temp = [];
                    foreach ( $data_result as $data ) {
                        array_push( $temp, $data->product_id );
                        if ( !in_array( $data->product_id, $temp ) ) {
                            $requested_product_list[$data->product_id] = 0;
                        }
                        $requested_product_list[$data->product_id] += $data->requested_stock;
                    }
                }
                
                wp_cache_add( $supplier_id, $requested_product_list, 'oimwc_ordered_product_list_cache' );
            }
            
            return $requested_product_list;
        }
    
    }
    new OIMWC_Order();
}
