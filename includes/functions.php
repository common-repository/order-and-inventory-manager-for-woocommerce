<?php

if (!function_exists('oimwc_get_countries_list')) {
    /**
     * List of countries
     *
     * @return Array
     */
    function oimwc_get_countries_list() {
        return array(
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BQ' => 'Bonaire, Saint Eustatius and Saba',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CW' => 'Curacao',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CD' => 'Democratic Republic of the Congo',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'TL' => 'East Timor',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'CI' => 'Ivory Coast',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'XK' => 'Kosovo',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'CG' => 'Republic of the Congo',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SX' => 'Sint Maarten',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'KR' => 'South Korea',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );
    }
}

if (!function_exists('oimwc_getCurrentDateByTimeZone')) {
    /**
     * Gets current date by time zone
     *
     * @param string $format
     * @return object of date format
     */
    function oimwc_getCurrentDateByTimeZone($format = "Y-m-d H:i:s") {
        $dateTime = 'now';
        $timeZone = new DateTimeZone('UTC');
        try {
            $date = new DateTime($dateTime, $timeZone);
            $timeZone = new DateTimeZone('UTC');
            $date->setTimezone($timeZone);
        } catch (Exception $e) {
            $date = new DateTime($dateTime);
        }
        return $date->format($format);
    }
}

if (!function_exists('oimwc_print_data')) {
    function oimwc_print_data($data){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

if (!function_exists('oimwc_get_correct_array_values')) {
    function oimwc_get_correct_array_values( $values){
        global $stock_output;
        foreach ($values as $key => $value) {
            if( $key >= 2018 ){
                if( is_array( $stock_output[$key] ) ){
                    $stock_output[$key] =  oimwc_array_merge( $stock_output[$key] , $value );    
                }else{
                    $stock_output[$key] =  $value;    
                }
            }else{
                oimwc_get_correct_array_values( $value );
            }
        }
        return $stock_output;
    }
}

if (!function_exists('oimwc_array_merge')) {
    function oimwc_array_merge( $old_array, $new_array ){
        foreach ($new_array as $key => $value) {
            $old_array[$key] = $value;
        }
        return $old_array;
    }
}

if (!function_exists('oimwc_get_current_day_stock')) {
    function oimwc_get_current_day_stock( $date, $prefix = '', $monthly_stock = true ){
        global $wpdb;
        $daywise_stock_data = $stock_data = [];
        $year = date('Y');
        $month = date('n');
         
        $sql = array(
                'post_type' => array('product', 'product_variation'),
                'numberposts' => -1,
                'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'oimwc_supplier_id',
                            'compare' => '>',
                            'value' => 0,
                            'type' => 'NUMERIC'
                        ),
                        array(
                            'key' => '_stock',
                            'compare' => '>',
                            'value' => 0,
                            'type' => 'NUMERIC'
                        ),
                        array(
                            'key' => '_manage_stock',
                            'compare' => '=',
                            'value' => 'yes',
                        )
                    ),
                'post_status' => array( 'private', 'publish' )
        );
        
        //$product_list = $wpdb->get_results($sql);
        $product_list = get_posts($sql);
        $data = array();
        
        $currency = oimwc_get_product_used_currency();
        $stock_data[$year][$month] = array();
        $default_our_pack_size = get_option('oimwc_default_our_pack_size');
        $default_supplier_pack_size = get_option('oimwc_default_supplier_pack_size');
        if ($product_list) {
            foreach ($product_list as $product_row) {
                $flag = true;
                //$id = $product_row->post_id;
                $id = $product_row->ID;
                $product_status = get_post_status($id);
                //$supplier_id = $product_row->meta_value;
                $supplier_id = get_post_meta($id,'oimwc_supplier_id',true);
                $arrived_stock = get_post_meta($id, '_stock', true);
                $product_obj = wc_get_product($id);
                $available_variations = $product_obj->get_children();
                if( is_array($available_variations) && count($available_variations) > 0 ){
                    $flag = false;
                }

                if( $arrived_stock >= 0 && $flag){
                    $our_pack_size  = get_post_meta( $id, 'oimwc_our_pack_size', true );
                    $our_pack = $our_pack_size ? $our_pack_size : ($default_our_pack_size ? $default_our_pack_size : 1);
                    $supplier_pack_size = get_post_meta( $id, 'oimwc_supplier_pack_size', true );
                    $supplier_pack = $supplier_pack_size ? $supplier_pack_size : ($default_supplier_pack_size ? $default_supplier_pack_size : 1);
                    $supplier_remaining_pieces = get_post_meta($id, 'oimwc_supplier_remaining_pieces', true);
                    $supplier_remaining_pieces = !empty($supplier_remaining_pieces) ? $supplier_remaining_pieces : 0;
                    $total_pieces = floor(($arrived_stock*$our_pack)+$supplier_remaining_pieces);
                    $product = get_post($id);
                    $purchase_price = wc_format_decimal( get_post_meta($id, 'oimwc_supplier_purchase_price', true), true );
                    $purchase_currency = get_post_meta($supplier_id, 'oimwc_supplier_currency', true);
                    $units_in_stock = get_post_meta($id, 'oimwc_physical_units_stock', true);
                    $price = ($purchase_price/$supplier_pack);
                    if( $product_obj->is_type( 'simple' ) ){
                        $type = '_product_id'; 
                    }
                    else
                    {
                        $type = '_variation_id';
                    }
                    $main_supplier_info = array();
                    $supplier_info = oimwc_additional_supplier_information($id,$type);
                    $main_supplier_info[$id] = ['product_id' => $id, 'price' => $price];
                    if(is_array($supplier_info) && !empty($supplier_info))
                    {    
                        $array_data = array_merge($supplier_info,$main_supplier_info);
                        $array_sort = array_column($array_data, 'price');
                        array_multisort($array_sort, SORT_ASC, $array_data);
                        if($array_data[0]['price'] == '' && count($array_data[0]['price']) == 0)
                        {
                            $price = $array_data[1]['price'];
                        }
                        else
                        {
                            $price = $array_data[0]['price'];
                        }
                    }
                    /*$purchase_currency = get_post_meta($supplier_id, 'oimwc_supplier_currency', true);
                    $purchase_price = $purchase_price ? $purchase_price : 0;
                    if (!$purchase_price && $product->post_parent) {
                        $purchase_price = wc_format_decimal(get_post_meta($product->post_parent, 'oimwc_supplier_purchase_price', true));
                        $purchase_price = $purchase_price ? $purchase_price : 0;
                    }
                    $purchase_amount = ($total_pieces/$supplier_pack)* $purchase_price; // our method */
                    $purchase_amount = $price * $units_in_stock;
                    if($product_status == "publish"){
                        if ($currency) {                            
                            foreach ($currency as $curr) {
                                if ($curr['currency'] == $purchase_currency) {
                                    $curr = $prefix ? strtolower( $prefix.$curr['currency'] ) : $curr['currency'];
                                    $previous_amount = 0;
                                    if (is_array($stock_data) && isset($stock_data[$year][$month][$curr])) {
                                        $previous_amount = $stock_data[$year][$month][$curr];
                                    }
                                    $stock_data[$year][$month][$curr] = $previous_amount + $purchase_amount;
                                    /*$previous_amount = 0;
                                    if (is_array($daywise_stock_data) && isset($daywise_stock_data[$date][$curr])) {
                                        $previous_amount = $daywise_stock_data[$date][$curr];
                                    }
                                    $daywise_stock_data[$date][$curr] = $previous_amount + $purchase_amount;*/
                                } 
                                else {
                                    $curr = $prefix ? strtolower( $prefix.$curr['currency'] ) : $curr['currency'];
                                    if (!isset($stock_data[$year][$month][$curr])) {
                                        $stock_data[$year][$month][$curr] = 0;
                                    }
                                    /*if (!isset($daywise_stock_data[$date][$curr])) {
                                        $daywise_stock_data[$date][$curr] = 0;
                                    }*/
                                }
                            }
                        }
                    }
                }
            }
        }
        if( $monthly_stock ){
            return $stock_data;
        }
        return $daywise_stock_data;
    }
}

