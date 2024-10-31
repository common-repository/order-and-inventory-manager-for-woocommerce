<?php

/**
 * DataPurchaseOrder class
 *
 * Display products in purchase order table
 *
 * @since    1.0.0
 */
// WP_List_Table is not loaded automatically so we need to load it in our application
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
require_once ABSPATH . 'wp-admin/includes/template.php';
/**
 * DataPurchaseOrder Display products in purchase order table
 */
class DataPurchaseOrder extends WP_List_Table
{
    public  $total_pages ;
    public  $currency ;
    public  $total_supplier_product_low_stock ;
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        global  $total_pagination_records ;
        $perPage = get_option( 'oimwc_per_page' );
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items = $data;
    }
    
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'thumb'          => '<span class="wc-image tips" data-product="' . __( 'Products', 'order-and-inventory-manager-for-woocommerce' ) . '" data-tip="' . esc_attr__( 'Image', 'woocommerce' ) . '">' . __( 'Image', 'woocommerce' ),
            'product_info'   => __( 'Product Info', 'order-and-inventory-manager-for-woocommerce' ),
            'supplier_info'  => __( 'Supplier Info', 'order-and-inventory-manager-for-woocommerce' ),
            'product_detail' => __( 'Product Price & Stock', 'order-and-inventory-manager-for-woocommerce' ),
            'amount'         => __( 'Qty', 'order-and-inventory-manager-for-woocommerce' ),
        );
        return $columns;
    }
    
    /**
     * Define which columns are hidden
     *
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
        $sortable_columns = array(
            'product_info'   => array( 'post_title', false ),
            'supplier_info'  => array( 'supplier_name', false ),
            'product_detail' => array( 'price', false ),
        );
        return $sortable_columns;
    }
    
    /**
     * Get the table data
     *
     * @return Array
     */
    public function table_data()
    {
        global  $default_supplier, $wpdb, $additional_product ;
        global  $custom_offset, $custom_limit ;
        $data = array();
        wp_enqueue_style( 'woocommerce_admin_styles' );
        $perPage = 40;
        $paged = 1;
        if ( !empty($_POST["page"]) ) {
            $paged = $_POST["page"];
        }
        if ( $paged == 1 ) {
            $perPage = 20;
        }
        $custom_offset = $perPage;
        $custom_limit = ($paged - 1) * $perPage;
        if ( $paged > 1 ) {
            $custom_limit = $custom_limit - 20;
        }
        $search = '';
        if ( isset( $_POST['search_val'] ) ) {
            $search = $_POST['search_val'];
        }
        $orderby = ( !empty($_GET['orderby']) ? sanitize_text_field( $_GET['orderby'] ) : 'post_title' );
        $order = ( !empty($_GET['order']) ? sanitize_text_field( $_GET['order'] ) : 'asc' );
        $supplier_ids = array();
        if ( $orderby == 'supplier' ) {
            $supplier_ids = get_suppliers_by_sort_order( $order );
        }
        $variable_products = wp_cache_get( 'oimwc_variable_products', 'oimwc_low_stock_products_cache' );
        
        if ( !$variable_products ) {
            $sql = 'SELECT DISTINCT(post_parent) FROM ' . $wpdb->posts . ' WHERE post_parent > 0 AND post_type = "product_variation"';
            $variable_products = $wpdb->get_col( $sql );
            wp_cache_add( 'oimwc_variable_products', $variable_products, 'oimwc_low_stock_products_cache' );
        }
        
        
        if ( $_REQUEST['supplier_id'] != 'all' && isset( $_REQUEST['supplier_id'] ) ) {
            $ordered_product = OIMWC_Order::get_ordered_product( $_REQUEST['supplier_id'], false, '=' );
        } else {
            $post_not_in = $variable_products;
            $ordered_product = OIMWC_Order::get_ordered_product( 0, false, '=' );
        }
        
        $post_not_in = $variable_products;
        $order_data = get_post_meta( $_REQUEST['supplier_id'], 'oimwc_supplier_purchase_order_data', true );
        if ( is_array( $order_data ) ) {
            
            if ( array_key_exists( '0', $order_data ) ) {
                unset( $order_data[0] );
                update_post_meta( $_REQUEST['supplier_id'], 'oimwc_supplier_purchase_order_data', $order_data );
            }
        
        }
        
        if ( $order_data ) {
            $post_in = array_keys( $order_data );
        } else {
            $post_in = '';
        }
        
        $request_supplier_id = 0;
        $additional_product = array();
        
        if ( isset( $_REQUEST['supplier_id'] ) && sanitize_text_field( $_REQUEST['supplier_id'] ) !== "all" ) {
            $request_supplier_id = sanitize_text_field( $_REQUEST['supplier_id'] );
            ( $_REQUEST['show_all_product'] == 1 ? true : false );
            
            if ( $_REQUEST['show_all_product'] == 1 ) {
                $additional_product = oimwc_show_all_product_stock_count(
                    $_REQUEST['supplier_id'],
                    false,
                    '=',
                    true
                );
            } else {
                $additional_product = oimwc_additional_supplier_low_stock_count( $_REQUEST['supplier_id'], false );
            }
            
            
            if ( !$additional_product ) {
                $meta_query = array(
                    'key'     => 'oimwc_supplier_id',
                    'compare' => '=',
                    'value'   => sanitize_text_field( $_REQUEST['supplier_id'] ),
                    'type'    => 'NUMERIC',
                );
            } else {
                $meta_query = '';
            }
        
        }
        
        $order_clause_data = array();
        
        if ( $orderby == 'price' ) {
            $orderby = 'order_clause';
            $order_clause_data = array(
                'key'  => '_price',
                'type' => 'NUMERIC',
            );
        }
        
        
        if ( $orderby == 'supplier_name' ) {
            $supplier_ids = implode( ',', $supplier_ids );
            $orderby = "FIELD(ID," . $supplier_ids . ")";
        }
        
        if ( $additional_product ) {
            if ( array_key_exists( $additional_product, $post_in ) ) {
                $post_in = array_merge( $post_in, $additional_product );
            }
        }
        
        if ( $post_in ) {
            $args = array(
                'post_type'    => array( 'product', 'product_variation' ),
                'meta_query'   => array(
                'relation'     => 'AND',
                'supplier'     => $meta_query,
                'order_clause' => $order_clause_data,
                'meta_query'   => array(
                'relation' => 'OR',
                array(
                'key'     => 'oimwc_discontinued_product',
                'compare' => 'NOT EXISTS',
                'value'   => '',
            ),
                array(
                'key'   => 'oimwc_discontinued_product',
                'value' => 'no',
            ),
            ),
            ),
                's'            => $search,
                'orderby'      => $orderby,
                'order'        => $order,
                'post__in'     => $post_in,
                'post__not_in' => $post_not_in,
                'post_status'  => array( 'private', 'publish' ),
            );
        } else {
            $args = '';
        }
        
        
        if ( $post_in ) {
            $lowstock_array = array();
            $requested_stock = array();
            $product_ids = array();
            $order_ids = array();
            $lowstock_list = array_keys( $order_data );
            foreach ( $lowstock_list as $value ) {
                $lowstock_threshold = get_post_meta( $value, 'oimwc_low_stock_threshold_level', true );
                $lowstock_array[$value] = $lowstock_threshold;
            }
            $lowstock_ids = implode( ', ', array_unique( $lowstock_list ) );
            $query = 'SELECT DISTINCT(product_id), SUM(stock + requested_stock) AS total FROM ' . $wpdb->prefix . 'order_inventory WHERE product_id IN (' . $lowstock_ids . ') AND completed_order = 0';
            
            if ( $_REQUEST['supplier_id'] ) {
                $query .= ' AND supplier_id =' . $_REQUEST['supplier_id'] . ' GROUP BY product_id';
            } else {
                $query .= ' GROUP BY product_id';
            }
            
            $lowstock_result = $wpdb->get_results( $query, ARRAY_A );
            if ( $lowstock_result ) {
                foreach ( $lowstock_result as $key => $val ) {
                    $requested_stock[$val['product_id']] = $val['total'];
                }
            }
            ksort( $lowstock_array );
            ksort( $requested_stock );
            foreach ( $lowstock_array as $key => $val ) {
                
                if ( array_key_exists( $key, $requested_stock ) ) {
                    if ( $val >= $requested_stock[$key] ) {
                        array_push( $product_ids, $key );
                    }
                } else {
                    array_push( $order_ids, $key );
                }
            
            }
            $post_in = array_merge( $product_ids, $order_ids );
            
            if ( $ordered_product ) {
                $args['post__in'] = $post_in;
            } else {
                if ( $additional_product ) {
                    $args['post__in'] = $post_in;
                }
            }
            
            $args['post__in'] = $post_in;
        } else {
            if ( is_array( $args ) ) {
                $args['post__in'] = '';
            }
        }
        
        add_filter(
            'post_limits',
            'oimwc_custom_limits',
            10,
            2
        );
        add_filter( 'posts_request', array( $this, 'closure' ) );
        $product_list = new WP_Query( $args );
        $total_records = $product_list->found_posts - $perPage;
        $this->total_supplier_product_low_stock = $product_list->found_posts;
        if ( $_REQUEST['show_all_product'] == 1 ) {
            update_option( 'oimwc_show_all_product', $product_list->post_count );
        }
        $total_pages = ceil( $total_records / 40 );
        $this->total_pages = $total_pages + 1;
        remove_filter( 'posts_request', array( $this, 'closure' ) );
        remove_filter(
            'post_limits',
            'oimwc_custom_limits',
            10,
            2
        );
        $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
        $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
        
        if ( $_REQUEST['supplier_id'] != 'all' && isset( $_REQUEST['supplier_id'] ) ) {
            $requested_item = OIMWC_Order::get_requested_product_stock( $_REQUEST['supplier_id'], false, '=' );
            $temporary_product = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'oimwc_temp_product WHERE supplier_id=' . $_REQUEST['supplier_id'] . ' AND order_id = 0 AND id IN (' . implode( ',', array_keys( $order_data ) ) . ')' );
            $all_supplier = true;
        } else {
            $requested_item = OIMWC_Order::get_requested_product_stock( 0, false, '=' );
            if ( is_array( $order_data ) ) {
                $temporary_product = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'oimwc_temp_product  WHERE order_id = 0 AND id IN (' . implode( ',', array_keys( $order_data ) ) . ')' );
            }
            $all_supplier = false;
        }
        
        
        if ( $product_list->have_posts() ) {
            while ( $product_list->have_posts() ) {
                global  $post ;
                $product_list->the_post();
                $id = get_the_ID();
                $product_supplier = get_post_meta( $id, 'oimwc_supplier_product_url', true );
                $product_stock = (int) get_post_meta( $id, '_stock', true );
                $product_sku = get_post_meta( $id, '_sku', true );
                $warning_level = get_post_meta( $id, 'oimwc_low_stock_threshold_level', true );
                $purchase_price = wc_format_decimal( get_post_meta( $id, 'oimwc_supplier_purchase_price', true ) );
                $product_first_supplier_id = get_post_meta( $id, 'oimwc_supplier_id', true );
                $product_supplier_id = ( $request_supplier_id ? $request_supplier_id : $product_first_supplier_id );
                $supplier_product_id = get_post_meta( $id, 'oimwc_supplier_product_id', true );
                $supplier_note = get_post_meta( $id, 'oimwc_supplier_note', true );
                /*if( !$product_supplier_id ){
                      $product_parent_id = wp_get_post_parent_id( $id );
                      if( $product_parent_id ){
                          $product_supplier_id = get_post_meta( $product_parent_id, 'oimwc_supplier_id', true );
                      }
                  }*/
                $purchase_currency = get_post_meta( $product_supplier_id, 'oimwc_supplier_currency', true );
                $purchase_price = ( $purchase_price ? $purchase_price : 0 );
                $product_type = $post->post_type;
                $product_title = ( $product_type == 'product' ? get_the_title() : get_the_title( $post->post_parent ) );
                $actual_product_id = ( $product_type == 'product' ? $id : $post->post_parent );
                $product_variant = '-';
                $supplier_pack_size = get_post_meta( $id, 'oimwc_supplier_pack_size', true );
                $our_pack_size = get_post_meta( $id, 'oimwc_our_pack_size', true );
                
                if ( $product_first_supplier_id != $product_supplier_id ) {
                    $additional_supplier_info = oimwc_additional_supplier_details_from_product( $id, $product_supplier_id );
                    
                    if ( is_array( $additional_supplier_info ) && count( $additional_supplier_info ) ) {
                        $supplier_pack_size = $additional_supplier_info['pack_size'];
                        $purchase_price = wc_format_decimal( $additional_supplier_info['purchase_price'] );
                        $purchase_price = ( $purchase_price ? $purchase_price : 0 );
                        $product_supplier = $additional_supplier_info['supplier_product_url'];
                    }
                
                }
                
                $our_pack = ( $our_pack_size ? $our_pack_size : (( $default_our_pack_size ? $default_our_pack_size : 1 )) );
                $supplier_remaining_pieces = (int) get_post_meta( $id, 'oimwc_supplier_remaining_pieces', true );
                //$total_pieces = floor( ($product_stock * $our_pack ) + $supplier_remaining_pieces );
                $total_pieces = floor( $product_stock * $our_pack );
                $image = '<img width="40" height="40" src="' . plugins_url( '/woocommerce/assets/images/placeholder.png' ) . '" />';
                $product = wc_get_product( $id );
                
                if ( $product instanceof WC_Product ) {
                    $price = $product->get_price();
                } else {
                    $price = '';
                }
                
                
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
                    
                    if ( !$product_sku ) {
                        $product_sku = get_post_meta( $post->post_parent, '_sku', true );
                    }
                    
                    if ( has_post_thumbnail( $id ) ) {
                        $image = get_the_post_thumbnail( $id, array( 75, 75 ) );
                        $thumb_url = get_the_post_thumbnail_url( $id, array( 75, 75 ) );
                        if ( !file_exists( $thumb_url ) ) {
                            $thumb_url = get_the_post_thumbnail_url( $id, array( 100, 100 ) );
                        }
                    } else {
                        
                        if ( has_post_thumbnail( $product->get_parent_id() ) ) {
                            $image = get_the_post_thumbnail( $product->get_parent_id(), array( 75, 75 ) );
                            $thumb_url = get_the_post_thumbnail_url( $product->get_parent_id(), array( 75, 75 ) );
                            if ( !file_exists( $thumb_url ) ) {
                                $thumb_url = get_the_post_thumbnail_url( $product->get_parent_id(), array( 100, 100 ) );
                            }
                        } else {
                            $thumb_url = '';
                        }
                    
                    }
                    
                    $price = $product->get_price();
                } else {
                    
                    if ( has_post_thumbnail( $id ) ) {
                        $image = get_the_post_thumbnail( $id, array( 75, 75 ) );
                        $thumb_url = get_the_post_thumbnail_url( $id, array( 75, 75 ) );
                        if ( !file_exists( $thumb_url ) ) {
                            $thumb_url = get_the_post_thumbnail_url( $id, array( 100, 100 ) );
                        }
                    } else {
                        $thumb_url = '';
                    }
                
                }
                
                $product_title_url = sprintf( '<a href="%s" >%s</a>', get_edit_post_link( $actual_product_id ), $product_title );
                $supplier_product_link = sprintf( '<a target="_blank" href="%s" >%s</a>', $product_supplier, __( 'Link to product', 'order-and-inventory-manager-for-woocommerce' ) );
                $supplier_name = ( $product_supplier_id ? oiwmc_get_supplier_with_link( $product_supplier_id ) : '-' );
                $supplier_name_list = '';
                $has_additional_supplier = false;
                
                if ( !isset( $_REQUEST['supplier_id'] ) || isset( $_REQUEST['supplier_id'] ) && sanitize_text_field( $_REQUEST['supplier_id'] ) == "all" ) {
                    $additional_supplier_list = oimwc_additional_supplier_from_product( $id );
                    
                    if ( is_array( $additional_supplier_list ) && count( $additional_supplier_list ) ) {
                        $has_additional_supplier = true;
                        $short_name = oiwmc_get_supplier_with_link( $product_supplier_id );
                        $supplier_name_list = $short_name;
                        foreach ( $additional_supplier_list as $additional_supplier_id ) {
                            $short_name = oiwmc_get_supplier_with_link( $additional_supplier_id );
                            $supplier_name_list .= " | " . $short_name;
                        }
                    }
                    
                    $additional_supplier_list = oimwc_additional_supplier_from_product( $id, false );
                    
                    if ( is_array( $additional_supplier_list ) && count( $additional_supplier_list ) ) {
                        $has_additional_supplier = true;
                        
                        if ( !$supplier_name_list ) {
                            $short_name = oiwmc_get_supplier_with_link( $product_supplier_id );
                            $supplier_name_list = $short_name;
                        }
                        
                        foreach ( $additional_supplier_list as $additional_supplier_id ) {
                            $short_name = oiwmc_get_supplier_with_link( $additional_supplier_id );
                            $supplier_name_list .= " | " . $short_name;
                        }
                    }
                    
                    if ( $has_additional_supplier ) {
                        $supplier_name = __( 'Multiple Supplier', 'order-and-inventory-manager-for-woocommerce' );
                    }
                }
                
                $request_item = '';
                if ( $requested_item ) {
                    foreach ( $requested_item as $product_id => $request_stock ) {
                        if ( $product_id == $id ) {
                            $request_item = $request_stock;
                        }
                    }
                }
                foreach ( $order_data as $key => $value ) {
                    
                    if ( $key == $id ) {
                        $request_data = $value;
                        $po_price = wc_format_decimal( $request_data * $purchase_price, 2 );
                    }
                
                }
                
                if ( $product instanceof WC_Product ) {
                    $product_class = $product->get_type();
                } else {
                    $product_class = '';
                }
                
                $data[] = array(
                    'id'                        => $id,
                    'thumb_url'                 => ( !empty($thumb_url) ? $thumb_url : plugins_url( '/woocommerce/assets/images/placeholder.png' ) ),
                    'thumb'                     => $image,
                    'product_name'              => $product_title_url,
                    'product_sku'               => $product_sku,
                    'product_variant'           => $product_variant,
                    'product_supplier'          => ( $product_supplier && !$has_additional_supplier ? $supplier_product_link : '' ),
                    'product_stock'             => ( $product_stock ? $product_stock : 0 ),
                    'warning_level'             => ( $warning_level ? $warning_level : 0 ),
                    'purchase_price'            => ( $has_additional_supplier ? '-' : wc_price( $purchase_price, array(
                    'currency' => $purchase_currency,
                ) ) ),
                    'non_format_purchase_price' => $purchase_price,
                    'supplier_pack_size'        => ( $has_additional_supplier ? '-' : (( $supplier_pack_size ? $supplier_pack_size : $default_supplier_pack_size )) ),
                    'supplier_currency'         => $purchase_currency,
                    'our_pack_size'             => $our_pack,
                    'total_pieces'              => ( intval( $total_pieces ) > 0 ? $total_pieces : 0 ),
                    'price'                     => wc_price( $price ),
                    'product_link'              => get_edit_post_link( $actual_product_id ),
                    'supplier_name'             => $supplier_name,
                    'supplier_id'               => $product_supplier_id,
                    'product_title_without_url' => $product_title,
                    'product_edit_url'          => get_edit_post_link( $actual_product_id ),
                    'product_class'             => $product_class,
                    'multi_supplier_class'      => ( $has_additional_supplier ? 'multiple_supplier_panel' : '' ),
                    'supplier_name_list'        => $supplier_name_list,
                    'show_all_product'          => 1,
                    'request_data'              => $request_data,
                    'po_price'                  => $po_price,
                    'request_item'              => $request_item,
                    'temp_product'              => false,
                    'qty'                       => '',
                    'all_supplier'              => $all_supplier,
                    'supplier_product_id'       => $supplier_product_id,
                    'supplier_note'             => $supplier_note,
                );
            }
            $total_low_stock_products = get_post_meta( $product_supplier_id, 'oimwc_total_low_stock_products', true );
        }
        
        if ( is_array( $temporary_product ) && count( $temporary_product ) > 0 ) {
            foreach ( $temporary_product as $key => $temp_data ) {
                $image = '<img width="40" height="40" src="' . plugins_url( '/woocommerce/assets/images/placeholder.png' ) . '" />';
                $supplier_name = oiwmc_get_supplier_with_link( $temp_data->supplier_id );
                $supplier_product_link = ( $temp_data->product_url ? sprintf( '<a target="_blank" href="%s" >%s</a>', $temp_data->product_url, __( 'Link to product', 'order-and-inventory-manager-for-woocommerce' ) ) : '' );
                $price = sanitize_text_field( $temp_data->product_price );
                $our_pack = ( $default_our_pack_size ? $default_our_pack_size : 1 );
                $product_class = ( $temp_data->variation_name ? '' : 'simple' );
                $product_price = wc_format_decimal( $temp_data->product_qty * $price, 2 );
                $purchase_currency = get_post_meta( $temp_data->supplier_id, 'oimwc_supplier_currency', true );
                $supplier_note = $temp_data->supplier_notes;
                $temp_array[] = array(
                    'id'                        => $temp_data->id,
                    'thumb_url'                 => plugins_url( '/woocommerce/assets/images/placeholder.png' ),
                    'thumb'                     => $image,
                    'product_name'              => $temp_data->product_name,
                    'supplier_product_id'       => ( $temp_data->supplier_product_id ? $temp_data->supplier_product_id : '' ),
                    'product_variant'           => $temp_data->variation_name,
                    'product_supplier'          => $supplier_product_link,
                    'product_stock'             => 0,
                    'warning_level'             => 0,
                    'purchase_price'            => wc_price( $price, array(
                    'currency' => $purchase_currency,
                ) ),
                    'non_format_purchase_price' => $price,
                    'supplier_pack_size'        => ( $default_supplier_pack_size ? $default_supplier_pack_size : 1 ),
                    'supplier_currency'         => $purchase_currency,
                    'our_pack_size'             => ( $default_our_pack_size ? $default_our_pack_size : 1 ),
                    'total_pieces'              => 0,
                    'price'                     => $product_price,
                    'product_link'              => '',
                    'supplier_name'             => $supplier_name,
                    'supplier_id'               => $temp_data->supplier_id,
                    'product_title_without_url' => $temp_data->product_name,
                    'product_edit_url'          => '',
                    'product_class'             => $product_class,
                    'multi_supplier_class'      => '',
                    'supplier_name_list'        => '',
                    'temp_product'              => true,
                    'temp_product_class'        => 'temp_product_color',
                    'qty'                       => $temp_data->product_qty,
                    'product_price'             => $product_price,
                    'request_data'              => $temp_data->product_qty,
                    'po_price'                  => $product_price,
                    'all_supplier'              => $all_supplier,
                    'supplier_note'             => $supplier_note,
                );
            }
        }
        wp_reset_query();
        
        if ( is_array( $temporary_product ) && count( $temporary_product ) > 0 && $paged == 1 ) {
            $low_stock_data = array_merge( $temp_array, $data );
        } else {
            $low_stock_data = $data;
        }
        
        return $low_stock_data;
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
        if ( isset( $item['supplier_currency'] ) ) {
            $this->currency = $item['supplier_currency'];
        }
        switch ( $column_name ) {
            case 'thumb':
                return $item[$column_name] . sprintf(
                    '<input type="hidden" class="productId" name="productId[]" value="%s" /><div class="mobile_prod_info"><div>%s</div><div class="%s">%s</div></div>',
                    $item['id'],
                    $item['product_title_without_url'],
                    $item['product_class'] . '_product',
                    $item['product_variant']
                );
            case 'amount':
                $supplier = get_post_meta( $item['id'], 'oimwc_supplier_id', true );
                
                if ( !isset( $_REQUEST['supplier_id'] ) || isset( $_REQUEST['supplier_id'] ) && (sanitize_text_field( $_REQUEST['supplier_id'] ) == "all" || sanitize_text_field( $_REQUEST['supplier_id'] == 0 )) ) {
                    $qty_column = '<div class="tips" data-tip="' . __( 'Please select a supplier to enable the Create Purchase Order feature', 'order-and-inventory-manager-for-woocommerce' ) . '"><input type="text" class="arrived_qty_handler no_supplier" title="" disabled />';
                } else {
                    $cls = '';
                    $msg = '';
                    
                    if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() ) {
                        $cls = 'disabled_panel tips';
                        $msg = OIMWC_SILVER_UPGRDAE_NOTICE;
                    }
                    
                    $qty_column = '<div class="' . $cls . '" data-tip="' . $msg . '"><input type="text" class="arrived_qty_handler" data-stock="' . $item['product_stock'] . '" data-id="' . $item['id'] . '" data-warning="' . $item['warning_level'] . '" name="product[' . $item['id'] . '][qty]" value="' . $item['request_data'] . '" /></div>';
                    $qty_column .= '<input type="hidden" name="product[' . $item['id'] . '][stock]" value="' . $item['product_stock'] . '" /><input type="hidden" name="product[' . $item['id'] . '][supplier]" value="' . $supplier . '" />';
                    $qty_column .= '<div class="product_calc"><span data-price="' . $item['non_format_purchase_price'] . '" class="amount amount_' . $item['id'] . '">0</span> <span class="currency">' . $item['supplier_currency'] . '</span></div>';
                    $qty_column .= '<div class="' . $cls . '" data-tip="' . $msg . '"><input type="button" class="button btnAddItemToOrder" value="' . __( 'Add to order', 'order-and-inventory-manager-for-woocommerce' ) . '" /></div>';
                }
                
                return $qty_column;
            case 'product_info':
                $variant = ( isset( $item['product_variant'] ) && $item['product_variant'] == '' && $item['product_variant'] === FALSE ? '' : $item['product_variant'] );
                $variant = ( $variant && $variant != '-' ? sprintf( '<div>%s: %s</div>', __( 'Variant', 'order-and-inventory-manager-for-woocommerce' ), $variant ) : '' );
                return sprintf(
                    '<div><div>%1$s</div>%2$s<div class="product_sku">%3$s: %4$s</div><div>%5$s: %6$s</div></div>',
                    $item['product_name'],
                    $variant,
                    __( 'Product ID', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $item['product_sku'] ),
                    __( 'Low stock threshold', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $item['warning_level'] )
                );
                break;
            case 'supplier_info':
                $supplier_name = ( isset( $item['supplier_name'] ) && $item['supplier_name'] == '' && $item['supplier_name'] === FALSE ? '-' : $item['supplier_name'] );
                $purchase_price = ( isset( $item['purchase_price'] ) && $item['purchase_price'] == '' && $item['purchase_price'] === FALSE ? '-' : $item['purchase_price'] );
                $supplier_pack_size = ( isset( $item['supplier_pack_size'] ) && $item['supplier_pack_size'] == '' && $item['supplier_pack_size'] === FALSE ? '-' : $item['supplier_pack_size'] );
                //$url_text = ($item['product_supplier'] != '-') ? '<a href='.esc_url($item[ 'product_supplier' ]).' target="_blank">'.__('Link to product','order-and-inventory-manager-for-woocommerce').'</a>' : '-';
                $supplier_name_list = $item['supplier_name_list'];
                $supplier_class = $item['multi_supplier_class'];
                $tool_tip = '';
                if ( $supplier_name_list ) {
                    $supplier_name = $supplier_name_list;
                }
                $div_text = '';
                return sprintf(
                    '<div><div class="%8$s">%1$s: %2$s</div><div>%3$s: %4$s</div><div>%5$s: %6$s</div>%7$s</div>',
                    __( 'Supplier', 'order-and-inventory-manager-for-woocommerce' ),
                    $supplier_name . $tool_tip,
                    __( 'URL', 'order-and-inventory-manager-for-woocommerce' ),
                    $item['product_supplier'],
                    __( 'Purchase price', 'order-and-inventory-manager-for-woocommerce' ),
                    $purchase_price,
                    $div_text,
                    $supplier_class
                );
                break;
            case 'product_detail':
                $product_price = ( isset( $item['price'] ) && $item['price'] == '' && $item['price'] === FALSE ? '-' : $item['price'] );
                $our_pack_size = ( isset( $item['our_pack_size'] ) && $item['our_pack_size'] == '' && $item['our_pack_size'] === FALSE ? '-' : $item['our_pack_size'] );
                $total_pieces = ( isset( $item['total_pieces'] ) && $item['total_pieces'] == '' && $item['total_pieces'] === FALSE ? '-' : $item['total_pieces'] );
                $product_stock = ( isset( $item['product_stock'] ) && $item['product_stock'] == '' && $item['product_stock'] === FALSE ? '-' : (int) $item['product_stock'] );
                $request_item = ( isset( $item['request_item'] ) && $item['request_item'] == '' && $item['request_item'] === FALSE ? '-' : (int) $item['request_item'] );
                $div_text = '';
                $req_text = '';
                if ( $request_item ) {
                    $req_text = sprintf( ', %s %s', $request_item, __( 'in order', 'order-and-inventory-manager-for-woocommerce' ) );
                }
                return sprintf(
                    '<div><div>%1$s: %2$s</div>%3$s<div>%4$s: %5$s%6$s</div><div>%7$s: %8$s</div></div>',
                    __( 'Shop price', 'order-and-inventory-manager-for-woocommerce' ),
                    $product_price,
                    $div_text,
                    __( 'Items in stock', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $product_stock ),
                    $req_text,
                    __( 'Physical units in stock', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $total_pieces )
                );
                break;
            default:
                return print_r( $item, true );
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
        // Set defaults
        $orderby = 'product_name';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if ( !empty($_GET['orderby']) ) {
            $orderby = sanitize_text_field( $_GET['orderby'] );
        }
        // If order is set use this as the order
        if ( !empty($_GET['order']) ) {
            $order = sanitize_text_field( $_GET['order'] );
        }
        $result = strnatcmp( $a[$orderby], $b[$orderby] );
        if ( $order === 'asc' ) {
            return $result;
        }
        return -$result;
    }
    
    /**
     * Replaces single quotes from string
     *
     * @param mixed $sql
     */
    function closure( $sql )
    {
        return str_replace( "'mt1.meta_value'", "mt1.meta_value", $sql );
    }
    
    function single_row( $item )
    {
        echo  '<tr>' ;
        $this->single_row_columns( $item );
        echo  '</tr>' ;
        echo  '<tr><td colspan="5"><div class="table_seperator"></div></td></tr>' ;
    }
    
    function extra_tablenav( $which )
    {
        if ( $which == 'top' ) {
            return;
        }
        printf( '<div class="total_order_info_panel" style="display:none;">%s: <span class="amount">0</span> <span class="currency">%s</span></div>', __( 'Total', 'order-and-inventory-manager-for-woocommerce' ), $this->currency );
    }
    
    function no_items()
    {
        _e( 'Enter the amount you wish to order and press the "Update PO preview" button to move the product to this section.', 'order-and-inventory-manager-for-woocommerce' );
    }

}