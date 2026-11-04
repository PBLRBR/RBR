<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/database.php';

/** Read operations for member reservation pages. */
final class Reservation
{
    public static function activeRooms(): array
    {
        return simart_db()->query(
            "SELECT id_tempat, nama_tempat, kapasitas FROM tempat WHERE status = 'aktif' ORDER BY nama_tempat"
        )->fetchAll();
    }

    public static function allPlaces(): array
    {
        return simart_db()->query(
            'SELECT id_tempat, nama_tempat, deskripsi, kapasitas, status '
            . 'FROM tempat ORDER BY nama_tempat'
        )->fetchAll();
    }

    public static function createPlace(string $name, ?string $description, int $capacity): void
    {
        $statement = simart_db()->prepare(
            "INSERT INTO tempat (nama_tempat, deskripsi, kapasitas, status) "
            . "VALUES (:nama, :deskripsi, :kapasitas, 'aktif')"
        );
        $statement->execute([
            ':nama' => $name,
            ':deskripsi' => $description,
            ':kapasitas' => $capacity,
        ]);
    }

    public static function upcomingApproved(): array
    {
        return simart_db()->query(
            "SELECT r.tanggal_acara::text AS tanggal, r.jam_mulai, r.jam_selesai, r.nama_acara, "
            . "t.nama_tempat FROM reservasi r JOIN tempat t ON t.id_tempat = r.id_tempat "
            . "WHERE r.status_reservasi = 'disetujui' AND r.tanggal_acara >= CURRENT_DATE "
            . 'ORDER BY r.tanggal_acara, r.jam_mulai LIMIT 200'
        )->fetchAll();
    }

    public static function forMember(int $memberId): array
    {
        $query = simart_db()->prepare(
            'SELECT r.dibuat_pada, r.tanggal_acara, r.jam_mulai, r.jam_selesai, '
            . 'r.nama_acara, r.status_reservasi, t.nama_tempat '
            . 'FROM reservasi r JOIN tempat t ON t.id_tempat = r.id_tempat '
            . 'WHERE r.id_user = :id_user ORDER BY r.dibuat_pada DESC'
        );
        $query->execute([':id_user' => $memberId]);

        return $query->fetchAll();
    }
}
