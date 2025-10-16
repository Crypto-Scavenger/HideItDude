<?php
/**
 * Admin functionality
 *
 * @package HideItDude
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HideItDude_Admin {

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
		
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_post_hideitdude_save_settings', array( $this, 'save_settings' ) );
	}

	/**
	 * Get settings (lazy loading - CRITICAL pattern)
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
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_management_page(
			__( 'HideItDude Settings', 'hideitdude' ),
			__( 'HideItDude', 'hideitdude' ),
			'manage_options',
			'hideitdude-settings',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'tools_page_hideitdude-settings' !== $hook ) {
			return;
		}
		
		wp_enqueue_style(
			'hideitdude-admin',
			HIDEITDUDE_URL . 'assets/admin.css',
			array(),
			HIDEITDUDE_VERSION
		);
		
		wp_enqueue_script(
			'hideitdude-admin',
			HIDEITDUDE_URL . 'assets/admin.js',
			array( 'jquery' ),
			HIDEITDUDE_VERSION,
			true
		);
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		// CRITICAL: Check capability in render
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access', 'hideitdude' ) );
		}
		
		$settings = $this->get_settings();
		?>
		<div class="wrap hideitdude-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully.', 'hideitdude' ); ?></p>
				</div>
			<?php endif; ?>
			
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'hideitdude_save_settings', 'hideitdude_nonce' ); ?>
				<input type="hidden" name="action" value="hideitdude_save_settings">
				
				<div class="hideitdude-tabs">
					<nav class="nav-tab-wrapper">
						<a href="#tab-menus" class="nav-tab nav-tab-active"><?php esc_html_e( 'Admin Menus', 'hideitdude' ); ?></a>
						<a href="#tab-adminbar" class="nav-tab"><?php esc_html_e( 'Admin Bar', 'hideitdude' ); ?></a>
						<a href="#tab-dashboard" class="nav-tab"><?php esc_html_e( 'Dashboard', 'hideitdude' ); ?></a>
						<a href="#tab-general" class="nav-tab"><?php esc_html_e( 'General', 'hideitdude' ); ?></a>
						<a href="#tab-redirect" class="nav-tab"><?php esc_html_e( 'Redirect & Bar', 'hideitdude' ); ?></a>
						<a href="#tab-cleanup" class="nav-tab"><?php esc_html_e( 'Cleanup', 'hideitdude' ); ?></a>
					</nav>
					
					<div class="tab-content">
						<!-- Admin Menus Tab -->
						<div id="tab-menus" class="tab-pane active">
							<h3><?php esc_html_e( 'Hide Admin Menus', 'hideitdude' ); ?></h3>
							<p><?php esc_html_e( 'Select which admin menu items to hide from subscribers:', 'hideitdude' ); ?></p>
							<?php $this->render_admin_menus_checkboxes( $settings ); ?>
						</div>
						
						<!-- Admin Bar Tab -->
						<div id="tab-adminbar" class="tab-pane">
							<h3><?php esc_html_e( 'Hide Admin Bar Items', 'hideitdude' ); ?></h3>
							<p><?php esc_html_e( 'Select which admin bar items to hide from subscribers:', 'hideitdude' ); ?></p>
							<?php $this->render_admin_bar_checkboxes( $settings ); ?>
						</div>
						
						<!-- Dashboard Tab -->
						<div id="tab-dashboard" class="tab-pane">
							<h3><?php esc_html_e( 'Hide Dashboard Widgets', 'hideitdude' ); ?></h3>
							<p><?php esc_html_e( 'Select which dashboard widgets to hide from subscribers:', 'hideitdude' ); ?></p>
							<?php $this->render_dashboard_widgets_checkboxes( $settings ); ?>
						</div>
						
						<!-- General Tab -->
						<div id="tab-general" class="tab-pane">
							<h3><?php esc_html_e( 'General Hide Options', 'hideitdude' ); ?></h3>
							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Hide Notifications', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="hide_notifications" value="1" 
												<?php checked( $settings['hide_notifications'] ?? '0', '1' ); ?>>
											<?php esc_html_e( 'Hide WordPress and plugin notifications/notices', 'hideitdude' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Hide Screen Options', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="hide_screen_options" value="1" 
												<?php checked( $settings['hide_screen_options'] ?? '0', '1' ); ?>>
											<?php esc_html_e( 'Hide Screen Options tab', 'hideitdude' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Hide Help Tab', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="hide_help_tab" value="1" 
												<?php checked( $settings['hide_help_tab'] ?? '0', '1' ); ?>>
											<?php esc_html_e( 'Hide Help tab', 'hideitdude' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Replace Account Menu', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="replace_account_menu" value="1" 
												<?php checked( $settings['replace_account_menu'] ?? '0', '1' ); ?>>
											<?php esc_html_e( 'Replace admin bar account menu with logout button', 'hideitdude' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Hide Profile Menu', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="hide_profile_menu" value="1" 
												<?php checked( $settings['hide_profile_menu'] ?? '0', '1' ); ?>>
											<?php esc_html_e( 'Hide Profile menu from backend for subscribers', 'hideitdude' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>
						
						<!-- Redirect & Bar Tab -->
						<div id="tab-redirect" class="tab-pane">
							<h3><?php esc_html_e( 'Redirect and Admin Bar Options', 'hideitdude' ); ?></h3>
							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Redirect Subscribers', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="redirect_subscribers" value="1" 
												<?php checked( $settings['redirect_subscribers'] ?? '0', '1' ); ?>>
											<?php esc_html_e( 'Redirect subscribers to homepage after login instead of admin', 'hideitdude' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Hide Admin Bar', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="hide_admin_bar" value="1" 
												<?php checked( $settings['hide_admin_bar'] ?? '0', '1' ); ?>>
											<?php esc_html_e( 'Completely remove/disable admin bar for subscribers', 'hideitdude' ); ?>
										</label>
									</td>
								</tr>
							</table>
						</div>

						<!-- Cleanup Tab -->
						<div id="tab-cleanup" class="tab-pane">
							<h3><?php esc_html_e( 'Cleanup Options', 'hideitdude' ); ?></h3>
							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Delete Data on Uninstall', 'hideitdude' ); ?></th>
									<td>
										<label>
											<input type="checkbox" name="cleanup_on_uninstall" value="1" 
												<?php checked( $settings['cleanup_on_uninstall'] ?? '1', '1' ); ?>>
											<?php esc_html_e( 'Remove all plugin data when plugin is deleted', 'hideitdude' ); ?>
										</label>
										<p class="description">
											<?php esc_html_e( 'If enabled, all settings will be permanently deleted when you uninstall the plugin.', 'hideitdude' ); ?>
										</p>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				
				<p class="submit">
					<?php submit_button( __( 'Save Settings', 'hideitdude' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render admin menus checkboxes
	 *
	 * @param array $settings Plugin settings.
	 */
	private function render_admin_menus_checkboxes( $settings ) {
		$hidden_menus = isset( $settings['hide_admin_menus'] ) ? $settings['hide_admin_menus'] : array();
		if ( ! is_array( $hidden_menus ) ) {
			$hidden_menus = array();
		}
		
		$default_menus = array(
			'index.php'              => __( 'Dashboard', 'hideitdude' ),
			'edit.php'               => __( 'Posts', 'hideitdude' ),
			'upload.php'             => __( 'Media', 'hideitdude' ),
			'edit.php?post_type=page' => __( 'Pages', 'hideitdude' ),
			'edit-comments.php'      => __( 'Comments', 'hideitdude' ),
			'themes.php'             => __( 'Appearance', 'hideitdude' ),
			'plugins.php'            => __( 'Plugins', 'hideitdude' ),
			'users.php'              => __( 'Users', 'hideitdude' ),
			'tools.php'              => __( 'Tools', 'hideitdude' ),
			'options-general.php'    => __( 'Settings', 'hideitdude' ),
		);
		
		echo '<div class="hideitdude-checkboxes">';
		foreach ( $default_menus as $slug => $name ) {
			$checked = in_array( $slug, $hidden_menus, true ) ? 'checked' : '';
			printf(
				'<label><input type="checkbox" name="hide_admin_menus[]" value="%s" %s> %s</label>',
				esc_attr( $slug ),
				esc_attr( $checked ),
				esc_html( $name )
			);
		}
		echo '</div>';
	}

	/**
	 * Render admin bar checkboxes
	 *
	 * @param array $settings Plugin settings.
	 */
	private function render_admin_bar_checkboxes( $settings ) {
		$hidden_items = isset( $settings['hide_admin_bar_items'] ) ? $settings['hide_admin_bar_items'] : array();
		if ( ! is_array( $hidden_items ) ) {
			$hidden_items = array();
		}
		
		$admin_bar_items = array(
			'wp-logo'     => __( 'WordPress Logo', 'hideitdude' ),
			'site-name'   => __( 'Site Name', 'hideitdude' ),
			'updates'     => __( 'Updates', 'hideitdude' ),
			'comments'    => __( 'Comments', 'hideitdude' ),
			'new-content' => __( 'New Content', 'hideitdude' ),
			'edit'        => __( 'Edit', 'hideitdude' ),
			'my-account'  => __( 'My Account', 'hideitdude' ),
		);
		
		echo '<div class="hideitdude-checkboxes">';
		foreach ( $admin_bar_items as $id => $name ) {
			$checked = in_array( $id, $hidden_items, true ) ? 'checked' : '';
			printf(
				'<label><input type="checkbox" name="hide_admin_bar_items[]" value="%s" %s> %s</label>',
				esc_attr( $id ),
				esc_attr( $checked ),
				esc_html( $name )
			);
		}
		echo '</div>';
	}

	/**
	 * Render dashboard widgets checkboxes
	 *
	 * @param array $settings Plugin settings.
	 */
	private function render_dashboard_widgets_checkboxes( $settings ) {
		$hidden_widgets = isset( $settings['hide_dashboard_widgets'] ) ? $settings['hide_dashboard_widgets'] : array();
		if ( ! is_array( $hidden_widgets ) ) {
			$hidden_widgets = array();
		}
		
		$dashboard_widgets = array(
			'dashboard_right_now'   => __( 'At a Glance', 'hideitdude' ),
			'dashboard_activity'    => __( 'Activity', 'hideitdude' ),
			'dashboard_quick_press' => __( 'Quick Draft', 'hideitdude' ),
			'dashboard_primary'     => __( 'WordPress Events and News', 'hideitdude' ),
		);
		
		echo '<div class="hideitdude-checkboxes">';
		foreach ( $dashboard_widgets as $id => $name ) {
			$checked = in_array( $id, $hidden_widgets, true ) ? 'checked' : '';
			printf(
				'<label><input type="checkbox" name="hide_dashboard_widgets[]" value="%s" %s> %s</label>',
				esc_attr( $id ),
				esc_attr( $checked ),
				esc_html( $name )
			);
		}
		echo '</div>';
	}

	/**
	 * Save settings
	 */
	public function save_settings() {
		// CRITICAL: Check capability in save (again!)
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access', 'hideitdude' ) );
		}
		
		// CRITICAL: Verify nonce
		if ( ! isset( $_POST['hideitdude_nonce'] ) || 
		     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hideitdude_nonce'] ) ), 'hideitdude_save_settings' ) ) {
			wp_die( esc_html__( 'Security check failed', 'hideitdude' ) );
		}
		
		// Save checkbox arrays
		$hide_admin_menus = isset( $_POST['hide_admin_menus'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['hide_admin_menus'] ) ) : array();
		$this->database->save_setting( 'hide_admin_menus', $hide_admin_menus );
		
		$hide_admin_bar_items = isset( $_POST['hide_admin_bar_items'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['hide_admin_bar_items'] ) ) : array();
		$this->database->save_setting( 'hide_admin_bar_items', $hide_admin_bar_items );
		
		$hide_dashboard_widgets = isset( $_POST['hide_dashboard_widgets'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['hide_dashboard_widgets'] ) ) : array();
		$this->database->save_setting( 'hide_dashboard_widgets', $hide_dashboard_widgets );
		
		// Save single checkboxes
		$single_options = array(
			'hide_notifications',
			'hide_screen_options',
			'hide_help_tab',
			'replace_account_menu',
			'hide_profile_menu',
			'redirect_subscribers',
			'hide_admin_bar',
			'cleanup_on_uninstall',
		);
		
		foreach ( $single_options as $option ) {
			$value = isset( $_POST[ $option ] ) ? '1' : '0';
			$this->database->save_setting( $option, $value );
		}
		
		wp_safe_redirect( add_query_arg(
			array(
				'page'             => 'hideitdude-settings',
				'settings-updated' => 'true',
			),
			admin_url( 'tools.php' )
		) );
		exit;
	}
}