if (!function_exists('oimwc_get_product_used_currency')) {
    function oimwc_get_product_used_currency(){
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
}

add_action('wp_ajax_oimwc_get_current_stock_values','oimwc_get_current_stock_values');
if( !function_exists( 'oimwc_get_current_stock_values' ) ){
    function oimwc_get_current_stock_values(){
        $curren_date = date('Y-n-d');
        $stock = oimwc_get_current_day_stock($curren_date,'product_in_');

        $current_year = date('Y');
        $current_month = date('n');
        $data = [];
        if($stock){
            foreach($stock[$current_year][$current_month] as $key => $val){
                $curr = explode('product_in_',$key);
                $curr = $curr[1];
                $data[$key] = wc_price($val,array('currency'=> strtoupper($curr)));
            }    
        }
        echo json_encode($data);
        die;
    }
}

if (!function_exists('oimwc_check_permission')) {
    /**
    * Check the user accessibility
    *
    * @since 1.0.0
    * @return void
    */
    function oimwc_check_permission($check_menu = false){
        global $oimwc_permission;
        
        $user = wp_get_current_user();
        $flag = false;
        if($user ){
            $roles = (array) $user->roles;
            $user_role = $roles[0];
            if(in_array('administrator', $roles)){
                return true;
            }
            $access_roles = get_option( 'oimwc_access_roles' );
            $access_user_list = get_option('oimwc_user_access_list');
            if ( (is_array($access_roles) && !in_array($user_role, $access_roles) ) && (is_array($access_user_list) && !in_array($user->ID, $access_user_list))) {
                $flag = true;
            }
        }
        $oimwc_permission = $flag;
        if( $check_menu ){
            $oimwc_permission = $oimwc_permission ? false : true;
        }
        return $oimwc_permission;
    }
}

if (!function_exists('oimwc_hide_supplier_info')) {
    function oimwc_hide_supplier_info(){
        $user = wp_get_current_user();
        if($user){
            $roles = (array) $user->roles;
            if(in_array('administrator', $roles)){
                return true;
            }
            else{
                return false;   
            }
        }
    }
}

if (!function_exists('get_suppliers_by_sort_order')) {
    function get_suppliers_by_sort_order($sort_order){
        global $wpdb;
        $table = $wpdb->prefix.'posts';
        $supplier_ids = $wpdb->get_col("SELECT ID FROM {$table} WHERE post_type = 'supplier' AND post_status = 'publish' ORDER BY post_title ".$sort_order);

        return $supplier_ids;
    }
}

if (!function_exists('calculate_physical_stock')) {
    function calculate_physical_stock($post_id,$product_type){
        
        $order_qty = get_ordered_product_qty( $post_id, $product_type );

        $stock = get_post_meta($post_id,'_stock',true);
        $manage_stock = get_post_meta($post_id,'_manage_stock',true);
        if( $manage_stock == 'no' ){
            $_stock_status = get_post_meta( $post_id, '_stock_status', true );
            if( $_stock_status == 'instock' || $_stock_status == 'outofstock'){
                $physical_stock = 0;    
            }
        }
        else
        {   
            $physical_stock = $stock + $order_qty;
        }
        
        //update_post_meta($post_id,'oimwc_physical_stock', $physical_stock );
        
        return $physical_stock;
    }
}

if (!function_exists('get_ordered_product_qty')) {
    function get_ordered_product_qty($post_id,$product_type){
        global $wpdb;

        $order_status = get_option('oimwc_selected_order_status');
        if( is_array($order_status) && count($order_status) > 0 ){
            $order_status = "('".implode("','", $order_status)."')";
        }else{
            $order_status = "('wc-processing','wc-on-hold')";
        }

        $order_qty = 0;
        $refunded_qty = 0;

        $product_type = ($product_type == 'simple') ? '_product_id' : '_variation_id';
        $sql = 'SELECT b.meta_value AS qty, WO.order_item_id
                FROM '.$wpdb->prefix.'woocommerce_order_itemmeta AS a
                LEFT JOIN '.$wpdb->prefix.'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
                LEFT JOIN '.$wpdb->prefix.'woocommerce_order_items AS WO ON ( WO.order_item_id = b.order_item_id )
                LEFT JOIN '.$wpdb->prefix.'posts AS p ON ( p.ID = WO.order_id )
                WHERE a.meta_key = "'.$product_type.'" AND a.meta_value = '.$post_id.' AND b.meta_key = "_qty"
                AND p.post_status IN '.$order_status;  
        
        $orders     = $wpdb->get_results($sql, ARRAY_A);

        if( count( $orders ) ){
            foreach ($orders as $order) {
                $order_qty += $order[ 'qty' ];
                $sql = 'SELECT b.meta_value AS qty
                    FROM '.$wpdb->prefix.'woocommerce_order_itemmeta AS a
                    LEFT JOIN '.$wpdb->prefix.'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
                    WHERE a.meta_key = "_refunded_item_id" 
                    AND a.meta_value = '. $order[ 'order_item_id' ] .'
                    AND b.meta_key = "_qty"';  
                $refunded_qty += intval( $wpdb->get_var( $sql ) );
            }
        }
        return $order_qty + $refunded_qty;
    }
}

if (!function_exists('final_physical_stock')) {
    function final_physical_stock($post_id,$product_type){
        global $wpdb;

        $flag = false;
        $reduce_physical_stock_OStatus = get_option('oimwc_reduce_physical_stock_OStatus');
        if( is_array($reduce_physical_stock_OStatus) && count($reduce_physical_stock_OStatus) > 0 ){
            $reduce_physical_stock_OStatus = "('".implode("','", $reduce_physical_stock_OStatus)."')";
            $flag = true;
        }
        
        $physical_stock = calculate_physical_stock($post_id,$product_type);

        if( $flag )
        {
            $product_type = ($product_type == 'simple') ? '_product_id' : '_variation_id';
            $sql = 'SELECT sum(b.meta_value) AS qty
                    FROM '.$wpdb->prefix.'woocommerce_order_itemmeta AS a
                    LEFT JOIN '.$wpdb->prefix.'woocommerce_order_itemmeta AS b ON ( a.order_item_id = b.order_item_id )
                    LEFT JOIN '.$wpdb->prefix.'woocommerce_order_items AS WO ON ( WO.order_item_id = b.order_item_id )
                    LEFT JOIN '.$wpdb->prefix.'posts AS p ON ( p.ID = WO.order_id )
                    WHERE a.meta_key = "'.$product_type.'" AND a.meta_value = '.$post_id.' AND b.meta_key = "_qty"
                    AND p.post_status IN '.$reduce_physical_stock_OStatus;
            
            $order_qty = $wpdb->get_var($sql);
            $physical_stock = $physical_stock - $order_qty;
        }
        return $physical_stock;
    }
}

if (!function_exists('include_js_templates')) {
    add_action('admin_footer','include_js_templates');
    function include_js_templates(){
        include(OIMWC_JS_TEMPLATES. 'supplier_tmpl.php');
        include(OIMWC_JS_TEMPLATES. 'lowstock_supplier_filter_tmpl.php');
        include(OIMWC_JS_TEMPLATES. 'awaiting_delivery_tbl_tmpl.php');
        include(OIMWC_JS_TEMPLATES. 'completed_po_tmpl.php');
        include(OIMWC_JS_TEMPLATES. 'add_product_existing_PO_tmpl.php');
        include(OIMWC_JS_TEMPLATES. 'view_po_tmpl.php');
        include(OIMWC_JS_TEMPLATES. 'additional_supplier_tmpl.php');
    }
}

if( !function_exists( 'oimwc_custom_limits' ) ){
    function oimwc_custom_limits( $limit, $query ){
        global $custom_offset, $custom_limit;
        return "LIMIT $custom_limit, $custom_offset";
    }
}

if( !function_exists( 'oimwc_supplier_low_stock_count' ) ){
    function oimwc_supplier_low_stock_count( $product_id = 0, $supplier_id = 0 ){
        global $wpdb;
        if( empty( $supplier_id ) ){
            return;
        }
        if( $product_id ){
            $_stock = get_post_meta( $product_id, '_stock', true );
            $low_stock_limit = get_post_meta( $product_id, 'oimwc_low_stock_threshold_level', true );
            $supplier_id = get_post_meta( $product_id, 'oimwc_supplier_id', true );
            if( $_stock > $low_stock_limit ){
                return;
            }
        }

        $variable_products = wp_cache_get( 'oimwc_variable_products_'.$supplier_id, 'oimwc_low_stock_products_cache' );
        if(!$variable_products){
            $sql = 'SELECT DISTINCT(A.post_parent) FROM '.$wpdb->posts.' AS A LEFT JOIN '.$wpdb->postmeta.' AS B ON A.id = B.post_id WHERE A.post_parent > 0 AND A.post_type = "product_variation" AND B.meta_key = "oimwc_supplier_id" AND B.meta_value = "'. $supplier_id .'"';
            $variable_products = $wpdb->get_col( $sql );
            wp_cache_add( 'oimwc_variable_products_'.$supplier_id, $variable_products, 'oimwc_low_stock_products_cache' );
        }
        
        $post_not_in = $variable_products;
        $post_not_in = array_unique( $post_not_in );
        $ids = 0;
        if( count( $post_not_in ) ){
            $ids = implode(',', $post_not_in);
        }
        if($supplier_id != 'all' && isset($supplier_id)){
            $ordered_product = OIMWC_Order::get_ordered_product($supplier_id,false,'=');
        }else{
            $ordered_product = OIMWC_Order::get_ordered_product(0,false,'=');
        }
        // Fetch simple additional supplier products.
        $simple_product_supplier_query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A LEFT JOIN '.$wpdb->prefix.'additional_supplier_info B ON A.post_id = B.product_id LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            WHERE B.variable_id = 0 AND B.supplier_id = '. $supplier_id. ' AND A.meta_key = "oimwc_low_stock_threshold_level" AND C.meta_key = "_stock" 
                AND CAST(A.meta_value AS SIGNED) >= CAST(C.meta_value AS SIGNED)';
        $additional_supplier_ids = $wpdb->get_col($simple_product_supplier_query);

        // Fetch variable additional supplier products.
        $variable_product_supplier_query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A LEFT JOIN '.$wpdb->prefix.'additional_supplier_info B ON A.post_id = B.variable_id LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            WHERE B.supplier_id = '. $supplier_id.' AND A.meta_key = "oimwc_low_stock_threshold_level" AND C.meta_key = "_stock" 
                AND CAST(A.meta_value AS SIGNED) >= CAST(C.meta_value AS SIGNED)';
        $variable_supplier_ids = $wpdb->get_col($variable_product_supplier_query);

        // Fetch all low stock products.
        $query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
                LEFT JOIN '.$wpdb->prefix.'postmeta B ON A.post_id = B.post_id 
                WHERE A.meta_key = "oimwc_low_stock_threshold_level" AND B.meta_key = "_stock" 
                AND CAST(A.meta_value AS SIGNED) >= CAST(B.meta_value AS SIGNED) AND A.post_id NOT IN ('.$ids.')';
        $all_post_ids = $wpdb->get_col($query);

        // Fetch products supplier wise.
        $query = 'SELECT A.post_id FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "oimwc_supplier_id" AND A.meta_value = '. $supplier_id.' AND A.post_id IN ( '. implode(',', $all_post_ids) .' )';
        $post_ids = $wpdb->get_col($query);
        $post_ids = array_merge($additional_supplier_ids,$variable_supplier_ids,$post_ids);

        // For check show in low stock or not.
        $query = 'SELECT A.post_id FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "oimwc_show_in_low_stock" AND A.meta_value = "yes" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);

        // For check manage stock or not.
        $query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "_manage_stock" AND A.meta_value = "yes" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);
    
        if(count($post_ids) > 0){
            $additional_product = oimwc_additional_supplier_low_stock_count( $supplier_id, false );

            $lowstock_array = array();
            $requested_stock = array();
            $product_ids = array();
            $order_ids = array();
            $lowstock_list = array_merge($ordered_product,$additional_product);
            foreach ($lowstock_list as $value) {
                $lowstock_threshold = get_post_meta($value,'oimwc_low_stock_threshold_level',true);
                $lowstock_array[$value] = $lowstock_threshold;
            }

            $lowstock_ids = implode(', ', array_unique(($ordered_product)));
            $query = 'SELECT DISTINCT(product_id), SUM(stock + requested_stock) AS total FROM '.$wpdb->prefix.'order_inventory WHERE product_id IN ('.$lowstock_ids.') AND completed_order = 0';
            if($supplier_id){
                $query .= ' AND supplier_id =' . $supplier_id.' GROUP BY product_id';
            }else{
                $query .= ' GROUP BY product_id';
            }
            $lowstock_result = $wpdb->get_results($query,ARRAY_A);
            if ($lowstock_result) {
                foreach ($lowstock_result as $key => $val) {
                    $requested_stock[$val['product_id']] = $val['total'];
                }
            }
            ksort($lowstock_array);
            ksort($requested_stock);
            foreach($lowstock_array as $key => $val){
                if(array_key_exists($key, $requested_stock))
                {
                    if($val >= $requested_stock[$key]){
                        array_push($product_ids, $key);
                    }
                }else{
                    array_push($order_ids, $key);
                }
            }
            $post_in = array_merge($product_ids,$order_ids);
            $args = array(
                'post_type' => array('product', 'product_variation'),
                'meta_query' => array(
                    'relation' => 'OR',
                        array(
                         'key' => 'oimwc_discontinued_product',
                         'compare' => 'NOT EXISTS',
                         'value' => ''
                        ),
                        array(
                         'key' => 'oimwc_discontinued_product',
                         'value' => 'no'
                        )
                    ),
                'post__in' => $post_in,
                'post_status' => array( 'private', 'publish' ),
            );
            $product_list = new WP_Query( $args );
            $total_count = $product_list->found_posts;
        }else{
            $total_count = 0;
        }
        if( $supplier_id ){
            update_post_meta( $supplier_id, 'oimwc_total_low_stock_products', $total_count );
        }else{
            update_option( 'oimwc_total_low_stock_prod_without_supplier', $total_count );
        }

        $qry = "SELECT COUNT(A.post_id) as total_supplier FROM {$wpdb->prefix}postmeta AS A 
                LEFT JOIN {$wpdb->prefix}postmeta B ON A.post_id = B.post_id
                WHERE A.meta_key = 'oimwc_supplier_products_lowstock_level' AND B.meta_key = 'oimwc_total_low_stock_products'
                AND A.meta_value != '' AND B.meta_value != ''
                AND CAST(B.meta_value AS UNSIGNED)  >= CAST(A.meta_value AS UNSIGNED)";
        $supplier_lowstock_count = $wpdb->get_var($qry);
        update_option('oimwc_total_lowstock_supplier_count',$supplier_lowstock_count);

        return $total_count;
    }
}

