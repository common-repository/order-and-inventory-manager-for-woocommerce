<script type="text/html" id="tmpl-add_product_existing_PO">
    <p>
        <select id="request_date" name="request_date">
            <option value=""><?php _e( 'Select order to add the product', 'order-and-inventory-manager-for-woocommerce' ); ?></option>
            <# _.each( data.supplier_order_list, function(value, index) { 
                if( data.order_status[index] == data.receiving_status ){ #>
                    <option value='{{index}}' disabled title="{{data.lock_title}}" data-id="{{value}}">{{value}}</option>
                <# } else { #>
                    <option value='{{index}}' data-id="{{value}}" name='{{value}}'>{{value}}</option>
                <# } #> 
            <# }) #>
        </select>
    </p>
    <input type="hidden" name="action" value="update_product_file" />
    <input type="hidden" name="product_id" value="{{data.product_id}}" />
    <input type="hidden" name="qty" value="{{data.qty}}" />
    <input type="hidden" name="stock" value="{{data.stock}}" />
    <input type="hidden" name="oimwc_product_nonce" value="{{data.oimwc_product_nonce}}" />
    <input type="hidden" name="selected_order_id" id="selected_order_id" value="" />
    <input type="hidden" name="supplier_id" value="{{data.supplier_id}}" />
    <input class="button button-primary" type="submit" value="<?php _e('Add to order','order-and-inventory-manager-for-woocommerce');?>" />
    <input class="cancel_order button" type="button" value="<?php _e('Cancel','order-and-inventory-manager-for-woocommerce');?>" />
</script>