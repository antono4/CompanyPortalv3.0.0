<?php
declare(strict_types=1);

use App\Core\Auth;

require __DIR__ . '/../layouts/admin_header.php';

$fileIcon = static function (?string $name): string {
    return match (strtolower(pathinfo((string) $name, PATHINFO_EXTENSION))) {
        'pdf' => 'bi-file-earmark-pdf text-danger',
        'doc', 'docx' => 'bi-file-earmark-word text-primary',
        'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel text-success',
        'ppt', 'pptx' => 'bi-file-earmark-ppt text-warning',
        'zip', 'rar' => 'bi-file-earmark-zip text-secondary',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'bi-file-earmark-image text-info',
        default => 'bi-file-earmark text-secondary',
    };
};
?>

<div class="page-heading">
  <p class="subtitle">Berbagi berkas internal antar karyawan.</p>
  <a href="<?= url('/uploads/create'); ?>" class="btn btn-primary">
    <i class="bi bi-cloud-arrow-up me-1"></i>Upload Berkas
  </a>
</div>

<div class="card">
  <div class="card-header card-toolbar">
    <h2 class="card-title"><i class="bi bi-cloud-arrow-up me-2 text-primary"></i>Berkas Terbagi</h2>
    <span class="badge badge-soft"><?= count($uploads); ?> berkas</span>
  </div>
  <div class="card-body p-0">
    <?php if ($uploads !== []): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Berkas</th>
              <th>Deskripsi</th>
              <th class="text-nowrap">Ukuran</th>
              <th>Pengunggah</th>
              <th class="text-nowrap">Tanggal</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($uploads as $row): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi <?= $fileIcon($row['original_name']); ?> fs-4"></i>
                    <div style="min-width: 0;">
                      <div class="fw-semibold text-truncate" style="max-width: 220px;"><?= e($row['title']); ?></div>
                      <div class="small text-secondary text-truncate" style="max-width: 220px;">
                        <?= e($row['original_name']); ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="text-secondary small">
                  <?= e(mb_strimwidth((string) ($row['description'] ?? ''), 0, 70, '...')); ?>
                </td>
                <td class="small text-nowrap"><?= e(human_size((int) $row['filesize'])); ?></td>
                <td class="small"><?= e($row['uploader'] ?? 'Unknown'); ?></td>
                <td class="small text-nowrap text-secondary"><?= e(fmt_date($row['created_at'])); ?></td>
                <td>
                  <div class="table-actions">
                    <a href="<?= url('/uploads/' . (int) $row['id'] . '/download'); ?>"
                       class="btn btn-sm btn-outline-success btn-icon" aria-label="Unduh berkas">
                      <i class="bi bi-download"></i>
                    </a>
                    <?php if (Auth::isAdmin() || (int) $row['uploaded_by'] === Auth::id()): ?>
                      <form method="post" action="<?= url('/uploads/' . (int) $row['id'] . '/delete'); ?>"
                            onsubmit="return confirm('Hapus berkas ini?');">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Hapus berkas">
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
        <i class="bi bi-cloud-arrow-up"></i>
        <h6>Belum ada berkas</h6>
        <p>Bagikan berkas pertama melalui tombol Upload Berkas.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
