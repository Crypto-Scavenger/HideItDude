<?php
/**
 * Plugin Name: HideItDude
 * Description: Hide WordPress backend elements from subscriber roles with advanced configuration options
 * Version: 1.0.0
 * Text Domain: hideitdude
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'HIDEITDUDE_VERSION', '1.0.0' );
define( 'HIDEITDUDE_DIR', plugin_dir_path( __FILE__ ) );
define( 'HIDEITDUDE_URL', plugin_dir_url( __FILE__ ) );

// Include classes
require_once HIDEITDUDE_DIR . 'includes/class-database.php';
require_once HIDEITDUDE_DIR . 'includes/class-core.php';
require_once HIDEITDUDE_DIR . 'includes/class-admin.php';

/**
 * Initialize plugin
 */
function hideitdude_init() {
	$database = new HideItDude_Database();
	$core     = new HideItDude_Core( $database );
	
	if ( is_admin() ) {
		$admin = new HideItDude_Admin( $database );
	}
}
add_action( 'plugins_loaded', 'hideitdude_init' );

// Activation/Deactivation/Uninstall hooks
register_activation_hook( __FILE__, array( 'HideItDude_Database', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'HideItDude_Database', 'uninstall' ) );
