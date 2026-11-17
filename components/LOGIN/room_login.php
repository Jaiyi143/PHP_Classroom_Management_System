<?php
declare(strict_types=1);

session_start();
if (($_SESSION['room_logged_in'] ?? false) === true) {
    header('Location: ../../room_index.php');
    exit;
}
$hasError = isset($_GET['error']);
$role = ($_GET['role'] ?? '') === 'admin' ? 'admin' : 'student';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | Room Management System</title>
    <link rel="stylesheet" href="../../Style/styles.css?v=59">
    <script type="importmap">{"imports":{"three":"../../assets/vendor/three/three.module.js"}}</script>
</head>
<body class="auth-page">
    <main class="auth-layout">
        <?php require __DIR__ . '/../Auth/auth_intro.php'; ?>
        <div class="auth-panel-shell" data-auth-shell>
        <section class="auth-panel" aria-labelledby="login-heading">
            <header class="auth-panel-header">
                <span class="auth-eyebrow">TUP VISAYAS · CAMPUS ROOMS</span>
                <h2 id="login-heading">Sign in</h2>
                <p>Choose your account type to continue.</p>
            </header>
            <nav class="auth-role-nav" aria-label="Sign in as">
                <a href="room_login.php?role=student" <?= $role === 'student' ? 'aria-current="page"' : '' ?>>Student</a>
                <a href="room_login.php?role=admin" <?= $role === 'admin' ? 'aria-current="page"' : '' ?>>Admin/Teacher</a>
            </nav>
            <p class="error auth-form-error" role="alert" <?= $hasError ? '' : 'hidden' ?>>Sign in failed. Check your <?= $role === 'admin' ? 'username and password' : 'student username' ?>.</p>
            <p class="auth-role-note"><?= $role === 'admin' ? 'Admin/Teacher accounts need a username and password.' : 'Students: enter your assigned username. No password is needed.' ?></p>
            <form action="../Auth/room_process.php" method="post">
                <input type="hidden" name="role" value="<?= $role ?>">
                <div class="auth-field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" placeholder="Enter username" autocomplete="username" maxlength="80" required autofocus>
                </div>
                <?php if ($role === 'admin'): ?>
                    <div class="auth-field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" placeholder="Enter password" autocomplete="current-password" required>
                    </div>
                <?php endif; ?>
                <button class="auth-submit-button" type="submit">Sign in</button>
            </form>
        </section>
        <section class="auth-loading-panel" aria-hidden="true" aria-label="Signing in" tabindex="-1">
            <div class="auth-loading-visual" data-auth-loading-scene data-logo-src="../../assets/tup-logo.png">
                <img class="auth-loading-logo-fallback" src="../../assets/tup-logo.png" alt="">
            </div>
            <img class="auth-loading-jerjer" src="../../assets/jerjer.png" alt="" aria-hidden="true">
            <p class="auth-loading-caption">TUP VISAYAS · CAMPUS ROOMS</p>
            <p class="auth-loading-message" data-auth-stage role="status" aria-live="polite">Authenticating…</p>
            <div class="auth-loading-progress" aria-hidden="true"><span></span><span></span><span></span></div>
        </section>
        </div>
    </main>
    <script type="module" src="../../assets/js/login-animation.js?v=9"></script>
    <script type="module" src="../../assets/js/login-transition.js?v=2"></script>
</body>
</html>
