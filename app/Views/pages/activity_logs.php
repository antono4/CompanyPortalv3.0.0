<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Jejak aktivitas pengguna di dalam portal.</p>
  <form method="post" action="<?= url('/activity-logs/clear'); ?>"
        onsubmit="return confirm('Hapus seluruh log aktivitas? Tindakan ini tidak dapat dibatalkan.');">
    <?= csrf_field(); ?>
    <button type="submit" class="btn btn-outline-danger">
      <i class="bi bi-trash me-1"></i>Bersihkan Log
    </button>
  </form>
</div>

<div class="card">
  <div class="card-header card-toolbar">
    <h2 class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Log Aktivitas</h2>
    <span class="badge badge-soft"><?= (int) $total; ?> entri</span>
  </div>
  <div class="card-body p-0">
    <?php if ($logs !== []): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th class="text-nowrap">Waktu</th>
              <th>User</th>
              <th>Aktivitas</th>
              <th class="text-nowrap">IP Address</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $row): ?>
              <tr>
                <td class="small text-nowrap text-secondary"><?= e(fmt_datetime($row['created_at'])); ?></td>
                <td class="small fw-semibold"><?= e($row['user_name'] ?? 'Sistem'); ?></td>
                <td class="small"><?= e($row['activity']); ?></td>
                <td class="small text-secondary"><code><?= e($row['ip_address']); ?></code></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-clock-history"></i>
        <h6>Belum ada log</h6>
        <p>Aktivitas pengguna akan tercatat otomatis di sini.</p>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($pages > 1): ?>
    <div class="card-footer bg-white">
      <nav aria-label="Navigasi halaman log">
        <ul class="pagination pagination-sm mb-0 justify-content-center">
          <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
              <a class="page-link" href="<?= url('/activity-logs') . '?page=' . $i; ?>"><?= $i; ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
