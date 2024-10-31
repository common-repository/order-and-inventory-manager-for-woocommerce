<?php

/**
 * DataDeliveryTable class
 *
 * Handles products awaiting deliveries
 *
 * @since    1.0.0
 */
// WP_List_Table is not loaded automatically so we need to load it in our application
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
require_once ABSPATH . 'wp-admin/includes/template.php';
/**
 * DataDeliveryTable handles products awaiting deliveries
 */
class DataDeliveryTable extends WP_List_Table
{
    public  $total_pages ;
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
        //        usort( $data, array( &$this, 'sort_data' ) );
        global  $total_pagination_records ;
        $perPage = get_option( 'oimwc_per_page' );
        $this->set_pagination_args( array(
            'total_items' => $total_pagination_records,
            'per_page'    => ( $perPage ? $perPage : 25 ),
        ) );
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
            'order_info'     => __( 'Order Info', 'order-and-inventory-manager-for-woocommerce' ),
            'action'         => __( 'Process product', 'order-and-inventory-manager-for-woocommerce' ),
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
            'product_detail' => array( 'price', false ),
            'order_info'     => array( 'order_number', false ),
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
        global  $default_supplier, $wpdb, $total_pagination_records ;
        $data = array();
        wp_enqueue_style( 'woocommerce_admin_styles' );
        $supplier_id = ( isset( $_REQUEST['supplier_id'] ) ? sanitize_text_field( $_REQUEST['supplier_id'] ) : $default_supplier );
        $ordered_product = OIMWC_Order::get_ordered_product( $supplier_id );
        $default_our_pack_size = get_option( 'oimwc_default_our_pack_size' );
        $default_supplier_pack_size = get_option( 'oimwc_default_supplier_pack_size' );
        $requested_stock = '';
        
