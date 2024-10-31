<script type="text/html" id="tmpl-view_purchase_order">
	<tr>
		<td class="thumb column-thumb has-row-actions column-primary" data-colname="Image">
			<img width="75" height="75" src="{{data.thumb_url}}" class="attachment-75x75 size-75x75 wp-post-image" alt="" sizes="(max-width: 75px) 100vw, 75px">
			<div class="mobile_prod_info">
				<div>{{data.product_title_without_url}}</div>
				<div class="{{ data.product_class }}_product">{{data.product_variant}}</div>
			</div>
			<button type="button" class="toggle-row">
				<span class="screen-reader-text">Show more details</span>
			</button>
		</td>
		<td class="product_info column-product_info" data-colname="Product Info">
			<div>
				<div>
					<a href="{{data.product_link}}">{{data.product_title_without_url}}</a>
				</div>
				<div class="{{ data.product_class }}_product"><?php echo __('Variant','order-and-inventory-manager-for-woocommerce')?>: {{data.product_variant}}</div>
				<div class="product_sku"><?php echo __('Product ID','order-and-inventory-manager-for-woocommerce');?>: {{data.product_sku}}</div>
			</div>
		</td>
		<td class="supplier_info column-supplier_info" data-colname="Supplier Info">
			<div>
				<div><?php echo __('URL','order-and-inventory-manager-for-woocommerce')?>: 
					{{data.product_supplier}}
				</div>
				<div><?php echo __('Supplier pack size','order-and-inventory-manager-for-woocommerce')?>: {{data.order_supplier_pack_size}}</div>
			</div>
		</td>
		<td class="product_detail column-product_detail" data-colname="Product Price & Stock">
			<div>
				<div><?php echo __('Shop price','order-and-inventory-manager-for-woocommerce')?>: {{data.price}}
				</div>
				<div><?php echo __('Shop pack size','order-and-inventory-manager-for-woocommerce')?>: {{data.our_pack_size}}</div>
				<div><?php echo __('Items in stock','order-and-inventory-manager-for-woocommerce')?>: {{data.product_stock}}</div>
				<div><?php echo __('Physical units in stock','order-and-inventory-manager-for-woocommerce')?>: {{data.total_pieces}}</div>
			</div>
		</td>
		<td class="order_info column-order_info" data-colname="Order Info">
			<div>
				<div>{{data.ordered_pieces_str}}</div>
				<div><?php echo __('Total','order-and-inventory-manager-for-woocommerce')?> : {{data.total}}</div>
				<div><?php echo __('ETA','order-and-inventory-manager-for-woocommerce')?> : {{data.arrival_date}}</div>
				{{data.arrival_status}}
			</div>
		</td>
		<# if( data.po_type != 'completed' ){ #>
		<td class="action column-action" data-colname="Process product">
			<input type="text" placeholder="<?php _e('Qty','order-and-inventory-manager-for-woocommerce');?>" class="arrived_qty_handler {{data.hide}}" data-id="{{data.table_id}}">
    		<input type="button" data-id="{{data.table_id}}" class="btnOrderSave button tips {{data.hide}}" value="<?php _e('Arrived','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('Update stock with entered qty','order-and-inventory-manager-for-woocommerce');?>">
    		<input type="button" data-product_id="{{data.id}}" data-id="{{data.table_id}}" class="btnOrderFullyArrived button tips {{data.hide}}" value="<?php _e('Fully arrived','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('Update stock with ordered qty','order-and-inventory-manager-for-woocommerce')?>">
    		<input type="button" data-product_id="{{data.id}}" data-id="{{data.table_id}}" data-requested_stock="{{data.requested_stock}}" data-arrvived_stock="{{data.arrvived_stock}}" class="btnFinalizeProduct button tips {{data.hide}}" value="<?php _e('Finalize','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('No further qty of this product will arrive in this shipment','order-and-inventory-manager-for-woocommerce')?>">
    		<# if( data.po_type == 'pending' ){ #>
    		<input type="button" data-product_id="{{data.id}}" data-id="{{data.table_id}}" class="btnRemoveProduct button tips {{data.lock_product_hide}}" value="<?php _e('Remove','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('Remove product from purchase order','order-and-inventory-manager-for-woocommerce')?>">
    		<# } #>	
    	</td>
    	<# } #>
    </tr>
    <tr>
    	<# if( data.po_type != 'completed' ){ #>
		<td colspan="6">
		<# } else { #>	
		<td colspan="5">
		<# } #>		
			<div class="table_seperator"></div>
		</td>
	</tr>
</script>