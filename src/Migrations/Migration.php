<?php

namespace WPEloquent\Migrations;

abstract class Migration {
	abstract public function up();
	abstract public function down();
}