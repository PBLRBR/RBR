<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/database.php';

/** Data access for the public book catalog. */
final class Book
{
    private static function columnExists(string $column): bool
    {
        static $columns = null;
        if ($columns === null) {
            $statement = simart_db()->prepare(
                'SELECT column_name FROM information_schema.columns '
                . "WHERE table_schema = current_schema() AND table_name = 'buku'"
            );
            $statement->execute();
            $columns = array_flip($statement->fetchAll(PDO::FETCH_COLUMN));
        }

        return isset($columns[$column]);
    }

    private static function typeExpression(string $alias = 'b'): string
    {
        return self::columnExists('jenis_buku')
            ? "{$alias}.jenis_buku"
            : "CASE WHEN {$alias}.bisa_dijual THEN 'dijual' ELSE 'koleksi' END";
    }

    private static function activeFilter(string $alias = 'b'): string
    {
        return self::columnExists('is_archived') ? "{$alias}.is_archived = FALSE" : 'TRUE';
    }

    public static function categories(): array
    {
        return simart_db()->query(
            'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori'
        )->fetchAll();
    }

    public static function published(): array
    {
        return simart_db()->query(
            'SELECT b.id_buku, b.judul, b.penulis, ' . self::typeExpression() . ' AS jenis_buku, '
            . 'b.harga, b.stok, b.foto_sampul, '
            . 'k.id_kategori, k.nama_kategori FROM buku b '
            . 'JOIN kategori k ON k.id_kategori = b.id_kategori '
            . 'WHERE ' . self::activeFilter() . ' ORDER BY b.judul'
        )->fetchAll();
    }

    public static function findPublished(int $id): ?array
    {
        $query = simart_db()->prepare(
            'SELECT b.*, ' . self::typeExpression() . ' AS jenis_buku, k.nama_kategori FROM buku b '
            . 'JOIN kategori k ON k.id_kategori = b.id_kategori '
            . 'WHERE b.id_buku = :id AND ' . self::activeFilter()
        );
        $query->execute([':id' => $id]);
        $book = $query->fetch();

        return is_array($book) ? $book : null;
    }

    public static function saleable(): array
    {
        return simart_db()->query(
            'SELECT id_buku, judul, harga, stok FROM buku '
            . 'WHERE ' . self::activeFilter('buku') . ' AND '
            . (self::columnExists('jenis_buku') ? "jenis_buku = 'dijual'" : 'bisa_dijual = TRUE')
            . ' AND stok > 0 ORDER BY judul'
        )->fetchAll();
    }

    public static function findActive(int $id): ?array
    {
        $query = simart_db()->prepare(
            'SELECT buku.*, ' . self::typeExpression('buku') . ' AS jenis_buku FROM buku '
            . 'WHERE id_buku = :id AND ' . self::activeFilter('buku')
        );
        $query->execute([':id' => $id]);
        $book = $query->fetch();

        return is_array($book) ? $book : null;
    }

    public static function categoriesWithDescriptions(): array
    {
        return simart_db()->query(
            'SELECT id_kategori, nama_kategori, deskripsi FROM kategori ORDER BY nama_kategori'
        )->fetchAll();
    }
}
