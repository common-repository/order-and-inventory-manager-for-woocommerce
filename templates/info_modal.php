<!-- Private Note Dialog Modal -->
<div id="private-note-dialog-form" title="<?php 
echo  __( 'Private Notes', 'order-and-inventory-manager-for-woocommerce' ) ;
?>" style="display: none;"> 
	  <form>
	    <fieldset>
	    	<textarea name="private_note_txt" rows="" cols="" class="text ui-widget-content ui-corner-all private_note_cls"></textarea>
	      	<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
	    </fieldset>
	  </form>
</div>

<!-- Additional Information Note Dialog Modal -->
<div id="additional-info-dialog-form" title="<?php 
echo  __( 'Additional Information in PO', 'order-and-inventory-manager-for-woocommerce' ) ;
?>" style="display:none;"> 
  <form>
    <fieldset>
    	<div>
    		<textarea name="additional_info_txt" rows="" cols="" class="text ui-widget-content ui-corner-all additional_info_cls"></textarea>
    	</div>
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div>

<!-- Display Popup when downloading PO file -->
<div id="manage-po-dialog-form" title="<?php 
echo  __( 'Manage Purchase Order Information', 'order-and-inventory-manager-for-woocommerce' ) ;
?>" style="display:none;"> 
  <form name="download_po_form" id="download_po_form">
    <fieldset>
      <div class="manage_po_chks">
        <p><strong><?php 
esc_html_e( 'Choose information which will be included in the PO file:', 'order-and-inventory-manager-for-woocommerce' );
?></strong></p>
        <input type="checkbox" name="manage_po_chks[]" id="product_image" value="product_image"><?php 
esc_html_e( 'Product Image', 'order-and-inventory-manager-for-woocommerce' );
?>
        <br />
        <input type="checkbox" name="manage_po_chks[]" id="shop_product_name" value="shop_product_name"><?php 
esc_html_e( 'Shop Product Name', 'order-and-inventory-manager-for-woocommerce' );
?>
        <br/>
        <input type="checkbox" name="manage_po_chks[]" id="shop_variant_name" value="shop_variant_name"><?php 
esc_html_e( 'Shop Variant Name', 'order-and-inventory-manager-for-woocommerce' );
?>
        <br/>
        <input type="checkbox" name="manage_po_chks[]" id="product_url" value="product_url"><?php 
esc_html_e( 'Product URL', 'order-and-inventory-manager-for-woocommerce' );
?>
        <br/>
        <input type="checkbox" name="manage_po_chks[]" id="product_id" value="product_id"><?php 
esc_html_e( 'Product ID', 'order-and-inventory-manager-for-woocommerce' );
?>
        <br/>
        <input type="checkbox" name="manage_po_chks[]" id="notes" value="notes"><?php 
esc_html_e( 'Notes', 'order-and-inventory-manager-for-woocommerce' );
?>
        <br/>
        <input type="checkbox" name="manage_po_chks[]" id="qty" value="qty"><?php 
esc_html_e( 'Qty', 'order-and-inventory-manager-for-woocommerce' );
?>
        <br />
        <input type="checkbox" name="manage_po_chks[]" id="product_price" value="product_price"><?php 
esc_html_e( 'Product Price', 'order-and-inventory-manager-for-woocommerce' );
?>
        <hr/>
      </div>
      <div class="manage_po_lang download_po_field">
        <p><strong><?php 
esc_html_e( 'Choose language in which PO file should be in:', 'order-and-inventory-manager-for-woocommerce' );
?></strong></p>
        <?php 
$available_languages = get_available_languages( OIMWC_PLUGIN_DIR . 'languages/' );
require_once ABSPATH . 'wp-admin/includes/translation-install.php';
$all_languages = wp_get_available_translations();

if ( is_array( $available_languages ) && count( $available_languages ) > 0 ) {
    ?>
        <select name="manage_po_lang_dd" id="manage_po_lang_dd">
          <?php 
    foreach ( $available_languages as $lang_code ) {
        $lang_code = str_replace( 'order-and-inventory-manager-for-woocommerce-', '', $lang_code );
        
        if ( $lang_code == 'en_US' ) {
            $lang_name = 'English';
        } else {
            $lang_name = $all_languages[$lang_code]['english_name'];
        }
        
        ?>
            <option value="<?php 
        echo  $lang_code ;
        ?>"><?php 
        echo  $lang_name ;
        ?></option>
          <?php 
    }
    ?>
        </select>
        <?php 
}

?>
        <hr/>
      </div>
      <div class="manage_po_office_address download_po_field">
        <p><strong><?php 
echo  __( 'Select Office Address', 'order-and-inventory-manager-for-woocommerce' ) ;
?></strong>
        </p>
        <select name="select_office_address" id="select_office_address">
        <?php 
