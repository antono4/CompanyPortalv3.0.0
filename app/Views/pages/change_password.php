<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Gunakan password yang kuat dan tidak dipakai di layanan lain.</p>
  <a href="<?= url('/profile'); ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Kembali ke Profil
  </a>
</div>

<div class="card form-card narrow">
  <div class="card-header">
    <h2 class="card-title"><i class="bi bi-key me-2 text-primary"></i>Ganti Password</h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= url('/change-password'); ?>">
      <?= csrf_field(); ?>

      <div class="mb-3">
        <label for="current_password" class="form-label required">Password Saat Ini</label>
        <input type="password" class="form-control" id="current_password" name="current_password"
               required autocomplete="current-password">
      </div>

      <div class="mb-3">
        <label for="new_password" class="form-label required">Password Baru</label>
        <input type="password" class="form-control" id="new_password" name="new_password"
               required minlength="8" autocomplete="new-password">
        <div class="form-text">Minimal 8 karakter dan berbeda dari password lama.</div>
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label required">Konfirmasi Password Baru</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
               required minlength="8" autocomplete="new-password">
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?= url('/profile'); ?>" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-shield-lock me-1"></i>Simpan Password
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
