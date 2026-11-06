<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';

$isEdit = $employee !== null;
$action = $isEdit ? url('/employees/' . (int) $employee['id']) : url('/employees');
?>

<div class="page-heading">
  <p class="subtitle"><?= $isEdit ? 'Perbarui data karyawan.' : 'Lengkapi data karyawan baru.'; ?></p>
  <a href="<?= url('/employees'); ?>" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="card form-card">
  <div class="card-header">
    <h2 class="card-title">
      <i class="ti ti-id me-2 text-primary"></i><?= $isEdit ? 'Edit Karyawan' : 'Tambah Karyawan'; ?>
    </h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= $action; ?>">
      <?= csrf_field(); ?>

      <div class="row g-3">
        <div class="col-md-4">
          <label for="employee_code" class="form-label">Kode Karyawan</label>
          <input type="text" class="form-control" id="employee_code" name="employee_code" maxlength="40"
                 value="<?= old('employee_code', $employee['employee_code'] ?? ''); ?>">
        </div>

        <div class="col-md-8">
          <label for="fullname" class="form-label required">Nama Lengkap</label>
          <input type="text" class="form-control" id="fullname" name="fullname" required maxlength="120"
                 value="<?= old('fullname', $employee['fullname'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" maxlength="150"
                 value="<?= old('email', $employee['email'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="phone" class="form-label">Telepon</label>
          <input type="text" class="form-control" id="phone" name="phone" maxlength="40"
                 value="<?= old('phone', $employee['phone'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="designation" class="form-label">Jabatan</label>
          <input type="text" class="form-control" id="designation" name="designation" maxlength="100"
                 value="<?= old('designation', $employee['designation'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="department_id" class="form-label">Departemen</label>
          <?php $selectedDepartment = (string) old('department_id', (string) ($employee['department_id'] ?? '')); ?>
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

        <div class="col-md-6">
          <label for="joining_date" class="form-label">Tanggal Bergabung</label>
          <input type="date" class="form-control" id="joining_date" name="joining_date"
                 value="<?= old('joining_date', $employee['joining_date'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="status" class="form-label required">Status</label>
          <?php $selectedStatus = old('status', $employee['status'] ?? 'active'); ?>
          <select class="form-select" id="status" name="status">
            <option value="active" <?= $selectedStatus === 'active' ? 'selected' : ''; ?>>Aktif</option>
            <option value="disabled" <?= $selectedStatus === 'disabled' ? 'selected' : ''; ?>>Nonaktif</option>
          </select>
        </div>

        <div class="col-12">
          <label for="address" class="form-label">Alamat</label>
          <textarea class="form-control" id="address" name="address" rows="3" maxlength="500"><?= old('address', $employee['address'] ?? ''); ?></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= url('/employees'); ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-device-floppy me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan'; ?>
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
