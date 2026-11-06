<?php
declare(strict_types=1);

use App\Core\Session;
use App\Models\AttendanceSchedule;

require __DIR__ . '/../layouts/admin_header.php';

$isEdit = $schedule !== null;
$action = $isEdit
    ? url('/attendance/schedules/' . (int) $schedule['id'])
    : url('/attendance/schedules');

$oldDays = Session::get('_old_schedule_days');
if ($oldDays !== null) {
    $selectedDays = array_map('intval', explode(',', (string) $oldDays));
    Session::forget('_old_schedule_days');
} elseif ($isEdit) {
    $selectedDays = array_map('intval', explode(',', (string) $schedule['days_of_week']));
} else {
    $selectedDays = [1, 2, 3, 4, 5];
}

$hasOld  = Session::has('_old');
$isActive = $hasOld
    ? Session::old('is_active') === '1'
    : ($isEdit ? (int) $schedule['is_active'] === 1 : true);
?>

<div class="page-heading">
  <p class="subtitle mb-0"><?= $isEdit ? 'Perbarui shift dan toleransi absensi.' : 'Buat shift kerja baru untuk karyawan.'; ?></p>
  <a href="<?= url('/attendance/schedules'); ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="card form-card">
  <div class="card-header">
    <h2 class="card-title">
      <i class="bi bi-calendar-plus me-2 text-primary"></i><?= $isEdit ? 'Edit Jadwal Absensi' : 'Tambah Jadwal Absensi'; ?>
    </h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= $action; ?>">
      <?= csrf_field(); ?>

      <div class="mb-3">
        <label for="name" class="form-label required">Nama Jadwal</label>
        <input type="text" class="form-control" id="name" name="name" required minlength="3" maxlength="100"
               value="<?= old('name', $schedule['name'] ?? ''); ?>" placeholder="Contoh: Shift Reguler">
      </div>

      <div class="mb-3">
        <span class="form-label required d-block">Hari Kerja</span>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach (AttendanceSchedule::DAYS as $number => $dayName): ?>
            <div class="form-check form-check-inline att-day-check">
              <input class="form-check-input" type="checkbox" id="day-<?= $number; ?>" name="days[]"
                     value="<?= $number; ?>" <?= in_array($number, $selectedDays, true) ? 'checked' : ''; ?>>
              <label class="form-check-label" for="day-<?= $number; ?>"><?= e($dayName); ?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="form-text">Pilih minimal satu hari. Jadwal hanya muncul pada hari yang dipilih.</div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label for="start_time" class="form-label required">Jam Mulai</label>
          <input type="time" class="form-control" id="start_time" name="start_time" required
                 value="<?= old('start_time', $isEdit ? substr((string) $schedule['start_time'], 0, 5) : '08:00'); ?>">
        </div>
        <div class="col-md-6">
          <label for="end_time" class="form-label required">Jam Selesai</label>
          <input type="time" class="form-control" id="end_time" name="end_time" required
                 value="<?= old('end_time', $isEdit ? substr((string) $schedule['end_time'], 0, 5) : '17:00'); ?>">
        </div>
        <div class="col-md-6">
          <label for="late_tolerance" class="form-label">Toleransi Keterlambatan (menit)</label>
          <input type="number" class="form-control" id="late_tolerance" name="late_tolerance"
                 min="0" max="180" step="1"
                 value="<?= old('late_tolerance', (string) ($schedule['late_tolerance'] ?? 15)); ?>">
          <div class="form-text">Absen setelah jam mulai + toleransi dihitung terlambat.</div>
        </div>
        <div class="col-md-6">
          <label for="location" class="form-label">Lokasi</label>
          <input type="text" class="form-control" id="location" name="location" maxlength="120"
                 value="<?= old('location', $schedule['location'] ?? ''); ?>" placeholder="Contoh: Kantor Pusat">
        </div>
      </div>

      <div class="form-check form-switch mt-4">
        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
               <?= $isActive ? 'checked' : ''; ?>>
        <label class="form-check-label" for="is_active">Jadwal aktif</label>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= url('/attendance/schedules'); ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Jadwal'; ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
