<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Employee extends Model
{
    protected string $table = 'employees';
    protected array $fillable = [
        'employee_code', 'fullname', 'email', 'phone', 'designation',
        'department_id', 'joining_date', 'address', 'status',
    ];

    public function withDepartment(): array
    {
        return Database::query(
            'SELECT e.*, d.department_name
               FROM employees e
               LEFT JOIN departments d ON d.id = e.department_id
              ORDER BY e.fullname ASC'
        )->fetchAll();
    }

    public function findWithDepartment(int $id): ?array
    {
        $row = Database::query(
            'SELECT e.*, d.department_name
               FROM employees e
               LEFT JOIN departments d ON d.id = e.department_id
              WHERE e.id = ? LIMIT 1',
            [$id]
        )->fetch();
        return $row ?: null;
    }

    public function countByStatus(string $status): int
    {
        return $this->count('status = ?', [$status]);
    }
}