$office_address = get_option( 'oimwc_company_address' );
$count = 1;
foreach ( $office_address as $key => $value ) {
    printf( '<option value="%s">%s</option>', $key, $value['title'] );
}
?>
        </select>
        <hr/>
      </div>
      <?php 
?>
      <div class="manage_po_chks_download download_po_field">
        <p><strong><?php 
esc_html_e( 'File Type', 'order-and-inventory-manager-for-woocommerce' );
?></strong></p>
        <select name="download_po_file" id="download_po_file">
          <option value="xlsx"><?php 
esc_html_e( 'XLS', 'order-and-inventory-manager-for-woocommerce' );
?></option>
          <?php 
?>
        </select>
        <hr/>
      </div>
      <div class="delivery_date download_po_field">
        <p><strong><?php 
esc_html_e( 'Delivery Date', 'order-and-inventory-manager-for-woocommerce' );
?></strong></p>
        <input type="text" class="delivery_date" id="delivery_date" name="delivery_date" value="" />
        <hr/>
      </div>
      <div class="shipping_terms download_po_field">
        <p><strong><?php 
esc_html_e( 'Shipping Terms', 'order-and-inventory-manager-for-woocommerce' );
?></strong></p>
        <input type="text" class="shipping_terms" id="shipping_terms" name="shipping_terms" value="" />
        <hr/>
      </div>
      <div class="shipping_method download_po_field">
        <p><strong><?php 
esc_html_e( 'Shipping Method', 'order-and-inventory-manager-for-woocommerce' );
?></strong></p>
        <input type="text" class="shipping_method" id="shipping_method" name="shipping_method" value="" />
        <hr/>
      </div>
      <div class="po_attn download_po_field">
        <p><strong><?php 
esc_html_e( 'Attn', 'order-and-inventory-manager-for-woocommerce' );
echo  '.' ;
?></strong></p>
        <input type="text" class="po_attn" id="po_attn" name="po_attn" value="" />
        <hr/>
      </div>
      <div class="">
        <p><strong><?php 
esc_html_e( 'Save Default Value', 'order-and-inventory-manager-for-woocommerce' );
?></strong></p>
        <input type="checkbox" name="save_default_sett_chk" id="save_default_sett_chk" value="1">
        <?php 
esc_html_e( 'Save this as default value for this supplier', 'order-and-inventory-manager-for-woocommerce' );
?>
      </div>
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div>

<!-- Send the PO to the supplier's purchase order email address -->
<div id="send-po-order-form" title="<?php 
echo  __( 'Send Purchase Order', 'order-and-inventory-manager-for-woocommerce' ) ;
?>" style="display:none;"> 
  <form>
    <div class="form-body">
      <div class="form-inline">
        <label><strong><?php 
_e( 'From', 'order-and-inventory-manager-for-woocommerce' );
?></strong></label>
        <span><?php 
$user_info = get_userdata( 1 );
echo  $user_info->user_login ;
?></span>
      </div>
      <div class="form-inline">
        <label><strong><?php 
_e( 'Reply to', 'order-and-inventory-manager-for-woocommerce' );
?></strong></label>
        <input type="text" class="reply_to_order_email" name="reply_to_order_email" id="reply_to_order_email" value="<?php 
echo  ( !empty(get_option( 'oimwc_pdf_email' )) ? get_option( 'oimwc_pdf_email' ) : get_option( 'admin_email' ) ) ;
?>" />
      </div>
      <hr color="#ccc" size="1px" style="margin-bottom:10px" />
      <div class="form-field">
        <label><strong><?php 
_e( 'To', 'order-and-inventory-manager-for-woocommerce' );
echo  ':' ;
?></strong></label><br />
        <input type="text" class="send_po_order_email" name="send_po_order_email" id="send_po_order_email" />
      </div>
      <div class="form-field">
        <div style="display: inline-block; width: 100%;">
          <label><strong><?php 
_e( 'Title', 'order-and-inventory-manager-for-woocommerce' );
echo  ':' ;
?></strong></label>
        </div><br />
        <input type="text" class="send_po_email_subject" name="send_po_email_subject" id="send_po_email_subject" /><br/>
        <label><strong><?php 
_e( 'Message', 'order-and-inventory-manager-for-woocommerce' );
echo  ':' ;
?></strong></label>
        <textarea name="send_po_email_message" rows="10" cols="40" class="text ui-widget-content ui-corner-all send_po_order_cls"></textarea>
      </div>
      <div class="form-field attachment_file">
        <label><strong><?php 
_e( 'Attached file', 'order-and-inventory-manager-for-woocommerce' );
echo  ':' ;
?></strong></label>
        <i class="fa fa-file"></i><span class="attach_filename"></span>
      </div>
    </div>
    <div class="send_po lw_spin"><img src="<?php 
echo  OIMWC_PLUGIN_URL . 'images/loader.gif' ;
?>" /></div>
  </form>
</div>