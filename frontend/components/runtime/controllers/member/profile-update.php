<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
require_once dirname(__DIR__, 3) . '/runtime/support/database.php';
$member = simart_require_role('member');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
simart_verify_csrf();

$name = trim((string) ($_POST['nama'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$phone = trim((string) ($_POST['no_hp'] ?? ''));
$currentPassword = (string) ($_POST['current_password'] ?? '');
$newPassword = (string) ($_POST['new_password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
if ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || mb_strlen($email) > 150 || mb_strlen($phone) > 20
    || (($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '')
        && ($currentPassword === '' || strlen($newPassword) < 8 || !hash_equals($newPassword, $confirmPassword)))) {
    header('Location: ' . simart_url('/pages/auth/profile.php') . '?updated=error');
    exit;
}

try {
    $db = simart_db();
    $db->beginTransaction();
    $existing = $db->prepare('SELECT password FROM users WHERE id_user = :id_user FOR UPDATE');
    $existing->execute([':id_user' => $member['id_user']]);
    $currentHash = $existing->fetchColumn();
    if (!is_string($currentHash) || ($currentPassword !== '' && !password_verify($currentPassword, $currentHash))) {
        $db->rollBack();
        header('Location: ' . simart_url('/pages/auth/profile.php') . '?updated=error');
        exit;
    }
    $newHash = $newPassword !== '' ? password_hash($newPassword, PASSWORD_DEFAULT) : $currentHash;
    $statement = $db->prepare(
        'UPDATE users SET nama = :nama, email = :email, no_hp = :no_hp, password = :password WHERE id_user = :id_user'
    );
    $statement->execute([
        ':nama' => $name,
        ':email' => $email,
        ':no_hp' => $phone !== '' ? $phone : null,
        ':password' => $newHash,
        ':id_user' => $member['id_user'],
    ]);
    $db->commit();
    $_SESSION['simart_user']['nama'] = $name;
    $_SESSION['simart_user']['email'] = $email;
    header('Location: ' . simart_url('/pages/auth/profile.php') . '?updated=1');
    exit;
} catch (Throwable $exception) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('SIMART profile update error: ' . $exception->getMessage());
    header('Location: ' . simart_url('/pages/auth/profile.php') . '?updated=error');
    exit;
}
