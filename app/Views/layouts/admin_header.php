<?php
/**
 * Company Portal - Admin layout header (AdminLTE 4)
 * Uses the official AdminLTE 4 structure: app-wrapper > app-header / app-sidebar / app-main / app-footer
 */
declare(strict_types=1);

use App\Config\App;
use App\Core\Auth;
use App\Core\Session;

$currentUser  = Auth::user();
$documentTitle = $title ?? 'Dashboard';
$pageTitle    = preg_replace('/\s+-\s+' . preg_quote(App::NAME, '/') . '$/', '', $documentTitle) ?: 'Dashboard';
$flashSuccess = Session::pullFlash('success');
$flashError   = Session::pullFlash('error');

try {
    $companyName = (new \App\Models\Setting())->get('company_name', App::NAME);
} catch (\Throwable) {
    $companyName = App::NAME;
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="light">
  <title><?= e($documentTitle); ?></title>

  <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg'); ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('img/favicon-32.png'); ?>">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= asset('img/favicon-192.png'); ?>">
  <link rel="shortcut icon" href="<?= asset('img/favicon.ico'); ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/apple-touch-icon.png'); ?>">

  <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('vendor/adminlte/css/adminlte.min.css'); ?>">
  <link rel="stylesheet" href="<?= asset('css/app.css'); ?>">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

  <!-- ================= Header ================= -->
  <nav class="app-header navbar navbar-expand bg-body border-bottom">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Buka/tutup sidebar">
            <i class="bi bi-list"></i>
          </a>
        </li>
        <li class="nav-item d-none d-md-block">
          <span class="nav-link text-secondary">
            <i class="bi bi-grid-1x2 me-1"></i><?= e($companyName); ?>
          </span>
        </li>
      </ul>

      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item d-none d-sm-block me-2">
          <span class="badge text-bg-light border">
            <i class="bi bi-calendar3 me-1"></i><?= date('d M Y'); ?>
          </span>
        </li>

        <li class="nav-item dropdown user-menu">
          <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
            <?= avatar_html($currentUser); ?>
            <span class="d-none d-lg-inline"><?= e($currentUser['fullname'] ?? 'User'); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li class="px-3 py-2 border-bottom">
              <div class="fw-semibold"><?= e($currentUser['fullname'] ?? 'User'); ?></div>
              <div class="small text-secondary">
                @<?= e($currentUser['username'] ?? ''); ?>
                &middot;
                <span class="text-uppercase"><?= e($currentUser['role'] ?? 'user'); ?></span>
              </div>
            </li>
            <li>
              <a class="dropdown-item" href="<?= url('/profile'); ?>">
                <i class="bi bi-person me-2"></i>Profil Saya
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="<?= url('/change-password'); ?>">
                <i class="bi bi-key me-2"></i>Ganti Password
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?= url('/logout'); ?>">
                <i class="bi bi-box-arrow-right me-2"></i>Keluar
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <!-- ================= Sidebar ================= -->
  <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
      <a href="<?= url('/dashboard'); ?>" class="brand-link">
        <span class="brand-mark"><i class="bi bi-buildings"></i></span>
        <span class="brand-text fw-semibold"><?= e($companyName); ?></span>
      </a>
    </div>

    <div class="sidebar-wrapper">
      <nav class="mt-2" aria-label="Navigasi utama">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

          <li class="nav-item">
            <a href="<?= url('/dashboard'); ?>" class="nav-link <?= nav_active('/dashboard'); ?>">
              <i class="nav-icon bi bi-speedometer2"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-header">ORGANISASI</li>
          <li class="nav-item">
            <a href="<?= url('/departments'); ?>" class="nav-link <?= nav_active('/departments'); ?>">
              <i class="nav-icon bi bi-diagram-3"></i>
              <p>Departemen</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= url('/employees'); ?>" class="nav-link <?= nav_active('/employees'); ?>">
              <i class="nav-icon bi bi-person-vcard"></i>
              <p>Karyawan</p>
            </a>
          </li>

          <li class="nav-header">KONTEN</li>
          <li class="nav-item">
            <a href="<?= url('/attendance'); ?>" class="nav-link <?= App::path() === '/attendance' ? 'active' : ''; ?>">
              <i class="nav-icon bi bi-fingerprint"></i>
              <p>Absensi</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= url('/announcements'); ?>" class="nav-link <?= nav_active('/announcements'); ?>">
              <i class="nav-icon bi bi-megaphone"></i>
              <p>Pengumuman</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= url('/documents'); ?>" class="nav-link <?= nav_active('/documents'); ?>">
              <i class="nav-icon bi bi-folder2-open"></i>
              <p>Dokumen</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= url('/uploads'); ?>" class="nav-link <?= nav_active('/uploads'); ?>">
              <i class="nav-icon bi bi-cloud-arrow-up"></i>
              <p>Upload Center</p>
            </a>
          </li>

          <?php if (Auth::isAdmin()): ?>
            <li class="nav-header">ADMINISTRASI</li>
            <li class="nav-item">
              <a href="<?= url('/users'); ?>" class="nav-link <?= nav_active('/users'); ?>">
                <i class="nav-icon bi bi-people"></i>
                <p>Manajemen User</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= url('/attendance/schedules'); ?>" class="nav-link <?= nav_active('/attendance/schedules'); ?>">
                <i class="nav-icon bi bi-calendar2-week"></i>
                <p>Jadwal Absensi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= url('/activity-logs'); ?>" class="nav-link <?= nav_active('/activity-logs'); ?>">
                <i class="nav-icon bi bi-clock-history"></i>
                <p>Log Aktivitas</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= url('/settings'); ?>" class="nav-link <?= nav_active('/settings'); ?>">
                <i class="nav-icon bi bi-gear"></i>
                <p>Pengaturan</p>
              </a>
            </li>
          <?php endif; ?>

        </ul>
      </nav>
    </div>
  </aside>

  <!-- ================= Main ================= -->
  <main class="app-main">
    <div class="app-content-header">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-sm-6">
            <h1 class="mb-0 fs-3"><?= e($pageTitle); ?></h1>
          </div>
          <div class="col-sm-6">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb float-sm-end mb-0">
                <li class="breadcrumb-item"><a href="<?= url('/dashboard'); ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($pageTitle); ?></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="app-content">
      <div class="container-fluid">

        <?php if ($flashSuccess): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= e($flashSuccess); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($flashError); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
          </div>
        <?php endif; ?>
