<select name="filter_supplier" id="filter_supplier">
	<option value="0">
		<?php esc_html_e( 'All Suppliers','order-and-inventory-manager-for-woocommerce' ); ?>
	</option>
	<?php
		$args = array(
			'post_type' => 'supplier',
			'order' => 'ASC',
			'posts_per_page' => -1
		);

		$supliers = get_posts($args);

		if ( $supliers ) {
		    foreach ( $supliers as $post ){  
		        setup_postdata( $post );
		        $supplier_id = $post->ID;
		        $supplier_custom_name = get_post_meta($supplier_id,'oimwc_supplier_short_name',true);
		        $selected = ( isset($_GET['filter_supplier']) && sanitize_text_field($_GET['filter_supplier']) == $supplier_id) ? 'selected' : '';
		        echo '<option value="'.$supplier_id.'" '.$selected.'>'.$supplier_custom_name.'</option>';
		        
		    }
	    	wp_reset_postdata();
	    }

	?>

</select>