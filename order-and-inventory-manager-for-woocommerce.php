<?php

/*
 * Plugin Name: Order and Inventory Manager for WooCommerce
 * Plugin URI: https://www.oimwc.com
 * Description: Manage inventory stock levels and product purchases for WooCommerce.
 * Version: 1.4.3
 * Author: WPHydraCode
 * Author URI: www.wphydracode.com
 * Text Domain: order-and-inventory-manager-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 6.4.1
 */

if ( !function_exists( 'oimwc_fs' ) ) {
    // Create a helper function for easy SDK access.
    function oimwc_fs()
    {
        global  $oimwc_fs ;
        
        if ( !isset( $oimwc_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $oimwc_fs = fs_dynamic_init( array(
                'id'             => '3922',
                'slug'           => 'order-and-inventory-manager-for-woocommerce',
                'type'           => 'plugin',
                'public_key'     => 'pk_eefc9c2ee976caabc9ded23f556a4',
                'is_premium'     => false,
                'premium_suffix' => '',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'slug'    => 'order-inventory-management',
                'contact' => false,
                'support' => false,
                'parent'  => array(
                'slug' => 'order-inventory-management',
            ),
            ),
                'is_live'        => true,
            ) );
        }
        
        return $oimwc_fs;
    }
    
    // Init Freemius.
    oimwc_fs();
    // Signal that SDK was initiated.
    do_action( 'oimwc_fs_loaded' );
}

