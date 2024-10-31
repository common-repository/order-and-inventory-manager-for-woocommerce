<?php
global $wpdb;

$sql = array(
        'post_type' => array('product', 'product_variation'),
        'numberposts' => -1,
        'meta_query' => array(
            'relation' => 'AND',
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

$product_list = get_posts($sql);
if ($product_list) {
    foreach ($product_list as $product_row) {
        $id = $product_row->ID;
        $flag = true;
        $product_obj = wc_get_product($id);
        $available_variations = $product_obj->get_children();
        if( is_array($available_variations) && count($available_variations) > 0 ){
            $flag = false;
        }

        if($flag){
            $_stock = get_post_meta($id,'_stock',true);
        	$physical_stock = get_post_meta($id,'oimwc_physical_stock',true);
            $physical_stock = !empty($physical_stock) ? $physical_stock : (!empty($_stock) ? $_stock : 0);
            $pack_size = get_post_meta($id,'oimwc_our_pack_size',true);

        	$pack_size = !empty($pack_size) ? $pack_size : 1;
        	$total_pieces = floor($physical_stock * $pack_size);
        	update_post_meta($id,'oimwc_physical_units_stock',$total_pieces);
    	}
        
    }
}

?>