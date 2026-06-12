<?php
/**
 * Laravel Model Base Class - Using Eloquent Pattern
 */

namespace LaravelApp\Models;

use PDO;

/**
 * Base Model Class - Implements Eloquent-like ORM
 */
class Model {
    protected static $connection;
    protected $table;
    protected $fillable = [];
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;

    protected $timestamps = true;
    protected $created_at = 'created_at';
    protected $updated_at = 'updated_at';

    /**
     * Initialize PDO connection
     */
    protected static function getConnection() {
        if (self::$connection === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $database = getenv('DB_DATABASE') ?: 'sistema_labs';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';

            self::$connection = new PDO(
                "mysql:host=$host;dbname=$database;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }

        return self::$connection;
    }

    /**
     * Create a new model instance
     */
    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Set attribute value
     */
    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get attribute value
     */
    public function getAttribute($key) {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic getter
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter
     */
    public function __set($key, $value) {
        return $this->setAttribute($key, $value);
    }

    /**
     * Query builder - start a new query
     */
    public static function query() {
        return new QueryBuilder(get_called_class());
    }

    /**
     * Find by primary key
     */
    public static function find($id) {
        return static::query()->where('id', $id)->first();
    }

    /**
     * Get all records
     */
    public static function all() {
        return static::query()->get();
    }

    /**
     * Create a new instance
     */
    public static function create(array $attributes = []) {
        $model = new static();
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    /**
     * Save the model
     */
    public function save() {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Insert into database
     */
    protected function insert() {
        $pdo = self::getConnection();

        $attributes = $this->attributes;
        if ($this->timestamps) {
            $attributes[$this->created_at] = date('Y-m-d H:i:s');
            $attributes[$this->updated_at] = date('Y-m-d H:i:s');
        }

        $columns = implode(', ', array_keys($attributes));
        $placeholders = implode(', ', array_fill(0, count($attributes), '?'));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($attributes));

        $this->setAttribute('id', $pdo->lastInsertId());
        $this->exists = true;
        $this->original = $this->attributes;

        return true;
    }

    /**
     * Update in database
     */
    protected function update() {
        $pdo = self::getConnection();

        $changes = [];
        foreach ($this->attributes as $key => $value) {
            if (!isset($this->original[$key]) || $this->original[$key] != $value) {
                $changes[$key] = $value;
            }
        }

        if ($this->timestamps) {
            $changes[$this->updated_at] = date('Y-m-d H:i:s');
        }

        if (empty($changes)) {
            return true;
        }

        $set = implode(' = ?, ', array_keys($changes)) . ' = ?';
        $sql = "UPDATE {$this->table} SET $set WHERE id = ?";

        $values = array_values($changes);
        $values[] = $this->getAttribute('id');

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        $this->original = $this->attributes;
        return true;
    }

    /**
     * Delete from database
     */
    public function delete() {
        if (!$this->exists) {
            return false;
        }

        $pdo = self::getConnection();
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->getAttribute('id')]);

        return true;
    }

    /**
     * Get table name
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * Convert to array
     */
    public function toArray() {
        return $this->attributes;
    }
}

/**
 * Simple Query Builder Class
 */
class QueryBuilder {
    protected $model;
    protected $wheres = [];
    protected $orders = [];
    protected $limit = null;
    protected $offset = null;

    public function __construct($modelClass) {
        $this->model = $modelClass;
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function orderBy($column, $direction = 'asc') {
        $this->orders[] = "$column $direction";
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function first() {
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function get() {
        $pdo = $this->model::getConnection();
        $table = (new $this->model())->getTable();

        $sql = "SELECT * FROM $table";

        $bindings = [];
        if (!empty($this->wheres)) {
            $conditions = [];
            foreach ($this->wheres as $where) {
                $conditions[] = "{$where['column']} {$where['operator']} ?";
                $bindings[] = $where['value'];
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if (!empty($this->orders)) {
            $sql .= " ORDER BY " . implode(", ", $this->orders);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . intval($this->limit);
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . intval($this->offset);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $instance = new $this->model($row);
            $instance->exists = true;
            $instance->original = $row;
            $results[] = $instance;
        }

        return $results;
    }

    public function count() {
        return count($this->get());
    }
}
?>
