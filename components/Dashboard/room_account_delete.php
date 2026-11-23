<?php
require __DIR__ . '/../../room_helpers.php';
requireLogin();
require __DIR__ . '/../../room_config.php';
requireAdminTeacher($pdo);

$currentUserId = positiveId($_SESSION['room_user_id'] ?? null);
$accountId = positiveId($_GET['id'] ?? null);
if ($accountId === 0 || $accountId === $currentUserId) {
    header('Location: room_accounts.php');
    exit;
}

$accountStmt = $pdo->prepare('SELECT username, email, role FROM users WHERE user_id = ?');
$accountStmt->execute([$accountId]);
$account = $accountStmt->fetch();
if (!$account) {
    http_response_code(404);
    exit('Account not found.');
}

$_SESSION['room_account_csrf'] ??= bin2hex(random_bytes(32));
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $csrf = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
    if (!hash_equals($_SESSION['room_account_csrf'], $csrf)) {
        http_response_code(403);
        $error = 'This confirmation expired. Refresh the page and try again.';
    } else {
        try {
            $deleteStmt = $pdo->prepare('DELETE FROM users WHERE user_id = ? AND user_id <> ?');
            $deleteStmt->execute([$accountId, $currentUserId]);
            if ($deleteStmt->rowCount() === 1) {
                header('Location: room_accounts.php?deleted=1');
                exit;
            }
            $error = 'The account could not be found. Refresh the accounts page.';
        } catch (PDOException $exception) {
            $error = 'The account could not be deleted. Please try again.';
        }
    }
}

ob_start(); ?>
<div class="dashboard-edit-overlay">
    <section class="dashboard-edit-modal account-delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-account-heading">
        <div class="room-modal-topbar"><span class="room-modal-eyebrow">DELETE ACCOUNT</span><a class="room-modal-close" href="room_accounts.php" aria-label="Close delete account">×</a></div>
        <div class="dashboard-edit-content">
            <span class="room-modal-kicker"><?= e(strtoupper(roomRoleLabel($account['role']))) ?> ACCOUNT</span>
            <h2 id="delete-account-heading"><?= e($account['username']) ?></h2>
            <?php if ($account['email']): ?><p class="dashboard-edit-subtitle"><?= e($account['email']) ?></p><?php endif; ?>
            <p>Delete this account? This action cannot be undone.</p>
            <?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
            <form method="post" class="account-delete-form">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['room_account_csrf']) ?>">
                <a href="room_accounts.php">Cancel</a>
                <button type="submit" class="danger">Yes, delete account</button>
            </form>
        </div>
    </section>
</div>
<?php page('Delete Account', ob_get_clean());