/**
* Plugin file path
*
* @since 1.0.0
* @var string OIMWC_PLUGIN_FILE
*/
define( 'OIMWC_PLUGIN_FILE', __FILE__ );
/**
* Plugin directory path
*
* @var string OIMWC_PLUGIN_DIR
* @since 1.0.0
*/
define( 'OIMWC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
/**
* Plugin directory url
*
* @var string OIMWC_PLUGIN_URL
* @since 1.0.0
*/
define( 'OIMWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/**
* Plugin template directory path
*
* @var string OIMWC_TEMPLATE
* @since 1.0.0
*/
define( 'OIMWC_TEMPLATE', OIMWC_PLUGIN_DIR . 'templates/' );
/**
* Plugin classes directory
*
* @var string OIMWC_CLASSES
* @since 1.0.0
*/
define( 'OIMWC_CLASSES', OIMWC_PLUGIN_DIR . 'classes/' );
/**
* Plugin include directory path
*
* @var string OIMWC_INCLUDES
* @since 1.0.0
*/
define( 'OIMWC_INCLUDES', OIMWC_PLUGIN_DIR . 'includes/' );
/**
* Plugin js-templates directory path
*
* @var string OIMWC_JS_TEMPLATES
* @since 1.0.0
*/
define( 'OIMWC_JS_TEMPLATES', OIMWC_PLUGIN_DIR . 'js-templates/' );
/**
* Plugin name
*
* @var string OIMWC_NAME
* @since 1.0.0
*/
define( 'OIMWC_NAME', 'Order and Inventory Manager for WooCommerce' );
if ( !defined( 'OIMWC_PHP_TAB' ) ) {
    /**
     * PHP Tabs
     *
     * @var string OIMWC_PHP_TAB
     * @since 1.0.0
     */
    define( 'OIMWC_PHP_TAB', "\n\t" );
}
// Load plugin files
add_action( 'plugins_loaded', 'oimwc_init' );
if ( !function_exists( 'oimwc_init' ) ) {
    /**
     * Plugin initialization
     *
     *  loads the plugin dependency file and adds localization files
     *
     * @since 1.0.0
     */
    function oimwc_init()
    {
        
        if ( isset( $_GET['po_lang'] ) ) {
            $locale = $_GET['po_lang'];
        } else {
            
            if ( is_admin() && !wp_doing_ajax() && function_exists( 'get_user_locale' ) ) {
                $locale = get_user_locale();
            } else {
                
                if ( is_admin() && function_exists( 'get_user_locale' ) ) {
                    $locale = get_user_locale();
                } else {
                    $locale = get_locale();
                }
            
            }
        
        }
        
        $locale = apply_filters( 'plugin_locale', $locale, 'order-and-inventory-manager-for-woocommerce' );
        unload_textdomain( 'order-and-inventory-manager-for-woocommerce' );
        load_textdomain( 'order-and-inventory-manager-for-woocommerce', OIMWC_PLUGIN_DIR . 'languages/' . "order-and-inventory-manager-for-woocommerce-" . $locale . '.mo' );
        load_plugin_textdomain( 'order-and-inventory-manager-for-woocommerce', false, OIMWC_PLUGIN_DIR . 'languages' );
        require_once OIMWC_CLASSES . 'class.oimwc.php';
    }

}
register_activation_hook( __FILE__, 'oimwc_update_metas_plugin_activation' );
if ( !function_exists( 'oimwc_update_metas_plugin_activation' ) ) {
    function oimwc_update_metas_plugin_activation()
    {
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'postmeta';
        $posts_table = $wpdb->prefix . 'posts';
        $im_default_low_stock_data = get_option( 'oimwc_default_low_stock_data' );
        
        if ( !$im_default_low_stock_data ) {
            $sql = "SELECT A.ID FROM {$posts_table} AS A LEFT JOIN {$table_name} AS B ON ( A.ID = B.post_id ) LEFT JOIN {$table_name} AS mt1 ON (A.ID = mt1.post_id AND mt1.meta_key = 'oimwc_supplier_id' ) WHERE ( B.meta_key = '_manage_stock' AND B.meta_value = 'yes' AND mt1.post_id IS NULL ) AND A.post_type IN ('product', 'product_variation') AND (A.post_status = 'publish' OR A.post_status = 'private') GROUP BY A.ID ORDER BY A.post_date DESC";
            $product_list = $wpdb->get_col( $sql );
            $qry = "INSERT INTO {$table_name} (post_id,meta_key,meta_value) VALUES ";
            $seperator = '';
            
            if ( is_array( $product_list ) && count( $product_list ) > 0 ) {
                foreach ( $product_list as $key => $post_id ) {
                    $stock = get_post_meta( $post_id, '_stock', true );
                    $_low_stock_amount = get_post_meta( $post_id, '_low_stock_amount', true );
                    $meta_key = '_product_id';
                    $product = wc_get_product( $post_id );
                    if ( $product->is_type( 'variable' ) ) {
                        $meta_key = '_variation_id';
                    }
                    $physical_stock_sql = 'SELECT sum(b.meta_value) AS qty
                            FROM ' . $wpdb->prefix . 'woocommerce_order_itemmeta AS a
                            LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
                            LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_items AS WO ON ( WO.order_item_id = b.order_item_id )
                            LEFT JOIN ' . $wpdb->prefix . 'posts AS p ON ( p.ID = WO.order_id )
                            WHERE a.meta_key = "' . $meta_key . '" AND a.meta_value = ' . $post_id . ' AND b.meta_key = "_qty"
                            AND p.post_status IN ("wc-processing","wc-on-hold")';
                    $order_qty = $wpdb->get_var( $physical_stock_sql );
                    $real_stock = (int) $stock + (int) $order_qty;
                    $meta_keys = array();
                    $meta_keys = array(
                        'oimwc_previous_stock'            => $stock,
                        'oimwc_previous_pack_size'        => 1,
                        'oimwc_previous_total_pieces'     => $stock,
                        'oimwc_physical_stock'            => $real_stock,
                        'oimwc_discontinued_product'      => 'no',
                        'oimwc_supplier_product_id'       => 0,
                        'oimwc_all_discontinued_products' => 'no',
                        'oimwc_supplier_product_id'       => '',
                        'oimwc_supplier_product_url'      => '',
                        'oimwc_low_stock_threshold_level' => $_low_stock_amount,
                        'oimwc_supplier_note'             => '',
                        'oimwc_supplier_purchase_price'   => 0.0,
                        'oimwc_supplier_pack_size'        => 1,
                        'oimwc_our_pack_size'             => 1,
                        'oimwc_supplier_unit'             => 'piece',
                        'oimwc_show_in_low_stock'         => 'yes',
                        'oimwc_physical_units_stock'      => $stock,
                        'oimwc_manual_pack_size_setting'  => 2,
                        'oimwc_supplier_remaining_pieces' => 0,
                    );
                    foreach ( $meta_keys as $meta_key => $meta_value ) {
                        $qry .= sprintf(
                            "%s('%d','%s','%s')",
                            $seperator,
                            $post_id,
                            $meta_key,
                            $meta_value
                        );
                        $seperator = ',';
                    }
                }
                $result = $wpdb->query( $qry );
                if ( $result ) {
                    update_option( 'oimwc_default_low_stock_data', 1 );
                }
            }
        
        }
        
        oimwc_create_order_inventory_tbl();
        oimwc_product_stock_tbl( false );
        add_action( 'update_option_active_plugins', 'oimwc_deactivate_free_plugin' );
    }

}
/**
* creates a database table to store inventory orders
* @since 1.0.0
*/
if ( !function_exists( 'oimwc_create_order_inventory_tbl' ) ) {
    function oimwc_create_order_inventory_tbl()
    {
        global  $wpdb, $OIMProductStock ;
        $table_name = $wpdb->prefix . 'order_inventory';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (\r\n                id mediumint(9) NOT NULL AUTO_INCREMENT,\r\n                product_id mediumint(9) NOT NULL,\r\n                supplier_id mediumint(9) NOT NULL,\r\n                stock mediumint(5) NOT NULL,\r\n                requested_stock mediumint(5) NOT NULL,\r\n                arrvived_stock VARCHAR(5) NULL,\r\n                order_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,\r\n                PRIMARY KEY (id)\r\n                ) {$charset_collate};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        $res = $wpdb->query( "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $wpdb->dbname . "' AND TABLE_NAME = '{$table_name}' AND COLUMN_NAME = 'additional_information'" );
        
        if ( !$res ) {
            $wpdb->query( "ALTER TABLE `{$table_name}` add additional_information varchar(255) " );
            $wpdb->query( "ALTER TABLE `{$table_name}` add arrival_date varchar(50) " );
            $wpdb->query( "ALTER TABLE `{$table_name}` add lock_product int(2) DEFAULT 0" );
            $wpdb->query( "ALTER TABLE `{$table_name}` add private_note varchar(255)" );
            $wpdb->query( "ALTER TABLE `{$table_name}` add order_number mediumint(9) " );
        }
        
        $qry = $wpdb->query( "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $wpdb->dbname . "' AND TABLE_NAME = '{$table_name}' AND COLUMN_NAME = 'finalize_product'" );
        
        if ( !$qry ) {
            $wpdb->query( "ALTER TABLE `{$table_name}` add finalize_product int(2) DEFAULT 0" );
            $wpdb->query( "ALTER TABLE `{$table_name}` add completed_order int(2) DEFAULT 0" );
        }
        
        $table = $wpdb->prefix . 'additional_supplier_info';
        $supplier_table = "CREATE TABLE IF NOT EXISTS {$table} (\r\n                id mediumint(9) NOT NULL AUTO_INCREMENT,\r\n                product_id mediumint(9) NOT NULL,\r\n                variable_id mediumint(9) DEFAULT 0,\r\n                supplier_id mediumint(9) NOT NULL,\r\n                supplier_product_id VARCHAR(20),\r\n                supplier_product_url VARCHAR(500),\r\n                purchase_price float(9),\r\n                pack_size mediumint(3),\r\n                product_notes VARCHAR(500),\r\n                PRIMARY KEY (id)\r\n                ) {$charset_collate};";
        dbDelta( $supplier_table );
        $table = $wpdb->prefix . 'oim_product_stock';
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (\r\n                id mediumint(9) NOT NULL AUTO_INCREMENT,\r\n                product_id mediumint(9) NOT NULL,\r\n                variation_id mediumint(9) DEFAULT 0 NOT NULL,\r\n                user_id mediumint(9) DEFAULT 0 NOT NULL,\r\n                order_id mediumint(9) DEFAULT 0 NOT NULL,\r\n                purchase_order_id mediumint(9) DEFAULT 0 NOT NULL,\r\n                stock mediumint(5) DEFAULT 0 NOT NULL,\r\n                physical_stock mediumint(5) DEFAULT 0 NOT NULL,\r\n                sell_qty mediumint(5) DEFAULT 0 NOT NULL,\r\n                arrive_qty mediumint(5) DEFAULT 0 NOT NULL,\r\n                stock_in_units mediumint(5) DEFAULT 0 NOT NULL,\r\n                shop_pack_size mediumint(5) DEFAULT 0 NOT NULL,\r\n                imported mediumint(1) DEFAULT 0 NOT NULL,\r\n                note TEXT DEFAULT NULL,\r\n                date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,\r\n                PRIMARY KEY (id)\r\n                ) {$charset_collate};";
        dbDelta( $sql );
    }

}
/**
* creates a database table to store inventory orders
* @since 1.2.8
*/
if ( !function_exists( 'oimwc_product_stock_tbl' ) ) {
    function oimwc_product_stock_tbl( $flag = true )
    {
        global  $wpdb ;
        $is_stock_save = get_option( 'oim_product_stock' );
        
        if ( !$is_stock_save || !$flag ) {
            /*
             * Save simple product stock
             */
            $sql = $wpdb->prepare( "SELECT product_id FROM {$wpdb->prefix}oim_product_stock WHERE variation_id = 0" );
            $product_IDs = $wpdb->get_col( $sql );
            $where = '';
            if ( is_array( $product_IDs ) && count( $product_IDs ) ) {
                $where = sprintf( ' AND C.ID NOT IN ( %s ) ', implode( ',', $product_IDs ) );
            }
            $sql = sprintf(
                'SELECT A.post_id AS product_id, A.meta_value AS stock, D.meta_value AS shop_pack_size, E.meta_value AS physical_stock, F.meta_value AS stock_in_units FROM %1$s AS A 
            LEFT JOIN %1$s B ON A.post_id = B.post_id 
            LEFT JOIN %2$s C ON A.post_id = C.ID  
            LEFT JOIN %1$s D ON A.post_id = D.post_id 
            LEFT JOIN %1$s E ON A.post_id = E.post_id 
            LEFT JOIN %1$s F ON A.post_id = F.post_id 
            WHERE A.meta_key = "_stock" 
            AND B.meta_key = "_manage_stock" AND B.meta_value = "yes"
            AND D.meta_key = "oimwc_our_pack_size"
            AND E.meta_key = "oimwc_physical_stock"
            AND F.meta_key = "oimwc_physical_units_stock"
            AND C.post_type = "product" AND C.post_status IN ( "private", "publish" ) %3$s GROUP BY A.post_id',
                $wpdb->prefix . 'postmeta',
                $wpdb->prefix . 'posts',
                $where
            );
            $product_data = $wpdb->get_results( $sql, ARRAY_A );
            $main_sql = sprintf( 'INSERT INTO %1$s (`product_id`, `stock`, `physical_stock`, `stock_in_units`, `shop_pack_size`, `note`) VALUES ', $wpdb->prefix . 'oim_product_stock' );
            $sql_data = '';
            $count = 0;
            
            if ( $product_data ) {
                $seperator = '';
                foreach ( $product_data as $row ) {
                    $shop_pack_size = ( $row['shop_pack_size'] ? $row['shop_pack_size'] : 1 );
                    $stock_in_units = ( $row['stock_in_units'] ? $row['stock_in_units'] : 0 );
                    if ( !$row['shop_pack_size'] ) {
                        update_post_meta( $row['product_id'], 'oimwc_our_pack_size', $shop_pack_size );
                    }
                    $sql_data .= sprintf(
                        '%s ( %d, %d, %d, %d, %d, "%s" ) ',
                        $seperator,
                        $row['product_id'],
                        $row['stock'],
                        $row['physical_stock'],
                        $stock_in_units,
                        $shop_pack_size,
                        'new_product'
                    );
                    $seperator = ',';
                    
                    if ( $count++ > 500 ) {
                        $wpdb->query( $main_sql . $sql_data );
                        $sql_data = $seperator = '';
                        $count = 0;
                    }
                
                }
                $wpdb->query( $main_sql . $sql_data );
            }
            
            /*
             * Save variable product stock
             */
            $sql = $wpdb->prepare( "SELECT variation_id FROM {$wpdb->prefix}oim_product_stock WHERE variation_id > 0" );
            $product_IDs = $wpdb->get_col( $sql );
            $where = '';
            if ( is_array( $product_IDs ) && count( $product_IDs ) ) {
                $where = sprintf( ' AND C.ID NOT IN ( %s ) ', implode( ',', $product_IDs ) );
            }
            $sql = sprintf(
                'SELECT C.post_parent AS product_id, A.post_id AS variation_id, A.meta_value AS stock, D.meta_value AS shop_pack_size, E.meta_value AS physical_stock, F.meta_value AS stock_in_units FROM %1$s AS A 
            LEFT JOIN %1$s B ON A.post_id = B.post_id 
            LEFT JOIN %2$s C ON A.post_id = C.ID  
            LEFT JOIN %1$s D ON A.post_id = D.post_id 
            LEFT JOIN %1$s E ON A.post_id = E.post_id 
            LEFT JOIN %1$s F ON A.post_id = F.post_id 
            WHERE A.meta_key = "_stock" 
            AND B.meta_key = "_manage_stock" AND B.meta_value = "yes"
            AND D.meta_key = "oimwc_our_pack_size"
            AND E.meta_key = "oimwc_physical_stock"
            AND F.meta_key = "oimwc_physical_units_stock"
            AND C.post_type = "product_variation" AND C.post_status IN ( "private", "publish" ) %3$s GROUP BY A.post_id',
                $wpdb->prefix . 'postmeta',
                $wpdb->prefix . 'posts',
                $where
            );
            $product_data = $wpdb->get_results( $sql, ARRAY_A );
            $main_sql = sprintf( 'INSERT INTO %1$s (`product_id`, `variation_id`, `stock`, `physical_stock`, `stock_in_units`, `shop_pack_size`, `note`) VALUES ', $wpdb->prefix . 'oim_product_stock' );
            $sql_data = '';
            $count = 0;
            
            if ( $product_data ) {
                $seperator = '';
                foreach ( $product_data as $row ) {
                    $shop_pack_size = ( $row['shop_pack_size'] ? $row['shop_pack_size'] : 0 );
                    $stock_in_units = ( $row['stock_in_units'] ? $row['stock_in_units'] : 0 );
                    
                    if ( !$shop_pack_size ) {
                        $shop_pack_size = get_post_meta( $row['product_id'], 'oimwc_our_pack_size', true );
                        $shop_pack_size = ( $shop_pack_size ? $shop_pack_size : 1 );
                        update_post_meta( $row['variation_id'], 'oimwc_our_pack_size', $shop_pack_size );
                    }
                    
                    $sql_data .= sprintf(
                        '%s ( %d, %d, %d, %d, %d, %d, "%s" ) ',
                        $seperator,
                        $row['product_id'],
                        $row['variation_id'],
                        $row['stock'],
                        $row['physical_stock'],
                        $stock_in_units,
                        $shop_pack_size,
                        'new_product'
                    );
                    $seperator = ',';
                    
                    if ( $count++ > 500 ) {
                        $wpdb->query( $main_sql . $sql_data );
                        $sql_data = $seperator = '';
                        $count = 0;
                    }
                
                }
                $wpdb->query( $main_sql . $sql_data );
            }
            
            update_option( 'oim_product_stock', true );
        }
    
    }

}
/**
* Download PO file based on selected language in PO modal
* @since 1.0.0
*/
// add_filter('plugin_locale','set_lang_on_download_po');
if ( !function_exists( 'set_lang_on_download_po' ) ) {
    function set_lang_on_download_po( $locale )
    {
        if ( isset( $_GET['po_lang'] ) ) {
            $locale = $_GET['po_lang'];
        }
        return $locale;
    }

}
if ( !function_exists( 'oimwc_deactivate_free_plugin' ) ) {
    function oimwc_deactivate_free_plugin()
    {
        $dependent = 'order-and-inventory-manager-for-woocommerce-premium/order-and-inventory-manager-for-woocommerce.php';
        
        if ( is_plugin_active( $dependent ) ) {
            $dependent = 'order-and-inventory-manager-for-woocommerce/order-and-inventory-manager-for-woocommerce.php';
            deactivate_plugins( $dependent );
        }
    
    }

}