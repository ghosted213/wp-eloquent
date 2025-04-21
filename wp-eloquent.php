<?php
/**
 * Plugin Name: Eloquent
 * Description: A standalone WordPress ORM plugin for managing database models, migrations, and seeders.
 * Version: 1.0.0
 * Author: WordpressFan
 * Author URI: https://github.com/WordpressFan
 * Text Domain: eloquent
 * Requires at least: 6.5
 * Tested up to: 6.7
 * Requires PHP: 7.1
 */

if ( ! function_exists( 'eloquent_register_1_dot_0_dot_0' ) && function_exists( 'add_action' ) ) { // WRCS: DEFINED_VERSION.

	if ( ! class_exists( '\WPEloquent\Versions', false ) ) {
		require_once __DIR__ . '/src/Versions.php';
		add_action( 'plugins_loaded', array( 'Versions', 'initialize_latest_version' ), 1, 0 );
	}

	add_action( 'plugins_loaded', 'eloquent_register_1_dot_0_dot_0', 0, 0 ); // WRCS: DEFINED_VERSION.

	/**
	 * Registers this version of Eloquent.
	 */
	function eloquent_register_1_dot_0_dot_0() {
		$versions = \WPEloquent\Versions::instance();
		$versions->register( '1.0.0', 'eloquent_initialize_1_dot_0_dot_0' );
	}

	/**
	 * Initializes this version of Eloquent.
	 */
	function eloquent_initialize_1_dot_0_dot_0() {
		if ( class_exists( '\WPEloquent\Eloquent', false ) ) {
			return;
		}

		require_once __DIR__ . '/src/Eloquent.php';
		\WPEloquent\Eloquent::init( __FILE__ );
	}

	// Support usage in themes - load this version if no plugin has loaded a version yet.
	if ( did_action( 'plugins_loaded' ) && ! doing_action( 'plugins_loaded' ) && ! class_exists( '\WPEloquent\Eloquent', false ) ) {
		eloquent_initialize_1_dot_0_dot_0();
		do_action( 'eloquent_pre_theme_init' );
		\WPEloquent\Versions::initialize_latest_version();
	}
}
