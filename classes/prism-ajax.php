<?php
define('DOING_AJAX', true);

if (!isset( $_REQUEST['action']))
    die('-1');

include_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/wp-load.php'); 

$action = $_REQUEST['action'];
$allowed_actions = array(
    'load_low_stock_products'
);

if(in_array($action, $allowed_actions)){
    do_action('prism_ajax_'.$action);
}
else{
    die('-1');
} 