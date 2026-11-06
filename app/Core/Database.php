<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Lazy PDO connection singleton.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $configFile = APP_ROOT . '/config/database.php';
        if (!is_file($configFile)) {
            throw new RuntimeException('Konfigurasi database belum ada. Buka /install/.');
        }
        $config = require $configFile;

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'] ?? '127.0.0.1',
            (int) ($config['port'] ?? 3306),
            $config['name'] ?? 'company_portal',
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            self::$pdo = new PDO($dsn, $config['user'] ?? 'root', $config['pass'] ?? '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Koneksi database gagal. Periksa konfigurasi atau jalankan installer.',
                0,
                $e
            );
        }

        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        return $statement;
    }
}
