<?php
declare(strict_types=1);

use App\Config\App;
use App\Core\Auth;

require __DIR__ . '/../layouts/admin_header.php';

$activePercent   = $totalEmployees > 0 ? (int) round($activeEmployees / $totalEmployees * 100) : 0;
$inactivePercent = $totalEmployees > 0 ? (int) round($inactiveEmployees / $totalEmployees * 100) : 0;
?>

<!-- ================= Header Band ================= -->
<div class="cp-header-band">
  <div class="row align-items-center g-3">
    <div class="col-lg-9 col-md-8">
      <h1 class="cp-band-title"><?= e($pageTitle); ?></h1>
      <p class="cp-band-subtitle">
        Selamat datang kembali, <strong><?= e($currentUser['fullname'] ?? 'User'); ?></strong>. Berikut ringkasan aktivitas dan informasi terbaru perusahaan.
      </p>
    </div>
    <div class="col-lg-3 col-md-4">
      <div class="cp-band-actions">
        <span class="cp-band-badge">
          <i class="bi bi-calendar3"></i> <?= date('d M Y'); ?>
        </span>
        <a href="<?= url('/announcements/create'); ?>" class="cp-band-btn">
          <i class="bi bi-megaphone"></i> Buat Pengumuman
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ================= Hero Banner ================= -->
<?php if (!empty($latestAnnouncements)): ?>
  <?php $hero = reset($latestAnnouncements); ?>
  <?php $heroThumb = news_image_url($hero); ?>
  <div class="cp-hero-banner">
    <div class="cp-hero-banner-media">
      <?php if ($heroThumb !== null): ?>
        <img src="<?= e($heroThumb); ?>" alt="<?= e($hero['title']); ?>" loading="lazy">
      <?php else: ?>
        <img src="<?= asset('img/favicon.svg'); ?>" alt="Banner" loading="lazy">
      <?php endif; ?>
      <div class="cp-hero-banner-actions">
        <a href="<?= url('/announcements/' . (int) $hero['id']); ?>" class="cp-band-btn">
          <i class="bi bi-arrow-right"></i> Baca selengkapnya
        </a>
      </div>
    </div>
    <div class="cp-hero-banner-content">
      <span class="cp-hero-banner-kicker">Pengumuman Utama</span>
      <h2 class="cp-hero-banner-title">
        <a href="<?= url('/announcements/' . (int) $hero['id']); ?>">
          <?= e($hero['title']); ?>
        </a>
      </h2>
      <p class="cp-hero-banner-excerpt"><?= e(excerpt($hero['description'], 120)); ?></p>
    </div>
  </div>
<?php endif; ?>

<!-- ================= Stat Cards ================= -->
<div class="row g-3 mb-1">
  <div class="col-xl-3 col-md-6">
    <div class="cp-stat-card text-bg-primary">
      <div class="inner">
        <h3><?= (int) $totalEmployees; ?></h3>
        <p>Total Karyawan</p>
      </div>
      <i class="bi bi-people-fill stat-icon"></i>
      <a href="<?= url('/employees'); ?>" class="small-box-footer">
        Lihat data <i class="bi bi-arrow-right-circle"></i>
      </a>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="cp-stat-card text-bg-success">
      <div class="inner">
        <h3><?= (int) $totalDepartments; ?></h3>
        <p>Departemen</p>
      </div>
      <i class="bi bi-diagram-3-fill stat-icon"></i>
      <a href="<?= url('/departments'); ?>" class="small-box-footer">
        Lihat data <i class="bi bi-arrow-right-circle"></i>
      </a>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="cp-stat-card text-bg-warning">
      <div class="inner">
        <h3><?= (int) $totalDocuments; ?></h3>
        <p>Dokumen</p>
      </div>
      <i class="bi bi-folder2-open stat-icon"></i>
      <a href="<?= url('/documents'); ?>" class="small-box-footer">
        Lihat data <i class="bi bi-arrow-right-circle"></i>
      </a>
    </div>
  </div>

  <div class="col-xl-3 col-md-6">
    <div class="cp-stat-card text-bg-info">
      <div class="inner">
        <h3><?= (int) $totalUsers; ?></h3>
        <p>User Terdaftar</p>
      </div>
      <i class="bi bi-person-badge-fill stat-icon"></i>
      <?php if (Auth::isAdmin()): ?>
        <a href="<?= url('/users'); ?>" class="small-box-footer">
          Kelola user <i class="bi bi-arrow-right-circle"></i>
        </a>
      <?php else: ?>
        <span class="small-box-footer">&nbsp;</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ================= Two-column row: Employee status + Recent activities ================= -->
