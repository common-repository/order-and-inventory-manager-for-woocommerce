<script type="text/html" id="tmpl-additional_supplier">
	<div class="accordion-inner" data-supplier_id="{{data.supplier_id}}">
		<div class="link">
			<span class="supplier_title">{{data.supplier_name}}</span>
			<a href="#" class="remove_supplier delete">{{data.remove_txt}}</a>
		</div>
		<div class="submenu">
			<div>
				<input type="hidden" data-name="supplier_id" value="{{data.supplier_id}}">
				<p class="form-field oimwc_supplier_product_id_field silver_version form-row form-row-first">
					<label for="oimwc_supplier_product_id">{{data.supplier_prod_id}}</label>
					<span class="woocommerce-help-tip"></span>
					<input type="text" class="short" style="" data-name="product_id" id="oimwc_supplier_product_id" value="" placeholder="">
				</p>
				<p class="form-field oimwc_supplier_product_url_field silver_version form-row form-row-last">
					<label for="oimwc_supplier_product_url">{{data.supplier_prod_url}}</label>
					<span class="woocommerce-help-tip"></span>
					<input type="text" class="short wc_input_url" style="" data-name="product_url" id="oimwc_supplier_product_url" value="" placeholder="">
				</p>
				<p class="form-field oimwc_supplier_note_field form-row form-row-first silver_version">
					<label for="oimwc_supplier_note">{{data.product_notes}}</label>
					<span class="woocommerce-help-tip"></span>
					<input type="text" class="short" style="" data-name="supplier_note" id="oimwc_supplier_note" value="" placeholder=""> 
				</p>
				<p class="form-field oimwc_supplier_purchase_price_field form-row form-row-last  silver_version">
					<label for="oimwc_supplier_purchase_price">{{data.purchase_price}}{{data.currency}}</label>
					<input type="text" class="short wc_input_price" style="" data-name="purchase_price" id="oimwc_supplier_purchase_price" value="0.00" placeholder=""> 
				</p>
				<p class="form-field oimwc_supplier_pack_size_field form-row form-row-first gold_version">
					<label for="oimwc_supplier_pack_size">{{data.pack_size}}</label>
					<span class="woocommerce-help-tip"></span>
					<input type="number" class="short wc_input_stock" style="" data-name="pack_size" id="oimwc_supplier_pack_size" value="1" placeholder=""> 
				</p>
				<div class="supplier_btn">
					<button type="button" class="button-primary save_supplier" data-id="{{data.supplier_id}}" data-index="{{data.key}}"><?php _e('Save supplier info','order-and-inventory-manager-for-woocommerce');?></button>
					<span class="spinner"></span>
				</div>
			</div>
		</div>
	</div>
</script>