<?php
/**
 * Plugin Name: Wordpress Settings
 * Plugin URI: http://huypham.io
 * Description: Create Setting Page for Wordpress with Tabs
 * Version: 1.0.0
 * Author: Huy Pham
 * Author URI: http://huypham.io
 * License: GPL2
 */
add_action( 'plugins_loaded', [ 'HuyPham_Wordpress_Settings', 'init' ] );

class HuyPham_Wordpress_Settings {
	private $location;
	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	public function __construct() {
		$this->location = apply_filters('huypham_settings_location', 'theme_page'); // theme_page, options_page
		add_action( 'init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'settings_page_init' ] );
		add_action( 'huypham_setting_tab_panel', [ $this, 'general_settings' ], 0, 2 );
		add_filter( 'huypham_setting_save_tab', [ $this, 'save_general_settings' ], 0, 2 );
	}

	public function admin_init() {
		$settings = get_option( "huypham_settings" );
		if ( empty( $settings ) ) {
			$settings = apply_filters( 'huypham_settings_default_values', [ ] );
			add_option( "huypham_settings", $settings, '', 'yes' );
		}
	}

	/**
	 * Create Setting Page
	 */
	public function settings_page_init() {
		if($this->location == 'theme_page'){
			$settings_page = add_theme_page(
				apply_filters( 'huypham_settings_page_title', __( ' Wordpress Settings', 'huypham-text' ) ), // Page Title
				apply_filters( 'huypham_settings_menu_title', __( 'Wordpress Settings', 'huypham-text' ) ), // Menu Title
				apply_filters( 'huypham_settings_capability', 'edit_theme_options' ), // Capability
				'huypham-settings', // Menu Slug
				[ $this, 'settings_page' ] // Function
			);
		} else {
			$settings_page = add_options_page(
				apply_filters( 'huypham_settings_page_title', __( ' Wordpress Settings', 'huypham-text' ) ), // Page Title
				apply_filters( 'huypham_settings_menu_title', __( 'Wordpress Settings', 'huypham-text' ) ), // Menu Title
				apply_filters( 'huypham_settings_capability', 'edit_theme_options' ), // Capability
				'huypham-settings', // Menu Slug
				[ $this, 'settings_page' ] // Function
			);
		}
		add_action( "load-{$settings_page}", [ $this, 'load_settings_page' ] );
	}

	/**
	 * Load Setting Page follow the page created
	 */
	public function load_settings_page() {
		if ( $_POST["huypham-settings-submit"] == 'Y' ) {
			check_admin_referer( "huypham-settings-page" );
			$this->save_theme_settings();
			$url_parameters = isset( $_GET['tab'] ) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
			wp_redirect( admin_url( 'themes.php?page=huypham-settings&' . $url_parameters ) );
			exit;
		}
	}

	/**
	 * Display content of Setting Page
	 */
	public function settings_page() {
		global $pagenow;
		$settings = get_option( "huypham_settings" );
		$parent_page = $this->location == 'theme_page' ? 'themes.php' : 'options-general.php';
		?>

		<div class="wrap">
			<h2><?php echo apply_filters( 'huypham_settings_page_heading', __( 'Theme Settings', 'huypham-text' ) ); ?> </h2>

			<?php
			if ( 'true' == esc_attr( $_GET['updated'] ) ) {
				echo '<div class="updated" ><p>' . apply_filters( 'huypham_settings_updated_text', __( 'Theme Settings updated.', 'huypham-text' ) ) . '</p></div>';
			}

			if ( isset ( $_GET['tab'] ) ) {
				$this->admin_tabs( $_GET['tab'] );
			} else {
				$this->admin_tabs( 'general' );
			}
			?>

			<div id="poststuff">
				<form method="post" action="<?php admin_url( $parent_page.'?page=theme-settings' ); ?>">
					<?php
					wp_nonce_field( "huypham-settings-page" );

					if ( $pagenow == $parent_page && $_GET['page'] == 'huypham-settings' ) {

						if ( isset ( $_GET['tab'] ) ) {
							$tab = $_GET['tab'];
						} else {
							$tab = 'general';
						}

						echo '<table class="form-table">';
						do_action( 'huypham_setting_tab_panel', $settings, $tab );
						echo '</table>';
					}
					?>
					<p class="submit" style="clear: both;">
						<input type="submit" name="Submit" class="button-primary" value="Update Settings"/>
						<input type="hidden" name="huypham-settings-submit" value="Y"/>
					</p>
				</form>
			</div>

		</div>
		<?php
	}

	/**
	 * Create Tabs for Setting Page
	 *
	 * @param string $current
	 */
	public function admin_tabs( $current = 'homepage' ) {
		$tabs = apply_filters( 'huypham_setting_heading_tabs', [ 'general' => apply_filters( 'huypham_settings_heading_tab_general', __( 'General', 'huypham-text' ) ) ] );
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			$class = ( $tab == $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=huypham-settings&tab=$tab'>$name</a>";

		}
		echo '</h2>';
	}

	/**
	 * Save Setting Page
	 */
	public function save_theme_settings() {
		global $pagenow;
		$parent_page = $this->location == 'theme_page' ? 'themes.php' : 'options-general.php';
		$settings = get_option( "huypham_settings" );
		if ( $pagenow == $parent_page && $_GET['page'] == 'huypham-settings' ) {
			if ( isset ( $_GET['tab'] ) ) {
				$tab = $_GET['tab'];
			} else {
				$tab = 'general';
			}
			$settings = apply_filters( 'huypham_setting_save_tab', $settings, $tab );

		}
		$updated = update_option( "huypham_settings", $settings );
	}

	public function general_settings( $settings, $tab ) {
		if ( $tab == 'general' ) {
			do_action( 'huypham_settings_tab_panel_general', $settings );
		}
	}

	public function save_general_settings( $settings, $tab ) {
		if ( $tab == 'general' ) {
			return apply_filters( 'huypham_settings_save_tab_general', $settings );
		} else {
			return $settings;
		}
	}
}

include( 'sample-config.php' );