<div class="row g-3">
  <!-- Employee status -->
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="cp-card-header">
        <h2 class="cp-card-title"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Status Karyawan</h2>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-secondary small">Karyawan aktif</span>
          <span class="fw-semibold"><?= (int) $activeEmployees; ?></span>
        </div>
        <div class="progress mb-3" style="height: 8px;" role="progressbar" aria-label="Karyawan aktif"
             aria-valuenow="<?= $activePercent; ?>" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar bg-success" style="width: <?= $activePercent; ?>%"></div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-secondary small">Karyawan nonaktif</span>
          <span class="fw-semibold"><?= (int) $inactiveEmployees; ?></span>
        </div>
        <div class="progress mb-3" style="height: 8px;" role="progressbar" aria-label="Karyawan nonaktif"
             aria-valuenow="<?= $inactivePercent; ?>" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar bg-danger" style="width: <?= $inactivePercent; ?>%"></div>
        </div>

        <hr>

        <div class="d-flex justify-content-between small mb-2">
          <span class="text-secondary"><i class="bi bi-folder me-1"></i>Dokumen</span>
          <span class="fw-semibold"><?= (int) $totalDocuments; ?> berkas</span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
          <span class="text-secondary"><i class="bi bi-cloud-arrow-up me-1"></i>Upload center</span>
          <span class="fw-semibold"><?= (int) $totalUploads; ?> berkas</span>
        </div>
        <div class="d-flex justify-content-between small mb-2">
          <span class="text-secondary"><i class="bi bi-megaphone me-1"></i>Pengumuman</span>
          <span class="fw-semibold"><?= (int) $totalAnnouncements; ?> posting</span>
        </div>
        <div class="d-flex justify-content-between small">
          <span class="text-secondary"><i class="bi bi-hdd me-1"></i>Penyimpanan</span>
          <span class="fw-semibold"><?= e(human_size($storageUsed)); ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent activities -->
  <div class="col-xl-8">
    <div class="card h-100">
      <div class="cp-card-header card-toolbar">
        <h2 class="cp-card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Terbaru</h2>
        <?php if (Auth::isAdmin()): ?>
          <a href="<?= url('/activity-logs'); ?>" class="cp-section-link">
            Lihat semua <i class="bi bi-arrow-right"></i>
          </a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (!empty($recentActivities)): ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentActivities as $activity): ?>
              <div class="cp-activity-item">
                <span class="activity-dot"><i class="bi bi-activity"></i></span>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between gap-3">
                    <span class="activity-text">
                      <strong><?= e($activity['user_name'] ?? 'Sistem'); ?></strong>
                      &middot; <?= e($activity['activity']); ?>
                    </span>
                    <span class="activity-meta text-nowrap"><?= e(fmt_datetime($activity['created_at'])); ?></span>
                  </div>
                  <div class="activity-meta">
                    <i class="bi bi-hdd-network me-1"></i><?= e($activity['ip_address'] ?? '-'); ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="cp-empty-state">
            <i class="bi bi-clock"></i>
            <h6>Belum ada aktivitas</h6>
            <p>Aktivitas pengguna akan tampil di sini.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ================= In the Know (featured cards) ================= -->
<div class="cp-section-header">
  <h2 class="cp-section-title">In the Know</h2>
  <a href="<?= url('/announcements'); ?>" class="cp-section-link">Lihat semua pengumuman</a>
</div>

<div class="row g-3">
  <?php
  $featured = array_slice($latestAnnouncements, 0, 4);
  if ($featured === []): ?>
    <div class="col-12">
      <div class="cp-empty-state">
        <i class="bi bi-megaphone"></i>
        <h6>Belum ada pengumuman</h6>
        <p>Pengumuman perusahaan akan tampil di sini.</p>
      </div>
    </div>
  <?php endif; ?>
  <?php foreach ($featured as $announcement): ?>
    <?php $newsThumb = news_image_url($announcement); ?>
    <div class="col-xl-3 col-md-6">
      <div class="cp-featured-card">
        <?php if ($newsThumb !== null): ?>
          <div class="cp-featured-card-media">
            <img src="<?= e($newsThumb); ?>" alt="<?= e($announcement['title']); ?>" loading="lazy">
          </div>
        <?php else: ?>
          <div class="cp-featured-card-media">
            <img src="<?= asset('img/favicon.svg'); ?>" alt="Banner" loading="lazy">
          </div>
        <?php endif; ?>
        <div class="cp-featured-card-body">
          <span class="cp-featured-card-category">
            <?= e(fmt_date($announcement['created_at'])); ?>
          </span>
          <h3 class="cp-featured-card-title">
            <a href="<?= url('/announcements/' . (int) $announcement['id']); ?>">
              <?= e($announcement['title']); ?>
            </a>
          </h3>
          <div class="cp-featured-card-meta">
            <span>
              <i class="bi bi-person me-1"></i><?= e($announcement['author'] ?? 'Admin'); ?>
            </span>
            <span>
              <i class="bi bi-clock me-1"></i><?= reading_time($announcement['description']); ?> menit
            </span>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ================= Resources ================= -->
