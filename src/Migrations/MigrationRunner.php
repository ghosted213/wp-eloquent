<?php

namespace WPEloquent\Migrations;

class MigrationRunner {
	protected $migrationsTable = 'migrations';

	public function __construct() {
		global $wpdb;
		$table = $wpdb->prefix . $this->migrationsTable;

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        ) ENGINE=InnoDB;");
	}

	public function runMigrations() {
		global $wpdb;
		$table = $wpdb->prefix . $this->migrationsTable;

		$appliedMigrations = $wpdb->get_col("SELECT migration FROM {$table}");

		$migrationFiles = glob(__DIR__ . '/../../migrations/*.php');
		$batch = time();

		foreach ($migrationFiles as $file) {
			$migrationName = basename($file, '.php');
			if (!in_array($migrationName, $appliedMigrations)) {
				require_once $file;
				$migrationClass = new $migrationName();
				$migrationClass->up();

				$wpdb->insert($table, ['migration' => $migrationName, 'batch' => $batch]);
				echo "Migration applied: {$migrationName}\n";
			}
		}
	}

	public function rollbackMigrations($steps = 1) {
		global $wpdb;
		$table = $wpdb->prefix . $this->migrationsTable;

		$batches = $wpdb->get_col(
			$wpdb->prepare("SELECT DISTINCT batch FROM {$table} ORDER BY batch DESC LIMIT %d", $steps)
		);

		foreach ($batches as $batch) {
			$migrations = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE batch = %d", $batch));
			foreach ($migrations as $migration) {
				require_once __DIR__ . "/../../migrations/{$migration->migration}.php";
				$migrationClass = new $migration->migration();
				$migrationClass->down();

				$wpdb->delete($table, ['migration' => $migration->migration]);
				echo "Rolled back: {$migration->migration}\n";
			}
		}
	}
}