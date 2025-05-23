<?php

/*
Plugin Name: WP Speed Optimization By Add Expires Headers & Optimized Minify Plugin
Plugin URI: http://www.addexpiresheaders.com/
Description: This plugin will add expires headers for various types of resources of website and also help to add optimized minification and merging of resources to have better performance and speed optimization.
Author: Passionate Brains
Version: 2.10.0
Author URI: http://www.addexpiresheaders.com/
License: GPLv2 or later
*/
/* initiating freemius */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'dd_aeh' ) ) {
    dd_aeh()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'dd_aeh' ) ) {
        // Create a helper function for easy SDK access.
        function dd_aeh()
        {
            global  $dd_aeh ;
            
            if ( !isset( $dd_aeh ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $dd_aeh = fs_dynamic_init( array(
                    'id'              => '5598',
                    'slug'            => 'add-expires-headers',
                    'premium_slug'    => 'AddExpiresHeaders-premium',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_ba20d9daf118a0e03f28dbbc805e3',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Pro',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 3,
                    'is_require_payment' => false,
                ),
                    'has_affiliation' => 'all',
                    'menu'            => array(
                    'slug'       => 'aeh_pro_plugin_options',
                    'first-path' => 'admin.php?page=aeh_pro_plugin_options',
                    'support'    => false,
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $dd_aeh;
        }
        
        // Init Freemius.
        dd_aeh();
        // Signal that SDK was initiated.
        do_action( 'dd_aeh_loaded' );
    }
    
    /* Defining some of constant which will be helpful throughout */
    if ( !defined( 'AEH_BASENAME' ) ) {
        define( 'AEH_BASENAME', plugin_basename( __FILE__ ) );
    }
    if ( !defined( 'AEH_DIR' ) ) {
        define( 'AEH_DIR', plugin_dir_path( __FILE__ ) );
    }
    if ( !defined( 'AEH_URL' ) ) {
        define( 'AEH_URL', plugin_dir_url( __FILE__ ) );
    }
    if ( !defined( 'AEH_SITE_URL' ) ) {
        define( 'AEH_SITE_URL', site_url() );
    }
    if ( !defined( 'AEH_SITE_DOMAIN' ) ) {
        define( 'AEH_SITE_DOMAIN', trim( str_ireplace( array( 'http://', 'https://' ), '', trim( AEH_SITE_URL, '/' ) ) ) );
    }
    if ( !defined( 'AEH_PREFIX' ) ) {
        define( 'AEH_PREFIX', 'AEH_' );
    }
    if ( !defined( 'AEH_VERSION' ) ) {
        define( 'AEH_VERSION', '2.10.0' );
    }
    /* Definining main class */
    if ( !class_exists( 'AEH_Pro' ) ) {
        class AEH_Pro
        {
            private static  $instance = null ;
            private  $main ;
            private  $admin ;
			private  $minify;
            public static function get_instance()
            {
                if ( !self::$instance ) {
                    self::$instance = new self();
                }
                return self::$instance;
            }
            
            private function __construct()
            {
                
                if ( $this->aeh_compat_checker() ) {
                    $this->includes();
                    $this->init();
                }
            
            }
            
            /*loads other support classes*/
            private function includes()
            {
                require_once AEH_DIR . 'main/class-aeh-minify.php';
                require_once AEH_DIR . 'main/class-aeh-settings.php';
                require_once AEH_DIR . 'main/class-aeh-main.php';
                require_once AEH_DIR . 'main/class-aeh-admin.php';
            }
            
            /* init support classes*/
            private function init()
            {
                $this->main = new AEH_Main();
                $this->admin = new AEH_Admin();
                $this->minify = new AEH_Minify();
            }
            
            /* returning main class object */
            public function main()
            {
                return $this->main;
            }
            
            /* returning admin class object */
            public function admin()
            {
                return $this->admin;
            }
            
            public static function dd_aeh_uninstall_cleanup()
            {
                if ( class_exists( 'AEH_Main' ) ) {
                    AEH_Pro::get_instance()->main()->remove_settings();
                }
            }
            
            /* checking compatibility for plugin to get activated and working */
            public function aeh_compat_checker()
            {
                global  $wp_version ;
                $error = '';
                if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
                    $error = 'Add Expires Headers requires PHP 5.4 or higher. You’re still on ' . PHP_VERSION;
                }
                if ( version_compare( $GLOBALS['wp_version'], '4.5', '<' ) ) {
                    $error = 'Add Expires Headers requires WP 4.5 or higher. You’re still on ' . $GLOBALS['wp_version'];
                }
                
                if ( is_plugin_active( plugin_basename( __FILE__ ) ) && !empty($error) || !empty($error) ) {
                    if ( isset( $_GET['activate'] ) ) {
                        unset( $_GET['activate'] );
                    }
                    add_action( 'admin_notices', function () use( $error ) {
                        echo  '<div class="notice notice-error is-dismissible"><p><b>' . $error . '</b></p></div>' ;
                    } );
                    return false;
                } else {
                    return true;
                }
            
            }
        
        }
    }
    add_action( 'plugins_loaded', array( 'AEH_Pro', 'get_instance' ) );
    dd_aeh()->add_action( 'after_uninstall', array( 'AEH_Pro', 'dd_aeh_uninstall_cleanup' ) );
    register_uninstall_hook( __FILE__, array( 'AEH_Pro', 'dd_aeh_uninstall_cleanup' ) );
}
