<?php
global $wpdb;

$sql = "UPDATE {$wpdb->postmeta} SET `meta_key` = 'oimwc_physical_units_stock' WHERE `meta_key` = 'oimwc_supplier_total_pieces'";

$wpdb->query( $sql );