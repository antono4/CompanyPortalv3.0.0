<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Minimal active-record style base model.
 */
abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    /** Columns that may be mass-assigned. */
    protected array $fillable = [];

    protected function db(): PDO
    {
        return Database::pdo();
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        $orderBy = $this->safeOrder($orderBy);
        return $this->db()->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db()->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBy(string $column, mixed $value): ?array
    {
        $column = $this->safeColumn($column);
        $stmt = $this->db()->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $column  = $this->safeColumn($column);
        $orderBy = $this->safeOrder($orderBy);
        $stmt = $this->db()->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = ? ORDER BY {$orderBy}");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $data = $this->filter($data);
        if ($data === []) {
            return 0;
        }
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $this->table,
            implode('`, `', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filter($data);
        if ($data === []) {
            return false;
        }
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "`{$column}` = ?";
        }
        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = ?',
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );
        $params = array_values($data);
        $params[] = $id;

        return $this->db()->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db()->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        return $stmt->execute([$id]);
    }

    public function exists(string $column, mixed $value, ?int $exceptId = null): bool
    {
        $column = $this->safeColumn($column);
        $sql    = "SELECT COUNT(*) FROM `{$this->table}` WHERE `{$column}` = ?";
        $params = [$value];

        if ($exceptId !== null) {
            $sql .= " AND `{$this->primaryKey}` <> ?";
            $params[] = $exceptId;
        }

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function paginate(int $page = 1, int $perPage = 15, string $orderBy = 'id DESC'): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset  = ($page - 1) * $perPage;
        $total   = $this->count();
        $orderBy = $this->safeOrder($orderBy);

        $stmt = $this->db()->prepare("SELECT * FROM `{$this->table}` ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute();

        return [
            'data'     => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / $perPage),
        ];
    }

    protected function filter(array $data): array
    {
        if ($this->fillable === []) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    private function safeColumn(string $column): string
    {
        if (preg_match('/^[a-zA-Z0-9_]+$/', $column) !== 1) {
            throw new \InvalidArgumentException('Invalid column name.');
        }
        return $column;
    }

    private function safeOrder(string $orderBy): string
    {
        if (preg_match('/^[a-zA-Z0-9_]+( (ASC|DESC))?(, [a-zA-Z0-9_]+( (ASC|DESC))?)*$/i', $orderBy) !== 1) {
            return 'id DESC';
        }
        return $orderBy;
    }
}
