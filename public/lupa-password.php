<?php
// Halaman Lupa Password (PHP + HTML dalam 1 file).
$pageTitle = 'Lupa Password | SIMART';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=20261125-reservation-popup">
    <link rel="stylesheet" href="/css/responsive.css?v=20261125-reservation-popup">
</head>

<body>
    <main class="container page-content">
        <h1>Lupa Password</h1>
        <p class="subtitle">Masukkan email akunmu. Jika terdaftar, tautan reset akan dikirim.</p>

        <!-- form email -->
        <form class="reservation-form" id="forgot-password-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" required>
            </div>
            <button class="btn-primary" type="submit">Kirim tautan reset</button>
        </form>
        <p class="margin-top-30"><a href="/?auth=login">Kembali ke halaman masuk</a></p>
    </main>
    <?php require __DIR__ . '/_footer.php'; ?>
    <script src="/js/api.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/password.js"></script>
</body>
</html>
