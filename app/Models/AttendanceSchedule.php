<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Work shift / attendance schedule.
 */
final class AttendanceSchedule extends Model
{
    protected string $table = 'attendance_schedules';
    protected array $fillable = [
        'name', 'days_of_week', 'start_time', 'end_time',
        'late_tolerance', 'location', 'is_active',
    ];

    /** ISO-8601 weekday numbers (1 = Monday, 7 = Sunday). */
    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /** All schedules, active first, ordered by start time. */
    public function ordered(): array
    {
        return Database::query(
            'SELECT * FROM attendance_schedules
              ORDER BY is_active DESC, start_time ASC, name ASC'
        )->fetchAll();
    }

    /** Active schedules that apply to a given weekday. */
    public function forDay(int $dayOfWeek): array
    {
        return Database::query(
            'SELECT * FROM attendance_schedules
              WHERE is_active = 1 AND FIND_IN_SET(?, days_of_week)
              ORDER BY start_time ASC, name ASC',
            [$dayOfWeek]
        )->fetchAll();
    }

    /** Human label for a comma separated list of weekdays. */
    public static function daysLabel(?string $days): string
    {
        $numbers = array_values(array_unique(array_filter(
            array_map('intval', explode(',', (string) $days)),
            static fn (int $day): bool => $day >= 1 && $day <= 7
        )));
        sort($numbers);

        if ($numbers === []) {
            return '-';
        }
        if (count($numbers) === 1) {
            return self::DAYS[$numbers[0]];
        }

        $first = $numbers[0];
        $last  = $numbers[count($numbers) - 1];
        if (($last - $first + 1) === count($numbers) && count($numbers) >= 3) {
            return self::DAYS[$first] . ' &ndash; ' . self::DAYS[$last];
        }

        return implode(', ', array_map(
            static fn (int $day): string => self::DAYS[$day],
            $numbers
        ));
    }

    /**
     * Realtime state of a schedule relative to the current (or given) time.
     *
     * @return array{state:string,label:string,progress:int,message:string,late_after:string,start:string,end:string}
     */
    public static function stateFor(array $schedule, ?string $now = null): array
    {
        $now   = $now ?? date('H:i:s');
        $start = substr((string) $schedule['start_time'], 0, 5);
        $end   = substr((string) $schedule['end_time'], 0, 5);
        $tolerance = max(0, (int) ($schedule['late_tolerance'] ?? 0));
        $lateAfter = self::addMinutes($start, $tolerance);

        $current  = self::minutes($now);
        $startMin = self::minutes($start);
        $endMin   = self::minutes($end);
        $total    = max(1, $endMin - $startMin);

        if ($current < $startMin) {
            $remaining = $startMin - $current;
            return [
                'state'      => 'upcoming',
                'label'      => 'Belum dimulai',
                'progress'   => 0,
                'message'    => 'Dimulai dalam ' . self::duration($remaining),
                'late_after' => $lateAfter,
                'start'      => $start,
                'end'        => $end,
            ];
        }

        if ($current >= $endMin) {
            return [
                'state'      => 'ended',
                'label'      => 'Selesai',
                'progress'   => 100,
                'message'    => 'Shift telah selesai',
                'late_after' => $lateAfter,
                'start'      => $start,
                'end'        => $end,
            ];
        }

        $done = min($total, $current - $startMin);

        return [
            'state'      => 'active',
            'label'      => 'Berlangsung',
            'progress'   => (int) round($done / $total * 100),
            'message'    => 'Sisa ' . self::duration($endMin - $current),
            'late_after' => $lateAfter,
            'start'      => $start,
            'end'        => $end,
        ];
    }

    /** True when the given time is past the schedule's late tolerance. */
    public static function isLate(array $schedule, ?string $now = null): bool
    {
        $state = self::stateFor($schedule, $now);
        return self::minutes($now ?? date('H:i:s')) > self::minutes($state['late_after']);
    }

    /** Human duration from a number of minutes. */
    public static function duration(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $hours   = intdiv($minutes, 60);
        $rest    = $minutes % 60;

        if ($hours > 0 && $rest > 0) {
            return $hours . ' jam ' . $rest . ' menit';
        }
        if ($hours > 0) {
            return $hours . ' jam';
        }
        return $rest . ' menit';
    }

    private static function minutes(string $time): int
    {
        [$hour, $minute] = array_pad(explode(':', $time, 3), 3, '0');
        return ((int) $hour * 60) + (int) $minute;
    }

    private static function addMinutes(string $time, int $minutes): string
    {
        $total = (self::minutes($time) + $minutes) % (24 * 60);
        return sprintf('%02d:%02d', intdiv($total, 60), $total % 60);
    }
}
