<form name="order_status_form" id="order_status_form" method="post" action="#">
	<select name="order_filter" id="dropdown_order_filter">
		<option value="">
			<?php esc_html_e( 'All Orders', 'order-and-inventory-manager-for-woocommerce' ); ?>
		</option>
		<option <?php echo ( isset( $_POST['order_filter'] ) && sanitize_text_field($_POST['order_filter']) == 1 ? 'selected' : '' ); ?> value="1"><?php esc_html_e( 'Orders with all in stock', 'order-and-inventory-manager-for-woocommerce' ); ?>
		</option>
		<option <?php echo ( isset( $_POST['order_filter'] ) && sanitize_text_field($_POST['order_filter']) == 2 ? 'selected' : '' ); ?> value="2"><?php esc_html_e( 'Orders with products that are out of stock', 'order-and-inventory-manager-for-woocommerce' ); ?>
		</option>
		<option <?php echo ( isset( $_POST['order_filter'] ) && sanitize_text_field($_POST['order_filter']) == 'yes' ? 'selected' : '' ); ?> value="yes"><?php esc_html_e( 'Orders with discontinued products', 'order-and-inventory-manager-for-woocommerce' ); ?>
		</option>
	</select>
	<button type="submit" name="submit_btn" value="Filter" class="button">Filter</button>
</form>