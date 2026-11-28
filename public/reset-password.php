<?php
// Halaman Reset Password (PHP + HTML dalam 1 file).
$pageTitle = 'Reset Password | SIMART';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="/images/rbr.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=20261128-public-qa">
    <link rel="stylesheet" href="/css/responsive.css?v=20261128-public-qa">
</head>

<body>
    <main class="container page-content">
        <h1>Atur Password Baru</h1>

        <!-- form password baru -->
        <form class="reservation-form" id="reset-password-form">
            <div class="form-group"><label>Password baru</label><input type="password" minlength="8" required></div>
            <div class="form-group"><label>Ulangi password</label><input type="password" minlength="8" required></div>
            <button class="btn-primary" type="submit">Simpan password</button>
        </form>
        <p class="margin-top-30"><a href="/?auth=login">Kembali ke halaman masuk</a></p>
    </main>
    <?php require __DIR__ . '/_footer.php'; ?>
    <script src="/js/api.js"></script>
    <script src="/js/main.js?v=20261128-public-qa"></script>
    <script src="/js/password.js"></script>
</body>
</html>
