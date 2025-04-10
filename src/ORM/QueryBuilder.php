<?php

namespace WPEloquent\ORM;

class QueryBuilder {
	protected $model;
	protected $query = '';
	protected $bindings = [];
	protected $withRelations = [];

	public function __construct($model) {
		$this->model = $model;
	}

	public function where($column, $operator, $value) {
		$this->query .= empty($this->query) ? " WHERE " : " AND ";
		$this->query .= "{$column} {$operator} %s";
		$this->bindings[] = $value;
		return $this;
	}

	public function orderBy($column, $direction = 'ASC') {
		$this->query .= " ORDER BY {$column} {$direction}";
		return $this;
	}

	public function limit($limit) {
		$this->query .= " LIMIT {$limit}";
		return $this;
	}

	public function with($relations) {
		$this->withRelations = (array) $relations;
		return $this;
	}

	protected function eagerLoad($models) {
		foreach ($this->withRelations as $relation) {
			foreach ($models as $model) {
				if (method_exists($model, $relation)) {
					$model->$relation = $model->$relation();
				}
			}
		}
		return $models;
	}

	public function get() {
		global $wpdb;
		$table = $this->model->table;
		$sql = "SELECT * FROM {$table}{$this->query}";
		$results = $wpdb->get_results($wpdb->prepare($sql, ...$this->bindings), ARRAY_A);

		$models = array_map(function ($result) {
			return new ($this->model)($result);
		}, $results);

		return $this->eagerLoad($models);
	}

	public function first() {
		$this->limit(1);
		$results = $this->get();
		return $results[0] ?? null;
	}

	public function paginate($perPage, $page = 1) {
		global $wpdb;
		$offset = ($page - 1) * $perPage;
		$table = $this->model->table;

		$sql = "SELECT * FROM {$table}{$this->query} LIMIT %d OFFSET %d";
		$results = $wpdb->get_results($wpdb->prepare($sql, $perPage, $offset), ARRAY_A);

		$models = array_map(function ($result) {
			return new ($this->model)($result);
		}, $results);

		// Get total count for pagination metadata
		$totalSql = "SELECT COUNT(*) FROM {$table}{$this->query}";
		$total = $wpdb->get_var($wpdb->prepare($totalSql, ...$this->bindings));

		return [
			'data' => $models,
			'meta' => [
				'total' => $total,
				'per_page' => $perPage,
				'current_page' => $page,
				'last_page' => ceil($total / $perPage),
			],
		];
	}

	public function join($table, $first, $operator, $second) {
		$this->query .= " INNER JOIN {$table} ON {$first} {$operator} {$second}";
		return $this;
	}

	public function leftJoin($table, $first, $operator, $second) {
		$this->query .= " LEFT JOIN {$table} ON {$first} {$operator} {$second}";
		return $this;
	}
}