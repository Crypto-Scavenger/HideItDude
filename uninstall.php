<?php
/**
 * Uninstall HideItDude
 *
 * @package HideItDude
 * @since 1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Include database class
require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';

// Run cleanup
HideItDude_Database::uninstall();
