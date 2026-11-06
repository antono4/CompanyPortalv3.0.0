<?php
/**
 * Company Portal - web installer.
 * Creates the schema, seeds the initial administrator and writes the DB config.
 */
declare(strict_types=1);

$root       = dirname(__DIR__);
$configFile = $root . '/config/database.php';

$step    = $_GET['step'] ?? 'form';
$errors  = [];
$success = false;

$defaults = [
    'host'   => '127.0.0.1',
    'port'   => '3306',
    'name'   => 'company_portal',
    'user'   => 'root',
    'pass'   => '',
    'admin_name'     => 'Administrator',
    'admin_username' => 'admin',
    'admin_email'    => 'admin@company.local',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($defaults as $key => $value) {
        $defaults[$key] = trim((string) ($_POST[$key] ?? $value));
    }
    $adminPassword = (string) ($_POST['admin_password'] ?? '');

    if ($defaults['host'] === '' || $defaults['name'] === '' || $defaults['user'] === '') {
        $errors[] = 'Host, nama database, dan user wajib diisi.';
    }
    if (preg_match('/^[A-Za-z0-9_]+$/', $defaults['name']) !== 1) {
        $errors[] = 'Nama database hanya boleh huruf, angka, dan underscore.';
    }
    if ($defaults['admin_username'] === '' || $defaults['admin_name'] === '' || $defaults['admin_email'] === '') {
        $errors[] = 'Data administrator wajib lengkap.';
    }
    if (mb_strlen($adminPassword) < 8) {
        $errors[] = 'Password administrator minimal 8 karakter.';
    }

    if ($errors === []) {
        try {
            $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $defaults['host'], (int) $defaults['port']);
            $pdo = new PDO($dsn, $defaults['user'], $defaults['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $pdo->exec(
                'CREATE DATABASE IF NOT EXISTS `' . $defaults['name'] . '`
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
            $pdo->exec('USE `' . $defaults['name'] . '`');

            $schema = file_get_contents($root . '/database/schema.sql');
            if ($schema === false) {
                throw new RuntimeException('Berkas schema.sql tidak ditemukan.');
            }
            $pdo->exec($schema);

            $now = date('Y-m-d H:i:s');

            // Departments
            $departments = [
                ['Human Resources', 'Mengelola SDM, rekrutmen, dan kesejahteraan karyawan.'],
                ['Finance', 'Akuntansi, penggajian, dan pelaporan keuangan.'],
                ['Information Technology', 'Infrastruktur TI, aplikasi, dan dukungan teknis.'],
                ['Marketing', 'Pemasaran, branding, dan komunikasi perusahaan.'],
            ];
            $insertDepartment = $pdo->prepare(
                'INSERT INTO departments (department_name, description, status, created_at, updated_at)
                 VALUES (?, ?, "active", ?, ?)'
            );
            foreach ($departments as $department) {
                $insertDepartment->execute([$department[0], $department[1], $now, $now]);
            }
            $departmentIds = $pdo->query('SELECT id, department_name FROM departments')->fetchAll();
            $departmentMap = [];
            foreach ($departmentIds as $row) {
                $departmentMap[$row['department_name']] = (int) $row['id'];
            }

            // Administrator
            $insertUser = $pdo->prepare(
                'INSERT INTO users (fullname, username, email, password, role, department_id, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, "admin", ?, "active", ?, ?)'
            );
            $insertUser->execute([
                $defaults['admin_name'],
                $defaults['admin_username'],
                $defaults['admin_email'],
                password_hash($adminPassword, PASSWORD_DEFAULT),
                $departmentMap['Information Technology'] ?? null,
                $now,
                $now,
            ]);
            $adminId = (int) $pdo->lastInsertId();

            // Announcements
            $insertAnnouncement = $pdo->prepare(
                'INSERT INTO announcements (title, description, created_by, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $insertAnnouncement->execute([
                'Selamat Datang di Portal Intranet',
                "Portal intranet perusahaan resmi digunakan. Silakan lengkapi profil Anda dan gunakan menu yang tersedia untuk mengakses dokumen serta pengumuman perusahaan.",
                $adminId,
                $now,
                $now,
            ]);
            $insertAnnouncement->execute([
                'Jadwal Maintenance Sistem',
                "Pemeliharaan sistem rutin dilakukan setiap akhir bulan pada pukul 22.00 - 00.00 WIB. Selama proses berlangsung, sebagian layanan mungkin tidak dapat diakses.",
                $adminId,
                $now,
                $now,
            ]);

            // Employees
            $employees = [
                ['EMP-001', 'Budi Santoso', 'budi.santoso@company.local', '+62 812 1111 0001', 'HR Manager', 'Human Resources', 'active'],
                ['EMP-002', 'Siti Rahayu', 'siti.rahayu@company.local', '+62 812 1111 0002', 'Finance Staff', 'Finance', 'active'],
                ['EMP-003', 'Agus Pratama', 'agus.pratama@company.local', '+62 812 1111 0003', 'IT Support', 'Information Technology', 'active'],
                ['EMP-004', 'Dewi Lestari', 'dewi.lestari@company.local', '+62 812 1111 0004', 'Marketing Specialist', 'Marketing', 'active'],
                ['EMP-005', 'Rizky Hidayat', 'rizky.hidayat@company.local', '+62 812 1111 0005', 'Software Engineer', 'Information Technology', 'active'],
                ['EMP-006', 'Maya Anggraini', 'maya.anggraini@company.local', '+62 812 1111 0006', 'Recruitment Officer', 'Human Resources', 'disabled'],
            ];
            $insertEmployee = $pdo->prepare(
                'INSERT INTO employees
                    (employee_code, fullname, email, phone, designation, department_id, joining_date, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($employees as $employee) {
                $insertEmployee->execute([
                    $employee[0], $employee[1], $employee[2], $employee[3], $employee[4],
                    $departmentMap[$employee[5]] ?? null,
                    '2023-01-16',
                    $employee[6],
                    $now,
                    $now,
                ]);
            }

            // Settings
            $settings = [
                'company_name'    => 'Company Portal',
                'company_email'   => 'info@company.local',
                'company_phone'   => '+62 21 5555 1234',
                'company_website' => '',
                'company_address' => 'Jl. Contoh Raya No. 1, Jakarta',
                'timezone'        => 'Asia/Jakarta',
            ];
            $insertSetting = $pdo->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            foreach ($settings as $key => $value) {
                $insertSetting->execute([$key, $value]);
            }

            // Write configuration
            $config = "<?php\n"
                . "/**\n"
                . " * Company Portal - Database configuration\n"
                . " * Generated by the installer on " . date('Y-m-d H:i:s') . "\n"
                . " */\n"
                . "return [\n"
                . "    'host'    => " . var_export($defaults['host'], true) . ",\n"
                . "    'port'    => " . (int) $defaults['port'] . ",\n"
                . "    'name'    => " . var_export($defaults['name'], true) . ",\n"
                . "    'user'    => " . var_export($defaults['user'], true) . ",\n"
                . "    'pass'    => " . var_export($defaults['pass'], true) . ",\n"
                . "    'charset' => 'utf8mb4',\n"
                . "];\n";

            if (file_put_contents($configFile, $config) === false) {
                throw new RuntimeException('Tidak dapat menulis config/database.php. Periksa izin folder.');
            }

            foreach (['storage/sessions', 'storage/logs'] as $dir) {
                if (!is_dir($root . '/' . $dir)) {
                    mkdir($root . '/' . $dir, 0775, true);
                }
            }

            $success = true;
            $step = 'done';
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Installer - Company Portal</title>
  <link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32.png">
  <link rel="shortcut icon" href="../favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/img/apple-touch-icon.png">
  <style>
    :root { color-scheme: light; }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; padding: 2rem 1rem;
      font-family: "Segoe UI", system-ui, sans-serif;
      background:
        radial-gradient(900px 500px at 10% -10%, rgba(126,29,178,.45), transparent 60%),
        radial-gradient(800px 450px at 110% 110%, rgba(79,35,155,.4), transparent 55%),
        linear-gradient(160deg, #14082b, #2b0f4f 55%, #14082b);
      display: flex; align-items: center; justify-content: center;
    }
    .wrap { width: 100%; max-width: 640px; }
    .card { background: #fff; border-radius: 16px; overflow: hidden; border-top: 4px solid #6a1b9a; box-shadow: 0 30px 60px -20px rgba(2,6,23,.6); }
    .head { padding: 1.75rem 1.75rem 1rem; background: linear-gradient(180deg, #faf7ff, #fff); }
    .head h1 { margin: .6rem 0 .25rem; font-size: 1.3rem; color: #2b0f4f; }
    .head p { margin: 0; color: #6b6478; font-size: .88rem; }
    .mark { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg,#7e1db2,#4f239b); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; }
    .body { padding: .5rem 1.75rem 1.75rem; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
    @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
    label { display: block; font-size: .8rem; font-weight: 600; color: #334155; margin-bottom: .3rem; }
    input { width: 100%; padding: .55rem .7rem; border: 1px solid #ded4ea; border-radius: 8px; font-size: .88rem; }
    input:focus { outline: none; border-color: #6a1b9a; box-shadow: 0 0 0 .2rem rgba(106,27,154,.14); }
    .full { grid-column: 1 / -1; }
    .section { margin: 1.1rem 0 .6rem; font-size: .72rem; font-weight: 700; letter-spacing: .1em; color: #6b6478; text-transform: uppercase; }
    button { width: 100%; margin-top: 1.2rem; padding: .7rem; border: 0; border-radius: 9px; background: linear-gradient(135deg,#6a1b9a,#7e1db2); color: #fff; font-weight: 600; font-size: .9rem; cursor: pointer; }
    button:hover { background: linear-gradient(135deg,#4f239b,#6a1b9a); }
    .alert { padding: .75rem .9rem; border-radius: 9px; font-size: .84rem; margin-bottom: 1rem; }
    .alert-error { background: #fef2f2; color: #991b1b; }
    .alert-ok { background: #f0fdf4; color: #166534; }
    .alert ul { margin: 0; padding-left: 1.1rem; }
    .foot { padding: .9rem; text-align: center; font-size: .75rem; color: #6b6478; background: #faf7ff; border-top: 1px solid #e9e3f2; }
    a.btn { display: inline-block; padding: .6rem 1rem; background: linear-gradient(135deg,#6a1b9a,#7e1db2); color: #fff; text-decoration: none; border-radius: 8px; font-size: .88rem; font-weight: 600; }
    .hint { font-size: .76rem; color: #6b6478; margin-top: .25rem; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="head">
      <div class="mark">&#127970;</div>
      <h1>Installer Company Portal</h1>
      <p>Siapkan database dan akun administrator.</p>
    </div>

    <div class="body">
      <?php if ($step === 'done' && $success): ?>
        <div class="alert alert-ok">
          Instalasi berhasil. Database, akun administrator, dan data contoh sudah dibuat.
        </div>
        <p style="font-size:.88rem;color:#334155">
          Login dengan username <strong><?= htmlspecialchars($defaults['admin_username'], ENT_QUOTES); ?></strong>
          dan password yang baru saja Anda tentukan.
        </p>
        <a class="btn" href="../login">Buka Halaman Login</a>
        <p class="hint" style="margin-top:1rem">
          Demi keamanan, hapus atau lindungi folder <code>install/</code> setelah selesai.
        </p>
      <?php else: ?>
        <?php if ($errors !== []): ?>
          <div class="alert alert-error">
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="?step=run">
          <div class="section">Koneksi Database</div>
          <div class="grid">
            <div>
              <label for="host">Host</label>
              <input id="host" name="host" value="<?= htmlspecialchars($defaults['host'], ENT_QUOTES); ?>" required>
            </div>
            <div>
              <label for="port">Port</label>
              <input id="port" name="port" value="<?= htmlspecialchars($defaults['port'], ENT_QUOTES); ?>" required>
            </div>
            <div>
              <label for="name">Nama Database</label>
              <input id="name" name="name" value="<?= htmlspecialchars($defaults['name'], ENT_QUOTES); ?>" required>
            </div>
            <div>
              <label for="user">User</label>
              <input id="user" name="user" value="<?= htmlspecialchars($defaults['user'], ENT_QUOTES); ?>" required>
            </div>
            <div class="full">
              <label for="pass">Password Database</label>
              <input id="pass" name="pass" type="password" value="">
              <div class="hint">Kosongkan jika user database tidak memakai password.</div>
            </div>
          </div>

          <div class="section">Akun Administrator</div>
          <div class="grid">
            <div class="full">
              <label for="admin_name">Nama Lengkap</label>
              <input id="admin_name" name="admin_name" value="<?= htmlspecialchars($defaults['admin_name'], ENT_QUOTES); ?>" required>
            </div>
            <div>
              <label for="admin_username">Username</label>
              <input id="admin_username" name="admin_username" value="<?= htmlspecialchars($defaults['admin_username'], ENT_QUOTES); ?>" required>
            </div>
            <div>
              <label for="admin_email">Email</label>
              <input id="admin_email" name="admin_email" type="email" value="<?= htmlspecialchars($defaults['admin_email'], ENT_QUOTES); ?>" required>
            </div>
            <div class="full">
              <label for="admin_password">Password</label>
              <input id="admin_password" name="admin_password" type="password" minlength="8" required>
              <div class="hint">Minimal 8 karakter.</div>
            </div>
          </div>

          <button type="submit">Pasang Sekarang</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="foot">Company Portal &middot; Installer</div>
  </div>
</div>
</body>
</html>
