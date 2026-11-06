<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Struktur organisasi dan unit kerja perusahaan.</p>
  <?php if (is_admin()): ?>
    <a href="<?= url('/departments/create'); ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Tambah Departemen
    </a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header card-toolbar">
    <h2 class="card-title"><i class="bi bi-diagram-3 me-2 text-primary"></i>Daftar Departemen</h2>
    <span class="badge badge-soft"><?= count($departments); ?> departemen</span>
  </div>
  <div class="card-body p-0">
    <?php if ($departments !== []): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Nama Departemen</th>
              <th>Deskripsi</th>
              <th class="text-center">Karyawan</th>
              <th>Status</th>
              <?php if (is_admin()): ?><th class="text-end">Aksi</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($departments as $row): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="stat-icon bg-primary-subtle text-primary" style="width:34px;height:34px;font-size:.95rem;">
                      <i class="bi bi-building"></i>
                    </span>
                    <span class="fw-semibold"><?= e($row['department_name']); ?></span>
                  </div>
                </td>
                <td class="text-secondary small">
                  <?= e(mb_strimwidth((string) ($row['description'] ?? ''), 0, 90, '...')); ?>
                </td>
                <td class="text-center">
                  <span class="badge badge-soft"><?= (int) $row['employee_count']; ?></span>
                </td>
                <td>
                  <span class="badge <?= $row['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                    <?= $row['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                  </span>
                </td>
                <?php if (is_admin()): ?>
                  <td>
                    <div class="table-actions">
                      <a href="<?= url('/departments/' . (int) $row['id'] . '/edit'); ?>"
                         class="btn btn-sm btn-outline-primary btn-icon" aria-label="Edit departemen">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <form method="post" action="<?= url('/departments/' . (int) $row['id'] . '/delete'); ?>"
                            onsubmit="return confirm('Hapus departemen ini?');">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Hapus departemen">
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
        <i class="bi bi-diagram-3"></i>
        <h6>Belum ada departemen</h6>
        <p>Tambahkan departemen untuk mengelompokkan karyawan.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
