<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Data karyawan aktif dan nonaktif perusahaan.</p>
  <?php if (is_admin()): ?>
    <a href="<?= url('/employees/create'); ?>" class="btn btn-primary">
      <i class="bi bi-person-plus me-1"></i>Tambah Karyawan
    </a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header card-toolbar">
    <h2 class="card-title"><i class="bi bi-person-vcard me-2 text-primary"></i>Daftar Karyawan</h2>
    <span class="badge badge-soft"><?= count($employees); ?> karyawan</span>
  </div>
  <div class="card-body p-0">
    <?php if ($employees !== []): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Karyawan</th>
              <th>Jabatan</th>
              <th>Departemen</th>
              <th>Kontak</th>
              <th>Status</th>
              <?php if (is_admin()): ?><th class="text-end">Aksi</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($employees as $row): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="avatar-initials"><?= e(initials($row['fullname'])); ?></span>
                    <div>
                      <div class="fw-semibold"><?= e($row['fullname']); ?></div>
                      <div class="small text-secondary">
                        <?= e($row['employee_code'] ?: 'Tanpa kode'); ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td><?= e($row['designation'] ?: '-'); ?></td>
                <td>
                  <?php if (!empty($row['department_name'])): ?>
                    <span class="badge badge-soft"><?= e($row['department_name']); ?></span>
                  <?php else: ?>
                    <span class="text-secondary">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="small"><?= e($row['email'] ?: '-'); ?></div>
                  <div class="small text-secondary"><?= e($row['phone'] ?: '-'); ?></div>
                </td>
                <td>
                  <span class="badge <?= $row['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                    <?= $row['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                  </span>
                </td>
                <?php if (is_admin()): ?>
                  <td>
                    <div class="table-actions">
                      <a href="<?= url('/employees/' . (int) $row['id'] . '/edit'); ?>"
                         class="btn btn-sm btn-outline-primary btn-icon" aria-label="Edit karyawan">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <form method="post" action="<?= url('/employees/' . (int) $row['id'] . '/delete'); ?>"
                            onsubmit="return confirm('Hapus data karyawan ini?');">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Hapus karyawan">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-person-vcard"></i>
        <h6>Belum ada data karyawan</h6>
        <p>Tambahkan karyawan untuk melengkapi data organisasi.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
