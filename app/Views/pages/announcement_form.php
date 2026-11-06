<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';

$isEdit = $announcement !== null;
$action = $isEdit ? url('/announcements/' . (int) $announcement['id']) : url('/announcements');
?>

<div class="page-heading">
  <p class="subtitle"><?= $isEdit ? 'Perbarui isi pengumuman.' : 'Tulis pengumuman baru untuk seluruh karyawan.'; ?></p>
  <a href="<?= url('/announcements'); ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="card form-card">
  <div class="card-header">
    <h2 class="card-title">
      <i class="bi bi-megaphone me-2 text-primary"></i><?= $isEdit ? 'Edit Pengumuman' : 'Buat Pengumuman'; ?>
    </h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= $action; ?>" enctype="multipart/form-data">
      <?= csrf_field(); ?>

      <div class="mb-3">
        <label for="title" class="form-label required">Judul</label>
        <input type="text" class="form-control" id="title" name="title" required maxlength="180"
               value="<?= old('title', $announcement['title'] ?? ''); ?>">
      </div>

      <div class="mb-3">
        <label for="image" class="form-label">Gambar Berita</label>
        <?php $currentImage = $isEdit ? news_image_url($announcement) : null; ?>
        <?php if ($currentImage !== null): ?>
          <div class="news-form-preview mb-2">
            <img src="<?= e($currentImage); ?>" alt="Gambar saat ini">
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
            <label class="form-check-label small" for="remove_image">Hapus gambar saat menyimpan</label>
          </div>
        <?php endif; ?>
        <input type="file" class="form-control" id="image" name="image"
               accept="image/jpeg,image/png,image/gif,image/webp">
        <div class="form-text">
          JPG, PNG, GIF, atau WEBP. Maksimal 3 MB. Gambar tampil sebagai hero di halaman berita
          dan thumbnail pada kartu berita.
        </div>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label required">Isi Pengumuman</label>
        <textarea class="form-control" id="description" name="description" rows="10" required><?= old('description', $announcement['description'] ?? ''); ?></textarea>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= url('/announcements'); ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-send me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Publikasikan'; ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
