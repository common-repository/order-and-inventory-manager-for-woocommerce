<?php 
$po_email_message = '';
$po_email_message .= sprintf(__("Hi %s", 'order-and-inventory-manager-for-woocommerce'),$supplier_name).', ';
$po_email_message .= '<br /><br />';
$po_email_message .= sprintf(__("Here's purchase order PO-%s for %s.", 'order-and-inventory-manager-for-woocommerce'), $order_id,strip_tags($total_purchase)); 
$po_email_message .= '<br /><br />';
$po_email_message .= __('Delivery due date, address and instructions are included in the purchase order.','order-and-inventory-manager-for-woocommerce');
$po_email_message .= '<br /><br />';
$po_email_message .= __('If you have any questions, please let us know.','order-and-inventory-manager-for-woocommerce');
$po_email_message .= '<br /><br />';
$po_email_message .= __('Thanks','order-and-inventory-manager-for-woocommerce').','; 
$po_email_message .= '<br />';
$po_email_message .= __('Order & Inventory Manager', 'order-and-inventory-manager-for-woocommerce');
