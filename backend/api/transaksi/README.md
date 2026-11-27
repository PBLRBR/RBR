# API Transaksi SIMART

Semua endpoint transaksi memerlukan sesi admin. Akses endpoint melalui
`/api/index.php?route=transaksi/<nama-file>.php`.

| Metode | Route | Fungsi |
|---|---|---|
| GET | `transaksi/transaksi.php` | Mengambil daftar transaksi dari yang terbaru. |
| GET | `transaksi/detail_transaksi.php&id_transaksi=<id>` | Mengambil rincian invoice dan item pembelian. |
| POST | `transaksi/tambah_transaksi.php` | Menyimpan checkout POS dan memperbarui stok buku. |

Checkout menerima JSON dengan `idempotency_key`, `metode_transaksi` (`cash`
atau `qris`), `nama_pembeli` (opsional), dan `items`. Setiap item berisi
`id_buku` dan `jumlah_beli`. Kirim token CSRF melalui header `X-CSRF-Token`.

- `201` berarti transaksi baru tersimpan.
- `200` berarti checkout yang sama sudah tersimpan. Stok tidak dikurangi lagi.
- `409` berarti kunci checkout yang sama dipakai dengan data transaksi berbeda.

Jika respons checkout tidak diterima, gunakan kembali kunci yang sama saat
mencoba ulang transaksi dengan data yang sama.