<div class="cp-section-header">
  <h2 class="cp-section-title">Resources</h2>
</div>

<div class="cp-resources-grid">
  <a href="<?= url('/employees'); ?>" class="cp-resource-tile">
    <span class="cp-resource-icon primary"><i class="bi bi-people-fill"></i></span>
    Karyawan
  </a>
  <a href="<?= url('/departments'); ?>" class="cp-resource-tile">
    <span class="cp-resource-icon success"><i class="bi bi-diagram-3"></i></span>
    Departemen
  </a>
  <a href="<?= url('/documents'); ?>" class="cp-resource-tile">
    <span class="cp-resource-icon warning"><i class="bi bi-folder2-open"></i></span>
    Dokumen
  </a>
  <a href="<?= url('/uploads'); ?>" class="cp-resource-tile">
    <span class="cp-resource-icon info"><i class="bi bi-cloud-arrow-up"></i></span>
    Upload Center
  </a>
  <a href="<?= url('/announcements'); ?>" class="cp-resource-tile">
    <span class="cp-resource-icon purple"><i class="bi bi-megaphone"></i></span>
    Pengumuman
  </a>
  <a href="<?= url('/attendance'); ?>" class="cp-resource-tile">
    <span class="cp-resource-icon dark"><i class="bi bi-fingerprint"></i></span>
    Absensi
  </a>
  <a href="<?= url('/profile'); ?>" class="cp-resource-tile">
    <span class="cp-resource-icon teal"><i class="bi bi-person"></i></span>
    Profil Saya
  </a>
  <?php if (Auth::isAdmin()): ?>
    <a href="<?= url('/settings'); ?>" class="cp-resource-tile">
      <span class="cp-resource-icon dark"><i class="bi bi-gear"></i></span>
      Pengaturan
    </a>
  <?php endif; ?>
</div>

<!-- ================= Bottom Banner ================= -->
<div class="cp-bottom-banner">
  <div class="cp-bottom-banner-content">
    <p class="cp-bottom-banner-text">
      Have a great example of <strong>Company Vision</strong> coming to life?
    </p>
    <a href="<?= url('/announcements/create'); ?>" class="cp-bottom-banner-btn">
      <i class="bi bi-share-fill"></i> Share It With Us!
    </a>
  </div>
</div>

<!-- ================= Quick Actions (admin) ================= -->
<?php if (Auth::isAdmin()): ?>
  <div class="cp-section-header mt-4">
    <h2 class="cp-section-title">Quick Actions</h2>
  </div>

  <div class="cp-quick-action-card">
    <div class="card-body">
      <div class="cp-quick-action-grid">
        <a href="<?= url('/employees/create'); ?>" class="cp-quick-action-tile">
          <i class="bi bi-person-plus"></i>
          <span>Karyawan</span>
        </a>
        <a href="<?= url('/departments/create'); ?>" class="cp-quick-action-tile">
          <i class="bi bi-diagram-3"></i>
          <span>Departemen</span>
        </a>
        <a href="<?= url('/users/create'); ?>" class="cp-quick-action-tile">
          <i class="bi bi-person-badge"></i>
          <span>User</span>
        </a>
        <a href="<?= url('/announcements/create'); ?>" class="cp-quick-action-tile">
          <i class="bi bi-megaphone"></i>
          <span>Pengumuman</span>
        </a>
        <a href="<?= url('/documents/create'); ?>" class="cp-quick-action-tile">
          <i class="bi bi-upload"></i>
          <span>Dokumen</span>
        </a>
        <a href="<?= url('/settings'); ?>" class="cp-quick-action-tile">
          <i class="bi bi-gear"></i>
          <span>Pengaturan</span>
        </a>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
