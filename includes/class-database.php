<?php
/**
 * Database operations
 *
 * @package HideItDude
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HideItDude_Database {

	/**
	 * Settings cache
	 *
	 * @var array|null
	 */
	private $settings_cache = null;

	/**
	 * Table existence verified
	 *
	 * @var bool|null
	 */
	private $table_verified = null;

	/**
	 * Get table name
	 *
	 * @return string
	 */
	private function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'hideitdude_settings';
	}

	/**
	 * Ensure table exists (CRITICAL: Call before every query)
	 *
	 * @return bool
	 */
	private function ensure_table_exists() {
		if ( null !== $this->table_verified ) {
			return $this->table_verified;
		}

		global $wpdb;
		$table = $this->get_table_name();
		
		$table_exists = $wpdb->get_var( $wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$table
		) );
		
		if ( $table !== $table_exists ) {
			$this->create_tables();
			$table_exists = $wpdb->get_var( $wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table
			) );
		}
		
		$this->table_verified = ( $table === $table_exists );
		return $this->table_verified;
	}

	/**
	 * Activate plugin - create tables
	 */
	public static function activate() {
		$instance = new self();
		$instance->create_tables();
		$instance->initialize_defaults();
	}

	/**
	 * Create database tables
	 */
	private function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table           = $this->get_table_name();
		
		// CRITICAL: Concatenate charset_collate AFTER prepare()
		$sql = $wpdb->prepare(
			'CREATE TABLE IF NOT EXISTS %i (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				setting_key varchar(191) NOT NULL,
				setting_value longtext,
				PRIMARY KEY (id),
				UNIQUE KEY setting_key (setting_key)
			)',
			$table
		) . ' ' . $charset_collate;
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Initialize default settings
	 */
	private function initialize_defaults() {
		$defaults = array(
			'hide_admin_menus'        => serialize( array() ),
			'hide_admin_bar_items'    => serialize( array() ),
			'hide_dashboard_widgets'  => serialize( array() ),
			'hide_notifications'      => '0',
			'hide_screen_options'     => '0',
			'hide_help_tab'           => '0',
			'replace_account_menu'    => '0',
			'hide_profile_menu'       => '0',
			'redirect_subscribers'    => '0',
			'hide_admin_bar'          => '0',
			'cleanup_on_uninstall'    => '1',
		);
		
		foreach ( $defaults as $key => $value ) {
			if ( false === $this->get_setting( $key ) ) {
				$this->save_setting( $key, $value );
			}
		}
	}

	/**
	 * Get single setting
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_setting( $key, $default = false ) {
		if ( ! $this->ensure_table_exists() ) {
			return $default;
		}

		global $wpdb;
		$table = $this->get_table_name();
		
		$value = $wpdb->get_var( $wpdb->prepare(
			'SELECT setting_value FROM %i WHERE setting_key = %s',
			$table,
			$key
		) );
		
		if ( null === $value ) {
			return $default;
		}
		
		return maybe_unserialize( $value );
	}

	/**
	 * Get all settings (with caching)
	 *
	 * @return array
	 */
	public function get_all_settings() {
		if ( null !== $this->settings_cache ) {
			return $this->settings_cache;
		}

		if ( ! $this->ensure_table_exists() ) {
			return array();
		}

		global $wpdb;
		$table = $this->get_table_name();
		
		$results = $wpdb->get_results(
			$wpdb->prepare( 'SELECT setting_key, setting_value FROM %i', $table ),
			ARRAY_A
		);
		
		if ( ! is_array( $results ) ) {
			return array();
		}
		
		$settings = array();
		foreach ( $results as $row ) {
			// CRITICAL: Use null coalescing for array keys
			$key   = $row['setting_key'] ?? '';
			$value = $row['setting_value'] ?? '';
			if ( ! empty( $key ) ) {
				$settings[ $key ] = maybe_unserialize( $value );
			}
		}
		
		$this->settings_cache = $settings;
		return $settings;
	}

	/**
	 * Save setting
	 *
	 * @param string $key Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool
	 */
	public function save_setting( $key, $value ) {
		if ( ! $this->ensure_table_exists() ) {
			return false;
		}

		global $wpdb;
		$table = $this->get_table_name();
		
		$result = $wpdb->replace(
			$table,
			array(
				'setting_key'   => $key,
				'setting_value' => maybe_serialize( $value ),
			),
			array( '%s', '%s' )
		);
		
		if ( false !== $result ) {
			$this->settings_cache = null;
		}
		
		return false !== $result;
	}

	/**
	 * Cleanup on uninstall
	 */
	public static function uninstall() {
		$instance = new self();
		$cleanup  = $instance->get_setting( 'cleanup_on_uninstall', '1' );
		
		if ( '1' !== $cleanup ) {
			return;
		}
		
		global $wpdb;
		$table = $instance->get_table_name();
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );
	}
}
