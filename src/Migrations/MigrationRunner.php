<?php

namespace WPEloquent\Migrations;

class MigrationRunner {
	protected $migrationsTable = 'migrations';
	protected $migrationsRegistry;

	public function __construct($migrationsRegistry) {
		global $wpdb;
		$this->migrationsRegistry = $migrationsRegistry;
		$table = $wpdb->prefix . $this->migrationsTable;

		// Create the migrations table if it doesn't exist
		$wpdb->query("CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            plugin_name VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        ) ENGINE=InnoDB;");
	}

	public function runMigrations() {
		global $wpdb;
		$table = $wpdb->prefix . $this->migrationsTable;

		$appliedMigrations = $wpdb->get_results("SELECT migration, plugin_name FROM {$table}", OBJECT_K);

		foreach ($this->migrationsRegistry as $pluginName => $path) {
			$migrationFiles = glob($path . '/*.php');
			$batch = time();

			foreach ($migrationFiles as $file) {
				$migrationName = basename($file, '.php');

				// Skip already applied migrations
				if (isset($appliedMigrations["{$pluginName}:{$migrationName}"])) {
					continue;
				}

				require_once $file;
				$migrationClass = new $migrationName();
				$migrationClass->up();

				// Record the migration in the database
				$wpdb->insert($table, [
					'migration' => $migrationName,
					'plugin_name' => $pluginName,
					'batch' => $batch,
				]);
				echo "Migration applied: {$migrationName} (Plugin: {$pluginName})\n";
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
				$path = $this->migrationsRegistry[$migration->plugin_name];
				require_once $path . "/{$migration->migration}.php";

				$migrationClass = new $migration->migration();
				$migrationClass->down();

				// Remove the migration record
				$wpdb->delete($table, ['migration' => $migration->migration]);
				echo "Rolled back: {$migration->migration} (Plugin: {$migration->plugin_name})\n";
			}
		}
	}
}
