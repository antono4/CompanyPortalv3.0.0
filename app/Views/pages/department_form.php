<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';

$isEdit = $department !== null;
$action = $isEdit ? url('/departments/' . (int) $department['id']) : url('/departments');
?>

<div class="page-heading">
  <p class="subtitle"><?= $isEdit ? 'Perbarui data departemen.' : 'Buat departemen baru.'; ?></p>
  <a href="<?= url('/departments'); ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="card form-card narrow">
  <div class="card-header">
    <h2 class="card-title">
      <i class="bi bi-diagram-3 me-2 text-primary"></i><?= $isEdit ? 'Edit Departemen' : 'Tambah Departemen'; ?>
    </h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= $action; ?>">
      <?= csrf_field(); ?>

      <div class="mb-3">
        <label for="department_name" class="form-label required">Nama Departemen</label>
        <input type="text" class="form-control" id="department_name" name="department_name" required maxlength="100"
               value="<?= old('department_name', $department['department_name'] ?? ''); ?>">
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="description" name="description" rows="3" maxlength="500"><?= old('description', $department['description'] ?? ''); ?></textarea>
      </div>

      <div class="mb-3">
        <label for="status" class="form-label required">Status</label>
        <?php $selectedStatus = old('status', $department['status'] ?? 'active'); ?>
        <select class="form-select" id="status" name="status">
          <option value="active" <?= $selectedStatus === 'active' ? 'selected' : ''; ?>>Aktif</option>
          <option value="disabled" <?= $selectedStatus === 'disabled' ? 'selected' : ''; ?>>Nonaktif</option>
        </select>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= url('/departments'); ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan'; ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
