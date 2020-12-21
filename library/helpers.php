<?php
/**
 * JS Engine helpers.
 *
 * @package jsengine\library
 */

/**
 * Run activation hook.
 */
function js_engine_activate() {
	update_option( 'js_engine_activating', true );

	$upload_dir = wp_upload_dir();
	if ( ! file_exists( $upload_dir['basedir'] . '/js-engine/' ) ) {
		wp_mkdir_p( $upload_dir['basedir'] . '/js-engine/' );

		// Add an index file for security.
		js_engine_get_filesystem()->put_contents( $upload_dir['basedir'] . '/js-engine/index.html', '' );
	}
}

/**
 * Get file system.
 *
 * @return mixed
 */
function js_engine_get_filesystem() {
	global $wp_filesystem;
	require_once ABSPATH . '/wp-admin/includes/file.php';
	WP_Filesystem();
	return $wp_filesystem;
}

/**
 * Get JS Engine options.
 *
 * @since  1.0.0
 * @param  bool $name Option name.
 * @return bool|mixed Option or options on success, false on failure.
 */
function jse_options( $name = false ) {
	$default = array(
		'enabled'  => true,
		'theme'    => get_stylesheet(),
		'parent'   => get_template(),
		'template' => 'index.php',
	);

	$options = get_option( 'js_engine', $default );
	if ( false !== $name ) {
		return isset( $options[ $name ] ) ? $options[ $name ] : false;
	}
	return $options;
}

/**
 * JS Engine SSR.
 *
 * @param string $path Server script path.
 * @since 1.0.0
 * @return bool|void   False on disable, void on echo.
 */
function jse_ssr( $path = 'server' ) {
	if (
		! jse_options( 'enabled' )
		|| ! current_user_can( 'edit_theme_options' )
		|| ! function_exists( 'exec' )
	) {
		return false;
	}

	$pdir = JS_ENGINE_PLUGIN_DIR;
	$tdir = get_stylesheet_directory();

	$command = 'cp -u -t ' . $tdir . '/ ' . $pdir . '/assets/engine &&
	cd ' . $tdir . ' &&
	./engine ' . $pdir . '/entry ' . $tdir . '/' . $path;

	$lines = array();
	$res   = 0;

	exec( $command, $lines, $res ); // phpcs:ignore

	// Todo: If res is not 0, command failed.
	foreach ( $lines as $line ) {
		echo $line; // phpcs:ignore
	}
}

/**
 * Get array of files from directory.
 *
 * @param  string $dir Directory.
 * @return array       Files array.
 */
function js_engine_get_templates( $dir ) {
	$result = array();
	$cdir   = scandir( $dir );

	foreach ( $cdir as $key => $value ) {
		if (
			! in_array( $value, array( '.', '..' ), true )
			&& ( 'functions.php' !== $value )
			&& ( strpos( $value, '.php' ) !== false )
			&& ! is_dir( $dir . DIRECTORY_SEPARATOR . $value )
		) {
			$result[] = $value;
		}
	}

	return $result;
}
