<?php
/**
 * Core functionality
 *
 * @package HideItDude
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HideItDude_Core {

	/**
	 * Database instance
	 *
	 * @var HideItDude_Database
	 */
	private $database;

	/**
	 * Settings cache (lazy loaded)
	 *
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * Constructor
	 *
	 * @param HideItDude_Database $database Database instance.
	 */
	public function __construct( $database ) {
		$this->database = $database;
		
		// Apply hide functionality only for subscribers
		add_action( 'wp_loaded', array( $this, 'apply_hide_functionality' ) );
	}

	/**
	 * Get settings (lazy loading)
	 *
	 * @return array
	 */
	private function get_settings() {
		if ( null === $this->settings ) {
			$this->settings = $this->database->get_all_settings();
		}
		return $this->settings;
	}

	/**
	 * Check if user has only subscriber role
	 *
	 * @return bool
	 */
	private function user_has_only_subscriber_role() {
		$user = wp_get_current_user();
		return ! empty( $user->roles ) && 1 === count( $user->roles ) && in_array( 'subscriber', $user->roles, true );
	}

	/**
	 * Apply hide functionality
	 */
	public function apply_hide_functionality() {
		if ( ! $this->user_has_only_subscriber_role() ) {
			return;
		}
		
		$settings = $this->get_settings();
		
		// Hide admin menus
		add_action( 'admin_menu', array( $this, 'hide_admin_menus' ), 999 );
		
		// Hide admin bar items
		add_action( 'wp_before_admin_bar_render', array( $this, 'hide_admin_bar_items' ) );
		
		// Hide dashboard widgets
		add_action( 'wp_dashboard_setup', array( $this, 'hide_dashboard_widgets' ) );
		
		// Hide notifications
		if ( '1' === ( $settings['hide_notifications'] ?? '0' ) ) {
			add_action( 'admin_head', array( $this, 'hide_admin_notices' ) );
		}
		
		// Hide screen options
		if ( '1' === ( $settings['hide_screen_options'] ?? '0' ) ) {
			add_filter( 'screen_options_show_screen', '__return_false' );
		}
		
		// Hide help tab
		if ( '1' === ( $settings['hide_help_tab'] ?? '0' ) ) {
			add_action( 'admin_head', array( $this, 'hide_help_tab' ) );
		}
		
		// Replace account menu
		if ( '1' === ( $settings['replace_account_menu'] ?? '0' ) ) {
			add_action( 'wp_before_admin_bar_render', array( $this, 'replace_account_menu' ) );
		}
		
		// Hide profile menu
		if ( '1' === ( $settings['hide_profile_menu'] ?? '0' ) ) {
			add_action( 'admin_menu', array( $this, 'hide_profile_menu' ), 999 );
		}
		
		// Redirect subscribers
		if ( '1' === ( $settings['redirect_subscribers'] ?? '0' ) ) {
			add_action( 'admin_init', array( $this, 'redirect_subscribers' ) );
		}
		
		// Hide admin bar completely
		if ( '1' === ( $settings['hide_admin_bar'] ?? '0' ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	/**
	 * Hide admin menus
	 */
	public function hide_admin_menus() {
		$settings     = $this->get_settings();
		$hidden_menus = isset( $settings['hide_admin_menus'] ) ? $settings['hide_admin_menus'] : array();
		
		if ( is_array( $hidden_menus ) ) {
			foreach ( $hidden_menus as $menu_slug ) {
				remove_menu_page( $menu_slug );
			}
		}
	}

	/**
	 * Hide admin bar items
	 */
	public function hide_admin_bar_items() {
		global $wp_admin_bar;
		
		$settings     = $this->get_settings();
		$hidden_items = isset( $settings['hide_admin_bar_items'] ) ? $settings['hide_admin_bar_items'] : array();
		
		if ( is_array( $hidden_items ) ) {
			foreach ( $hidden_items as $item_id ) {
				$wp_admin_bar->remove_node( $item_id );
			}
		}
	}

	/**
	 * Hide dashboard widgets
	 */
	public function hide_dashboard_widgets() {
		$settings       = $this->get_settings();
		$hidden_widgets = isset( $settings['hide_dashboard_widgets'] ) ? $settings['hide_dashboard_widgets'] : array();
		
		if ( is_array( $hidden_widgets ) ) {
			foreach ( $hidden_widgets as $widget_id ) {
				remove_meta_box( $widget_id, 'dashboard', 'normal' );
				remove_meta_box( $widget_id, 'dashboard', 'side' );
			}
		}
	}

	/**
	 * Hide admin notices
	 */
	public function hide_admin_notices() {
		echo '<style>.notice, .error, .updated { display: none !important; }</style>';
	}

	/**
	 * Hide help tab
	 */
	public function hide_help_tab() {
		echo '<style>#contextual-help-link-wrap { display: none !important; }</style>';
	}

	/**
	 * Replace account menu with logout button
	 */
	public function replace_account_menu() {
		global $wp_admin_bar;
		
		$wp_admin_bar->remove_node( 'my-account' );
		$wp_admin_bar->add_node(
			array(
				'id'    => 'logout-button',
				'title' => __( 'Logout', 'hideitdude' ),
				'href'  => wp_logout_url(),
				'meta'  => array( 'class' => 'hideitdude-logout-btn' ),
			)
		);
	}

	/**
	 * Hide profile menu
	 */
	public function hide_profile_menu() {
		remove_menu_page( 'profile.php' );
		remove_submenu_page( 'users.php', 'profile.php' );
	}

	/**
	 * Redirect subscribers to homepage
	 */
	public function redirect_subscribers() {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}
}
