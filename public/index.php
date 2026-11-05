<?php
// Halaman Beranda (PHP + HTML dalam 1 file).
$pageTitle = 'Rumah Budaya Ratna | SIMART';

// data dummy preview katalog: judul, penulis, kategori, harga, stok
$books = [
    ['Bumi Manusia', 'Pramoedya Ananta Toer', 'SASTRA', 85000, 12],
    ['Laskar Pelangi', 'Andrea Hirata', 'SASTRA', 70000, 8],
    ['Sapiens', 'Yuval Noah Harari', 'SEJARAH', 120000, 5],
    ['Filosofi Teras', 'Henry Manampiring', 'UMUM', 88000, 15],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/responsive.css">
</head>

<body>
    <!-- navbar -->
    <header class="navbar">
        <div class="container nav-container">
            <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span class="logo-text">SIMART</span></a>
            <nav class="nav-menu">
                <a class="active" href="/">Beranda</a>
                <a href="/tentang.php">Tentang</a>
                <a href="/infoevent.php">Info Event</a>
                <a href="/katalog.php">Katalog Buku</a>
                <a href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <div class="nav-auth">
                <a class="btn-secondary" href="/?auth=login">Masuk</a>
                <a class="btn-primary" href="/?auth=register">Daftar</a>
            </div>
        </div>
    </header>

    <!-- hero -->
    <section class="hero">
        <div class="container hero-container">
            <div class="hero-content">
                <h1>Jelajahi Koleksi Buku &amp; Rumah Budaya Ratna</h1>
                <p>Temukan buku favorit Anda dan reservasi ruang budaya di Rumah Budaya Ratna, pusat literasi dan kebudayaan di Malang.</p>
                <div class="hero-buttons">
                    <a href="/katalog.php" class="btn-primary">Lihat Katalog</a>
                    <a href="/reservasi.php" class="btn-secondary">Cek Jadwal Ruang</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="/images/rbrfront.jpeg" alt="Suasana Rumah Budaya Ratna" style="width:100%;height:320px;object-fit:cover;border-radius:16px">
            </div>
        </div>
    </section>

    <!-- tentang bu ratna -->
    <section id="tentang" class="tentang-section">
        <div class="container tentang-container">
            <div class="tentang-image">
                <div class="profile-frame"><span>Foto / Ilustrasi Bu Ratna</span></div>
            </div>
            <div class="tentang-content">
                <span class="sub-title">Sosok Di Balik Layar</span>
                <h2>Sutrisni Ratna Artsanti (Bu Ratna)</h2>
                <p>Rumah Budaya Ratna berdiri atas dedikasi cinta literasi dan kebudayaan seorang tokoh pendidik serta pecinta seni Malang. Beliau merawat warisan sastra, mendukung seniman lokal, dan menciptakan ruang inkubasi bagi generasi muda.</p>
                <p>Bagi Bu Ratna, rumah bukan sekadar bangunan fisik, melainkan ruang hangat untuk bertukar pikiran dan merayakan keanekaragaman budaya.</p>
            </div>
        </div>
    </section>

    <!-- preview katalog -->
    <section class="katalog-preview">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Katalog Buku</h2>
                    <p>Pilihan koleksi literasi terbaik di Rumah Budaya Ratna</p>
                </div>
                <a href="/katalog.php" class="btn-outline">Lihat Semua Buku &rarr;</a>
            </div>
            <div class="book-grid">
                <?php foreach ($books as [$judul, $penulis, $kategori, $harga, $stok]): ?>
                <div class="book-card">
                    <div class="book-cover"><span>Cover Buku</span></div>
                    <div class="book-info">
                        <span class="category-badge"><?= $kategori ?></span>
                        <h3><?= htmlspecialchars($judul) ?></h3>
                        <p class="author"><?= htmlspecialchars($penulis) ?></p>
                        <div class="book-footer">
                            <span class="price">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                            <span class="stock">Stok: <?= $stok ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ruang budaya -->
    <section class="ruang-section">
        <div class="container">
            <div class="text-center">
                <h2>Reservasi Ruang Budaya</h2>
                <p>Ruangan representatif untuk kegiatan budaya, literasi, diskusi, dan kreatif Anda.</p>
            </div>
            <div class="ruang-grid-two">
                <div class="ruang-card">
                    <div class="ruang-image"><span>Pendopo Utama</span></div>
                    <div class="ruang-info">
                        <span class="capacity-tag">👥 100-150 Orang</span>
                        <h3>Pendopo Utama</h3>
                        <p>Area terbuka beratap joglo, ideal untuk pertunjukan seni, pameran, dan lokakarya.</p>
                    </div>
                </div>
                <div class="ruang-card">
                    <div class="ruang-image"><span>Ruang Diskusi &amp; Lesehan</span></div>
                    <div class="ruang-info">
                        <span class="capacity-tag">👥 16-40 Orang</span>
                        <h3>Ruang Diskusi &amp; Lesehan</h3>
                        <p>Ruang tertutup dengan papan tulis dan proyektor, pas untuk rapat dan diskusi komunitas.</p>
                    </div>
                </div>
            </div>
            <div class="text-center margin-top-30">
                <a href="/reservasi.php" class="btn-primary btn-large">Ajukan Reservasi Ruang</a>
            </div>
        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-col brand-col">
                <h3>SIMART</h3>
                <p>Platform literasi dan reservasi ruang kebudayaan di Rumah Budaya Ratna Malang.</p>
            </div>
            <div class="footer-col">
                <h4>Tautan Cepat</h4>
                <ul>
                    <li><a href="/">Beranda</a></li>
                    <li><a href="/katalog.php">Katalog Buku</a></li>
                    <li><a href="/reservasi.php">Reservasi Ruang</a></li>
                    <li><a href="/tentang.php">Tentang</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <p>Jl. Cokroaminoto No.6, Klojen, Kota Malang, Jawa Timur 65111</p>
                <p>info@rumahbudayaratna.org</p>
                <p>+62 341 555123</p>
            </div>
            <div class="footer-col map-col">
                <h4>Lokasi Kami</h4>
                <div class="map-wrapper">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3375.373829808816!2d112.63107834774493!3d-7.970008297222715!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd6290052a77475%3A0xa948270dfe439e48!2sRumah%20Budaya%20Ratna!5e0!3m2!1sen!2sid!4v1790146759693!5m2!1sen!2sid" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
        </div>
        <div class="footer-bottom"><p>&copy; 2026 SIMART Rumah Budaya Ratna.</p></div>
    </footer>

    <!-- popup masuk / daftar (dibuka lewat ?auth=login atau ?auth=register) -->
    <div class="modal-overlay" id="modal-auth">
        <div class="modal-box">
            <button type="button" class="modal-close" id="close-modal">&times;</button>
            <div class="modal-tabs">
                <button type="button" class="tab-btn active" id="tab-login-btn">Masuk</button>
                <button type="button" class="tab-btn" id="tab-register-btn">Daftar</button>
            </div>

            <!-- form masuk -->
            <form id="form-login" class="auth-form active" data-dummy>
                <div class="form-group">
                    <label>Email atau Nomor Telepon</label>
                    <input type="text" placeholder="contoh: budi@ratna.org">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" placeholder="Masukkan password Anda">
                </div>
                <button type="submit" class="btn-primary full-width">Masuk</button>
                <p><a href="/lupa-password.php">Lupa password?</a></p>
            </form>

            <!-- form daftar -->
            <form id="form-register" class="auth-form" data-dummy>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" placeholder="Nama lengkap"></div>
                <div class="form-group"><label>Email</label><input type="email" placeholder="budi@ratna.org"></div>
                <div class="form-group"><label>Nomor WhatsApp</label><input type="tel" placeholder="0812XXXXXXXX"></div>
                <div class="form-group"><label>Password</label><input type="password" placeholder="Minimal 8 karakter"></div>
                <button type="submit" class="btn-primary full-width">Daftar Akun</button>
            </form>
        </div>
    </div>

    <script src="/js/main.js"></script>
</body>
</html>
