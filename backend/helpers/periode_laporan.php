<?php

declare(strict_types=1);

/**
 * Rentang tanggal inklusif, default: bulan ini (zona waktu WIB).
 * Tidak mengubah timezone koneksi PostgreSQL.
 *
 * @return array{jenis:string,mulai:string,selesai:string}
 */
function rentangPeriodeLaporan(array $query): array
{
    $zona = new DateTimeZone('Asia/Jakarta');
    $hariIni = new DateTimeImmutable('today', $zona);
    $rawPeriode = $query['periode'] ?? null;

    if ($rawPeriode !== null && !is_string($rawPeriode)) {
        throw new InvalidArgumentException('Parameter periode tidak valid.');
    }

    $adaTanggal = array_key_exists('tanggal_mulai', $query)
        || array_key_exists('tanggal_selesai', $query);
    $jenis = $rawPeriode ?? ($adaTanggal ? 'kustom' : 'bulan_ini');

    switch ($jenis) {
        case 'hari_ini':
            $mulai = $hariIni;
            $selesai = $hariIni;
            break;

        case '7_hari':
            $mulai = $hariIni->modify('-6 days');
            $selesai = $hariIni;
            break;

        case 'bulan_ini':
            $mulai = $hariIni->modify('first day of this month');
            $selesai = $hariIni;
            break;

        case '6_bulan':
            $mulai = $hariIni->modify('first day of this month')->modify('-5 months');
            $selesai = $hariIni;
            break;

        case 'kustom':
            $tanggalMulai = $query['tanggal_mulai'] ?? null;
            $tanggalSelesai = $query['tanggal_selesai'] ?? null;
            if (!is_string($tanggalMulai) || !is_string($tanggalSelesai)) {
                throw new InvalidArgumentException('Tanggal mulai dan selesai wajib diisi.');
            }
            $mulai = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggalMulai, $zona);
            $selesai = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggalSelesai, $zona);
            if (!$mulai || !$selesai
                || $mulai->format('Y-m-d') !== $tanggalMulai
                || $selesai->format('Y-m-d') !== $tanggalSelesai
            ) {
                throw new InvalidArgumentException('Format tanggal harus YYYY-MM-DD dan tanggal harus valid.');
            }
            break;

        default:
            throw new InvalidArgumentException('Periode tidak dikenal.');
    }

    if ($mulai > $selesai) {
        throw new InvalidArgumentException('Tanggal mulai tidak boleh setelah tanggal selesai.');
    }

    // Batasi rentang agar query/chart tidak memproses jumlah titik tak terbatas.
    if ($mulai->diff($selesai)->days > 365) {
        throw new InvalidArgumentException('Rentang maksimal 366 hari.');
    }

    return [
        'jenis' => $jenis,
        'mulai' => $mulai->format('Y-m-d'),
        'selesai' => $selesai->format('Y-m-d'),
    ];
}
