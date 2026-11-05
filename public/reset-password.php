<?php
// Halaman Reset Password (PHP + HTML dalam 1 file).
$pageTitle = 'Reset Password | SIMART';
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
    <main class="container page-content">
        <h1>Atur Password Baru</h1>

        <!-- form password baru -->
        <form class="reservation-form" data-dummy>
            <div class="form-group"><label>Password baru</label><input type="password" minlength="8" required></div>
            <div class="form-group"><label>Ulangi password</label><input type="password" minlength="8" required></div>
            <button class="btn-primary" type="submit">Simpan password</button>
        </form>
        <p class="margin-top-30"><a href="/?auth=login">Kembali ke halaman masuk</a></p>
    </main>
    <script src="/js/main.js"></script>
</body>
</html>
