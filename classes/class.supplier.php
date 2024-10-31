<?php

/**
 * OIMWC_Supplier class
 *
 * Saves inventory settings of products and variations
 *
 * @since    1.0.0
 */

if ( !class_exists( 'OIMWC_Supplier' ) ) {
    /**
     * OIMWC_Supplier Saves inventory settings of products and variations
     *
     * @since    1.0.0
     */
    class OIMWC_Supplier
    {
        /**
         * Private variables.
         *
         * @since 1.0.0
         * @var integer $product_id stores product's id.
         */
        private  $product_id ;
        /**
         * Setup class.
         *
         * @since 1.0.0
         */
        function __construct()
        {
            add_filter( 'woocommerce_product_data_tabs', array( $this, 'supplier_data_tabs' ) );
            add_action( 'woocommerce_product_data_panels', array( $this, 'supplier_data_panel' ) );
            add_action(
                'woocommerce_product_after_variable_attributes',
                array( $this, 'supplier_info_baseon_varible' ),
                10,
                3
            );
            add_action( 'save_post', array( $this, 'save_product_supplier' ) );
            add_action(
                'woocommerce_save_product_variation',
                array( $this, 'save_supplier_variation' ),
                10,
                2
            );
            add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
            add_action( 'woocommerce_before_variations_form', array( $this, 'add_discontinued_txt_disabled_variants' ) );
            add_action( 'wp_ajax_custom_search_supplier', array( $this, 'custom_search_supplier' ) );
            add_action( 'wp_ajax_custom_sort_supplier', array( $this, 'custom_sort_supplier' ) );
            add_filter( 'post_row_actions', array( $this, 'remove_supplier_quick_edit' ) );
            add_action( 'woocommerce_thankyou', array( $this, 'check_low_stock_order_completed' ) );
            add_action( 'woocommerce_order_status_cancelled', array( $this, 'check_low_stock_order_completed' ) );
            add_action( 'woocommerce_order_status_failed', array( $this, 'check_low_stock_order_completed' ) );
            add_action( 'wp_ajax_save_additional_supplier', array( $this, 'save_additional_supplier' ) );
            add_action( 'pmxi_before_post_import', array( $this, 'remove_save_hook_on_import' ) );
            add_action( 'wp_ajax_save_supplier_info', array( $this, 'save_supplier_info' ) );
            add_action( 'wp_ajax_update_oimwc_data', array( $this, 'update_oimwc_data' ) );
            add_action( 'wp_ajax_update_oimwc_data_variable', array( $this, 'update_oimwc_data_variable' ) );
            add_action( 'wp_ajax_get_products_list_with_sku', array( $this, 'get_products_list_with_sku' ) );
        }
        
        /**
         * Remove save hooks on WP ALL Import
         *
         * @since 1.2.8
         */
        function remove_save_hook_on_import()
        {
            remove_action( 'save_post', array( $this, 'save_product_supplier' ) );
            remove_action(
                'woocommerce_save_product_variation',
                array( $this, 'save_supplier_variation' ),
                10,
                2
            );
        }
        
        /**
         * Enter title Here
         * Adds placeholder to the title of new supplier post.
         *
         * @param string $title stores placeholder title
         * @return string placeholder title.
         * @since 1.0.0
         */
        function enter_title_here( $title )
        {
            $screen = get_current_screen();
            if ( $screen->post_type == 'supplier' ) {
                $title = __( 'Enter full company name', 'order-and-inventory-manager-for-woocommerce' );
            }
            return $title;
        }
        
        /**
         * Supplier data panel
         * Adds tab to supplier data panel
         *
         * @param array $tabs stores tab details
         * @return array of supplier tab
         * @since 1.0.0
         */
        function supplier_data_tabs( $tabs )
        {
            wp_enqueue_style( 'oimwc_style' );
            $tabs['supplier_tab'] = array(
                'label'    => __( 'Order & Inventory Manager', 'order-and-inventory-manager-for-woocommerce' ),
                'target'   => 'suppliers_data_panel',
                'class'    => array( 'show_if_simple', 'show_if_variable' ),
                'priority' => 80,
            );
            return $tabs;
        }
        
        /**
         * Supplier data panel
         * Fetches all stored inventory product values, includes the view of inventory product panel and display stored values.
         *
         * @since 1.0.0
         */
        function supplier_data_panel()
        {
            global  $post, $woocommerce ;
            $supplier_id = get_post_meta( $post->ID, 'oimwc_supplier_id', true );
            if ( !$supplier_id ) {
                //$supplier_id = get_post_meta($post->post_parent, 'oimwc_supplier_id', true);
            }
            $supplier_art_id = get_post_meta( $post->ID, 'oimwc_supplier_product_id', true );
            $supplier_product_url = get_post_meta( $post->ID, 'oimwc_supplier_product_url', true );
            $supplier_note = get_post_meta( $post->ID, 'oimwc_supplier_note', true );
            $supplier_currency = get_post_meta( $supplier_id, 'oimwc_supplier_currency', true );
            $default_currency = get_option( 'oimwc_default_supplier_currency' );
            $our_pack_size = get_post_meta( $post->ID, 'oimwc_our_pack_size', true );
            $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
            $our_pack = ( $our_pack_size ? $our_pack_size : (( $default_our_pack_size ? $default_our_pack_size : 1 )) );
            $supplier_pack_size = get_post_meta( $post->ID, 'oimwc_supplier_pack_size', true );
            $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
            $supplier_pack = ( $supplier_pack_size ? $supplier_pack_size : (( $default_supplier_pack_size ? $default_supplier_pack_size : 1 )) );
            $display_pack_size = get_post_meta( $post->ID, 'oimwc_manual_pack_size_setting', true );
            $units = get_option( 'oimwc_units' );
            $selected_unit = get_post_meta( $post->ID, 'oimwc_supplier_unit', true );
            $default_unit = get_option( 'oimwc_default_unit' );
            $unit = ( $selected_unit ? $selected_unit : (( $default_unit ? $default_unit : "piece" )) );
            if ( is_array( $units ) ) {
                
                if ( !array_key_exists( $selected_unit, $units ) ) {
                    
                    if ( $default_unit && array_key_exists( $default_unit, $units ) ) {
                        $unit = $default_unit;
                    } else {
                        $unit = "piece";
                    }
                
                } else {
                    $unit = $selected_unit;
                }
            
            }
            $supplier_show_in_low_stock = get_post_meta( $post->ID, 'oimwc_show_in_low_stock', true );
            
            if ( $supplier_show_in_low_stock == "yes" ) {
                $supplier_show_in_low_stock = "yes";
            } else {
                
                if ( $supplier_show_in_low_stock == "no" ) {
                    $supplier_show_in_low_stock = "no";
                } else {
                    $supplier_show_in_low_stock = "yes";
                }
            
            }
            
            $current_stock = (int) get_post_meta( $post->ID, '_stock', true );
            $supplier_remaining_pieces = (int) get_post_meta( $post->ID, 'oimwc_supplier_remaining_pieces', true );
            update_post_meta( $post->ID, 'oimwc_previous_stock', sanitize_text_field( $current_stock ) );
            update_post_meta( $post->ID, 'oimwc_previous_pack_size', sanitize_text_field( $our_pack ) );
            //$total_pieces = floor( ($current_stock * $our_pack) + $supplier_remaining_pieces);
            $physical_stock = get_post_meta( $post->ID, 'oimwc_physical_stock', true );
            
            if ( $physical_stock != '' ) {
                $prevoius_total_pieces = floor( $physical_stock * $our_pack );
                update_post_meta( $post->ID, 'oimwc_previous_total_pieces', sanitize_text_field( $prevoius_total_pieces ) );
            }
            
            $total_pieces = get_post_meta( $post->ID, 'oimwc_physical_units_stock', true );
            if ( $total_pieces != '' ) {
                //$total_pieces = $total_pieces + $supplier_remaining_pieces;
            }
            $purchase_price = get_post_meta( $post->ID, 'oimwc_supplier_purchase_price', true );
            $discontinued_product = get_post_meta( $post->ID, 'oimwc_discontinued_product', true );
            $manage_stock = get_post_meta( $post->ID, '_manage_stock', true );
            $all_discontinued = get_post_meta( $post->ID, 'oimwc_all_discontinued_products', true );
            $gtin_num = get_post_meta( $post->ID, 'oimwc_gtin_num', true );
            $discontinued_replacement_product = get_post_meta( $post->ID, 'oimwc_discontinued_replacement_product', true );
            $oimwc_discontinued_replacement_title = get_post_meta( $post->ID, 'oimwc_discontinued_replacement_title', true );
            ob_start();
            include_once OIMWC_TEMPLATE . 'product_supplier.php';
            $content = ob_get_contents();
            ob_clean();
            
            if ( $manage_stock == 'no' ) {
                $tooltip = __( "Please enable 'Stock management' to unlock the fields.", 'order-and-inventory-manager-for-woocommerce' );
                $content = str_replace( '<p', '<p data-msg="' . $tooltip . '"', $content );
            }
            
            echo  $content ;
        }
        
        /**
         * Supplier info based in variable
         * Fetches all stored inventory variation data, includes the view of variation panel and display stored values.
         *
         * @since 1.0.0
         * @param int     $loop
         * @param array   $variation_data
         * @param WP_Post $variation
         */
        function supplier_info_baseon_varible( $loop, $variation_data, $variation )
        {
            global  $post, $woocommerce ;
            $this->product_id = $variation->ID;
            $this->parent = $variation->post_parent;
            $supplier_id = get_post_meta( $variation->ID, 'oimwc_supplier_id', true );
            if ( !$supplier_id ) {
                //$supplier_id = get_post_meta($variation->post_parent, 'oimwc_supplier_id', true);
            }
            $supplier_art_id = get_post_meta( $variation->ID, 'oimwc_supplier_product_id', true );
            $purchase_price = get_post_meta( $variation->ID, 'oimwc_supplier_purchase_price', true );
            $supplier_product_url = get_post_meta( $variation->ID, 'oimwc_supplier_product_url', true );
            $supplier_note = get_post_meta( $variation->ID, 'oimwc_supplier_note', true );
            $supplier_currency = get_post_meta( $supplier_id, 'oimwc_supplier_currency', true );
            $default_currency = get_option( 'oimwc_default_supplier_currency' );
            $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
            $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
            $parent_supplier_pack_size = get_post_meta( $variation->post_parent, 'oimwc_supplier_pack_size', true );
            $parent_our_pack_size = get_post_meta( $variation->post_parent, 'oimwc_our_pack_size', true );
            $our_pack = ( $this->get_data( 'oimwc_our_pack_size' ) ? $this->get_data( 'oimwc_our_pack_size' ) : (( $parent_our_pack_size ? $parent_our_pack_size : (( $default_our_pack_size ? $default_our_pack_size : 1 )) )) );
            $supplier_pack = ( $this->get_data( 'oimwc_supplier_pack_size' ) ? $this->get_data( 'oimwc_supplier_pack_size' ) : (( $parent_supplier_pack_size ? $parent_supplier_pack_size : (( $default_supplier_pack_size ? $default_supplier_pack_size : 1 )) )) );
            $units = get_option( 'oimwc_units' );
            $parent_unit = get_post_meta( $variation->post_parent, 'oimwc_supplier_unit', true );
            $default_unit = get_option( 'oimwc_default_unit' );
            $unit = ( $this->get_data( 'oimwc_supplier_unit' ) ? $this->get_data( 'oimwc_supplier_unit' ) : (( $parent_unit ? $parent_unit : (( $default_unit ? $default_unit : "piece" )) )) );
            $check_unit = $this->get_data( 'oimwc_supplier_unit' );
            
            if ( $check_unit && !array_key_exists( $check_unit, $units ) ) {
                
                if ( !array_key_exists( $parent_unit, $units ) ) {
                    
                    if ( $default_unit && array_key_exists( $default_unit, $units ) ) {
                        $unit = $default_unit;
                    } else {
                        $unit = "piece";
                    }
                
                } else {
                    $unit = $parent_unit;
                }
            
            } else {
                $unit = $this->get_data( 'oimwc_supplier_unit' );
            }
            
            $parent_show_in_low_stock = get_post_meta( $variation->post_parent, 'oimwc_show_in_low_stock', true );
            
            if ( $this->get_data( 'oimwc_show_in_low_stock' ) ) {
                
                if ( $this->get_data( 'oimwc_show_in_low_stock' ) == "yes" ) {
                    $supplier_show_in_low_stock = "yes";
                } else {
                    if ( $this->get_data( 'oimwc_show_in_low_stock' ) == "no" ) {
                        $supplier_show_in_low_stock = "no";
                    }
                }
            
            } else {
                
                if ( $parent_show_in_low_stock ) {
                    $supplier_show_in_low_stock = $parent_show_in_low_stock;
                } else {
                    $supplier_show_in_low_stock = "yes";
                }
            
            }
            
            $current_stock = (int) get_post_meta( $variation->ID, '_stock', true );
            $supplier_remaining_pieces = get_post_meta( $variation->ID, 'oimwc_supplier_remaining_pieces', true );
            update_post_meta( $variation->ID, 'oimwc_previous_stock', sanitize_text_field( $current_stock ) );
            update_post_meta( $variation->ID, 'oimwc_previous_pack_size', sanitize_text_field( $our_pack ) );
            $physical_stock = get_post_meta( $variation->ID, 'oimwc_physical_stock', true );
            $physical_stock = ( !empty($physical_stock) ? $physical_stock : 0 );
            $prevoius_total_pieces = floor( $physical_stock * $our_pack );
            update_post_meta( $variation->ID, 'oimwc_previous_total_pieces', $prevoius_total_pieces );
            $total_pieces = get_post_meta( $variation->ID, 'oimwc_physical_units_stock', true );
            $discontinued_product = get_post_meta( $variation->ID, 'oimwc_discontinued_product', true );
            $manage_stock = get_post_meta( $variation->ID, '_manage_stock', true );
            $gtin_num = get_post_meta( $variation->ID, 'oimwc_gtin_num', true );
            $physical_stock = get_post_meta( $variation->ID, 'oimwc_physical_stock', true );
            $discontinued_replacement_product = get_post_meta( $variation->ID, 'oimwc_discontinued_replacement_product', true );
            $oimwc_discontinued_replacement_title = get_post_meta( $variation->ID, 'oimwc_discontinued_replacement_title', true );
            ob_start();
            include OIMWC_TEMPLATE . 'variable_supplier.php';
            $content = ob_get_contents();
            ob_clean();
            
            if ( $manage_stock == 'no' ) {
                $tooltip = __( "Please enable 'Stock management' to unlock the fields.", 'order-and-inventory-manager-for-woocommerce' );
                $content = str_replace( '<p', '<p data-msg="' . $tooltip . '"', $content );
            }
            
            echo  $content ;
        }
        
        /**
         * Save product supplier
         * Saves inventory setting of a product.
         *
         * @since 1.0.0
         * @param integer $product_id stores product's id
         */
        function save_product_supplier( $product_id )
        {
            global  $wpdb, $OIMProductStock ;
            if ( wp_is_post_revision( $product_id ) ) {
                return;
            }
            $post_type = get_post_type( $product_id );
            remove_action( 'save_post', array( $this, 'save_product_supplier' ) );
            // If this isn't a 'supplier' post, don't update it.
            if ( !in_array( $post_type, array( 'product', 'product_variation' ) ) ) {
                return;
            }
            if ( !isset( $_POST['oimwc_supplier_id'] ) ) {
                //return;
            }
            $product_obj = wc_get_product( $product_id );
            $oimwc_physical_stock = get_post_meta( $product_id, 'oimwc_physical_stock', true );
            $previous_stock = get_post_meta( $product_id, 'oimwc_previous_stock', true );
            $previous_pack_size = get_post_meta( $product_id, 'oimwc_previous_pack_size', true );
            $previous_total_pieces = get_post_meta( $product_id, 'oimwc_previous_total_pieces', true );
            $new_stock = ( isset( $_POST['_stock'] ) ? sanitize_text_field( $_POST['_stock'] ) : 0 );
            $new_our_pack_size = ( isset( $_POST['oimwc_our_pack_size'] ) ? sanitize_text_field( $_POST['oimwc_our_pack_size'] ) : 1 );
            $new_total_pieces = ( isset( $_POST['oimwc_physical_units_stock'] ) ? sanitize_text_field( $_POST['oimwc_physical_units_stock'] ) : 0 );
            $oimwc_gtin_num = ( isset( $_POST['oimwc_gtin_num'] ) ? sanitize_text_field( $_POST['oimwc_gtin_num'] ) : '' );
            $oimwc_manual_pack_size_setting = ( isset( $_POST['oimwc_manual_pack_size_setting'] ) ? sanitize_text_field( $_POST['oimwc_manual_pack_size_setting'] ) : 0 );
            $oimwc_supplier_unit = ( isset( $_POST['oimwc_supplier_unit'] ) ? sanitize_text_field( $_POST['oimwc_supplier_unit'] ) : 'piece' );
            $oimwc_show_in_low_stock = ( isset( $_POST['oimwc_show_in_low_stock'] ) ? sanitize_text_field( $_POST['oimwc_show_in_low_stock'] ) : 'no' );
            update_post_meta( $product_id, 'oimwc_gtin_num', $oimwc_gtin_num );
            /** Don't update if user has no plugin access **/
            
            if ( oimwc_check_permission() && oimwc_hide_supplier_info() && isset( $_POST['oimwc_supplier_id'] ) ) {
                
                if ( isset( $_POST['oimwc_supplier_id'] ) ) {
                    update_post_meta( $product_id, 'oimwc_supplier_id', sanitize_text_field( $_POST['oimwc_supplier_id'] ) );
                    
                    if ( $product_obj->is_type( 'variable' ) ) {
                        $variations = $product_obj->get_children();
                        if ( is_array( $variations ) && count( $variations ) > 0 ) {
                            foreach ( $variations as $variation_id ) {
                                $exist_supplier_id = get_post_meta( $variation_id, 'oimwc_supplier_id', true );
                                if ( !isset( $exist_supplier_id ) ) {
                                    update_post_meta( $variation_id, 'oimwc_supplier_id', sanitize_text_field( $_POST['oimwc_supplier_id'] ) );
                                }
                            }
                        }
                    }
                
                } else {
                    update_post_meta( $product_id, 'oimwc_supplier_id', 0 );
                }
                
                update_post_meta( $product_id, 'oimwc_supplier_product_id', sanitize_text_field( $_POST['oimwc_supplier_product_id'] ) );
                update_post_meta( $product_id, 'oimwc_supplier_product_url', sanitize_text_field( $_POST['oimwc_supplier_product_url'] ) );
                update_post_meta( $product_id, 'oimwc_supplier_note', sanitize_text_field( $_POST['oimwc_supplier_note'] ) );
                
                if ( isset( $_POST['oimwc_supplier_purchase_price'] ) ) {
                    update_post_meta( $product_id, 'oimwc_supplier_purchase_price', sanitize_text_field( wc_format_decimal( $_POST['oimwc_supplier_purchase_price'], 2 ) ) );
                } else {
                    update_post_meta( $product_id, 'oimwc_supplier_purchase_price', number_format( 0, 2 ) );
                }
                
                
                if ( $_POST['oimwc_supplier_pack_size'] > 0 ) {
                    update_post_meta( $product_id, 'oimwc_supplier_pack_size', sanitize_text_field( $_POST['oimwc_supplier_pack_size'] ) );
                } else {
                    $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
                    
                    if ( $default_supplier_pack_size ) {
                        update_post_meta( $product_id, 'oimwc_supplier_pack_size', sanitize_text_field( $default_supplier_pack_size ) );
                    } else {
                        update_post_meta( $product_id, 'oimwc_supplier_pack_size', 1 );
                    }
                
                }
            
            }
            
            /** ** **/
            
            if ( isset( $_POST['oimwc_low_stock_threshold_level'] ) ) {
                update_post_meta( $product_id, 'oimwc_low_stock_threshold_level', sanitize_text_field( $_POST['oimwc_low_stock_threshold_level'] ) );
            } else {
                update_post_meta( $product_id, 'oimwc_low_stock_threshold_level', 0 );
            }
            
            update_post_meta( $product_id, 'oimwc_manual_pack_size_setting', $oimwc_manual_pack_size_setting );
            
            if ( $new_our_pack_size > 0 ) {
                update_post_meta( $product_id, 'oimwc_our_pack_size', sanitize_text_field( $new_our_pack_size ) );
            } else {
                $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
                
                if ( $default_our_pack_size ) {
                    update_post_meta( $product_id, 'oimwc_our_pack_size', sanitize_text_field( $default_our_pack_size ) );
                } else {
                    update_post_meta( $product_id, 'oimwc_our_pack_size', 1 );
                }
            
            }
            
            update_post_meta( $product_id, 'oimwc_supplier_unit', $oimwc_supplier_unit );
            
            if ( $oimwc_show_in_low_stock ) {
                update_post_meta( $product_id, 'oimwc_show_in_low_stock', "yes" );
            } else {
                update_post_meta( $product_id, 'oimwc_show_in_low_stock', "no" );
            }
            
            
            if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() || oimwc_fs()->is_plan_or_trial( 'silver', true ) ) {
                wc_update_product_stock( $product_id, $new_stock );
                update_post_meta( $product_id, 'oimwc_physical_units_stock', sanitize_text_field( $new_stock ) );
            }
            
            $this->save_additional_supplier();
            oimwc_supplier_low_stock_count( $product_id );
        }
        
        /**
         * Save supplier variation
         * Saves inventory settings of variation.
         *
         * @since 1.0.0
         **/
        function add_discontinued_txt_disabled_variants()
        {
            global  $product ;
            $available_variations = $product->get_available_variations();
            if ( empty($available_variations) && false !== $available_variations ) {
                echo  '<p>' . __( 'Discontinued Product', 'order-and-inventory-manager-for-woocommerce' ) . '</p>' ;
            }
        }
        
        /**
         * Save supplier variation
         * Saves inventory settings of variation.
         *
         * @since 1.0.0
         * @param integer $variation_id stores variation's id
         * @param integer $i stores variation's loop counter
         */
        function save_supplier_variation( $variation_id, $i )
        {
            global  $wpdb, $OIMProductStock ;
            $product_id = wp_get_post_parent_id( $variation_id );
            $previous_stock = get_post_meta( $variation_id, 'oimwc_previous_stock', true );
            $previous_pack_size = get_post_meta( $variation_id, 'oimwc_previous_pack_size', true );
            $previous_total_pieces = get_post_meta( $variation_id, 'oimwc_previous_total_pieces', true );
            $new_stock = sanitize_text_field( $_POST['variable_stock'][$i] );
            $new_our_pack_size = ( isset( $_POST['oimwc_our_pack_size'][$i] ) ? sanitize_text_field( $_POST['oimwc_our_pack_size'][$i] ) : 1 );
            $new_total_pieces = sanitize_text_field( $_POST['oimwc_physical_units_stock'][$i] );
            $oimwc_physical_stock = get_post_meta( $variation_id, 'oimwc_physical_stock', true );
            /** Don't update if user has no plugin access **/
            
            if ( oimwc_check_permission() && oimwc_hide_supplier_info() ) {
                $supplier_id = $_POST['oimwc_supplier_id'][$i];
                $supplier_product_id = ( isset( $_POST['oimwc_supplier_product_id'][$i] ) ? sanitize_text_field( $_POST['oimwc_supplier_product_id'][$i] ) : '' );
                $supplier_product_url = ( isset( $_POST['oimwc_supplier_product_url'][$i] ) ? sanitize_text_field( $_POST['oimwc_supplier_product_url'][$i] ) : '' );
                $supplier_note = ( isset( $_POST['oimwc_supplier_note'][$i] ) ? sanitize_text_field( $_POST['oimwc_supplier_note'][$i] ) : '' );
                $supplier_purchase_price = $_POST['oimwc_supplier_purchase_price'][$i];
                $supplier_pack_size = $_POST['oimwc_supplier_pack_size'][$i];
                /** Add Additional Supplier Info **/
                $additional_suppliers = $_POST['additional_variable_suppliers'][$i];
                $tablename = $wpdb->prefix . 'additional_supplier_info';
                $additional_supplier_list = oimwc_additional_supplier_from_product( $variation_id, false );
                $wpdb->delete( $tablename, array(
                    'variable_id' => $variation_id,
                ) );
                $has_main_supplier = ( isset( $_POST['oimwc_supplier_id'][$i] ) ? 1 : 0 );
                if ( is_array( $additional_suppliers ) && count( $additional_suppliers ) > 0 ) {
                    foreach ( $additional_suppliers as $value ) {
                        if ( !empty($value['supplier_id']) ) {
                            
                            if ( $has_main_supplier ) {
                                $wpdb->insert( $tablename, array(
                                    'product_id'           => $product_id,
                                    'variable_id'          => $variation_id,
                                    'supplier_id'          => $value['supplier_id'],
                                    'supplier_product_id'  => $value['product_id'],
                                    'supplier_product_url' => $value['product_url'],
                                    'purchase_price'       => wc_format_decimal( $value['purchase_price'], 2 ),
                                    'pack_size'            => $value['pack_size'],
                                    'product_notes'        => $value['supplier_note'],
                                ), array(
                                    '%d',
                                    '%d',
                                    '%s',
                                    '%s',
                                    '%s',
                                    '%f',
                                    '%s',
                                    '%s'
                                ) );
                                oimwc_supplier_low_stock_count( 0, $value['supplier_id'] );
                            } else {
                                $has_main_supplier = true;
                                $supplier_id = $value['supplier_id'];
                                $supplier_product_id = $value['product_id'];
                                $supplier_product_url = $value['product_url'];
                                $supplier_note = $value['supplier_note'];
                                $supplier_purchase_price = wc_format_decimal( $value['purchase_price'], 2 );
                                $supplier_pack_size = $value['pack_size'];
                            }
                        
                        }
                    }
                }
                
                if ( isset( $supplier_purchase_price ) ) {
                    update_post_meta( $variation_id, 'oimwc_supplier_purchase_price', sanitize_text_field( wc_format_decimal( $supplier_purchase_price, 2 ) ) );
                } else {
                    update_post_meta( $variation_id, 'oimwc_supplier_purchase_price', number_format( 0, 2 ) );
                }
                
                $existing_supplier_id = get_post_meta( $variation_id, 'oimwc_supplier_id', true );
                
                if ( isset( $supplier_id ) ) {
                    update_post_meta( $variation_id, 'oimwc_supplier_id', sanitize_text_field( $supplier_id ) );
                } else {
                    update_post_meta( $variation_id, 'oimwc_supplier_id', 0 );
                }
                
                oimwc_supplier_low_stock_count( 0, $supplier_id );
                if ( $existing_supplier_id != $supplier_id ) {
                    $additional_supplier_list[] = $existing_supplier_id;
                }
                if ( is_array( $additional_supplier_list ) && count( $additional_supplier_list ) ) {
                    foreach ( $additional_supplier_list as $additional_supplier ) {
                        oimwc_supplier_low_stock_count( 0, $additional_supplier );
                    }
                }
                update_post_meta( $variation_id, 'oimwc_supplier_product_id', $supplier_product_id );
                update_post_meta( $variation_id, 'oimwc_supplier_product_url', $supplier_product_url );
                update_post_meta( $variation_id, 'oimwc_supplier_note', $supplier_note );
                
                if ( $supplier_pack_size > 0 ) {
                    update_post_meta( $variation_id, 'oimwc_supplier_pack_size', sanitize_text_field( $supplier_pack_size ) );
                } else {
                    $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
                    
                    if ( $default_supplier_pack_size ) {
                        update_post_meta( $variation_id, 'oimwc_supplier_pack_size', sanitize_text_field( $default_supplier_pack_size ) );
                    } else {
                        update_post_meta( $variation_id, 'oimwc_supplier_pack_size', 1 );
                    }
                
                }
                
                /** **/
            }
            
            /** **/
            
            if ( isset( $_POST['oimwc_low_stock_threshold_level'][$i] ) ) {
                update_post_meta( $variation_id, 'oimwc_low_stock_threshold_level', sanitize_text_field( $_POST['oimwc_low_stock_threshold_level'][$i] ) );
            } else {
                update_post_meta( $variation_id, 'oimwc_low_stock_threshold_level', 0 );
            }
            
            update_post_meta( $variation_id, 'oimwc_gtin_num', sanitize_text_field( $_POST['oimwc_gtin_num'][$i] ) );
            
            if ( $_POST['oimwc_our_pack_size'][$i] > 0 ) {
                update_post_meta( $variation_id, 'oimwc_our_pack_size', sanitize_text_field( $new_our_pack_size ) );
            } else {
                $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
                
                if ( $default_our_pack_size ) {
                    update_post_meta( $variation_id, 'oimwc_our_pack_size', $default_our_pack_size );
                } else {
                    update_post_meta( $variation_id, 'oimwc_our_pack_size', 1 );
                }
            
            }
            
            update_post_meta( $variation_id, 'oimwc_supplier_unit', sanitize_text_field( $_POST['oimwc_supplier_unit'][$i] ) );
            
            if ( $_POST['oimwc_show_in_low_stock'][$i] ) {
                update_post_meta( $variation_id, 'oimwc_show_in_low_stock', "yes" );
            } else {
                update_post_meta( $variation_id, 'oimwc_show_in_low_stock', "no" );
            }
            
            
            if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() || oimwc_fs()->is_plan_or_trial( 'silver', true ) ) {
                wc_update_product_stock( $variation_id, $new_stock );
                update_post_meta( $variation_id, 'oimwc_physical_units_stock', sanitize_text_field( $new_stock ) );
            }
            
            
            if ( !empty($_POST['oimwc_discontinued_replacement_product'][$i]) ) {
                $productSKU = get_post_meta( $_POST['oimwc_discontinued_replacement_product'][$i], '_sku', true );
                if ( empty($productSKU) ) {
                    $productSKU = '#' . $_POST['oimwc_discontinued_replacement_product'][$i];
                }
                $productTitle = get_the_title( $_POST['oimwc_discontinued_replacement_product'][$i] );
                $title = $productTitle . ' (' . $productSKU . ')';
                update_post_meta( $variation_id, 'oimwc_discontinued_replacement_title', $title );
            } else {
                update_post_meta( $variation_id, 'oimwc_discontinued_replacement_title', '' );
                update_post_meta( $variation_id, 'oimwc_discontinued_replacement_product', '' );
            }
            
            oimwc_supplier_low_stock_count( $variation_id );
            ?>
        <?php 
        }
        
        /**
         * Get data
         * Fetches all inventory settings of product and variation
         *
         * @since 1.0.0
         * @param string $key meta key 
         */
        function get_data( $key )
        {
            $data = get_post_meta( $this->product_id, $key, true );
            if ( !$data && isset( $this->parent_id ) ) {
                $data = get_post_meta( $this->parent_id, $key, true );
            }
            return $data;
        }
        
        /**
         * Store low stock product count to supplier postmeta when order placed & product stock reduced
         **/
        function check_low_stock_order_completed( $order_id )
        {
            if ( !$order_id ) {
                return;
            }
            $order = wc_get_order( $order_id );
            foreach ( $order->get_items() as $item_id => $item ) {
                
                if ( $item['variation_id'] > 0 ) {
                    $product_id = $item['variation_id'];
                } else {
                    $product_id = $item['product_id'];
                }
                
                oimwc_supplier_low_stock_count( $product_id );
            }
        }
        
        public function custom_search_supplier()
        {
            global  $wp_query ;
            $search = $_POST['search_val'];
            $args = array(
                's'              => $search,
                'posts_per_page' => -1,
                'post_type'      => 'supplier',
                'order'          => 'ASC',
                'orderby'        => 'name',
            );
            $posts = new WP_Query( $args );
            $data = $this->iterate_supplier_data( $posts );
            echo  json_encode( $data ) ;
            die;
        }
        
        public function custom_sort_supplier()
        {
            $orderby = $_POST['orderby'];
            $order = $_POST['order'];
            $args = array(
                'posts_per_page' => -1,
                'post_type'      => 'supplier',
                'order'          => $order,
                'orderby'        => $orderby,
            );
            $posts = new WP_Query( $args );
            $data = $this->iterate_supplier_data( $posts );
            echo  json_encode( $data ) ;
            die;
        }
        
        function iterate_supplier_data( $posts )
        {
            $data = [];
            if ( $posts->have_posts() ) {
                while ( $posts->have_posts() ) {
                    $posts->the_post();
                    $supplier_id = get_the_ID();
                    $supplier_name = get_the_title();
                    $short_name = get_post_meta( $supplier_id, 'oimwc_supplier_short_name', true );
                    $post_name = $posts->post->post_name;
                    $post_author = $posts->post->post_author;
                    $post_status = $posts->post->post_status;
                    $comment_status = $posts->post->comment_status;
                    $ping_status = $posts->post->ping_status;
                    $post_date = strtotime( $posts->post->post_date );
                    $yr = date( "Y", $post_date );
                    $mon = date( "m", $post_date );
                    $date = date( "d", $post_date );
                    $hh = date( "H", $post_date );
                    $mm = date( "i", $post_date );
                    $ss = date( "s", $post_date );
                    $data[] = array(
                        'supplier_id'    => $supplier_id,
                        'supplier_name'  => $supplier_name,
                        'short_name'     => $short_name,
                        'post_name'      => $post_name,
                        'post_author'    => $post_author,
                        'post_status'    => $post_status,
                        'comment_status' => $comment_status,
                        'ping_status'    => $ping_status,
                        'yr'             => $yr,
                        'mon'            => $mon,
                        'date'           => $date,
                        'hh'             => $hh,
                        'mm'             => $mm,
                        'ss'             => $ss,
                    );
                }
            }
            return $data;
        }
        
        function remove_supplier_quick_edit( $actions )
        {
            global  $current_screen ;
            if ( $current_screen->post_type != 'supplier' ) {
                return $actions;
            }
            unset( $actions['inline hide-if-no-js'] );
            return $actions;
        }
        
        function save_additional_supplier()
        {
            global  $wpdb ;
            $data = $_POST;
            $product_id = ( isset( $data['post_ID'] ) ? $data['post_ID'] : '' );
            $supplier_id = ( isset( $data['oimwc_supplier_id'] ) ? sanitize_text_field( $data['oimwc_supplier_id'] ) : '' );
            $supplier_product_id = ( isset( $data['oimwc_supplier_product_id'] ) ? sanitize_text_field( $data['oimwc_supplier_product_id'] ) : '' );
            $supplier_product_url = ( isset( $data['oimwc_supplier_product_url'] ) ? sanitize_text_field( $data['oimwc_supplier_product_url'] ) : '' );
            $supplier_note = ( isset( $data['oimwc_supplier_note'] ) ? sanitize_text_field( $data['oimwc_supplier_note'] ) : '' );
            $supplier_purchase_price = ( isset( $data['oimwc_supplier_purchase_price'] ) ? sanitize_text_field( $data['oimwc_supplier_purchase_price'] ) : '' );
            $supplier_pack_size = ( isset( $data['oimwc_supplier_pack_size'] ) ? sanitize_text_field( $data['oimwc_supplier_pack_size'] ) : '' );
            $has_main_supplier = ( isset( $data['oimwc_supplier_id'] ) ? 1 : 0 );
            $additional_suppliers = ( isset( $data['additional_suppliers'] ) ? $data['additional_suppliers'] : array() );
            $tablename = $wpdb->prefix . 'additional_supplier_info';
            $additional_supplier_list = oimwc_additional_supplier_from_product( $product_id );
            $wpdb->delete( $tablename, array(
                'product_id'  => $product_id,
                'variable_id' => 0,
            ) );
            if ( is_array( $additional_suppliers ) && count( $additional_suppliers ) > 0 ) {
                foreach ( $additional_suppliers as $key => $value ) {
                    if ( !empty($value['supplier_id']) ) {
                        
                        if ( $has_main_supplier ) {
                            $wpdb->insert( $tablename, array(
                                'product_id'           => $product_id,
                                'supplier_id'          => $value['supplier_id'],
                                'supplier_product_id'  => $value['product_id'],
                                'supplier_product_url' => $value['product_url'],
                                'purchase_price'       => wc_format_decimal( $value['purchase_price'], 2 ),
                                'pack_size'            => $value['pack_size'],
                                'product_notes'        => $value['supplier_note'],
                            ), array(
                                '%d',
                                '%d',
                                '%s',
                                '%s',
                                '%f',
                                '%d',
                                '%s'
                            ) );
                            oimwc_supplier_low_stock_count( 0, $value['supplier_id'] );
                        } else {
                            $has_main_supplier = true;
                            $supplier_id = $value['supplier_id'];
                            $supplier_product_id = $value['product_id'];
                            $supplier_product_url = $value['product_url'];
                            $supplier_note = $value['supplier_note'];
                            $supplier_purchase_price = wc_format_decimal( $value['purchase_price'], 2 );
                            $supplier_pack_size = $value['pack_size'];
                        }
                    
                    }
                }
            }
            $existing_supplier_id = get_post_meta( $product_id, 'oimwc_supplier_id', true );
            update_post_meta( $product_id, 'oimwc_supplier_id', sanitize_text_field( $supplier_id ) );
            oimwc_supplier_low_stock_count( 0, $supplier_id );
            if ( $existing_supplier_id != $supplier_id ) {
                $additional_supplier_list[] = $existing_supplier_id;
            }
            if ( is_array( $additional_supplier_list ) && count( $additional_supplier_list ) ) {
                foreach ( $additional_supplier_list as $additional_supplier ) {
                    oimwc_supplier_low_stock_count( 0, $additional_supplier );
                }
            }
            update_post_meta( $product_id, 'oimwc_supplier_product_id', sanitize_text_field( $supplier_product_id ) );
            update_post_meta( $product_id, 'oimwc_supplier_product_url', sanitize_text_field( $supplier_product_url ) );
            update_post_meta( $product_id, 'oimwc_supplier_note', sanitize_text_field( $supplier_note ) );
            
            if ( isset( $supplier_purchase_price ) ) {
                update_post_meta( $product_id, 'oimwc_supplier_purchase_price', sanitize_text_field( wc_format_decimal( $supplier_purchase_price, 2 ) ) );
            } else {
                update_post_meta( $product_id, 'oimwc_supplier_purchase_price', number_format( 0, 2 ) );
            }
            
            
            if ( $supplier_pack_size > 0 ) {
                update_post_meta( $product_id, 'oimwc_supplier_pack_size', sanitize_text_field( $supplier_pack_size ) );
            } else {
                $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
                
                if ( $default_supplier_pack_size ) {
                    update_post_meta( $product_id, 'oimwc_supplier_pack_size', sanitize_text_field( $default_supplier_pack_size ) );
                } else {
                    update_post_meta( $product_id, 'oimwc_supplier_pack_size', 1 );
                }
            
            }
            
            //die;
        }
        
        /* Add save supplier info button in each supplier tab */
        function save_supplier_info()
        {
            global  $wpdb ;
            $data = $_POST;
            $product_id = ( isset( $data['product_id'] ) ? $data['product_id'] : '' );
            $parent_id = wp_get_post_parent_id( $data['product_id'] );
            $product_obj_id = $product_id;
            $variation_id = 0;
            
            if ( $parent_id ) {
                $product_id = $parent_id;
                $variation_id = $data['product_id'];
                $product_obj_id = $variation_id;
            }
            
            $supplier_id = ( isset( $data['oimwc_supplier_id'] ) ? sanitize_text_field( $data['oimwc_supplier_id'] ) : '' );
            $supplier_product_id = ( isset( $data['oimwc_supplier_product_id'] ) ? sanitize_text_field( $data['oimwc_supplier_product_id'] ) : '' );
            $supplier_product_url = ( isset( $data['oimwc_supplier_product_url'] ) ? sanitize_text_field( $data['oimwc_supplier_product_url'] ) : '' );
            $supplier_note = ( isset( $data['oimwc_supplier_note'] ) ? sanitize_text_field( $data['oimwc_supplier_note'] ) : '' );
            $supplier_purchase_price = ( isset( $data['oimwc_supplier_purchase_price'] ) ? sanitize_text_field( $data['oimwc_supplier_purchase_price'] ) : wc_format_decimal( 0, 2 ) );
            $supplier_pack_size = ( isset( $data['oimwc_supplier_pack_size'] ) ? sanitize_text_field( $data['oimwc_supplier_pack_size'] ) : 1 );
            
            if ( $_POST['supplier_index'] >= 1 ) {
                $tablename = $wpdb->prefix . 'additional_supplier_info';
                $additional_supplier_list = oimwc_additional_supplier_from_product( $product_id );
                $wpdb->delete( $tablename, array(
                    'product_id'  => $product_id,
                    'variable_id' => $variation_id,
                    'supplier_id' => $supplier_id,
                ) );
                
                if ( $supplier_id != '' ) {
                    $wpdb->insert( $tablename, array(
                        'product_id'           => $product_id,
                        'variable_id'          => $variation_id,
                        'supplier_id'          => $supplier_id,
                        'supplier_product_id'  => $supplier_product_id,
                        'supplier_product_url' => $supplier_product_url,
                        'purchase_price'       => wc_format_decimal( $supplier_purchase_price, 2 ),
                        'pack_size'            => $supplier_pack_size,
                        'product_notes'        => $supplier_note,
                    ), array(
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%f',
                        '%d',
                        '%s'
                    ) );
                    oimwc_supplier_low_stock_count( 0, $supplier_id );
                }
                
                $existing_supplier_id = get_post_meta( $product_id, 'oimwc_supplier_id', true );
                oimwc_supplier_low_stock_count( 0, $supplier_id );
                if ( $existing_supplier_id != $supplier_id ) {
                    $additional_supplier_list[] = $existing_supplier_id;
                }
                if ( is_array( $additional_supplier_list ) && count( $additional_supplier_list ) ) {
                    foreach ( $additional_supplier_list as $additional_supplier ) {
                        oimwc_supplier_low_stock_count( 0, $additional_supplier );
                    }
                }
            } else {
                update_post_meta( $product_obj_id, 'oimwc_supplier_id', sanitize_text_field( $supplier_id ) );
                update_post_meta( $product_obj_id, 'oimwc_supplier_product_id', sanitize_text_field( $supplier_product_id ) );
                update_post_meta( $product_obj_id, 'oimwc_supplier_product_url', sanitize_text_field( $supplier_product_url ) );
                update_post_meta( $product_obj_id, 'oimwc_supplier_note', sanitize_text_field( $supplier_note ) );
                
                if ( isset( $supplier_purchase_price ) ) {
                    update_post_meta( $product_obj_id, 'oimwc_supplier_purchase_price', sanitize_text_field( wc_format_decimal( $supplier_purchase_price, 2 ) ) );
                } else {
                    update_post_meta( $product_obj_id, 'oimwc_supplier_purchase_price', wc_format_decimal( 0, 2 ) );
                }
                
                
                if ( $supplier_pack_size > 0 ) {
                    update_post_meta( $product_obj_id, 'oimwc_supplier_pack_size', sanitize_text_field( $supplier_pack_size ) );
                } else {
                    $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
                    
                    if ( $default_supplier_pack_size ) {
                        update_post_meta( $product_obj_id, 'oimwc_supplier_pack_size', sanitize_text_field( $default_supplier_pack_size ) );
                    } else {
                        update_post_meta( $product_obj_id, 'oimwc_supplier_pack_size', 1 );
                    }
                
                }
            
            }
        
        }
        
        /* update oimwc tab data */
        function update_oimwc_data()
        {
            global  $wpdb, $OIMProductStock ;
            parse_str( $_POST['formdata'], $_POST );
            wc_update_product_stock( $_POST['post_ID'], $_POST['_stock'] );
            $this->save_product_supplier( $_POST['post_ID'] );
            $stock_data = $OIMProductStock->get_product_stock_detail( array(
                'product_id'   => $_POST['post_ID'],
                'variation_id' => 0,
            ), false );
            $nonce = wp_create_nonce( 'download_stock_history_log_nonce' );
            $log_history_link = add_query_arg( array(
                'action'     => 'download_stock_history_log',
                'product_id' => $_POST['post_ID'],
                '_nonce'     => $nonce,
            ), admin_url( 'admin-ajax.php' ) );
            ob_start();
            require_once OIMWC_TEMPLATE . 'display_product_stock.php';
            $product_stock_history = ob_get_contents();
            ob_clean();
            $data = array(
                'physical_stock'       => get_post_meta( $_POST['post_ID'], 'oimwc_physical_stock', true ),
                'physical_units_stock' => get_post_meta( $_POST['post_ID'], 'oimwc_physical_units_stock', true ),
                'our_pack_size'        => get_post_meta( $_POST['post_ID'], 'oimwc_our_pack_size', true ),
                'stock'                => intval( get_post_meta( $_POST['post_ID'], '_stock', true ) ),
                'stock_history'        => $product_stock_history,
            );
            wp_send_json_success( $data );
        }
        
        /* update variable product oimwc tab data */
        function update_oimwc_data_variable()
        {
            global  $wpdb ;
            parse_str( $_POST['formdata'], $_POST );
            $product_id = $_POST['post_ID'];
            $product_obj = wc_get_product( $product_id );
            $oimwc_show_in_low_stock = ( isset( $_POST['oimwc_show_in_low_stock'] ) ? sanitize_text_field( $_POST['oimwc_show_in_low_stock'] ) : 'no' );
            $oimwc_low_stock_threshold_level = ( isset( $_POST['oimwc_low_stock_threshold_level'] ) ? sanitize_text_field( $_POST['oimwc_low_stock_threshold_level'] ) : 0 );
            $oimwc_supplier_unit = ( isset( $_POST['oimwc_supplier_unit'] ) ? sanitize_text_field( $_POST['oimwc_supplier_unit'] ) : 'piece' );
            $oimwc_manual_pack_size_setting = ( isset( $_POST['oimwc_manual_pack_size_setting'] ) ? sanitize_text_field( $_POST['oimwc_manual_pack_size_setting'] ) : 0 );
            
            if ( $oimwc_show_in_low_stock ) {
                update_post_meta( $product_id, 'oimwc_show_in_low_stock', "yes" );
            } else {
                update_post_meta( $product_id, 'oimwc_show_in_low_stock', "no" );
            }
            
            update_post_meta( $product_id, 'oimwc_low_stock_threshold_level', sanitize_text_field( $oimwc_low_stock_threshold_level ) );
            update_post_meta( $product_id, 'oimwc_supplier_unit', $oimwc_supplier_unit );
            update_post_meta( $product_id, 'oimwc_manual_pack_size_setting', $oimwc_manual_pack_size_setting );
            
            if ( isset( $_POST['oimwc_all_discontinued_products'] ) ) {
                update_post_meta( $product_id, 'oimwc_all_discontinued_products', 'yes' );
                
                if ( $product_obj->is_type( 'variable' ) ) {
                    $variations = $product_obj->get_children();
                    if ( is_array( $variations ) && count( $variations ) > 0 ) {
                        foreach ( $variations as $variation_id ) {
                            update_post_meta( $variation_id, 'oimwc_discontinued_product', 'yes' );
                            update_post_meta( $variation_id, 'oimwc_show_in_low_stock', 'no' );
                            update_post_meta( $variation_id, '_backorders', 'no' );
                            $stock_status = get_post_meta( $variation_id, '_stock', true );
                            ( $stock_status > 1 ? update_post_meta( $variation_id, '_stock_status', 'instock' ) : update_post_meta( $variation_id, '_stock_status', 'outofstock' ) );
                        }
                    }
                }
            
            } else {
                update_post_meta( $product_id, 'oimwc_all_discontinued_products', 'no' );
            }
        
        }
        
        function get_products_list_with_sku()
        {
            global  $wpdb ;
            $search_val = $_GET['search_val'];
            $product_arr = array(
                0 => __( 'Select Product', 'order-and-inventory-manager-for-woocommerce' ),
            );
            $postmeta_table = $wpdb->prefix . 'postmeta';
            $posts_table = $wpdb->prefix . 'posts';
            $sql = "SELECT po.post_title as title,\r\n                               po.ID as id\r\n                               FROM {$postmeta_table} as pmt\r\n                               LEFT JOIN {$posts_table} AS po ON ( po.ID = pmt.post_id )\r\n                               WHERE po.post_title LIKE '%{$search_val}%'\r\n                               AND po.post_type IN('product','product_variation')";
            $products = $wpdb->get_results( $sql, ARRAY_A );
            if ( count( $products ) ) {
                foreach ( $products as $product ) {
                    $productSKU = get_post_meta( $product['id'], '_sku', true );
                    if ( empty($productSKU) ) {
                        $productSKU = '#' . $product['id'];
                    }
                    $productTitle = $product['title'];
                    $product_arr[$product['id']] = $productTitle . ' (' . $productSKU . ')';
                }
            }
            echo  json_encode( $product_arr ) ;
            die;
        }
    
    }
    new OIMWC_Supplier();
}
