<?php
/**
 * Displays add supplier meta box
 *
 * @since 1.0.0
 */
?>
<table class="form-table">
   <tbody>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_short_name"><?php _e('Custom name', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
            <input name="supplier_short_name" id="supplier_short_name" type="text" value="<?php echo esc_html($supplier_short_name); ?>" /> 						
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_address"><?php _e('Address', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <textarea name="supplier_address" id="supplier_address"><?php echo esc_textarea($supplier_address); ?></textarea>
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_country"><?php _e('Country', 'order-and-inventory-manager-for-woocommerce')?></label></label>
         </th>
         <td class="forminp">
			<select name="supplier_country" id="supplier_country">
				<option value=""><?php _e('Select country', 'order-and-inventory-manager-for-woocommerce')?></option>
				<?php 
				foreach( $countries as $key => $country ){
					$selected = $supplier_country == $key ? 'selected' : '';
					echo sprintf('<option %s value="%s">%s</option>', $selected, $key, __( $country ) );	
				}
				?>
			</selct>
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_website_url"><?php _e('Homepage URL', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <input name="supplier_website_url" id="supplier_website_url" type="text" value="<?php echo esc_html($supplier_website_url); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_order_url"><?php _e('URL for ordering', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <input name="supplier_order_url" id="supplier_order_url" type="text" value="<?php echo esc_html($supplier_order_url); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_currency"><?php _e('Currency', 'order-and-inventory-manager-for-woocommerce')?></label></label>
         </th>
         <td class="forminp">
 			<select id="supplier_currency" name="supplier_currency" data-placeholder="<?php esc_attr_e( 'Choose a currency&hellip;', 'woocommerce' ); ?>" class="location-input wc-enhanced-select dropdown">
 				<option value=""><?php esc_html_e( 'Choose a currency&hellip;', 'woocommerce' ); ?></option>
 				<?php 
				$currencies = get_woocommerce_currencies();
				asort($currencies);
				foreach ( $currencies as $code => $name ) :
					 ?>
 					<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $supplier_currency, $code ); ?>>
 						<?php printf( esc_html__( '%1$s (%2$s)', 'woocommerce' ), $name, get_woocommerce_currency_symbol( $code ) ); ?>
 					</option>
 				<?php endforeach; ?>
 			</select>
         </td>
      </tr><tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_description"><?php _e('Description of supplier', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <textarea name="supplier_description" id="supplier_description"><?php echo esc_textarea($supplier_description); ?></textarea>
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_email"><?php _e('General email address', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <input name="supplier_email" id="supplier_email" type="text" value="<?php echo esc_html($supplier_email); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_order_email"><?php _e('Email address for ordering', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <input name="supplier_order_email" id="supplier_order_email" type="text" value="<?php echo esc_html($supplier_order_email); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_skype_id"><?php _e('Skype name', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <input name="supplier_skype_id" id="supplier_skype_id" type="text" value="<?php echo esc_html($supplier_skype_id); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_phone_no"><?php _e('Phone number', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
			 <input name="supplier_phone_no" id="supplier_phone_no" type="text" value="<?php echo esc_html($supplier_phone_no); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_tax_no"><?php _e('Tax/VAT Number', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
          <input name="supplier_tax_no" id="supplier_tax_no" type="text" value="<?php echo esc_html($supplier_tax_no); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_contact_person"><?php _e('Contact person', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
          <input name="supplier_contact_person" id="supplier_contact_person" type="text" value="<?php echo esc_html($supplier_contact_person); ?>" />
         </td>
      </tr>
      <tr valign="top">
         <th scope="row" class="titledesc">
            <label for="supplier_products_lowstock_level"><?php _e('Low Stock Threshold Level', 'order-and-inventory-manager-for-woocommerce')?></label>
         </th>
         <td class="forminp forminp-text">
          <input name="supplier_products_lowstock_level" class="oimwc_restrict_characters_on_paste oimwc_allow_only_numbers" id="supplier_products_lowstock_level" type="number" min=
          "0" value="<?php echo esc_html($supplier_products_lowstock_level); ?>" />
          <span class="woocommerce-help-tip tips" data-tip="<?php esc_html_e('When set number of products from this supplier have reach its low stock threshold level, the supplier will warn about the low stock amount with a dot in the admin menu.');?>"></span>
         </td>
      </tr>
   </tbody>
</table>