<?php
declare(strict_types=1);

require __DIR__ . '/../layouts/admin_header.php';

$image = news_image_url($announcement);
?>

<div class="mb-3">
  <a href="<?= url('/announcements'); ?>" class="text-secondary text-decoration-none small">
    <i class="bi bi-arrow-left me-1"></i>Kembali ke Berita
  </a>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <article class="card article-card">
      <header class="article-header">
        <span class="news-kicker"><i class="bi bi-megaphone"></i>Pengumuman</span>
        <h1 class="article-title mt-3"><?= e($announcement['title']); ?></h1>
        <div class="news-meta mt-3">
          <span><i class="bi bi-person-circle"></i><?= e($announcement['author'] ?? 'Admin'); ?></span>
          <span><i class="bi bi-calendar3"></i><?= e(fmt_datetime($announcement['created_at'])); ?></span>
          <span><i class="bi bi-clock"></i><?= reading_time($announcement['description']); ?> menit baca</span>
        </div>
      </header>

      <?php if ($image !== null): ?>
        <figure class="article-hero mb-0">
          <img src="<?= e($image); ?>" alt="<?= e($announcement['title']); ?>">
        </figure>
      <?php endif; ?>

      <div class="article-body">
        <?= nl2br(e($announcement['description'])); ?>
      </div>

      <?php if (is_admin()): ?>
        <div class="article-actions">
          <a href="<?= url('/announcements/' . (int) $announcement['id'] . '/edit'); ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit Berita
          </a>
          <form method="post" action="<?= url('/announcements/' . (int) $announcement['id'] . '/delete'); ?>"
                onsubmit="return confirm('Hapus berita ini?');">
            <?= csrf_field(); ?>
            <button type="submit" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-trash me-1"></i>Hapus
            </button>
          </form>
        </div>
      <?php endif; ?>
    </article>
  </div>

  <div class="col-lg-4">
    <div class="card sidebar-card">
      <div class="card-header">
        <h2 class="card-title"><i class="bi bi-newspaper me-2 text-primary"></i>Berita Lainnya</h2>
      </div>

      <?php if ($others !== []): ?>
        <div class="list-group list-group-flush">
          <?php foreach ($others as $other): ?>
            <?php $otherImage = news_image_url($other); ?>
            <a href="<?= url('/announcements/' . (int) $other['id']); ?>"
               class="list-group-item news-side-item">
              <?php if ($otherImage !== null): ?>
                <img src="<?= e($otherImage); ?>" alt="" class="news-side-thumb" loading="lazy">
              <?php else: ?>
                <span class="news-side-thumb news-placeholder"><i class="bi bi-megaphone"></i></span>
              <?php endif; ?>
              <span class="news-side-text">
                <span class="news-side-title"><?= e($other['title']); ?></span>
                <span class="news-side-date"><i class="bi bi-calendar3 me-1"></i><?= e(fmt_date($other['created_at'])); ?></span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="card-body text-center text-secondary small py-4">
          <i class="bi bi-journal-x d-block fs-3 mb-2 opacity-50"></i>
          Belum ada berita lain.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
