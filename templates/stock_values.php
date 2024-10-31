<?php
/**
 * Displays currency wise stock values
 *
 * @since 1.0.0
 */
?>
<?php include( OIMWC_TEMPLATE . 'top_area.php' );?>
<div class="wrap">
    <h2></h2>
    <?php $current_year = (isset($_GET['year_stock']) && !empty($_GET['year_stock'])) ? sanitize_text_field($_GET['year_stock']) : date('Y');?>
    <p class="p_font"><?php echo __('Inventory Stock Values','order-and-inventory-manager-for-woocommerce').' - '.$current_year;?></p>
    <div class="inventory_management_panel stock_values blockUI">
        <div class="supplier_filter_panel">
            <?php
                $stock_data = get_option('oimwc_stock_data');
                $stock_years = array();
                if ($stock_data && is_array($stock_data)) {
                    $stock_years = array_keys($stock_data);
                }
                ?>
                <form>
                    <input type="hidden" name="page" value="<?php echo sanitize_text_field($_GET['page']);?>" />
                    <input type="hidden" name="subpage" value="<?php echo sanitize_text_field($_GET['subpage']);?>" /> 
                    <select name="year_stock">
                        <option value=""><?php echo __('Select year','order-and-inventory-manager-for-woocommerce'); ?></option>
                        <?php
                        if (count($stock_years)) {
                            foreach ($stock_years as $stock_year) {
                                $selected = '';
                                if (isset($_GET['year_stock']) && sanitize_text_field($_GET['year_stock']) == $stock_year) {
                                    $selected = 'selected';
                                }
                                echo sprintf('<option %s value="%s">%s</option>', $selected, $stock_year, esc_html($stock_year));
                            }
                        } else {
                            $stock_year = date('Y');
                            echo sprintf('<option selected value="%s">%s</option>', $stock_year, esc_html($stock_year));
                        }
                        ?>
                    </select>
                    <input type="submit" value="<?php _e('Filter','order-and-inventory-manager-for-woocommerce'); ?>" class="button" />
                </form>
                <?php
            ?>
        </div>

        <div class="product_listing_panel oimwc-table-shadow">
            <form>
                <?php
                $data->display();
                wp_nonce_field('oimwc_create_product', 'oimwc_product_nonce');
                ?>
                <input type="hidden" name="action" value="create_product_file" />
            </form>
        </div>
    </div>
</div>