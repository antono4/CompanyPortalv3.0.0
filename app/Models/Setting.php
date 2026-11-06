<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Setting extends Model
{
    protected string $table = 'settings';
    protected array $fillable = ['setting_key', 'setting_value'];

    /** @return array<string, string> */
    public function allKeyed(): array
    {
        $rows = $this->db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $out  = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    }

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        return $value === false ? $default : (string) $value;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value]);
    }

    public static function defaults(): array
    {
        return [
            'company_name'    => 'Company Portal',
            'company_email'   => 'info@company.com',
            'company_phone'   => '+62 21 0000 0000',
            'company_website' => '',
            'company_address' => '',
            'timezone'        => 'Asia/Jakarta',
        ];
    }
}
