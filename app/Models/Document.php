<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Document extends Model
{
    protected string $table = 'documents';
    protected array $fillable = [
        'title', 'description', 'filename', 'original_name',
        'media_id', 'filesize', 'filetype', 'uploaded_by',
    ];

    public function withUploader(): array
    {
        return Database::query(
            'SELECT d.*, u.fullname AS uploader, u.username
               FROM documents d
               LEFT JOIN users u ON u.id = d.uploaded_by
              ORDER BY d.created_at DESC'
        )->fetchAll();
    }

    public function findWithUploader(int $id): ?array
    {
        $row = Database::query(
            'SELECT d.*, u.fullname AS uploader, u.username
               FROM documents d
               LEFT JOIN users u ON u.id = d.uploaded_by
              WHERE d.id = ? LIMIT 1',
            [$id]
        );
        $row = $row->fetch();
        return $row ?: null;
    }

    public function totalSize(): int
    {
        return (int) $this->db()->query('SELECT COALESCE(SUM(filesize), 0) FROM documents')->fetchColumn();
    }
}
