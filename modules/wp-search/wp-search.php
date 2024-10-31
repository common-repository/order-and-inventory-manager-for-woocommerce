<?php

if (!class_exists("OIMWC_Search")) {

    class OIMWC_Search {

        public function __construct() {
			define( 'WS_OIMWC_PLUGIN_DIR', OIMWC_PLUGIN_DIR.'modules/wp-search/' );
			define( 'WS_OIMWC_PLUGIN_URL', OIMWC_PLUGIN_URL.'modules/wp-search/' );
			
            add_action('admin_bar_menu', array($this, 'oimwc_search_field'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_script'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_script'));
            add_action('init', array($this, 'save_post_types'));
            add_action('wp_ajax_get_searched_posts', array($this,'get_searched_posts'));
        }

        function oimwc_search_field() {
            global $wp_admin_bar;
            $selected_posttypes = get_option('oimwc_wpsearch_posttypes');
            $enable_wpsearch = get_option('oimwc_enable_wpsearch');
			
            $last_searched_posttype = get_option('oimwc_last_searched_posttype');
            $default_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : ($last_searched_posttype ? $last_searched_posttype : 'post');
			if( !count( $selected_posttypes ) || !$enable_wpsearch ){
				return;
			}
            $search_query = '';
            $get_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
            if (in_array($get_post_type, $selected_posttypes)) {
                $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
            }
            $posttyes = '<select id="wpsearchtype">';
            foreach ($selected_posttypes as $key => $value) {
                $label = get_post_type_object($value)->labels->singular_name;
                $selected = $last_searched_posttype == $value ? "selected" : "";
                $posttyes .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
            }
            $posttyes .= '</select>';
            
            $wp_admin_bar->add_menu(array(
                'id' => 'wp_search_field',
                'parent' => 'top-secondary',
                'title' => '<form method="get" id="wpsearchfrm" action="' . admin_url('edit.php?post_type='.$last_searched_posttype) . '">
                        ' . $posttyes . '
                        <input name="s" id="wpsearchtext" type="text" value="' . $search_query . '" placeholder="'.__('Search...','order-and-inventory-manager-for-woocommerce').'"/>
                        <input id="wpsearchtype_hidden" name="post_type" value="'.$last_searched_posttype.'" type="hidden">
                    </form>'
            ));
        }

        function enqueue_script() {
            wp_enqueue_style('wp-search-css', WS_OIMWC_PLUGIN_URL .'css/wp-search.css');
            
            wp_enqueue_style('jquery-ui-css',OIMWC_PLUGIN_URL.'css/jquery-ui.min.css');
            wp_enqueue_style('jquery-theme', OIMWC_PLUGIN_URL.'css/theme.min.css');

            wp_enqueue_script('wp-search-js', WS_OIMWC_PLUGIN_URL . 'js/wp-search.js', array('jquery-ui-autocomplete', 'jquery'));
            wp_localize_script('wp-search-js', 'search', array(
                'frm_action' => admin_url('edit.php?post_type='),
                'ajaxurl' => admin_url('admin-ajax.php'),
            ));
        }

        function save_post_types() {
            $types = get_option('oimwc_wpsearch_posttypes');
            if (!is_array($types)) {
                $types = array();
            }
            if (!in_array('post', $types) && !in_array('page', $types)) {
                //$types[] = 'post';
                //$types[] = 'page';
                update_option('oimwc_wpsearch_posttypes', $types);
            }

            if (isset($_GET['page']) && sanitize_text_field($_GET['page']) == "wp_search" && isset($_POST['save_wp_search_types'])) {
                $types = array();
                //$types[] = 'post';
                //$types[] = 'page';
				if( isset( $_POST['search_types'] ) ){
	                foreach ($_POST['search_types'] as $key => $value) {
	                    $types[] = $value;
	                }
				}
                $types = array_unique($types);
                $types = array_values($types);
                update_option('oimwc_wpsearch_posttypes', $types);
				update_option('oimwc_enable_wpsearch', isset( $_POST['enable_admin_seachbar'] ) ? 1: 0 );
            }
        }

        function get_searched_posts() {
            global $wpdb;
            $titles = array();
            $data = array();
            $post_type = sanitize_text_field($_POST['post_type']);
            $keyword = '%' . $wpdb->esc_like(stripslashes($_POST['keyword'])) . '%';
            if ($post_type == "shop_order") {
                $order_table = $wpdb->prefix . "woocommerce_order_items";
                //$orderids = WC_Order_Data_Store_CPT::search_orders($_POST['keyword']);
                $sql = "select ID from $wpdb->posts where ID like %s AND post_type = '$post_type' ORDER BY ID DESC LIMIT 0, 20";
                $sql = $wpdb->prepare($sql, $keyword);
                $orderids = $wpdb->get_results($sql);
                if(!empty( $orderids )){
                    foreach ($orderids as $row) {
						$id = $row->ID;
                        $email = get_post_meta($id,'_billing_email',true);
                        $fname = get_post_meta($id,'_billing_first_name',true);
                        $lname = get_post_meta($id,'_billing_last_name',true);
                        $name = $fname." ".$lname;
                        if(empty(trim($name))){
                            $name = $email;
                        }
                        $titles['label'] = "#$id - ".$name;
                        $titles['value'] = admin_url("post.php?post=$id&action=edit");
                        $data[] = $titles;
                    }
                }
            } else {
                $sql = "select ID,post_title from $wpdb->posts where post_title like %s AND post_type = '$post_type' AND post_status='publish'";
                $sql = $wpdb->prepare($sql, $keyword);
                $results = $wpdb->get_results($sql);
                if ($results) {
                    foreach ($results as $r) {
                        $titles['label'] = $r->post_title;
                        $titles['value'] = admin_url("post.php?post=$r->ID&action=edit");
                        $data[] = $titles;
                    }
                }
            }
            update_option('oimwc_last_searched_posttype', $post_type);
            echo json_encode($data);
            die();
        }

    }

    new OIMWC_Search();
}
