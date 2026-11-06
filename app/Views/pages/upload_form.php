<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Unggah berkas untuk dibagikan ke seluruh karyawan.</p>
  <a href="<?= url('/uploads'); ?>" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="card form-card">
  <div class="card-header">
    <h2 class="card-title"><i class="ti ti-cloud-upload me-2 text-primary"></i>Upload Berkas</h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= url('/uploads'); ?>" enctype="multipart/form-data">
      <?= csrf_field(); ?>

      <div class="mb-3">
        <label for="title" class="form-label required">Judul Berkas</label>
        <input type="text" class="form-control" id="title" name="title" required maxlength="180"
               value="<?= old('title'); ?>">
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="description" name="description" rows="4" maxlength="2000"><?= old('description'); ?></textarea>
      </div>

      <div class="mb-3">
        <label for="file" class="form-label required">Berkas</label>
        <input type="file" class="form-control" id="file" name="file" required
               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.csv,.jpg,.jpeg,.png,.gif,.webp">
        <div class="form-text">
          Format: PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, ZIP/RAR, TXT, CSV, JPG, PNG, GIF, WEBP. Maksimal 10 MB.
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= url('/uploads'); ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-cloud-upload me-1"></i>Upload
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
