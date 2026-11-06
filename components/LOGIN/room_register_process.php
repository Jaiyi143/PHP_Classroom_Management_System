<?php
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: room_register.php');
    exit;
}

session_start();
$email = $_POST['email'] ?? null;
$username = $_POST['username'] ?? null;
$phone = $_POST['phone'] ?? null;
$password = $_POST['password'] ?? null;
$confirm = $_POST['confirm_password'] ?? null;

if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || !is_string($username) || strlen(trim($username)) < 3 || strlen($username) > 80
    || !is_string($phone) || ($phone !== '' && !preg_match('/^[0-9+() -]{7,20}$/', $phone))
    || !is_string($password) || strlen($password) < 8
    || !is_string($confirm) || $password !== $confirm) {
    $_SESSION['register_error'] = 'Please check the information and matching passwords.';
    header('Location: room_register.php');
    exit;
}

require __DIR__ . '/../../room_config.php';
try {
    $stmt = $pdo->prepare('INSERT INTO users (username, email, phone, password_sha256, role) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([trim($username), trim($email), trim($phone) ?: null, hash('sha256', $password), 'user']);
} catch (PDOException $exception) {
    $_SESSION['register_error'] = 'That username or email is already in use, or the database needs its users migration.';
    header('Location: room_register.php');
    exit;
}

header('Location: room_login.php?registered=1');
exit;
