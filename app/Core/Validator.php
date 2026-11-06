<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Tiny rule based validator.
 * Rules: required|email|min:N|max:N|in:a,b,c|numeric|date
 */
final class Validator
{
    private static array $errors = [];

    public static function validate(array $rules): array
    {
        self::$errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = Input::post($field);
            $value = is_scalar($value) ? trim((string) $value) : '';

            foreach (explode('|', $fieldRules) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $label = ucwords(str_replace('_', ' ', $field));

                switch ($name) {
                    case 'required':
                        if ($value === '') {
                            self::add($field, "{$label} wajib diisi.");
                        }
                        break;
                    case 'email':
                        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            self::add($field, "Format {$label} tidak valid.");
                        }
                        break;
                    case 'numeric':
                        if ($value !== '' && !is_numeric($value)) {
                            self::add($field, "{$label} harus berupa angka.");
                        }
                        break;
                    case 'min':
                        if ($value !== '' && mb_strlen($value) < (int) $param) {
                            self::add($field, "{$label} minimal {$param} karakter.");
                        }
                        break;
                    case 'max':
                        if (mb_strlen($value) > (int) $param) {
                            self::add($field, "{$label} maksimal {$param} karakter.");
                        }
                        break;
                    case 'in':
                        $allowed = explode(',', (string) $param);
                        if ($value !== '' && !in_array($value, $allowed, true)) {
                            self::add($field, "{$label} tidak valid.");
                        }
                        break;
                    case 'date':
                        if ($value !== '' && strtotime($value) === false) {
                            self::add($field, "{$label} bukan tanggal yang valid.");
                        }
                        break;
                }
            }
        }

        return self::$errors;
    }

    public static function errors(): array
    {
        return self::$errors;
    }

    private static function add(string $field, string $message): void
    {
        if (!in_array($message, self::$errors, true)) {
            self::$errors[$field] = $message;
        }
    }
}
