<?php
declare(strict_types=1);
session_start();
if (($_SESSION['room_logged_in'] ?? false) === true) {
    header('Location: ../../room_index.php');
    exit;
}
$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
$authPrompt = 'If you already have an account, sign in.';
$authLinkText = 'sign in here!';
$authLinkHref = 'room_login.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign up | Room Management System</title>
    <link rel="stylesheet" href="../../Style/styles.css?v=11">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <?php require __DIR__ . '/../Auth/auth_intro.php'; ?>
        <section class="auth-panel auth-panel--register" aria-labelledby="register-heading">
            <h2 id="register-heading">Sign up</h2>
            <?php if ($error !== ''): ?><p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <form action="room_register_process.php" method="post">
                <label for="email">Email</label><input id="email" name="email" type="email" placeholder="Email" autocomplete="email" required>
                <label for="username">Username</label><input id="username" name="username" placeholder="Create username" autocomplete="username" minlength="3" maxlength="80" required>
                <label for="phone">Contact number</label><input id="phone" name="phone" type="tel" placeholder="Contact number" autocomplete="tel" maxlength="20">
                <label for="password">Password</label><input id="password" name="password" type="password" placeholder="Password" autocomplete="new-password" minlength="8" required>
                <label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm password" autocomplete="new-password" minlength="8" required>
                <button type="submit">Register</button>
            </form>
            <p class="mt-6 text-center text-sm text-muted-dark">Already have an account? <a class="font-semibold text-brand underline underline-offset-2 hover:text-brand-strong" href="room_login.php">Sign in</a></p>
        </section>
    </main>
</body>
</html>