function oimwc_additional_supplier_low_stock_count( $supplier_id, $return_total = true, $compare =  '=' ){
    global $wpdb;
    if( ! $supplier_id || empty( $supplier_id ) ){
        return;
    }
    if( $return_total ){
        $supplier_cache = wp_cache_get( 'oimwc_supplier_count_'.$supplier_id, 'oimwc_low_stock_products_cache' );
        if( $supplier_cache ){
            //return $supplier_cache;
        }
    }
    $variable_products = wp_cache_get( 'oimwc_variable_products_'.$supplier_id, 'oimwc_low_stock_products_cache' );
    if(!$variable_products){
        $sql = 'SELECT DISTINCT(A.post_parent) FROM '.$wpdb->posts.' AS A LEFT JOIN '.$wpdb->postmeta.' AS B ON A.id = B.post_id WHERE A.post_parent > 0 AND A.post_type = "product_variation" AND B.meta_key = "oimwc_supplier_id" AND B.meta_value = "'. $supplier_id .'"';
        $variable_products = $wpdb->get_col( $sql );
        wp_cache_add( 'oimwc_variable_products_'.$supplier_id, $variable_products, 'oimwc_low_stock_products_cache' );
    }

    $post_not_in = $variable_products;
    $post_not_in = array_unique( $post_not_in );
    $ids = 0;
    if( count( $post_not_in ) ){
        $ids = implode(',', $post_not_in);
    }
    $select_var = ''; 
    $total_count = 0;
    if( $return_total ){
        $select_var = 'count';
    }else{
        $query = 'SELECT A.post_id FROM '.$wpdb->prefix.'postmeta AS A 
                LEFT JOIN '.$wpdb->prefix.'postmeta B ON A.post_id = B.post_id 
                LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
                LEFT JOIN '.$wpdb->prefix.'postmeta D ON A.post_id = D.post_id
                LEFT JOIN '.$wpdb->prefix.'postmeta E ON A.post_id = E.post_id 
                LEFT JOIN '.$wpdb->prefix.'posts F ON A.post_id = F.ID
                WHERE A.meta_key = "oimwc_low_stock_threshold_level" AND B.meta_key = "_stock" 
                AND C.meta_key = "oimwc_show_in_low_stock" AND C.meta_value = "yes" 
                AND D.meta_key = "_manage_stock" AND D.meta_value = "yes"
                AND CAST(A.meta_value AS SIGNED)  >= CAST(B.meta_value AS SIGNED)
                AND A.post_id NOT IN('.$ids.') AND F.post_status IN ("private","publish")
                AND E.meta_key = "oimwc_supplier_id" AND E.meta_value '. $compare . $supplier_id;
        $total_count = $wpdb->get_col( $query );
    }

    $query = 'SELECT '. $select_var .'(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
            LEFT JOIN '.$wpdb->prefix.'postmeta B ON A.post_id = B.post_id 
            LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            LEFT JOIN '.$wpdb->prefix.'postmeta D ON A.post_id = D.post_id
            LEFT JOIN '.$wpdb->prefix.'additional_supplier_info  E ON A.post_id = E.product_id  
            LEFT JOIN '.$wpdb->prefix.'posts F ON A.post_id = F.ID
            WHERE A.meta_key = "oimwc_low_stock_threshold_level" AND B.meta_key = "_stock" 
            AND C.meta_key = "oimwc_show_in_low_stock" AND C.meta_value = "yes" 
            AND D.meta_key = "_manage_stock" AND D.meta_value = "yes"
            AND CAST(A.meta_value AS SIGNED)  >= CAST(B.meta_value AS SIGNED)
            AND A.post_id NOT IN('.$ids.') AND F.post_status IN ("private","publish")
            AND E.variable_id = 0 AND E.supplier_id '. $compare . $supplier_id;
    $total_simple_count = $return_total ? $wpdb->get_var( $query ) : $wpdb->get_col( $query );

    $query = 'SELECT '. $select_var .'(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
            LEFT JOIN '.$wpdb->prefix.'postmeta B ON A.post_id = B.post_id 
            LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            LEFT JOIN '.$wpdb->prefix.'postmeta D ON A.post_id = D.post_id
            LEFT JOIN '.$wpdb->prefix.'additional_supplier_info  E ON A.post_id = E.variable_id  
            LEFT JOIN '.$wpdb->prefix.'posts F ON A.post_id = F.ID
            WHERE A.meta_key = "oimwc_low_stock_threshold_level" AND B.meta_key = "_stock" 
            AND C.meta_key = "oimwc_show_in_low_stock" AND C.meta_value = "yes" 
            AND D.meta_key = "_manage_stock" AND D.meta_value = "yes"
            AND CAST(A.meta_value AS SIGNED)  >= CAST(B.meta_value AS SIGNED)
            AND A.post_id NOT IN('.$ids.') AND F.post_status IN ("private","publish")
            AND E.supplier_id '. $compare . $supplier_id;
    $total_variable_count = $return_total ? $wpdb->get_var( $query ) : $wpdb->get_col( $query );

    
    if( $return_total ){
        $total_count = $total_count + $total_simple_count + $total_variable_count;
        wp_cache_add( 'oimwc_supplier_count_'.$supplier_id, $total_count, 'oimwc_low_stock_products_cache' );
    }else{
        $total_count = array_merge( $total_count, $total_simple_count, $total_variable_count );
    }

    return $total_count;
}

