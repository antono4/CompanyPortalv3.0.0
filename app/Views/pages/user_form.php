<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';

$isEdit = $user !== null;
$action = $isEdit ? url('/users/' . (int) $user['id']) : url('/users');
?>

<div class="page-heading">
  <p class="subtitle">
    <?= $isEdit ? 'Perbarui data akun pengguna.' : 'Buat akun pengguna baru untuk portal.'; ?>
  </p>
  <a href="<?= url('/users'); ?>" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="card form-card">
  <div class="card-header">
    <h2 class="card-title">
      <i class="ti <?= $isEdit ? 'ti-edit' : 'ti-user-plus'; ?> me-2 text-primary"></i>
      <?= $isEdit ? 'Edit User' : 'Tambah User'; ?>
    </h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= $action; ?>">
      <?= csrf_field(); ?>

      <div class="row g-3">
        <div class="col-md-6">
          <label for="fullname" class="form-label required">Nama Lengkap</label>
          <input type="text" class="form-control" id="fullname" name="fullname" required maxlength="120"
                 value="<?= old('fullname', $user['fullname'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="username" class="form-label required">Username</label>
          <input type="text" class="form-control" id="username" name="username" required maxlength="60"
                 value="<?= old('username', $user['username'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label required">Email</label>
          <input type="email" class="form-control" id="email" name="email" required maxlength="150"
                 value="<?= old('email', $user['email'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="password" class="form-label <?= $isEdit ? '' : 'required'; ?>">
            Password <?= $isEdit ? '<span class="text-secondary fw-normal">(kosongkan jika tidak diubah)</span>' : ''; ?>
          </label>
          <input type="password" class="form-control" id="password" name="password"
                 <?= $isEdit ? '' : 'required minlength="8"'; ?> autocomplete="new-password">
          <?php if (!$isEdit): ?>
            <div class="form-text">Minimal 8 karakter.</div>
          <?php endif; ?>
        </div>

        <div class="col-md-4">
          <label for="role" class="form-label required">Role</label>
          <select class="form-select" id="role" name="role">
            <?php $selectedRole = old('role', $user['role'] ?? 'user'); ?>
            <option value="user" <?= $selectedRole === 'user' ? 'selected' : ''; ?>>User</option>
            <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : ''; ?>>Administrator</option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="status" class="form-label required">Status</label>
          <?php $selectedStatus = old('status', $user['status'] ?? 'active'); ?>
          <select class="form-select" id="status" name="status">
            <option value="active" <?= $selectedStatus === 'active' ? 'selected' : ''; ?>>Aktif</option>
            <option value="disabled" <?= $selectedStatus === 'disabled' ? 'selected' : ''; ?>>Nonaktif</option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="department_id" class="form-label">Departemen</label>
          <?php $selectedDepartment = (string) old('department_id', (string) ($user['department_id'] ?? '')); ?>
          <select class="form-select" id="department_id" name="department_id">
            <option value="">- Tanpa departemen -</option>
            <?php foreach ($departments as $department): ?>
              <option value="<?= (int) $department['id']; ?>"
                <?= $selectedDepartment === (string) $department['id'] ? 'selected' : ''; ?>>
                <?= e($department['department_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= url('/users'); ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-device-floppy me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan User'; ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
