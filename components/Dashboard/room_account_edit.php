<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
requireAdminTeacher($pdo);

$currentUserId = positiveId($_SESSION['room_user_id'] ?? null);
$actorStmt = $pdo->prepare('SELECT user_id, username, role, password_sha256 FROM users WHERE user_id = ?');
$actorStmt->execute([$currentUserId]);
$actor = $actorStmt->fetch();
if (!$actor) {
    header('Location: ../LOGIN/room_logout.php');
    exit;
}

$_SESSION['room_role'] = $actor['role'];
$_SESSION['room_username'] = $actor['username'];
$accountId = positiveId($_GET['id'] ?? null);
if ($accountId === 0) {
    header('Location: room_accounts.php');
    exit;
}
$accountStmt = $pdo->prepare('SELECT user_id, username, email, role FROM users WHERE user_id = ?');
$accountStmt->execute([$accountId]);
$account = $accountStmt->fetch();
if (!$account) {
    http_response_code(404);
    exit('Account not found.');
}

$_SESSION['room_account_csrf'] ??= bin2hex(random_bytes(32));
$error = '';
$username = $account['username'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $currentPassword = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
    $newPassword = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
    $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    $csrf = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';

    if (!hash_equals($_SESSION['room_account_csrf'], $csrf)) {
        http_response_code(403);
        $error = 'This form expired. Refresh the page and try again.';
    } elseif (strlen($username) < 3 || strlen($username) > 80) {
        $error = 'Username must be between 3 and 80 characters.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The new passwords do not match.';
    } elseif (!hash_equals($actor['password_sha256'], hash('sha256', $currentPassword))) {
        $error = 'Admin password is incorrect.';
    } else {
        $duplicateStmt = $pdo->prepare('SELECT user_id FROM users WHERE (username = :username OR email = :email) AND user_id <> :user_id LIMIT 1');
        $duplicateStmt->execute([
            'username' => $username,
            'email' => $username,
            'user_id' => $accountId,
        ]);
        if ($duplicateStmt->fetch()) {
            $error = 'That username is already in use.';
        } else {
            try {
                if ($newPassword === '') {
                    $updateStmt = $pdo->prepare('UPDATE users SET username = ? WHERE user_id = ?');
                    $updateStmt->execute([$username, $accountId]);
                } else {
                    $updateStmt = $pdo->prepare('UPDATE users SET username = ?, password_sha256 = ? WHERE user_id = ?');
                    $updateStmt->execute([$username, hash('sha256', $newPassword), $accountId]);
                }
                if ($accountId === $currentUserId) {
                    $_SESSION['room_username'] = $username;
                    session_regenerate_id(true);
                }
                header('Location: room_accounts.php?updated=1');
                exit;
            } catch (PDOException $exception) {
                $error = 'The username could not be saved. Try another username.';
            }
        }
    }
}

ob_start();
?>
<div class="dashboard-editor">
<p class="account-back"><a href="room_accounts.php">← Back to accounts</a></p>
<?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<div class="account-grid account-editor-grid">
    <section class="account-card" aria-labelledby="account-title">
        <div class="account-card-heading">
            <div>
                <span class="account-eyebrow"><?= e(strtoupper(roomRoleLabel($account['role']))) ?> ACCOUNT</span>
                <h2 id="account-title"><?= e($account['username']) ?></h2>
                <?php if ($account['email']): ?><p><?= e($account['email']) ?></p><?php endif; ?>
            </div>
            <?php if ($accountId === $currentUserId): ?><span class="account-self">You</span><?php endif; ?>
        </div>
        <form method="post" class="account-form">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['room_account_csrf']) ?>">
            <label for="account-username">Username
                <input id="account-username" name="username" maxlength="80" minlength="3" required autocomplete="off" value="<?= e($username) ?>">
            </label>
            <label for="account-new-password">New password
                <input id="account-new-password" name="new_password" type="password" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current">
            </label>
            <label for="account-confirm-password">Confirm new password
                <input id="account-confirm-password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" placeholder="Repeat new password">
            </label>
            <label for="account-current-password">Admin password
                <input id="account-current-password" name="current_password" type="password" required autocomplete="current-password" placeholder="Required to save changes">
            </label>
            <button type="submit">Save changes</button>
        </form>
    </section>
</div>
</div>
<?php page('Edit Account', ob_get_clean());
