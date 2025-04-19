<?php

namespace WPEloquent\Seeders;

class SeederRunner {
	protected $seedersRegistry;

	public function __construct($seedersRegistry) {
		$this->seedersRegistry = $seedersRegistry;
	}

	public function runSeeders() {
		foreach ($this->seedersRegistry as $pluginName => $path) {
			$seederFiles = glob($path . '/*.php');

			foreach ($seederFiles as $file) {
				require_once $file;
				$seederName = basename($file, '.php');
				$seederClass = new $seederName();
				$seederClass->run();
				echo "Seeder executed: {$seederName} (Plugin: {$pluginName})\n";
			}
		}
	}
}