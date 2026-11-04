<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumah Budaya Ratna | SIMART</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(simart_url('/assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(simart_url('/assets/css/responsive.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>

    <!-- Header / Navbar -->
    <?php require_once dirname(__DIR__, 3) . '/header.php'; ?>

    <!-- Hero Section (Fullscreen height) -->
    <section id="beranda" class="hero">
        <div class="container hero-container">
            <div class="hero-content">
                <h1>Jelajahi Koleksi Buku & Rumah Budaya Ratna</h1>
                <p>Temukan buku favorit Anda dan reservasi ruang budaya di Rumah Budaya Ratna, pusat literasi dan kebudayaan di Malang.</p>
                <div class="hero-buttons">
                    <a href="<?= htmlspecialchars(simart_url('/pages/buku/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">Lihat Katalog</a>
                    <a href="<?= htmlspecialchars(simart_url('/pages/reservasi/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary">Cek Jadwal Ruang</a>
                </div>
            </div>
            <div class="hero-image">
                    <img src="<?= htmlspecialchars(simart_url('/assets/images/rbrfront.jpeg'), ENT_QUOTES, 'UTF-8') ?>" alt="Suasana Rumah Budaya Ratna" style="width:100%;height:320px;object-fit:cover;border-radius:16px">
            </div>
        </div>
    </section>

    <!-- Section 2: Tentang Bu Ratna & Rumah Budaya -->
    <section id="tentang" class="tentang-section">
        <div class="container tentang-container">
                <div class="tentang-image">
                <div class="profile-frame">
                    <span>Foto / Ilustrasi Bu Ratna</span>
                </div>
            </div>
            <div class="tentang-content">
                <span class="sub-title">Sosok Di Balik Layar</span>
                <h2>Ratna Indraswari Ibrahim (Bu Ratna)</h2>
                <p>
                    Rumah Budaya Ratna berdiri atas azas dedikasi cinta literasi dan kebudayaan seorang tokoh pendidik serta pecinta seni Malang, Ibu Sutrisni Ratna Artsanti. Beliau mendedikasikan hidupnya untuk merawat warisan sastra, mendukung seniman lokal, serta menciptakan ruang inkubasi bagi generasi muda.
                </p>
                <p>
                    Bagi Bu Ratna, rumah bukan sekadar bangunan fisik, melainkan ruang hangat untuk saling bertukar pikiran dan merayakan keanekaragaman budaya. Melalui platform SIMART, cita-cita beliau diwujudkan dalam kemudahan akses pendaftaran komunitas, pustaka digital budaya, serta keterbukaan ruang diskusi bagi siapa saja yang ingin berkarya.
                </p>
            </div>
        </div>
    </section>

    <!-- Section 3: Preview Katalog Buku -->
    <section id="katalog" class="katalog-preview">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Katalog Buku</h2>
                    <p>Pilihan koleksi literasi terbaik di Rumah Budaya Ratna</p>
                </div>
                <a href="<?= htmlspecialchars(simart_url('/pages/buku/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-outline">Lihat Semua Buku &rarr;</a>
            </div>

            <div class="book-grid">
                <div class="book-card">
                    <div class="book-cover"><span>Cover Buku</span></div>
                    <div class="book-info">
                        <span class="category-badge">SASTRA</span>
                        <h3>Bumi Manusia</h3>
                        <p class="author">Pramoedya Ananta Toer</p>
                        <div class="book-footer">
                            <span class="price">Rp 85.000</span>
                            <span class="stock">Stok: 12</span>
                        </div>
                    </div>
                </div>

                <div class="book-card">
                    <div class="book-cover"><span>Cover Buku</span></div>
                    <div class="book-info">
                        <span class="category-badge">SASTRA</span>
                        <h3>Laskar Pelangi</h3>
                        <p class="author">Andrea Hirata</p>
                        <div class="book-footer">
                            <span class="price">Rp 70.000</span>
                            <span class="stock">Stok: 8</span>
                        </div>
                    </div>
                </div>

                <div class="book-card">
                    <div class="book-cover"><span>Cover Buku</span></div>
                    <div class="book-info">
                        <span class="category-badge">SEJARAH</span>
                        <h3>Sapiens</h3>
                        <p class="author">Yuval Noah Harari</p>
                        <div class="book-footer">
                            <span class="price">Rp 120.000</span>
                            <span class="stock">Stok: 5</span>
                        </div>
                    </div>
                </div>

                <div class="book-card">
                    <div class="book-cover"><span>Cover Buku</span></div>
                    <div class="book-info">
                        <span class="category-badge">UMUM</span>
                        <h3>Filosofi Teras</h3>
                        <p class="author">Henry Manampiring</p>
                        <div class="book-footer">
                            <span class="price">Rp 88.000</span>
                            <span class="stock">Stok: 15</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 4: Reservasi Ruang Budaya (2 Card + 1 Tombol Utama) -->
    <section id="ruang" class="ruang-section">
        <div class="container">
            <div class="section-title text-center">
                <h2>Reservasi Ruang Budaya</h2>
                <p>Kami menyediakan berbagai tipe ruangan representatif untuk menunjang segala aktivitas kebudayaan, literasi, diskusi, dan kegiatan kreatif Anda.</p>
            </div>

            <div class="ruang-grid-two">
                <div class="ruang-card">
                    <div class="ruang-image"><span>Pendopo Utama</span></div>
                    <div class="ruang-info">
                        <span class="capacity-tag">👥 100-150 Orang</span>
                        <h3>Pendopo Utama</h3>
                        <p>Area terbuka beratap joglo tradisional, ideal untuk pertunjukan seni, pameran besar, lokakarya budaya hingga seminar komunitas.</p>
                    </div>
                </div>

                <div class="ruang-card">
                    <div class="ruang-image"><span>Ruang Diskusi & Lesehan</span></div>
                    <div class="ruang-info">
                        <span class="capacity-tag">👥 16-40 Orang</span>
                        <h3>Ruang Diskusi & Lesehan</h3>
                        <p>Area santai maupun ruang ber-AC tertutup dengan kelengkapan papan tulis dan proyektor, pas untuk rapat formal atau diskusi komunitas.</p>
                    </div>
                </div>
            </div>

            <div class="text-center margin-top-30">
                <a href="<?= htmlspecialchars(simart_url('/pages/reservasi/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary btn-large">Ajukan Reservasi Ruang</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php $footerVariant = 'landing'; require_once dirname(__DIR__, 3) . '/footer.php'; ?>

    <!-- Modal Pop-Up Auth -->
    <div class="modal-overlay" id="modal-auth">
        <div class="modal-box">
            <button type="button" class="modal-close" id="close-modal">&times;</button>
            
            <div class="modal-tabs">
                <button type="button" class="tab-btn active" id="tab-login-btn">Masuk</button>
                <button type="button" class="tab-btn" id="tab-register-btn">Daftar</button>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <p class="subtitle" role="alert">Data masuk atau pendaftaran tidak valid. Periksa kembali lalu coba lagi.</p>
            <?php elseif (isset($_GET['registered'])): ?>
                <p class="subtitle" role="status">Pendaftaran berhasil. Silakan masuk dengan akun baru.</p>
            <?php endif; ?>

            <!-- Form Login -->
            <form id="form-login" class="auth-form active" method="post" action="<?= htmlspecialchars(simart_url('/pages/auth/submit.php'), ENT_QUOTES, 'UTF-8') ?>">
                <?= simart_csrf_field() ?>
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars((string) ($_GET['return_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group">
                    <label>Email atau Nomor Telepon</label>
                    <input type="text" name="identity" autocomplete="username" placeholder="contoh: budi@ratna.org atau 081234..." required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" autocomplete="current-password" placeholder="Masukkan password Anda" required>
                </div>
                <button type="submit" class="btn-primary full-width">Masuk</button>
                <p><a href="<?= htmlspecialchars(simart_url('/pages/auth/lupa_password.php'), ENT_QUOTES, 'UTF-8') ?>">Lupa password?</a></p>
            </form>

            <!-- Form Register -->
            <form id="form-register" class="auth-form" method="post" action="<?= htmlspecialchars(simart_url('/pages/auth/submit.php'), ENT_QUOTES, 'UTF-8') ?>">
                <?= simart_csrf_field() ?>
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" maxlength="100" autocomplete="name" placeholder="Masukkan nama lengkap Anda" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" maxlength="150" autocomplete="email" placeholder="contoh: budi@ratna.org" required>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp</label>
                    <input type="tel" name="no_hp" maxlength="20" autocomplete="tel" placeholder="contoh: 0812XXXXXXXX">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
                </div>
                <button type="submit" class="btn-primary full-width">Daftar Akun</button>
            </form>
        </div>
    </div>

    <script src="<?= htmlspecialchars(simart_url('/assets/js/auth.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php if (isset($_GET['auth'])): ?>
    <script>window.simartAuthTab = <?= json_encode($_GET['auth'] === 'register' ? 'register' : 'login') ?>;</script>
    <?php endif; ?>
</body>
</html>
