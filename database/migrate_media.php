<?php
/**
 * Company Portal - one-off migration: move uploaded files into MySQL.
 *
 * Usage: php database/migrate_media.php
 *
 * The script is safe to re-run: it only touches legacy path columns and rows
 * whose media_id is still empty. It refuses to drop the legacy columns when a
 * referenced file cannot be found.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

$root   = dirname(__DIR__);
$config = require $root . '/config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $config['host'],
    (int) ($config['port'] ?? 3306),
    $config['name'] ?? 'company_portal',
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

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function indexExists(PDO $pdo, string $table, string $index): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?'
    );
    $stmt->execute([$table, $index]);
    return (int) $stmt->fetchColumn() > 0;
}

function constraintExists(PDO $pdo, string $table, string $name): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
          WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?'
    );
    $stmt->execute([$table, $name]);
    return (int) $stmt->fetchColumn() > 0;
}

if (!tableExists($pdo, 'media')) {
    $pdo->exec(
        "CREATE TABLE media (
            id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            kind          VARCHAR(20) NOT NULL DEFAULT 'file',
            original_name VARCHAR(190) NOT NULL,
            mime_type     VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
            filesize      BIGINT UNSIGNED NOT NULL DEFAULT 0,
            data          LONGBLOB NOT NULL,
            uploaded_by   INT UNSIGNED NULL,
            created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_media_kind (kind),
            KEY idx_media_uploader (uploaded_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    echo "Created table media.\n";
}

foreach ([
    ['users', 'photo_id', 'BIGINT UNSIGNED NULL AFTER photo'],
    ['announcements', 'image_id', 'BIGINT UNSIGNED NULL AFTER image'],
    ['documents', 'media_id', 'BIGINT UNSIGNED NULL AFTER original_name'],
    ['uploads', 'media_id', 'BIGINT UNSIGNED NULL AFTER original_name'],
] as [$table, $column, $definition]) {
    if (tableExists($pdo, $table) && !columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        echo "Added {$table}.{$column}.\n";
    }
}

$migrated = 0;
$missing  = [];

/**
 * Insert one file into media and return the new id, or null when the file is gone.
 */
