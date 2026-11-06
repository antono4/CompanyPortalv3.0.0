<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Kelola informasi profil, foto, dan keamanan akun Anda.</p>
  <a href="<?= url('/change-password'); ?>" class="btn btn-outline-primary">
    <i class="ti ti-shield-lock me-1"></i>Ganti Password
  </a>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body text-center">
        <?= avatar_html($user, 'avatar-lg mb-3'); ?>
        <h2 class="h5 fw-bold mb-1"><?= e($user['fullname'] ?? ''); ?></h2>
        <p class="text-secondary small mb-3">@<?= e($user['username'] ?? ''); ?></p>

        <span class="badge <?= ($user['role'] ?? '') === 'admin' ? 'badge-role-admin' : 'badge-role-user'; ?> mb-3">
          <?= ($user['role'] ?? '') === 'admin' ? 'Administrator' : 'User'; ?>
        </span>

        <form method="post" action="<?= url('/profile/photo'); ?>" enctype="multipart/form-data"
              class="text-start border rounded p-3 mt-1">
          <?= csrf_field(); ?>
          <label for="photo" class="form-label">Foto Profil</label>
          <input type="file" class="form-control form-control-sm" id="photo" name="photo"
                 accept="image/jpeg,image/png,image/gif,image/webp" required>
          <div class="form-text">JPG, PNG, GIF, atau WEBP. Maksimal 3 MB.</div>
          <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
            <i class="ti ti-camera me-1"></i><?= avatar_url($user) ? 'Ganti Foto' : 'Unggah Foto'; ?>
          </button>
        </form>

        <?php if (avatar_url($user)): ?>
          <form method="post" action="<?= url('/profile/photo/delete'); ?>" class="mt-2"
                onsubmit="return confirm('Hapus foto profil Anda?');">
            <?= csrf_field(); ?>
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
              <i class="ti ti-trash me-1"></i>Hapus Foto
            </button>
          </form>
        <?php endif; ?>

        <hr>

        <div class="text-start small">
          <div class="d-flex justify-content-between py-1">
            <span class="text-secondary">Email</span>
            <span class="fw-semibold text-truncate ms-2"><?= e($user['email'] ?? '-'); ?></span>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-secondary">Departemen</span>
            <span class="fw-semibold"><?= e($user['department_name'] ?? '-'); ?></span>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-secondary">Status</span>
            <span class="badge <?= ($user['status'] ?? '') === 'active' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
              <?= ($user['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif'; ?>
            </span>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-secondary">Login terakhir</span>
            <span class="fw-semibold"><?= e($user['last_login'] ? fmt_datetime($user['last_login']) : 'Belum pernah'); ?></span>
          </div>
        </div>

        <a href="<?= url('/change-password'); ?>" class="btn btn-outline-primary w-100 mt-3">
          <i class="ti ti-key me-1"></i>Ganti Password
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h2 class="card-title"><i class="ti ti-edit me-2 text-primary"></i>Edit Profil</h2>
      </div>
      <div class="card-body">
        <form method="post" action="<?= url('/profile'); ?>">
          <?= csrf_field(); ?>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="fullname" class="form-label required">Nama Lengkap</label>
              <input type="text" class="form-control" id="fullname" name="fullname" required maxlength="120"
                     value="<?= old('fullname', $user['fullname'] ?? ''); ?>">
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label required">Email</label>
              <input type="email" class="form-control" id="email" name="email" required maxlength="150"
                     value="<?= old('email', $user['email'] ?? ''); ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" value="<?= e($user['username'] ?? ''); ?>" disabled>
              <div class="form-text">Username tidak dapat diubah.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Role</label>
              <input type="text" class="form-control"
                     value="<?= ($user['role'] ?? '') === 'admin' ? 'Administrator' : 'User'; ?>" disabled>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-device-floppy me-1"></i>Simpan Profil
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