function oimwc_additional_supplier_from_product( $product_id, $simple_product = true ){
    global $wpdb;
    $table_name = $wpdb->prefix.'additional_supplier_info';
    $field_name = 'variable_id';
    $where = '';
    if( $simple_product ){
        $field_name = 'product_id';
        $where = ' variable_id = 0 AND ';
    }
    $prepared_statement = $wpdb->prepare( "SELECT supplier_id FROM {$table_name} WHERE {$where} {$field_name} = %d", $product_id );
    return $wpdb->get_col( $prepared_statement );
}

function oimwc_additional_supplier_details_from_product( $product_id, $supplier_id ){
    global $wpdb, $oimwc_asdfp_cache;

    if( isset( $oimwc_asdfp_cache[ $product_id ] ) && isset( $oimwc_asdfp_cache[ $product_id ][ $supplier_id] ) ){
        return $oimwc_asdfp_cache[ $product_id ][ $supplier_id];
    }
    $table_name = $wpdb->prefix.'additional_supplier_info';
    $prepared_statement = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE ( product_id = %d OR variable_id = %d ) AND supplier_id = %d", $product_id, $product_id, $supplier_id );
    $data = $wpdb->get_row( $prepared_statement, ARRAY_A );
    $oimwc_asdfp_cache[ $product_id ][ $supplier_id] = $data;

    return $data;
}

