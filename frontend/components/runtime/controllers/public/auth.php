<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
require_once dirname(__DIR__, 3) . '/runtime/support/database.php';

simart_start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}

simart_verify_csrf();
$action = $_POST['action'] ?? '';
$returnTo = $_POST['return_to'] ?? '';

try {
    if ($action === 'register') {
        $db = simart_db();
        $name = trim((string) ($_POST['nama'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['no_hp'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || mb_strlen($email) > 150 || mb_strlen($phone) > 20 || strlen($password) < 8) {
            header('Location: ' . simart_url('/index.php') . '?auth=register&error=invalid');
            exit;
        }

        $statement = $db->prepare(
            'INSERT INTO users (nama, email, password, no_hp, role) '
            . 'VALUES (:nama, :email, :password, :no_hp, \'member\')'
        );
        $statement->execute([
            ':nama' => $name,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':no_hp' => $phone !== '' ? $phone : null,
        ]);

        header('Location: ' . simart_url('/index.php') . '?auth=login&registered=1');
        exit;
    }

    if ($action === 'login') {
        $db = simart_db();
        $identity = trim((string) ($_POST['identity'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $statement = $db->prepare(
            'SELECT id_user, nama, email, password, role FROM users '
            . 'WHERE lower(email) = lower(:email_identity) OR no_hp = :phone_identity LIMIT 1'
        );
        $statement->execute([':email_identity' => $identity, ':phone_identity' => $identity]);
        $account = $statement->fetch();

        if (!$account || !password_verify($password, $account['password'])) {
            header('Location: ' . simart_url('/index.php') . '?auth=login&error=invalid');
            exit;
        }

        if (!in_array($account['role'], ['admin', 'member'], true)) {
            http_response_code(403);
            exit('Role akun tidak dikenali.');
        }

        session_regenerate_id(true);
        $_SESSION['simart_user'] = [
            'id_user' => (int) $account['id_user'],
            'nama' => $account['nama'],
            'email' => $account['email'],
            'role' => $account['role'],
        ];
        $_SESSION['user'] = $_SESSION['simart_user'];

        header('Location: ' . simart_safe_return_path($returnTo, $account['role']));
        exit;
    }

    if ($action === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        header('Location: ' . simart_url('/index.php'));
        exit;
    }

    http_response_code(400);
    exit('Aksi tidak dikenal.');
} catch (Throwable $exception) {
    if ($action === 'register' && $exception instanceof PDOException && $exception->getCode() === '23505') {
        header('Location: ' . simart_url('/index.php') . '?auth=register&error=duplicate');
        exit;
    }
    error_log('SIMART authentication database error: ' . $exception->getMessage());
    http_response_code(503);
    exit('Layanan akun sedang tidak tersedia. Silakan coba lagi.');
}
