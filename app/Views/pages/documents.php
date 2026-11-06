<?php
declare(strict_types=1);

use App\Core\Auth;

require __DIR__ . '/../layouts/admin_header.php';

$fileIcon = static function (?string $name): string {
    return match (strtolower(pathinfo((string) $name, PATHINFO_EXTENSION))) {
        'pdf' => 'ti-file-type-pdf text-danger',
        'doc', 'docx' => 'ti-file-type-docx text-primary',
        'xls', 'xlsx', 'csv' => 'ti-file-type-xls text-success',
        'ppt', 'pptx' => 'ti-file-type-ppt text-warning',
        'zip', 'rar' => 'ti-file-zip text-secondary',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'ti-photo text-info',
        'txt' => 'ti-file-text text-secondary',
        default => 'ti-file text-secondary',
    };
};
?>

<div class="page-heading">
  <p class="subtitle">Repositori dokumen resmi perusahaan.</p>
  <a href="<?= url('/documents/create'); ?>" class="btn btn-primary">
    <i class="ti ti-upload me-1"></i>Upload Dokumen
  </a>
</div>

<div class="card">
  <div class="card-header card-toolbar">
    <h2 class="card-title"><i class="ti ti-folder-open me-2 text-primary"></i>Daftar Dokumen</h2>
    <span class="badge badge-soft"><?= count($documents); ?> dokumen</span>
  </div>
  <div class="card-body p-0">
    <?php if ($documents !== []): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Dokumen</th>
              <th>Deskripsi</th>
              <th class="text-nowrap">Ukuran</th>
              <th>Pengunggah</th>
              <th class="text-nowrap">Tanggal</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documents as $row): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti <?= $fileIcon($row['original_name']); ?> fs-4"></i>
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
                    <a href="<?= url('/documents/' . (int) $row['id'] . '/download'); ?>"
                       class="btn btn-sm btn-outline-success btn-icon" aria-label="Unduh dokumen">
                      <i class="ti ti-download"></i>
                    </a>
                    <?php if (Auth::isAdmin() || (int) $row['uploaded_by'] === Auth::id()): ?>
                      <form method="post" action="<?= url('/documents/' . (int) $row['id'] . '/delete'); ?>"
                            onsubmit="return confirm('Hapus dokumen ini?');">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Hapus dokumen">
                          <i class="ti ti-trash"></i>
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
        <i class="ti ti-folder-open"></i>
        <h6>Belum ada dokumen</h6>
        <p>Unggah dokumen pertama untuk membangun repositori.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
