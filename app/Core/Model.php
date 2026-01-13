<?php

namespace App\Core;

/**
 * Base Model Class
 * Provides basic CRUD operations and attribute handling
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $casts = [];

    protected array $attributes = [];
    protected array $original = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Set attribute
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $this->castAttribute($key, $value);
    }

    /**
     * Get attribute
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic getter
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic isset
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Cast attribute to type
     */
    protected function castAttribute(string $key, $value)
    {
        if (!isset(static::$casts[$key]) || $value === null) {
            return $value;
        }

        switch (static::$casts[$key]) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'datetime':
                return $value;
            default:
                return $value;
        }
    }

    /**
     * Get all attributes
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get primary key value
     */
    public function getId()
    {
        return $this->getAttribute(static::$primaryKey);
    }

    /**
     * Check if model exists in database
     */
    public function exists(): bool
    {
        return !empty($this->getId());
    }

    /**
     * Get dirty (changed) attributes
     */
    public function getDirty(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    /**
     * Check if model has changes
     */
    public function isDirty(): bool
    {
        return !empty($this->getDirty());
    }

    /**
     * Save model to database
     */
    public function save(): bool
    {
        $db = Database::getInstance();

        if ($this->exists()) {
            // Update
            $dirty = $this->getDirty();
            if (empty($dirty)) {
                return true;
            }

            // Auto update timestamps
            if (!isset($dirty['updated_at'])) {
                $dirty['updated_at'] = date('Y-m-d H:i:s');
                $this->attributes['updated_at'] = $dirty['updated_at'];
            }

            $db->update(
                static::$table,
                $dirty,
                static::$primaryKey . ' = ?',
                [$this->getId()]
            );
        } else {
            // Insert
            $data = $this->getInsertData();

            // Auto timestamps
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->attributes['created_at'] = $data['created_at'];
            }
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->attributes['updated_at'] = $data['updated_at'];
            }

            $id = $db->insert(static::$table, $data);
            $this->attributes[static::$primaryKey] = $id;
        }

        $this->original = $this->attributes;
        return true;
    }

    /**
     * Get data for insert (only fillable fields)
     */
    protected function getInsertData(): array
    {
        if (empty(static::$fillable)) {
            return $this->attributes;
        }

        $data = [];
        foreach (static::$fillable as $field) {
            if (array_key_exists($field, $this->attributes)) {
                $data[$field] = $this->attributes[$field];
            }
        }
        return $data;
    }

    /**
     * Delete model from database
     */
    public function delete(): bool
    {
        if (!$this->exists()) {
            return false;
        }

        $db = Database::getInstance();
        $db->delete(static::$table, static::$primaryKey . ' = ?', [$this->getId()]);

        return true;
    }

    /**
     * Find by primary key
     */
    public static function find(int $id): ?static
    {
        $db = Database::getInstance();
        $row = $db->fetch(
            'SELECT * FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ?',
            [$id]
        );

        return $row ? new static($row) : null;
    }

    /**
     * Find by primary key or fail
     */
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        if (!$model) {
            abort(404);
        }
        return $model;
    }

    /**
     * Get all records
     */
    public static function all(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll('SELECT * FROM `' . static::$table . '`');

        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Find by column
     */
    public static function where(string $column, $value): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            'SELECT * FROM `' . static::$table . '` WHERE `' . $column . '` = ?',
            [$value]
        );

        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Find first by column
     */
    public static function firstWhere(string $column, $value): ?static
    {
        $db = Database::getInstance();
        $row = $db->fetch(
            'SELECT * FROM `' . static::$table . '` WHERE `' . $column . '` = ? LIMIT 1',
            [$value]
        );

        return $row ? new static($row) : null;
    }

    /**
     * Count records
     */
    public static function count(string $where = '', array $params = []): int
    {
        $db = Database::getInstance();
        $sql = 'SELECT COUNT(*) FROM `' . static::$table . '`';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        return (int) $db->fetchColumn($sql, $params);
    }

    /**
     * Create new model and save
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }
}
