<script type="text/html" id="tmpl-awaiting_delivery_tbl">
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
				<div><?php echo __('Order No.','order-and-inventory-manager-for-woocommerce')?>: 
					{{data.order_number_link}}
				</div>
				<div>{{data.ordered_pieces_str}}</div>
				<div><?php echo __('Total','order-and-inventory-manager-for-woocommerce')?> : {{data.total}}</div>
				<div><?php echo __('PO date','order-and-inventory-manager-for-woocommerce')?>: {{data.po_date}}</div>
				<div><?php echo __('ETA','order-and-inventory-manager-for-woocommerce')?> : {{data.arrival_date}}</div>
				{{data.arrival_status}}
			</div>
		</td>
		<td class="action column-action" data-colname="Process product">
			<# if( data.temp_product ){ #>
				<input type="button" data-product_id="{{data.id}}" data-id="{{data.table_id}}" class="btnRemoveProduct button tips {{data.hide}}" data-page="delivery_page" value="<?php _e('Remove','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('Remove product from purchase order','order-and-inventory-manager-for-woocommerce')?>">
			<# }else{ #>
				<input type="text" placeholder="<?php _e('Qty','order-and-inventory-manager-for-woocommerce');?>" class="arrived_qty_handler" data-id="{{data.table_id}}" {{data.disabled}}>
	    		<input type="button" data-id="{{data.table_id}}" data-page="delivery_page" class="btnOrderSave button tips" value="<?php _e('Arrived','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('Update stock with entered qty','order-and-inventory-manager-for-woocommerce');?>" {{data.disabled}}>
	    		<input type="button" data-product_id="{{data.id}}" data-id="{{data.table_id}}" class="btnOrderFullyArrived button tips" data-page="delivery_page" value="<?php _e('Fully arrived','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('Update stock with ordered qty','order-and-inventory-manager-for-woocommerce')?>"{{data.disabled}}>
	    		<input type="button" data-product_id="{{data.id}}" data-id="{{data.table_id}}" data-supplier_id="{{data.supplier_id}}" data-order_date="{{data.full_order_date}}" class="btnFinalizeProduct button tips" data-page="delivery_page" value="<?php _e('Finalize','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('No further qty of this product will arrive in this shipment','order-and-inventory-manager-for-woocommerce')?>" {{data.disabled}}><br>
	    		<input type="button" data-product_id="{{data.id}}" data-id="{{data.table_id}}" class="btnRemoveProduct button tips {{data.hide}}" data-page="delivery_page" value="<?php _e('Remove','order-and-inventory-manager-for-woocommerce')?>" data-tip="<?php _e('Remove product from purchase order','order-and-inventory-manager-for-woocommerce')?>">
			<# } #>
    	</td>
    </tr>
    <tr>
		<td colspan="6">
			<div class="table_seperator"></div>
		</td>
	</tr>
</script>