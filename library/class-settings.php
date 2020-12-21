<?php
/**
 * JS Engine settings.
 *
 * @package jsengine\library
 */

namespace JSEngine;

/**
 * Settings.
 *
 * @since  1.0.0
 */
class Settings {
	/**
	 * Holds the instances of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Page hook for the settings screen
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $settings_screen = null;

	/**
	 * Initialize the settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'activation_redirect' ) );

		add_action( 'wp_ajax_jse_save', array( $this, 'save' ) );

		// Add the required scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add the importer options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( JS_ENGINE_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Activation redirect.
	 */
	public function activation_redirect() {
		if ( get_option( 'js_engine_activating', false ) ) {
			delete_option( 'js_engine_activating' );
			if ( wp_safe_redirect( admin_url( 'admin.php?page=js-engine' ) ) ) {
				exit;
			}
		}
	}

	/**
	 * Add plugin list action links.
	 *
	 * @since  1.0.0
	 * @param  array $links Links.
	 * @return array        Added links.
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=js-engine' ) . '">' . esc_html__( 'Settings', 'jsengine' ) . '</a>',
		);

		return array_merge( $links, $plugin_links );
	}

	/**
	 * Save options.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		if (
			! isset( $_POST['nonce'] )
			|| ! isset( $_POST['options'] )
			|| ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'js_engine' ) )
		) {
			die( '0' );
		}

		$new_options = json_decode( sanitize_textarea_field( wp_unslash( $_POST['options'] ) ), true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			die( '0' );
		}

		$options = get_option( 'js_engine' );

		if ( $options === $new_options ) {
			wp_send_json( 2 );
		}

		wp_send_json( update_option( 'js_engine', $new_options ) );
	}

	/**
	 * Get all themes.
	 *
	 * @since  1.0.0
	 * @return array All themes.
	 */
	public function get_themes() {
		$themes = array();

		$all = wp_get_themes();
		foreach ( $all as $k => $t ) {
			$template = $t->get( 'Template' );

			$theme = array(
				'value'     => $k,
				'label'     => $t->get( 'Name' ),
				'parent'    => ! empty( $template ) ? $template : $k,
				'templates' => js_engine_get_templates( $t->theme_root . DIRECTORY_SEPARATOR . $k ),
			);
			array_push( $themes, $theme );
		}
		return $themes;
	}

	/**
	 * Loads the required javascript.
	 *
	 * @since 1.0.0
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $this->settings_screen !== $hook ) {
			return;
		}

		$engine_object = array(
			'title'     => __( 'JS Engine', 'jsengine' ),
			'apiHome'   => JS_ENGINE_API_HOME,
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'js_engine' ),
			'dnonce'    => wp_create_nonce( 'jse_download' ),
			'themes'    => $this->get_themes(),
			'options'   => jse_options(),
			'templates' => jse_options(),
			'installed' => file_exists( JS_ENGINE_UPLOADS_DIR . '/engine' ),
			'canExec'   => function_exists( 'exec' ),
		);

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'js-engine', JS_ENGINE_JS_URL . '/engine' . $min . '.js', array( 'react-dom', 'wp-i18n', 'wp-components' ), JS_ENGINE_VERSION, true );
		wp_localize_script( 'js-engine', 'JSEngine', $engine_object );

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'js-engine', 'jsengine' );
		}

		wp_enqueue_style( 'js-engine', JS_ENGINE_CSS_URL . '/engine.css', array( 'wp-components' ), JS_ENGINE_VERSION );
	}

	/**
	 * Define menu options.
	 *
	 * @since 1.0.0
	 */
	protected static function menu_settings() {
		return array(
			'page_title' => __( 'JS Engine', 'jsengine' ),
			'menu_title' => __( 'JS Engine', 'jsengine' ),
			'capability' => 'edit_theme_options',
			'menu_slug'  => 'js-engine',
			'icon_url'   => 'dashicons-airplane',
			'position'   => '61',
		);
	}

	/**
	 * Add menu page.
	 *
	 * @since 1.0.0
	 */
	public function add_menu() {
		$menu = $this->menu_settings();

		$this->settings_screen = add_menu_page(
			$menu['page_title'],
			$menu['menu_title'],
			$menu['capability'],
			$menu['menu_slug'],
			array( $this, 'settings_page' ),
			$menu['icon_url'],
			$menu['position']
		);
	}

	/**
	 * Build out the settings panel.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {
		?>
		<div id="js-engine"></div>
		<?php
	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Settings::get_instance();
