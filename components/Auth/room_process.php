<?php
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../LOGIN/room_login.php');
    exit;
}

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;
$role = $_POST['role'] ?? null;

if (is_string($username) && is_string($role) && in_array($role, ['student', 'admin'], true) && trim($username) !== '') {
    require __DIR__ . '/../../room_config.php';
    $stmt = $pdo->prepare('SELECT user_id, username, role, password_sha256 FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([trim($username)]);
    $account = $stmt->fetch();
} else {
    $account = false;
}

if ($account && $account['role'] === $role && (
    $role === 'student' || (is_string($password) && hash_equals($account['password_sha256'], hash('sha256', $password)))
)) {
    session_start();
    session_regenerate_id(true);
    $_SESSION['room_logged_in'] = true;
    $_SESSION['room_user_id'] = (int) $account['user_id'];
    $_SESSION['room_username'] = $account['username'];
    $_SESSION['room_role'] = $account['role'];
    header('Location: ../../room_index.php');
    exit;
}

header('Location: ../LOGIN/room_login.php?role=' . ($role === 'admin' ? 'admin' : 'student') . '&error=1');
exit;
