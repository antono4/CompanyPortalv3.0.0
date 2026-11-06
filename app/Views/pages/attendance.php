<?php
declare(strict_types=1);

use App\Models\AttendanceRecord;
use App\Models\AttendanceSchedule;

require __DIR__ . '/../layouts/admin_header.php';

$primaryState = $primary !== null ? AttendanceSchedule::stateFor($primary, $serverNow) : null;
$hasCheckedIn = $record !== null && !empty($record['check_in']);
$hasCheckedOut = $record !== null && !empty($record['check_out']);
$hadirPercent = $summary['total_user'] > 0
    ? (int) round($summary['hadir'] / $summary['total_user'] * 100)
    : 0;

$stateBadge = static fn (string $state): string => match ($state) {
    'active'   => 'text-bg-success',
    'upcoming' => 'text-bg-secondary',
    default    => 'text-bg-dark',
};
?>

<div class="page-heading">
  <div>
    <p class="subtitle mb-0">Jadwal dan kehadiran yang diperbarui secara langsung.</p>
  </div>
  <div class="d-flex flex-wrap align-items-center gap-2">
    <span class="att-live-pill att-live-pill-dark">
      <span class="att-live-dot"></span>Realtime
    </span>
    <?php if (is_admin()): ?>
      <a href="<?= url('/attendance/schedules'); ?>" class="btn btn-outline-primary">
        <i class="bi bi-calendar2-week me-1"></i>Kelola Jadwal
      </a>
    <?php endif; ?>
  </div>
</div>

