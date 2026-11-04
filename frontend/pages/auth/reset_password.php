<?php $token = htmlspecialchars((string) ($_GET['token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | SIMART</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body>
    <main class="container page-content">
        <h1>Atur Password Baru</h1>
        <form class="api-form" data-endpoint="auth/reset_password.php">
            <input type="hidden" name="token" value="<?= $token ?>">
            <label for="password">Password baru</label>
            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
            <label for="konfirmasi_password">Ulangi password</label>
            <input id="konfirmasi_password" name="konfirmasi_password" type="password" minlength="8" autocomplete="new-password" required>
            <button class="btn-primary" type="submit">Simpan password</button>
            <p class="form-message" role="status" aria-live="polite"></p>
        </form>
        <a href="login.php">Kembali ke halaman masuk</a>
    </main>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/auth.js"></script>
</body>
</html>
