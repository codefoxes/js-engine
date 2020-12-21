<?php
/**
 * Plugin Name: JS Engine
 * Description: Build and SSR for Javascript frameworks
 * Plugin URI: https://greenletwp.com/js-engine
 * Author: Greenlet Team
 * Version: 1.0.0
 * Author URI: https://greenletwp.com/about/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: jsengine
 * Domain Path: /library/languages
 *
 * @package jsengine
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JS_ENGINE_VERSION', '1.0.0' );
define( 'JS_ENGINE_PLUGIN_FILE', __FILE__ );
define( 'JS_ENGINE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JS_ENGINE_LIBRARY_DIR', JS_ENGINE_PLUGIN_DIR . 'library' );

define( 'JS_ENGINE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JS_ENGINE_JS_URL', JS_ENGINE_PLUGIN_URL . 'assets/js' );
define( 'JS_ENGINE_CSS_URL', JS_ENGINE_PLUGIN_URL . 'assets/css' );

require_once JS_ENGINE_LIBRARY_DIR . '/helpers.php';

if ( jse_options( 'enabled' ) ) {
	require_once JS_ENGINE_LIBRARY_DIR . '/class-templates.php';
}

/**
 * Load importer.
 *
 * @since 1.0.0
 */
function js_engine_load_importer() {
	if ( is_admin() ) {
		require_once JS_ENGINE_LIBRARY_DIR . '/class-settings.php';
	}
}

add_action( 'init', 'js_engine_load_importer' );

register_activation_hook( __FILE__, 'js_engine_activate' );