function storeLegacyFile(
    PDO $pdo,
    string $root,
    string $relativePath,
    string $kind,
    string $originalName,
    int $fallbackSize,
    string $fallbackMime,
    ?int $uploadedBy,
    array &$missing
): ?int {
    $path = $root . '/storage/uploads/' . ltrim($relativePath, '/\\');
    if (!is_file($path)) {
        $missing[] = $relativePath;
        return null;
    }

    $data = file_get_contents($path);
    if ($data === false) {
        $missing[] = $relativePath;
        return null;
    }

    $mime = mime_content_type($path);
    if (!is_string($mime) || $mime === '') {
        $mime = $fallbackMime !== '' ? $fallbackMime : 'application/octet-stream';
    }

    $stmt = $pdo->prepare(
        'INSERT INTO media (kind, original_name, mime_type, filesize, data, uploaded_by)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bindValue(1, $kind);
    $stmt->bindValue(2, mb_substr($originalName !== '' ? $originalName : basename($path), 0, 190));
    $stmt->bindValue(3, $mime);
    $stmt->bindValue(4, $fallbackSize > 0 ? $fallbackSize : strlen($data), PDO::PARAM_INT);
    $stmt->bindValue(5, $data, PDO::PARAM_LOB);
    $stmt->bindValue(6, $uploadedBy, $uploadedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->execute();

    return (int) $pdo->lastInsertId();
}

if (columnExists($pdo, 'users', 'photo')) {
    $rows = $pdo->query("SELECT id, photo FROM users WHERE photo IS NOT NULL AND photo <> '' AND photo_id IS NULL")->fetchAll();
    foreach ($rows as $row) {
        $id = storeLegacyFile(
            $pdo,
            $root,
            (string) $row['photo'],
            'avatar',
            basename((string) $row['photo']),
            0,
            'image/jpeg',
            (int) $row['id'],
            $missing
        );
        if ($id !== null) {
            $pdo->prepare('UPDATE users SET photo_id = ? WHERE id = ?')->execute([$id, (int) $row['id']]);
            $migrated++;
        }
    }
}

if (columnExists($pdo, 'announcements', 'image')) {
    $rows = $pdo->query("SELECT id, image, created_by FROM announcements WHERE image IS NOT NULL AND image <> '' AND image_id IS NULL")->fetchAll();
    foreach ($rows as $row) {
        $id = storeLegacyFile(
            $pdo,
            $root,
            (string) $row['image'],
            'news',
            basename((string) $row['image']),
            0,
            'image/jpeg',
            $row['created_by'] !== null ? (int) $row['created_by'] : null,
            $missing
        );
        if ($id !== null) {
            $pdo->prepare('UPDATE announcements SET image_id = ? WHERE id = ?')->execute([$id, (int) $row['id']]);
            $migrated++;
        }
    }
}

foreach ([['documents', 'document'], ['uploads', 'upload']] as [$table, $kind]) {
    if (!columnExists($pdo, $table, 'file_path')) {
        continue;
    }
    $rows = $pdo->query(
        "SELECT id, file_path, original_name, filesize, filetype, uploaded_by
           FROM `{$table}`
          WHERE file_path IS NOT NULL AND file_path <> '' AND media_id IS NULL"
    )->fetchAll();
    foreach ($rows as $row) {
        $id = storeLegacyFile(
            $pdo,
            $root,
            (string) $row['file_path'],
            $kind,
            (string) ($row['original_name'] ?? ''),
            (int) ($row['filesize'] ?? 0),
            (string) ($row['filetype'] ?? ''),
            $row['uploaded_by'] !== null ? (int) $row['uploaded_by'] : null,
            $missing
        );
        if ($id !== null) {
            $pdo->prepare("UPDATE `{$table}` SET media_id = ? WHERE id = ?")->execute([$id, (int) $row['id']]);
            $migrated++;
        }
    }
}

echo "Migrated files: {$migrated}\n";

if ($missing !== []) {
    fwrite(STDERR, "Missing files, legacy columns were kept:\n - " . implode("\n - ", array_unique($missing)) . "\n");
    exit(1);
}

$pdo->beginTransaction();
try {
    if (columnExists($pdo, 'users', 'photo')) {
        $pdo->exec('ALTER TABLE users DROP COLUMN photo');
    }
    if (columnExists($pdo, 'announcements', 'image')) {
        $pdo->exec('ALTER TABLE announcements DROP COLUMN image');
    }
    foreach (['documents', 'uploads'] as $table) {
        if (columnExists($pdo, $table, 'file_path')) {
            $pdo->exec("ALTER TABLE `{$table}` DROP COLUMN file_path");
        }
    }
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    echo "Dropped legacy path columns.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Could not drop legacy columns: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$constraints = [
    ['users', 'photo_id', 'idx_users_photo', 'fk_users_photo'],
    ['announcements', 'image_id', 'idx_announcements_image', 'fk_announcements_image'],
    ['documents', 'media_id', 'idx_documents_media', 'fk_documents_media'],
    ['uploads', 'media_id', 'idx_uploads_media', 'fk_uploads_media'],
];
foreach ($constraints as [$table, $column, $index, $constraint]) {
    if (!indexExists($pdo, $table, $index)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD KEY `{$index}` (`{$column}`)");
    }
    if (!constraintExists($pdo, $table, $constraint)) {
        $pdo->exec(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraint}`
             FOREIGN KEY (`{$column}`) REFERENCES media (id)
             ON DELETE SET NULL ON UPDATE CASCADE"
        );
    }
}
echo "Indexes and foreign keys ready.\n";

$total = (int) $pdo->query('SELECT COUNT(*) FROM media')->fetchColumn();
echo "Media rows: {$total}\n";
echo "Migration complete. Verify the app, then delete the old files under storage/uploads/avatars and news.\n";
