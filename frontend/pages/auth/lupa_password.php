<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password | SIMART</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body>
    <main class="container page-content">
        <h1>Lupa Password</h1>
        <p>Masukkan email akunmu. Jika terdaftar, tautan reset akan dikirim.</p>
        <form class="api-form" data-endpoint="auth/lupa_password.php">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required>
            <button class="btn-primary" type="submit">Kirim tautan reset</button>
            <p class="form-message" role="status" aria-live="polite"></p>
        </form>
        <a href="login.php">Kembali ke halaman masuk</a>
    </main>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/auth.js"></script>
</body>
</html>