function oimwc_all_supplier_low_stock_count( $product_id ){
    $supplier_id = get_post_meta( $product_id, 'oimwc_supplier_id', true );
    $product_supplier_list = oimwc_additional_supplier_from_product( $product_id );
    $variation_supplier_list = oimwc_additional_supplier_from_product( $product_id, false );
    $product_supplier_list = array_merge( array( $supplier_id ),  $product_supplier_list, $variation_supplier_list );
    if( is_array( $product_supplier_list ) && count( $product_supplier_list ) ){
        foreach ($product_supplier_list as $additional_supplier ) {
            oimwc_supplier_low_stock_count( 0, $additional_supplier );   
            oimwc_show_all_product_stock_count( $additional_supplier, false, '=', true); 
        }
    }
    return get_option('oimwc_total_lowstock_supplier_count');
}

function oiwmc_get_supplier_with_link( $supplier_id ){
    $short_name = get_post_meta( $supplier_id, 'oimwc_supplier_short_name', true );
    $short_name = $short_name ? $short_name : get_the_title( $supplier_id );
    $short_name = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $supplier_id ), $short_name );
    return $short_name;
}

function oimwc_additional_supplier_information( $product_id, $type){
    global $wpdb;
    $table_name = $wpdb->prefix.'additional_supplier_info';
    $where = '';
    if( $type  == '_product_id'){
        $field_name = 'product_id';
        $where = ' variable_id = 0 AND product_id IN ('.$product_id.') AND';
    }
    else
    {
        $field_name = 'variable_id';
    }
    $prepared_statement = $wpdb->prepare( "SELECT product_id,MIN(purchase_price/pack_size) as price FROM {$table_name} WHERE {$where} {$field_name} = %d", $product_id );
    $purchase_price_array = $wpdb->get_results( $prepared_statement,ARRAY_A);
    if(!empty($purchase_price_array))
    {
        return $purchase_price_array;
    }
}

