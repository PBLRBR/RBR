# SIMART Rumah Budaya Ratna

```text
RBR/
├── backend/
│   ├── api/
│   │   ├── auth/
│   │   ├── buku/
│   │   ├── karya/
│   │   ├── kategori/
│   │   ├── kunjungan/
│   │   ├── laporan/
│   │   ├── pengunjung/
│   │   ├── reservasi/
│   │   ├── tempat/
│   │   └── transaksi/
│   ├── config/
│   ├── vendor/                # dibuat oleh Composer, diabaikan Git
│   ├── composer.json
│   └── composer.lock
├── frontend/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css
│   │   │   ├── dashboard.css
│   │   │   └── responsive.css
│   │   ├── js/
│   │   │   ├── api.js
│   │   │   ├── auth.js
│   │   │   ├── buku.js
│   │   │   ├── kategori.js
│   │   │   ├── karya.js
│   │   │   ├── reservasi.js
│   │   │   ├── transaksi.js
│   │   │   └── laporan.js
│   │   └── images/
│   ├── components/
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   ├── navbar.php
│   │   └── footer.php
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── lupa_password.php
│   │   │   └── reset_password.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── buku/
│   │   │   ├── index.php
│   │   │   ├── tambah.php
│   │   │   ├── edit.php
│   │   │   └── detail.php
│   │   ├── kategori/
│   │   ├── karya/
│   │   ├── reservasi/
│   │   ├── transaksi/
│   │   ├── pengunjung/
│   │   ├── kunjungan/
│   │   └── laporan/
│   ├── index.php
│   └── .htaccess
├── .gitignore
└── README.md
```

Controller, model, view, dan helper PHP diletakkan sebagai file langsung di dalam kelompok halaman yang memakainya, misalnya `Book.php` dan `catalog.view.php` di `frontend/pages/buku/`. Tidak ada lagi folder `models/`, `views/`, `support/`, atau `runtime/`. `frontend/components/` hanya berisi header, navbar, sidebar, dan footer. `.htaccess` membatasi akses langsung ke file controller, view, dan helper.

## Menjalankan lokal

1. Siapkan `backend/config/database.local.php` dengan array `host`, `port`, `dbname`, `user`, dan `password` untuk PostgreSQL lokal.
2. Dari folder `backend/`, jalankan `composer install` agar PHPMailer tersedia di `backend/vendor/`.
3. Dari folder proyek, jalankan `php -S 127.0.0.1:8000`, lalu buka <http://127.0.0.1:8000/frontend/>.

Buka frontend melalui `/frontend/` agar endpoint API di `/backend/api/` tetap berada pada origin yang sama. File konfigurasi lokal dan dependensi Composer tidak disimpan ke Git.

## Catatan API

`backend/api/transaksi/` disiapkan untuk modul transaksi. Versi API PBLReal belum memiliki endpoint transaksi; kasir saat ini memakai handler PHP yang diarahkan melalui `frontend/pages/dashboard/`.