<div id="attendanceLive"
     data-status-url="<?= e(url('/attendance/status')); ?>"
     data-server-ts="<?= (int) $serverTs; ?>"
     data-server-time="<?= e($serverNow); ?>"
     data-record-check-in="<?= $hasCheckedIn ? '1' : '0'; ?>"
     data-record-check-out="<?= $hasCheckedOut ? '1' : '0'; ?>">

  <div class="row g-3">
    <div class="col-xl-4">
      <div class="card att-clock-card border-0 h-100">
        <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-center gap-2">
            <span class="att-live-pill"><span class="att-live-dot"></span>LIVE</span>
            <span class="small text-white-50" id="attDate"><?= e($dayLabel . ', ' . $dateLabel); ?></span>
          </div>

          <div class="att-clock" id="attClock"><?= e($serverNow); ?></div>
          <div class="small text-white-50 mb-4">
            <i class="bi bi-geo-alt me-1"></i>Waktu server (WIB) &middot; sinkron otomatis
          </div>

          <div class="mt-auto">
            <div class="d-flex justify-content-between small text-white-50 mb-1">
              <span>Kehadiran hari ini</span>
              <span id="attHadirRatio"><?= (int) $summary['hadir']; ?>/<?= (int) $summary['total_user']; ?></span>
            </div>
            <div class="progress att-progress" role="progressbar" aria-label="Kehadiran hari ini">
              <div class="progress-bar bg-info" id="attHadirBar" style="width: <?= $hadirPercent; ?>%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-8">
      <div class="card h-100">
        <div class="card-header card-toolbar">
          <h2 class="card-title"><i class="bi bi-calendar-check me-2 text-primary"></i>Jadwal Hari Ini</h2>
          <span class="badge badge-soft"><?= count($schedules); ?> shift</span>
        </div>

        <div class="card-body">
          <?php if ($schedules === []): ?>
            <div class="empty-state">
              <i class="bi bi-calendar-x"></i>
              <h6>Tidak ada jadwal hari ini</h6>
              <p>Jadwal absensi untuk hari <?= e($dayLabel); ?> belum diatur.</p>
            </div>
          <?php else: ?>
            <?php foreach ($schedules as $schedule): ?>
              <?php $state = AttendanceSchedule::stateFor($schedule, $serverNow); ?>
              <div class="att-shift"
                   data-schedule-id="<?= (int) $schedule['id']; ?>"
                   data-shift-start="<?= e($state['start']); ?>"
                   data-shift-end="<?= e($state['end']); ?>">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div>
                    <h3 class="h6 fw-bold mb-1"><?= e($schedule['name']); ?></h3>
                    <div class="small text-secondary">
                      <i class="bi bi-geo-alt me-1"></i><?= e($schedule['location'] ?: 'Kantor Pusat'); ?>
                    </div>
                  </div>
                  <span class="badge att-state-badge <?= $stateBadge($state['state']); ?>" data-shift-badge>
                    <?= e($state['label']); ?>
                  </span>
                </div>

                <div class="d-flex align-items-baseline gap-2 mt-3">
                  <span class="att-time" data-shift-start-label><?= e($state['start']); ?></span>
                  <span class="text-secondary">s.d.</span>
                  <span class="att-time" data-shift-end-label><?= e($state['end']); ?></span>
                  <span class="ms-auto small text-secondary" data-shift-message><?= e($state['message']); ?></span>
                </div>

                <div class="progress att-progress mt-2" role="progressbar" aria-label="Progres shift">
                  <div class="progress-bar" data-shift-progress style="width: <?= (int) $state['progress']; ?>%"></div>
                </div>

                <div class="small text-secondary mt-2">
                  <i class="bi bi-hourglass-split me-1"></i>
                  Toleransi <?= (int) $schedule['late_tolerance']; ?> menit &middot;
                  masuk paling lambat <?= e($state['late_after']); ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <div class="att-action-panel mt-3">
            <?php if ($hasCheckedIn): ?>
              <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="att-stamp">
                  <span class="att-stamp-label">Masuk</span>
                  <strong><?= e(substr((string) $record['check_in'], 0, 5)); ?></strong>
                </div>
                <div class="att-stamp">
                  <span class="att-stamp-label">Keluar</span>
                  <strong><?= $hasCheckedOut ? e(substr((string) $record['check_out'], 0, 5)) : '--:--'; ?></strong>
                </div>
                <span class="badge <?= AttendanceRecord::statusBadge($record['status']); ?>">
                  <?= e(AttendanceRecord::statusLabel($record['status'])); ?>
                </span>

                <div class="ms-auto">
                  <?php if (!$hasCheckedOut): ?>
                    <form method="post" action="<?= url('/attendance/check-out'); ?>">
                      <?= csrf_field(); ?>
                      <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-1"></i>Absen Keluar
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-success small fw-semibold">
                      <i class="bi bi-check-circle-fill me-1"></i>Absensi hari ini selesai
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            <?php elseif ($primary !== null): ?>
              <div class="d-flex flex-wrap align-items-center gap-3">
                <div>
                  <div class="fw-semibold">Siap memulai hari?</div>
                  <div class="small text-secondary">
                    Shift aktif: <?= e($primary['name']); ?> &middot;
                    <?= e($primaryState['start']); ?>&ndash;<?= e($primaryState['end']); ?>
                  </div>
                </div>
                <form method="post" action="<?= url('/attendance/check-in'); ?>" class="ms-auto">
                  <?= csrf_field(); ?>
                  <button type="submit" class="btn btn-success">
                    <i class="bi bi-fingerprint me-1"></i>Absen Masuk Sekarang
                  </button>
                </form>
              </div>
            <?php else: ?>
              <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Belum ada jadwal untuk hari ini, sehingga absen masuk belum dapat dilakukan.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-0">
    <div class="col-6 col-xl-3">
      <div class="card att-stat h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-person-check"></i></span>
          <div>
            <div class="att-stat-value" id="attSumHadir"><?= (int) $summary['hadir']; ?></div>
            <div class="small text-secondary">Hadir hari ini</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="card att-stat h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-alarm"></i></span>
          <div>
            <div class="att-stat-value" id="attSumTerlambat"><?= (int) $summary['terlambat']; ?></div>
            <div class="small text-secondary">Terlambat</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="card att-stat h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-person-dash"></i></span>
          <div>
            <div class="att-stat-value" id="attSumBelum"><?= (int) $summary['belum']; ?></div>
            <div class="small text-secondary">Belum absen</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="card att-stat h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></span>
          <div>
            <div class="att-stat-value" id="attSumTotal"><?= (int) $summary['total_user']; ?></div>
            <div class="small text-secondary">Karyawan aktif</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-0">
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header card-toolbar">
          <h2 class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Absensi Saya</h2>
          <span class="badge badge-soft"><?= count($history); ?> catatan</span>
        </div>
        <div class="card-body p-0">
          <?php if ($history !== []): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Jadwal</th>
                    <th class="text-center">Masuk</th>
                    <th class="text-center">Keluar</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($history as $row): ?>
                    <tr>
                      <td class="small fw-semibold text-nowrap"><?= e(fmt_date($row['attendance_date'])); ?></td>
                      <td class="small text-secondary"><?= e($row['schedule_name'] ?? '-'); ?></td>
                      <td class="text-center small">
                        <?= $row['check_in'] ? e(substr((string) $row['check_in'], 0, 5)) : '-'; ?>
                      </td>
                      <td class="text-center small">
                        <?= $row['check_out'] ? e(substr((string) $row['check_out'], 0, 5)) : '-'; ?>
                      </td>
                      <td>
                        <span class="badge <?= AttendanceRecord::statusBadge($row['status']); ?>">
                          <?= e(AttendanceRecord::statusLabel($row['status'])); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="empty-state py-4">
              <i class="bi bi-clock-history"></i>
              <h6>Belum ada riwayat</h6>
              <p>Catatan absensi Anda akan muncul di sini.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header card-toolbar">
          <h2 class="card-title">
            <span class="att-live-dot me-2"></span>Aktivitas Hari Ini
          </h2>
          <span class="badge badge-soft">Live</span>
        </div>
        <div class="card-body p-0" id="attFeed">
          <?php if ($feed !== []): ?>
            <?php foreach ($feed as $row): ?>
              <?php
              $feedUser = [
                  'id'       => (int) $row['user_id'],
                  'fullname' => $row['fullname'],
                  'photo_id' => $row['photo_id'],
              ];
              ?>
              <div class="att-feed-item">
                <?= avatar_html($feedUser, 'avatar-sm'); ?>
                <div class="flex-grow-1" style="min-width: 0;">
                  <div class="d-flex justify-content-between align-items-center gap-2">
                    <span class="fw-semibold small text-truncate"><?= e($row['fullname']); ?></span>
                    <span class="badge <?= AttendanceRecord::statusBadge($row['status']); ?>">
                      <?= e(AttendanceRecord::statusLabel($row['status'])); ?>
                    </span>
                  </div>
                  <div class="small text-secondary text-truncate"><?= e($row['schedule_name'] ?? '-'); ?></div>
                  <div class="att-feed-times">
                    <span><i class="bi bi-box-arrow-in-right"></i>
                      <?= $row['check_in'] ? e(substr((string) $row['check_in'], 0, 5)) : '--:--'; ?>
                    </span>
                    <span><i class="bi bi-box-arrow-right"></i>
                      <?= $row['check_out'] ? e(substr((string) $row['check_out'], 0, 5)) : '--:--'; ?>
                    </span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state py-4" id="attFeedEmpty">
              <i class="bi bi-broadcast"></i>
              <h6>Belum ada aktivitas</h6>
              <p>Absensi karyawan hari ini akan tampil di sini secara langsung.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header card-toolbar">
      <h2 class="card-title"><i class="bi bi-calendar3 me-2 text-primary"></i>Jadwal Mingguan</h2>
      <span class="badge badge-soft"><?= count($weekSchedules); ?> jadwal aktif</span>
    </div>
    <div class="card-body p-0">
      <?php if ($weekSchedules !== []): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Jadwal</th>
                <th>Hari</th>
                <th>Jam Kerja</th>
                <th class="text-center">Toleransi</th>
                <th>Lokasi</th>
                <th class="text-end">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($weekSchedules as $row): ?>
                <?php
                $rowDays = array_map('intval', explode(',', (string) $row['days_of_week']));
                $isToday = in_array((int) date('N'), $rowDays, true);
                ?>
                <tr class="<?= $isToday ? 'table-active' : ''; ?>">
                  <td>
                    <div class="fw-semibold"><?= e($row['name']); ?></div>
                    <?php if ($isToday): ?>
                      <span class="badge text-bg-primary mt-1">Hari ini</span>
                    <?php endif; ?>
                  </td>
                  <td class="small"><?= AttendanceSchedule::daysLabel($row['days_of_week']); ?></td>
                  <td class="small text-nowrap">
                    <?= e(substr((string) $row['start_time'], 0, 5)); ?> &ndash;
                    <?= e(substr((string) $row['end_time'], 0, 5)); ?>
                  </td>
                  <td class="text-center small"><?= (int) $row['late_tolerance']; ?> mnt</td>
                  <td class="small text-secondary"><?= e($row['location'] ?: '-'); ?></td>
                  <td class="text-end">
                    <span class="badge text-bg-success">Aktif</span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state py-4">
          <i class="bi bi-calendar2-week"></i>
          <h6>Belum ada jadwal</h6>
          <p>Administrator dapat menambahkan jadwal absensi melalui menu Kelola Jadwal.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  var root = document.getElementById('attendanceLive');
  if (!root) return;

  var statusUrl = root.getAttribute('data-status-url');
  var clockEl = document.getElementById('attClock');
  var dateEl = document.getElementById('attDate');
  var offset = parseInt(root.getAttribute('data-server-ts'), 10) * 1000 - Date.now();
  var reloaded = false;

  function pad(value) { return value < 10 ? '0' + value : String(value); }

  function currentDate() { return new Date(Date.now() + offset); }

  function minutesOfDay() {
    var now = currentDate();
    return now.getHours() * 60 + now.getMinutes() + now.getSeconds() / 60;
  }

  function toMinutes(value) {
    var parts = String(value).split(':');
    return (parseInt(parts[0], 10) || 0) * 60 + (parseInt(parts[1], 10) || 0);
  }

  function humanDuration(minutes) {
    minutes = Math.max(0, Math.round(minutes));
    var hours = Math.floor(minutes / 60);
    var rest = minutes % 60;
    if (hours > 0 && rest > 0) return hours + ' jam ' + rest + ' menit';
    if (hours > 0) return hours + ' jam';
    return rest + ' menit';
  }

  function renderClock() {
    var now = currentDate();
    if (clockEl) {
      clockEl.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    }
  }

  function renderShifts() {
    var now = minutesOfDay();

    document.querySelectorAll('[data-shift-start]').forEach(function (shift) {
      var start = toMinutes(shift.getAttribute('data-shift-start'));
      var end = toMinutes(shift.getAttribute('data-shift-end'));
      var total = Math.max(1, end - start);
      var progress = 0;
      var state = 'upcoming';
      var label = 'Belum dimulai';
      var message = '';

      if (now < start) {
        message = 'Dimulai dalam ' + humanDuration(start - now);
      } else if (now >= end) {
        progress = 100;
        state = 'ended';
        label = 'Selesai';
        message = 'Shift telah selesai';
      } else {
        progress = Math.min(100, Math.round((now - start) / total * 100));
        state = 'active';
        label = 'Berlangsung';
        message = 'Sisa ' + humanDuration(end - now);
      }

      var badge = shift.querySelector('[data-shift-badge]');
      if (badge) {
        badge.textContent = label;
        badge.className = 'badge att-state-badge ' + (state === 'active' ? 'text-bg-success' : (state === 'upcoming' ? 'text-bg-secondary' : 'text-bg-dark'));
      }

      var messageEl = shift.querySelector('[data-shift-message]');
      if (messageEl) messageEl.textContent = message;

      var bar = shift.querySelector('[data-shift-progress]');
      if (bar) bar.style.width = progress + '%';
    });
  }

  function renderSummary(summary) {
    if (!summary) return;
    var map = {
      attSumHadir: summary.hadir,
      attSumTerlambat: summary.terlambat,
      attSumBelum: summary.belum,
      attSumTotal: summary.total_user
    };
    Object.keys(map).forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.textContent = map[id];
    });

    var ratio = document.getElementById('attHadirRatio');
    if (ratio) ratio.textContent = summary.hadir + '/' + summary.total_user;

    var bar = document.getElementById('attHadirBar');
    if (bar) {
      var percent = summary.total_user > 0 ? Math.round(summary.hadir / summary.total_user * 100) : 0;
      bar.style.width = percent + '%';
    }
  }

  function feedItem(item) {
    var wrap = document.createElement('div');
    wrap.className = 'att-feed-item';

    var avatar = document.createElement('div');
    avatar.innerHTML = item.avatar || '';
    if (avatar.firstElementChild) wrap.appendChild(avatar.firstElementChild);

    var body = document.createElement('div');
    body.className = 'flex-grow-1';
    body.style.minWidth = '0';

    var top = document.createElement('div');
    top.className = 'd-flex justify-content-between align-items-center gap-2';

    var name = document.createElement('span');
    name.className = 'fw-semibold small text-truncate';
    name.textContent = item.name;

    var badge = document.createElement('span');
    badge.className = 'badge ' + item.badge;
    badge.textContent = item.status_label;

    top.appendChild(name);
    top.appendChild(badge);

    var schedule = document.createElement('div');
    schedule.className = 'small text-secondary text-truncate';
    schedule.textContent = item.schedule;

    var times = document.createElement('div');
    times.className = 'att-feed-times';
    times.innerHTML = '<span><i class="bi bi-box-arrow-in-right"></i>' + (item.check_in || '--:--') +
      '</span><span><i class="bi bi-box-arrow-right"></i>' + (item.check_out || '--:--') + '</span>';

    body.appendChild(top);
    body.appendChild(schedule);
    body.appendChild(times);
    wrap.appendChild(body);

    return wrap;
  }

  function renderFeed(items) {
    var feed = document.getElementById('attFeed');
    if (!feed) return;
    feed.innerHTML = '';

    if (!items || !items.length) {
      var empty = document.createElement('div');
      empty.className = 'empty-state py-4';
      empty.innerHTML = '<i class="bi bi-broadcast"></i><h6>Belum ada aktivitas</h6>' +
        '<p>Absensi karyawan hari ini akan tampil di sini secara langsung.</p>';
      feed.appendChild(empty);
      return;
    }

    items.forEach(function (item) {
      feed.appendChild(feedItem(item));
    });
  }

  function refresh() {
    fetch(statusUrl, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (response) { return response.ok ? response.json() : null; })
      .then(function (data) {
        if (!data) return;

        if (data.timestamp) {
          offset = data.timestamp * 1000 - Date.now();
        }
        if (dateEl && data.day_label) {
          dateEl.textContent = data.day_label + ', ' + data.date_label;
        }

        renderSummary(data.summary);
        renderFeed(data.feed);

        var hasIn = !!(data.record && data.record.check_in);
        var hasOut = !!(data.record && data.record.check_out);
        var initialIn = root.getAttribute('data-record-check-in') === '1';
        var initialOut = root.getAttribute('data-record-check-out') === '1';

        if (!reloaded && ((hasIn && !initialIn) || (hasOut && !initialOut))) {
          reloaded = true;
          location.reload();
        }
      })
      .catch(function () { /* keep the last known state on network errors */ });
  }

  renderClock();
  renderShifts();
  setInterval(function () {
    renderClock();
    renderShifts();
  }, 1000);
  setInterval(refresh, 10000);
  refresh();
})();
</script>

<?php require __DIR__ . '/../layouts/admin_footer.php'; ?>
