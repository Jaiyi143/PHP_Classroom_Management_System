<?php
declare(strict_types=1);

session_start();
if (($_SESSION['room_logged_in'] ?? false) === true) {
    header('Location: ../../room_index.php');
    exit;
}
$hasError = isset($_GET['error']);
$authPrompt = 'If you do not have an account, register.';
$authLinkText = 'register here!';
$authLinkHref = 'room_register.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | Room Management System</title>
    <link rel="stylesheet" href="../../Style/styles.css?v=11">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <?php require __DIR__ . '/../Auth/auth_intro.php'; ?>
        <section class="auth-panel" aria-labelledby="login-heading">
            <h2 id="login-heading">Sign in</h2>
            <?php if ($hasError): ?><p class="error" role="alert">Incorrect username or password.</p><?php endif; ?>
            <?php if (isset($_GET['registered'])): ?><p class="auth-success">Account created. Sign in below.</p><?php endif; ?>
            <form action="../Auth/room_process.php" method="post">
                <label for="username">Email or username</label>
                <input id="username" name="username" type="text" placeholder="Enter email or username" autocomplete="username" required autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Password" autocomplete="current-password" required>
                <button type="submit">Login</button>
            </form>
            <p class="mt-6 text-center text-sm text-muted-dark">Don't have an account? <a class="font-semibold text-brand underline underline-offset-2 hover:text-brand-strong" href="room_register.php">Sign up</a></p>
        </section>
    </main>
</body>
</html>
