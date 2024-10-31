<select name="filter_order" id="dropdown_shop_order_payment_method">
	<option value="0">
		<?php esc_html_e( 'All order stock statuses', 'order-and-inventory-manager-for-woocommerce' ); ?>
	</option>
	<option <?php echo ( isset( $_GET['filter_order'] ) && sanitize_text_field($_GET['filter_order']) == 3 ? 'selected' : '' ); ?> value="3"><?php esc_html_e( 'In stock', 'order-and-inventory-manager-for-woocommerce' ); ?></option>
	<option <?php echo ( isset( $_GET['filter_order'] ) && sanitize_text_field($_GET['filter_order']) == 4 ? 'selected' : '' ); ?> value="4"><?php esc_html_e( 'Unmanaged stock', 'order-and-inventory-manager-for-woocommerce' ); ?></option>
	<option <?php echo ( isset( $_GET['filter_order'] ) && sanitize_text_field($_GET['filter_order']) == 1 ? 'selected' : '' ); ?> value="1"><?php esc_html_e( 'In stock + Unmanaged stock', 'order-and-inventory-manager-for-woocommerce' ); ?></option>
	<option <?php echo ( isset( $_GET['filter_order'] ) && sanitize_text_field($_GET['filter_order']) == 2 ? 'selected' : '' ); ?> value="2"><?php esc_html_e( 'Out of stock', 'order-and-inventory-manager-for-woocommerce' ); ?></option>
</select>