<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Upload extends Model
{
    protected string $table = 'uploads';
    protected array $fillable = [
        'title', 'description', 'filename', 'original_name',
        'media_id', 'filesize', 'filetype', 'uploaded_by',
    ];

    public function withUploader(): array
    {
        return Database::query(
            'SELECT f.*, u.fullname AS uploader, u.username
               FROM uploads f
               LEFT JOIN users u ON u.id = f.uploaded_by
              ORDER BY f.created_at DESC'
        )->fetchAll();
    }

    public function findWithUploader(int $id): ?array
    {
        $row = Database::query(
            'SELECT f.*, u.fullname AS uploader, u.username
               FROM uploads f
               LEFT JOIN users u ON u.id = f.uploaded_by
              WHERE f.id = ? LIMIT 1',
            [$id]
        );
        $row = $row->fetch();
        return $row ?: null;
    }

    public function totalSize(): int
    {
        return (int) $this->db()->query('SELECT COALESCE(SUM(filesize), 0) FROM uploads')->fetchColumn();
    }
}
