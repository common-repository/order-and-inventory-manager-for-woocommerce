<script type="text/html" id="tmpl-lowstock_supplier_filter">
	<tr>
		<td class="thumb column-thumb has-row-actions column-primary" data-colname="Image">
			<img width="75" height="75" src="{{data.thumb_url}}" class="attachment-75x75 size-75x75 wp-post-image" alt="" srcset="" sizes="(max-width: 75px) 100vw, 75px">
			<# if( data.show_all_product ){ #>
				<input type="hidden" class="productId" name="productId" value="{{data.id}}" />
			<# } else { #>
				<span type="hidden" name="purchase_order_data" class="purchase_order_data" style="display: none;">{{data.purchase_data}}</span>
				<input type="hidden" class="productId product_{{data.id}}" name="productId" value="{{data.id}}" />
			<# } #>
			<div class="mobile_prod_info">
				<div>{{data.product_title_without_url}}</div>
				<div class="{{ data.product_class }}_product">{{data.product_variant}}</div>
			</div>
			<button type="button" class="toggle-row">
				<span class="screen-reader-text">Show more details</span>
			</button>
		</td>
		<td class="product_info column-product_info" data-colname="Product Info">
			<div class="{{data.temp_product_class}}">
				<div>
					<# if( data.temp_product ){ #>
						{{data.product_title_without_url}}
					<# }else{ #>
						<a href="{{data.product_edit_url}}">{{data.product_title_without_url}}</a>
					<# } #>
				</div>
				<div class="{{ data.product_class }}_product"><?php echo __('Variant','order-and-inventory-manager-for-woocommerce')?>: {{data.product_variant}}</div>
				<# if( !data.temp_product ){ #>
					<div class="product_sku"><?php echo __('Product ID','order-and-inventory-manager-for-woocommerce');?>: {{data.product_sku}}</div>
				<# } #>
				<# if( !data.temp_product ){ #>
					<div><?php echo __('Low stock threshold','order-and-inventory-manager-for-woocommerce')?>: {{data.warning_level}}</div>
				<# } #>
			</div>
		</td>
		<td class="supplier_info column-supplier_info" data-colname="Supplier Info">
			<div>
				<# if( data.product_supplier ){ #>
					<div><?php echo __('URL','order-and-inventory-manager-for-woocommerce')?>: 
						{{data.product_supplier}}
					</div>
				<# } #>
				<div><?php echo __('Purchase price','order-and-inventory-manager-for-woocommerce')?>:
					{{data.purchase_price}}
				</div>
				<div><?php echo __('Supplier pack size','order-and-inventory-manager-for-woocommerce')?>: {{data.supplier_pack_size}}</div>
				<# if( data.supplier_product_id ){ #>
					<div><?php echo __('Supplier Product ID','order-and-inventory-manager-for-woocommerce') ?>: 	{{data.supplier_product_id}}</div>
					</div>
				<# } #>
				<# if( data.supplier_note ){ #>
					<div><?php echo __('Product Notes','order-and-inventory-manager-for-woocommerce') ?>: {{data.supplier_note}}</div>
					</div>
				<# } #>
		</td>
		<td class="product_detail column-product_detail" data-colname="Product Price & Stock">
			<# if( !data.temp_product ){ #>
				<div>
					<div><?php echo __('Shop price','order-and-inventory-manager-for-woocommerce')?>: {{data.price}}
					</div>
					<div><?php echo __('Shop pack size','order-and-inventory-manager-for-woocommerce')?>: {{data.our_pack_size}}</div>
					<div><?php echo __('Items in stock','order-and-inventory-manager-for-woocommerce')?>: {{data.product_stock}}<# if(data.request_item) { #><?php echo ' (+'; ?>{{data.request_item}}<?php echo ' '.__('in order','order-and-inventory-manager-for-woocommerce').')'; ?><# } #></div>
					<div><?php echo __('Physical units in stock','order-and-inventory-manager-for-woocommerce')?>: {{data.total_pieces}}</div>
				</div>
			<# } #>
		</td>
		<td class="amount column-amount" data-colname="Qty">
			<# if( data.temp_product ){ #>
				<# if( data.qty != 0 && data.temp_product == true && data.all_supplier != true){ #>
					<div class="" data-tip="">
						<span><?php echo __('Qty','order-and-inventory-manager-for-woocommerce').': '; ?> {{data.qty}}</span>
					</div>
				<# } else { #> 
					<div class="" data-tip="">
						<input type="text" class="arrived_qty_handler" data-stock="{{data.product_stock}}" data-id="{{data.id}}" name="product[{{data.id}}][qty]" value="{{data.request_data}}">
					</div>
					<input type="hidden" name="product[{{data.id}}][stock]" value="{{data.product_stock}}">
					<input type="hidden" name="product[{{data.id}}][supplier]" value="{{data.supplier_id}}">
					<div class="product_calc">
						<# if( data.show_all_product == 1){ #>
							<span data-price="{{data.non_format_purchase_price}}" class="amount amount_{{data.id}}">{{data.po_price}}</span>
						<# }else { #>
							<span data-price="{{data.non_format_purchase_price}}" class="amount amount_{{data.id}}">{{data.po_price}}</span>
						<# } #>
				<span class="currency">{{data.supplier_currency}}</span>
			</div>
			<div class="" data-tip="">
				<input type="button" class="button btnAddItemToOrder" value="<?php echo __('Add to order','order-and-inventory-manager-for-woocommerce')?>">
				<# if( data.show_all_product == 1){ #>
					<input type="button" data-id="{{data.id}}" class="btnRemovePO button btn_{{data.id}}" value="<?php echo __('Remove','order-and-inventory-manager-for-woocommerce'); ?>" style="display: block;" />
				<# }else { #>
					<input type="button" data-id="{{data.id}}" class="btnRemoveProduct button btn_{{data.id}}" value="<?php echo __('Remove','order-and-inventory-manager-for-woocommerce'); ?>" style="display: block;" />
				<# } #>
			</div>
			<div class="temp_product"><input type="button" class="button btnRemoveProduct btn_{{data.id}}" data-id="{{data.id}}" value="<?php echo __('Remove', 'order-and-inventory-manager-for-woocommerce'); ?>" /></div>
			<# } #>
			<# }else{ #>
				<div class="" data-tip="">
					<input type="text" class="arrived_qty_handler" data-stock="{{data.product_stock}}" data-id="{{data.id}}" data-warning="{{data.warning_level}}" name="product[{{data.id}}][qty]" value="{{data.request_data}}">
				</div>
				<input type="hidden" name="product[{{data.id}}][stock]" value="{{data.product_stock}}">
				<input type="hidden" name="product[{{data.id}}][supplier]" value="{{data.supplier_id}}">
				<div class="product_calc">
					<# if( data.show_all_product == 1){ #>
						<span data-price="{{data.non_format_purchase_price}}" class="amount amount_{{data.id}}">{{data.po_price}}</span>
					<# }else { #>
						<span data-price="{{data.non_format_purchase_price}}" class="amount amount_{{data.id}}">0</span>
					<# } #>
					<span class="currency">{{data.supplier_currency}}</span>
				</div>
				<div class="" data-tip="">
					<input type="button" class="button btnAddItemToOrder" value="<?php echo __('Add to order','order-and-inventory-manager-for-woocommerce')?>">
					<# if( data.show_all_product == 1){ #>
						<input type="button" data-id="{{data.id}}" class="btnRemovePO button btn_{{data.id}}" value="<?php echo __('Remove','order-and-inventory-manager-for-woocommerce'); ?>" style="display: block;" />
					<# }else { #>
						<input type="button" data-id="{{data.id}}" class="btnRemovePO button btn_{{data.id}}" value="<?php echo __('Remove','order-and-inventory-manager-for-woocommerce'); ?>" style="display: none;" />
					<# } #>
				</div>
			<# } #>
		</td>
	</tr>
	<tr>
		<td colspan="5">
			<div class="table_seperator"></div>
		</td>
	</tr>
</script>