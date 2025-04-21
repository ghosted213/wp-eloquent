<?php
namespace WPEloquent;

use WPEloquent\Commands\WPCLICommands;

/**
 * Class ActionScheduler_Versions
 */
class Eloquent {

	private static $plugin_file;

	public static function init( $plugin_file ) {
		self::$plugin_file = $plugin_file;

		self::init_commands();
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