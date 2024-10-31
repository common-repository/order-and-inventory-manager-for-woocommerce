<script type="text/html" id="tmpl-completed_po">
	<tr>
		<td class="col-order_number" scope="row" data-label="No.">{{data.order_number}}</td>
		<td class="col-supplier" data-label="Supplier">{{data.supplier_name}}</td>
		<td class="col-order_date" data-label="Created">{{data.order_date_time}}</td>
		<td class="col-total_products" data-label="Products">{{data.total_products}}</td>
		<td class="col-total_purchase" data-label="Subtotal">{{data.total_purchase}}</td>
		<td class="col-arrived" data-label="Arrived">{{data.total_awaiting_products}} / {{data.total_products}}</td>
		<# if( data.order_type == 'active_orders' ){ #>
		<td data-label="Status">{{data.status}}</td>
		<# } #>
		<td class="po_action_btns" data-supplier_id="{{data.supplier_id}}" data-order_date="{{data.order_date}}" data-default_cols="{{data.default_cols}}" data-default_po_lang="{{data.po_default_lang}}" data-label="" data-default_ship_address="{{data.default_ship_add}}" data-download_pdf="{{data.download_pdf}}" data-supplier-email="{{data.supplier_email}}" data-email-subject="{{data.po_email_subject}}" data-email-message="{{data.po_email_message}}" data-default_save_flag="{{data.save_default_sett}}" data-delivery_date="{{data.delivery_date}}" data-shipping_method="{{data.shipping_method}}" data-shipping_terms="{{data.shipping_terms}}" data-po_attn="{{data.po_attn}}" data-file_name="{{data.attach_filename}}">
			<a class="button wc-action-button tips go_to_order" href="{{data.view_order_url}}" data-tip="{{data.goToOrder_txt}}"><i class="fas fa-arrow-right"></i>
			</a>
			<a class="wc-action-button button add_info_po_btn tips {{data.add_info_cls}}" data-add_info="{{data.additional_info}}" data-supplier_id="{{data.supplier_id}}" data-order_date="{{data.order_date}}" data-tip="{{data.additional_info_txt}}"><i class="fas fa-clipboard"></i></a>
			<a class="wc-action-button button add_private_note_btn tips {{data.private_cls}} " data-private_note="{{data.private_note}}" data-supplier_id="{{data.supplier_id}}" data-order_date="{{data.order_date}}" data-tip="{{data.private_note_txt}}"><i class="far fa-sticky-note"></i></a>
			<a target="_blank" class="wc-action-button button tips download_po_btn" data-tip="{{data.download_po_txt}}"><i class="fas fa-file-alt"></i>
			</a>
			<# if( data.order_type == 'active_orders' ){ #>
			<a class="button wc-action-button lock_order_btn tips {{data.disable_lock}}" data-lock="1" data-tip="{{data.lock_title}}"><i class="{{data.cls}}"></i>
			</a>
			<div class="tips" data-tip="{{data.est_arrival_txt}}">
				<input type="text" placeholder="ETA" class="arrival_date_cls button hasDatepicker {{data.disable_lock}}" value="{{data.arrival_date}}" style="{{data.style}}" {{data.disabled}}>
			</div>
			<div class="tips" data-tip="{{data.cancel_order_txt}}">
				<input type="button" class="button cancel_awaiting_order {{data.disable_lock}}" value="Cancel" style="{{data.style}}" {{data.disabled}}>
			</div>
			<# } #>
		</td>
	</tr>
</script>