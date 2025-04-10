<?php

namespace WPEloquent\Commands;

use WP_CLI;
use WPEloquent\Migrations\MigrationRunner;
use WPEloquent\Seeders\SeederRunner;

class WPCLICommands {
	public function migrate() {
		$migrationRunner = new MigrationRunner();
		$migrationRunner->runMigrations();
		WP_CLI::success('Migrations applied successfully.');
	}

	public function rollback($args, $assoc_args) {
		$steps = isset($assoc_args['steps']) ? (int) $assoc_args['steps'] : 1;
		$migrationRunner = new MigrationRunner();
		$migrationRunner->rollbackMigrations($steps);
		WP_CLI::success("Rolled back {$steps} batch(es) successfully.");
	}

	public function seed() {
		$seederRunner = new SeederRunner();
		$seederRunner->runSeeders();
		WP_CLI::success('Seeders executed successfully.');
	}
}