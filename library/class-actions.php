<?php
/**
 * JS Engine Actions.
 *
 * @package jsengine\library
 */

namespace JSEngine;

/**
 * Actions.
 *
 * @since  1.0.0
 */
class Actions {
	/**
	 * Holds the instances of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Initialize actions.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		add_action( 'wp_ajax_jse_download', array( $this, 'download' ) );
	}

	/**
	 * Activation redirect.
	 */
	public function download() {
		if (
			! isset( $_POST['nonce'] )
			|| ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'jse_download' ) )
		) {
			wp_send_json_error( __( 'Download failed', 'jsengine' ) );
		}

		$url = JS_ENGINE_API_HOME . '/wp-content/uploads/js-engine/engine.zip';
		$zip = JS_ENGINE_UPLOADS_DIR . '/engine.zip';

		if ( ! copy( $url, $zip ) ) {
			wp_send_json_error( __( 'Download failed', 'jsengine' ) );
		}

		$wp_filesystem = js_engine_get_filesystem();

		$to = str_replace( ABSPATH, $wp_filesystem->abspath(), JS_ENGINE_UPLOADS_DIR );

		$result = unzip_file( $zip, $to );

		if ( true !== $result ) {
			wp_send_json_error( __( 'Unzip failed', 'jsengine' ) );
		}

		wp_delete_file( $zip );

		wp_send_json(
			array(
				'success' => true,
				'data'    => __( 'Downloaded successfully', 'jsengine' ),
			)
		);
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

Actions::get_instance();