        if ( is_array( $ordered_product ) && count( $ordered_product ) ) {
            $total_pagination_records = count( $ordered_product );
            /*$perPage = get_option('oimwc_per_page');
              if(!$perPage){
                  $perPage = 25;
              }*/
            /*$paged = isset($_REQUEST['paged']) ? sanitize_text_field($_REQUEST['paged']) : 1 ;
              if($paged == 1){
                  $start=0;
                  $end=$perPage;
              }else{
                  $start=$perPage*($paged-1);
                  $end=$perPage*$paged;
              }*/
            $perPage = 20;
            $paged = 1;
            
            if ( !empty($_POST["page"]) ) {
                $perPage = 40;
                $paged = $_POST["page"];
            }
            
            $start = ($paged - 1) * $perPage;
            if ( $start > 1 ) {
                $start = $start - 20;
            }
            $table_name = $wpdb->prefix . 'order_inventory';
            $wp_posts = $wpdb->prefix . "posts";
            $total_pages = $this->get_total_pages_delivery_tbl( $supplier_id, $perPage );
            $this->total_pages = $total_pages;
            $order = ( isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : "ASC" );
            $orderby = ( !empty($_GET['orderby']) ? sanitize_text_field( $_GET['orderby'] ) : 'order_number' );
            $search = ( isset( $_POST['search_val'] ) ? $_POST['search_val'] : '' );
            if ( $search ) {
                $search = "AND {$wp_posts}.post_title LIKE '%{$search}%'";
            }
            $sql = "SELECT {$table_name}.* , {$wp_posts}.post_title\r\n                    FROM {$table_name}\r\n                    JOIN {$wp_posts} ON {$table_name}.product_id = {$wp_posts}.id\r\n                    WHERE supplier_id = {$supplier_id} and finalize_product = 0\r\n                    {$search}\r\n                    AND {$wp_posts}.post_status IN ('publish','private')\r\n                    ORDER BY {$orderby} {$order} LIMIT {$start},{$perPage}";
            $product_list = $wpdb->get_results( $sql );
            $sql_count = "SELECT {$table_name}.* , {$wp_posts}.post_title\r\n                    FROM {$table_name}\r\n                    JOIN {$wp_posts} ON {$table_name}.product_id = {$wp_posts}.id\r\n                    WHERE supplier_id = {$supplier_id} and finalize_product = 0\r\n                    {$search}\r\n                    AND {$wp_posts}.post_status IN ('publish','private')\r\n                    ORDER BY {$orderby} {$order}";
            $product_count = $wpdb->get_results( $sql_count );
            $this->total_supplier_product_low_stock = count( $product_count );
            if ( $product_list ) {
                foreach ( $product_list as $product_row ) {
                    $id = $product_row->product_id;
                    $product = get_post( $id );
                    $product_supplier = get_post_meta( $id, 'oimwc_supplier_product_url', true );
                    $product_stock = get_post_meta( $id, '_stock', true );
                    $product_sku = get_post_meta( $id, '_sku', true );
                    $warning_level = get_post_meta( $id, 'oimwc_low_stock_threshold_level', true );
                    $purchase_price = wc_format_decimal( get_post_meta( $id, 'oimwc_supplier_purchase_price', true ) );
                    $purchase_currency = get_post_meta( $supplier_id, 'oimwc_supplier_currency', true );
                    $purchase_price = ( $purchase_price ? $purchase_price : 0 );
                    $product_type = $product->post_type;
                    $product_title = ( $product_type == 'product' ? get_the_title( $product->ID ) : get_the_title( $product->post_parent ) );
                    $actual_product_id = ( $product_type == 'product' ? $product->ID : $product->post_parent );
                    $product_variant = '-';
                    $order_supplier_pack_size = get_post_meta( $id, 'oimwc_supplier_pack_size', true );
                    $our_pack_size = get_post_meta( $id, 'oimwc_our_pack_size', true );
                    $our_pack = ( $our_pack_size ? $our_pack_size : (( $default_our_pack_size ? $default_our_pack_size : 1 )) );
                    $supplier_remaining_pieces = (int) get_post_meta( $id, 'oimwc_supplier_remaining_pieces', true );
                    $total_pieces = (int) get_post_meta( $id, 'oimwc_physical_units_stock', true );
                    $image = '<img width="40" height="40" src="' . plugins_url( '/woocommerce/assets/images/placeholder.png' ) . '" />';
                    $product = wc_get_product( $id );
                    $price = $product->get_price();
                    
                    if ( $product_type == 'product_variation' ) {
                        if ( !$product_sku ) {
                            $product_sku = get_post_meta( $product->post_parent, '_sku', true );
                        }
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
                    
                    $order_number_link = admin_url() . 'admin.php?page=order-inventory-management';
                    $order_number_link = add_query_arg( array(
                        'subpage'    => 'purchase-orders',
                        'view_order' => 1,
                        'supplier'   => $supplier_id,
                        'date'       => strtotime( $product_row->order_date ),
                    ), $order_number_link );
                    $order_number_link = sprintf( '<a href="%s">%s</a>', $order_number_link, $product_row->order_number );
                    $order_supplier_pack_size = ( $order_supplier_pack_size ? $order_supplier_pack_size : $default_supplier_pack_size );
                    $ordered_pieces = $order_supplier_pack_size * $product_row->requested_stock;
                    $ordered_pieces_str = '';
                    $ordered_pieces_str .= sprintf(
                        __( 'Ordered: %d × %d-packs at %s %0.2f', 'order-and-inventory-manager-for-woocommerce' ),
                        $requested_stock,
                        $order_supplier_pack_size,
                        $purchase_currency,
                        number_format( $purchase_price / $order_supplier_pack_size, 2 )
                    );
                    $total = $product_row->requested_stock * $purchase_price;
                    $total = $purchase_currency . ' ' . number_format( $total, 2 );
                    $requested_stock = $product_row->requested_stock;
                    $arrvived_stock = $product_row->arrvived_stock;
                    $arrvived_stock = ( $arrvived_stock ? $arrvived_stock : 0 );
                    $arrvived_stock_text = "<span class='arrived_stock_count'> {$arrvived_stock} </span> / <span class='requested_stock_count'>{$requested_stock}</span>";
                    
                    if ( $product_row->finalize_product == 1 ) {
                        $arrvived_stock_text .= ' - ' . __( 'Finalized', 'order-and-inventory-manager-for-woocommerce' );
                    } else {
                        if ( $product_row->finalize_product == 2 ) {
                            $arrvived_stock_text .= ' - ' . __( 'Fully Arrived', 'order-and-inventory-manager-for-woocommerce' );
                        }
                    }
                    
                    $arrival_status = sprintf( '<div class="arrival_stock_txt"><strong>%s: %s</strong></div>', __( 'Arrived', 'order-and-inventory-manager-for-woocommerce' ), $arrvived_stock_text );
                    $disabled = ( $product_row->finalize_product >= 1 ? 'disabled' : '' );
                    $hide = ( $product_row->lock_product ? 'hide' : '' );
                    $product_title_url = sprintf( '<a href="%s" >%s</a>', get_edit_post_link( $actual_product_id ), $product_title );
                    $supplier_product_link = sprintf( '<a target="_blank" href="%s" >%s</a>', $product_supplier, __( 'Link to product', 'order-and-inventory-manager-for-woocommerce' ) );
                    $data[] = array(
                        'id'                        => $id,
                        'table_id'                  => $product_row->id,
                        'thumb'                     => $image,
                        'thumb_url'                 => ( !empty($thumb_url) ? $thumb_url : plugins_url( '/woocommerce/assets/images/placeholder.png' ) ),
                        'product_name'              => $product_title_url,
                        'product_title_without_url' => $product_title,
                        'product_sku'               => $product_sku,
                        'product_variant'           => $product_variant,
                        'product_supplier'          => ( !empty($product_supplier) ? $supplier_product_link : '-' ),
                        'full_order_date'           => $product_row->order_date,
                        'order_supplier_pack_size'  => $order_supplier_pack_size,
                        'our_pack_size'             => $our_pack,
                        'product_stock'             => ( $product_stock ? (int) $product_stock : 0 ),
                        'total_pieces'              => ( intval( $total_pieces ) > 0 ? $total_pieces : 0 ),
                        'price'                     => wc_price( $price ),
                        'product_link'              => get_edit_post_link( $actual_product_id ),
                        'arrival_date'              => ( $product_row->arrival_date != '' ? date_i18n( get_option( 'date_format' ), strtotime( $product_row->arrival_date ) ) : __( 'Unknown', 'order-and-inventory-manager-for-woocommerce' ) ),
                        'finalize_product'          => $product_row->finalize_product,
                        'lock_product'              => $product_row->lock_product,
                        'order_number_link'         => $order_number_link,
                        'ordered_pieces_str'        => $ordered_pieces_str,
                        'total'                     => $total,
                        'po_date'                   => date_i18n( get_option( 'date_format' ), strtotime( $product_row->order_date ) ),
                        'arrival_status'            => $arrival_status,
                        'supplier_id'               => $supplier_id,
                        'disabled'                  => $disabled,
                        'hide'                      => $hide,
                        'product_class'             => $product->get_type(),
                    );
                }
            }
            wp_reset_query();
        }
        
        return $data;
    }
    
