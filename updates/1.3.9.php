<?php 
global $wpdb;
$table = $wpdb->prefix . 'oimwc_temp_product';
$charset_collate = $wpdb->get_charset_collate();
$sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            supplier_id mediumint(10) NOT NULL,
            product_name VARCHAR(500) NOT NULL,
            variation_name VARCHAR(500) NOT NULL,
            supplier_product_id VARCHAR(500) NOT NULL,
            product_url VARCHAR(500) NOT NULL,
            supplier_notes VARCHAR(500) NOT NULL,
            product_qty mediumint(5) DEFAULT 0 NOT NULL,
            product_price VARCHAR(500) DEFAULT 0 NOT NULL,
            order_id mediumint(2) DEFAULT 0 NOT NULL,
            supplier_pack_size int(10) DEFAULT 1,
            order_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta($sql);

$table_name = $wpdb->prefix . 'order_inventory';
$qry = $wpdb->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".$wpdb->dbname."' AND TABLE_NAME = '{$table_name}' AND COLUMN_NAME = 'temp_product'");
if(!$qry){
    $wpdb->query("ALTER TABLE `{$table_name}` add temp_product int(2) DEFAULT 0");
}