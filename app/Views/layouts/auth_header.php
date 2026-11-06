<?php
declare(strict_types=1);

use App\Config\App;

$pageTitle = $title ?? 'Masuk';
$companyName = App::NAME;
try {
    $companyName = (new \App\Models\Setting())->get('company_name', App::NAME);
} catch (\Throwable) {
    // database not ready yet
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle); ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg'); ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('img/favicon-32.png'); ?>">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= asset('img/favicon-192.png'); ?>">
  <link rel="shortcut icon" href="<?= asset('img/favicon.ico'); ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/apple-touch-icon.png'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/tabler-icons/tabler-icons.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/adminlte/css/adminlte.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('css/app.css'); ?>">
</head>
<body class="auth-body">
  <main class="auth-shell">
