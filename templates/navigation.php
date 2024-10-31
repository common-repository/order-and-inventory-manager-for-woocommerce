<?php 

global $wpdb;

$cls = '';
$link = 'admin.php?page=order-inventory-management&subpage=delivery_table';
if ( oimwc_fs()->is_free_plan() && !oimwc_fs()->is_trial() )
{
	$cls = 'upgrade_notice_cls silver_version';
	$link = '#';
}	
$supplier_id = isset($_GET['supplier']) ? sanitize_text_field($_GET['supplier']) : '';
$po_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
?>
<?php 
	if( isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) == 'purchase-orders' ){
		
		$active_order_link = 'admin.php?page=order-inventory-management&subpage=purchase-orders&tab=active_orders';
		$purchase_order_link = 'admin.php?page=order-inventory-management&subpage=purchase-orders&tab=finalize_orders';
		$view_order_link = 'admin.php?page=order-inventory-management&subpage=purchase-orders&view_order=1&supplier='.$supplier_id.'&date='.$po_date;
		
		$flag = false;			
		if( isset($_GET['view_order']) && sanitize_text_field($_GET['view_order']) == 1 && isset($_GET['supplier']) && isset($_GET['date']) ){
			$flag = true;
		}
		?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		    <a class="nav-tab <?php echo (isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) == 'purchase-orders' && !isset($_GET['tab']) && !isset($_GET['view_order']) ) || (isset($_GET['tab']) && sanitize_text_field($_GET['tab']) == 'active_orders' ) ? 'nav-tab-active' : ''; ?>" href="<?php echo $active_order_link; ?>"><?php _e('Active Purchase Orders', 'order-and-inventory-manager-for-woocommerce') ?></a>
		    <a class="nav-tab <?php echo ( isset($_GET['tab']) && sanitize_text_field($_GET['tab']) == 'finalize_orders' ) ? 'nav-tab-active' : ''; ?>" href="<?php echo $purchase_order_link;?>"><?php _e('Completed Purchase Orders', 'order-and-inventory-manager-for-woocommerce') ?></a>
		    <?php if( $flag ){ ?>
		    <a class="nav-tab nav-tab-active" href="<?php echo $view_order_link;?>"><?php _e('Order #'.$order_number, 'order-and-inventory-manager-for-woocommerce') ?></a>
		    <?php } ?>
		</nav>
		<?php
	}
	else
	{
		?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		    <a class="nav-tab  <?php echo!isset($_GET['subpage']) && !isset($_GET['view_order']) ? 'nav-tab-active' : ''; ?>" href="admin.php?page=order-inventory-management"><?php _e('Products with low stock', 'order-and-inventory-manager-for-woocommerce') ?></a>
		    <a class="nav-tab <?php echo isset($_GET['subpage']) && sanitize_text_field($_GET['subpage']) == 'delivery_table' ? 'nav-tab-active' : ''; echo $cls; ?>" href="<?php echo $link;?>"><?php _e('Products awaiting delivery', 'order-and-inventory-manager-for-woocommerce') ?></a>
		</nav>
		<?php
	}
?>