    public function get_total_pages_delivery_tbl( $supplier_id, $perPage )
    {
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'order_inventory';
        $wp_posts = $wpdb->prefix . "posts";
        $sql = "SELECT {$table_name}.*\r\n                FROM {$table_name}\r\n                JOIN {$wp_posts} ON {$table_name}.product_id = {$wp_posts}.id\r\n                WHERE supplier_id = {$supplier_id} and finalize_product = 0\r\n                AND {$wp_posts}.post_status IN ('publish','private')";
        $data_result = $wpdb->get_col( $sql );
        $total_records = count( $data_result ) - $perPage;
        $total_pages = ceil( $total_records / 40 ) + 1;
        return $total_pages;
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
        global  $wpdb, $default_supplier ;
        $supplier_id = ( isset( $_REQUEST['supplier_id'] ) ? sanitize_text_field( $_REQUEST['supplier_id'] ) : $default_supplier );
        $supplier_pack_size = ( isset( $item['order_supplier_pack_size'] ) && $item['order_supplier_pack_size'] == '' && $item['order_supplier_pack_size'] === FALSE ? '-' : $item['order_supplier_pack_size'] );
        $requested_stock = ( isset( $item['requested_stock'] ) ? $item['requested_stock'] : 0 );
        $purchase_price = ( isset( $item['purchase_price'] ) ? $item['purchase_price'] : 0 );
        $purchase_currency = get_post_meta( $supplier_id, 'oimwc_supplier_currency', true );
        $order_supplier_pack_size = $item['order_supplier_pack_size'];
        switch ( $column_name ) {
            case 'thumb':
                return $item[$column_name] . sprintf(
                    '<div class="mobile_prod_info"><div>%s</div><div class="%s">%s</div></div>',
                    $item['product_title_without_url'],
                    $item['product_class'] . '_product',
                    $item['product_variant']
                );
            case 'action':
                $disabled = ( $item['finalize_product'] >= 1 ? 'disabled' : '' );
                $action = '';
                $action .= '<input type="text" placeholder="' . __( 'Qty', 'order-and-inventory-manager-for-woocommerce' ) . '" class="arrived_qty_handler" data-id="' . $item['table_id'] . '" ' . $disabled . '  />
    					<input type="button" data-id="' . $item['table_id'] . '" data-page="delivery_page" class="btnOrderSave button tips" data-tip="' . __( 'Update stock with entered qty', 'order-and-inventory-manager-for-woocommerce' ) . '" value="' . __( 'Arrived', 'order-and-inventory-manager-for-woocommerce' ) . '" ' . $disabled . ' />
    					<input type="button" data-product_id="' . $item['id'] . '" data-id="' . $item['table_id'] . '" class="btnOrderFullyArrived button tips" data-tip="' . __( 'Update stock with ordered qty', 'order-and-inventory-manager-for-woocommerce' ) . '" data-page="delivery_page" value="' . __( 'Fully arrived', 'order-and-inventory-manager-for-woocommerce' ) . '" ' . $disabled . ' />';
                $action .= '   <input type="button" data-product_id="' . $item['id'] . '" data-id="' . $item['table_id'] . '" data-supplier_id = "' . $supplier_id . '" data-order_date = "' . $item['full_order_date'] . '" class="btnFinalizeProduct button tips" data-tip="' . __( 'No further qty of this product will arrive in this shipment', 'order-and-inventory-manager-for-woocommerce' ) . '" data-page="delivery_page" value="' . __( 'Finalize', 'order-and-inventory-manager-for-woocommerce' ) . '" ' . $disabled . ' /><br/>';
                $hide = ( $item['lock_product'] ? 'hide' : '' );
                $action .= '<input type="button" data-product_id="' . $item['id'] . '" data-id="' . $item['table_id'] . '" class="btnRemoveProduct button tips ' . $hide . '" data-tip="' . __( 'Remove product from purchase order', 'order-and-inventory-manager-for-woocommerce' ) . '" data-page="delivery_page" value="' . __( 'Remove', 'order-and-inventory-manager-for-woocommerce' ) . '" />';
                return $action;
            case 'product_info':
                $variant = ( isset( $item['product_variant'] ) && $item['product_variant'] == '' && $item['product_variant'] === FALSE ? '' : $item['product_variant'] );
                $variant = ( $variant && $variant != '-' ? sprintf( '<div>%s: %s</div>', __( 'Variant', 'order-and-inventory-manager-for-woocommerce' ), $variant ) : '' );
                return sprintf(
                    '<div><div>%1$s</div>%2$s<div class="product_sku">%3$s: %4$s</div></div>',
                    $item['product_name'],
                    $variant,
                    __( 'Product ID', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $item['product_sku'] )
                );
                break;
            case 'supplier_info':
                //$url_text = ($item[ 'product_supplier' ]) ? '<a href='.esc_url($item[ 'product_supplier' ]).' target="_blank">'.__('Link to product','order-and-inventory-manager-for-woocommerce').'</a>' : '-';
                $div_text = '';
                return sprintf(
                    '<div><div>%1$s: %2$s</div>%3$s</div>',
                    __( 'URL', 'order-and-inventory-manager-for-woocommerce' ),
                    $item['product_supplier'],
                    $div_text
                );
                break;
            case 'product_detail':
                $product_price = ( isset( $item['price'] ) && $item['price'] == '' && $item['price'] === FALSE ? '-' : $item['price'] );
                $our_pack_size = ( isset( $item['our_pack_size'] ) && $item['our_pack_size'] == '' && $item['our_pack_size'] === FALSE ? '-' : $item['our_pack_size'] );
                $total_pieces = ( isset( $item['total_pieces'] ) && $item['total_pieces'] == '' && $item['total_pieces'] === FALSE ? '-' : $item['total_pieces'] );
                $product_stock = ( isset( $item['product_stock'] ) && $item['product_stock'] == '' && $item['product_stock'] === FALSE ? '-' : (int) $item['product_stock'] );
                $div_text = '';
                return sprintf(
                    '<div><div>%1$s: %2$s</div>%3$s<div>%4$s: <span class="items_in_stock">%5$s</span></div><div>%6$s: <span class="units_in_stock">%7$s</span></div></div>',
                    __( 'Shop price', 'order-and-inventory-manager-for-woocommerce' ),
                    $product_price,
                    $div_text,
                    __( 'Items in stock', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $product_stock ),
                    __( 'Physical units in stock', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $total_pieces )
                );
                break;
            case 'order_info':
                return sprintf(
                    '<div><div>%1$s: %2$s</div><div>%3$s</div><div>%4$s: %5$s</div><div>%6$s: %7$s</div><div>%8$s: %9$s</div>%10$s</div>',
                    __( 'Order No.', 'order-and-inventory-manager-for-woocommerce' ),
                    $item['order_number_link'],
                    sprintf(
                    __( 'Ordered: %d × %d-packs at %s %0.2f', 'order-and-inventory-manager-for-woocommerce' ),
                    $requested_stock,
                    $order_supplier_pack_size,
                    $purchase_currency,
                    number_format( $purchase_price / $order_supplier_pack_size, 2 )
                ),
                    __( 'Total', '  order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $item['total'] ),
                    __( 'PO date', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $item['po_date'] ),
                    __( 'ETA', 'order-and-inventory-manager-for-woocommerce' ),
                    esc_html( $item['arrival_date'] ),
                    $item['arrival_status']
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
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if ( $order === 'asc' ) {
            return $result;
        }
        return $result;
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
        echo  '<tr><td colspan="6"><div class="table_seperator"></div></td></tr>' ;
    }

}