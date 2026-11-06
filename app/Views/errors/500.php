<?php
declare(strict_types=1);

use App\Config\App;

$detail = $message ?? '';
?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 - Terjadi Kesalahan</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg'); ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('img/favicon-32.png'); ?>">
  <link rel="shortcut icon" href="<?= asset('img/favicon.ico'); ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/apple-touch-icon.png'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/tabler-icons/tabler-icons.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('css/app.css'); ?>">
</head>
<body class="bg-body-tertiary">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="card">
          <div class="card-body p-4 p-md-5 text-center">
            <i class="ti ti-alert-octagon text-danger" style="font-size: 3.5rem;"></i>
            <h1 class="display-6 fw-bold mt-3 mb-1">Terjadi Kesalahan</h1>
            <p class="text-secondary mb-4">
              Permintaan tidak dapat diproses. Silakan coba lagi atau hubungi administrator.
            </p>
            <?php if ($detail !== ''): ?>
              <div class="alert alert-danger text-start small mb-4">
                <?= e($detail); ?>
              </div>
            <?php endif; ?>
            <div class="d-flex justify-content-center gap-2">
              <a href="<?= url('/dashboard'); ?>" class="btn btn-primary">
                <i class="ti ti-home me-1"></i>Dashboard
              </a>
              <a href="<?= url('/login'); ?>" class="btn btn-outline-secondary">Halaman Login</a>
            </div>
          </div>
        </div>
        <p class="text-center text-secondary small mt-3">&copy; <?= date('Y'); ?> <?= e(App::NAME); ?></p>
      </div>
    </div>
  </main>
</body>
</html>
