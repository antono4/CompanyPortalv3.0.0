<?php
declare(strict_types=1);

use App\Config\App;

require __DIR__ . '/../layouts/admin_header.php';
?>

<div class="page-heading">
  <p class="subtitle">Identitas perusahaan yang tampil di seluruh portal.</p>
</div>

<div class="card form-card">
  <div class="card-header">
    <h2 class="card-title"><i class="bi bi-gear me-2 text-primary"></i>Pengaturan Perusahaan</h2>
  </div>
  <div class="card-body">
    <form method="post" action="<?= url('/settings'); ?>">
      <?= csrf_field(); ?>

      <div class="row g-3">
        <div class="col-md-6">
          <label for="company_name" class="form-label required">Nama Perusahaan</label>
          <input type="text" class="form-control" id="company_name" name="company_name" required maxlength="120"
                 value="<?= old('company_name', $settings['company_name'] ?? App::NAME); ?>">
        </div>

        <div class="col-md-6">
          <label for="company_email" class="form-label">Email</label>
          <input type="email" class="form-control" id="company_email" name="company_email" maxlength="150"
                 value="<?= old('company_email', $settings['company_email'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="company_phone" class="form-label">Telepon</label>
          <input type="text" class="form-control" id="company_phone" name="company_phone" maxlength="40"
                 value="<?= old('company_phone', $settings['company_phone'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="company_website" class="form-label">Website</label>
          <input type="text" class="form-control" id="company_website" name="company_website" maxlength="180"
                 placeholder="https://example.com"
                 value="<?= old('company_website', $settings['company_website'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
          <label for="timezone" class="form-label required">Zona Waktu</label>
          <?php $selectedTimezone = old('timezone', $settings['timezone'] ?? 'Asia/Jakarta'); ?>
          <select class="form-select" id="timezone" name="timezone">
            <option value="Asia/Jakarta" <?= $selectedTimezone === 'Asia/Jakarta' ? 'selected' : ''; ?>>Asia/Jakarta (WIB)</option>
            <option value="Asia/Makassar" <?= $selectedTimezone === 'Asia/Makassar' ? 'selected' : ''; ?>>Asia/Makassar (WITA)</option>
            <option value="Asia/Jayapura" <?= $selectedTimezone === 'Asia/Jayapura' ? 'selected' : ''; ?>>Asia/Jayapura (WIT)</option>
            <option value="UTC" <?= $selectedTimezone === 'UTC' ? 'selected' : ''; ?>>UTC</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Versi Aplikasi</label>
          <input type="text" class="form-control" value="v<?= e(App::VERSION); ?>" disabled>
        </div>

        <div class="col-12">
          <label for="company_address" class="form-label">Alamat</label>
          <textarea class="form-control" id="company_address" name="company_address" rows="3" maxlength="500"><?= old('company_address', $settings['company_address'] ?? ''); ?></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-1"></i>Simpan Pengaturan
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
