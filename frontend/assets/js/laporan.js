window.SIMART_Laporan = {
    penjualan: (tanggalMulai, tanggalSelesai) => window.SIMART_Transaksi.laporan(tanggalMulai, tanggalSelesai),
    stok: () => window.SIMART_API.get('laporan/ringkasan_stok.php'),
    kunjungan: () => window.SIMART_API.get('laporan/laporan_kunjungan.php'),
};
