<?php
declare(strict_types=1);

use App\Models\AttendanceSchedule;

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <div>
    <p class="subtitle mb-0">Atur shift kerja dan toleransi keterlambatan.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="<?= url('/attendance'); ?>" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Kembali ke Absensi
    </a>
    <a href="<?= url('/attendance/schedules/create'); ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Tambah Jadwal
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header card-toolbar">
    <h2 class="card-title"><i class="bi bi-calendar2-week me-2 text-primary"></i>Daftar Jadwal Absensi</h2>
    <span class="badge badge-soft"><?= count($schedules); ?> jadwal</span>
  </div>
  <div class="card-body p-0">
    <?php if ($schedules !== []): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Nama Jadwal</th>
              <th>Hari</th>
              <th>Jam Kerja</th>
              <th class="text-center">Toleransi</th>
              <th>Lokasi</th>
              <th>Status</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($schedules as $row): ?>
              <?php $rowDays = array_map('intval', explode(',', (string) $row['days_of_week'])); ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= e($row['name']); ?></div>
                  <?php if (in_array((int) date('N'), $rowDays, true)): ?>
                    <span class="badge text-bg-primary mt-1">Berlaku hari ini</span>
                  <?php endif; ?>
                </td>
                <td class="small"><?= AttendanceSchedule::daysLabel($row['days_of_week']); ?></td>
                <td class="small text-nowrap">
                  <?= e(substr((string) $row['start_time'], 0, 5)); ?> &ndash;
                  <?= e(substr((string) $row['end_time'], 0, 5)); ?>
                </td>
                <td class="text-center small"><?= (int) $row['late_tolerance']; ?> menit</td>
                <td class="small text-secondary"><?= e($row['location'] ?: '-'); ?></td>
                <td>
                  <span class="badge <?= (int) $row['is_active'] === 1 ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                    <?= (int) $row['is_active'] === 1 ? 'Aktif' : 'Nonaktif'; ?>
                  </span>
                </td>
                <td>
                  <div class="table-actions">
                    <a href="<?= url('/attendance/schedules/' . (int) $row['id'] . '/edit'); ?>"
                       class="btn btn-sm btn-outline-primary btn-icon" aria-label="Edit jadwal">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="<?= url('/attendance/schedules/' . (int) $row['id'] . '/delete'); ?>"
                          onsubmit="return confirm('Hapus jadwal ini? Riwayat absensi tetap tersimpan.');">
                      <?= csrf_field(); ?>
                      <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Hapus jadwal">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state py-5">
        <i class="bi bi-calendar2-week"></i>
        <h6>Belum ada jadwal absensi</h6>
        <p>Tambahkan jadwal agar karyawan dapat melakukan absen masuk.</p>
        <a href="<?= url('/attendance/schedules/create'); ?>" class="btn btn-primary btn-sm mt-3">
          <i class="bi bi-plus-lg me-1"></i>Tambah Jadwal
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
