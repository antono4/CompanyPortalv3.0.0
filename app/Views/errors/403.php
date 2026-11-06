<?php
declare(strict_types=1);

use App\Config\App;
?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 - Akses Ditolak</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg'); ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('img/favicon-32.png'); ?>">
  <link rel="shortcut icon" href="<?= asset('img/favicon.ico'); ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/apple-touch-icon.png'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('css/app.css'); ?>">
</head>
<body class="bg-body-tertiary">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card text-center">
          <div class="card-body py-5">
            <i class="bi bi-shield-lock text-danger" style="font-size: 3.5rem;"></i>
            <h1 class="display-5 fw-bold mt-3 mb-1">403</h1>
            <p class="text-secondary mb-4">Anda tidak memiliki hak akses ke halaman ini.</p>
            <a href="<?= url('/dashboard'); ?>" class="btn btn-primary">
              <i class="bi bi-house me-1"></i>Kembali ke Dashboard
            </a>
          </div>
        </div>
        <p class="text-center text-secondary small mt-3">&copy; <?= date('Y'); ?> <?= e(App::NAME); ?></p>
      </div>
    </div>
  </main>
</body>
</html>
