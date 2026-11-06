<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';

$currentUserId = positiveId($_SESSION['room_user_id'] ?? null);
$actorStmt = $pdo->prepare('SELECT username, role, password_sha256 FROM users WHERE user_id = ?');
$actorStmt->execute([$currentUserId]);
$actor = $actorStmt->fetch();
if (!$actor) {
    header('Location: ../LOGIN/room_logout.php');
    exit;
}
if ($actor['role'] !== 'admin') {
    http_response_code(403);
    exit('Only an admin/teacher can add accounts.');
}

$_SESSION['room_role'] = $actor['role'];
$_SESSION['room_username'] = $actor['username'];
$_SESSION['room_account_csrf'] ??= bin2hex(random_bytes(32));

$error = '';
$username = '';
$email = '';
$phone = '';
$role = 'student';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
    $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    $currentPassword = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
    $csrf = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';

    if (!hash_equals($_SESSION['room_account_csrf'], $csrf)) {
        http_response_code(403);
        $error = 'This form expired. Refresh the page and try again.';
    } elseif (strlen($username) < 3 || strlen($username) > 80) {
        $error = 'Username must be between 3 and 80 characters.';
    } elseif ($email !== '' && (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $error = 'Enter a valid email address or leave it blank.';
    } elseif ($phone !== '' && !preg_match('/^[0-9+() -]{7,20}$/', $phone)) {
        $error = 'Enter a valid contact number or leave it blank.';
    } elseif (!in_array($role, ['admin', 'student'], true)) {
        $error = 'Choose a valid account role.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } elseif (!hash_equals($actor['password_sha256'], hash('sha256', $currentPassword))) {
        $error = 'Admin password is incorrect.';
    } else {
        $duplicateStmt = $pdo->prepare('SELECT user_id FROM users WHERE username = :username OR email = :email LIMIT 1');
        foreach (array_filter([$username, $email], static fn(string $value): bool => $value !== '') as $identifier) {
            $duplicateStmt->execute(['username' => $identifier, 'email' => $identifier]);
            if ($duplicateStmt->fetch()) {
                $error = 'That username or email is already in use.';
                break;
            }
        }
        if ($error === '') {
            try {
                $insertStmt = $pdo->prepare('INSERT INTO users (username, email, phone, password_sha256, role) VALUES (?, ?, ?, ?, ?)');
                $insertStmt->execute([$username, $email !== '' ? $email : null, $phone !== '' ? $phone : null, hash('sha256', $password), $role]);
                header('Location: room_accounts.php?created=1');
                exit;
            } catch (PDOException $exception) {
                $error = 'The account could not be added. Try a different username or email.';
            }
        }
    }
}

ob_start();
?>
<p class="account-back"><a href="room_accounts.php">← Back to accounts</a></p>
<?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<div class="account-grid">
    <section class="account-card" aria-labelledby="new-account-title">
        <div class="account-card-heading">
            <div>
                <span class="account-eyebrow">NEW ACCOUNT</span>
                <h2 id="new-account-title">Account details</h2>
            </div>
        </div>
        <form method="post" class="account-form">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['room_account_csrf']) ?>">
            <label for="account-username">Username
                <input id="account-username" name="username" maxlength="80" minlength="3" required autocomplete="off" value="<?= e($username) ?>">
            </label>
            <label for="account-email">Email (optional)
                <input id="account-email" name="email" type="email" maxlength="255" autocomplete="off" value="<?= e($email) ?>">
            </label>
            <label for="account-phone">Contact number (optional)
                <input id="account-phone" name="phone" type="tel" maxlength="20" autocomplete="off" value="<?= e($phone) ?>">
            </label>
            <label for="account-role">Role
                <select id="account-role" name="role" required>
                    <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin/Teacher</option>
                </select>
            </label>
            <label for="account-password">Password
                <input id="account-password" name="password" type="password" minlength="8" required autocomplete="new-password">
            </label>
            <label for="account-confirm-password">Confirm password
                <input id="account-confirm-password" name="confirm_password" type="password" minlength="8" required autocomplete="new-password">
            </label>
            <label for="account-current-password">Admin password
                <input id="account-current-password" name="current_password" type="password" required autocomplete="current-password" placeholder="Required to add an account">
            </label>
            <button type="submit">Add account</button>
        </form>
    </section>
</div>
<?php page('Add Account', ob_get_clean());
