<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Validates uploaded files and returns their bytes for storage in MySQL.
 *
 * No file is written to disk: the returned `data` value is inserted into the
 * `media` table as a LONGBLOB.
 */
final class Uploader
{
    /** @var array<string, string> */
    private const MIME_BY_EXT = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip'  => 'application/zip',
        'rar'  => 'application/vnd.rar',
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ];

    private const MAX_BYTES = 10 * 1024 * 1024;

    private const MAX_IMAGE_BYTES = 3 * 1024 * 1024;

    /** @var array<string, string> */
    private const IMAGE_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * Validate a document / Upload Center file (max 10 MB).
     *
     * @return array{filename:string, original_name:string, filesize:int, filetype:string, data:string}
     */
    public static function readDocument(array $file): array
    {
        self::assertUploaded($file, 'file');

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0) {
            throw new RuntimeException('File kosong atau tidak valid.');
        }
        if ($size > self::MAX_BYTES) {
            throw new RuntimeException('Ukuran file maksimal 10 MB.');
        }

        $original = (string) ($file['name'] ?? 'file');
        $ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        if (!isset(self::MIME_BY_EXT[$ext])) {
            throw new RuntimeException('Ekstensi file tidak diizinkan: .' . $ext);
        }

        $data = @file_get_contents((string) $file['tmp_name']);
        if ($data === false) {
            throw new RuntimeException('File gagal dibaca dari server.');
        }

        return self::payload($original, $ext, $size, self::MIME_BY_EXT[$ext], $data);
    }

    /**
     * Validate a profile photo or announcement image (max 3 MB).
     *
     * @return array{filename:string, original_name:string, filesize:int, filetype:string, data:string}
     */
    public static function readImage(array $file): array
    {
        self::assertUploaded($file, 'foto');

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0) {
            throw new RuntimeException('File foto kosong atau tidak valid.');
        }
        if ($size > self::MAX_IMAGE_BYTES) {
            throw new RuntimeException('Ukuran foto maksimal 3 MB.');
        }

        $tmp  = (string) $file['tmp_name'];
        $info = @getimagesize($tmp);
        $mime = is_array($info) ? ($info['mime'] ?? '') : '';

        if (!isset(self::IMAGE_MIME[$mime])) {
            throw new RuntimeException('Format foto harus JPG, PNG, GIF, atau WEBP.');
        }

        $data = @file_get_contents($tmp);
        if ($data === false) {
            throw new RuntimeException('File foto gagal dibaca dari server.');
        }

        $original = (string) ($file['name'] ?? 'foto.' . self::IMAGE_MIME[$mime]);

        return self::payload($original, self::IMAGE_MIME[$mime], $size, $mime, $data);
    }

    /**
     * @return array{filename:string, original_name:string, filesize:int, filetype:string, data:string}
     */
    private static function payload(string $original, string $ext, int $size, string $mime, string $data): array
    {
        return [
            'filename'      => date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext,
            'original_name' => mb_substr($original, 0, 190),
            'filesize'      => $size,
            'filetype'      => $mime,
            'data'          => $data,
        ];
    }

    private static function assertUploaded(array $file, string $label): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Silakan pilih ' . $label . ' yang akan diunggah.');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload gagal (kode ' . (int) $file['error'] . ').');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('File upload tidak valid.');
        }
    }
}
