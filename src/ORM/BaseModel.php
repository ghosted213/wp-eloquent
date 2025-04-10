<?php

namespace WPEloquent\ORM;

use wpdb;

abstract class BaseModel {
	protected $table; // The name of the database table
	protected $primaryKey = 'id'; // The primary key column
	protected $fillable = []; // Columns that can be mass-assigned
	protected $attributes = []; // Model attributes (column values)
	protected $timestamps = true; // Automatically manage timestamps
	protected static $wpdb;

	public function __construct(array $attributes = []) {
		global $wpdb;
		self::$wpdb = $wpdb;

		$this->fill($attributes);
	}

	// Mass-assign attributes
	public function fill(array $attributes) {
		foreach ($attributes as $key => $value) {
			if (in_array($key, $this->fillable)) {
				$this->attributes[$key] = $value;
			}
		}
	}

	// Get an attribute
	public function __get($key) {
		return $this->attributes[$key] ?? null;
	}

	// Set an attribute
	public function __set($key, $value) {
		if (in_array($key, $this->fillable)) {
			$this->attributes[$key] = $value;
		}
	}

	// Save the model (insert or update)
	public function save() {
		if (isset($this->attributes[$this->primaryKey])) {
			// Update
			self::$wpdb->update(
				$this->table,
				$this->attributes,
				[$this->primaryKey => $this->attributes[$this->primaryKey]]
			);
		} else {
			// Insert
			self::$wpdb->insert($this->table, $this->attributes);
			$this->attributes[$this->primaryKey] = self::$wpdb->insert_id;
		}

		if ($this->timestamps) {
			$this->attributes['updated_at'] = current_time('mysql');
		}
	}

	// Find a record by primary key
	public static function find($id) {
		global $wpdb;
		$instance = new static();
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = %d", $id), ARRAY_A);
		if ($result) {
			return new static($result);
		}
		return null;
	}

	// Delete the model
	public function delete() {
		if (isset($this->attributes[$this->primaryKey])) {
			return self::$wpdb->delete($this->table, [$this->primaryKey => $this->attributes[$this->primaryKey]]);
		}
		return false;
	}

	// Begin a query (static access)
	public static function query() {
		return new QueryBuilder(new static());
	}

	// Define the `hasOne` relationship
	public function hasOne($relatedModel, $foreignKey, $localKey = null) {
		$localKey = $localKey ?? $this->primaryKey;
		$relatedInstance = new $relatedModel();
		return $relatedInstance->query()->where($foreignKey, '=', $this->attributes[$localKey])->first();
	}

	// Define the `hasMany` relationship
	public function hasMany($relatedModel, $foreignKey, $localKey = null) {
		$localKey = $localKey ?? $this->primaryKey;
		$relatedInstance = new $relatedModel();
		return $relatedInstance->query()->where($foreignKey, '=', $this->attributes[$localKey])->get();
	}

	// Define the `belongsTo` relationship
	public function belongsTo($relatedModel, $foreignKey, $ownerKey = null) {
		$ownerKey = $ownerKey ?? (new $relatedModel())->primaryKey;
		$relatedInstance = new $relatedModel();
		return $relatedInstance->query()->where($ownerKey, '=', $this->attributes[$foreignKey])->first();
	}

	// Define the `belongsToMany` relationship
	public function belongsToMany($relatedModel, $pivotTable, $foreignKey, $relatedKey) {
		$relatedInstance = new $relatedModel();
		$localKey = $this->primaryKey;

		$query = "SELECT {$relatedInstance->table}.* 
                  FROM {$relatedInstance->table}
                  INNER JOIN {$pivotTable}
                  ON {$relatedInstance->table}.{$relatedInstance->primaryKey} = {$pivotTable}.{$relatedKey}
                  WHERE {$pivotTable}.{$foreignKey} = %d";

		global $wpdb;
		$results = $wpdb->get_results($wpdb->prepare($query, $this->attributes[$localKey]), ARRAY_A);

		return array_map(function ($result) use ($relatedModel) {
			return new $relatedModel($result);
		}, $results);
	}
}