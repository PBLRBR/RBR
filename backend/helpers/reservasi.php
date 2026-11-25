<?php

function tolakReservasiMenungguYangBentrok(
    PDO $pdo,
    int $idReservasi,
    int $idTempat,
    string $tanggal,
    string $jamMulai,
    string $jamSelesai
): int {
    $stmt = $pdo->prepare(
        "UPDATE reservasi
         SET status_reservasi = 'ditolak',
             alasan_penolakan = :alasan
         WHERE id_tempat = :id_tempat
           AND tanggal_acara = :tanggal
           AND status_reservasi = 'menunggu'
           AND id_reservasi <> :id
           AND jam_mulai < :jam_selesai
           AND jam_selesai > :jam_mulai"
    );

    $stmt->execute([
        'alasan' => 'Jadwal bertabrakan dengan reservasi lain yang telah disetujui.',
        'id_tempat' => $idTempat,
        'tanggal' => $tanggal,
        'id' => $idReservasi,
        'jam_selesai' => $jamSelesai,
        'jam_mulai' => $jamMulai,
    ]);

    return $stmt->rowCount();
}