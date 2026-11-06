<?php
declare(strict_types=1);

use App\Config\App;
use App\Core\Csrf;

require __DIR__ . '/../layouts/auth_header.php';
?>
<div class="auth-card">
  <div class="auth-card-brand">
    <span class="brand-mark brand-mark-lg"><i class="ti ti-buildings"></i></span>
    <h1><?= e(App::NAME); ?></h1>
    <p>Portal intranet perusahaan</p>
  </div>

  <div class="auth-card-body">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger d-flex align-items-start" role="alert">
        <i class="ti ti-alert-triangle-filled me-2 mt-1"></i>
        <div><?= e($error); ?></div>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= url('/login'); ?>" autocomplete="on" novalidate>
      <?= Csrf::field(); ?>

      <div class="mb-3">
        <label for="username" class="form-label">Username atau Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="ti ti-user"></i></span>
          <input type="text" class="form-control" id="username" name="username"
                 placeholder="Masukkan username" required autofocus
                 value="<?= old('username'); ?>">
        </div>
      </div>

      <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="ti ti-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password"
                 placeholder="Masukkan password" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan password">
            <i class="ti ti-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="ti ti-login-2 me-2"></i>Masuk
      </button>
    </form>
  </div>

  <div class="auth-card-footer">
    &copy; <?= date('Y'); ?> <?= e(App::NAME); ?> &middot; v<?= e(App::VERSION); ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/auth_footer.php'; ?>