function oimwc_show_all_product_stock_count( $supplier_id, $return_total = true, $compare =  '=',$show_all_product = true ){
    global $wpdb;
    if( ! $supplier_id || empty( $supplier_id ) ){
        return;
    }
    if( $return_total ){
        $supplier_cache = wp_cache_get( 'oimwc_supplier_count_'.$supplier_id, 'oimwc_low_stock_products_cache' );
        if( $supplier_cache ){
            //return $supplier_cache;
        }
    }
    $variable_products = wp_cache_get( 'oimwc_variable_products_'.$supplier_id, 'oimwc_low_stock_products_cache' );
    if(!$variable_products){
        $sql = 'SELECT DISTINCT(A.post_parent) FROM '.$wpdb->posts.' AS A LEFT JOIN '.$wpdb->postmeta.' AS B ON A.id = B.post_id WHERE A.post_parent > 0 AND A.post_type = "product_variation" AND B.meta_key = "oimwc_supplier_id" AND B.meta_value = "'. $supplier_id .'"';
        $variable_products = $wpdb->get_col( $sql );
        wp_cache_add( 'oimwc_variable_products_'.$supplier_id, $variable_products, 'oimwc_low_stock_products_cache' );
    }
    if($supplier_id != 'all' && isset($supplier_id)){
        $ordered_product = OIMWC_Order::get_ordered_product($supplier_id,false,'=');
    }else{
        $ordered_product = OIMWC_Order::get_ordered_product(0,false,'=');
    }
    $post_not_in = $variable_products;
    $post_not_in = array_unique( $post_not_in );
    $ids = 0;
    if( count( $post_not_in ) ){
        $ids = implode(',', $post_not_in);
    }
    $select_var = ''; 
    $total_count = 0;
    if( $return_total ){
        // Fetch simple additional supplier products.
        $simple_product_supplier_query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A LEFT JOIN '.$wpdb->prefix.'additional_supplier_info B ON A.post_id = B.product_id LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            WHERE B.variable_id = 0 AND B.supplier_id = '. $supplier_id. ' AND A.meta_key = "oimwc_low_stock_threshold_level" AND C.meta_key = "_stock" 
                AND CAST(A.meta_value AS SIGNED) >= CAST(C.meta_value AS SIGNED)';
        $additional_supplier_ids = $wpdb->get_col($simple_product_supplier_query);

        // Fetch variable additional supplier products.
        $variable_product_supplier_query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A LEFT JOIN '.$wpdb->prefix.'additional_supplier_info B ON A.post_id = B.variable_id LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            WHERE B.supplier_id = '. $supplier_id.' AND A.meta_key = "oimwc_low_stock_threshold_level" AND C.meta_key = "_stock" 
                AND CAST(A.meta_value AS SIGNED) >= CAST(C.meta_value AS SIGNED)';
        $variable_supplier_ids = $wpdb->get_col($variable_product_supplier_query);

        // Fetch all low stock products.
        $query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
                LEFT JOIN '.$wpdb->prefix.'postmeta B ON A.post_id = B.post_id 
                WHERE A.meta_key = "_stock_status" IN ("instock", "outofstock","onbackorder") AND B.meta_key = "oimwc_low_stock_threshold_level"';
        $all_post_ids = $wpdb->get_col($query);

        // Fetch products supplier wise.
        $query = 'SELECT A.post_id FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "oimwc_supplier_id" AND A.meta_value = '. $supplier_id. ' AND A.post_id IN ( '. implode(',', $all_post_ids) .' )';
        $post_ids = $wpdb->get_col($query);
        $post_ids = array_merge($additional_supplier_ids,$variable_supplier_ids,$post_ids);

        // For check show in low stock or not.
        $query = 'SELECT A.post_id FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "oimwc_show_in_low_stock" AND A.meta_value = "yes" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);

        // For check products are not discontinued.
        $query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "oimwc_discontinued_product" AND A.meta_value = "no" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);

        // For check manage stock or not.
        $query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "_manage_stock" AND A.meta_value = "yes" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);
        if(count($post_ids) > 0){
        $lowstock_array = array();
        $requested_stock = array();
        $product_ids = array();
        $order_ids = array();
        $lowstock_list = array_merge($ordered_product,$post_ids);
        foreach ($lowstock_list as $value) {
            $lowstock_threshold = get_post_meta($value,'oimwc_low_stock_threshold_level',true);
            $lowstock_array[$value] = $lowstock_threshold;
        }

        $lowstock_ids = implode(', ', array_unique(($ordered_product)));
        $query = 'SELECT DISTINCT(product_id), SUM(stock + requested_stock) AS total FROM '.$wpdb->prefix.'order_inventory WHERE product_id IN ('.$lowstock_ids.')';
        if($supplier_id){
            $query .= ' AND supplier_id =' . $supplier_id.' GROUP BY product_id';
        }else{
            $query .= ' GROUP BY product_id';
        }
        $lowstock_result = $wpdb->get_results($query,ARRAY_A);
        if ($lowstock_result) {
            foreach ($lowstock_result as $key => $val) {
                $requested_stock[$val['product_id']] = $val['total'];
            }
        }
        ksort($lowstock_array);
        ksort($requested_stock);
        foreach($lowstock_array as $key => $val){
            if(array_key_exists($key, $requested_stock))
            {
                if($val >= $requested_stock[$key]){
                    array_push($product_ids, $key);
                }
            }else{
                array_push($order_ids, $key);
            }
        }
        $post_in = array_merge($product_ids,$order_ids);
        $total_count = $post_in;
        }else{
            $total_count = '';
        }
    }else{
        // Fetch simple additional supplier products.
        $simple_product_supplier_query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A LEFT JOIN '.$wpdb->prefix.'additional_supplier_info B ON A.post_id = B.product_id LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            WHERE B.variable_id = 0 AND B.supplier_id = '. $supplier_id. ' AND A.meta_key = "oimwc_low_stock_threshold_level" AND C.meta_key = "_stock" 
                AND CAST(A.meta_value AS SIGNED) >= CAST(C.meta_value AS SIGNED)';
        $additional_supplier_ids = $wpdb->get_col($simple_product_supplier_query);

        // Fetch variable additional supplier products.
        $variable_product_supplier_query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A LEFT JOIN '.$wpdb->prefix.'additional_supplier_info B ON A.post_id = B.variable_id LEFT JOIN '.$wpdb->prefix.'postmeta C ON A.post_id = C.post_id
            WHERE B.supplier_id = '. $supplier_id.' AND A.meta_key = "oimwc_low_stock_threshold_level" AND C.meta_key = "_stock" 
                AND CAST(A.meta_value AS SIGNED) >= CAST(C.meta_value AS SIGNED)';
        $variable_supplier_ids = $wpdb->get_col($variable_product_supplier_query);

        // Fetch products supplier wise.
        $query = 'SELECT A.post_id FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "oimwc_supplier_id" AND A.meta_value = '. $supplier_id;
        $post_ids = $wpdb->get_col($query);
        $post_ids = array_merge($additional_supplier_ids,$variable_supplier_ids,$post_ids);

        // Fetch all low stock products.
        $query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
                LEFT JOIN '.$wpdb->prefix.'postmeta B ON A.post_id = B.post_id 
                WHERE A.meta_key = "_stock_status" IN ("instock", "outofstock","onbackorder") AND B.meta_key = "oimwc_low_stock_threshold_level" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);

        // For check show in low stock or not.
        $query = 'SELECT A.post_id FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "oimwc_show_in_low_stock" AND A.meta_value = "yes" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);

        // For check manage stock or not.
        $query = 'SELECT DISTINCT(A.post_id) FROM '.$wpdb->prefix.'postmeta AS A 
                WHERE A.meta_key = "_manage_stock" AND A.meta_value = "yes" AND A.post_id IN ( '. implode(',', $post_ids) .' )';
        $post_ids = $wpdb->get_col($query);
        if(count($post_ids) > 0){
            $lowstock_list = array_merge($ordered_product,$post_ids);
            $post_ids = implode(',',$post_ids);

            $lowstock_array = array();
            $requested_stock = array();
            $product_ids = array();
            $order_ids = array();
            foreach ($lowstock_list as $value) {
                $lowstock_threshold = get_post_meta($value,'oimwc_low_stock_threshold_level',true);
                $lowstock_array[$value] = $lowstock_threshold;
            }

            $lowstock_ids = implode(', ', array_unique(($ordered_product)));
            $query = 'SELECT DISTINCT(product_id), SUM(stock + requested_stock) AS total FROM '.$wpdb->prefix.'order_inventory WHERE product_id IN ('.$lowstock_ids.')';
            if($supplier_id){
                $query .= ' AND supplier_id =' . $supplier_id.' GROUP BY product_id';
            }else{
                $query .= ' GROUP BY product_id';
            }
            $lowstock_result = $wpdb->get_results($query,ARRAY_A);
            if ($lowstock_result) {
                foreach ($lowstock_result as $key => $val) {
                    $requested_stock[$val['product_id']] = $val['total'];
                }
            }
            ksort($lowstock_array);
            ksort($requested_stock);
            foreach($lowstock_array as $key => $val){
                if(array_key_exists($key, $requested_stock))
                {
                    if($val >= $requested_stock[$key]){
                        array_push($product_ids, $key);
                    }
                }else{
                    array_push($order_ids, $key);
                }
            }
            $post_in = array_merge($product_ids,$order_ids);
            $args = array(
                'post_type' => array('product', 'product_variation'),
                'meta_query' => array(
                    'relation' => 'OR',
                        array(
                         'key' => 'oimwc_discontinued_product',
                         'compare' => 'NOT EXISTS',
                         'value' => ''
                        ),
                        array(
                         'key' => 'oimwc_discontinued_product',
                         'value' => 'no'
                        )
                    ),
                'post__in' => $post_in,
                'post_status' => array( 'private', 'publish' ),
            );
            $product_list = new WP_Query( $args );
            $total_count = $product_list->found_posts;
        }else{
            $total_count = 0;
        }
    }
    if( $return_total ){
        $total_count = $post_in;
        wp_cache_add( 'oimwc_supplier_count_'.$supplier_id, $total_count, 'oimwc_low_stock_products_cache' );
    }else{
        $total_count = $total_count;
        if($show_all_product){
            update_post_meta( $supplier_id, 'oimwc_show_all_products', $total_count);
        }
    }
    
    return $total_count;
}

?>