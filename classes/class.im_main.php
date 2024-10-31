<?php

/**
 * OIMWC_MAIN class
 *
 * Main class of plugin
 * Creates menus,registers new post type and saves new post type's data,saves plugin settings, manages order status
 *
 * @since    1.0.0
 */

if ( !class_exists( 'OIMWC_MAIN' ) ) {
    /**
     * OIMWC_MAIN is a main class of plugin
     *
     * Creates menus,registers new post type and saves new post type's data,saves plugin settings, manages order status
     *
     * @since    1.0.0
     */
    class OIMWC_MAIN
    {
        /**
         * An empty array
         *
         * @var Array $arr
         * @since 1.0.0 
         */
        public  $arr = array() ;
        /**
         * Stores total ordered quantity
         *
         * @var Array $total_order_qty_arr
         * @since 1.0.0 
         */
        public  $total_order_qty_arr = array() ;
        /**
         * Stores total physical stock
         *
         * @var Array $physical_stock_arr
         * @since 1.0.0 
         */
        public  $physical_stock_arr = array() ;
        /**
         * Stores actual physical stock
         *
         * @var Array $actual_physical_stock 
         * @since 1.0.0 
         */
        public  $actual_physical_stock = array() ;
        /**
         * Stores products in stock
         *
         * @var Array $manage_in_stock_arr 
         * @since 1.0.0 
         */
        public  $manage_in_stock_arr = array() ;
        /**
         * Stores out of stock products
         *
         * @var Array $manage_out_stock_arr 
         * @since 1.0.0 
         */
        public  $manage_out_stock_arr = array() ;
        /**
         * Stores staus of order
         *
         * @var Array $order_status 
         * @since 1.0.0 
         */
        public  $order_status = array() ;
        protected static  $instance ;
        /**
         * Setup class.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            if ( !defined( 'OIMWC_SILVER_UPGRDAE_NOTICE' ) ) {
                define( 'OIMWC_SILVER_UPGRDAE_NOTICE', __( '(Upgrade OIMWC to at least Silver to unlock this feature)', 'order-and-inventory-manager-for-woocommerce' ) );
            }
            if ( !defined( 'OIMWC_GOLD_UPGRDAE_NOTICE' ) ) {
                define( 'OIMWC_GOLD_UPGRDAE_NOTICE', __( '(Upgrade OIMWC to at least Gold to unlock this feature)', 'order-and-inventory-manager-for-woocommerce' ) );
            }
            if ( !defined( 'OIMWC_PLATINUM_UPGRDAE_NOTICE' ) ) {
                define( 'OIMWC_PLATINUM_UPGRDAE_NOTICE', __( '(Upgrade OIMWC to platinum to unlock this feature)', 'order-and-inventory-manager-for-woocommerce' ) );
            }
            add_action( 'init', array( $this, 'supplier_init' ), 6 );
            add_filter(
                'manage_supplier_posts_columns',
                array( $this, 'add_supplier_custom_column' ),
                5,
                1
            );
            add_action(
                'manage_supplier_posts_custom_column',
                array( $this, 'add_supplier_custom_column_content' ),
                5,
                2
            );
            add_filter( 'manage_edit-supplier_sortable_columns', array( $this, 'supplier_sortable_columns' ) );
            add_filter(
                'posts_clauses',
                array( $this, 'supplier_sort_custom_column' ),
                10,
                2
            );
            add_action( 'save_post', array( $this, 'save_supplier' ) );
            add_action( 'add_meta_boxes', array( $this, 'supplier_additional_info' ) );
            add_action( 'init', array( $this, 'add_database_updation_notice' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'plugin_scripts' ) );
            add_action( 'wp_head', array( $this, 'wp_plugin_scripts' ) );
            add_action( 'admin_menu', array( $this, 'inventory_management_menu' ), 99 );
            add_action( 'prism_ajax_load_low_stock_products', array( $this, 'load_low_stock_products' ) );
            add_action( 'wp_ajax_add_product_to_order_callback', array( $this, 'add_product_to_order_callback' ) );
            add_action( 'wp_ajax_save_inventory_settings_callback', array( $this, 'save_inventory_settings_callback' ) );
            $im_receiver = get_option( 'oimwc_receiver' );
            $im_address1 = get_option( 'oimwc_receiver_address1' );
            $im_address2 = get_option( 'oimwc_receiver_address2' );
            $im_city = get_option( 'oimwc_receiver_city' );
            $im_state = get_option( 'oimwc_receiver_state' );
            $im_zip_code = get_option( 'oimwc_receiver_zip_code' );
            $im_contact = get_option( 'oimwc_receiver_contact' );
            $im_country = get_option( 'oimwc_receiver_country' );
            $shipping_address_flag = get_option( 'oimwc_shipping_address_flag' );
            $shipping_address = [];
            
            if ( empty($shipping_address_flag) && ($im_receiver != '' || $im_address1 != '' || $im_address2 != '' || $im_city != '' || $im_state != '' || $im_zip_code != '' || $im_contact != '' || $im_country != '') ) {
                $shipping_address['address_0'] = array(
                    'im_receiver' => $im_receiver,
                    'im_address1' => $im_address1,
                    'im_address2' => $im_address2,
                    'im_city'     => $im_city,
                    'im_state'    => $im_state,
                    'im_zip_code' => $im_zip_code,
                    'im_contact'  => $im_contact,
                    'im_country'  => $im_country,
                    'title'       => 'Address 1',
                );
                update_option( 'oimwc_shipping_address', $shipping_address );
                update_option( 'oimwc_shipping_address_flag', 1 );
            }
            
            add_action( 'wp_ajax_save_unit_to_meta', array( $this, 'save_unit_to_meta' ) );
            add_action( 'wp_ajax_delete_unit_from_meta', array( $this, 'delete_unit_from_meta' ) );
            add_action( 'wp_ajax_get_product_data_to_update', array( $this, 'get_product_data_to_update' ) );
            add_action( 'wp_ajax_add_default_data_to_existing_product', array( $this, 'add_default_data_to_existing_product' ) );
            add_action( 'admin_init', array( $this, 'save_per_page' ) );
            add_action( 'restrict_manage_posts', array( $this, 'filter_products_by_supplier' ), 20 );
            add_filter( 'request', array( $this, 'filter_products_by_supplier_query' ) );
            add_action( 'woocommerce_product_meta_start', array( $this, 'display_gtin_num_single_product' ), 10 );
            add_filter( 'woocommerce_available_variation', array( $this, 'load_gtin_num_variation' ) );
            add_filter( 'admin_body_class', array( $this, 'oimwc_sticky_header_body_class' ) );
            add_action( 'all_admin_notices', array( $this, 'add_top_area_to_supplier' ) );
            oimwc_fs()->add_filter( 'templates/pricing.php', array( $this, 'add_freemius_page_to_tabs' ) );
            oimwc_fs()->add_filter( 'templates/checkout.php', array( $this, 'add_freemius_page_to_tabs' ) );
            oimwc_fs()->add_filter( 'templates/account.php', array( $this, 'add_freemius_page_to_tabs' ) );
            add_filter(
                'woocommerce_grouped_product_list_column_price',
                array( $this, 'add_text_grouped_product' ),
                10,
                2
            );
            add_action( 'wp_ajax_submit_contact_form', array( $this, 'submit_contact_form' ) );
            add_filter( 'parent_file', array( $this, 'active_submenu_of_oimwc' ) );
            add_filter( 'custom_menu_order', array( $this, 'reorder_oimwc_submenu' ) );
            add_action( 'init', array( $this, 'oimwc_update_units_is_stock' ) );
            add_action(
                'woocommerce_product_import_inserted_product_object',
                array( $this, 'product_import_process' ),
                10,
                2
            );
            //Add admin notice to rate plugin
            if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) == 'order-inventory-management' ) {
                add_action( 'admin_notices', array( $this, 'oimwc_plugin_rating_notice' ) );
            }
            add_action( 'wp_ajax_oimwc_plugin_rating_ignore_notice', array( $this, 'oimwc_plugin_rating_ignore_notice' ) );
            add_action(
                'woocommerce_product_export_row_data',
                array( $this, 'product_export_process' ),
                10,
                2
            );
            add_action(
                'pmxi_saved_post',
                array( $this, 'product_import_new_product' ),
                99,
                3
            );
            add_action( 'wp_ajax_send_po_order_email', array( $this, 'send_po_order_email' ) );
            add_action( 'wp_ajax_download_po_document', array( $this, 'download_po_document' ) );
            add_action( 'wp_ajax_purchase_order_table_data', array( $this, 'purchase_order_table_data' ) );
            add_action( 'wp_ajax_remove_purchase_order_product', array( $this, 'remove_purchase_order_product' ) );
            add_action( 'wp_ajax_add_temporary_product', array( $this, 'add_temporary_product' ) );
            add_action( 'wp_ajax_remove_temporary_product', array( $this, 'remove_temporary_product' ) );
        }
        
        /* Update units of stock in all products (if filename match with the plugin version) */
        function oimwc_update_units_is_stock()
        {
            /*Add new version file for updating new changes */
            $oimwc_latest_version = get_option( 'oimwc_latest_version' );
            $plugin_data = get_plugin_data( OIMWC_PLUGIN_FILE );
            $version_num = $plugin_data['Version'];
            if ( !$oimwc_latest_version || $version_num != $oimwc_latest_version ) {
                
                if ( file_exists( OIMWC_PLUGIN_DIR . 'updates/' . $version_num . '.php' ) ) {
                    require_once OIMWC_PLUGIN_DIR . 'updates/' . $version_num . '.php';
                    update_option( 'oimwc_latest_version', $version_num );
                    wp_cache_delete( 'alloptions', 'options' );
                }
            
            }
            $update_units_flag = get_option( 'oimwc_update_units_flag' );
            
            if ( !$update_units_flag ) {
                $plugin_data = get_plugin_data( OIMWC_PLUGIN_FILE );
                $version_num = $plugin_data['Version'];
                $file = scandir( OIMWC_PLUGIN_DIR . '/updates', 1 );
                $info = pathinfo( $file[0] );
                $file_name = basename( $file[0], '.' . $info['extension'] );
                
                if ( $file_name == $version_num ) {
                    require_once OIMWC_PLUGIN_DIR . 'updates/1.2.5.php';
                    update_option( 'oimwc_update_units_flag', 1 );
                }
            
            }
            
            $update_units_flag = get_option( 'oimwc_update_product_stock_units_name' );
            
            if ( !$update_units_flag ) {
                $plugin_data = get_plugin_data( OIMWC_PLUGIN_FILE );
                $version_num = $plugin_data['Version'];
                $file_list = scandir( OIMWC_PLUGIN_DIR . '/updates', 1 );
                foreach ( $file_list as $file ) {
                    $info = pathinfo( $file );
                    $version_num = $plugin_data['Version'];
                    $file_name = basename( $file, '.' . $info['extension'] );
                    
                    if ( $file_name == $version_num ) {
                        require_once OIMWC_PLUGIN_DIR . 'updates/' . $version_num . '.php';
                        update_option( 'oimwc_update_product_stock_units_name', 1 );
                    }
                
                }
            }
        
        }
        
        public static function init()
        {
            if ( !isset( self::$instance ) && !self::$instance instanceof OIMWC_MAIN ) {
                self::$instance = new OIMWC_MAIN();
            }
            return self::$instance;
        }
        
        /**
         * Update product stock, physical stock and physical stock qty during import product.
         * @param $object product object
         * @param $data array single row data
         * @since 1.2.8
         */
        function product_import_process( $object, $data )
        {
            global  $wpdb, $OIMProductStock ;
            $table_name = $wpdb->prefix . 'oim_product_stock';
            $product_id = $data['id'];
            $variation_id = 0;
            $stock = ( isset( $data['stock_quantity'] ) ? $data['stock_quantity'] : '' );
            $physical_stock = '';
            $units_in_stock = '';
            $shop_pack_size = '';
            $supplier_id = 0;
            $flag = false;
            $product_obj_id = $product_id;
            $exist_column = [];
            $parent_id = wp_get_post_parent_id( $data['id'] );
            if ( isset( $data['type'] ) && $data['type'] == 'variable' ) {
                return;
            }
            
            if ( $parent_id ) {
                $product_id = $parent_id;
                $variation_id = $data['id'];
                $product_obj_id = $variation_id;
            }
            
            if ( isset( $data['stock_quantity'] ) ) {
                $exist_column[] = 'stock';
            }
            if ( isset( $data['meta_data'] ) ) {
                foreach ( $data['meta_data'] as $meta ) {
                    
                    if ( $meta['key'] == 'oimwc_physical_stock' ) {
                        $exist_column[] = 'physical_stock';
                        $physical_stock = intval( $meta['value'] );
                    }
                    
                    
                    if ( $meta['key'] == 'oimwc_our_pack_size' ) {
                        $exist_column[] = 'shop_pack_size';
                        $shop_pack_size = intval( $meta['value'] );
                    }
                    
                    
                    if ( $meta['key'] == 'oimwc_physical_units_stock' ) {
                        $exist_column[] = 'units_in_stock';
                        $units_in_stock = intval( $meta['value'] );
                    }
                    
                    if ( $meta['key'] == 'oimwc_supplier_id' ) {
                        $supplier_id = intval( $meta['value'] );
                    }
                }
            }
            $product_stock = $OIMProductStock->get_product_stock_detail( array(
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
            ) );
            
            if ( is_array( $product_stock ) && count( $product_stock ) ) {
                if ( $stock === '' ) {
                    $stock = $product_stock['stock'];
                }
                if ( $physical_stock === '' ) {
                    $physical_stock = $product_stock['physical_stock'];
                }
                if ( $shop_pack_size === '' ) {
                    $shop_pack_size = $product_stock['shop_pack_size'];
                }
                if ( $units_in_stock === '' ) {
                    $units_in_stock = $product_stock['stock_in_units'];
                }
            } else {
                if ( !in_array( 'stock', $exist_column ) ) {
                    $stock = get_post_meta( $product_obj_id, '_stock', true );
                }
                if ( !in_array( 'physical_stock', $exist_column ) ) {
                    $physical_stock = get_post_meta( $product_obj_id, 'oimwc_physical_stock', true );
                }
                if ( !in_array( 'shop_pack_size', $exist_column ) ) {
                    $shop_pack_size = get_post_meta( $product_obj_id, 'oimwc_our_pack_size', true );
                }
                if ( !in_array( 'units_in_stock', $exist_column ) ) {
                    $units_in_stock = get_post_meta( $product_obj_id, 'oimwc_physical_units_stock', true );
                }
            }
            
            
            if ( !$shop_pack_size ) {
                $shop_pack_size = get_post_meta( $product_obj_id, 'oimwc_our_pack_size', true );
                if ( !$shop_pack_size && $variation_id ) {
                    $shop_pack_size = get_post_meta( $product_id, 'oimwc_our_pack_size', true );
                }
            }
            
            $existing_field = '';
            
            if ( in_array( 'units_in_stock', $exist_column ) ) {
                $existing_field = 'units_in_stock';
            } else {
                
                if ( in_array( 'physical_stock', $exist_column ) ) {
                    $existing_field = 'physical_stock';
                } else {
                    
                    if ( in_array( 'stock', $exist_column ) ) {
                        $existing_field = 'stock';
                    } else {
                        if ( in_array( 'shop_pack_size', $exist_column ) ) {
                            $existing_field = 'shop_pack_size';
                        }
                    }
                
                }
            
            }
            
            $shop_pack_size = ( $shop_pack_size ? $shop_pack_size : 1 );
            if ( $physical_stock || $stock || $units_in_stock ) {
                $flag = $OIMProductStock->update_product(
                    $product_id,
                    $variation_id,
                    $stock,
                    $physical_stock,
                    $units_in_stock,
                    $shop_pack_size,
                    1,
                    $existing_field
                );
            }
            oimwc_supplier_low_stock_count( 0, $supplier_id );
            oimwc_show_all_product_stock_count(
                $supplier_id,
                false,
                '=',
                true
            );
        }
        
        /**
         * Check product shop pack size and supplier pack size
         * @param $row  array single row data
         * @param $product product object
         * @since 1.2.8
         */
        function product_export_process( $row, $product )
        {
            if ( isset( $row['meta:oimwc_supplier_pack_size'] ) && (empty($row['meta:oimwc_supplier_pack_size']) || !$row['meta:oimwc_supplier_pack_size']) ) {
                $row['meta:oimwc_supplier_pack_size'] = 1;
            }
            if ( isset( $row['meta:oimwc_our_pack_size'] ) && (empty($row['meta:oimwc_our_pack_size']) || !$row['meta:oimwc_our_pack_size']) ) {
                $row['meta:oimwc_our_pack_size'] = 1;
            }
            return $row;
        }
        
        /**
         * Add unique body class for sticky header
         * @since 1.0.0
         */
        function oimwc_sticky_header_body_class( $classes )
        {
            global  $post ;
            
            if ( isset( $_GET['subpage'] ) && sanitize_text_field( $_GET['subpage'] ) == 'delivery_table' || isset( $_GET['subpage'] ) && sanitize_text_field( $_GET['subpage'] ) == 'purchase-orders' || (isset( $_GET['post_type'] ) && sanitize_text_field( $_GET['post_type'] ) == 'supplier' || isset( $post->post_type ) && $post->post_type == 'supplier') ) {
                $classes = $classes . ' oimwc_sticky_header ';
            } else {
                if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) == 'order-inventory-management' && !isset( $_GET['subpage'] ) ) {
                    $classes = $classes . ' oimwc_sticky_header ';
                }
            }
            
            if ( isset( $_GET['subpage'] ) && sanitize_text_field( $_GET['subpage'] ) == 'settings' ) {
                $classes = $classes . ' oimwc_settings ';
            }
            return $classes;
        }
        
        /**
         * set product catalog visibility to hidden if product is checked to discontinued and stock is below 0
         *
         * @since 1.0.0
         */
        function hide_visibility_for_discontinued_prod( $order )
        {
            $items = $order->get_items();
            foreach ( $items as $item ) {
                $product_name = $item->get_name();
                $product_id = $item->get_product_id();
                $product_variation_id = $item->get_variation_id();
                $parent_id = 0;
                $product_obj = wc_get_product( $product_id );
                $product_stock = (int) $product_obj->get_stock_quantity();
                $discontinued_product = get_post_meta( $product_id, 'oimwc_discontinued_product', true );
                if ( $product_stock <= 0 && $discontinued_product == 'yes' ) {
                    wp_set_object_terms( $product_id, array( 'exclude-from-search', 'exclude-from-catalog' ), 'product_visibility' );
                }
                /** 
                 * set product catalog visibility to hidden if all variants are checked to discontinued and stock is below 0
                 **/
                
                if ( $product_variation_id ) {
                    $parent_id = $product_id;
                    $product_id = $product_variation_id;
                } else {
                    continue;
                }
                
                $product_obj = new WC_Product_Variable( $parent_id );
                $available_variations = $product_obj->get_available_variations();
                
                if ( is_array( $available_variations ) && count( $available_variations ) > 0 ) {
                    $total_variations = 0;
                    $total_discontinued = 0;
                    foreach ( $available_variations as $key => $variation ) {
                        $total_variations++;
                        $variation_id = $variation['variation_id'];
                        $discontinued = get_post_meta( $variation_id, 'oimwc_discontinued_product', true );
                        $variation_obj = wc_get_product( $variation_id );
                        $variation_actual_stock = (int) $variation_obj->get_stock_quantity();
                        
                        if ( $variation_actual_stock <= 0 && $discontinued == 'yes' ) {
                            $total_discontinued++;
                            //Display variant if stock is out of stock and also discontinued product.
                            // $args = array(
                            //     'ID'  => $variation_id,
                            //     'post_status' => 'private'
                            // );
                            // wp_update_post( $args );
                        }
                    
                    }
                    if ( $total_variations == $total_discontinued ) {
                        wp_set_object_terms( $parent_id, array( 'exclude-from-search', 'exclude-from-catalog' ), 'product_visibility' );
                    }
                }
            
            }
        }
        
        /**
         * Hide discontinued products from search results
         *
         * @since 1.0.0
         */
        function hide_discontinued_prod_frm_search( $query )
        {
            
            if ( $query->is_search() && $query->is_main_query() ) {
                $tax_query = $query->get( 'tax_query', array() );
                $tax_query[] = array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => array( 'exclude-from-catalog', 'exclude-from-search' ),
                    'operator' => 'NOT IN',
                );
                $query->set( 'tax_query', $tax_query );
            }
        
        }
        
        /**
         * Add freemius account and pricing pages to oimwc tabs section
         *
         * @since 1.0.0
         */
        function add_freemius_page_to_tabs( $html_content )
        {
            ob_start();
            //echo '<div class="wrap">';
            include_once OIMWC_TEMPLATE . 'top_area.php';
            //echo '</div>';
            $tabs_html = ob_get_contents();
            ob_clean();
            return $tabs_html . $html_content;
        }
        
        /**
         * Include top area layout to supplier post type
         *
         * @since 1.0.0
         */
        function add_top_area_to_supplier()
        {
            global  $post ;
            
            if ( isset( $_GET['post_type'] ) && sanitize_text_field( $_GET['post_type'] ) == 'supplier' || isset( $post->post_type ) && $post->post_type == 'supplier' ) {
                //echo '<div class="wrap">';
                include OIMWC_TEMPLATE . 'top_area.php';
                //echo '</div>';
            }
        
        }
        
        /**
         * Inventory management menu
         * creates inventory management menu and submenus
         *
         * @since 1.0.0
         */
        function inventory_management_menu()
        {
            
            if ( oimwc_check_permission( true ) ) {
                $supplier_count_html = '';
                $count = get_option( 'oimwc_total_lowstock_supplier_count' );
                if ( $count > 0 ) {
                    $supplier_count_html .= '<span class="oimwc_red_bubble awaiting-mod">' . $count . '</span>';
                }
                add_menu_page(
                    __( 'Order & Inventory Manager', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Order & Inventory Manager', 'order-and-inventory-manager-for-woocommerce' ) . ' ' . $supplier_count_html,
                    'read',
                    'order-inventory-management',
                    array( $this, 'inventory_management' ),
                    OIMWC_PLUGIN_URL . 'images/OIMWC_admin_menu_icon.png',
                    56
                );
                add_submenu_page(
                    'order-inventory-management',
                    __( 'Inventory overview', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Inventory overview', 'order-and-inventory-manager-for-woocommerce' ) . ' ' . $supplier_count_html,
                    'read',
                    'order-inventory-management',
                    ''
                );
                $subpage = 'admin.php?page=order-inventory-management&subpage=';
                
                if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() ) {
                    $subpage_link = 'javascript:version_popup(oimwc_obj.silver_upgrade_text)';
                } else {
                    $subpage_link = $subpage . 'purchase-orders';
                }
                
                add_submenu_page(
                    'order-inventory-management',
                    __( 'Purchase Orders', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Purchase Orders', 'order-and-inventory-manager-for-woocommerce' ),
                    'read',
                    $subpage_link,
                    ''
                );
                add_submenu_page(
                    'order-inventory-management',
                    __( 'Suppliers', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Suppliers', 'order-and-inventory-manager-for-woocommerce' ),
                    'read',
                    'edit.php?post_type=supplier',
                    ''
                );
                
                if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() || oimwc_fs()->is_plan_or_trial( 'silver', true ) || oimwc_fs()->is_plan_or_trial( 'gold', true ) ) {
                    $subpage_link = 'javascript:version_popup(oimwc_obj.platinum_upgrade_text)';
                } else {
                    $subpage_link = $subpage . 'order-status';
                }
                
                add_submenu_page(
                    'order-inventory-management',
                    __( 'Order Overview', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Order Overview', 'order-and-inventory-manager-for-woocommerce' ),
                    'read',
                    $subpage_link,
                    ''
                );
                
                if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() || oimwc_fs()->is_plan_or_trial( 'silver', true ) || oimwc_fs()->is_plan_or_trial( 'gold', true ) ) {
                    $subpage_link = 'javascript:version_popup(oimwc_obj.platinum_upgrade_text)';
                } else {
                    $subpage_link = $subpage . 'stock-values';
                }
                
                add_submenu_page(
                    'order-inventory-management',
                    __( 'Inventory Stock Values', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Inventory Stock Values', 'order-and-inventory-manager-for-woocommerce' ),
                    'read',
                    $subpage_link,
                    ''
                );
                add_submenu_page(
                    'order-inventory-management',
                    __( 'Settings', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Settings', 'order-and-inventory-manager-for-woocommerce' ),
                    'manage_options',
                    $subpage . 'settings',
                    ''
                );
                add_submenu_page(
                    'order-inventory-management',
                    __( 'Help', 'order-and-inventory-manager-for-woocommerce' ),
                    __( 'Help', 'order-and-inventory-manager-for-woocommerce' ),
                    'read',
                    $subpage . 'help',
                    ''
                );
            }
        
        }
        
        /**
         * Active submenus of OIMWC parent menu
         *
         * @since 1.0.0
         */
        function active_submenu_of_oimwc( $parent_file )
        {
            global  $submenu_file ;
            
            if ( $parent_file == 'order-inventory-management' && isset( $_GET['subpage'] ) ) {
                $subpage = 'admin.php?page=order-inventory-management&subpage=';
                
                if ( sanitize_text_field( $_GET['subpage'] ) == 'delivery_table' ) {
                    $subpage = $parent_file;
                } elseif ( sanitize_text_field( $_GET['subpage'] ) == 'purchase-orders' ) {
                    $subpage .= 'purchase-orders';
                } elseif ( sanitize_text_field( $_GET['subpage'] ) == 'order-status' ) {
                    $subpage .= 'order-status';
                } elseif ( sanitize_text_field( $_GET['subpage'] ) == 'stock-values' ) {
                    $subpage .= 'stock-values';
                } elseif ( sanitize_text_field( $_GET['subpage'] ) == 'settings' ) {
                    $subpage .= 'settings';
                } elseif ( sanitize_text_field( $_GET['subpage'] ) == 'help' ) {
                    $subpage .= 'help';
                }
                
                $submenu_file = $subpage;
            }
            
            return $parent_file;
        }
        
        /**
         * Reorder submenu position under OIMWC parent menu
         * Move Help page under 'Account' & 'Pricing' pages
         *
         * @since 1.0.0
         */
        function reorder_oimwc_submenu( $menu_ord )
        {
            global  $submenu, $menu ;
            $arr = array();
            
            if ( isset( $submenu['order-inventory-management'] ) && is_array( $submenu['order-inventory-management'] ) && count( $submenu['order-inventory-management'] ) > 0 ) {
                foreach ( $submenu['order-inventory-management'] as $key => $menu_item ) {
                    
                    if ( in_array( 'admin.php?page=order-inventory-management&subpage=help', $menu_item ) ) {
                        $arr = $menu_item;
                        unset( $submenu['order-inventory-management'][$key] );
                    }
                    
                    if ( in_array( 'order-inventory-management-pricing', $menu_item ) ) {
                        $submenu['order-inventory-management'][$key][0] = __( 'Pricing', 'order-and-inventory-manager-for-woocommerce' );
                    }
                    if ( in_array( 'order-inventory-management-account', $menu_item ) ) {
                        $submenu['order-inventory-management'][$key][0] = __( 'Account', 'order-and-inventory-manager-for-woocommerce' );
                    }
                }
                if ( count( $arr ) > 0 ) {
                    $submenu['order-inventory-management'][] = $arr;
                }
            }
            
            return $menu_ord;
        }
        
        /**
         * Inventory Management
         * handles low stock data , products in awaiting delivery and stock level pages
         *
         * @since 1.0.0
         */
        function inventory_management()
        {
            global  $default_supplier ;
            global  $total_pagination_records ;
            wp_enqueue_style( 'oimwc_style' );
            if ( !$this->add_stock_values_menu() ) {
                return;
            }
            
            if ( isset( $_GET['subpage'] ) && sanitize_text_field( $_GET['subpage'] ) == 'settings' && oimwc_check_permission() ) {
                $this->settings_page();
                return;
            }
            
            if ( oimwc_check_permission() && !$this->help_page() ) {
                return;
            }
            
            if ( isset( $_GET['subpage'] ) && sanitize_text_field( $_GET['subpage'] ) == 'purchase-orders' ) {
                $remaning_orders = OIMWC_MAIN::pending_order_list();
                echo  $remaning_orders ;
                return;
            }
            
            $supplier_order_list = [];
            
            if ( isset( $_GET['subpage'] ) && sanitize_text_field( $_GET['subpage'] ) == 'delivery_table' ) {
            } else {
                require_once OIMWC_CLASSES . 'class.data_purchase_order.php';
                $data_order = new DataPurchaseOrder();
                $supplier_list = $this->get_all_supplier_list();
            }
            
            
            if ( $data_order ) {
                $data_order->prepare_items();
            } else {
                $data_order = '';
            }
            
            
            if ( $data ) {
                $data->prepare_items();
            } else {
                $data = '';
            }
            
            include OIMWC_TEMPLATE . 'inventory_management.php';
        }
        
        /**
         * Get supplier list
         * gets list of supplier and supplier id wise products
         *
         * @param Array $default_supplier_list
         * @since 1.0.0
         */
        static function get_supplier_list( $default_supplier_list = false )
        {
            $args = array(
                'post_type'      => 'supplier',
                'posts_per_page' => -1,
                'meta_key'       => 'oimwc_supplier_short_name',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
            );
            $supplier_list = array( __( 'Select supplier', 'order-and-inventory-manager-for-woocommerce' ) );
            $supplier_data = get_posts( $args );
            if ( $supplier_data ) {
                foreach ( $supplier_data as $supplier ) {
                    $short_name = get_post_meta( $supplier->ID, 'oimwc_supplier_short_name', true );
                    $supplier_list[$supplier->ID] = ( $short_name ? $short_name : $supplier->post_name );
                }
            }
            if ( $default_supplier_list ) {
                return $supplier_list;
            }
            return $supplier_list;
        }
        
        function get_all_supplier_list()
        {
            $args = array(
                'post_type'      => 'supplier',
                'posts_per_page' => -1,
                'meta_key'       => 'oimwc_supplier_short_name',
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
            );
            $supplier_data = get_posts( $args );
            $supplier_lowstock_products = 0;
            $reached_level_arr = $lowstock_arr = $without_lowstock_arr = [];
            if ( $supplier_data ) {
                foreach ( $supplier_data as $supplier ) {
                    $supplier_id = $supplier->ID;
                    $short_name = get_post_meta( $supplier->ID, 'oimwc_supplier_short_name', true );
                    $supplier_lowstock_products = get_post_meta( $supplier_id, 'oimwc_supplier_products_lowstock_level', true );
                    $lowstock_count = get_post_meta( $supplier_id, 'oimwc_total_low_stock_products', true );
                    if ( $lowstock_count == '' ) {
                        $lowstock_count = oimwc_supplier_low_stock_count( 0, $supplier_id );
                    }
                    
                    if ( $supplier_lowstock_products && $lowstock_count >= $supplier_lowstock_products ) {
                        $reached_level_arr[$supplier_id] = ( $short_name ? $short_name : $supplier->post_name );
                    } else {
                        
                        if ( $lowstock_count ) {
                            $lowstock_arr[$supplier_id] = ( $short_name ? $short_name : $supplier->post_name );
                        } else {
                            $without_lowstock_arr[$supplier_id] = ( $short_name ? $short_name : $supplier->post_name );
                        }
                    
                    }
                
                }
            }
            $supplier_list = array(
                __( 'Threshold Level Reached', 'order-and-inventory-manager-for-woocommerce' ) => $reached_level_arr,
                __( 'With Low Stock', 'order-and-inventory-manager-for-woocommerce' )          => $lowstock_arr,
                __( 'All in stock', 'order-and-inventory-manager-for-woocommerce' )            => $without_lowstock_arr,
            );
            return $supplier_list;
        }
        
        function add_product_to_order_callback()
        {
            $supplier_id = $_POST['supplier_id'];
            
            if ( !empty($supplier_id) && $supplier_id !== "all" ) {
                $supplier_order_array = OIMWC_Order::get_ordered_supplier( $supplier_id );
                $supplier_order_list = $supplier_order_array['supplier_list'];
                $lock_supplier_list = $supplier_order_array['lock_supplier_list'];
                $lock_product = $supplier_order_array['lock_product'];
                $order_status = $supplier_order_array['order_status'];
                array_unique( $supplier_order_list );
                // asort( $supplier_order_list );
                
                if ( is_array( $supplier_order_list ) && count( $supplier_order_list ) > 0 ) {
                    wp_send_json_success( array(
                        'supplier_order_list' => $supplier_order_list,
                        'lock_supplier_list'  => $lock_supplier_list,
                        'lock_product'        => $lock_product,
                        'order_status'        => $order_status,
                        'check_status'        => __( 'Receiving', 'order-and-inventory-manager-for-woocommerce' ),
                        'lock_title'          => __( 'Locked', 'order-and-inventory-manager-for-woocommerce' ),
                    ) );
                } else {
                    wp_send_json_error();
                }
            
            }
        
        }
        
        /**
         * Help Page
         * handles contact, information about how to use plugin
         *
         * @since 1.0.0
         */
        function help_page()
        {
            $val = get_option( 'oimwc_initial_page' );
            
            if ( isset( $_GET['subpage'] ) && sanitize_text_field( $_GET['subpage'] ) == 'help' || !$val ) {
                include OIMWC_TEMPLATE . 'help.php';
                update_option( 'oimwc_initial_page', 1 );
                return false;
            }
            
            return true;
        }
        
        /**
         * Auto Load low stock products using ajax
         * 
         * @since 1.0.0
         */
        function load_low_stock_products()
        {
            $subpage = ( isset( $_POST['subpage'] ) ? $_POST['subpage'] : '' );
            
            if ( $subpage == 'delivery_table' ) {
                require_once OIMWC_CLASSES . 'class.data_delivery_table.php';
                $dataDelivery_obj = new DataDeliveryTable();
                $data = $dataDelivery_obj->table_data();
                $total_pages = $dataDelivery_obj->total_pages;
                $total_supplier_product_low_stock = $dataDelivery_obj->total_supplier_product_low_stock;
            }
            
            
            if ( $_POST['show_all_product'] == 1 || $_POST['show_all_product'] == 0 && $subpage == '' ) {
                require_once OIMWC_CLASSES . 'class.data_purchase_order.php';
                $purchaseorder_obj = new DataPurchaseOrder();
                $data_order = $purchaseorder_obj->table_data();
                $total_pages = $purchaseorder_obj->total_pages;
                $total_supplier_product_low_stock = $purchaseorder_obj->total_supplier_product_low_stock;
                require_once OIMWC_CLASSES . 'class.data_low_stock.php';
                $lowstock_obj = new DataLowStock();
                $data = $lowstock_obj->table_data();
                $total_pages = $lowstock_obj->total_pages;
                $total_supplier_product_low_stock = $lowstock_obj->total_supplier_product_low_stock;
            }
            
            
            if ( $data_order ) {
                $data_order = $data_order;
            } else {
                $data_order = '';
            }
            
            
            if ( $data ) {
                $data = $data;
            } else {
                $data = '';
                $total_pages = 0;
                $total_supplier_product_low_stock = 0;
            }
            
            wp_send_json_success( array(
                'records'                          => $data,
                'total_pages'                      => $total_pages,
                'total_supplier_product_low_stock' => $total_supplier_product_low_stock,
                'data_order'                       => $data_order,
            ) );
        }
        
        /**
         * Supplier init
         * Registers new post type "Supplier"
         *
         * @since 1.0.0
         */
        function supplier_init()
        {
            $labels = array(
                'name'               => _x( 'Suppliers', 'Post type general name', 'order-and-inventory-manager-for-woocommerce' ),
                'singular_name'      => _x( 'Supplier', 'Post type singular name', 'order-and-inventory-manager-for-woocommerce' ),
                'menu_name'          => __( 'Suppliers', 'order-and-inventory-manager-for-woocommerce' ),
                'name_admin_bar'     => __( 'Supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'all_items'          => __( 'Suppliers', 'order-and-inventory-manager-for-woocommerce' ),
                'add_new_item'       => __( 'Add new supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'add_new'            => __( 'Add new supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'new_item'           => __( 'New supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'edit_item'          => __( 'Edit supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'update_item'        => __( 'Update supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'view_item'          => __( 'View supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'view_items'         => __( 'View suppliers', 'order-and-inventory-manager-for-woocommerce' ),
                'search_items'       => __( 'Search supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'not_found'          => __( 'Not found', 'order-and-inventory-manager-for-woocommerce' ),
                'not_found_in_trash' => __( 'Not found in Trash', 'order-and-inventory-manager-for-woocommerce' ),
            );
            $args = array(
                'label'               => __( 'Supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'labels'              => $labels,
                'supports'            => array( 'title' ),
                'hierarchical'        => false,
                'public'              => false,
                'show_ui'             => true,
                'show_in_menu'        => false,
                'exclude_from_search' => true,
                'publicly_queryable'  => false,
                'capability_type'     => 'page',
                'map_meta_cap'        => true,
                'rewrite'             => false,
                'query_var'           => false,
                'show_in_nav_menus'   => false,
                'show_in_admin_bar'   => true,
                'has_archive'         => false,
            );
            register_post_type( 'supplier', $args );
        }
        
        /**
         * Add id column in supplier table
         *
         * @param string $column column name 
         * @param integer $id of columns
         * @since 1.0.0
         */
        function add_supplier_custom_column_content( $column, $id )
        {
            
            if ( sanitize_text_field( $_GET['post_type'] ) == 'supplier' ) {
                wp_enqueue_style( 'oimwc_style' );
                if ( $column == 'supplier_id' ) {
                    echo  esc_html( $id ) ;
                }
                
                if ( $column == 'custom_name' ) {
                    $supplier_short_name = get_post_meta( $id, 'oimwc_supplier_short_name', true );
                    echo  esc_html( $supplier_short_name ) ;
                }
                
                ?>
                <style> .bulkactions{ display: none;}#cb,.check-column{display: none;}</style>
                <?php 
            }
            
            
            if ( sanitize_text_field( $_GET['post_type'] ) == 'product' ) {
                wp_enqueue_style( 'oimwc_style' );
                
                if ( $column == 'supplier' ) {
                    $supplier_id = get_post_meta( $id, 'oimwc_supplier_id', true );
                    $supplier_short_name = get_post_meta( $supplier_id, 'oimwc_supplier_short_name', true );
                    echo  esc_html( $supplier_short_name ) ;
                }
            
            }
        
        }
        
        /**
         * Adds supplier columns in table header
         *
         * @param Array $columns of columns
         * @since 1.0.0
         */
        function add_supplier_custom_column( $columns )
        {
            
            if ( isset( $_GET['post_type'] ) && sanitize_text_field( $_GET['post_type'] ) == 'supplier' ) {
                $newcolumns['cb'] = $columns['cb'];
                $newcolumns['supplier_id'] = __( 'ID', 'order-and-inventory-manager-for-woocommerce' );
                $newcolumns['title'] = __( 'Supplier Name', 'order-and-inventory-manager-for-woocommerce' );
                $newcolumns['custom_name'] = __( 'Custom Name', 'order-and-inventory-manager-for-woocommerce' );
                $columns = $newcolumns;
                return $columns;
            }
            
            
            if ( isset( $_GET['post_type'] ) && sanitize_text_field( $_GET['post_type'] ) == 'product' ) {
                $newcolumns['supplier'] = __( 'Supplier', 'order-and-inventory-manager-for-woocommerce' );
                $columns = $newcolumns;
                return $columns;
            }
        
        }
        
        /**
         * Supplier Sortable Columns
         *
         * @param Array $columns of columns
         * @since 1.0.0
         */
        function supplier_sortable_columns( $columns )
        {
            $columns['supplier_id'] = 'id';
            $columns['custom_name'] = 'custom_name';
            return $columns;
        }
        
        function supplier_sort_custom_column( $clauses, $wp_query )
        {
            global  $wpdb ;
            
            if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'supplier' || isset( $_POST['post_type'] ) && $_POST['post_type'] == 'supplier' ) {
                
                if ( isset( $wp_query->query['orderby'] ) && $wp_query->query['orderby'] == 'custom_name' ) {
                    $clauses['join'] .= "\r\n                        LEFT JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id)";
                    $clauses['where'] .= "AND ({$wpdb->postmeta}.meta_key = 'oimwc_supplier_short_name')";
                    $clauses['orderby'] = "{$wpdb->postmeta}.meta_value ";
                } elseif ( isset( $wp_query->query['orderby'] ) && ($wp_query->query['orderby'] == 'id' || $wp_query->query['orderby'] == 'supplier_id') ) {
                    $clauses['orderby'] = "{$wpdb->posts}.ID ";
                } else {
                    $clauses['orderby'] = "{$wpdb->posts}.post_title ";
                }
                
                
                if ( isset( $_GET['order'] ) || isset( $_POST['order'] ) ) {
                    
                    if ( strtoupper( $wp_query->get( 'order' ) ) == 'DESC' ) {
                        $clauses['orderby'] .= 'DESC';
                    } else {
                        $clauses['orderby'] .= 'ASC';
                    }
                
                } else {
                    $clauses['orderby'] .= 'ASC';
                }
            
            }
            
            return $clauses;
        }
        
        /**
         * Supplier additional info
         * Adds meta box of supplier details
         *
         * @param Array $columns of columns
         * @since 1.0.0
         */
        function supplier_additional_info()
        {
            add_meta_box(
                'supplier_meta_box',
                __( 'Supplier details', 'order-and-inventory-manager-for-woocommerce' ),
                array( $this, 'supplier_details' ),
                'supplier',
                'normal',
                'high'
            );
        }
        
        /**
         * Supplier details
         * Gets stored supplier information values and displays it in html
         *
         * @since 1.0.0
         */
        function supplier_details()
        {
            $post_id = get_the_ID();
            $countries = oimwc_get_countries_list();
            wp_enqueue_style( 'oimwc_style' );
            $supplier_short_name = get_post_meta( $post_id, 'oimwc_supplier_short_name', true );
            $supplier_address = get_post_meta( $post_id, 'oimwc_supplier_address', true );
            $supplier_country = get_post_meta( $post_id, 'oimwc_supplier_country', true );
            $supplier_website_url = get_post_meta( $post_id, 'oimwc_supplier_website_url', true );
            $supplier_order_url = get_post_meta( $post_id, 'oimwc_supplier_order_url', true );
            $supplier_currency = get_post_meta( $post_id, 'oimwc_supplier_currency', true );
            $default_supplier_currency = get_option( 'oimwc_default_supplier_currency' );
            if ( !$supplier_currency ) {
                $supplier_currency = $default_supplier_currency;
            }
            $supplier_description = get_post_meta( $post_id, 'oimwc_supplier_description', true );
            $supplier_email = get_post_meta( $post_id, 'oimwc_supplier_email', true );
            $supplier_order_email = get_post_meta( $post_id, 'oimwc_supplier_order_email', true );
            $supplier_skype_id = get_post_meta( $post_id, 'oimwc_supplier_skype_id', true );
            $supplier_phone_no = get_post_meta( $post_id, 'oimwc_supplier_phone_no', true );
            $supplier_tax_no = get_post_meta( $post_id, 'oimwc_supplier_tax_no', true );
            $supplier_contact_person = get_post_meta( $post_id, 'oimwc_supplier_contact_person', true );
            $supplier_products_lowstock_level = get_post_meta( $post_id, 'oimwc_supplier_products_lowstock_level', true );
            include_once OIMWC_TEMPLATE . 'metabox_supplier.php';
        }
        
        /**
         * Plugin Scripts
         * Registers and enqueues plugin scripts and styles
         *
         * @since 1.0.0
         */
        function plugin_scripts()
        {
            $version = filemtime( OIMWC_PLUGIN_DIR . 'css/admin_style.css' );
            wp_enqueue_style(
                'oimwc_style',
                OIMWC_PLUGIN_URL . 'css/admin_style.css',
                array(),
                $version
            );
            wp_enqueue_style( 'jquery-ui-css', OIMWC_PLUGIN_URL . 'css/jquery-ui.min.css' );
            wp_enqueue_style( 'jquery-theme', OIMWC_PLUGIN_URL . 'css/theme.min.css' );
            wp_register_style( 'oimwc_font_awesome', OIMWC_PLUGIN_URL . 'css/fontawesome-all.min.css' );
            wp_enqueue_style( 'ubuntu-font', 'https://fonts.googleapis.com/css?family=Ubuntu:400,700&display=swap' );
            wp_enqueue_script( 'jquery-validate-min', OIMWC_PLUGIN_URL . 'js/jquery.validate.min.js', array( 'jquery' ) );
            $version = filemtime( OIMWC_PLUGIN_DIR . 'js/scripts.js' );
            wp_register_script(
                'oimwc_script',
                OIMWC_PLUGIN_URL . 'js/scripts.js',
                array(
                'jquery',
                'jquery-ui-datepicker',
                'jquery-ui-dialog',
                'jquery-tiptip',
                'jquery-ui-sortable',
                'jquery-ui-tabs',
                'wp-util'
            ),
                $version
            );
            wp_enqueue_script( 'sweetalert-js', OIMWC_PLUGIN_URL . 'js/sweetalert.min.js' );
            wp_enqueue_script( 'select2-min-js', OIMWC_PLUGIN_URL . 'js/select2.min.js' );
            wp_enqueue_style( 'select2-min-css', OIMWC_PLUGIN_URL . 'css/select2.min.css' );
            wp_enqueue_script( 'colorpicker-js', OIMWC_PLUGIN_URL . 'js/jscolor.js' );
            
            if ( class_exists( 'woocommerce' ) ) {
                wp_dequeue_style( 'select2' );
                wp_deregister_style( 'select2' );
                wp_dequeue_script( 'select2' );
                wp_deregister_script( 'select2' );
            }
            
            $translation_array = array(
                'cancel_text'                     => __( 'Cancel', 'order-and-inventory-manager-for-woocommerce' ),
                'save_text'                       => __( 'Save', 'order-and-inventory-manager-for-woocommerce' ),
                'singular_ex'                     => __( 'Ex. meter', 'order-and-inventory-manager-for-woocommerce' ),
                'plural_ex'                       => __( 'Ex. meters', 'order-and-inventory-manager-for-woocommerce' ),
                'delete_text'                     => __( 'Delete', 'order-and-inventory-manager-for-woocommerce' ),
                'confirm_msg'                     => __( "Deleting a unit will set all products using this unit to the default unit, are you sure you want to delete this unit?", 'order-and-inventory-manager-for-woocommerce' ),
                'order_date_format'               => $this->date_format_php_to_js( get_option( 'date_format' ) ),
                'tooltip_msg'                     => __( "Please enable 'Stock management' to unlock the fields.", 'order-and-inventory-manager-for-woocommerce' ),
                'silver_upgrade_text'             => __( 'Upgrade OIMWC to Silver version or higher to unlock this feature.', 'order-and-inventory-manager-for-woocommerce' ),
                'gold_upgrade_text'               => __( 'Upgrade OIMWC to Gold version or higher to unlock this feature.', 'order-and-inventory-manager-for-woocommerce' ),
                'platinum_upgrade_text'           => __( 'Upgrade OIMWC to Platinum version to unlock this feature.', 'order-and-inventory-manager-for-woocommerce' ),
                'upgrade_btn_text'                => __( 'UPGRADE NOW', 'order-and-inventory-manager-for-woocommerce' ),
                'upgrade_location'                => admin_url( 'admin.php?page=order-inventory-management-pricing' ),
                'finalize_product_notice'         => __( 'Are you sure you want to finalize the product?', 'order-and-inventory-manager-for-woocommerce' ),
                'order_type'                      => ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' ),
                'prompt_msg'                      => __( 'Add additional information to order (optional)', 'order-and-inventory-manager-for-woocommerce' ),
                'cancel_order_msg'                => __( 'Are you sure you want to cancel the order?', 'order-and-inventory-manager-for-woocommerce' ),
                'remove_product_txt'              => __( 'Are you sure you want to remove this product from order?', 'order-and-inventory-manager-for-woocommerce' ),
                'user_remove_text'                => __( 'Are you sure you want to remove selected user?', 'order-and-inventory-manager-for-woocommerce' ),
                'select_any_user_txt'             => __( 'Please select any user', 'order-and-inventory-manager-for-woocommerce' ),
                'no_user_found'                   => __( 'No users Found.', 'order-and-inventory-manager-for-woocommerce' ),
                'download_text'                   => __( 'Download Purchase Order', 'order-and-inventory-manager-for-woocommerce' ),
                'search_supplier_label'           => __( 'Search supplier...', 'order-and-inventory-manager-for-woocommerce' ),
                'address'                         => __( 'Address', 'order-and-inventory-manager-for-woocommerce' ),
                'receiver'                        => __( 'Receiver', 'order-and-inventory-manager-for-woocommerce' ),
                'contact_person'                  => __( 'Contact person', 'order-and-inventory-manager-for-woocommerce' ),
                'address_1'                       => __( 'Address line 1', 'order-and-inventory-manager-for-woocommerce' ),
                'address_2'                       => __( 'Address line 2', 'order-and-inventory-manager-for-woocommerce' ),
                'city'                            => __( 'City', 'order-and-inventory-manager-for-woocommerce' ),
                'state'                           => __( 'State / Province / Region', 'order-and-inventory-manager-for-woocommerce' ),
                'zip_code'                        => __( 'Zip / Postal code', 'order-and-inventory-manager-for-woocommerce' ),
                'country'                         => __( 'Country', 'order-and-inventory-manager-for-woocommerce' ),
                'select_country'                  => __( 'Select country', 'order-and-inventory-manager-for-woocommerce' ),
                'delete_address'                  => __( 'Delete Address', 'order-and-inventory-manager-for-woocommerce' ),
                'delete_address_msg'              => __( 'Are you sure you want to delete this address?', 'order-and-inventory-manager-for-woocommerce' ),
                'address_name'                    => __( 'Add Address Name', 'order-and-inventory-manager-for-woocommerce' ),
                'countries_dropdown'              => oimwc_get_countries_list(),
                'change_pending_status'           => __( 'Unlock Order And Change Status to Pending', 'order-and-inventory-manager-for-woocommerce' ),
                'change_ordered_status'           => __( 'Lock Order And Change Status to Ordered', 'order-and-inventory-manager-for-woocommerce' ),
                'no_previous_order'               => __( 'There is no pending order from this supplier.', 'order-and-inventory-manager-for-woocommerce' ),
                'enter_value'                     => __( 'Please enter value.', 'order-and-inventory-manager-for-woocommerce' ),
                'enter_valid_qty'                 => __( 'Please enter valid qty.', 'order-and-inventory-manager-for-woocommerce' ),
                'select_order'                    => __( 'Please select order from list', 'order-and-inventory-manager-for-woocommerce' ),
                'no_products'                     => __( 'No products found.', 'order-and-inventory-manager-for-woocommerce' ),
                'select_supplier'                 => __( 'Please select a supplier to enable the Create Purchase Order feature', 'order-and-inventory-manager-for-woocommerce' ),
                'select_supplier_for_list'        => __( 'Please select a supplier to enable the Add to List feature', 'order-and-inventory-manager-for-woocommerce' ),
                'remove_txt'                      => __( 'Remove', 'order-and-inventory-manager-for-woocommerce' ),
                'supplier_prod_id'                => __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' ),
                'supplier_prod_url'               => __( 'Supplier Prod. URL', 'order-and-inventory-manager-for-woocommerce' ),
                'product_notes'                   => __( 'Product Notes to Supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'purchase_price'                  => __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ),
                'pack_size'                       => __( 'Supplier pack size', 'order-and-inventory-manager-for-woocommerce' ),
                'manage_multi_supplier'           => 0,
                'disabled_field_on_stock_changed' => __( 'The value is locked because the stock changed elsewhere. Undo the changes, update the product, or reload the page to unlock the field.', 'order-and-inventory-manager-for-woocommerce' ),
                'lock_product_msg'                => __( 'No available open orders to add products to.', 'order-and-inventory-manager-for-woocommerce' ),
                'wrong_data_label'                => __( 'Products with mismatching data', 'order-and-inventory-manager-for-woocommerce' ),
                'wrong_order_label'               => __( 'Orders with problems', 'order-and-inventory-manager-for-woocommerce' ),
                'phone_number'                    => __( 'Phone Number', 'order-and-inventory-manager-for-woocommerce' ),
                'select_product'                  => __( 'Select Product', 'order-and-inventory-manager-for-woocommerce' ),
                'product_placeholder'             => __( 'Please enter 3 or more characters', 'order-and-inventory-manager-for-woocommerce' ),
                'send_email_text'                 => __( 'Send', 'order-and-inventory-manager-for-woocommerce' ),
                'send_to_supplier_txt'            => __( 'Email to the Supplier', 'order-and-inventory-manager-for-woocommerce' ),
                'back_text'                       => __( 'Back', 'order-and-inventory-manager-for-woocommerce' ),
                'office_address_name'             => __( 'Add Office Address Name', 'order-and-inventory-manager-for-woocommerce' ),
                'company_name'                    => __( 'Company Name', 'order-and-inventory-manager-for-woocommerce' ),
                'office_address'                  => __( 'Office Address', 'order-and-inventory-manager-for-woocommerce' ),
                'street'                          => __( 'Street', 'order-and-inventory-manager-for-woocommerce' ),
                'email_address'                   => __( 'Email Address', 'order-and-inventory-manager-for-woocommerce' ),
                'fax_number'                      => __( 'Fax Number', 'order-and-inventory-manager-for-woocommerce' ),
                'website'                         => __( 'Website', 'order-and-inventory-manager-for-woocommerce' ),
                'tax_vat'                         => __( 'Tax registration nr. / VAT', 'order-and-inventory-manager-for-woocommerce' ),
                'close_text'                      => __( 'Close', 'order-and-inventory-manager-for-woocommerce' ),
                'select_show_all_product'         => __( 'Please uncheck show all product checkbox to enable the Add to List feature', 'order-and-inventory-manager-for-woocommerce' ),
                'no_preview_data_msg'             => __( 'Enter the amount you wish to order and press the "Update PO preview" button to move the product to this section.', 'order-and-inventory-manager-for-woocommerce' ),
                'load_low_stock_msg'              => __( 'Select a supplier to show products that have a low stock quantity.', 'order-and-inventory-manager-for-woocommerce' ),
                'products_text'                   => __( 'Products', 'order-and-inventory-manager-for-woocommerce' ),
                'select_supplier_for_search'      => __( 'Please select a supplier to enable the search feature', 'order-and-inventory-manager-for-woocommerce' ),
                'prism_ajax_url'                  => OIMWC_PLUGIN_URL . 'classes/prism-ajax.php',
                'po_order_file'                   => __( 'No purchase order file was created!', 'order-and-inventory-manager-for-woocommerce' ),
                'add_tmp_product_text'            => __( 'Add Temporary Product', 'order-and-inventory-manager-for-woocommerce' ),
                'select_tmp_product'              => __( 'Please select a supplier to enable the Create Temporary Product feature', 'order-and-inventory-manager-for-woocommerce' ),
                'remove_tmp_product_txt'          => __( 'Are you sure you want to remove this product from the list?', 'order-and-inventory-manager-for-woocommerce' ),
                'no_supplier'                     => __( 'No supplier found.', 'order-and-inventory-manager-for-woocommerce' ),
            );
            wp_localize_script( 'oimwc_script', 'oimwc_obj', $translation_array );
            wp_enqueue_script( 'oimwc_script' );
            wp_enqueue_style( 'oimwc_font_awesome' );
            if ( !did_action( 'wp_enqueue_media' ) ) {
                wp_enqueue_media();
            }
        }
        
        function wp_plugin_scripts()
        {
            global  $product, $post ;
            
            if ( is_product() ) {
                if ( !is_array( $product ) ) {
                    $product = wc_get_product( $post->ID );
                }
                $var_data = [];
                
                if ( is_single() && $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
                    $variations = $product->get_available_variations();
                    foreach ( $variations as $variation ) {
                        $variation_id = $variation['variation_id'];
                        $dis_prod = get_post_meta( $variation_id, 'oimwc_discontinued_product', true );
                        $stock = get_post_meta( $variation_id, '_stock', true );
                        
                        if ( $dis_prod == 'yes' && $stock <= 0 ) {
                            $var_data[$variation_id] = 1;
                        } else {
                            $var_data[$variation_id] = 0;
                        }
                    
                    }
                }
                
                $version = filemtime( OIMWC_PLUGIN_DIR . 'css/front_style.css' );
                wp_enqueue_style(
                    'oimwc_front_style',
                    OIMWC_PLUGIN_URL . 'css/front_style.css',
                    array(),
                    $version
                );
                $version = filemtime( OIMWC_PLUGIN_DIR . 'js/front_script.js' );
                wp_register_script(
                    'oimwc_front_script',
                    OIMWC_PLUGIN_URL . 'js/front_script.js',
                    array( 'jquery' ),
                    $version
                );
                wp_localize_script( 'oimwc_front_script', 'oimwc_front_obj', array(
                    'ajaxurl'       => admin_url( 'admin-ajax.php' ),
                    'variation_ids' => $var_data,
                ) );
                wp_enqueue_script( 'oimwc_front_script' );
            }
        
        }
        
        function date_format_php_to_js( $sFormat )
        {
            $arr1 = array(
                'F',
                'd',
                'Y',
                'm',
                'j'
            );
            $arr2 = array(
                'MM',
                'dd',
                'yy',
                'mm',
                'dd'
            );
            return str_replace( $arr1, $arr2, $sFormat );
            /*switch( $sFormat ) {
                  case 'F j, Y':
                      return( 'MM dd, yy' );
                      break;
                  case 'Y-m-d':
                      return( 'yy-mm-dd' );
                      break;
                  case 'Y/m/d':
                      return( 'yy/mm/dd' );
                      break;
                  case 'm/d/Y':
                      return( 'mm/dd/yy' );
                      break;
                  case 'd/m/Y':
                      return( 'dd/mm/yy' );
                      break;
              }*/
        }
        
        /**
         * Save supplier
         * Saves supplier data
         *
         * @param integer $post_id post id of supplier
         * @since 1.0.0
         */
        function save_supplier( $post_id )
        {
            if ( wp_is_post_revision( $post_id ) ) {
                return;
            }
            $post_type = get_post_type( $post_id );
            // If this isn't a 'supplier' post, don't update it.
            if ( "supplier" != $post_type ) {
                return;
            }
            if ( !isset( $_POST['supplier_short_name'] ) ) {
                return;
            }
            update_post_meta( $post_id, 'oimwc_supplier_short_name', sanitize_text_field( $_POST['supplier_short_name'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_address', sanitize_text_field( $_POST['supplier_address'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_country', sanitize_text_field( $_POST['supplier_country'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_website_url', sanitize_text_field( $_POST['supplier_website_url'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_order_url', sanitize_text_field( $_POST['supplier_order_url'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_currency', sanitize_text_field( $_POST['supplier_currency'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_description', sanitize_text_field( $_POST['supplier_description'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_email', sanitize_email( $_POST['supplier_email'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_order_email', sanitize_email( $_POST['supplier_order_email'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_skype_id', sanitize_text_field( $_POST['supplier_skype_id'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_phone_no', sanitize_text_field( $_POST['supplier_phone_no'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_tax_no', sanitize_text_field( $_POST['supplier_tax_no'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_contact_person', sanitize_text_field( $_POST['supplier_contact_person'] ) );
            update_post_meta( $post_id, 'oimwc_supplier_products_lowstock_level', sanitize_text_field( $_POST['supplier_products_lowstock_level'] ) );
            oimwc_supplier_low_stock_count( 0, $supplier_id );
            oimwc_show_all_product_stock_count(
                $supplier_id,
                false,
                '=',
                true
            );
        }
        
        /**
         * Pending order list
         * Gets ordered products which are not arrived
         *
         * @since 1.0.0
         */
        public static function pending_order_list()
        {
        }
        
        function load_completed_purchase_orders()
        {
            $page = 1;
            if ( isset( $_POST['page'] ) ) {
                $page = $_POST['page'];
            }
            $order_type = $_POST['order_type'];
            $supplier_id = ( !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : '' );
            $orderby = ( !empty($_POST['orderby']) ? $_POST['orderby'] : '' );
            $order = ( !empty($_POST['order']) ? $_POST['order'] : '' );
            $remaning_orders = OIMWC_Order::get_remaining_orders(
                $order_type,
                $page,
                $supplier_id,
                $orderby,
                $order
            );
            $data = $this->purchase_orders_list( $remaning_orders, $order_type );
            echo  json_encode( $data ) ;
            die;
        }
        
        function purchase_orders_list( $remaning_orders, $order_type )
        {
            $data = [];
            
            if ( is_array( $remaning_orders ) && count( $remaning_orders ) ) {
                $order_status = array();
                foreach ( $remaning_orders as $remaning_order ) {
                    $order_number = $remaning_order->order_number;
                    $supplier_id = $remaning_order->supplier_id;
                    $supplier_name = get_post_meta( $supplier_id, 'oimwc_supplier_short_name', true );
                    $supplier_order_url = get_post_meta( $supplier_id, 'oimwc_supplier_order_url', true );
                    $supplier_name = ( $supplier_order_url ? '<a href="' . $supplier_order_url . '" target="_blank">' . $supplier_name . '</a>' : $supplier_name );
                    $order_date = $remaning_order->order_date;
                    $order_date_time = date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $order_date ) );
                    $total_products = OIMWC_Order::get_total_products( array(
                        'supplier_id' => $supplier_id,
                        'order_date'  => $order_date,
                    ) );
                    $total_purchase = OIMWC_Order::get_total_purchase_amount( array(
                        'supplier_id' => $supplier_id,
                        'order_date'  => $order_date,
                    ) );
                    $total_awaiting_products = OIMWC_Order::get_awaiting_delivery_products( array(
                        'supplier_id' => $supplier_id,
                        'order_date'  => $order_date,
                    ) );
                    $view_order_url = admin_url() . 'admin.php?page=order-inventory-management';
                    $view_order_url = add_query_arg( array(
                        'subpage'    => 'purchase-orders',
                        'view_order' => 1,
                        'supplier'   => $supplier_id,
                        'date'       => strtotime( $order_date ),
                    ), $view_order_url );
                    $po_default_cols = get_post_meta( $remaning_order->supplier_id, 'oimwc_default_po_settings', true );
                    $po_default_setting_flag = get_post_meta( $remaning_order->supplier_id, 'oimwc_default_po_settings_flag', true );
                    $po_default_lang = get_post_meta( $remaning_order->supplier_id, 'oimwc_download_po_lang', true );
                    $default_cols = ( $po_default_setting_flag ? $po_default_cols : '' );
                    $po_default_lang = ( $po_default_setting_flag ? $po_default_lang : '' );
                    $additional_info = $remaning_order->additional_information;
                    $private_note = $remaning_order->private_note;
                    if ( !isset( $order_status[$supplier_id] ) || !isset( $order_status[$supplier_id][$order_date] ) ) {
                        $order_status[$supplier_id][$order_date] = $this->get_purchase_order_status( $supplier_id, $order_date );
                    }
                    
                    if ( $order_status[$supplier_id][$order_date] == __( 'Receiving', 'order-and-inventory-manager-for-woocommerce' ) ) {
                        $disable_lock = 'disabled';
                        $cls = 'fa fa-lock';
                    } else {
                        $disable_lock = '';
                        $cls = 'fa fa-unlock';
                    }
                    
                    $lock_product = $remaning_order->lock_product;
                    $disabled = ( $lock_product ? 'disabled' : '' );
                    $style = ( $lock_product ? "pointer-events:none" : "" );
                    $lock_title = ( $lock_product ? __( 'Unlock Order And Change Status to Pending', 'order-and-inventory-manager-for-woocommerce' ) : __( 'Lock Order And Change Status to Ordered', 'order-and-inventory-manager-for-woocommerce' ) );
                    $arrival_date = ( $remaning_order->arrival_date ? date_i18n( get_option( 'date_format' ), strtotime( $remaning_order->arrival_date ) ) : '' );
                    $add_info_cls = ( $additional_info ? 'additional_info_btn' : '' );
                    $private_cls = ( $private_note ? 'private_note_btn' : '' );
                    $default_ship_add = get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_shipping_address', true );
                    $default_ship_add = ( $default_ship_add ? $default_ship_add : '' );
                    $download_pdf = get_post_meta( $remaning_order->supplier_id, 'oimwc_download_po_file', true );
                    $download_pdf = ( $po_default_setting_flag ? $download_pdf : '' );
                    $supplier_email = ( !empty(get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_order_email', true )) ? get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_order_email', true ) : get_post_meta( $remaning_order->supplier_id, 'oimwc_supplier_email', true ) );
                    $po_email_subject = get_option( 'oimwc_pdf_email_title' );
                    $po_email_subject = ( !empty($po_email_subject) ? $po_email_subject : __( 'Purchase Order PO from Order & inventory Manager', 'order-and-inventory-manager-for-woocommerce' ) );
                    $po_email_message_default = stripslashes( get_option( 'oimwc_pdf_email_message' ) );
                    $order_id = ( $remaning_order->order_number != '' ? $remaning_order->order_number : 0 );
                    include OIMWC_TEMPLATE . 'supplier_email.php';
                    $po_email_message = ( !empty($po_email_message_default) ? $po_email_message_default : $po_email_message );
                    $save_default_sett = get_post_meta( $remaning_order->supplier_id, 'oimwc_default_po_settings_flag', true );
                    $save_default_sett = ( !empty($save_default_sett) ? 1 : 0 );
                    $delivery_date = get_post_meta( $remaning_order->supplier_id, 'oimwc_delivery_date', true );
                    $delivery_date = ( $po_default_setting_flag ? $delivery_date : '' );
                    $shipping_method = get_post_meta( $remaning_order->supplier_id, 'oimwc_shipping_method', true );
                    $shipping_method = ( $po_default_setting_flag ? $shipping_method : '' );
                    $shipping_terms = get_post_meta( $remaning_order->supplier_id, 'oimwc_shipping_terms', true );
                    $shipping_terms = ( $po_default_setting_flag ? $shipping_terms : '' );
                    $po_attn = get_post_meta( $remaning_order->supplier_id, 'oimwc_po_attn', true );
                    $po_attn = ( $po_default_setting_flag ? $po_attn : '' );
                    $orderDate = str_replace( array( ' ', ':' ), '-', $remaning_order->order_date );
                    $attach_filename = $remaning_order->supplier_id . '_' . $orderDate;
                    $data[] = array(
                        'order_number'            => ( $order_number ? $order_number : 0 ),
                        'supplier_name'           => $supplier_name,
                        'order_date_time'         => $order_date_time,
                        'total_products'          => $total_products,
                        'total_purchase'          => $total_purchase,
                        'total_awaiting_products' => $total_awaiting_products,
                        'supplier_id'             => $supplier_id,
                        'order_date'              => strtotime( $order_date ),
                        'view_order_url'          => $view_order_url,
                        'default_cols'            => $default_cols,
                        'po_default_lang'         => $po_default_lang,
                        'additional_info'         => $additional_info,
                        'private_note'            => $private_note,
                        'order_type'              => $order_type,
                        'status'                  => $order_status[$supplier_id][$order_date],
                        'lock_title'              => $lock_title,
                        'cls'                     => $cls,
                        'style'                   => $style,
                        'disabled'                => $disabled,
                        'arrival_date'            => $arrival_date,
                        'add_info_cls'            => $add_info_cls,
                        'private_cls'             => $private_cls,
                        'goToOrder_txt'           => __( 'Go to order', 'order-and-inventory-manager-for-woocommerce' ),
                        'additional_info_txt'     => __( 'Additional Information in PO', 'order-and-inventory-manager-for-woocommerce' ),
                        'private_note_txt'        => __( 'Private Notes', 'order-and-inventory-manager-for-woocommerce' ),
                        'download_po_txt'         => __( 'Download PO-file', 'order-and-inventory-manager-for-woocommerce' ),
                        'est_arrival_txt'         => __( 'Estimated Arrival Date', 'order-and-inventory-manager-for-woocommerce' ),
                        'cancel_order_txt'        => __( 'Cancel Order', 'order-and-inventory-manager-for-woocommerce' ),
                        'disable_lock'            => $disable_lock,
                        'default_ship_add'        => $default_ship_add,
                        'download_pdf'            => $download_pdf,
                        'supplier_email'          => $supplier_email,
                        'po_email_subject'        => $po_email_subject,
                        'po_email_message'        => $po_email_message,
                        'save_default_sett'       => $save_default_sett,
                        'delivery_date'           => $delivery_date,
                        'shipping_method'         => $shipping_method,
                        'shipping_terms'          => $shipping_terms,
                        'po_attn'                 => $po_attn,
                        'attach_filename'         => $attach_filename,
                    );
                }
            }
            
            return $data;
        }
        
        function load_view_purchase_orders_products()
        {
            require_once OIMWC_CLASSES . 'class.view_order_product_list.php';
            $ViewOrderTable_obj = new ViewOrderTable();
            $data = $ViewOrderTable_obj->table_data();
            echo  json_encode( $data ) ;
            die;
        }
        
        /**
         * Closure
         * returns meta values of field
         *
         * @return string meta value of field
         * @param mixed $sql 
         * @since 1.0.0
         */
        public static function closure( $sql )
        {
            return str_replace( "'mt1.meta_value'", "mt1.meta_value", $sql );
        }
        
        /**
         * Total low stock products
         * Gets no of low stock products supplier wise
         *
         * @param Integer $supplier_id 
         * @since 1.0.0
         */
        public static function total_low_stock_products( $supplier_id )
        {
            global  $wpdb, $oimwc_data ;
            if ( is_array( $oimwc_data ) && isset( $oimwc_data[$supplier_id] ) ) {
                return $oimwc_data[$supplier_id];
            }
            $variable_products = wp_cache_get( 'oimwc_variable_products', 'oimwc_low_stock_products_cache' );
            
            if ( !$variable_products ) {
                $sql = 'SELECT DISTINCT(post_parent) FROM ' . $wpdb->posts . ' WHERE post_parent > 0 AND post_type = "product_variation"';
                $variable_products = $wpdb->get_col( $sql );
                wp_cache_add( 'oimwc_variable_products', $variable_products, 'oimwc_low_stock_products_cache' );
            }
            
            //$supplier_id = 0;
            
            if ( $supplier_id !== "all" ) {
                $meta_query = array(
                    'key'     => 'oimwc_supplier_id',
                    'compare' => '=',
                    'value'   => $supplier_id,
                    'type'    => 'NUMERIC',
                );
            } else {
                $meta_query = array(
                    'key'     => 'oimwc_supplier_id',
                    'compare' => 'EXISTS',
                );
            }
            
            $ordered_product = OIMWC_Order::get_ordered_product();
            
            if ( is_array( $ordered_product ) && count( $ordered_product ) ) {
                $post_not_in = array_merge( $variable_products, $ordered_product );
            } else {
                $post_not_in = $variable_products;
            }
            
            $post_not_in = array_unique( $post_not_in );
            $args = array(
                'post_type'    => array( 'product', 'product_variation' ),
                'meta_query'   => array(
                'relation' => 'AND',
                array(
                'key'     => '_stock',
                'compare' => '<=',
                'value'   => 'mt1.meta_value',
                'type'    => 'NUMERIC',
            ),
                array(
                'key'     => 'oimwc_low_stock_threshold_level',
                'compare' => 'EXISTS',
            ),
                array(
                'key'     => 'oimwc_show_in_low_stock',
                'compare' => '=',
                'value'   => "yes",
            ),
                array(
                'key'     => '_manage_stock',
                'compare' => '=',
                'value'   => 'yes',
            ),
                $meta_query,
            ),
                'post_status'  => array( 'private', 'publish' ),
                'post__not_in' => $post_not_in,
            );
            add_filter( 'posts_request', array( 'OIMWC_MAIN', 'closure' ) );
            $product_list = new WP_Query( $args );
            $found_post = $product_list->found_posts;
            remove_filter( 'posts_request', array( 'OIMWC_MAIN', 'closure' ) );
            wp_reset_postdata();
            $oimwc_data[$supplier_id] = $found_post;
            return $found_post;
        }
        
        /**
         * Settings page
         * Handles settings page fields and displays its values
         *
         * @since 1.0.0
         */
        function settings_page()
        {
            wp_enqueue_style( 'oimwc_style' );
            $units = get_option( 'oimwc_units' );
            
            if ( !$units ) {
                $units = array(
                    'piece' => 'pieces',
                );
                update_option( 'oimwc_units', $units );
            }
            
            $license_key = get_option( 'oimwc_verified_purchase_license_key' );
            $license_verified = get_option( 'oimwc_license_verified' );
            $default_unit = get_option( 'oimwc_default_unit' );
            $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
            $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
            $default_supplier_currency = get_option( 'oimwc_default_supplier_currency' );
            $show_settings = get_option( 'oimwc_show_default_pack_setting' );
            $show_one_pack_settings = get_option( 'oimwc_show_one_pack_settings' );
            $im_receiver = get_option( 'oimwc_receiver' );
            $im_address1 = get_option( 'oimwc_receiver_address1' );
            $im_address2 = get_option( 'oimwc_receiver_address2' );
            $im_city = get_option( 'oimwc_receiver_city' );
            $im_state = get_option( 'oimwc_receiver_state' );
            $im_zip_code = get_option( 'oimwc_receiver_zip_code' );
            $im_contact = get_option( 'oimwc_receiver_contact' );
            $im_country = get_option( 'oimwc_receiver_country' );
            $countries = oimwc_get_countries_list();
            $currencies = get_woocommerce_currencies();
            $oimwc_order_status_feature = get_option( 'oimwc_order_status_feature' );
            $selected_posttypes = get_option( 'oimwc_wpsearch_posttypes' );
            $enable_wpsearch = get_option( 'oimwc_enable_wpsearch', 0 );
            $enable_packsize_invoice = get_option( 'oimwc_enable_packsize_in_invoice' );
            $access_roles = get_option( 'oimwc_access_roles' );
            $enable_arrival_status = get_option( 'oimwc_enable_arrival_status' );
            $show_gtin_number = get_option( 'oimwc_show_gtin_number' );
            $selected_order_status = get_option( 'oimwc_selected_order_status' );
            $reduce_physical_stock_OStatus = get_option( 'oimwc_reduce_physical_stock_OStatus' );
            $shipping_address = get_option( 'oimwc_shipping_address' );
            $stock_log_limitation = get_option( 'stock_log_limitation', 6 );
            $disable_gtin_fields = get_option( 'disable_oimwc_gtin_fields' );
            $company_address = get_option( 'oimwc_company_address' );
            include OIMWC_TEMPLATE . 'oimwc_settings.php';
        }
        
        /**
         * Gets all variations
         * Gets all variation of a product,checks its pack size and sets message for each variation
         *
         * @since 1.0.0
         */
        function get_all_variation_json()
        {
            global  $product, $wpdb ;
        }
        
        /**
         * Display pack size message on single product pages
         *
         * @since 1.0.0
         */
        function display_pcs_per_product_in_single_page()
        {
        }
        
        function add_text_grouped_product( $value, $grouped_product_child )
        {
            global  $product ;
            
            if ( $product->is_type( 'simple' ) ) {
                ob_start();
                $this->display_pcs_per_product_in_single_page();
                $content = ob_get_contents();
                ob_clean();
            }
            
            $value .= '<div class="grp_prod_cls">' . $content . '</div>';
            return $value;
        }
        
        /**
         * Display pack size message on change of variations
         *
         * @since 1.0.0
         */
        function change_our_pack_size_on_variation_change()
        {
            global  $post ;
            
            if ( is_single( $post->ID ) ) {
                wp_enqueue_script( array( 'jquery' ) );
                ?>
                <script>
                    jQuery(document).ready(function () {
                        jQuery('.single_variation_wrap').on('show_variation', function (event, variation) {
                            var variation_id = jQuery('.variation_id').val();
                            var variation_data = jQuery('.our_pack_size').attr('data-product_variations');
                            if(jQuery('.our_pack_size').length && variation_data){
                                variation_data = JSON.parse(variation_data);
                                for(var i = 0; i < variation_data.length; i++) {
                                    if (variation_data[i].variation_id == variation_id) {
                                        if (jQuery('.our_pack_size').length) {
                                            jQuery('.our_pack_size').empty();
                                        }
                                        jQuery('.our_pack_size').append(variation_data[i].msg);
                                        if(jQuery('.arrival_date_panel').length){
                                            jQuery('.arrival_date_panel').html(variation_data[i].arrival_date);
                                        }
                                    }
                                }
                            }
                        });
                        if(jQuery('.our_pack_size').length){
                            jQuery('.variations select').change(function(){
                                    if( jQuery(this).val() == '' ){
                                        jQuery('.our_pack_size').html( jQuery('.our_pack_size').data('default-message') );
                                    }			
                            });
                        }
                        return;
                    });

                </script>
                <?php 
            }
        
        }
        
        /**
         * Display GTIN Number on front page for simple and variable product
         *
         * @since 1.0.0
         */
        function display_gtin_num_single_product()
        {
            global  $product, $wpdb ;
            $product_id = $product->get_id();
            $show_gtin_number = get_option( 'oimwc_show_gtin_number' );
            $disable_gtin_fields = get_option( 'disable_oimwc_gtin_fields' );
            if ( $disable_gtin_fields != 1 ) {
                
                if ( $show_gtin_number ) {
                    echo  '<span class="gtin_number_panel">' ;
                    
                    if ( $product->is_type( 'simple' ) ) {
                        $gtin_number = get_post_meta( $product_id, 'oimwc_gtin_num', true );
                        if ( $gtin_number ) {
                            printf( '<span class="gtin_txt">%s</span>: <span class="gtin_val">%s</span>', __( 'GTIN', 'order-and-inventory-manager-for-woocommerce' ), $gtin_number );
                        }
                    } else {
                        
                        if ( $product->is_type( 'variable' ) ) {
                            echo  '<span class="gtin_txt" style="display:none">' . __( 'GTIN', 'order-and-inventory-manager-for-woocommerce' ) . ':</span> <span class="gtin_val"></span>' ;
                            
                            if ( $product_id ) {
                                wp_enqueue_script( array( 'jquery' ) );
                                ?>
                        <script>
                            jQuery(document).ready(function () {
                                jQuery('.single_variation_wrap').on('show_variation', function (event, variation) {
                                    var variation_id = jQuery('.variation_id').val();
                                    var variation_data = jQuery('.variations_form').attr('data-product_variations');
                                    if(variation_data){
                                        variation_data = JSON.parse(variation_data);
                                        jQuery('.gtin_number_panel .gtin_val').html('');
                                        jQuery('.gtin_number_panel .gtin_txt').hide();
                                        for(var i = 0; i < variation_data.length; i++) {
                                            if (variation_data[i].variation_id == variation_id) {
                                                if( variation_data[i].gtin_num ){
                                                    jQuery('.gtin_number_panel .gtin_txt').show();
                                                    jQuery('.gtin_number_panel .gtin_val').html(variation_data[i].gtin_num);
                                                }
                                            }
                                        }
                                    }
                                });
                            });

                        </script>
                        <?php 
                            }
                        
                        }
                    
                    }
                    
                    echo  '</span>' ;
                }
            
            }
        }
        
        function load_gtin_num_variation( $variations )
        {
            $variations['gtin_num'] = get_post_meta( $variations['variation_id'], 'oimwc_gtin_num', true );
            return $variations;
        }
        
        /**
         * Saves inventory settings
         *
         * @since 1.0.0
         */
        function save_inventory_settings_callback()
        {
            
            if ( isset( $_POST['oimwc_settings_nonce_field'] ) || wp_verify_nonce( $_POST['oimwc_settings_nonce_field'], 'oimwc_settings_nonce' ) ) {
                $default_our_pack_size = ( isset( $_POST['default_our_pack_size'] ) ? sanitize_text_field( $_POST['default_our_pack_size'] ) : '' );
                $default_supplier_pack_size = ( isset( $_POST['default_our_pack_size'] ) ? sanitize_text_field( $_POST['default_supplier_pack_size'] ) : '' );
                
                if ( isset( $default_our_pack_size ) && $default_our_pack_size != "" && is_numeric( $default_our_pack_size ) && $default_our_pack_size > 0 ) {
                    update_option( 'oimwc_default_our_pack_size', $default_our_pack_size );
                } else {
                    update_option( 'oimwc_default_our_pack_size', 1 );
                }
                
                
                if ( isset( $default_supplier_pack_size ) && $default_supplier_pack_size != "" && is_numeric( $default_supplier_pack_size ) && $default_supplier_pack_size > 0 ) {
                    update_option( 'oimwc_default_supplier_pack_size', $default_supplier_pack_size );
                } else {
                    update_option( 'oimwc_default_supplier_pack_size', 1 );
                }
                
                
                if ( isset( $_POST['show_pack_settings'] ) ) {
                    update_option( 'oimwc_show_default_pack_setting', true );
                } else {
                    update_option( 'oimwc_show_default_pack_setting', false );
                }
                
                
                if ( isset( $_POST['show_one_pack_settings'] ) ) {
                    update_option( 'oimwc_show_one_pack_settings', true );
                } else {
                    update_option( 'oimwc_show_one_pack_settings', false );
                }
                
                if ( isset( $_POST['default_supplier_currency'] ) ) {
                    update_option( 'oimwc_default_supplier_currency', sanitize_text_field( $_POST['default_supplier_currency'] ) );
                }
                if ( isset( $_POST['im_units'] ) ) {
                    update_option( 'oimwc_default_unit', sanitize_text_field( $_POST['im_units'] ) );
                }
                /*if(isset($_POST['im_receiver'])){
                      update_option('oimwc_receiver',sanitize_text_field($_POST['im_receiver'] ));
                  }
                  if(isset($_POST['im_address1'])){
                      update_option('oimwc_receiver_address1', sanitize_text_field($_POST['im_address1']));
                  }
                  if(isset($_POST['im_address2'])){
                      update_option('oimwc_receiver_address2', sanitize_text_field($_POST['im_address2']));
                  }
                  if(isset($_POST['im_city'])){
                      update_option('oimwc_receiver_city',sanitize_text_field($_POST['im_city'] ));
                  }
                  if(isset($_POST['im_state'])){
                      update_option('oimwc_receiver_state',sanitize_text_field($_POST['im_state'] ));
                  }
                  if(isset($_POST['im_zip_code'])){
                      update_option('oimwc_receiver_zip_code', sanitize_text_field($_POST['im_zip_code']));
                  }
                  if(isset($_POST['im_contact'])){
                      update_option('oimwc_receiver_contact',sanitize_text_field($_POST['im_contact'] ));
                  }
                  if(isset($_POST['im_country'])){
                       update_option('oimwc_receiver_country', sanitize_text_field($_POST['im_country']));
                  }*/
                $shipping_add_arr = $_POST['shipping_address'];
                update_option( 'oimwc_shipping_address', $shipping_add_arr );
                
                if ( isset( $_POST['oimwc_order_status_feature'] ) ) {
                    update_option( 'oimwc_order_status_feature', "yes" );
                } else {
                    update_option( 'oimwc_order_status_feature', "no" );
                }
                
                $types = get_option( 'oimwc_wpsearch_posttypes' );
                if ( !is_array( $types ) ) {
                    $types = array();
                }
                $types = array();
                if ( isset( $_POST['search_types'] ) ) {
                    foreach ( $_POST['search_types'] as $key => $value ) {
                        $types[] = $value;
                    }
                }
                $types = array_unique( $types );
                $types = array_values( $types );
                update_option( 'oimwc_wpsearch_posttypes', $types );
                update_option( 'oimwc_enable_wpsearch', ( isset( $_POST['enable_admin_seachbar'] ) ? 1 : 0 ) );
                
                if ( isset( $_POST['all_posttypes'] ) ) {
                    update_option( 'oimwc_all_search_posttypes', $_POST['all_posttypes'] );
                    update_option( 'oimwc_last_searched_posttype', $types[0] );
                }
                
                $access_roles = $_POST['access_roles'];
                
                if ( isset( $access_roles ) ) {
                    update_option( 'oimwc_access_roles', $access_roles );
                } else {
                    update_option( 'oimwc_access_roles', array() );
                }
                
                
                if ( isset( $_POST['enable_packsize_in_invoice'] ) ) {
                    update_option( 'oimwc_enable_packsize_in_invoice', 'yes' );
                } else {
                    update_option( 'oimwc_enable_packsize_in_invoice', "no" );
                }
                
                
                if ( isset( $_POST['enable_arrival_status'] ) ) {
                    update_option( 'oimwc_enable_arrival_status', true );
                } else {
                    update_option( 'oimwc_enable_arrival_status', false );
                }
                
                update_option( 'oimwc_show_gtin_number', ( isset( $_POST['show_gtin_number'] ) ? 1 : 0 ) );
                update_option( 'stock_log_limitation', ( isset( $_POST['stock_log_limitation'] ) ? intval( $_POST['stock_log_limitation'] ) : 1 ) );
                $selected_order_status = ( isset( $_POST['custom_order_status_dd'] ) ? array_unique( $_POST['custom_order_status_dd'] ) : array() );
                update_option( 'oimwc_selected_order_status', $selected_order_status );
                $reduce_physical_stock_OStatus = ( isset( $_POST['reduce_physical_stock_OStatus'] ) ? array_unique( $_POST['reduce_physical_stock_OStatus'] ) : array() );
                update_option( 'oimwc_reduce_physical_stock_OStatus', $reduce_physical_stock_OStatus );
                $selected_access_users = ( isset( $_POST['access_user_list'] ) ? array_unique( $_POST['access_user_list'] ) : array() );
                update_option( 'oimwc_user_access_list', $selected_access_users );
                update_option( 'disable_oimwc_gtin_fields', $_POST['disable_oimwc_gtin'] );
                if ( $_POST['disable_oimwc_gtin'] == 1 ) {
                    
                    if ( $_POST['show_gtin_number'] == 1 ) {
                        update_option( 'oimwc_show_gtin_number', 0 );
                    } else {
                        update_option( 'oimwc_show_gtin_number', 1 );
                    }
                
                }
                
                if ( $_POST['pdf_logo'] != '' ) {
                    $pdf_logo = site_url() . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/' . basename( get_attached_file( $_POST['pdf_logo'] ) );
                    update_option( 'oimwc_pdf_logo', $_POST['pdf_logo'] );
                    update_option( 'oimwc_pdf_logo_path', $pdf_logo );
                } else {
                    update_option( 'oimwc_pdf_logo', '' );
                    update_option( 'oimwc_pdf_logo_path', '' );
                }
                
                
                if ( $_POST['pdf_color'] != '' ) {
                    update_option( 'oimwc_pdf_color', $_POST['pdf_color'] );
                } else {
                    update_option( 'oimwc_pdf_color', '#ffffff' );
                }
                
                
                if ( $_POST['pdf_title_color'] != '' ) {
                    update_option( 'oimwc_pdf_title_color', $_POST['pdf_title_color'] );
                } else {
                    update_option( 'oimwc_pdf_title_color', '#ffffff' );
                }
                
                
                if ( $_POST['pdf_email_title'] != '' ) {
                    update_option( 'oimwc_pdf_email_title', $_POST['pdf_email_title'] );
                } else {
                    update_option( 'oimwc_pdf_email_title', '#ffffff' );
                }
                
                
                if ( $_POST['pdf_email_title'] != '' ) {
                    update_option( 'oimwc_pdf_email_title', $_POST['pdf_email_title'] );
                } else {
                    update_option( 'oimwc_pdf_email_title', '' );
                }
                
                
                if ( $_POST['pdf_email_message'] != '' ) {
                    update_option( 'oimwc_pdf_email_message', stripslashes( $_POST['pdf_email_message'] ) );
                } else {
                    update_option( 'oimwc_pdf_email_message', '' );
                }
                
                
                if ( $_POST['pdf_email'] != '' ) {
                    update_option( 'oimwc_pdf_email', $_POST['pdf_email'] );
                } else {
                    update_option( 'oimwc_pdf_email', '' );
                }
                
                $company_add_arr = $_POST['company_address'];
                update_option( 'oimwc_company_address', $company_add_arr );
                $success = true;
                $msg = __( 'Saved', 'order-and-inventory-manager-for-woocommerce' );
            } else {
                $success = false;
                $msg = __( 'Something went wrong! Try again!', 'order-and-inventory-manager-for-woocommerce' );
            }
            
            echo  json_encode( array(
                'success' => $success,
                'msg'     => $msg,
            ) ) ;
            die;
        }
        
        /**
         * Save unit to meta
         * Saves array of units in options
         *
         * @since 1.0.0
         */
        function save_unit_to_meta()
        {
            $data = array();
            $units = get_option( 'oimwc_units' );
            if ( !is_array( $units ) ) {
                $units = array();
            }
            $singular_name = $_POST['singular'];
            $plural_name = $_POST['plural'];
            $newArr = array_combine( $singular_name, $plural_name );
            $default = array(
                "piece" => "pieces",
            );
            $units = array_merge( $default, $newArr );
            update_option( 'oimwc_units', $units );
            $data['msg'] = __( "Added to list", 'order-and-inventory-manager-for-woocommerce' );
            $data['units'] = $units;
            echo  json_encode( $data ) ;
            die;
        }
        
        /**
         * Deleted unit from options table
         *
         * @since 1.0.0
         */
        function delete_unit_from_meta()
        {
            $units = get_option( 'oimwc_units' );
            $unit_key = sanitize_text_field( $_POST['unit_key'] );
            
            if ( array_key_exists( $unit_key, $units ) && $unit_key != "piece" ) {
                unset( $units[$unit_key] );
                update_option( 'oimwc_units', $units );
                $data['msg'] = "Unit Deleted";
                echo  json_encode( $data ) ;
            } else {
                $data['msg'] = "Something went wrong";
                echo  json_encode( $data ) ;
            }
            
            die;
        }
        
        /**
         * Adds database updation notice when activating plugin for the first time
         *
         * @since 1.0.0
         */
        function add_database_updation_notice()
        {
            add_action( 'admin_notices', array( $this, 'display_database_updation_notice' ) );
        }
        
        /**
         * Displays database updation notice when activating plugin for the first time
         *
         * @since 1.0.0
         */
        function display_database_updation_notice()
        {
            $im_default_low_stock_data = get_option( 'oimwc_default_low_stock_data' );
            
            if ( !$im_default_low_stock_data ) {
                ?>
            <div class="notice notice-info">
                <p><strong><?php 
                _e( 'Order & Inventory Manager - Update new database meta!', 'order-and-inventory-manager-for-woocommerce' );
                ?></strong></p>
                <p><a href="javascript:void(0);" id="update_database" class="button button-primary"><?php 
                _e( 'Update meta', 'order-and-inventory-manager-for-woocommerce' );
                ?></a>
                </p>
            </div>
            <?php 
            }
        
        }
        
        /**
         * Gets products to update data
         * Gets all products which manages their stock to update default data to display products in low stock
         *
         * @since 1.0.0
         */
        function get_product_data_to_update()
        {
            $args = array(
                'post_type'      => array( 'product', 'product_variation' ),
                'post_status'    => array( 'publish', 'private' ),
                'meta_query'     => array(
                'relation' => 'AND',
                array(
                'key'     => '_manage_stock',
                'value'   => 'yes',
                'compare' => '=',
            ),
                array(
                'key'     => 'oimwc_supplier_id',
                'compare' => 'NOT EXISTS',
            ),
            ),
                'posts_per_page' => 1,
            );
            $product_list = new WP_Query( $args );
            $arr = array();
            $arr['found_post'] = $product_list->found_posts;
            if ( $product_list->found_posts == 0 ) {
                update_option( 'oimwc_default_low_stock_data', 1 );
            }
            echo  json_encode( $arr ) ;
            die;
        }
        
        /**
         * Adds default data to existing managed products
         *
         * @since 1.0.0
         */
        function add_default_data_to_existing_product()
        {
            $args = array(
                'post_type'      => array( 'product', 'product_variation' ),
                'post_status'    => array( 'publish', 'private' ),
                'meta_query'     => array(
                'relation' => 'AND',
                array(
                'key'     => '_manage_stock',
                'value'   => 'yes',
                'compare' => '=',
            ),
                array(
                'key'     => 'oimwc_supplier_id',
                'compare' => 'NOT EXISTS',
            ),
            ),
                'posts_per_page' => 10,
            );
            $product_list = new WP_Query( $args );
            $arr = array();
            $arr['found_post'] = $product_list->found_posts;
            if ( $product_list->have_posts() ) {
                while ( $product_list->have_posts() ) {
                    $product_list->the_post();
                    $id = get_the_ID();
                    update_post_meta( $id, 'oimwc_supplier_id', 0 );
                    update_post_meta( $id, 'oimwc_show_in_low_stock', "yes" );
                    update_post_meta( $id, 'oimwc_low_stock_threshold_level', 0 );
                }
            }
            if ( $product_list->found_posts == 0 ) {
                update_option( 'oimwc_default_low_stock_data', 1 );
            }
            echo  json_encode( $arr ) ;
            die;
        }
        
        /**
         * Adds stock values submenu and order status menu
         *
         * @since 1.0.0
         */
        function add_stock_values_menu()
        {
            return true;
        }
        
        /**
         * Displays stock values on stock value page
         *
         * @since 1.0.0
         */
        function display_stock_values()
        {
            wp_enqueue_style( 'oimwc_style' );
            require_once OIMWC_CLASSES . 'class.data_stock.php';
            $data = new DataStock();
            $data->prepare_items();
            include OIMWC_TEMPLATE . 'stock_values.php';
        }
        
        /**
         * Used to display the order status table
         *
         * @since 1.0.0
         */
        function order_status_table()
        {
            require_once OIMWC_CLASSES . 'class.order_status_list.php';
            include OIMWC_TEMPLATE . 'top_area.php';
            echo  '<div class="wrap order_overview_main">' ;
            echo  '<h2></h2>' ;
            echo  sprintf( '<div class="order_field_wrap"><p class="p_font">%s</h2>', __( 'Order Overview', 'order-and-inventory-manager-for-woocommerce' ) ) ;
            echo  '<div class="lw_spin"><img src="' . OIMWC_PLUGIN_URL . 'images/loader.gif' . '" /></div></div>' ;
            $obj = new order_status_list();
            $obj->prepare_items();
            $obj->display();
            echo  '</div>' ;
        }
        
        /**
         * Used to create new column in order listing page
         *
         * @param Array $columns 
         * @since 1.0.0
         */
        function wc_new_order_column( $columns )
        {
        }
        
        /**
         * Order status data
         * Used to generate physical stock, total ordered quantity array. 
         * Used to generate the data to display the order status for in stock, out of stock, without manage stock products and dependent orders that take products from other products
         * @since 1.0.0
         */
        function order_status_data()
        {
            global  $post, $wpdb, $product ;
            $product_stock = array();
            $selected_order_status = get_option( 'oimwc_selected_order_status' );
            
            if ( is_array( $selected_order_status ) && count( $selected_order_status ) > 0 ) {
                $selected_order_status = "('" . implode( "','", $selected_order_status ) . "')";
            } else {
                $selected_order_status = "('wc-processing','wc-on-hold')";
            }
            
            $order_data = 'SELECT DISTINCT WO.order_id,a.meta_value AS product_id,b.meta_value AS qty,a.order_item_id
						FROM ' . $wpdb->prefix . 'woocommerce_order_itemmeta AS a
						LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
						LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_items AS WO ON ( WO.order_item_id = b.order_item_id )
						LEFT JOIN ' . $wpdb->prefix . 'posts AS p ON ( p.ID = WO.order_id )
						WHERE a.meta_key = "_product_id" AND b.meta_key = "_qty"
						AND p.post_status IN ' . $selected_order_status . ' order by WO.order_id ASC';
            $result = $wpdb->get_results( $order_data, 'ARRAY_A' );
            foreach ( $result as $oid ) {
                $product_id = $oid['product_id'];
                $variation_id = wc_get_order_item_meta( $oid['order_item_id'], '_variation_id' );
                if ( $variation_id ) {
                    $product_id = $variation_id;
                }
                $stock = get_post_meta( $product_id, '_stock', true );
                $product_stock[$product_id] = $stock;
                
                if ( isset( $this->total_order_qty_arr[$product_id] ) ) {
                    $this->total_order_qty_arr[$product_id] = $this->total_order_qty_arr[$product_id] + $oid['qty'];
                } else {
                    $this->total_order_qty_arr[$product_id] = $oid['qty'];
                }
                
                $manage_stock = get_post_meta( $product_id, '_manage_stock', true );
                $_stock_status = '';
                
                if ( $manage_stock == 'no' ) {
                    $_stock_status = get_post_meta( $product_id, '_stock_status', true );
                    
                    if ( $_stock_status == 'instock' || $_stock_status == 'onbackorder' ) {
                        $this->manage_in_stock_arr[$product_id] = $oid['qty'];
                    } else {
                        $this->manage_out_stock_arr[$product_id] = $oid['qty'];
                    }
                
                }
                
                $this->arr[$oid['order_id']][] = array(
                    'order_item_id' => $oid['order_item_id'],
                    'qty'           => $oid['qty'],
                    'product_id'    => $product_id,
                    '_manage_stock' => $manage_stock,
                    '_stock_status' => $_stock_status,
                );
            }
            foreach ( $this->total_order_qty_arr as $key => $qty ) {
                $manage_stock = get_post_meta( $key, '_manage_stock', true );
                
                if ( $manage_stock == 'no' ) {
                    $_stock_status = get_post_meta( $key, '_stock_status', true );
                    
                    if ( $_stock_status == 'instock' || $_stock_status == 'outofstock' || $_stock_status == 'onbackorder' ) {
                        $this->physical_stock_arr[$key] = 0;
                        $physical_stock = 0;
                    }
                
                } else {
                    $stock = get_post_meta( $key, '_stock', true );
                    $this->physical_stock_arr[$key] = $qty + $stock;
                    $physical_stock = $qty + $stock;
                }
            
            }
            $this->actual_physical_stock = $this->physical_stock_arr;
            $order_status = array();
            foreach ( $this->arr as $key => $a_rr ) {
                $available_products = 0;
                $dependant_product = 0;
                $out_of_stock = 0;
                $manage_in_stock = 0;
                $manage_out_stock = 0;
                $total_order_products = count( $a_rr );
                foreach ( $a_rr as $prod_arr ) {
                    $product_id = $prod_arr['product_id'];
                    $stock = $product_stock[$product_id];
                    
                    if ( is_array( $this->manage_in_stock_arr ) && isset( $this->manage_in_stock_arr[$product_id] ) ) {
                        $manage_in_stock++;
                    } else {
                        
                        if ( is_array( $this->manage_out_stock_arr ) && isset( $this->manage_out_stock_arr[$product_id] ) ) {
                            $manage_out_stock++;
                        } else {
                            
                            if ( $prod_arr['qty'] <= $this->physical_stock_arr[$product_id] ) {
                                $available_products++;
                                $this->physical_stock_arr[$product_id] = $this->physical_stock_arr[$product_id] - $prod_arr['qty'];
                            } else {
                                
                                if ( $prod_arr['qty'] <= $this->actual_physical_stock[$product_id] ) {
                                    $out_of_stock++;
                                    $dependant_product++;
                                } else {
                                    $this->physical_stock_arr[$product_id] = $this->physical_stock_arr[$product_id] - $prod_arr['qty'];
                                    $out_of_stock++;
                                }
                            
                            }
                        
                        }
                    
                    }
                
                }
                /**** 24/12/2019 ****/
                $status = 0;
                $special_order_status = 0;
                $special_product = 0;
                if ( $available_products ) {
                    $status = 1;
                }
                if ( $out_of_stock ) {
                    $status = 2;
                }
                if ( $manage_in_stock || $manage_out_stock ) {
                    $special_product = 1;
                }
                if ( $dependant_product && $out_of_stock && $dependant_product == $out_of_stock ) {
                    $special_order_status = 1;
                }
                if ( $dependant_product && $out_of_stock && ($manage_in_stock || $manage_out_stock) && $dependant_product == $out_of_stock ) {
                    $special_order_status = 1;
                }
                /****** ******/
                $this->order_status[$key] = array(
                    'status'               => $status,
                    'special_order_status' => $special_order_status,
                    'special_product'      => $special_product,
                );
                /**
                 * Add order status and special order status
                 */
                update_post_meta( $key, 'order_status', $status );
                update_post_meta( $key, 'special_order_status', $special_order_status );
                update_post_meta( $key, 'special_product_status', $special_product );
            }
        }
        
        /**
         * Used to display order status icons in the custom column
         * 
         * @param Array $column 
         * @since 1.0.0
         */
        function wc_order_extra_columns_content( $column )
        {
        }
        
        /**
         * Stores per page records value
         *
         * @since 1.0.0
         */
        function save_per_page()
        {
            if ( isset( $_POST['save_per_page'] ) ) {
                update_option( 'oimwc_per_page', sanitize_text_field( $_POST['items_per_page'] ) );
            }
        }
        
        /**
         * Add filter to order page
         *
         * @since 1.0.0
         */
        function filter_orders_by_product_availability()
        {
        }
        
        /**
         * Add query arg to default query for order listing page
         *
         * @since 1.0.0
         */
        function filter_orders_by_product_availability_query( $vars )
        {
        }
        
        /**
         * Add supplier filter to product page
         *
         * @since 1.0.0
         */
        function filter_products_by_supplier()
        {
            global  $typenow ;
            if ( 'product' === $typenow ) {
                require_once OIMWC_TEMPLATE . 'supplier_product_filter.php';
            }
        }
        
        /**
         * Add query arg to default query for supplier filter in products page
         *
         * @since 1.0.0
         */
        function filter_products_by_supplier_query( $vars )
        {
            global  $typenow ;
            if ( 'product' === $typenow && isset( $_GET['filter_supplier'] ) && sanitize_text_field( $_GET['filter_supplier'] ) > 0 ) {
                $vars['meta_query'] = array( array(
                    'key'     => 'oimwc_supplier_id',
                    'value'   => sanitize_text_field( $_GET['filter_supplier'] ),
                    'compare' => '=',
                ) );
            }
            return $vars;
        }
        
        /**
         * Display missing product and where to get it from.
         *
         * @since 1.0.0
         */
        function admin_order_preview_get_order_details( $data, $order )
        {
        }
        
        /**
         * Get items to display in the preview as HTML.
         *
         * @param  WC_Order $order Order object.
         * @return string
         */
        function get_order_preview_item_html( $order )
        {
            $hidden_order_itemmeta = apply_filters( 'woocommerce_hidden_order_itemmeta', array(
                '_qty',
                '_tax_class',
                '_product_id',
                '_variation_id',
                '_line_subtotal',
                '_line_subtotal_tax',
                '_line_total',
                '_line_tax',
                'method_id',
                'cost'
            ) );
            $order_id = $order->get_id();
            $order_status = get_post_meta( $order_id, 'order_status', true );
            $special_order_status = get_post_meta( $order_id, 'special_order_status', true );
            $line_items = apply_filters( 'woocommerce_admin_order_preview_line_items', $order->get_items(), $order );
            $columns = apply_filters( 'woocommerce_admin_order_preview_line_item_columns', array(
                'product'  => __( 'Product', 'woocommerce' ),
                'quantity' => __( 'Quantity', 'woocommerce' ),
                'tax'      => __( 'Tax', 'woocommerce' ),
                'total'    => __( 'Total', 'woocommerce' ),
            ), $order );
            if ( !wc_tax_enabled() ) {
                unset( $columns['tax'] );
            }
            $html = '
			<div class="wc-order-preview-table-wrapper">
				<table cellspacing="0" class="wc-order-preview-table">
					<thead>
						<tr>';
            foreach ( $columns as $column => $label ) {
                $html .= '<th class="wc-order-preview-table__column--' . esc_attr( $column ) . '">' . esc_html( $label ) . '</th>';
            }
            $html .= '
						</tr>
					</thead>
					<tbody>';
            foreach ( $line_items as $item_id => $item ) {
                $product_object = ( is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : null );
                $row_class = apply_filters(
                    'woocommerce_admin_html_order_preview_item_class',
                    '',
                    $item,
                    $order
                );
                $html .= '<tr class="wc-order-preview-table__item wc-order-preview-table__item--' . esc_attr( $item_id ) . (( $row_class ? ' ' . esc_attr( $row_class ) : '' )) . '">';
                foreach ( $columns as $column => $label ) {
                    $html .= '<td class="wc-order-preview-table__column--' . esc_attr( $column ) . '">';
                    switch ( $column ) {
                        case 'product':
                            $display = '';
                            $product_id = $item->get_product_id();
                            $variation_id = $item->get_variation_id();
                            
                            if ( in_array( $order_status, [ 2 ] ) || in_array( $special_order_status, [ 1 ] ) ) {
                                $missing_qty_arr = $this->get_total_ordered_products( $product_id, 'simple' );
                                $missing_qty = $missing_qty_arr[$order_id];
                                if ( $missing_qty > 0 ) {
                                    $display .= '<span class="missing_pipe"> | </span>' . $missing_qty . 'x ' . __( 'missing', 'order-and-inventory-manager-for-woocommerce' ) . ' ';
                                }
                                
                                if ( $variation_id ) {
                                    $missing_qty_arr = $this->get_total_ordered_products( $variation_id, 'variable' );
                                    $missing_qty = $missing_qty_arr[$order_id];
                                    if ( $missing_qty > 0 ) {
                                        $display .= '<span class="missing_pipe"> | </span>' . $missing_qty . 'x ' . __( 'missing', 'order-and-inventory-manager-for-woocommerce' ) . ' ';
                                    }
                                }
                            
                            }
                            
                            if ( $variation_id ) {
                                $product_id = $variation_id;
                            }
                            //$manage_stock = get_post_meta( $product_id, '_manage_stock', true );
                            //if( $manage_stock == 'yes' ){
                            //$_stock_status = get_post_meta( $product_id, '_stock_status', true );
                            $supplier_sku = get_post_meta( $product_id, 'oimwc_supplier_product_id', true );
                            $supplier_url = get_post_meta( $product_id, 'oimwc_supplier_product_url', true );
                            
                            if ( $supplier_sku || $supplier_url ) {
                                $display .= '<p class="order_preview_p">';
                                if ( $supplier_sku ) {
                                    $display .= '<span class="supplier_span">' . __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' ) . ': ' . $supplier_sku . '</span>';
                                }
                                
                                if ( $supplier_url ) {
                                    if ( $supplier_sku ) {
                                        $display .= '<span class="missing_pipe"> | </span>';
                                    }
                                    $display .= '<span class="supplier_span"> <a href="' . $supplier_url . '"> ' . __( 'Supplier URL', 'order-and-inventory-manager-for-woocommerce' ) . '</a></span>';
                                }
                                
                                $display .= '</p>';
                            }
                            
                            //}
                            $html .= wp_kses_post( $item->get_name() );
                            $html .= '<div class="wc-custom-order-preview">';
                            if ( $product_object ) {
                                $html .= '<div class="wc-order-item-sku">' . esc_html( $product_object->get_sku() ) . '</div>';
                            }
                            if ( $display ) {
                                $html .= '<div class="missing_prod_qty">' . $display . '</div>';
                            }
                            $html .= '</div>';
                            $meta_data = $item->get_formatted_meta_data( '' );
                            
                            if ( $meta_data ) {
                                $html .= '<table cellspacing="0" class="wc-order-item-meta">';
                                foreach ( $meta_data as $meta_id => $meta ) {
                                    if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
                                        continue;
                                    }
                                    $html .= '<tr><th>' . wp_kses_post( $meta->display_key ) . ':</th><td>' . wp_kses_post( force_balance_tags( $meta->display_value ) ) . '</td></tr>';
                                }
                                $html .= '</table>';
                            }
                            
                            break;
                        case 'quantity':
                            $html .= esc_html( $item->get_quantity() );
                            break;
                        case 'tax':
                            $html .= wc_price( $item->get_total_tax(), array(
                                'currency' => $order->get_currency(),
                            ) );
                            break;
                        case 'total':
                            $html .= wc_price( $item->get_total(), array(
                                'currency' => $order->get_currency(),
                            ) );
                            break;
                        default:
                            $html .= apply_filters(
                                'woocommerce_admin_order_preview_line_item_column_' . sanitize_key( $column ),
                                '',
                                $item,
                                $item_id,
                                $order
                            );
                            break;
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '
					</tbody>
				</table>
			</div>';
            return $html;
        }
        
        function get_total_ordered_products( $product_id, $type )
        {
            global  $wpdb ;
            $order_status = get_option( 'oimwc_selected_order_status' );
            
            if ( is_array( $order_status ) && count( $order_status ) > 0 ) {
                $order_status = "('" . implode( "','", $order_status ) . "')";
            } else {
                $order_status = "('wc-processing','wc-on-hold')";
            }
            
            $type = ( $type == 'simple' ? '_product_id' : '_variation_id' );
            $sql = 'SELECT DISTINCT WO.order_id,b.meta_value AS ordered_qty
                FROM ' . $wpdb->prefix . 'woocommerce_order_itemmeta AS a
                LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
                LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_items AS WO ON ( WO.order_item_id = b.order_item_id )
                LEFT JOIN ' . $wpdb->prefix . 'posts AS p ON ( p.ID = WO.order_id )
                WHERE (a.meta_key = "' . $type . '" AND a.meta_value = "' . $product_id . '")
                AND b.meta_key = "_qty"
                AND p.post_status IN ' . $order_status . ' order by WO.order_id DESC';
            $data = $wpdb->get_results( $sql );
            $arr = [];
            $stock = get_post_meta( $product_id, '_stock', true );
            $stock_x = abs( $stock );
            if ( is_array( $data ) && count( $data ) > 0 ) {
                foreach ( $data as $key => $value ) {
                    
                    if ( $stock < 0 ) {
                        $order_val = $value->ordered_qty;
                        
                        if ( $stock_x >= $order_val ) {
                            $arr[$value->order_id] = $value->ordered_qty;
                        } else {
                            $arr[$value->order_id] = $stock_x;
                        }
                        
                        $stock_x = $stock_x - $value->ordered_qty;
                    } else {
                        $arr[$value->order_id] = 0;
                    }
                
                }
            }
            return $arr;
        }
        
        function add_custom_data_order_detail_page( $item_id, $item, $_product )
        {
        }
        
        function add_discontinued_tab_product_list( $views )
        {
        }
        
        function get_discontinued_products( $query )
        {
        }
        
        function discontinued_posts_where( $where )
        {
        }
        
        /**** End discontinued products ( Discontinued Product )****/
        function add_pack_size_to_product_print_receipt( $product_name, $item )
        {
        }
        
        function lock_product_callback()
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $lock_product = sanitize_text_field( $_POST['lock_product'] );
            $supplier_id = sanitize_text_field( $_POST['supplier_id'] );
            $order_date = sanitize_text_field( $_POST['order_date'] );
            $order_date = date( 'Y-m-d H:i:s', $order_date );
            $wpdb->update(
                $table_name,
                array(
                'lock_product' => $lock_product,
            ),
                array(
                'supplier_id' => $supplier_id,
                'order_date'  => $order_date,
            ),
                array( '%d' ),
                array( '%d', '%s' )
            );
            die;
        }
        
        function convert_to_data_format( $date )
        {
            $format = get_option( 'date_format' );
            switch ( $format ) {
                case 'm-d-Y':
                    list( $m, $d, $y ) = explode( '-', $date );
                    break;
                case 'd-m-Y':
                    list( $d, $m, $y ) = explode( '-', $date );
                    break;
                case 'm/d/Y':
                    list( $m, $d, $y ) = explode( '/', $date );
                    break;
                case 'd/m/Y':
                    list( $d, $m, $y ) = explode( '/', $date );
                    break;
                case 'Y-m-d':
                    list( $y, $m, $d ) = explode( '-', $date );
                    break;
                default:
                    return date( 'Y-m-d', strtotime( $date ) );
                    break;
            }
            return "{$y}-{$m}-{$d}";
        }
        
        function save_arrival_date_callback()
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $arrival_date = sanitize_text_field( $_POST['arrival_date'] );
            $supplier_id = sanitize_text_field( $_POST['supplier_id'] );
            $order_date = sanitize_text_field( $_POST['order_date'] );
            $order_date = date( 'Y-m-d H:i:s', $order_date );
            $arrival_date = $this->convert_to_data_format( $arrival_date );
            $wpdb->update(
                $table_name,
                array(
                'arrival_date' => $arrival_date,
            ),
                array(
                'supplier_id' => $supplier_id,
                'order_date'  => $order_date,
            ),
                array( '%s' ),
                array( '%d', '%s' )
            );
            die;
        }
        
        function cancel_awaiting_order_callback()
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $supplier_id = sanitize_text_field( $_POST['supplier_id'] );
            $order_date = sanitize_text_field( $_POST['order_date'] );
            $order_date = date( 'Y-m-d H:i:s', $order_date );
            $flag = 0;
            
            if ( $supplier_id && $order_date ) {
                $flag = $wpdb->delete( $table_name, array(
                    'supplier_id' => $supplier_id,
                    'order_date'  => $order_date,
                ), array( '%d', '%s' ) );
                $product_id = $wpdb->get_var( "SELECT product_id FROM {$table_name} WHERE supplier_id = '{$supplier_id}' AND order_date = '{$order_date}'" );
                $supplier_lowstock_count = oimwc_all_supplier_low_stock_count( $product_id );
            }
            
            echo  json_encode( array(
                'flag'                    => $flag,
                'redirect_url'            => admin_url() . 'admin.php?page=order-inventory-management&subpage=purchase-orders&tab=active_orders',
                'supplier_lowstock_count' => $supplier_lowstock_count,
            ) ) ;
            die;
        }
        
        function add_info_po_callback()
        {
            global  $wpdb ;
            $additional_info = sanitize_text_field( $_POST['additional_info'] );
            $supplier_id = sanitize_text_field( $_POST['supplier_id'] );
            $order_date = sanitize_text_field( $_POST['order_date'] );
            $order_date = date( 'Y-m-d H:i:s', $order_date );
            $wpdb->update(
                $wpdb->prefix . 'order_inventory',
                array(
                'additional_information' => $additional_info,
            ),
                array(
                'supplier_id' => $supplier_id,
                'order_date'  => $order_date,
            ),
                array( '%s' ),
                array( '%d', '%s' )
            );
            echo  json_encode( array(
                'content' => $additional_info,
            ) ) ;
            die;
        }
        
        function add_private_note_callback()
        {
            global  $wpdb ;
            $private_note = sanitize_text_field( $_POST['private_note'] );
            $supplier_id = sanitize_text_field( $_POST['supplier_id'] );
            $order_date = sanitize_text_field( $_POST['order_date'] );
            $order_date = date( 'Y-m-d H:i:s', $order_date );
            $wpdb->update(
                $wpdb->prefix . 'order_inventory',
                array(
                'private_note' => $private_note,
            ),
                array(
                'supplier_id' => $supplier_id,
                'order_date'  => $order_date,
            ),
                array( '%s' ),
                array( '%d', '%s' )
            );
            if ( !empty($private_note) ) {
                echo  json_encode( array(
                    'content' => $private_note,
                ) ) ;
            }
            die;
        }
        
        function view_order_product_listing()
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $supplier_id = sanitize_text_field( $_GET['supplier'] );
            $supplier_name = get_the_title( $supplier_id );
            $order_date = date( 'Y-m-d H:i:s', sanitize_text_field( $_GET['date'] ) );
            require_once OIMWC_CLASSES . 'class.view_order_product_list.php';
            $obj = new ViewOrderTable();
            $obj->prepare_items();
            include OIMWC_TEMPLATE . 'view_purchase_order.php';
            include OIMWC_TEMPLATE . 'info_modal.php';
            echo  '<div class="oimwc-table-shadow view_order_product_listing_panel" data-pagination="">' ;
            $obj->display();
            echo  '</div>' ;
            echo  '<div class="view_po_spin lw_spin"><img src="' . OIMWC_PLUGIN_URL . 'images/loader.gif" /></div>' ;
            echo  '</div>' ;
            echo  '</div>' ;
        }
        
        function get_purchase_order_status( $supplier_id, $order_date, $localize = true )
        {
            global  $wpdb, $oimwc_data ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $status = '';
            if ( isset( $oimwc_data[$supplier_id] ) && isset( $oimwc_data[$supplier_id][$order_date] ) ) {
                return $oimwc_data[$supplier_id][$order_date];
            }
            $data = $this->check_finalize_product( $supplier_id, $order_date );
            $finalize_product_status = $data['finalize_product_status'];
            $arrived_product_status = $data['arrived_product_status'];
            $status_qry = $wpdb->get_var( "SELECT completed_order from {$table_name} where supplier_id = {$supplier_id} and order_date = '{$order_date}'" );
            
            if ( ($finalize_product_status || $arrived_product_status) && $status_qry != 1 ) {
                
                if ( $localize ) {
                    $status = __( 'Receiving', 'order-and-inventory-manager-for-woocommerce' );
                } else {
                    $status = __( 'Receiving', 'order-and-inventory-manager-for-woocommerce' );
                }
            
            } else {
                
                if ( $status_qry == 0 ) {
                    
                    if ( $localize ) {
                        $status = __( 'Pending', 'order-and-inventory-manager-for-woocommerce' );
                    } else {
                        $status = __( 'Pending', 'order-and-inventory-manager-for-woocommerce' );
                    }
                
                } else {
                    if ( $status_qry == 1 ) {
                        
                        if ( $localize ) {
                            $status = __( 'Completed', 'order-and-inventory-manager-for-woocommerce' );
                        } else {
                            $status = __( 'Completed', 'order-and-inventory-manager-for-woocommerce' );
                        }
                    
                    }
                }
            
            }
            
            $oimwc_data[$supplier_id][$order_date] = $status;
            return $status;
        }
        
        function check_finalize_product( $supplier_id, $order_date )
        {
            global  $wpdb ;
            $table_name = $wpdb->prefix . 'order_inventory';
            $flag = false;
            $flag1 = false;
            $finalize_products = $wpdb->get_results( "SELECT finalize_product,arrvived_stock from {$table_name} where supplier_id = {$supplier_id} and order_date = '{$order_date}'", ARRAY_A );
            if ( is_array( $finalize_products ) && count( $finalize_products ) > 0 ) {
                foreach ( $finalize_products as $key => $value ) {
                    if ( $value['finalize_product'] > 0 ) {
                        $flag = true;
                    }
                    if ( $value['arrvived_stock'] > 0 ) {
                        $flag1 = true;
                    }
                }
            }
            return array(
                'finalize_product_status' => $flag,
                'arrived_product_status'  => $flag1,
            );
        }
        
        function remove_prod_frm_purchase_ordr()
        {
            global  $wpdb ;
            $tablename = $wpdb->prefix . 'order_inventory';
            $id = sanitize_text_field( $_POST['id'] );
            $product_id = sanitize_text_field( $_POST['product_id'] );
            $sql = $wpdb->query( 'DELETE FROM ' . $tablename . ' where id = ' . $id );
            
            if ( $sql ) {
                $supplier_count = oimwc_all_supplier_low_stock_count( $product_id );
                echo  json_encode( array(
                    'success'        => 1,
                    'id'             => $id,
                    'supplier_count' => $supplier_count,
                ) ) ;
            }
            
            die;
        }
        
        function warning_neg_physical_stock()
        {
        }
        
        function submit_contact_form()
        {
            global  $wp_filter ;
            $formdata = $_POST['formdata'];
            parse_str( $formdata, $data );
            /*** unset these filters from global hooks. Because it conflicts with 'WooCommerce Order Status & Actions Manager' plugin ***/
            unset( $wp_filter['woocommerce_email_from_address'] );
            unset( $wp_filter['woocommerce_email_attachments'] );
            unset( $wp_filter['woocommerce_email_from_name'] );
            $license_key = $this->get_user_fs_license_key();
            $license_key = ( !empty($license_key) ? $license_key : '-' );
            $data['license_key'] = $license_key;
            $subject = 'New Inquiry';
            $message = $this->oimwc_contact_form_email_content( $data );
            $to = 'support@wphydracode.com';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: ' . sanitize_text_field( $data['name'] ) . ' <' . sanitize_email( $data['email'] ) . '>';
            $mailer = wp_mail(
                $to,
                $subject,
                $message,
                $headers
            );
            
            if ( $mailer ) {
                echo  json_encode( array(
                    'sent' => 1,
                ) ) ;
            } else {
                echo  json_encode( array(
                    'sent' => 0,
                ) ) ;
            }
            
            die;
        }
        
        function oimwc_contact_form_email_content( $data )
        {
            ob_start();
            include OIMWC_TEMPLATE . 'email_content.php';
            $content = ob_get_contents();
            ob_clean();
            return $content;
        }
        
        /**
         * Get user freemius license key from options table
         **/
        function get_user_fs_license_key()
        {
            global  $wpdb ;
            $secret_key = '';
            $option_table = $wpdb->prefix . 'options';
            $data = $wpdb->get_var( "SELECT option_value FROM {$option_table} WHERE option_name = 'fs_accounts'" );
            
            if ( $data ) {
                $data = unserialize( $data );
                $sites = $data['sites'];
                if ( is_array( $sites ) && count( $sites ) > 0 ) {
                    $license_id = $sites['order-inventory-management']->license_id;
                }
                $all_licenses = $data['all_licenses'][3922];
                if ( is_array( $all_licenses ) && count( $all_licenses ) > 0 ) {
                    foreach ( $all_licenses as $key => $value ) {
                        if ( $value->id == $license_id ) {
                            $secret_key = $value->secret_key;
                        }
                    }
                }
            }
            
            return $secret_key;
        }
        
        function get_arrival_date( $product_id )
        {
            global  $wpdb ;
            $arrival_date = $wpdb->get_var( "select arrival_date from " . $wpdb->prefix . "order_inventory where product_id = '" . $product_id . "'  and arrival_date  > curdate() and finalize_product < 1 LIMIT 1" );
            if ( $arrival_date ) {
                $arrival_date = date_i18n( get_option( 'date_format' ), strtotime( $arrival_date ) );
            }
            return $arrival_date;
        }
        
        function add_arrival_date_product_page()
        {
        }
        
        function add_estimated_status_in_out_stock( $availability, $_product )
        {
            return $availability;
        }
        
        function get_access_users_callback()
        {
            global  $wpdb ;
            $titles = array();
            $data = array();
            $tablename = $wpdb->prefix . 'users';
            $access_user_list = get_option( 'oimwc_user_access_list' );
            $access_user_list = ( is_array( $access_user_list ) && count( $access_user_list ) > 0 ? $access_user_list : array() );
            $keyword = '*' . sanitize_text_field( $_POST['keyword'] ) . '*';
            $users_args = array(
                'role__not_in'   => array( 'administrator' ),
                'fields'         => 'ids',
                'search'         => $keyword,
                'search_columns' => array(
                'user_login',
                'user_email',
                'user_nicename',
                'display_name'
            ),
                'exclude'        => $access_user_list,
            );
            $users = get_users( $users_args );
            if ( is_array( $users ) && count( $users ) > 0 ) {
                foreach ( $users as $key => $user_id ) {
                    $user = get_user_by( 'id', $user_id );
                    $titles['label'] = $user->display_name . ' (' . $user->user_email . ')';
                    $titles['value'] = $user->ID;
                    $data[] = $titles;
                }
            }
            echo  json_encode( $data ) ;
            die;
        }
        
        /** Add reminder notice to rate plugin after 2 weeks of use **/
        public function oimwc_plugin_rating_notice()
        {
            $plugin_settings = get_option( 'oimwc_rating_settings' );
            
            if ( !empty($plugin_settings['installed_on']) ) {
                $ignore_rating = ( empty($plugin_settings['ignore_rating']) ? "" : $plugin_settings['ignore_rating'] );
                
                if ( $ignore_rating != "yes" ) {
                    $difference_date = abs( strtotime( date( "Y/m/d" ) ) - strtotime( $plugin_settings['installed_on'] ) );
                    $days = floor( $difference_date / (60 * 60 * 24) );
                    if ( $days >= 14 ) {
                        require_once OIMWC_TEMPLATE . 'notice_for_rating_reminder.php';
                    }
                }
            
            }
        
        }
        
        public function oimwc_plugin_rating_ignore_notice()
        {
            $plugin_settings = get_option( 'oimwc_rating_settings' );
            
            if ( empty($plugin_settings['ignore_rating']) ) {
                $plugin_settings['ignore_rating'] = "yes";
                update_option( 'oimwc_rating_settings', $plugin_settings );
            }
            
            die;
        }
        
        /* Add popup with order list contains product with out of stock as well as discontinued.*/
        function discontinued_product_message()
        {
            global  $wpdb ;
            $output = '';
            $count = 1;
            $selected_order_status = get_option( 'oimwc_selected_order_status' );
            
            if ( is_array( $selected_order_status ) && count( $selected_order_status ) > 0 ) {
                $selected_order_status = "('" . implode( "','", $selected_order_status ) . "')";
            } else {
                $selected_order_status = "('wc-processing','wc-on-hold')";
            }
            
            $ordered_products = wp_cache_get( 'oimwc_ordered_products', 'oimwc_discontinued_products_cache' );
            
            if ( !$ordered_products ) {
                $query = 'SELECT A.post_id FROM ' . $wpdb->prefix . 'postmeta AS A  
                WHERE A.meta_key = "oimwc_discontinued_product" AND A.meta_value = "yes"';
                $post_ids = $wpdb->get_col( $query );
                $query = 'SELECT A.post_id FROM ' . $wpdb->prefix . 'postmeta AS A 
                    WHERE A.meta_key = "_stock_status" AND A.meta_value = "outofstock" AND A.post_id IN ( ' . implode( ',', $post_ids ) . ' )';
                $post_ids = $wpdb->get_col( $query );
                $post_ids = implode( ',', $post_ids );
                $order_data = 'SELECT DISTINCT WO.order_id, a.meta_value as product_id
                        FROM ' . $wpdb->prefix . 'woocommerce_order_itemmeta AS a
                        LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_items AS WO ON ( WO.order_item_id = a.order_item_id )
                        LEFT JOIN ' . $wpdb->prefix . 'posts AS p ON ( p.ID = WO.order_id )
                        WHERE a.meta_value IN (' . $post_ids . ') AND p.post_status IN ' . $selected_order_status . ' order by WO.order_id DESC';
                $ordered_products = $wpdb->get_results( $order_data, ARRAY_A );
                wp_cache_add( 'oimwc_ordered_products', $ordered_products, 'oimwc_discontinued_products_cache' );
            }
            
            
            if ( is_array( $ordered_products ) && !empty($ordered_products) ) {
                include_once OIMWC_TEMPLATE . 'discontinued_product_notice.php';
                ?>
                <div id="view_orders" style="display:none;">
                    <div class="incorrect_order_panel">
                <?php 
                foreach ( $ordered_products as $key => $value ) {
                    $order_id = $value['order_id'];
                    
                    if ( get_post_type( $value['product_id'] ) == 'product_variation' ) {
                        $product = new WC_Product_Variation( $value['product_id'] );
                        $product_id = wp_get_post_parent_id( $value['product_id'] );
                        $sku = get_post_meta( $value['product_id'], '_sku', true );
                        $productName = get_the_title( $product_id );
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
                    
                    } else {
                        $product_id = $value['product_id'];
                        $product = new WC_Product( $product_id );
                        $productName = get_the_title( $product_id );
                        $sku = get_post_meta( $product_id, '_sku', true );
                        $product_variant = '';
                    }
                    
                    ?>
                    <div class="order_list">
                        <?php 
                    echo  $count . ') ' ;
                    ?>
                        <a href="<?php 
                    echo  site_url() . '/wp-admin/post.php?post=' . $order_id . '&action=edit' ;
                    ?>" class="product_title"><?php 
                    echo  '#' . $order_id ;
                    ?></a><br />
                        <?php 
                    echo  sprintf( __( ' Issue: Contain a product that is out of stock and discontinued.', 'order-and-inventory-manager-for-woocommerce' ) ) ;
                    ?><br />
                        <?php 
                    echo  sprintf( __( 'Product Name', 'order-and-inventory-manager-for-woocommerce' ) ) . ': ' ;
                    ?>
                        <a class="product_title" href="<?php 
                    echo  site_url() . '/wp-admin/post.php?post=' . $product_id . '&action=edit' ;
                    ?>"><?php 
                    echo  $productName ;
                    ?></a><br />
                        <?php 
                    if ( $product_variant != '' ) {
                        echo  sprintf( __( 'Variant', 'order-and-inventory-manager-for-woocommerce' ) ) . ': ' . $product_variant . '<br />' ;
                    }
                    if ( $sku != '' ) {
                        echo  sprintf( __( 'Product SKU', 'order-and-inventory-manager-for-woocommerce' ) ) . ': ' . $sku ;
                    }
                    ?>

                    </div><br />
                   <?php 
                    $count++;
                }
                ?>
                </div></div>
                <?php 
                echo  $output ;
            }
        
        }
        
        /* Add new product through import and update product stock */
        function product_import_new_product( $post_id, $xml_node, $is_update )
        {
            global  $wpdb, $OIMProductStock, $OIMWC_import_keys ;
            $table_name = $wpdb->prefix . 'oim_product_stock';
            $product_id = $post_id;
            $product_obj_id = $product_id;
            $variation_id = 0;
            $exist_column = [];
            $parent_id = wp_get_post_parent_id( $post_id );
            $supplier_id = 0;
            $stock = '';
            $physical_stock = 0;
            $units_in_stock = 0;
            $shop_pack_size = '';
            
            if ( $parent_id ) {
                $product_id = $parent_id;
                $variation_id = $post_id;
                $product_obj_id = $variation_id;
            }
            
            $stock = get_post_meta( $product_obj_id, '_stock', true );
            $physical_stock = get_post_meta( $product_obj_id, 'oimwc_physical_stock', true );
            $units_in_stock = get_post_meta( $product_obj_id, 'oimwc_physical_units_stock', true );
            $shop_pack_size = get_post_meta( $product_obj_id, 'oimwc_our_pack_size', true );
            $product_stock = $OIMProductStock->get_product_stock_detail( array(
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
            ) );
            if ( is_array( $product_stock ) && count( $product_stock ) ) {
                if ( $shop_pack_size === '' ) {
                    $shop_pack_size = $product_stock['shop_pack_size'];
                }
            }
            
            if ( !$shop_pack_size ) {
                $shop_pack_size = get_post_meta( $product_obj_id, 'oimwc_our_pack_size', true );
                if ( !$shop_pack_size && $variation_id ) {
                    $shop_pack_size = get_post_meta( $product_id, 'oimwc_our_pack_size', true );
                }
            }
            
            $shop_pack_size = ( $shop_pack_size ? $shop_pack_size : 1 );
            $existing_field = '';
            $import = 1;
            $new_product = true;
            
            if ( is_array( $product_stock ) && count( $product_stock ) ) {
                $new_product = false;
                
                if ( $units_in_stock != $product_stock['stock_in_units'] ) {
                    $existing_field = 'units_in_stock';
                } else {
                    
                    if ( $physical_stock != $product_stock['physical_stock'] ) {
                        $existing_field = 'physical_stock';
                    } else {
                        
                        if ( $stock != $product_stock['stock'] ) {
                            $existing_field = 'stock';
                        } else {
                            if ( $shop_pack_size != $product_stock['shop_pack_size'] ) {
                                $existing_field = 'shop_pack_size';
                            }
                        }
                    
                    }
                
                }
            
            }
            
            
            if ( $existing_field || $new_product ) {
                $flag = $OIMProductStock->update_product(
                    $product_id,
                    $variation_id,
                    $stock,
                    $physical_stock,
                    $units_in_stock,
                    $shop_pack_size,
                    $import,
                    $existing_field
                );
                oimwc_supplier_low_stock_count( 0, $supplier_id );
                oimwc_show_all_product_stock_count(
                    $supplier_id,
                    false,
                    '=',
                    true
                );
            }
        
        }
        
        /* Send PO order mail & PO order file attachment to supplier */
        function send_po_order_email()
        {
            $user_info = get_userdata( 1 );
            $from_email = ( !empty($_POST['reply_to_order_email']) ? $_POST['reply_to_order_email'] : get_option( 'oimwc_pdf_email' ) );
            $supplier_email = get_post_meta( $_POST['supplier_id'], 'oimwc_supplier_email', true );
            $supplier_email = ( !empty($_POST['send_po_order_email']) ? $_POST['send_po_order_email'] : $supplier_email );
            
            if ( strpos( $supplier_email, ' | ' ) !== false ) {
                $email = explode( '|', $supplier_email );
                $supplier_email = $email[0];
            }
            
            $to = $supplier_email;
            $subject = sanitize_text_field( $_POST['po_order_subject'] );
            $message = stripslashes( nl2br( $_POST['po_order_message'] ) );
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            $headers .= 'From: ' . $user_info->user_login . '<' . $from_email . '>' . "\r\n" . 'Reply-To: ' . $from_email . "\r\n" . 'X-Mailer: PHP/' . phpversion();
            $dir = wp_upload_dir()['basedir'] . "/download_document/";
            
            if ( count( glob( "{$dir}/*" ) ) > 0 ) {
                foreach ( glob( $dir . '*.*' ) as $file ) {
                    $attachment = $file;
                }
                $mailer = wp_mail(
                    $to,
                    $subject,
                    $message,
                    $headers,
                    $attachment
                );
                
                if ( $mailer ) {
                    echo  json_encode( array(
                        'sent'           => 1,
                        'supplier_email' => $supplier_email,
                        'message'        => __( 'The order was sent successfully.', 'order-and-inventory-manager-for-woocommerce' ),
                    ) ) ;
                } else {
                    echo  json_encode( array(
                        'sent'           => 0,
                        'supplier_email' => $supplier_email,
                        'message'        => __( 'Something went wrong. Please try again later.', 'order-and-inventory-manager-for-woocommerce' ),
                    ) ) ;
                }
                
                if ( count( glob( "{$dir}/*" ) ) ) {
                    foreach ( glob( $dir . '*.*' ) as $file ) {
                        $attachment = $file;
                        unlink( $attachment );
                    }
                }
            } else {
                echo  json_encode( array(
                    'sent'    => 'no',
                    'message' => 'Attachment Missing. Please try again later.',
                ) ) ;
            }
            
            die;
        }
        
        /* Save PO attach file in temporary folder for PO supplier mail */
        function download_po_document()
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
                'supplier'         => $_POST['supplier_id'],
                'date'             => $_POST['order_date'],
                'po_lang'          => $_POST['manage_po_lang_dd'],
                'download_po_file' => $_POST['download_po_file'],
                'save_po_file'     => 'yes',
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
        
        function purchase_order_table_data()
        {
            global  $wpdb ;
            $tablename = $wpdb->prefix . 'oimwc_temp_product';
            $ids = $_POST['product_id'];
            $supplier_id = $_POST['supplier_id'];
            $requested_stock = $_POST['qty'];
            $purchase_order_data = get_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', true );
            
            if ( empty($purchase_order_data) ) {
                $product_ids = [
                    $ids => $requested_stock,
                ];
                update_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', $product_ids );
            } else {
                $product_ids = [
                    $ids => $requested_stock,
                ];
                $request_data = $purchase_order_data + $product_ids;
                update_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', $request_data );
            }
            
            $sql = $wpdb->query( 'UPDATE ' . $tablename . ' set product_qty = ' . $requested_stock . ' WHERE id = ' . $ids );
            wp_die();
        }
        
        function remove_purchase_order_product()
        {
            global  $wpdb ;
            $tablename = $wpdb->prefix . 'oimwc_temp_product';
            $id = $_POST['id'];
            $supplier_id = $_POST['supplier_id'];
            $request_data = get_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', true );
            foreach ( $request_data as $key => $value ) {
                
                if ( $key == $id ) {
                    unset( $request_data[$id] );
                } else {
                    update_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', '' );
                }
            
            }
            update_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', $request_data );
            $sql = $wpdb->query( 'UPDATE ' . $tablename . ' set product_qty = 0 WHERE id = ' . $id );
            wp_die();
        }
        
        /**
         * Add temporary product from popup 
         **/
        function add_temporary_product()
        {
            global  $wpdb ;
            $supplier_id = sanitize_text_field( $_POST['supplier_id'] );
            $product_name = sanitize_text_field( $_POST['tmp_product_name'] );
            $product_variant_name = sanitize_text_field( $_POST['tmp_variant_name'] );
            $oimwc_supplier_product_id = sanitize_text_field( $_POST['tmp_product_id'] );
            $oimwc_supplier_product_url = sanitize_text_field( $_POST['tmp_product_url'] );
            $oimwc_supplier_product_notes = sanitize_text_field( $_POST['tmp_product_notes'] );
            $oimwc_product_qty = ( !empty($_POST['tmp_product_qty']) ? sanitize_text_field( $_POST['tmp_product_qty'] ) : 0 );
            $oimwc_supplier_pack_size = sanitize_text_field( $_POST['tmp_pack_size'] );
            $oimwc_supplier_pack_size = ( !empty($oimwc_supplier_pack_size) ? $oimwc_supplier_pack_size : 1 );
            $price = str_replace( ',', '.', sanitize_text_field( $_POST['tmp_product_price'] ) );
            $purchase_currency = get_post_meta( $supplier_id, 'oimwc_supplier_currency', true );
            $product_price = ( isset( $price ) && $price == '' && $price === FALSE ? '-' : $price );
            $table_name = $wpdb->prefix . 'oimwc_temp_product';
            
            if ( $supplier_id != '' ) {
                $data = array(
                    'supplier_id'         => $supplier_id,
                    'product_name'        => $product_name,
                    'variation_name'      => $product_variant_name,
                    'supplier_product_id' => $oimwc_supplier_product_id,
                    'product_url'         => $oimwc_supplier_product_url,
                    'supplier_notes'      => $oimwc_supplier_product_notes,
                    'product_qty'         => $oimwc_product_qty,
                    'product_price'       => $product_price,
                    'supplier_pack_size'  => $oimwc_supplier_pack_size,
                );
                $sql = $wpdb->prepare(
                    "INSERT INTO " . $table_name . " (supplier_id, product_name, variation_name, supplier_product_id, product_url, supplier_notes, product_qty, product_price, order_id, supplier_pack_size ) VALUES ( %d, %s, %s, %s, %s, %s, %d, %s, %d, %d )",
                    $supplier_id,
                    $product_name,
                    $product_variant_name,
                    $oimwc_supplier_product_id,
                    $oimwc_supplier_product_url,
                    $oimwc_supplier_product_notes,
                    $oimwc_product_qty,
                    $product_price,
                    0,
                    $oimwc_supplier_pack_size
                );
                $wpdb->query( $sql );
                $product_id = $wpdb->insert_id;
            }
            
            $product_class = ( $product_variant_name ? '' : 'simple' );
            $image = '<img width="40" height="40" src="' . plugins_url( '/woocommerce/assets/images/placeholder.png' ) . '" />' . sprintf(
                '<input type="hidden" class="productId product_%s" name="productId" value="%s" /><div class="mobile_prod_info"><div>%s</div><div class="%s">%s</div></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>',
                $product_id,
                $product_id,
                $product_name,
                $product_class . '_product',
                $product_variant_name
            );
            $post = get_post( $product_id );
            $product_type = $post->post_type;
            $product_title = ( !empty($product_name) ? $product_name : '' );
            
            if ( $product_variant_name != '' ) {
                $product_variant = $product_variant_name;
            } else {
                $product_variant = '';
            }
            
            $variant = ( isset( $product_variant ) && $product_variant == '' && $product_variant === FALSE ? '' : $product_variant );
            $variant = ( $variant && $variant != '' ? sprintf( '<div>%s: %s</div>', __( 'Variant', 'order-and-inventory-manager-for-woocommerce' ), $variant ) : '' );
            $oimwc_supplier_product_id = ( $oimwc_supplier_product_id != '' ? $oimwc_supplier_product_id : '' );
            $warning_level = 0;
            $product_info = sprintf( '<div><div>%1$s</div>%2$s</div>', $product_title, $variant );
            $supplier_name = oiwmc_get_supplier_with_link( $supplier_id );
            $url_text = ( $oimwc_supplier_product_url ? '<a href=' . $oimwc_supplier_product_url . ' target="_blank">' . __( 'Link to product', 'order-and-inventory-manager-for-woocommerce' ) . '</a>' : '' );
            $purchase_price = wc_price( $product_price, array(
                'currency' => $purchase_currency,
            ) );
            $div_text = '';
            
            if ( $url_text ) {
                $url_txt = __( 'URL', 'order-and-inventory-manager-for-woocommerce' );
                $supplier_url_text = sprintf( '<div>%s: %s</div>', $url_txt, $url_text );
            }
            
            
            if ( $oimwc_supplier_product_id ) {
                $oimwc_supplier_product_id_txt = __( 'Supplier Product ID', 'order-and-inventory-manager-for-woocommerce' );
                $supplier_product_id_txt = sprintf( '<div>%s: %s</div>', $oimwc_supplier_product_id_txt, $oimwc_supplier_product_id );
            }
            
            
            if ( $oimwc_supplier_product_notes ) {
                $oimwc_supplier_product_notes_txt = __( 'Product Notes', 'order-and-inventory-manager-for-woocommerce' );
                $supplier_product_notes_txt = sprintf( '<div>%s: %s</div>', $oimwc_supplier_product_notes_txt, $oimwc_supplier_product_notes );
            }
            
            $supplier_info = sprintf(
                '<div>%1$s<div>%2$s: %3$s</div>%4$s%5$s%6$s</div>',
                $supplier_url_text,
                __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ),
                $purchase_price,
                $div_text,
                $supplier_product_id_txt,
                $supplier_product_notes_txt
            );
            $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
            $our_pack_size = ( $default_our_pack_size ? $default_our_pack_size : 1 );
            $total_pieces = 0;
            $product_stock = 0;
            $div_text = '';
            $warning_level = get_post_meta( $product_id, 'oimwc_low_stock_threshold_level', true );
            $product_detail = '';
            $product_price = wc_format_decimal( $oimwc_product_qty * $price, 2 );
            $qty_column = '<div class="" data-tip=""><input type="text" class="arrived_qty_handler" data-stock="' . $oimwc_product_qty . '" data-warning="' . $warning_level . '" data-id="' . $product_id . '" name="product[' . $product_id . '][qty]" value="' . $oimwc_product_qty . '"></div>';
            $qty_column .= '<input type="hidden" name="product[' . $product_id . '][stock]" value="' . $oimwc_product_qty . '"><input type="hidden" name="product[' . $product_id . '][supplier]" value="' . $supplier_id . '">';
            $qty_column .= '<div class="product_calc"><span data-price="' . $price . '" class="amount amount_' . $product_id . '">' . $product_price . ' </span><span class="currency">' . $purchase_currency . '</span></div>';
            $qty_column .= '<div class="" data-tip=""><input type="button" class="button btnAddItemToOrder" value="' . __( 'Add to order', 'order-and-inventory-manager-for-woocommerce' ) . '"></div>';
            $qty_column .= '<div class="temp_product"><input type="button" class="button btnRemoveProduct" data-id="' . $product_id . '" value="' . __( 'Remove', 'order-and-inventory-manager-for-woocommerce' ) . '" /></div>';
            $content = '<tr class="temp_background_color">
                            <td class="thumb column-thumb has-row-actions column-primary" data-colname="Image">%s</td>
                            <td class="product_info column-product_info" data-colname="Product Info">%s</td>
                            <td class="supplier_info column-supplier_info" data-colname="Supplier Info">%s</td>
                            <td class="product_detail column-product_detail" data-colname="Product Price &amp; Stock">%s</td>
                            <td class="amount column-amount" data-colname="Qty">%s</td>
                        </tr>
                        <tr>
                            <td colspan="5"><div class="table_seperator"></div></td>
                        </tr>';
            $message['message'] = __( 'The product was successfully added.', 'order-and-inventory-manager-for-woocommerce' );
            $message['product_id'] = $product_id;
            $message['data'] = sprintf(
                $content,
                $image,
                $product_info,
                $supplier_info,
                $product_detail,
                $qty_column
            );
            echo  json_encode( $message ) ;
            die;
        }
        
        function remove_temporary_product()
        {
            global  $wpdb ;
            $id = $_POST['id'];
            $supplier_id = $_POST['supplier_id'];
            $tablename = $wpdb->prefix . 'oimwc_temp_product';
            $sql = $wpdb->query( 'DELETE FROM ' . $tablename . ' where id = ' . $id );
            if ( $sql ) {
                echo  json_encode( array(
                    'success' => true,
                ) ) ;
            }
            $table = $wpdb->prefix . 'order_inventory';
            $sql_query = $wpdb->query( 'DELETE FROM ' . $table . ' where product_id = ' . $id );
            $request_data = get_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', true );
            foreach ( $request_data as $key => $value ) {
                
                if ( $key == $id ) {
                    unset( $request_data[$id] );
                } else {
                    update_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', '' );
                }
            
            }
            update_post_meta( $supplier_id, 'oimwc_supplier_purchase_order_data', $request_data );
            die;
        }
    
    }
    OIMWC_MAIN::init();
}
