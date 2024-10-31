<!-- Add temporary product popup -->
<div id="add-temp-product-form" title="<?php echo __('Add New Temporary Product','order-and-inventory-manager-for-woocommerce'); ?>" style="display:none;"> 
  <form name="new_product_form" id="new_product_form">
      <div class="tmp_product_name tmp_product_field">
        <p><?php esc_html_e('Shop Product Name','order-and-inventory-manager-for-woocommerce'); ?></p>
        <input type="text" class="tmp_product_name" id="tmp_product_name" name="tmp_product_name" value="" />
        <hr/>
      </div>
      <div class="tmp_variant_name tmp_product_field">
        <p><?php esc_html_e('Shop Variant Name','order-and-inventory-manager-for-woocommerce'); ?></p>
        <input type="text" class="tmp_variant_name" id="tmp_variant_name" name="tmp_variant_name" value="" />
        <hr/>
      </div>
      <div class="tmp_product_id tmp_product_field">
        <p><strong><?php esc_html_e('Supplier Product ID','order-and-inventory-manager-for-woocommerce'); ?></strong><em> *</em></p>
        <input type="text" class="tmp_product_id" id="tmp_product_id" name="tmp_product_id" value="" />
        <hr/>
      </div>
      <div class="tmp_product_url tmp_product_field">
        <p><strong><?php esc_html_e('Product URL','order-and-inventory-manager-for-woocommerce'); ?></strong><em> *</em></p>
        <input type="text" class="tmp_product_url" id="tmp_product_url" name="tmp_product_url" value="" />
        <hr/>
      </div>
      <div class="tmp_pack_size tmp_product_field">
        <p><?php esc_html_e('Supplier pack size','order-and-inventory-manager-for-woocommerce'); ?></p>
        <input type="text" class="tmp_pack_size" id="tmp_pack_size" name="tmp_pack_size" value="" />
        <hr/>
      </div>
      <div class="tmp_product_notes tmp_product_field">
        <p><?php esc_html_e('Notes','order-and-inventory-manager-for-woocommerce'); ?></p>
        <input type="text" class="tmp_product_notes" id="tmp_product_notes" name="tmp_product_notes" value="" />
        <hr/>
      </div>
      <div class="tmp_product_qty tmp_product_field">
        <p><?php esc_html_e('Qty','order-and-inventory-manager-for-woocommerce'); ?></p>
        <input type="text" class="tmp_product_qty" id="tmp_product_qty" name="tmp_product_qty" value="" />
        <hr/>
      </div>
      <div class="tmp_product_price tmp_product_field">
        <p><?php esc_html_e('Product Price','order-and-inventory-manager-for-woocommerce'); ?></p>
        <input type="text" class="tmp_product_price" id="tmp_product_price" name="tmp_product_price" value="" />
        <hr />
      </div>
      <p style="margin-bottom: -5px; font-size: 12px; margin-top: 2px;"><strong><?php echo '* '; esc_html_e('Required data: Supplier Product ID or Product URL','order-and-inventory-manager-for-woocommerce'); ?></strong></p>
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
  </form>
</div>