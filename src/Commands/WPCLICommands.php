<?php

namespace WPEloquent\Commands;

use WP_CLI;
use WPEloquent\ORM\Manager as ORMManager;

class WPCLICommands {
	public function migrate() {
		$migrationRunner = ORMManager::getMigrationRunner();
		$migrationRunner->runMigrations();
		WP_CLI::success( 'Migrations applied successfully.' );
	}

	public function rollback( $args, $assoc_args ) {
		$steps = isset( $assoc_args['steps'] ) ? (int) $assoc_args['steps'] : 1;
		$migrationRunner = ORMManager::getMigrationRunner();
		$migrationRunner->rollbackMigrations( $steps );
		WP_CLI::success( "Rolled back {$steps} batch(es) successfully." );
	}

	public function seed() {
		$seederRunner = ORMManager::getSeederRunner();
		$seederRunner->runSeeders();
		WP_CLI::success( 'Seeders executed successfully.' );
	}
}
