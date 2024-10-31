<?php
/**
 * OIMWC_Stock_status class
 *
 * Handles physical stock qty data and units in stock data
 *
 * @since    1.0.0
 */
if (!class_exists('OIMWC_Stock_status')) {
    /**
    * OIMWC_Stock_status Handles physical stock qty data and units in stock data
    *
    * @since    1.0.0
    */
    class OIMWC_Stock_status {

        protected static $instance;

        public static function init(){
            if(!isset(self::$instance) && !self::$instance instanceof OIMWC_Stock_status){
                self::$instance = new OIMWC_Stock_status();
            }
            return self::$instance;
        }
        /**
	    * Setup class.
	    *
	    * @since 1.0.0
	    */
        function __construct() {
            add_action('init', array($this, 'check_physical_stock_status'));
            add_action('check_physical_stock_status', array($this, 'generate_physical_stock_status'));
            if(isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'order-inventory-management'){
                add_action( 'admin_notices', array( $this, 'generate_physical_stock_status' ) );
            }
            add_action( 'admin_post_print.csv', array($this,'print_physical_stock_report' ));
            add_action( 'wp_ajax_download_wrong_stock_data', array( $this, 'download_wrong_stock_data' ) );
        }

        /**
        * Generate physical stock qty and units in stock report
        * Schedules generation of stock report daily at 23:59:59
        *
        * @since 1.0.0
        */
        function check_physical_stock_status() {
            if (!wp_next_scheduled('check_physical_stock_status')) {
                $schedule_time = oimwc_getCurrentDateByTimeZone('Y-m-d') . '23:59:59';
                $schedule_time = get_gmt_from_date($schedule_time, 'U');
                wp_schedule_event($schedule_time, 'daily', 'check_physical_stock_status');
            }
            $has_wrong_stock = get_option( 'oimwc_has_wrong_stock', 0 );
            if( $has_wrong_stock ){
                add_action( 'admin_notices', array( $this, 'generate_physical_stock_status' ) );
            }
            if( isset( $_GET['check_oimwc_stock'] ) ){
                add_action( 'admin_notices', array( $this, 'oimwc_check_product_stock' ) );
            } 
        }

        public function oimwc_check_product_stock(){
            ob_start();
            $this->generate_physical_stock_status();
            $content = ob_get_contents();
            ob_clean();

            $class = 'notice notice-success is-dismissible';
            //$message = __( 'No wrong stock values detected in the database.', 'order-and-inventory-manager-for-woocommerce' );
            
            $message = $content ? $content : $message;  

            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
        }

        public function get_physical_stock_data( $product_type, $limit, $id = array() ){
            global $wpdb;
            
            $post_id        = ($product_type == 'simple') ? 'product_id' : 'variation_id'; 
            $product_ids    = count( $id ) ? implode( ",", $id ) : '';
            $var_condition1 = ($product_type == 'simple') ? '=' : '>';
            $var_condition2 = count( $id ) ? " AND A.{$post_id} IN ( {$product_ids} ) " : ' AND floor(A.stock_in_units/A.shop_pack_size) != A.physical_stock ';

            $query = "SELECT A.product_id, A.variation_id, A.stock, A.physical_stock, A.stock_in_units AS supplier_total_pieces, A.shop_pack_size AS pack_size 
                FROM `{$wpdb->prefix}oim_product_stock` AS A, ( SELECT max(id) AS max_id FROM `{$wpdb->prefix}oim_product_stock` GROUP BY {$post_id} ) AS B
                WHERE A.id = B.max_id
                AND A.variation_id {$var_condition1} 0
                {$var_condition2}
                ORDER BY product_id ASC 
                $limit";

            return $wpdb->get_results( $query ,ARRAY_A);
        }

        public function get_ordered_product_data( $product_type ){
            global $wpdb, $oimwc_cache;
            if( isset( $oimwc_cache[ $product_type .'_product_type_ordered_product_data' ] ) ){
                return $oimwc_cache[ $product_type .'_product_type_ordered_product_data' ];
            }
            $limit = '';
            $post_id        = ($product_type == 'simple') ? '_product_id' : '_variation_id';
            $var_condition  = ($product_type == 'simple') ? ' AND b.meta_key = "_variation_id" AND b.meta_value = "0" ' : ' AND a.meta_value > 0 ';
            $join_condition = ($product_type == 'simple') ? " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id ) " : ''; 

            $order_status = get_option('oimwc_selected_order_status');
            if( is_array($order_status) && count($order_status) > 0 ){
                $order_status = "('".implode("','", $order_status)."')";
            }else{
                $order_status = "('wc-processing','wc-on-hold')";
            }

            $query          = "SELECT ID FROM {$wpdb->posts} where post_status IN {$order_status} ";
            $orderIDs       = $wpdb->get_col( $query );
            
            if( count( $orderIDs ) ){
                $var_condition .= sprintf( ' AND WO.order_id IN ( %s )', implode( ',', $orderIDs ) );
            }

            $query = "SELECT a.meta_value AS product_id, c.meta_value AS qty, WO.order_item_id
                    FROM {$wpdb->prefix}woocommerce_order_itemmeta AS a
                    {$join_condition}
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS c ON ( a.order_item_id = c.order_item_id )
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS WO ON ( WO.order_item_id = c.order_item_id )
                    WHERE a.meta_key = '{$post_id}' 
                    AND c.meta_key = '_qty'
                    {$var_condition}
                    $limit";

            $result = $wpdb->get_results( $query, ARRAY_A );
            $product_list = [];
            if( $result ) {
                foreach ( $result as $row ) {
                    $product_id     = $row[ 'product_id' ];
                    $qty            = $row[ 'qty' ];
                    $order_item_id  = $row[ 'order_item_id' ]; 

                    if( !isset( $product_list[ $product_id ] ) ){
                        $product_list[ $product_id ] = 0;
                    }
                    
                    $sql = 'SELECT b.meta_value AS qty
                    FROM '.$wpdb->prefix.'woocommerce_order_itemmeta AS a
                    LEFT JOIN '.$wpdb->prefix.'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
                    WHERE a.meta_key = "_refunded_item_id" 
                    AND a.meta_value = '. $order_item_id .'
                    AND b.meta_key = "_qty"';  
                    
                    $refunded_qty = intval( $wpdb->get_var( $sql ) );

                    $product_list[ $product_id ] = $product_list[ $product_id ] + $qty + $refunded_qty;
                }
            }
            $oimwc_cache[ $product_type .'_product_type_ordered_product_data' ] = $result;        
            return $product_list;
        }

        public function get_stock_data( $post_type, $limit, $product_array ){
            /**
            * Get product with ordered qty
            */
            $simple_ordered_product_IDs    = $this->get_ordered_product_data( $post_type );
            $simple_product_list            = array_keys( $simple_ordered_product_IDs );
            /**
            * Get ordered product data
            */
            $simple_product_list            = $this->get_physical_stock_data( $post_type, $limit, $simple_product_list );
            if( count( $simple_product_list ) ){
                /**
                * Check the product stock is valid or not
                */
                $search_var = $post_type == 'simple' ? 'product_id' : 'variation_id';
                foreach ( $simple_product_list as $data ) {
                    if( intval( $data[ 'stock' ] ) != ( intval( $data[ 'physical_stock' ] ) - intval( $simple_ordered_product_IDs[ $data[ $search_var ] ] ) ) ){
                        $product_array[]   = $data;              
                    }
                }
            }
            return $product_array;
        }

        public function physical_stock_query( $has_limit = false )
        {
            global $wpdb;

            $limit          = '';
            $array_final    = array();

            if( ( isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'order-inventory-management' ) || $has_limit )
            {
                $limit = 'LIMIT 0,10';
            }
            /**
            * Get simple product data for invalid product stock
            */
            $simple_product_array   = $this->get_physical_stock_data( 'simple', $limit );
            $total_simple_product   = count( $simple_product_array );
            $variable_product_array = array();
            $remaining_products     = 0;
            $total_variable_product = 0;  

            /**
            * Calculate limit and fetch remaining data for popup window
            */
            if( $limit && $total_simple_product && $total_simple_product < 10 ){
                $remaining_products = 10 - $total_simple_product;
                $limit              = "LIMIT 0, $remaining_products";
            }

            if( $remaining_products || !$limit ){
                /**
                * Get variable product data for invalid product stock
                */
                $variable_product_array = $this->get_physical_stock_data( 'variation', $limit );
                $total_variable_product = count( $variable_product_array );
            }

            $total_products         =    $total_simple_product + $total_variable_product;

            /**
            * Calculate limit and reset limit for check simple product stock
            */
            if( $limit && $total_products && $total_products < 10 ){
                $remaining_products = 10 - $total_products;
                $limit              = "LIMIT 0, $remaining_products";
            }

            if( $remaining_products || !$limit ){
                /**
                * Get simple product with ordered qty
                */
                $simple_product_array   = $this->get_stock_data( 'simple', $limit, $simple_product_array );
                $total_simple_product   = count( $simple_product_array ); 
                $total_products         = $total_simple_product + $total_variable_product;
            }

            /**
            * Calculate limit and reset limit for check variation product stock
            */
            if( $limit && $total_products && $total_products < 10 ){
                $remaining_products = 10 - $total_products;
                $limit              = "LIMIT 0, $remaining_products";
            }

            if( $remaining_products || !$limit ){
                /**
                * Get variation product with ordered qty
                */
                $variable_product_array   = $this->get_stock_data( 'variation', $limit, $variable_product_array );
            }

            /*
            * Merge simple and virtual product data
            */
            $post_data_array = array_merge( $simple_product_array, $variable_product_array );

            foreach ($post_data_array as $key => $value) {
                $product_obj_id = $value['product_id'];
                $product_stock  = $value['stock'];
                $shop_pack_size = $value['pack_size'];
                $physical_stock = $value['physical_stock'];
                $stock_in_units = $value['supplier_total_pieces'];
                $product_type   = 'simple';

                if( $value['variation_id'] ){
                    $product_obj_id = $value['variation_id'];
                    $product_type   = 'variable';
                }
                if( isset( $array_final[ $product_obj_id ] ) ){
                    continue;
                }
                $sku        = get_post_meta( $product_obj_id, '_sku', true );
                $order_qty  = get_ordered_product_qty( $product_obj_id, $product_type );
                $order_qty  = $order_qty ? $order_qty : 0;

                $actual_physical_stock           = $order_qty + $product_stock; 
                $actual_supplier_total_pieces    = ( $order_qty + $product_stock ) * $shop_pack_size; 
                $post_data_array[ $key ]['qty']  = $order_qty;

                $array_final[ $product_obj_id ] = array( 
                    'product_id'        => $product_obj_id, 
                    'physical_stock'    => $actual_physical_stock,
                    'unit_in_stock'     => $actual_supplier_total_pieces,
                    'db_physical_stock' => $physical_stock,
                    'db_unit_in_stock'  => $stock_in_units,
                    'shop_pack_size'    => $shop_pack_size, 
                    'sku'               => $sku, 
                    'stock'             => $product_stock,
                    'ordered_qty'       => $order_qty
                );
            }
            
            return $array_final;
        }
        /**
        * Display wrong data of physical stock qty and units in stock
        *
        * @since 1.0.0
        */
        function generate_physical_stock_status() {
            if ( false === ( $content = get_transient( 'oimwc_check_product_stock' ) ) ) {
                $total_count = $this->physical_stock_query( true );
                $total_records = count( $total_count );    
                update_option( 'oimwc_has_wrong_stock', 0 );
            }else{
                $total_records = get_option( 'oimwc_has_wrong_stock' );
                $total_count = $content;
            }
            
            $main_class = OIMWC_MAIN::init();
            
            $class      = $total_records >= 10 ? 'has_download_button' : '';
            $output = '';
            $data_output = '';
            if(!empty( $total_count ) ){ ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Products with mismatching stock values detected:','order-and-inventory-manager-for-woocommerce'); ?>
                        <a id="view_wrong_product_panel" href="#"><?php _e('View Products','order-and-inventory-manager-for-woocommerce'); ?>
                        </a>
                    </p>
                </div>
                <?php
                update_option( 'oimwc_has_wrong_stock', $total_records );
                $output .= '<div id="view_products" style="display:none;"><div class="incorrect_stock_products_panel '. $class .'">';
                $i = 1;

                if ( $content ) {
                    $data_output = $content;
                    goto product_stock_output; 
                }
                foreach ( $total_count as $post_data ){
                    $post_data['sku'] = $post_data['sku'] ? $post_data['sku'] : '-';
                    if(get_post_type($post_data['product_id']) == 'product_variation')
                    {
                        $product = new WC_Product_Variation( $post_data['product_id'] ); 
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
                            $product_variant = ' | '.implode(' | ', $variation_names );
                        } else {
                            $product_variant = '';
                        }
                        $product_id = wp_get_post_parent_id($post_data['product_id']);
                        $product_title  = get_the_title( $product_id );
                    }
                    else
                    {
                        // $product = new WC_Product( $post_data['product_id'] );
                        $product_variant = '';
                        $product_id = $post_data['product_id'];
                        $product_title  = get_the_title($post_data['product_id']);
                    }

                    $data_output .= '<div>'.$i.') <a href="' 
                    . site_url().'/wp-admin/post.php?post='.$product_id.'&action=edit" target="_blank" class="product_title">' 
                    . html_entity_decode($product_title) . '</a>'.html_entity_decode($product_variant).'<br />';
                    $data_output .= __('Product SKU','order-and-inventory-manager-for-woocommerce').': '.$post_data['sku']. '<br />';
                    $data_output .= __('Stock quantity','order-and-inventory-manager-for-woocommerce').' ('.(int)$post_data['stock'].') + '.__('Total Product Ordered','order-and-inventory-manager-for-woocommerce').' ('.(int)$post_data['ordered_qty'].'): '.((int)$post_data['stock']+(int)$post_data['ordered_qty']).'<br /> ';
                    $data_output .= __('Physical stock Qty','order-and-inventory-manager-for-woocommerce').': '.$post_data['db_physical_stock'].' <br />';
                    $data_output .= __('Physical units in stock','order-and-inventory-manager-for-woocommerce').': '.$post_data['db_unit_in_stock'].'<br />';
                    $data_output .= __('Shop pack size','order-and-inventory-manager-for-woocommerce').': '.$post_data['shop_pack_size'].'</div> <br />';
                    $i++;
                }
                
                set_transient( 'oimwc_check_product_stock', $data_output );
                product_stock_output:

                $output .= $data_output;
                $output .= '</div>';
                
                $nonce   = wp_create_nonce( 'download_wrong_stock_data_nonce' );
                if( $total_records >= 10 ){
                    $output .= '<div class="download_info">'.__('More than 10 errors were found.
To see all errors, download the complete list.','order-and-inventory-manager-for-woocommerce');
                    $output .=' <a id="download_stock" data-nonce="'. $nonce .'" class="download_wrong_stock_data_link ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" href="'.admin_url( 'admin-post.php?action=print.csv' ).'&export=table" download><i class="fas fa-download"></i></a><span class="loader"><img src="'.  OIMWC_PLUGIN_URL. 'images/bx_loader.gif" /></span></div>';
                }
                $output .= '</div>';
            }
            echo $output;
        }

        /**
        * Save report for wrong data of physical stock qty and units in stock
        *
        * @since 1.0.0
        */
        function download_wrong_stock_data()
        {
            global $wpdb;

            // Process export
            if( wp_verify_nonce( $_POST['nonce'], "download_wrong_stock_data_nonce" ) ) {
                require OIMWC_INCLUDES.'phpspreadsheet/vendor/autoload.php';
                $objPHPSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
                $objPHPSpreadsheet->getDefaultStyle()->getFont()->setName('Calibri')
                            ->setSize(12);
                $l = 'A';
                $header_row = array(
                    0 => __('Product Title','order-and-inventory-manager-for-woocommerce'),
                    1 => __('Product Variation','order-and-inventory-manager-for-woocommerce'),
                    2 => __('Product SKU','order-and-inventory-manager-for-woocommerce'),
                    3 => __('Stock quantity','order-and-inventory-manager-for-woocommerce') . ' + ' . __('Total Product Ordered', 'order-and-inventory-manager-for-woocommerce' ),
                    4 => __('Physical stock Qty','order-and-inventory-manager-for-woocommerce'),
                    5 => __('Physical units in stock','order-and-inventory-manager-for-woocommerce'),
                    6 => __('Shop pack size','order-and-inventory-manager-for-woocommerce'),
                    7 => __('Link to product','order-and-inventory-manager-for-woocommerce')
                );
                foreach ($header_row as $key => $value) {
                    $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue( $l.'1', $header_row[$key]);
                    $objPHPSpreadsheet->getActiveSheet()->getStyle($l.'1')->applyFromArray(array('font' => array('bold' => true)));
                    $objPHPSpreadsheet->getActiveSheet()->getColumnDimension($l)->setAutoSize(true);
                    $l++;
                }

                $rows = $this->physical_stock_query();
                $rowcount = 2;
                $edit_link = site_url() . '/wp-admin/post.php?post=%s&action=edit';
                if(!empty($rows)) 
                {
                    foreach($rows as $Record)
                    {  
                        if(get_post_type($Record['product_id']) == 'product_variation')
                        {
                            $product = new WC_Product_Variation( $Record['product_id'] ); 
                            $product_id = wp_get_post_parent_id($Record['product_id']);
                            $productName  = get_the_title( $product_id );
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
                                $product_variant = '';
                            }
                        }
                        else
                        {
                            $product_id = $Record['product_id'];
                            $product = new WC_Product( $Record['product_id'] );
                            $productName  = get_the_title($Record['product_id']);
                            $product_variant = '-';
                        }
                        $link   = sprintf( $edit_link, $product_id );     
                        $l = 'A';
                        foreach ($header_row as $key => $value) {
                            if( $header_row[$key] == __('Product Title','order-and-inventory-manager-for-woocommerce') ){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, empty($productName) ? '-' : html_entity_decode($productName) ); 
                            }
                            if( $header_row[$key] == __('Product Variation','order-and-inventory-manager-for-woocommerce') ){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, empty($product_variant) ? '-' : html_entity_decode($product_variant) ); 
                            }
                            if( $header_row[$key] == __('Product SKU','order-and-inventory-manager-for-woocommerce')){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, empty($Record['sku']) ? '-' : $Record['sku'] ); 
                            }
                            if( $header_row[$key] == __('Stock quantity','order-and-inventory-manager-for-woocommerce') . ' + ' . __('Total Product Ordered', 'order-and-inventory-manager-for-woocommerce' )){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, empty($Record['stock']) ? '0' : (int)$Record['stock'].' + '.(int)$Record['ordered_qty'].' = '. ((int)$Record['stock'] + (int)$Record['ordered_qty'])); 
                            }
                            if( $header_row[$key] == __('Physical stock Qty','order-and-inventory-manager-for-woocommerce')){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, empty($Record['db_physical_stock']) ? '0' : $Record['db_physical_stock'] ); 
                            }
                            if( $header_row[$key] == __('Physical units in stock','order-and-inventory-manager-for-woocommerce') ){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, empty($Record['db_unit_in_stock']) ? '0' : $Record['db_unit_in_stock'] ); 
                            }
                            if( $header_row[$key] == __('Shop pack size','order-and-inventory-manager-for-woocommerce') ){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, empty($Record['shop_pack_size']) ? '0' : $Record['shop_pack_size'] ); 
                            }
                            if( $header_row[$key] == __('Link to product','order-and-inventory-manager-for-woocommerce') ){
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->setCellValue($l.$rowcount, $link ); 
                               $objPHPSpreadsheet->setActiveSheetIndex(0)->getCell($l.$rowcount)->getHyperlink()->setUrl( $link );

                            } 
                            $l++;
                        }
                        $rowcount++;
                    }
                }

                $filename       = __( 'Incorrect product data', 'order-and-inventory-manager-for-woocommerce' ) .'-'. time() .'.xlsx';
                $filename       = str_replace( ' ', '-', $filename );
                $upload_dir     = wp_upload_dir();
                $file_path      = $upload_dir[ 'basedir' ] .'/report/';
                $writer         = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPSpreadsheet);

                if( !is_dir( $file_path ) ){
                    mkdir( $file_path );
                }

                array_map( 'unlink', array_filter((array) glob( $file_path . "*") ) );

                $writer->save( $file_path . $filename );

                wp_send_json_success( array( 'file_name' => $filename ) );
            }
        }

        /**
        * Download report for wrong data of physical stock qty and units in stock
        *
        * @since 1.0.0
        */
        function print_physical_stock_report()
        {
            global $wpdb;

            // Process export
            if( isset( $_GET['export'] ) ) {

                $file_name      = $_GET['file_name'];
                $upload_dir     = wp_upload_dir();
                $file_path      = $upload_dir[ 'basedir' ] .'/report/';

                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header("Content-Disposition: attachment; filename=\"$file_name\"");
                header("Cache-Control: max-age=0");

                readfile( $file_path . $file_name );
                die();
            }
        }
    }
    OIMWC_Stock_status::init();
}
?>