<?php
/**
 * Inventory management main page
 * 
 * Displays products which are in low stock and products which are in awaiting delivery
 *
 * @since 1.0.0
 */
?>
<?php include( OIMWC_TEMPLATE . 'top_area.php' );
include( OIMWC_TEMPLATE . 'add_temp_product.php'); ?>
<div class="IO_tab_panel">
    <?php include( OIMWC_TEMPLATE . 'navigation.php' ); ?>
</div>
<div class="wrap IO_main_panel">
    <div id="myProgress" style="display: none;">
        <div id="myBar">0%</div>
    </div>
    <?php 
        $im_default_low_stock_data = get_option('oimwc_default_low_stock_data');
        if(!$im_default_low_stock_data){
            return;
        }
    ?>
    <div class="inventory_management_panel blockUI">
        <div class="IO_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
        <div class="footer_panel <?php echo (isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) == 'delivery_table') ? 'remove_space_tab' : ''; ?>">
            <div class="footer_panel_flex">
         <?php
            if (isset($supplier_list) && is_array($supplier_list) && count($supplier_list)) {
                ?>
            <div class="supplier_filter_panel">
                <form>
                    <input type="hidden" name="page" value="order-inventory-management" />
                    <?php
                    if (isset($_GET['subpage'])) {
                        echo '<input type="hidden" name="subpage" value="delivery_table"/>';
                    }
                    ?>
                    <span class="IO_supplier_filter_span">
                    <select name="supplier_id" id="IO_supplier_filter">
                        <?php
                        $option_html = '';
                        $total_products = 0;
                        $hasValue = false;

                        if( isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) == 'delivery_table' ){
                            foreach ($supplier_list as $id => $supplier_name) {
                                $order_count = OIMWC_Order::get_ordered_product($id);
                                $count = count($order_count);

                                printf('<option data-name="%s" data-no="%s" value="%s">%s (%s)</option>',$supplier_name, $count, $id, $supplier_name, $count);
                            }
                        }
                        else
                        {
                            foreach ($supplier_list as $key => $supplier_list_value) {
                                $option_html .= '<optgroup label="'.$key.'">';
                                foreach ($supplier_list_value as $id => $supplier_name) { 
                                                                                                                                     
                                    $supplier_lowstock_products = 0;
                                    if($id != "all"){
                                        $supplier_lowstock_products = get_post_meta($id,'oimwc_supplier_products_lowstock_level',true);
                                        $supplier_currency = get_post_meta($id,'oimwc_supplier_currency',true);
                                    }
                                    $count = 0;
                                    $allcount = 0;   
                                    $all_product_flag = get_option('oimwc_show_all_product');              
                                    if( $id != 'all' || !$id ){
                                        if( $id > 0 ){
                                            $count = get_post_meta( $id, 'oimwc_total_low_stock_products', true );
                                            $allcount = get_post_meta($id, 'oimwc_show_all_products', true);
                                            //$count = get_supplier_count($id);
                                        }if($all_product_flag == 0){
                                            $allcount = get_post_meta($id, 'oimwc_show_all_products', true);
                                        }else{
                                            //$count = get_option('oimwc_total_low_stock_prod_without_supplier');
                                            $allcount = get_post_meta($id, 'oimwc_show_all_products', true);
                                        }
                                        if( $count == '' ){
                                            $count = oimwc_supplier_low_stock_count( 0, $id );
                                        }
                                        if($allcount == ''){
                                            $allcount = oimwc_show_all_product_stock_count( $id, false, '=', true); 
                                        }
                                    }
                                    
                                    if(!$count){
                                        $count = 0;
                                    }
                                    //if( $count ){
                                        
                                        $total_products = $total_products + $count;            
                                        $allcount = (!empty($allcount)) ? $allcount : 0;
                                        $option_html .= sprintf('<option data-name="%s" data-no="%s" value="%s" data-sup_level="%s" data-curr="%s" data-all_pro="%s">%s (%s)</option>',$supplier_name, $count, $id, $supplier_lowstock_products, $supplier_currency, $allcount, $supplier_name, $count);
                                        
                                    //} 
                                }
                                $option_html .= '</optgroup>';                            
                            }
                            $no_supplier_count = get_option('oimwc_total_low_stock_prod_without_supplier', 0);
                            $show_all_product = get_option('oimwc_show_all_product');
                            printf('<option data-name="%1$s" data-no="%3$s" selected value="%2$s" data-all_pro="%4$s">%1$s (%3$s)</option>', __('All Suppliers','order-and-inventory-manager-for-woocommerce'), 'all', (int)$total_products + (int)$no_supplier_count, $show_all_product);
                            
                            echo $option_html;
                            if( $no_supplier_count ){
                                printf('<option data-name="%1$s" data-no="%3$s" value="%2$s">%1$s (%3$s)</option>', __('No Supplier','order-and-inventory-manager-for-woocommerce'),0,$no_supplier_count);
                            }
                        }
                        
                        
                        ?>
                    </select>
                    <div class="lw_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
                    </span>
                    <input type="submit" class="button" value="<?php _e('Filter','order-and-inventory-manager-for-woocommerce'); ?>" />
                </form>
            </div>
            <?php if(!isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) != 'delivery_table'){
                $cls = 'disabled';
            } ?>
            <div class="search_lw_prod_panel">
                <form>
                    <input type="text" name="search_lw_txt" placeholder="<?php _e('Search','order-and-inventory-manager-for-woocommerce');?>" class="<?php echo $cls; ?>" <?php echo $cls; ?> />
                    <div class="lw_search_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
                    <input type="submit" value="<?php _e('Search', 'order-and-inventory-manager-for-woocommerce'); ?>" class="button search_btn_order" />
                </form>
            </div>
        <?php } ?>
        <?php
        if (!isset($_GET['subpage'])) {
            if(!isset($_GET['supplier_id']) || (isset($_GET['supplier_id']) && (sanitize_text_field($_GET['supplier_id']) == "all" || sanitize_text_field($_GET['supplier_id']) == 0)) ){
                $disabled = "disabled";
            }else{
                $disabled = "";
            }

            $cls = ''; $msg = ''; $create_PO_cls = 'create_PO_cls';
            if( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() ){
              $cls = 'disabled_panel tips';
              $msg = OIMWC_SILVER_UPGRDAE_NOTICE;
              $create_PO_cls = '';
            }

            ?> 
                <div class="product_handler">
                    <h3><?php echo __('Add to List','order-and-inventory-manager-for-woocommerce'); ?></h3>                  
                    <form id="frm_product_handler">
                        <input type="hidden" name="action" value="add_product_manually">
                        <input type="hidden" name="supplier_id" value="<?php echo isset($_REQUEST['supplier_id']) ? sanitize_text_field($_REQUEST['supplier_id']) : "all"; ?>">
                        <?php wp_nonce_field('oimwc_add_product', 'oimwc_nonce'); ?>
                        <div class="<?php echo $cls;?>" data-tip="<?php echo $msg;?>">
                            <input type="text" name="product_sku" placeholder="<?php _e('Product SKU','order-and-inventory-manager-for-woocommerce'); ?>" <?php echo $disabled ?>/>
                            <input type="submit" value="<?php _e('Add', 'order-and-inventory-manager-for-woocommerce'); ?>" class="button <?php echo $disabled ?>" />
                        </div>
                    </form>
                </div>
                <?php if(!isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) != 'delivery_table'){ ?>
                        <div class="temp_btn_handler">
                            <input type="button" class="button button-primary disabled" id="btn_create_product" value="<?php echo __('Create Temporary Product','order-and-inventory-manager-for-woocommerce'); ?>" />
                        </div>
                <?php } ?>
                <span class="show_product_info_handler" style="display: none;">
                    <label for="show_all_product"><input type="checkbox" class="show_all_product" name="show_all_product" value="<?php _e('Show all products','order-and-inventory-manager-for-woocommerce'); ?>" /><?php _e('Show all products','order-and-inventory-manager-for-woocommerce'); ?></label>
                </span>
                <span id="refresh_order" style="display: none;">
                    <input type="button" class="button button-primary tips" value="<?php _e('Update PO preview', 'order-and-inventory-manager-for-woocommerce'); ?>" data-tip="<?php _e('Click here to move products to purchase order preview table.'); ?>" />
                </span>
                <span class="<?php echo $cls;?> create_po_span" data-tip="<?php echo $msg;?>">
                    <input type="button" class="button button-primary <?php echo $create_PO_cls;?> <?php echo $disabled ?>" id="btnCreateProductFile" value="<?php _e('Create Purchase Order','order-and-inventory-manager-for-woocommerce'); ?>"  />
                </span>
                </div>
            <?php
        }else{
            echo '</div>';
        }
       ?>
        </div>
        <h2></h2>
            <div id="supplier_order_list_panel" title="<?php _e( 'Select Order', 'order-and-inventory-manager-for-woocommerce' ); ?>" style="display: none;">
                <form id="frm_supplier_order" action="" method="post">
                    <p>
                        <select id="request_date" name="request_date">
                            <option value=""><?php _e( 'Select order to add the product', 'order-and-inventory-manager-for-woocommerce' ); ?></option>
                            <?php 
                            foreach( $supplier_order_list as $key => $order_date ): ?>
                                <?php
                                $disabled = ($lock_supplier_list[$key]) ? 'disabled' : '';
                                ?>
                                <option value="<?php echo $key; ?>" <?php echo $disabled;?>><?php echo $order_date; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <input type="hidden" name="action" value="update_product_file" />
                    <input type="hidden" name="product_id" value="" />
                    <input type="hidden" name="qty" value="" />
                    <input type="hidden" name="stock" value="" />
                    <input type="hidden" name="oimwc_product_nonce" value="" />
                    <input class="button button-primary" type="submit" value="<?php _e('Add to order','order-and-inventory-manager-for-woocommerce');?>" />
                    <input class="cancel_order button" type="button" value="<?php _e('Cancel','order-and-inventory-manager-for-woocommerce');?>" />
                </form>
                <div id="loader"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/bx_loader.gif'; ?>" /></div>
            </div>
        
        <?php 
        $cls = '';
        if((isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) == 'delivery_table') || (isset($_GET['view_order']) && sanitize_text_field($_GET['view_order']) == 1 && isset($_GET['supplier']) && isset($_GET['date'])))
        {
            $cls = 'subpage_cls';
        }
        ?>
        <div data-pagination="" class="product_listing_panel <?php echo $cls;?>">
            <form>
                <?php
                if($_GET['subpage'] != 'delivery_table'){
                    if(!empty($data_order) && is_object($data_order)){
                        $class = 'open'; ?>
                        <div class="purchase_order_table" style="display: none;">
                            <h3><?php _e('Purchase Order Preview', 'order-and-inventory-manager-for-woocommerce'); ?></h3>
                            <?php $data_order->display(); ?>
                        </div>
                    <?php }else{ ?>
                    <div class="purchase_order_table" style="display: none;">
                        <h3><?php _e('Purchase Order Preview', 'order-and-inventory-manager-for-woocommerce'); ?></h3>
                        <?php _e('Enter the amount you wish to order and press the "Update PO preview" button to move the product to this section.','order-and-inventory-manager-for-woocommerce'); ?>
                    </div>
                    <?php }
                    if(is_object($data)){
                        if(count($data) > 1){ ?>
                            <h3><?php _e('Products', 'order-and-inventory-manager-for-woocommerce'); ?></h3>
                            <?php $data->display();
                    }}else{ ?>
                        <h3 class="load_lowstock_msg"><i class="fas fa-arrow-up"></i><?php _e('Select a supplier to show products that have a low stock quantity.','order-and-inventory-manager-for-woocommerce'); ?></h3>
                    <?php }
                }else{ ?>
                    <h3><?php _e('Products', 'order-and-inventory-manager-for-woocommerce'); ?></h3>
                    <?php $data->display();
                }
                ?>
                <?php wp_nonce_field('oimwc_create_product', 'oimwc_product_nonce'); ?>
                <input type="hidden" name="lw_total_pages" id="lw_total_pages" value="<?php echo $data->total_pages;?>">
                <input type="hidden" name="page" class="low_stock_page" value="1">
                <div class="lw_spin"><img src="<?php echo OIMWC_PLUGIN_URL. 'images/loader.gif'; ?>" /></div>
                <input type="hidden" name="action" value="create_product_file" />
                <input type="hidden" name="additional_info" value="" />
            </form>
        </div>
    </div>
</div>
