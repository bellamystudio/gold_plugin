<?php
/*
  Plugin Name: GOLD Feed Prices
  Description: Gold Price alterations
  Author: Ian Bryce @ Bellamy Studio
  Version: 0.1
  Author URI: http://bellamystudio.com
  Plugin URI: http://bellamystudio.com
  Requires at least: 3.0.0
  Tested up to: 4.8
*/

/***
** Before we get excited let's check that some basic first
** Check that someone isn't targeting the plugin
** Check if another version of the plugin is installed
** Check if WooCommerce is ready to go 
***/


if (!function_exists('get_option')) {
  header('HTTP/1.0 403 Forbidden');
  die;  // Silence is golden, direct call is prohibited
}

if (defined('GOLD_PLUGIN_URL')) {
   wp_die('It seems that other version of GOLD is active. Please deactivate it before you use this version');
}


/**
 *
 *  Activation Checking  
 *
 **/

if ( ! class_exists( 'WC_InstallCheck' ) ) {
  class WC_InstallCheck {
		static function install() {
			/**
			* Check if WooCommerce is acitve
			**/
			write_log('Checking WC Plugin Status');

			if ( !is_plugin_active('woocommerce/woocommerce.php'))
			{
				write_log('WC Not Active - Disabling Plugin');
				// Deactivate the plugin
				deactivate_plugins(__FILE__);
				
				// Throw an error in the wordpress admin console
				$error_message = __('This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugins to be active!', 'gold_plugin');
				die($error_message);				
			}
		}
	}
}

register_activation_hook( __FILE__, array('WC_InstallCheck', 'install') );


/***
**** Lets define some constants to sav time in the future
***/


define('GOLD_VERSION', '0.1');
define('GOLD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GOLD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GOLD_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('GOLD_PLUGIN_FILE', basename(__FILE__));
define('GOLD_PLUGIN_FULL_PATH', __FILE__);
define('GOLD_DEBUG', true);
define('GOLD_WP_ADMIN_URL', admin_url());
define('GOLD_ERROR', 'Error is encountered');
define('GOLD_PARENT', is_network_admin() ? 'network/users.php':'users.php');
define('GOLD_KEY_CAPABILITY', 'gold_manage_options');


/***
**** Start the logging function to help with debugs
**** The log will be stored in /wp-content/debug.log
**** Set WP_DEBUG to True in wp-config.php
***/

if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
write_log('START: GOLD Plugin');


/**
 *
 * Load the required includes
 *
 **/

require_once (GOLD_PLUGIN_DIR .'/includes/codestar/cs-framework.php');
require_once (GOLD_PLUGIN_DIR .'includes/gold-feed.php');
require_once (GOLD_PLUGIN_DIR .'includes/pruning.php');
require_once (GOLD_PLUGIN_DIR .'includes/calculate-woo-prices.php');
require_once (GOLD_PLUGIN_DIR .'includes/cron-jobs.php');
require_once (GOLD_PLUGIN_DIR .'includes/custom-woo-fields.php');
require_once (GOLD_PLUGIN_DIR .'includes/markup.php');
require_once (GOLD_PLUGIN_DIR .'includes/custom-post-types.php');
require_once (GOLD_PLUGIN_DIR .'includes/enqueue.php');

/**
 *
 * Initialisation function for WP - Executed each time WP is called
 *
 **/
 
function init_func() {
	// Setup the feed system every time wordpress is called
	update_prices_based_on_feed();	 
}
add_action('init', 'init_func');
 

/**
 *
 * Executed when plugin activated
 *
 **/
  
function gold_activate() {
    // Activation code below
	register_my_taxes_metal_type();
	register_my_cpts_metal_prices();
	check_if_metal_types_exist();
}
register_activation_hook( __FILE__, 'gold_activate' );

/**
 *
 * Clean up scripts when plugin deactivated
 *
 **/
 
function gold_deactivate()
{
	// Deactivation code below...
	
	// clear the permalinks to remove our post type's rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'gold_deactivate');


function add_setup_gold_admin()
{
	$style_path = GOLD_PLUGIN_DIR . "/css/admin.css";
	$style_ver  = filemtime($style_path);

	wp_register_style('gold_admin_stylesheet', GOLD_PLUGIN_URL . 'css/admin.css', array(),'1.1.'.$style_ver, true);
	wp_enqueue_style('gold__admin_stylesheet');
		
}
add_action( 'admin_init', 'add_setup_gold_admin');



/**********************************************************************************************
Functions below help set up WordPress 
***********************************************************************************************/


/*
* Please add the line below to yout wp-config MR Bellamy - Help with Debugging
*
define('WP_DEBUG', true);
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );
define( 'SCRIPT_DEBUG', true );
* Stop HERE
*
*/

