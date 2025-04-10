<?php

namespace WPEloquent\Seeders;

class SeederRunner {
	public function runSeeders() {
		$seederFiles = glob(__DIR__ . '/../../seeders/*.php');

		foreach ($seederFiles as $file) {
			require_once $file;
			$seederName = basename($file, '.php');
			$seederClass = new $seederName();
			$seederClass->run();
		}
	}
}