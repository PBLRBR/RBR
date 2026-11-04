<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/database.php';

/** Queries used by the administrator dashboard and receipt. */
final class AdminDashboard
{
    public static function reservations(): array
    {
        return simart_db()->query(
            'SELECT r.id_reservasi, r.nama_acara, r.penanggung_jawab, r.kontak_wa, r.tanggal_acara, '
            . 'r.jam_mulai, r.jam_selesai, r.status_reservasi, r.dibuat_pada, u.nama AS nama_member, '
            . 't.nama_tempat FROM reservasi r JOIN users u ON u.id_user = r.id_user '
            . 'JOIN tempat t ON t.id_tempat = r.id_tempat ORDER BY r.dibuat_pada DESC'
        )->fetchAll();
    }

    public static function summary(): array
    {
        $db = simart_db();
        return [
            'dashboard' => $db->query(
                "SELECT COALESCE(SUM(total_bayar), 0) AS total_bulan FROM transaksi_penjualan "
                . "WHERE tanggal_transaksi >= date_trunc('month', CURRENT_TIMESTAMP)"
            )->fetch(),
            'units' => (int) $db->query(
                "SELECT COALESCE(SUM(d.jumlah_beli), 0) FROM detail_transaksi_penjualan d "
                . "JOIN transaksi_penjualan t ON t.id_transaksi = d.id_transaksi "
                . "WHERE t.tanggal_transaksi >= date_trunc('month', CURRENT_TIMESTAMP)"
            )->fetchColumn(),
            'pending' => (int) $db->query(
                "SELECT COUNT(*) FROM reservasi WHERE status_reservasi = 'menunggu'"
            )->fetchColumn(),
            'recent_sales' => $db->query(
                "SELECT t.id_transaksi, t.kode_invoice, t.tanggal_transaksi, t.total_bayar, t.metode_transaksi, "
                . "string_agg(b.judul || ' (' || d.jumlah_beli::text || ')', ', ' ORDER BY d.id_detail) AS items "
                . "FROM transaksi_penjualan t JOIN detail_transaksi_penjualan d ON d.id_transaksi = t.id_transaksi "
                . "JOIN buku b ON b.id_buku = d.id_buku GROUP BY t.id_transaksi "
                . "ORDER BY t.tanggal_transaksi DESC LIMIT 10"
            )->fetchAll(),
        ];
    }

    public static function receipt(int $transactionId): ?array
    {
        $db = simart_db();
        $header = $db->prepare(
            'SELECT t.id_transaksi, t.kode_invoice, t.tanggal_transaksi, t.nama_pembeli, '
            . 't.metode_transaksi, t.total_bayar, u.nama AS nama_admin '
            . 'FROM transaksi_penjualan t JOIN users u ON u.id_user = t.id_user '
            . 'WHERE t.id_transaksi = :id'
        );
        $header->execute([':id' => $transactionId]);
        $sale = $header->fetch();
        if (!is_array($sale)) {
            return null;
        }

        $lines = $db->prepare(
            'SELECT d.jumlah_beli, d.harga_satuan, d.subtotal, b.judul '
            . 'FROM detail_transaksi_penjualan d JOIN buku b ON b.id_buku = d.id_buku '
            . 'WHERE d.id_transaksi = :id ORDER BY d.id_detail'
        );
        $lines->execute([':id' => $transactionId]);

        return ['sale' => $sale, 'lines' => $lines->fetchAll()];
    }
}
