<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Department extends Model
{
    protected string $table = 'departments';
    protected array $fillable = ['department_name', 'description', 'status'];

    public function withCounts(): array
    {
        return Database::query(
            "SELECT d.*, COUNT(e.id) AS employee_count
               FROM departments d
               LEFT JOIN employees e ON e.department_id = d.id AND e.status = 'active'
              GROUP BY d.id
              ORDER BY d.department_name ASC"
        )->fetchAll();
    }

    public function options(): array
    {
        return Database::query(
            "SELECT id, department_name FROM departments WHERE status = 'active' ORDER BY department_name ASC"
        )->fetchAll();
    }

    public function activeCount(): int
    {
        return $this->count('status = ?', ['active']);
    }
}
