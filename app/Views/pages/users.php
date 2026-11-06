<?php
declare(strict_types=1);

use App\Core\Auth;

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Kelola akun pengguna dan hak akses portal.</p>
  <a href="<?= url('/users/create'); ?>" class="btn btn-primary">
    <i class="bi bi-person-plus me-1"></i>Tambah User
  </a>
</div>

<div class="card">
  <div class="card-header card-toolbar">
    <h2 class="card-title"><i class="bi bi-people me-2 text-primary"></i>Daftar User</h2>
    <span class="badge badge-soft"><?= count($users); ?> user</span>
  </div>
  <div class="card-body p-0">
    <?php if ($users !== []): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Username</th>
              <th>Role</th>
              <th>Departemen</th>
              <th>Status</th>
              <th>Login Terakhir</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $row): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <?= avatar_html($row); ?>
                    <div>
                      <div class="fw-semibold"><?= e($row['fullname']); ?></div>
                      <div class="small text-secondary"><?= e($row['email']); ?></div>
                    </div>
                  </div>
                </td>
                <td><code><?= e($row['username']); ?></code></td>
                <td>
                  <span class="badge <?= $row['role'] === 'admin' ? 'text-bg-danger' : 'text-bg-primary'; ?>">
                    <?= $row['role'] === 'admin' ? 'Administrator' : 'User'; ?>
                  </span>
                </td>
                <td><?= e($row['department_name'] ?? '-'); ?></td>
                <td>
                  <span class="badge <?= $row['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                    <?= $row['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                  </span>
                </td>
                <td class="text-nowrap small text-secondary">
                  <?= e($row['last_login'] ? fmt_datetime($row['last_login']) : 'Belum pernah'); ?>
                </td>
                <td>
                  <div class="table-actions">
                    <a href="<?= url('/users/' . (int) $row['id'] . '/edit'); ?>"
                       class="btn btn-sm btn-outline-primary btn-icon" aria-label="Edit user">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <?php if ((int) $row['id'] !== Auth::id()): ?>
                      <form method="post" action="<?= url('/users/' . (int) $row['id'] . '/delete'); ?>"
                            onsubmit="return confirm('Hapus user ini?');">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Hapus user">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-people"></i>
        <h6>Belum ada user</h6>
        <p>Tambahkan user pertama untuk mulai berkolaborasi.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
