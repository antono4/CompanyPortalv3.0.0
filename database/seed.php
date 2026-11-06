<?php
/**
 * Company Portal - CLI database seeder.
 * Usage: php database/seed.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

$root   = dirname(__DIR__);
$config = require $root . '/config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;charset=%s',
    $config['host'],
    (int) ($config['port'] ?? 3306),
    $config['charset'] ?? 'utf8mb4'
);

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'Connection failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$config['name']}`");

$schema = file_get_contents($root . '/database/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "schema.sql not found\n");
    exit(1);
}
$pdo->exec($schema);
echo "Schema created.\n";

$now = date('Y-m-d H:i:s');

$departments = [
    ['Human Resources', 'Mengelola SDM, rekrutmen, dan kesejahteraan karyawan.'],
    ['Finance', 'Akuntansi, penggajian, dan pelaporan keuangan.'],
    ['Information Technology', 'Infrastruktur TI, aplikasi, dan dukungan teknis.'],
    ['Marketing', 'Pemasaran, branding, dan komunikasi perusahaan.'],
];

$insertDepartment = $pdo->prepare(
    'INSERT INTO departments (department_name, description, status, created_at, updated_at) VALUES (?, ?, "active", ?, ?)'
);
foreach ($departments as $department) {
    $insertDepartment->execute([$department[0], $department[1], $now, $now]);
}
echo 'Departments: ' . count($departments) . "\n";

$departmentMap = [];
foreach ($pdo->query('SELECT id, department_name FROM departments') as $row) {
    $departmentMap[$row['department_name']] = (int) $row['id'];
}

$adminUsername = 'admin';
$adminPassword = 'admin123';

$insertUser = $pdo->prepare(
    'INSERT INTO users (fullname, username, email, password, role, department_id, status, created_at, updated_at)
     VALUES (?, ?, ?, ?, "admin", ?, "active", ?, ?)'
);
$insertUser->execute([
    'Administrator',
    $adminUsername,
    'admin@company.local',
    password_hash($adminPassword, PASSWORD_DEFAULT),
    $departmentMap['Information Technology'] ?? null,
    $now,
    $now,
]);
$adminId = (int) $pdo->lastInsertId();
echo "Admin user created: {$adminUsername} / {$adminPassword}\n";

$insertAnnouncement = $pdo->prepare(
    'INSERT INTO announcements (title, description, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?)'
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
echo "Announcements: 2\n";

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
echo 'Employees: ' . count($employees) . "\n";

$schedules = [
    ['Shift Reguler', '1,2,3,4,5', '08:00:00', '17:00:00', 15, 'Kantor Pusat', 1],
    ['Shift Sabtu', '6', '08:00:00', '12:00:00', 15, 'Kantor Pusat', 1],
];
$insertSchedule = $pdo->prepare(
    'INSERT INTO attendance_schedules
        (name, days_of_week, start_time, end_time, late_tolerance, location, is_active, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
foreach ($schedules as $schedule) {
    $insertSchedule->execute([
        $schedule[0], $schedule[1], $schedule[2], $schedule[3],
        $schedule[4], $schedule[5], $schedule[6], $now, $now,
    ]);
}
echo 'Attendance schedules: ' . count($schedules) . "\n";

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
echo "Settings seeded.\n";

foreach (['storage/sessions', 'storage/logs'] as $dir) {
    if (!is_dir($root . '/' . $dir)) {
        mkdir($root . '/' . $dir, 0775, true);
    }
}

echo "Done.\n";
