<?php
namespace WPEloquent;

use WPEloquent\Commands\WPCLICommands;

/**
 * Main class.
 */
class Eloquent {

	private static $plugin_file;

	public static function init( $plugin_file ) {
		self::$plugin_file = $plugin_file;
		spl_autoload_register( [ __CLASS__, 'autoload' ] );

		/**
		 * Fires in the early stages of Eloquent init hook.
		 */
		do_action( 'eloquent_pre_init' );

		if ( ! did_action( 'init' ) ) {
			add_action( 'init', array( __CLASS__, 'start' ) );
		} else {
			self::start();
		}
	}

	public static function autoload( $class ) {

	}

	/**
	 * Get the absolute system path to the plugin directory, or a file thereinز
	 *
	 * @static
	 * @param string $path Path relative to plugin directory.
	 * @return string
	 */
	public static function plugin_path( $path ) {
		$base = dirname( self::$plugin_file );
		if ( $path ) {
			return trailingslashit( $base ) . $path;
		} else {
			return untrailingslashit( $base );
		}
	}

	public static function start() {
		self::init_commands();

		/**
		 * Fires when Eloquent is ready: it is safe to use the procedural API after this point.
		 */
		do_action( 'action_scheduler_init' );
	}



	private static function init_commands() {
		if ( ! defined('WP_CLI') || ! WP_CLI ) {
			return;
		}

		$commands = new WPCLICommands();

		WP_CLI::add_command( 'orm:migrate', [ $commands, 'migrate' ] );
		WP_CLI::add_command( 'orm:migrate:rollback', [ $commands, 'rollback' ] );
		WP_CLI::add_command( 'orm:seed', [ $commands, 'seed' ] );
	}

}