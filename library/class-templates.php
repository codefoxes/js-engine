<?php
/**
 * JS Engine templates.
 *
 * @package jsengine\library
 */

namespace JSEngine;

/**
 * Templates.
 *
 * @since  1.0.0
 */
class Templates {
	/**
	 * Holds the instances of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Initialize the templates.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'template', array( $this, 'switch_parent' ) );
		add_filter( 'option_template', array( $this, 'switch_parent' ) );
		add_filter( 'stylesheet', array( $this, 'switch_theme' ) );
		add_filter( 'option_stylesheet', array( $this, 'switch_theme' ) );

		add_filter( 'template_include', array( $this, 'override_template' ) );
	}

	/**
	 * Switch Template.
	 *
	 * @since  1.0.0
	 * @param  string $parent Current Template name.
	 * @return string Template name.
	 */
	public function switch_parent( $parent ) {
		$jse = get_option( 'js_engine' );
		return ( false !== $jse ) ? $jse['parent'] : $parent;
	}

	/**
	 * Switch Stylesheet.
	 *
	 * @since  1.0.0
	 * @param  string $theme Current Stylesheet name.
	 * @return string        Stylesheet name.
	 */
	public function switch_theme( $theme ) {
		$jse = get_option( 'js_engine' );
		return ( false !== $jse ) ? $jse['theme'] : $theme;
	}

	/**
	 * Override template.
	 *
	 * @since  1.0.0
	 * @param  string $original Original template.
	 * @return string           Template name.
	 */
	public function override_template( $original ) {
		$template = jse_options( 'template' );
		if ( 'default' === $template ) {
			return $original;
		}
		return locate_template( $template );
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

Templates::get_instance();
