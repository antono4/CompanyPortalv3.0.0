<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Daily attendance entry for a user.
 */
final class AttendanceRecord extends Model
{
    protected string $table = 'attendance_records';
    protected array $fillable = [
        'user_id', 'schedule_id', 'attendance_date',
        'check_in', 'check_out', 'status', 'note',
    ];

    public function findForUserDate(int $userId, string $date): ?array
    {
        $row = Database::query(
            'SELECT * FROM attendance_records WHERE user_id = ? AND attendance_date = ? LIMIT 1',
            [$userId, $date]
        )->fetch();

        return $row ?: null;
    }

    /** Today's attendance feed joined with user data, most recent activity first. */
    public function todayFeed(int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));

        return Database::query(
            "SELECT r.*, u.fullname, u.photo_id,
                    s.name AS schedule_name, s.start_time, s.end_time
               FROM attendance_records r
               INNER JOIN users u ON u.id = r.user_id
               LEFT JOIN attendance_schedules s ON s.id = r.schedule_id
              WHERE r.attendance_date = CURDATE()
                AND r.check_in IS NOT NULL
              ORDER BY r.updated_at DESC, r.id DESC
              LIMIT {$limit}"
        )->fetchAll();
    }

    /** Personal attendance history, newest first. */
    public function historyForUser(int $userId, int $limit = 8): array
    {
        $limit = max(1, min(60, $limit));

        return Database::query(
            "SELECT r.*, s.name AS schedule_name, s.start_time, s.end_time
               FROM attendance_records r
               LEFT JOIN attendance_schedules s ON s.id = r.schedule_id
              WHERE r.user_id = ?
              ORDER BY r.attendance_date DESC, r.id DESC
              LIMIT {$limit}",
            [$userId]
        )->fetchAll();
    }

    /**
     * Today's counters for the realtime dashboard.
     *
     * @return array{hadir:int,terlambat:int,izin:int,sakit:int,belum:int,total_user:int}
     */
    public function todaySummary(): array
    {
        $row = Database::query(
            "SELECT
                SUM(CASE WHEN check_in IS NOT NULL THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) AS sakit
               FROM attendance_records
              WHERE attendance_date = CURDATE()"
        )->fetch() ?: [];

        $total = (int) Database::query(
            "SELECT COUNT(*) FROM users WHERE status = 'active'"
        )->fetchColumn();

        $hadir = (int) ($row['hadir'] ?? 0);

        return [
            'hadir'     => $hadir,
            'terlambat' => (int) ($row['terlambat'] ?? 0),
            'izin'      => (int) ($row['izin'] ?? 0),
            'sakit'     => (int) ($row['sakit'] ?? 0),
            'belum'     => max(0, $total - $hadir),
            'total_user' => $total,
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'late'       => 'Terlambat',
            'permission' => 'Izin',
            'sick'       => 'Sakit',
            'present'    => 'Tepat Waktu',
            default      => 'Belum Absen',
        };
    }

    public static function statusBadge(?string $status): string
    {
        return match ($status) {
            'late'       => 'text-bg-warning',
            'permission' => 'text-bg-info',
            'sick'       => 'text-bg-secondary',
            'present'    => 'text-bg-success',
            default      => 'text-bg-light border',
        };
    }
}
