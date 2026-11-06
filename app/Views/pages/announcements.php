<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';

$featured = $announcements[0] ?? null;
$rest     = array_slice($announcements, 1);
?>

<div class="page-heading">
  <div>
    <h2 class="news-page-title mb-1">Berita &amp; Pengumuman</h2>
    <p class="subtitle mb-0">Informasi terbaru seputar perusahaan.</p>
  </div>
  <?php if (is_admin()): ?>
    <a href="<?= url('/announcements/create'); ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Tulis Berita
    </a>
  <?php endif; ?>
</div>

<?php if ($featured !== null): ?>
  <?php $featuredImage = news_image_url($featured); ?>
  <article class="news-hero mb-4<?= $featuredImage === null ? ' no-image' : ''; ?>">
    <?php if ($featuredImage !== null): ?>
      <img src="<?= e($featuredImage); ?>" alt="<?= e($featured['title']); ?>" class="news-hero-img">
    <?php endif; ?>
    <span class="news-hero-scrim"></span>

    <div class="news-hero-content">
      <span class="news-kicker"><i class="bi bi-star-fill"></i>Berita Utama</span>
      <h2 class="news-hero-title">
        <a href="<?= url('/announcements/' . (int) $featured['id']); ?>"><?= e($featured['title']); ?></a>
      </h2>
      <p class="news-hero-excerpt"><?= e(excerpt($featured['description'], 190)); ?></p>
      <div class="news-meta news-meta-light">
        <span><i class="bi bi-person"></i><?= e($featured['author'] ?? 'Admin'); ?></span>
        <span><i class="bi bi-calendar3"></i><?= e(fmt_datetime($featured['created_at'])); ?></span>
        <span><i class="bi bi-clock"></i><?= reading_time($featured['description']); ?> menit baca</span>
      </div>
    </div>

    <div class="news-hero-actions">
      <a href="<?= url('/announcements/' . (int) $featured['id']); ?>" class="btn btn-sm btn-light">
        <i class="bi bi-book me-1"></i>Baca
      </a>
      <?php if (is_admin()): ?>
        <a href="<?= url('/announcements/' . (int) $featured['id'] . '/edit'); ?>"
           class="btn btn-sm btn-light" aria-label="Edit berita utama">
          <i class="bi bi-pencil"></i>
        </a>
        <form method="post" action="<?= url('/announcements/' . (int) $featured['id'] . '/delete'); ?>"
              onsubmit="return confirm('Hapus berita ini?');">
          <?= csrf_field(); ?>
          <button type="submit" class="btn btn-sm btn-light text-danger" aria-label="Hapus berita utama">
            <i class="bi bi-trash"></i>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </article>
<?php endif; ?>

<?php if ($rest !== []): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="h5 fw-bold mb-0">Berita Lainnya</h3>
    <span class="text-secondary small"><i class="bi bi-collection me-1"></i><?= count($announcements); ?> artikel</span>
  </div>

  <div class="row g-3">
    <?php foreach ($rest as $row): ?>
      <?php $image = news_image_url($row); ?>
      <div class="col-md-6 col-xl-4">
        <article class="card news-card h-100">
          <a href="<?= url('/announcements/' . (int) $row['id']); ?>" class="news-card-media"
             aria-label="Baca <?= e($row['title']); ?>">
            <?php if ($image !== null): ?>
              <img src="<?= e($image); ?>" alt="<?= e($row['title']); ?>" loading="lazy">
            <?php else: ?>
              <span class="news-placeholder"><i class="bi bi-megaphone"></i></span>
            <?php endif; ?>
            <span class="news-date-chip"><?= e(fmt_date($row['created_at'], 'd M')); ?></span>
          </a>

          <div class="card-body d-flex flex-column">
            <span class="news-category">Pengumuman</span>
            <h3 class="news-card-title">
              <a href="<?= url('/announcements/' . (int) $row['id']); ?>"><?= e($row['title']); ?></a>
            </h3>
            <p class="news-card-excerpt"><?= e(excerpt($row['description'], 120)); ?></p>

            <div class="news-meta mt-auto">
              <span><i class="bi bi-person"></i><?= e($row['author'] ?? 'Admin'); ?></span>
              <span><i class="bi bi-clock"></i><?= reading_time($row['description']); ?> menit</span>
            </div>

            <div class="news-card-footer">
              <a href="<?= url('/announcements/' . (int) $row['id']); ?>" class="news-read-more">
                Baca selengkapnya <i class="bi bi-arrow-right"></i>
              </a>

              <?php if (is_admin()): ?>
                <div class="table-actions">
                  <a href="<?= url('/announcements/' . (int) $row['id'] . '/edit'); ?>"
                     class="btn btn-sm btn-outline-primary btn-icon" aria-label="Edit pengumuman">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form method="post" action="<?= url('/announcements/' . (int) $row['id'] . '/delete'); ?>"
                        onsubmit="return confirm('Hapus pengumuman ini?');">
                    <?= csrf_field(); ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Hapus pengumuman">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($announcements === []): ?>
  <div class="card">
    <div class="card-body">
      <div class="empty-state">
        <i class="bi bi-newspaper"></i>
        <h6>Belum ada berita</h6>
        <p>Berita dan pengumuman yang dipublikasikan akan tampil di sini.</p>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
