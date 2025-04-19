<?php

if (defined('WP_CLI') && WP_CLI) {
	require_once __DIR__ . '/src/Commands/WPCLICommands.php';
	require_once __DIR__ . '/src/ORM/ORMManager.php';

	// Register the WP-CLI commands with the central ORM Manager
	WP_CLI::add_command('orm:migrate', [new \WPEloquent\Commands\WPCLICommands(), 'migrate']);
	WP_CLI::add_command('orm:migrate:rollback', [new \WPEloquent\Commands\WPCLICommands(), 'rollback']);
	WP_CLI::add_command('orm:seed', [new \WPEloquent\Commands\WPCLICommands(), 'seed']);
}