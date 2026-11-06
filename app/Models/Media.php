<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Binary file contents stored directly in MySQL (LONGBLOB).
 *
 * Nothing in this module touches the filesystem, so profile photos,
 * announcement images, documents and Upload Center files all live in the database.
 */
final class Media extends Model
{
    protected string $table = 'media';
    protected array $fillable = [
        'kind', 'original_name', 'mime_type', 'filesize', 'data', 'uploaded_by',
    ];

    /**
     * Persist validated upload data and return the new media row metadata.
     *
     * @param array{filename:string, original_name:string, filesize:int, filetype:string, data:string} $file
     * @return array{id:int, filename:string, original_name:string, filesize:int, filetype:string}
     */
    public function store(array $file, string $kind, ?int $uploadedBy = null): array
    {
        $pdo  = $this->db();
        $stmt = $pdo->prepare(
            'INSERT INTO media (kind, original_name, mime_type, filesize, data, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->bindValue(1, $kind);
        $stmt->bindValue(2, mb_substr($file['original_name'], 0, 190));
        $stmt->bindValue(3, $file['filetype']);
        $stmt->bindValue(4, (int) $file['filesize'], PDO::PARAM_INT);
        $stmt->bindValue(5, $file['data'], PDO::PARAM_LOB);
        $stmt->bindValue(6, $uploadedBy, $uploadedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return [
            'id'            => (int) $pdo->lastInsertId(),
            'filename'      => $file['filename'],
            'original_name' => $file['original_name'],
            'filesize'      => (int) $file['filesize'],
            'filetype'      => $file['filetype'],
        ];
    }

    /** Fetch metadata plus the binary payload. */
    public function findWithData(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM media WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** Metadata without loading the BLOB into memory (lists, checks). */
    public function findMeta(int $id): ?array
    {
        $stmt = $this->db()->prepare(
            'SELECT id, kind, original_name, mime_type, filesize, uploaded_by, created_at
               FROM media WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
