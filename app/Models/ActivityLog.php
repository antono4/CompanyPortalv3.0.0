<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class ActivityLog extends Model
{
    protected string $table = 'activity_logs';
    protected array $fillable = ['user_id', 'activity', 'ip_address'];

    public function log(?int $userId, string $activity, ?string $ip = null): void
    {
        $this->create([
            'user_id'    => $userId,
            'activity'   => $activity,
            'ip_address' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'),
        ]);
    }

    public function withUser(int $limit = 200): array
    {
        $limit = max(1, min(500, $limit));
        return Database::query(
            "SELECT l.*, u.fullname AS user_name
               FROM activity_logs l
               LEFT JOIN users u ON u.id = l.user_id
              ORDER BY l.created_at DESC
              LIMIT {$limit}"
        )->fetchAll();
    }

    public function recent(int $limit = 6): array
    {
        return $this->withUser($limit);
    }
}
