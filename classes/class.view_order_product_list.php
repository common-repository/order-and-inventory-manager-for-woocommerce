<?php
/**
 * ViewOrderTable class
 *
 * List all products that order contains
 *
 * @since    1.0.0
 */
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * ViewOrderTable list all products that order contains
 */
class ViewOrderTable extends WP_List_Table
{
    public $total_pages;
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
        
        $this->_column_headers = array($columns, $hidden, $sortable);
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
            'thumb' => '<span class="wc-image tips" data-product="'.__('Products','order-and-inventory-manager-for-woocommerce').'" data-tip="' . esc_attr__('Image', 'woocommerce') . '">' . __('Image', 'woocommerce'),
            'product_info' =>  __('Product Info', 'order-and-inventory-manager-for-woocommerce' ),
            'supplier_info' => __('Supplier Info', 'order-and-inventory-manager-for-woocommerce' ),
            'product_detail' => __('Product Price & Stock', 'order-and-inventory-manager-for-woocommerce' ),
            'order_info' => __('Order Info','order-and-inventory-manager-for-woocommerce')
        );
       
        if($this->get_order_status() == 'active'){
          $columns['action'] = __('Process product', 'order-and-inventory-manager-for-woocommerce' );
        }
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
        return array('product_name' => array('product_name', true));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    public function table_data()
    {
    		global $default_supplier, $wpdb;
        $data = array();
        wp_enqueue_style('woocommerce_admin_styles');
        $supplier_id = isset( $_REQUEST['supplier'] ) ? sanitize_text_field($_REQUEST['supplier']) : $default_supplier;
        $default_our_pack_size = get_option('oimwc_default_our_pack_size');
        $default_supplier_pack_size = get_option('oimwc_default_supplier_pack_size');
        $thumb_url = '';
        /*$perPage = get_option('oimwc_per_page');
        if(!$perPage){
            $perPage = 25;
        }
        $paged = isset($_REQUEST['paged']) ? sanitize_text_field($_REQUEST['paged']) : 1 ;
        if($paged == 1){
            $start=0;
            $end=$perPage;
        }else{
            $start=$perPage*($paged-1);
            $end=$perPage*$paged;
        } */
        
        $perPage = 20;
        $paged  = 1;
        if(!empty($_POST["page"])) {
          $perPage = 40;
          $paged = $_POST["page"];
        }
        $start = ($paged - 1) * $perPage;
        if( $start > 1 ){
          $start = $start - 20;
        }

        /*if( isset($_POST['offset']) ){
          $start = $_POST['offset'];
        }*/
        $table_name = $wpdb->prefix.'order_inventory';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : "ASC" ;
        $wp_posts = $wpdb->prefix."posts";
        $order_date = date('Y-m-d H:i:s', sanitize_text_field($_REQUEST['date']));

        $main_obj = OIMWC_MAIN::init();
        $po_status = $main_obj->get_purchase_order_status($supplier_id, $order_date, false);

        $total_pages = $this->get_total_pages($supplier_id,$order_date,$perPage);
        $this->total_pages = $total_pages;

        $sql = "SELECT {$table_name}.* , $wp_posts.post_title FROM {$table_name} JOIN {$wp_posts} ON {$table_name}.product_id = {$wp_posts}.id WHERE supplier_id = {$supplier_id} AND order_date = '{$order_date}' AND {$wp_posts}.post_status IN ('publish','private') AND {$table_name}.temp_product = 0 ORDER BY {$table_name}.id DESC LIMIT {$start},{$perPage}";  
        $product_list = $wpdb->get_results( $sql );
        if( $product_list ){
          foreach( $product_list as $product_row ){
            $id = $product_row->product_id;
            $product = get_post( $id );
            $product_supplier   = get_post_meta( $id, 'oimwc_supplier_product_url', true );
            $product_stock    = get_post_meta( $id, '_stock', true );
            $product_sku    = get_post_meta( $id, '_sku', true );
            $warning_level    = get_post_meta( $id, 'oimwc_low_stock_threshold_level', true );
            $purchase_price   = wc_format_decimal(get_post_meta( $id, 'oimwc_supplier_purchase_price', true ));
            $purchase_currency  = get_post_meta( $supplier_id, 'oimwc_supplier_currency', true );
            $purchase_price   = $purchase_price ? $purchase_price : 0;
            $product_type   = $product->post_type;
            $product_title    = $product_type == 'product' ? get_the_title($product->ID) : get_the_title( $product->post_parent );
            $actual_product_id  = $product_type == 'product' ? $product->ID : $product->post_parent;
            $product_variant  = '-';  
            $order_supplier_pack_size = get_post_meta( $id, 'oimwc_supplier_pack_size', true );
            $our_pack_size      = get_post_meta( $id, 'oimwc_our_pack_size', true );
            $supplier_product_id = get_post_meta( $id, 'oimwc_supplier_product_id', true );
            $supplier_note = get_post_meta( $id, 'oimwc_supplier_note', true );
            $additional_supplier_info = oimwc_additional_supplier_details_from_product( $id, $supplier_id );
            if( is_array( $additional_supplier_info ) && count( $additional_supplier_info ) ){
                $order_supplier_pack_size = $additional_supplier_info['pack_size'];
                $purchase_price     = wc_format_decimal($additional_supplier_info['purchase_price']);
                $purchase_price     = $purchase_price ? $purchase_price : 0;
                $product_supplier   = $additional_supplier_info['supplier_product_url'];
            }
            $our_pack = $our_pack_size ? $our_pack_size : ($default_our_pack_size ? $default_our_pack_size : 1);
            $supplier_remaining_pieces = (int)get_post_meta($id, 'oimwc_supplier_remaining_pieces', true);
            $total_pieces = (int)get_post_meta($id, 'oimwc_physical_units_stock', true);
            $image        = '<img width="40" height="40" src="'.plugins_url('/woocommerce/assets/images/placeholder.png' ).'" />';
            $product      = wc_get_product( $id );
            if( $product instanceof WC_Product )
            {
              $price = $product->get_price();
            }else{
              $price = '';
            }
            if( $product_type == 'product_variation' ){
              if( !$product_sku ){
                $product_sku  = get_post_meta( $product->post_parent, '_sku', true ); 
              }
              $product = new WC_Product_Variation( $id ); 
              $product_variant = $product->get_variation_attributes();
              if (is_array($product_variant) && count($product_variant)) {
                $variation_names = array();
                foreach ($product_variant as $key=>$value) {
                  $term = get_term_by('slug', $value, str_replace("attribute_","", $key) );
                  if(!$term){
                      $variation_names[] = $value;
                  }else{
                      $variation_names[] = $term->name;
                  }
                }
                $product_variant = implode(' | ', $variation_names );
              } else {
                $product_variant = '-';
              }
              if( has_post_thumbnail( $id ) ){
                $image = get_the_post_thumbnail($id, array(75,75)); 
                if(!file_exists($image)){
                  $image = get_the_post_thumbnail($id, array(100,100));
                }
                $thumb_url = get_the_post_thumbnail_url($id, array(75,75)); 
                if(!file_exists($thumb_url)){
                  $thumb_url = get_the_post_thumbnail_url($id, array(100,100));
                }
              }else{
                if( has_post_thumbnail( $product->get_parent_id() ) ){
                  $image = get_the_post_thumbnail($product->get_parent_id(), array(75,75)); 
                  if(!file_exists($image)){
                    $image = get_the_post_thumbnail($product->get_parent_id(), array(100,100));
                  }
                  $thumb_url =get_the_post_thumbnail_url($product->get_parent_id(),array(75,75));
                  if(!file_exists($thumb_url)){
                    $thumb_url = get_the_post_thumbnail_url($product->get_parent_id(), array(100,100));
                  }
                }
              }
              $price  = $product->get_price();
            }else{
              if( has_post_thumbnail( $id ) ){
                $image = get_the_post_thumbnail($id, array(75,75)); 
                if(!file_exists($image)){
                  $image = get_the_post_thumbnail($id, array(100,100));
                }
                $thumb_url = get_the_post_thumbnail_url($id, array(75,75));
                if(!file_exists($thumb_url)){
                  $thumb_url = get_the_post_thumbnail_url($id, array(100,100));
                }
              } 
            }
            $order_supplier_pack_size = $order_supplier_pack_size ? $order_supplier_pack_size : $default_supplier_pack_size;
            $ordered_pieces = $order_supplier_pack_size * $product_row->requested_stock;
            $ordered_pieces_str = '';
            $ordered_pieces_str .= sprintf(__('Ordered: %d × %d-packs at %s %0.2f','order-and-inventory-manager-for-woocommerce'),$product_row->requested_stock,$order_supplier_pack_size,$purchase_currency,(number_format($purchase_price / $order_supplier_pack_size,2)));

            $total = $product_row->requested_stock * $purchase_price;
            $total = $purchase_currency .' '.number_format($total,2);

            $requested_stock =  $product_row->requested_stock;
            $arrvived_stock  =  $product_row->arrvived_stock;
            $arrvived_stock  =  $arrvived_stock ? $arrvived_stock : 0;
            $arrvived_stock_text = "<span class='arrived_stock_count'> $arrvived_stock </span> / <span class='requested_stock_count'>$requested_stock</span>";
            if($product_row->finalize_product == 1){
              $arrvived_stock_text .= ' - '.__('Finalized','order-and-inventory-manager-for-woocommerce');
            }
            else if($product_row->finalize_product == 2){
              $arrvived_stock_text .= ' - '.__('Fully Arrived','order-and-inventory-manager-for-woocommerce');
            }
            $arrival_status = sprintf('<div class="arrival_stock_txt"><strong>%s: %s</strong></div>',__('Arrived','order-and-inventory-manager-for-woocommerce'), $arrvived_stock_text );

            $disabled = ($product_row->finalize_product >= 1) ? 'disabled' : '';
            
            $hide = ($product_row->finalize_product >= 1) ? 'hide' : '';
            $lock_product_hide = ($product_row->lock_product) ? 'hide' : '';

            $product_title_url = sprintf( '<a href="%s" >%s</a>', get_edit_post_link( $actual_product_id ), $product_title);
            $supplier_product_link = sprintf( '<a target="_blank" href="%s" >%s</a>', $product_supplier, __('Link to product','order-and-inventory-manager-for-woocommerce'));
            if( $product instanceof WC_Product )
            {
              $product_type = $product->get_type();
            }else{
              $product_type = '';
            }
            $data[] = array(
                    'id'        =>  $id,
                    'table_id'      =>  $product_row->id,
                    'thumb'       =>  $image,
                    'thumb_url' => (!empty($thumb_url)) ? $thumb_url : plugins_url('/woocommerce/assets/images/placeholder.png'),
                    'product_name'    =>  $product_title_url,
                    'product_title_without_url' => $product_title,
                    'product_sku'   =>  $product_sku,
                    'product_variant' =>  $product_variant,
                    'product_supplier' => !empty($product_supplier) ? $supplier_product_link : '',
                    'order_date'    =>  date('Y-m-d', strtotime($product_row->order_date)),
                    'order_qty'     =>  $product_row->requested_stock,
                    'order_supplier_pack_size'     =>$order_supplier_pack_size ? $order_supplier_pack_size : $default_supplier_pack_size,
                    'our_pack_size' => $our_pack,
                    'product_stock'   =>  $product_stock ? (int)$product_stock : 0,
                    'total_pieces'=>intval( $total_pieces ) > 0 ? $total_pieces : 0,
                    'price'     => wc_price($price),

                    'product_link'  => get_edit_post_link( $actual_product_id ),
                    'arrival_date'  => ($product_row->arrival_date != '' && $product_row->arrival_date != 'NULL') ? $product_row->arrival_date : __('Unknown','order-and-inventory-manager-for-woocommerce'),
                    'requested_stock' => $product_row->requested_stock,
                    'arrvived_stock' => $product_row->arrvived_stock,
                    'finalize_product' => $product_row->finalize_product,
                    'purchase_price' => $purchase_price,
                    'purchase_currency' => $purchase_currency,
                    'order_number' => $product_row->order_number,
                    'lock_product' => $product_row->lock_product,
                    'product_class' => $product_type,
                    'po_type' => $po_status,
                    'ordered_pieces_str' => $ordered_pieces_str,
                    'total' => $total,
                    'arrival_status' => $arrival_status,
                    'disabled' => $disabled,
                    'hide' => $hide,
                    'lock_product_hide' => $lock_product_hide,
                    'order_supplier_pack_size' => $order_supplier_pack_size,
                    'temp_product' => 0,
                    'supplier_product_id' => $supplier_product_id,
                    'supplier_note' => $supplier_note
                  );
          }
        }
        $temporary_product = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."order_inventory as WOI LEFT JOIN ".$wpdb->prefix ."oimwc_temp_product as WPO ON ( WOI.product_id = WPO.id ) WHERE WOI.supplier_id = ".$supplier_id." AND WPO.order_id = WOI.id AND WOI.temp_product = 1 AND WOI.order_date = '".$order_date."'");
        if(count($temporary_product) > 0){
          foreach ($temporary_product as $key => $temp_data) {
              $image = '<img width="40" height="40" src="'.plugins_url('/woocommerce/assets/images/placeholder.png' ).'" />';
              $supplier_name = oiwmc_get_supplier_with_link( $temp_data->supplier_id );
              $supplier_product_link = (!empty($temp_data->product_url)) ? sprintf( '<a target="_blank" href="%s" >%s</a>', $temp_data->product_url, __('Link to product','order-and-inventory-manager-for-woocommerce')) : '';
              $price = (!empty($temp_data->product_price)) ? sanitize_text_field($temp_data->product_price) : 0;
              $our_pack = $default_our_pack_size ? $default_our_pack_size : 1;
              $total_pieces = floor( $temp_data->product_qty * $our_pack );
              $product_class = $temp_data->variation_name ? '' : 'simple';
              $purchase_currency = get_post_meta($temp_data->supplier_id,'oimwc_supplier_currency',true);
              $oimwc_supplier_pack_size = (!empty($temp_data->supplier_pack_size)) ? $temp_data->supplier_pack_size : 1;
              $supplier_note = (!empty($temp_data->supplier_notes)) ? $temp_data->supplier_notes : '';

              $temp_array[] = array(
                  'id'        =>  $temp_data->id,
                  'table_id'      =>  $temp_data->order_id,
                  'thumb'       =>  $image,
                  'thumb_url' => plugins_url('/woocommerce/assets/images/placeholder.png'),
                  'product_name'    =>  $temp_data->product_name,
                  'product_title_without_url' => $temp_data->product_name,
                  'supplier_product_id' =>  $temp_data->supplier_product_id ? $temp_data->supplier_product_id : '',
                  'product_variant' =>  $temp_data->variation_name,
                  'product_supplier' => $supplier_product_link,
                  'order_date'    =>  date('Y-m-d', strtotime($product_row->order_date)),
                  'order_qty'     =>  $temp_data->requested_stock,
                  'order_supplier_pack_size' => $oimwc_supplier_pack_size,
                  'our_pack_size' => $default_our_pack_size ? $default_our_pack_size : 1,
                  'product_stock'   =>  0,
                  'total_pieces'=>0,
                  'price'     => '-',
                  'product_link'  => '',
                  'arrival_date'  => ($temp_data->arrival_date != '' && $temp_data->arrival_date != 'NULL') ? $temp_data->arrival_date : __('Unknown','order-and-inventory-manager-for-woocommerce'),
                  'requested_stock' => $temp_data->product_qty,
                  'arrvived_stock' => $temp_data->arrvived_stock,
                  'finalize_product' => $temp_data->finalize_product,
                  'purchase_price' => $price,
                  'purchase_currency' => $purchase_currency,
                  'order_number' => $temp_data->order_number,
                  'lock_product' => $temp_data->lock_product,
                  'product_class' => $product_class,
                  'po_type' => $po_status,
                  'ordered_pieces_str' => $ordered_pieces_str,
                  'total' => $total,
                  'arrival_status' => $arrival_status,
                  'disabled' => $disabled,
                  'hide' => $hide,
                  'lock_product_hide' => $lock_product_hide,
                  'temp_product' => 1,
                  'supplier_note' => $supplier_note
              );
          }
        }
        wp_reset_query();
        if(count($temporary_product) > 0 && count($product_list) == 0 && $paged == 1){
          $low_stock_data = $temp_array;
        }if(count($temporary_product) > 0 && count($product_list) > 0 && $paged == 1){
          $low_stock_data = array_merge($temp_array, $data);
        }
        if(count($temporary_product) == 0 && count($product_list) > 0){
          $low_stock_data = $data;
        }
        return $low_stock_data;
    }

    public function get_order_status(){
      global $wpdb;
      $table_name = $wpdb->prefix.'order_inventory';
      $order_date = date('Y-m-d H:i:s', sanitize_text_field($_REQUEST['date']));
      $supplier_id = sanitize_text_field($_REQUEST['supplier']);
      $order_status = $wpdb->get_var("SELECT completed_order from $table_name where supplier_id = {$supplier_id} and order_date = '{$order_date}'");
      if($order_status){
          $order_status = 'finalized';
      }
      else{
          $order_status = 'active';
      }
      return $order_status;

    }

    public function get_total_pages($supplier_id,$order_date,$perPage) {
      global $wpdb;

      $table_name = $wpdb->prefix.'order_inventory';
      $posts_table = $wpdb->prefix. 'posts';
      
      $sql = "SELECT COUNT(*) FROM {$table_name} AS WOI
      LEFT JOIN {$posts_table} as WPO ON ( WOI.product_id = WPO.ID )
      WHERE supplier_id = {$supplier_id} AND order_date = '{$order_date}' AND WPO.post_status IN ('publish','private')";
      
      $data_result = $wpdb->get_var($sql);

      $total_records = $data_result - $perPage;
      $total_pages = ceil( $total_records / 40) + 1;
      
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
        $supplier_pack_size = isset( $item[ 'order_supplier_pack_size' ] ) && $item[ 'order_supplier_pack_size' ] == '' && $item[ 'order_supplier_pack_size' ] === FALSE ? '-' : $item[ 'order_supplier_pack_size' ];
        switch( $column_name ) {
            case 'thumb':
            
          return $item[ $column_name ] . sprintf('<div class="mobile_prod_info"><div>%s</div><div class="%s">%s</div></div>',
                    $item['product_title_without_url'],
                    $item['product_class'].'_product',
                    $item[ 'product_variant' ]
                  );
      case 'action':
          $action = '';
          $hide = ($item['finalize_product'] >= 1) ? 'hide' : '';
          if($item['temp_product'] == 1){
            $action .= '<input type="text" placeholder="'.__('Qty','order-and-inventory-manager-for-woocommerce').'" class="arrived_qty_handler '.$hide.'" data-id="'.$item['table_id'].'" />
              <input type="button" data-id="'.$item['table_id'].'" class="btnOrderSave button '.$hide.' tips" data-tip="'.__('Update stock with entered qty','order-and-inventory-manager-for-woocommerce').'" value="'.__('Arrived','order-and-inventory-manager-for-woocommerce').'"/>
              <input type="button" data-product_id="'.$item['id'].'" data-id="'.$item['table_id'].'" class="btnOrderFullyArrived button '.$hide.' tips" data-tip="'.__('Update stock with ordered qty','order-and-inventory-manager-for-woocommerce').'" value="'.__('Fully arrived','order-and-inventory-manager-for-woocommerce').'" />';
            $action .= '<input type="button" data-product_id="'.$item['id'].'" data-id="'.$item['table_id'].'" data-requested_stock="'.$item['requested_stock'].'" data-arrvived_stock="'.$item['arrvived_stock'].'" class="btnFinalizeProduct button '.$hide.' tips" data-tip="'.__('No further qty of this product will arrive in this shipment','order-and-inventory-manager-for-woocommerce').'" value="'.__('Finalize','order-and-inventory-manager-for-woocommerce').'" />';
            if($item['lock_product']){
              $hide = 'hide';
            }
            $action .= '<input type="button" data-id="'.$item['id'].'" class="btnTempRemoveProduct button '.$hide.' tips" data-tip="'.__('Remove product from purchase order','order-and-inventory-manager-for-woocommerce').'" value="'.__('Remove','order-and-inventory-manager-for-woocommerce').'" />';
          }else{
            $action .= '<input type="text" placeholder="'.__('Qty','order-and-inventory-manager-for-woocommerce').'" class="arrived_qty_handler '.$hide.'" data-id="'.$item['table_id'].'" />
              <input type="button" data-id="'.$item['table_id'].'" class="btnOrderSave button '.$hide.' tips" data-tip="'.__('Update stock with entered qty','order-and-inventory-manager-for-woocommerce').'" value="'.__('Arrived','order-and-inventory-manager-for-woocommerce').'"/>
              <input type="button" data-product_id="'.$item['id'].'" data-id="'.$item['table_id'].'" class="btnOrderFullyArrived button '.$hide.' tips" data-tip="'.__('Update stock with ordered qty','order-and-inventory-manager-for-woocommerce').'" value="'.__('Fully arrived','order-and-inventory-manager-for-woocommerce').'" />';
            $action .= '<input type="button" data-product_id="'.$item['id'].'" data-id="'.$item['table_id'].'" data-requested_stock="'.$item['requested_stock'].'" data-arrvived_stock="'.$item['arrvived_stock'].'" class="btnFinalizeProduct button '.$hide.' tips" data-tip="'.__('No further qty of this product will arrive in this shipment','order-and-inventory-manager-for-woocommerce').'" value="'.__('Finalize','order-and-inventory-manager-for-woocommerce').'" />';
            if($item['lock_product']){
              $hide = 'hide';
            }
            $action .= '<input type="button" data-product_id="'.$item['id'].'" data-id="'.$item['table_id'].'" class="btnRemoveProduct button '.$hide.' tips" data-tip="'.__('Remove product from purchase order','order-and-inventory-manager-for-woocommerce').'" value="'.__('Remove','order-and-inventory-manager-for-woocommerce').'" />';
          }

          return $action;
      break;

      case 'product_info':

        $variant = isset( $item[ 'product_variant' ] ) && $item[ 'product_variant' ] == '' && $item[ 'product_variant' ] === FALSE ? '' : $item[ 'product_variant' ];

        $variant = $variant && $variant != '-' ? sprintf( '<div>%s: %s</div>', __('Variant', 'order-and-inventory-manager-for-woocommerce'), $variant ) : '';

        $class  = $item['temp_product'] == 1 ? 'temp_product_color' : '';
        if( $item['temp_product'] == 1 ){
          return sprintf( '<div class="'.$class.'"><div>%1$s</div>%2$s</div>', $item['product_name'], $variant );  
        }else{
          return sprintf( '<div><div>%1$s</div>%2$s<div class="product_sku">%3$s: %4$s</div></div>', $item['product_name'], $variant, __('Product ID', 'order-and-inventory-manager-for-woocommerce'), esc_html($item[ 'product_sku' ]) );
        }
        break;

      case 'supplier_info':
        if( $item['product_supplier'] ){
          $url_txt = __('URL', 'order-and-inventory-manager-for-woocommerce');
          $supplier_url_text = sprintf('<div>%s: %s</div>',$url_txt , $item['product_supplier']);
        }
        if( $item['supplier_product_id'] ){
          $oimwc_supplier_product_id_txt = __('Supplier Product ID', 'order-and-inventory-manager-for-woocommerce');
          $supplier_product_id_txt = sprintf('<div>%s: %s</div>',$oimwc_supplier_product_id_txt , $item['supplier_product_id']);
        }
        if( $item['supplier_note'] ){
          $oimwc_supplier_product_notes_txt = __('Product Notes', 'order-and-inventory-manager-for-woocommerce');
          $supplier_product_notes_txt = sprintf('<div>%s: %s</div>',$oimwc_supplier_product_notes_txt , $item['supplier_note']);
        }

        return sprintf( '<div>%1$s<div>%2$s: %3$s</div><div>%4$s: %5$s</div>%6$s%7$s</div>', $supplier_url_text, __('Purchase price','order-and-inventory-manager-for-woocommerce'), wc_price($item['purchase_price'],array('currency'=> $item['purchase_currency'])), __('Supplier pack size', 'order-and-inventory-manager-for-woocommerce'), esc_html($supplier_pack_size), $supplier_product_id_txt, $supplier_product_notes_txt);

        break;

      case 'product_detail':

        $product_price = isset( $item[ 'price' ] ) && $item[ 'price' ] == '' && $item[ 'price' ] === FALSE ? '-' : $item[ 'price' ];

        $our_pack_size = isset( $item[ 'our_pack_size' ] ) && $item[ 'our_pack_size' ] == '' && $item[ 'our_pack_size' ] === FALSE ? '-' : $item[ 'our_pack_size' ];

        $total_pieces = isset( $item[ 'total_pieces' ] ) && $item[ 'total_pieces' ] == '' && $item[ 'total_pieces' ] === FALSE ? '-' : $item[ 'total_pieces' ];

        $product_stock = isset( $item[ 'product_stock' ] ) && $item[ 'product_stock' ] == '' && $item[ 'product_stock' ] === FALSE ? '-' : (int)$item[ 'product_stock' ]; 

        if($item['temp_product'] == 1){
          return;
        }else{
          return sprintf( '<div><div>%1$s: %2$s</div><div>%3$s: %4$s</div><div>%5$s: <span class="items_in_stock">%6$s</span></div><div>%7$s: <span class="units_in_stock">%8$s</span></div></div>', __('Shop price', 'order-and-inventory-manager-for-woocommerce'), $product_price, __('Shop pack size', 'order-and-inventory-manager-for-woocommerce'), esc_html($our_pack_size), __('Items in stock', 'order-and-inventory-manager-for-woocommerce'), esc_html($product_stock), __('Physical units in stock', 'order-and-inventory-manager-for-woocommerce'), esc_html($total_pieces) );
        }
      break;

      case 'order_info':

          $requested_stock =  $item['requested_stock'];
          $arrvived_stock  =  $item['arrvived_stock'];
          $arrvived_stock  =  $arrvived_stock ? $arrvived_stock : 0;
          $arrvived_stock_text = "<span class='arrived_stock_count'> $arrvived_stock </span> / <span class='requested_stock_count'>$requested_stock</span>";
          if($item['finalize_product'] == 1){
            $arrvived_stock_text .= ' - '.__('Finalized','order-and-inventory-manager-for-woocommerce');
          }
          else if($item['finalize_product'] == 2){
            $arrvived_stock_text .= ' - '.__('Fully Arrived','order-and-inventory-manager-for-woocommerce');
          }
          $arrival_status = sprintf('<div class="arrival_stock_txt"><strong>%s: %s</strong></div>',__('Arrived','order-and-inventory-manager-for-woocommerce'), $arrvived_stock_text );

          $arrival_date = $item[ 'arrival_date' ];
          if( $arrival_date != __('Unknown','order-and-inventory-manager-for-woocommerce') ){
            $arrival_date = date_i18n( get_option( 'date_format' ), strtotime( $item[ 'arrival_date' ] ) );
          }

          $ordered_pieces = $supplier_pack_size * $item[ 'order_qty' ];
          $purchase_price = $item['purchase_price'];
          $purchase_currency = $item['purchase_currency'];
          $ordered_pieces_str = '';
          $order_qty = $item['order_qty'];
          $ordered_pieces_str .= sprintf(__('Ordered: %d × %d-packs at %s %0.2f','order-and-inventory-manager-for-woocommerce'),$order_qty,$supplier_pack_size,$purchase_currency,(number_format($purchase_price / $supplier_pack_size,2)));
          $total = $item[ 'order_qty' ] * $purchase_price;
          $total = $purchase_currency .' '.number_format($total,2);
          if($item['temp_product'] == 1){
            return sprintf( '<div><div>%1$s </div><div>%2$s: %3$s</div><div>%4$s: %5$s</div>%6$s</div>', sprintf(__('Ordered: %d × %d-packs at %s %0.2f','order-and-inventory-manager-for-woocommerce'),$order_qty,$supplier_pack_size,$purchase_currency,(number_format($purchase_price / $supplier_pack_size,2))),__('Total','order-and-inventory-manager-for-woocommerce'),esc_html($total), __('ETA', 'order-and-inventory-manager-for-woocommerce'), esc_html($arrival_date), $arrival_status );
          }else{
            return sprintf( '<div><div>%1$s </div><div>%2$s: %3$s</div><div>%4$s: %5$s</div>%6$s</div>', sprintf(__('Ordered: %d × %d-packs at %s %0.2f','order-and-inventory-manager-for-woocommerce'),$order_qty,$supplier_pack_size,$purchase_currency,(number_format($purchase_price / $supplier_pack_size,2))),__('Total','order-and-inventory-manager-for-woocommerce'),esc_html($total), __('ETA', 'order-and-inventory-manager-for-woocommerce'), esc_html($arrival_date), $arrival_status );
          }
      break;
            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_REQUEST
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
        if(!empty($_REQUEST['orderby']))
        {
            $orderby = sanitize_text_field($_REQUEST['orderby']);
        }

        // If order is set use this as the order
        if(!empty($_REQUEST['order']))
        {
            $order = sanitize_text_field($_REQUEST['order']);
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
    /**
     * Replaces single quotes from string
     *
     * @param mixed $sql
     */
	function closure($sql){
	    return str_replace( "'mt1.meta_value'", "mt1.meta_value", $sql );
	}
	
	function single_row( $item ){
		echo '<tr>';
        $this->single_row_columns( $item );
		echo '</tr>';
    $colspan = ($this->get_order_status() == 'active') ? '6' : '5';
		echo '<tr><td colspan="'.$colspan.'"><div class="table_seperator"></div></td></tr>';
	}
}
	
?>