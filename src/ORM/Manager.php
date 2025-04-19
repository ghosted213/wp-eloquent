<?php

namespace WPEloquent\ORM;

use WPEloquent\Migrations\MigrationRunner;
use WPEloquent\Seeders\SeederRunner;

class Manager {
	protected static $migrationsRegistry = [];
	protected static $seedersRegistry = [];

	/**
	 * Register a plugin's migration path.
	 *
	 * @param string $pluginName
	 * @param string $migrationsPath
	 */
	public static function registerMigrations($pluginName, $migrationsPath) {
		self::$migrationsRegistry[$pluginName] = $migrationsPath;
	}

	/**
	 * Register a plugin's seeder path.
	 *
	 * @param string $pluginName
	 * @param string $seedersPath
	 */
	public static function registerSeeders($pluginName, $seedersPath) {
		self::$seedersRegistry[$pluginName] = $seedersPath;
	}

	/**
	 * Get a centralized MigrationRunner for all registered plugins.
	 *
	 * @return MigrationRunner
	 */
	public static function getMigrationRunner() {
		return new MigrationRunner(self::$migrationsRegistry);
	}

	/**
	 * Get a centralized SeederRunner for all registered plugins.
	 *
	 * @return SeederRunner
	 */
	public static function getSeederRunner() {
		return new SeederRunner(self::$seedersRegistry);
	}
}