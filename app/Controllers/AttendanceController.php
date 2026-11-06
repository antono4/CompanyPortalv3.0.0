<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Input;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSchedule;

/**
 * Realtime attendance schedule, check-in / check-out and shift management.
 */
final class AttendanceController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId      = (int) Auth::id();
        $now         = date('H:i:s');
        $dayOfWeek   = (int) date('N');
        $scheduleMdl = new AttendanceSchedule();
        $recordMdl   = new AttendanceRecord();

        $schedules = $scheduleMdl->forDay($dayOfWeek);
        $record    = $recordMdl->findForUserDate($userId, date('Y-m-d'));

        $this->view('pages/attendance', [
            'title'         => 'Absensi - ' . \App\Config\App::NAME,
            'schedules'     => $schedules,
            'weekSchedules' => array_values(array_filter(
                $scheduleMdl->ordered(),
                static fn (array $row): bool => (int) $row['is_active'] === 1
            )),
            'primary'       => $this->pickSchedule($schedules, $now),
            'record'        => $record,
            'summary'       => $recordMdl->todaySummary(),
            'feed'          => $recordMdl->todayFeed(8),
            'history'       => $recordMdl->historyForUser($userId, 8),
            'serverNow'     => $now,
            'serverTs'      => time(),
            'dayLabel'      => AttendanceSchedule::DAYS[$dayOfWeek] ?? '',
            'dateLabel'     => date('d M Y'),
            'historyStatus' => [AttendanceRecord::class, 'statusLabel'],
        ]);
    }

    /** Realtime payload consumed by the attendance page (polled every 10 seconds). */
    public function status(): void
    {
        $this->requireAuth();

        $userId    = (int) Auth::id();
        $now       = date('H:i:s');
        $dayOfWeek = (int) date('N');

        $scheduleMdl = new AttendanceSchedule();
        $recordMdl   = new AttendanceRecord();

        $schedules = array_map(
            static function (array $schedule) use ($now): array {
                $state = AttendanceSchedule::stateFor($schedule, $now);

                return [
                    'id'          => (int) $schedule['id'],
                    'name'        => $schedule['name'],
                    'start'       => $state['start'],
                    'end'         => $state['end'],
                    'late_after'  => $state['late_after'],
                    'tolerance'   => (int) $schedule['late_tolerance'],
                    'location'    => $schedule['location'],
                    'state'       => $state['state'],
                    'state_label' => $state['label'],
                    'progress'    => $state['progress'],
                    'message'     => $state['message'],
                ];
            },
            $scheduleMdl->forDay($dayOfWeek)
        );

        $record = $recordMdl->findForUserDate($userId, date('Y-m-d'));

        $feed = array_map(static function (array $row): array {
            $user = [
                'id'       => (int) $row['user_id'],
                'fullname' => $row['fullname'],
                'photo_id' => $row['photo_id'],
            ];

            return [
                'user_id'      => (int) $row['user_id'],
                'name'         => $row['fullname'],
                'avatar'       => avatar_html($user, 'avatar-sm'),
                'schedule'     => $row['schedule_name'] ?? '-',
                'check_in'     => $row['check_in'] ? substr((string) $row['check_in'], 0, 5) : null,
                'check_out'    => $row['check_out'] ? substr((string) $row['check_out'], 0, 5) : null,
                'status'       => $row['status'],
                'status_label' => AttendanceRecord::statusLabel($row['status']),
                'badge'        => AttendanceRecord::statusBadge($row['status']),
            ];
        }, $recordMdl->todayFeed(8));

        // Release the session lock so polling does not block other requests.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $this->json([
            'timestamp'  => time(),
            'time'       => $now,
            'date_label' => date('d M Y'),
            'day_label'  => AttendanceSchedule::DAYS[$dayOfWeek] ?? '',
            'schedules'  => $schedules,
            'record'     => $record === null ? null : [
                'check_in'     => $record['check_in'] ? substr((string) $record['check_in'], 0, 5) : null,
                'check_out'    => $record['check_out'] ? substr((string) $record['check_out'], 0, 5) : null,
                'status'       => $record['status'],
                'status_label' => AttendanceRecord::statusLabel($record['status']),
                'badge'        => AttendanceRecord::statusBadge($record['status']),
            ],
            'summary'    => $recordMdl->todaySummary(),
            'feed'       => $feed,
        ]);
    }

    public function checkIn(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId    = (int) Auth::id();
        $now       = date('H:i:s');
        $dayOfWeek = (int) date('N');
        $today     = date('Y-m-d');

        $scheduleMdl = new AttendanceSchedule();
        $recordMdl   = new AttendanceRecord();

        $schedules = $scheduleMdl->forDay($dayOfWeek);
        $schedule  = $this->pickSchedule($schedules, $now);
        if ($schedule === null) {
            Session::flash('error', 'Tidak ada jadwal absensi untuk hari ini.');
            $this->redirect('/attendance');
        }

        $record = $recordMdl->findForUserDate($userId, $today);
        if ($record !== null && !empty($record['check_in'])) {
            Session::flash('error', 'Anda sudah melakukan absen masuk hari ini.');
            $this->redirect('/attendance');
        }

        $status = AttendanceSchedule::isLate($schedule, $now) ? 'late' : 'present';
        $data   = [
            'schedule_id' => (int) $schedule['id'],
            'check_in'    => $now,
            'status'      => $status,
        ];

        if ($record === null) {
            $id = $recordMdl->create($data + [
                'user_id'         => $userId,
                'attendance_date' => $today,
            ]);
        } else {
            $recordMdl->update((int) $record['id'], $data);
            $id = (int) $record['id'];
        }

        (new ActivityLog())->log($userId, 'Absen masuk #' . $id . ' pukul ' . substr($now, 0, 5));
        Session::flash(
            'success',
            $status === 'late'
                ? 'Absen masuk tercatat pukul ' . substr($now, 0, 5) . ' (terlambat).'
                : 'Absen masuk tercatat pukul ' . substr($now, 0, 5) . '. Selamat bekerja!'
        );
        $this->redirect('/attendance');
    }

    public function checkOut(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userId = (int) Auth::id();
        $now    = date('H:i:s');
        $record = (new AttendanceRecord())->findForUserDate($userId, date('Y-m-d'));

        if ($record === null || empty($record['check_in'])) {
            Session::flash('error', 'Anda belum melakukan absen masuk hari ini.');
            $this->redirect('/attendance');
        }
        if (!empty($record['check_out'])) {
            Session::flash('error', 'Anda sudah melakukan absen keluar hari ini.');
            $this->redirect('/attendance');
        }

        (new AttendanceRecord())->update((int) $record['id'], ['check_out' => $now]);
        (new ActivityLog())->log($userId, 'Absen keluar #' . (int) $record['id'] . ' pukul ' . substr($now, 0, 5));
        Session::flash('success', 'Absen keluar tercatat pukul ' . substr($now, 0, 5) . '. Terima kasih!');
        $this->redirect('/attendance');
    }

    /* ---------------- Schedule management (admin) ---------------- */

    public function schedules(): void
    {
        $this->requireAdmin();

        $this->view('pages/attendance_schedules', [
            'title'     => 'Jadwal Absensi - ' . \App\Config\App::NAME,
            'schedules' => (new AttendanceSchedule())->ordered(),
        ]);
    }

    public function scheduleCreate(): void
    {
        $this->requireAdmin();

        $this->view('pages/attendance_schedule_form', [
            'title'    => 'Tambah Jadwal Absensi - ' . \App\Config\App::NAME,
            'schedule' => null,
        ]);
    }

    public function scheduleStore(): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        [$errors, $data] = $this->schedulePayload();

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::set('_old_schedule_days', implode(',', $data['days']));
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/attendance/schedules/create');
        }

        $id = (new AttendanceSchedule())->create([
            'name'           => $data['name'],
            'days_of_week'   => implode(',', $data['days']),
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'late_tolerance' => $data['late_tolerance'],
            'location'       => $data['location'],
            'is_active'      => $data['is_active'],
        ]);

        Session::forget('_old_schedule_days');
        (new ActivityLog())->log(Auth::id(), 'Menambahkan jadwal absensi #' . $id);
        Session::flash('success', 'Jadwal absensi berhasil ditambahkan.');
        $this->redirect('/attendance/schedules');
    }

    public function scheduleEdit(string $id): void
    {
        $this->requireAdmin();

        $schedule = (new AttendanceSchedule())->find((int) $id);
        if ($schedule === null) {
            $this->abort(404);
        }

        $this->view('pages/attendance_schedule_form', [
            'title'    => 'Edit Jadwal Absensi - ' . \App\Config\App::NAME,
            'schedule' => $schedule,
        ]);
    }

    public function scheduleUpdate(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $scheduleId = (int) $id;
        $model      = new AttendanceSchedule();
        if ($model->find($scheduleId) === null) {
            $this->abort(404);
        }

        [$errors, $data] = $this->schedulePayload();

        if ($errors !== []) {
            Session::setOld($_POST);
            Session::set('_old_schedule_days', implode(',', $data['days']));
            Session::flash('error', implode(' ', $errors));
            $this->redirect('/attendance/schedules/' . $scheduleId . '/edit');
        }

        $model->update($scheduleId, [
            'name'           => $data['name'],
            'days_of_week'   => implode(',', $data['days']),
            'start_time'     => $data['start_time'],
            'end_time'       => $data['end_time'],
            'late_tolerance' => $data['late_tolerance'],
            'location'       => $data['location'],
            'is_active'      => $data['is_active'],
        ]);

        Session::forget('_old_schedule_days');
        (new ActivityLog())->log(Auth::id(), 'Memperbarui jadwal absensi #' . $scheduleId);
        Session::flash('success', 'Jadwal absensi berhasil diperbarui.');
        $this->redirect('/attendance/schedules');
    }

    public function scheduleDestroy(string $id): void
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $scheduleId = (int) $id;
        $model      = new AttendanceSchedule();
        if ($model->find($scheduleId) === null) {
            $this->abort(404);
        }

        $model->delete($scheduleId);
        (new ActivityLog())->log(Auth::id(), 'Menghapus jadwal absensi #' . $scheduleId);
        Session::flash('success', 'Jadwal absensi berhasil dihapus.');
        $this->redirect('/attendance/schedules');
    }

    /* ---------------- Helpers ---------------- */

    /**
     * Prefer the shift currently running, then the next upcoming one.
     *
     * @param array<int, array<string, mixed>> $schedules
     */
    private function pickSchedule(array $schedules, string $now): ?array
    {
        if ($schedules === []) {
            return null;
        }

        foreach ($schedules as $schedule) {
            if (AttendanceSchedule::stateFor($schedule, $now)['state'] === 'active') {
                return $schedule;
            }
        }
        foreach ($schedules as $schedule) {
            if (AttendanceSchedule::stateFor($schedule, $now)['state'] === 'upcoming') {
                return $schedule;
            }
        }

        return $schedules[0];
    }

    /**
     * Validate schedule input.
     *
     * @return array{0:array<string,string>,1:array<string,mixed>}
     */
    private function schedulePayload(): array
    {
        $errors = $this->validate([
            'name'     => 'required|min:3|max:100',
            'location' => 'max:120',
        ]);

        $rawDays = Input::post('days');
        $days    = [];
        if (is_array($rawDays)) {
            foreach ($rawDays as $day) {
                $day = (int) $day;
                if ($day >= 1 && $day <= 7) {
                    $days[$day] = $day;
                }
            }
            ksort($days);
        }
        $days = array_values($days);
        if ($days === []) {
            $errors['days'] = 'Pilih minimal satu hari kerja.';
        }

        $start = Input::str('start_time');
        $end   = Input::str('end_time');
        $timePattern = '/^([01]\d|2[0-3]):[0-5]\d$/';

        if (preg_match($timePattern, $start) !== 1) {
            $errors['start_time'] = 'Jam mulai tidak valid.';
        }
        if (preg_match($timePattern, $end) !== 1) {
            $errors['end_time'] = 'Jam selesai tidak valid.';
        }
        if (!isset($errors['start_time'], $errors['end_time']) && $this->timeMinutes($end) <= $this->timeMinutes($start)) {
            $errors['end_time'] = 'Jam selesai harus lebih besar dari jam mulai.';
        }

        $tolerance = Input::int('late_tolerance', 15);
        if ($tolerance < 0 || $tolerance > 180) {
            $errors['late_tolerance'] = 'Toleransi keterlambatan harus antara 0 dan 180 menit.';
        }

        return [$errors, [
            'name'           => Input::str('name'),
            'days'           => $days,
            'start_time'     => $start,
            'end_time'       => $end,
            'late_tolerance' => $tolerance,
            'location'       => Input::str('location'),
            'is_active'      => Input::bool('is_active') ? 1 : 0,
        ]];
    }

    private function timeMinutes(string $time): int
    {
        [$hour, $minute] = array_pad(explode(':', $time, 2), 2, '0');
        return ((int) $hour * 60) + (int) $minute;
    }
}
