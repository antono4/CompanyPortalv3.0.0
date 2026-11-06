<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Announcement extends Model
{
    protected string $table = 'announcements';
    protected array $fillable = ['title', 'description', 'image_id', 'created_by'];

    public function withAuthor(): array
    {
        return Database::query(
            'SELECT a.*, u.fullname AS author
               FROM announcements a
               LEFT JOIN users u ON u.id = a.created_by
              ORDER BY a.created_at DESC'
        )->fetchAll();
    }

    public function findWithAuthor(int $id): ?array
    {
        $row = Database::query(
            'SELECT a.*, u.fullname AS author
               FROM announcements a
               LEFT JOIN users u ON u.id = a.created_by
              WHERE a.id = ? LIMIT 1',
            [$id]
        )->fetch();
        return $row ?: null;
    }

    public function latest(int $limit = 5): array
    {
        $limit = max(1, min(50, $limit));
        return Database::query(
            "SELECT a.*, u.fullname AS author
               FROM announcements a
               LEFT JOIN users u ON u.id = a.created_by
              ORDER BY a.created_at DESC
              LIMIT {$limit}"
        )->fetchAll();
    }

    /** Latest announcements excluding one id, for the "Berita Lainnya" sidebar. */
    public function latestExcept(int $excludeId, int $limit = 3): array
    {
        $limit = max(1, min(20, $limit));
        return Database::query(
            "SELECT a.*, u.fullname AS author
               FROM announcements a
               LEFT JOIN users u ON u.id = a.created_by
              WHERE a.id <> ?
              ORDER BY a.created_at DESC
              LIMIT {$limit}",
            [$excludeId]
        )->fetchAll();
    }
}
