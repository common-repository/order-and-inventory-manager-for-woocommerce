<?php
/**
 * OIMWC class 
 *
 * Handles dependencies and loads main file of plugin.
 *
 * @since    1.0.0
 */
if (!class_exists('OIMWC')) {
    /**
    * OIMWC class which handles dependencies and loads main file of plugin
    *
    * @since    1.0.0
    */
    class OIMWC {

        protected static $instance;
        /**
    	 * Setup class
    	 *
    	 * @since 1.0.0
    	 */
        function __construct() {
            if ($this->validate_dependencies()) {
                $this->load_files();
                if ( is_admin() ) {
                  add_filter( 'plugin_action_links_' . plugin_basename( OIMWC_PLUGIN_FILE ), array( &$this, 'plugin_manage_link' ), 10, 4 );
                }

            } else {
                /* Display dependency error */
                add_action('admin_notices', function () {
                    $arrActivePlugins = apply_filters('active_plugins', get_option('active_plugins'));

                    include_once( OIMWC_TEMPLATE . 'dependency_error.php' );
                });
            }

            
        }

        public static function init(){
            if(!isset(self::$instance) && !self::$instance instanceof OIMWC){
                self::$instance = new OIMWC();
            }
            return self::$instance;
        }

        /**
        * Validate Dependences
        * checks whether woocommerce is active or not.
        *
        * @since 1.0.0
        * 
        * @see __construct relied on
        * @return booean if true then it loads the file else it throws dependency error.
        */
        function validate_dependencies() {
            $arrActivePlugins = apply_filters('active_plugins', get_option('active_plugins'));

            if (!in_array('woocommerce/woocommerce.php', $arrActivePlugins)) {
                return false;
            }
            return true;
        }
        /**
        * Load files
        * loads plugin file if woocommerce is activated.
        *
        * @since 1.0.0
        */
        function load_files() {
            global $woocommerce;
            require_once( OIMWC_INCLUDES . 'functions.php' );
            require_once( OIMWC_CLASSES . 'class.im_main.php' );
            require_once( OIMWC_CLASSES . 'class.order.php' );
            require_once( OIMWC_CLASSES . 'class.supplier.php' );
            require_once( OIMWC_CLASSES . 'class.product_stock.php' );
            require_once( OIMWC_CLASSES . 'class.stock_status.php' );
			if( is_dir( OIMWC_PLUGIN_DIR .'modules' ) ){
				$module_list = scandir( OIMWC_PLUGIN_DIR .'modules' );
				if( isset( $module_list ) && is_array( $module_list ) && count( $module_list ) ):
					foreach( $module_list as $module ):
						if( is_dir( OIMWC_PLUGIN_DIR ."modules/$module" ) && file_exists( OIMWC_PLUGIN_DIR . "modules/$module/$module.php" ) ):
							require_once( OIMWC_PLUGIN_DIR. "modules/$module/$module.php" );
						endif;
					endforeach;
				endif;
			}
                
        }

        /**
         * Return the plugin action links.  This will only be called if the plugin
         * is active.
         *
         * @param array $actions associative array of action names to anchor tags
         * @param string $plugin_file plugin file name, ie my-plugin/my-plugin.php
         * @param array $plugin_data associative array of plugin data from the plugin file headers
         * @param string $context plugin status context, ie 'all', 'active', 'inactive', 'recently_active'
         *
         * @return array associative array of plugin action links
         */
        function plugin_manage_link( $actions, $plugin_file, $plugin_data, $context ) {

            $action_links = array(
                'settings' => '<a href="'.admin_url('admin.php?page=order-inventory-management&subpage=settings').'">' . __( 'Settings' ) . '</a>',
                'support'  => '<a href="'.admin_url('admin.php?page=order-inventory-management&subpage=help&tab=contact').'">' . __( 'Support' ) . '</a>'
                );
            return array_merge( $action_links, $actions );

        }
    }

    OIMWC::init();
}
?>